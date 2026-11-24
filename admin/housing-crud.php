<?php
// Included by properties.php and units.php with fixed schema identifiers.
if (!isset($table)) { http_response_code(404); return; }
require_once __DIR__ . '/housing-fields.php';
$pageUrl = $table . '.php';
$action = $_GET['action'] ?? 'list';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$record = null;
$error = '';
if (in_array($action, ['edit', 'delete'], true) && $id) {
    $record = admin_rows($db, "SELECT * FROM $table WHERE $idColumn = ?", [$id])[0] ?? null;
}
if (!in_array($action, ['list', 'add'], true) && !$record) {
    http_response_code(404);
    $action = 'missing';
}

if (in_array($action, ['edit', 'add'], true)) {
    foreach ($fields as &$field) {
        if ($field[1] === 'lookup') {
            [$lookupTable, $lookupId, $lookupLabel] = $field[2];
            $options = admin_rows($db, "SELECT $lookupId, $lookupLabel FROM $lookupTable ORDER BY $lookupLabel");
            $field[1] = 'select';
            $field[2] = array_column($options, $lookupLabel, $lookupId);
        }
    }
    unset($field);
}

$values = $record ?? $defaults;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit', 'delete'], true)) {
    try {
        if ($action === 'delete') {
            admin_query($db, "DELETE FROM $table WHERE $idColumn = ?", [$id])->close();
            admin_redirect($pageUrl . '?deleted=1');
        }
        $values = array_map(static fn($value) => is_string($value) ? $value : '', $_POST);
        $validated = [];
        foreach ($fields as $column => $field) {
            $validated[$column] = admin_housing_value(admin_text($_POST, $column), $field);
        }
        $columns = array_keys($validated);
        if ($action === 'add') {
            $names = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $stmt = admin_query($db, "INSERT INTO $table ($names) VALUES ($placeholders)", array_values($validated));
            $id = $stmt->insert_id;
            $stmt->close();
        } else {
            admin_transaction($db, function () use ($db, $table, $idColumn, $id, $columns, $validated) {
                $current = admin_rows($db, "SELECT * FROM $table WHERE $idColumn = ? FOR UPDATE", [$id])[0] ?? null;
                if (!$current) { throw new InvalidArgumentException('This record no longer exists.'); }
                if ($table === 'units' && (int)$current['property_id'] !== (int)$validated['property_id']) {
                    $photos = admin_rows($db, 'SELECT photo_id FROM photos WHERE unit_id = ? LIMIT 1', [$id]);
                    $shared = admin_rows($db, 'SELECT housing_amenity_id FROM housing_amenities
                        WHERE unit_id = ? AND property_id IS NOT NULL LIMIT 1', [$id]);
                    if ($photos || $shared) {
                        throw new InvalidArgumentException('This unit cannot change properties while it has photos or shared property amenities.');
                    }
                }
                $assignments = implode(' = ?, ', $columns) . ' = ?';
                admin_query($db, "UPDATE $table SET $assignments WHERE $idColumn = ?",
                    [...array_values($validated), $id])->close();
            });
        }
        admin_redirect($pageUrl . '?action=edit&id=' . $id . '&saved=1');
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (mysqli_sql_exception $e) {
        error_log($entity . ' administration: ' . $e->getMessage());
        $error = match ((int)$e->getCode()) {
            1451 => $action === 'delete' ? $blockedDelete
                : 'This unit cannot change properties while it has related listings or reviews.',
            1452 => 'A selected option no longer exists. Choose an existing option and save again.',
            default => 'Could not save this change.',
        };
    }
}

$plural = $table === 'properties' ? 'Properties' : 'Units';
$title = match ($action) {
    'add' => 'Add ' . $entity,
    'edit' => 'Edit ' . $entity,
    'delete' => 'Delete ' . $entity,
    default => $plural,
};
$recordLabel = $record['name'] ?? (($record['unit_label'] ?? '') ?: 'Unit #' . $id);
include __DIR__ . '/header.php';
?>
<h1><?= escape($title) ?></h1>
<?php if ($error) { ?><p role="alert"><?= escape($error) ?></p><?php } ?>
<?php if ($action === 'missing') { ?>
    <p><?= escape($entity) ?> not found.</p>
    <a href="<?= escape($pageUrl) ?>">Back to <?= escape(strtolower($plural)) ?></a>
<?php } elseif ($action === 'list') {
    $rows = admin_rows($db, $listSql); ?>
    <?php if (isset($_GET['deleted'])) { ?><p role="status"><?= escape($entity) ?> deleted.</p><?php } ?>
    <p><a class="btn" href="<?= escape($pageUrl) ?>?action=add">Add <?= escape(strtolower($entity)) ?></a></p>
    <?php if (!$rows) { ?><p>No <?= escape(strtolower($plural)) ?> yet.</p><?php } else { ?>
        <table class="admin-table housing-table">
            <thead><tr><th><?= escape($entity) ?></th><th><?= $table === 'properties' ? 'Address' : 'Property' ?></th>
                <th><?= $table === 'properties' ? 'Status' : 'Unit type' ?></th><th>Actions</th></tr></thead>
            <tbody><?php foreach ($rows as $row) { ?>
                <tr><td><?= escape($row['label']) ?></td><td><?= escape($row['detail']) ?></td><td><?= escape($row['extra']) ?></td>
                    <td><a href="<?= escape($pageUrl) ?>?action=edit&amp;id=<?= (int)$row[$idColumn] ?>">Edit</a>
                        <a href="<?= escape($pageUrl) ?>?action=delete&amp;id=<?= (int)$row[$idColumn] ?>">Delete</a></td></tr>
            <?php } ?></tbody>
        </table>
    <?php } ?>
<?php } elseif ($action === 'delete') { ?>
    <div class="confirm-box">
        <h2>Delete <?= escape($recordLabel) ?>?</h2>
        <p>This cannot be undone.</p>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
            <button class="btn btn-danger"<?= $adminPreview ? ' disabled' : '' ?>>Delete <?= escape(strtolower($entity)) ?></button>
            <a href="<?= escape($pageUrl) ?>">Cancel</a>
        </form>
    </div>
<?php } else { ?>
    <?php if (isset($_GET['saved'])) { ?><p role="status"><?= escape($entity) ?> saved.</p><?php } ?>
    <?php if ($record) { ?>
        <div class="admin-actions">
            <a href="photos.php?<?= escape($idColumn) ?>=<?= (int)$id ?>">Manage photos</a>
            <?php if ($table === 'properties') { ?>
                <a href="units.php?action=add&amp;property_id=<?= (int)$id ?>">Add a unit for this property</a>
            <?php } ?>
        </div>
    <?php } ?>
    <form class="listing-form housing-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
        <?php foreach ($fields as $column => $field) {
            admin_render_housing_field($column, $field, (string)($values[$column] ?? ''));
        } ?>
        <div class="admin-actions">
            <button class="btn"<?= $adminPreview ? ' disabled' : '' ?>><?= $record ? 'Save changes' : escape($title) ?></button>
            <a href="<?= escape($pageUrl) ?>">Back to <?= escape(strtolower($plural)) ?></a>
        </div>
    </form>
<?php } include __DIR__ . '/footer.php'; ?>
