<?php
function admin_save_amenities(mysqli $db, array $input, int $unitId): void {
    $formUnit = admin_text($input, 'amenities_unit_id');
    if ($formUnit === '') { return; }
    if ($formUnit !== (string)$unitId) {
        throw new InvalidArgumentException('Select the unit again before saving amenities.');
    }
    $baseline = json_decode(admin_text($input, 'amenities_baseline'), true, 4);
    $chosen = $input['amenity_ids'] ?? [];
    if (!is_array($baseline) || !is_array($chosen)) {
        throw new InvalidArgumentException('Choose amenities from the available options.');
    }

    $options = array_column(admin_rows($db, 'SELECT amenity_id FROM amenities'), 'amenity_id');
    $options = array_map('strval', $options);
    if (count($chosen) > count($options) || count($baseline) > count($options)) {
        throw new InvalidArgumentException('Choose amenities from the available options.');
    }
    foreach ([...$chosen, ...$baseline] as $id) {
        if (!in_array($id, $options, true)) {
            throw new InvalidArgumentException('Choose amenities from the available options.');
        }
    }
    $added = array_diff($chosen, $baseline);
    $removed = array_diff($baseline, $chosen);
    if (!$added && !$removed) { return; }

    $existing = admin_rows($db, 'SELECT amenity_id, property_id, is_available
        FROM housing_amenities WHERE unit_id = ? FOR UPDATE', [$unitId]);
    foreach ($existing as $row) {
        $id = (string)$row['amenity_id'];
        if ($row['property_id'] !== null) {
            // A shared association must retain its property and availability.
            continue;
        }
        if ((int)$row['is_available'] === 1 && in_array($id, $removed, true)) {
            admin_query($db, 'DELETE FROM housing_amenities WHERE unit_id = ? AND amenity_id = ?
                AND property_id IS NULL', [$unitId, $id])->close();
        } elseif ((int)$row['is_available'] !== 1 && in_array($id, $added, true)) {
            admin_query($db, 'UPDATE housing_amenities SET is_available = 1, last_verified_at = NULL
                WHERE unit_id = ? AND amenity_id = ? AND property_id IS NULL', [$unitId, $id])->close();
        }
    }
    $existingIds = array_map('strval', array_column($existing, 'amenity_id'));
    foreach (array_diff(array_unique($added), $existingIds) as $id) {
        admin_query($db, 'INSERT INTO housing_amenities (unit_id, amenity_id) VALUES (?, ?)',
            [$unitId, $id])->close();
    }
}
