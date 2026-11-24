<?php
$units = $db->query('SELECT u.unit_id, u.unit_label, p.name, ut.name AS unit_type FROM units u JOIN properties p ON p.property_id=u.property_id JOIN unit_types ut ON ut.unit_type_id=u.unit_type_id ORDER BY p.name, u.unit_id')->fetch_all(MYSQLI_ASSOC);
$bases = $db->query('SELECT rent_basis_id, name FROM rent_bases ORDER BY name')->fetch_all(MYSQLI_ASSOC);
function field($listing, $key) { return escape((string)($listing[$key] ?? '')); }
?>
<?php if (!empty($error)) { ?><p role="alert"><?= escape($error) ?></p><?php } ?>
<p>Choose an existing property unit. A property must be published for its listings to appear in public search.</p>
<form class="listing-form" method="post">
<input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
<label for="title">Listing title</label><input id="title" name="title" maxlength="255" value="<?= field($listing, 'title') ?>" required>
<label for="unit_id">Property and unit</label><select id="unit_id" name="unit_id" required><option value="">Choose a unit</option><?php foreach ($units as $unit) { ?><option value="<?= (int)$unit['unit_id'] ?>" <?= (string)($listing['unit_id'] ?? '') === (string)$unit['unit_id'] ? 'selected' : '' ?>><?= escape($unit['name'] . ' — ' . $unit['unit_type'] . ($unit['unit_label'] ? ' (' . $unit['unit_label'] . ')' : '') . ' #' . $unit['unit_id']) ?></option><?php } ?></select>
<label for="monthly_rent">Monthly rent ($)</label><input id="monthly_rent" name="monthly_rent" type="number" min="0" max="99999999.99" step="0.01" value="<?= field($listing, 'monthly_rent') ?>" required>
<label for="rent_basis_id">Rent basis</label><select id="rent_basis_id" name="rent_basis_id" required><option value="">Choose rent basis</option><?php foreach ($bases as $basis) { ?><option value="<?= (int)$basis['rent_basis_id'] ?>" <?= (string)($listing['rent_basis_id'] ?? '') === (string)$basis['rent_basis_id'] ? 'selected' : '' ?>><?= escape($basis['name']) ?></option><?php } ?></select>
<label for="status">Status</label><select id="status" name="status"><?php foreach (['pending','active','leased','expired','inactive'] as $status) { ?><option value="<?= $status ?>" <?= ($listing['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php } ?></select>
<label for="available_from">Available from</label><input id="available_from" name="available_from" type="date" value="<?= field($listing, 'available_from') ?>">
<label for="available_until">Available until</label><input id="available_until" name="available_until" type="date" value="<?= field($listing, 'available_until') ?>">
<label for="listing_url">Original listing link</label><input id="listing_url" name="listing_url" type="url" maxlength="500" value="<?= field($listing, 'listing_url') ?>">
<button type="submit" class="btn"<?= $adminPreview ? ' disabled' : '' ?>><?= escape($buttonText) ?></button> <a class="btn btn-outline" href="index.php">Cancel</a></form>
