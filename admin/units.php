<?php
require __DIR__ . '/common.php';

$entity = 'Unit';
$table = 'units';
$idColumn = 'unit_id';
$listSql = "SELECT u.unit_id, COALESCE(NULLIF(u.unit_label, ''), CONCAT('Unit #', u.unit_id)) AS label,
    p.name AS detail, t.name AS extra FROM units u JOIN properties p ON p.property_id = u.property_id
    JOIN unit_types t ON t.unit_type_id = u.unit_type_id ORDER BY p.name, u.unit_label, u.unit_id";
$fields = [
    'property_id' => ['Property', 'lookup', ['properties', 'property_id', 'name'], true],
    'unit_label' => ['Unit label or number', 'text', 50],
    'unit_type_id' => ['Unit type', 'lookup', ['unit_types', 'unit_type_id', 'name'], true],
    'bedrooms' => ['Bedrooms', 'decimal', 99.9, false, 0, 1],
    'bathrooms' => ['Bathrooms', 'decimal', 99.9, false, 0, 1],
    'square_feet' => ['Square feet', 'integer', 2147483647],
    'floor_number' => ['Floor number', 'integer', 2147483647, false, -2147483648],
    'max_occupancy' => ['Maximum occupancy', 'integer', 2147483647],
    'furnished' => ['Furnished', 'select', [0 => 'No', 1 => 'Yes']],
];
$defaults = ['property_id' => filter_var($_GET['property_id'] ?? null, FILTER_VALIDATE_INT)];
$blockedDelete = 'This unit still has related records such as listings, photos, reviews, or amenities. Remove eligible dependent records before deleting it.';
require __DIR__ . '/housing-crud.php';
