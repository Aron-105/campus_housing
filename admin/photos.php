<?php
require __DIR__ . '/common.php';
require_once __DIR__ . '/housing-fields.php';

$title = 'Photos';
$propertyId = filter_var($_GET['property_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$unitId = filter_var($_GET['unit_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$hasProperty = array_key_exists('property_id', $_GET);
$hasUnit = array_key_exists('unit_id', $_GET);
$context = null;
$error = '';
$fileUrl = '';
$caption = '';

if ($hasUnit && !$hasProperty && $unitId) {
    $context = admin_rows($db, 'SELECT u.unit_id, u.property_id, u.unit_label, p.name
        FROM units u JOIN properties p ON p.property_id = u.property_id WHERE u.unit_id = ?', [$unitId])[0] ?? null;
    $propertyId = (int)($context['property_id'] ?? 0);
} elseif ($hasProperty && !$hasUnit && $propertyId) {
    $context = admin_rows($db, 'SELECT property_id, name FROM properties WHERE property_id = ?', [$propertyId])[0] ?? null;
}
if (($hasUnit || $hasProperty) && !$context) { http_response_code(404); }

if ($context) {
    $contextUrl = $unitId ? 'photos.php?unit_id=' . $unitId : 'photos.php?property_id=' . $propertyId;
    $scope = $unitId ? 'unit_id = ? AND (property_id = ? OR property_id IS NULL)'
        : 'property_id = ? AND unit_id IS NULL';
    $scopeValues = $unitId ? [$unitId, $propertyId] : [$propertyId];
    $scope .= ' AND review_id IS NULL';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $action = admin_text($_POST, 'action');
            if ($action === 'add') {
                $fileUrl = admin_text($_POST, 'file_url');
                $caption = admin_text($_POST, 'caption');
                $source = strlen($fileUrl) <= 500 ? photo_source($fileUrl) : '';
                if (!$source || parse_url($source, PHP_URL_USER) !== null
                    || (!str_starts_with($source, 'https://') &&
                        (!preg_match('/\.(?:avif|gif|jpe?g|png|svg|webp)\z/i', $source)
                            || !is_file(__DIR__ . '/../' . $source)))) {
                    throw new InvalidArgumentException('Enter an HTTPS image URL or the path of an existing local image, up to 500 characters.');
                }
                $storedCaption = admin_housing_value($caption, ['Caption', 'text', 255]);
                admin_transaction($db, function () use ($db, $unitId, $propertyId, $userId, $source, $storedCaption) {
                    if ($unitId && !admin_rows($db, 'SELECT unit_id FROM units
                        WHERE unit_id = ? AND property_id = ? FOR UPDATE', [$unitId, $propertyId])) {
                        throw new InvalidArgumentException('The unit has moved or been deleted. Reload this page.');
                    }
                    admin_query($db, 'INSERT INTO photos (property_id, unit_id, uploaded_by_user_id, file_url, caption)
                        VALUES (?, ?, ?, ?, ?)', [$propertyId, $unitId ?: null, $userId, $source, $storedCaption])->close();
                });
            } elseif (in_array($action, ['caption', 'remove'], true)) {
                $photoId = filter_var($_POST['photo_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                admin_transaction($db, function () use ($db, $scope, $scopeValues, $photoId, $action) {
                    $values = [$photoId, ...$scopeValues];
                    if (!$photoId || !admin_rows($db, "SELECT photo_id FROM photos
                        WHERE photo_id = ? AND $scope FOR UPDATE", $values)) {
                        throw new InvalidArgumentException('This photo is no longer available in this context.');
                    }
                    if ($action === 'remove') {
                        admin_query($db, "DELETE FROM photos WHERE photo_id = ? AND $scope", $values)->close();
                    } else {
                        $newCaption = admin_housing_value(admin_text($_POST, 'caption'), ['Caption', 'text', 255]);
                        admin_query($db, "UPDATE photos SET caption = ? WHERE photo_id = ? AND $scope",
                            [$newCaption, ...$values])->close();
                    }
                });
            } else {
                throw new InvalidArgumentException('Choose a photo action.');
            }
            admin_redirect($contextUrl . ($action === 'remove' ? '&removed=1' : '&saved=1'));
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (mysqli_sql_exception $e) {
            error_log('Photo administration: ' . $e->getMessage());
            $error = 'Could not save this photo change. Check that the property or unit still exists.';
        }
    }
    $photos = admin_rows($db, "SELECT photo_id, file_url, caption FROM photos WHERE $scope ORDER BY photo_id", $scopeValues);
} elseif (!$hasUnit && !$hasProperty) {
    $properties = admin_rows($db, 'SELECT property_id, name, street_address FROM properties ORDER BY name');
    $units = admin_rows($db, 'SELECT u.unit_id, u.unit_label, p.name FROM units u
        JOIN properties p ON p.property_id = u.property_id ORDER BY p.name, u.unit_id');
}

include __DIR__ . '/header.php';
?>
<h1>Photos</h1>
<?php if ($error) { ?><p role="alert"><?= escape($error) ?></p><?php } ?>
<?php if ($context) { ?>
    <h2><?= escape($context['name']) ?><?= $unitId ? ' · ' . escape(($context['unit_label'] ?? '') ?: 'Unit #' . $unitId) : '' ?></h2>
    <div class="admin-actions">
        <a href="<?= $unitId ? 'units.php?action=edit&amp;id=' . $unitId : 'properties.php?action=edit&amp;id=' . $propertyId ?>">Edit <?= $unitId ? 'unit' : 'property' ?></a>
        <a href="photos.php">Choose another property or unit</a>
    </div>
    <?php if (isset($_GET['saved'])) { ?><p role="status">Photo saved.</p><?php } ?>
    <?php if (isset($_GET['removed'])) { ?><p role="status">Photo removed.</p><?php } ?>
    <?php if (!$photos) { ?><p>No photos for this <?= $unitId ? 'unit' : 'property' ?> yet.</p><?php } ?>
    <div class="photo-grid">
        <?php foreach ($photos as $photo) {
            $source = photo_source($photo['file_url']);
            $photoId = (int)$photo['photo_id']; ?>
            <section class="photo-editor">
                <?php if ($source) { ?>
                    <img src="<?= escape(str_starts_with($source, 'https://') ? $source : '../' . $source) ?>"
                        alt="<?= escape($photo['caption'] ?: $context['name']) ?>" loading="lazy">
                <?php } ?>
                <p class="muted photo-path"><?= escape($photo['file_url']) ?></p>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                    <input type="hidden" name="photo_id" value="<?= $photoId ?>">
                    <label for="caption-<?= $photoId ?>">Caption</label>
                    <input type="text" id="caption-<?= $photoId ?>" name="caption" maxlength="255" value="<?= escape($photo['caption']) ?>">
                    <div class="admin-actions">
                        <button class="btn btn-small" name="action" value="caption"<?= $adminPreview ? ' disabled' : '' ?>>Save caption</button>
                        <button class="btn btn-small btn-danger" name="action" value="remove"<?= $adminPreview ? ' disabled' : '' ?>>Remove photo</button>
                    </div>
                </form>
            </section>
        <?php } ?>
    </div>
    <form class="listing-form housing-form" method="post">
        <h2>Add photo</h2>
        <p>Use an existing HTTPS image URL or a local path relative to the site root, such as <code>assets/ellendale.jpg</code>.</p>
        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
        <input type="hidden" name="action" value="add">
        <label for="file_url">Image URL or path</label>
        <input type="text" id="file_url" name="file_url" maxlength="500" value="<?= escape($fileUrl) ?>" required>
        <label for="caption">Caption</label>
        <input type="text" id="caption" name="caption" maxlength="255" value="<?= escape($caption) ?>">
        <div class="admin-actions"><button class="btn"<?= $adminPreview ? ' disabled' : '' ?>>Add photo</button></div>
    </form>
<?php } elseif ($hasProperty || $hasUnit) { ?>
    <p>Property or unit not found.</p><p><a href="photos.php">Choose a property or unit</a></p>
<?php } else { ?>
    <div class="row">
        <section><h2>Property photos</h2><ul>
            <?php foreach ($properties as $property) { ?>
                <li><a href="photos.php?property_id=<?= (int)$property['property_id'] ?>"><?= escape($property['name'] . ' · ' . $property['street_address']) ?></a></li>
            <?php } ?>
        </ul></section>
        <section><h2>Unit photos</h2><ul>
            <?php foreach ($units as $unit) { ?>
                <li><a href="photos.php?unit_id=<?= (int)$unit['unit_id'] ?>"><?= escape($unit['name'] . ' · ' . ($unit['unit_label'] ?: 'Unit #' . $unit['unit_id'])) ?></a></li>
            <?php } ?>
        </ul></section>
    </div>
<?php } include __DIR__ . '/footer.php'; ?>
