<?php
require __DIR__ . '/bootstrap.php';
$eyebrow = 'Listing details';
$title = 'A closer look, coming soon.';
$description = 'Photos, availability and details will live here.';
$selections = [];
foreach (['property_id'=>'Property', 'listing_id'=>'Listing'] as $key => $label) {
    $id = filter_var($_GET[$key] ?? null, FILTER_VALIDATE_INT,
        ['options'=>['min_range'=>1,'max_range'=>2147483647]]);
    if ($id) { $selections[$label] = '#' . $id; }
}
require __DIR__ . '/placeholder.php';
