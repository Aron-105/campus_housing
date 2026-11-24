<?php
require __DIR__ . '/common.php';

$entity = 'Property';
$table = 'properties';
$idColumn = 'property_id';
$listSql = 'SELECT property_id, name AS label, street_address AS detail, status AS extra
    FROM properties ORDER BY name, property_id';
$fields = [
    'name' => ['Name', 'text', 255, true],
    'street_address' => ['Street address', 'text', 255, true],
    'city' => ['City', 'text', 100],
    'state' => ['State', 'state', 2],
    'zip_code' => ['ZIP code', 'zip', 10],
    'latitude' => ['Latitude', 'decimal', 90, false, -90, 7],
    'longitude' => ['Longitude', 'decimal', 180, false, -180, 7],
    'description' => ['Description', 'textarea', 65535],
    'status' => ['Publication status', 'select', ['pending' => 'Pending', 'published' => 'Published'], true],
    'property_type_id' => ['Property type', 'lookup', ['property_types', 'property_type_id', 'name'], true],
    'affiliation_type_id' => ['USC affiliation', 'lookup', ['affiliation_types', 'affiliation_type_id', 'name'], true],
    'neighborhood_id' => ['Neighborhood', 'lookup', ['neighborhoods', 'neighborhood_id', 'name']],
    'manager_id' => ['Property manager', 'lookup', ['property_managers', 'manager_id', 'name']],
    'year_built' => ['Year built', 'integer', 32767],
    'total_units' => ['Total units', 'integer', 2147483647],
    'usc_walk_minutes' => ['Walk to USC (minutes)', 'integer', 2147483647],
    'usc_distance_meters' => ['Distance to USC (meters)', 'integer', 2147483647],
    'nearest_shuttle_stop' => ['Nearest shuttle stop', 'text', 150],
    'shuttle_walk_minutes' => ['Walk to shuttle (minutes)', 'integer', 2147483647],
    'inside_dps_area' => ['Inside DPS area', 'select', [0 => 'No', 1 => 'Yes']],
    'inside_safety_service_area' => ['Inside safety service area', 'select', [0 => 'No', 1 => 'Yes']],
];
$defaults = ['city' => 'Los Angeles', 'state' => 'CA', 'status' => 'pending'];
$blockedDelete = 'This property still has related records such as units, listings, photos, reviews, or saved properties. Remove eligible dependent records before deleting it.';
require __DIR__ . '/housing-crud.php';
