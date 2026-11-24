<?php
if (!isset($selectedUnit)) { http_response_code(404); return; }
$amenities = admin_rows($db, 'SELECT amenity_id, name FROM amenities ORDER BY name');
$byUnit = [];
foreach (admin_rows($db, 'SELECT unit_id, amenity_id, property_id, is_available
    FROM housing_amenities WHERE unit_id IS NOT NULL') as $row) {
    $byUnit[$row['unit_id']][$row['amenity_id']] = [
        'available' => (int)$row['is_available'] === 1,
        'shared' => $row['property_id'] !== null,
    ];
}
$submitted = ($listing['amenities_unit_id'] ?? null) === $selectedUnit
    && is_array($listing['amenity_ids'] ?? []) ? ($listing['amenity_ids'] ?? []) : null;
?>
<fieldset id="amenities" class="amenity-fields" disabled
    data-units="<?= escape(json_encode((object)$byUnit, JSON_THROW_ON_ERROR)) ?>"
    data-submitted="<?= escape(json_encode($submitted === null ? null : array_values(array_filter($submitted, 'is_string')), JSON_THROW_ON_ERROR)) ?>">
    <legend>Unit amenities</legend>
    <p class="muted">Changes apply to every listing for the selected unit. Shared property amenities are read-only here.</p>
    <p id="amenities-prompt">Choose a unit to edit its amenities.</p>
    <noscript><p>Enable JavaScript to edit amenities. You can still save listing changes.</p></noscript>
    <input type="hidden" name="amenities_unit_id" id="amenities-unit-id">
    <input type="hidden" name="amenities_baseline" id="amenities-baseline">
    <div class="amenity-options" hidden>
        <?php foreach ($amenities as $amenity) { ?>
            <label class="checkbox"><input type="checkbox" name="amenity_ids[]" value="<?= (int)$amenity['amenity_id'] ?>"> <?= escape($amenity['name']) ?></label>
        <?php } ?>
    </div>
</fieldset>
<script src="amenity-fields.js" defer></script>
