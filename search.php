<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/browse_data.php';
$filters = browse_filters($_GET);
$neighborhoods = [];
$unitTypes = [];
$homes = [];
$isResults = false;
$errorMessage = '';
try {
    $db = browse_database();
    $neighborhoods = browse_neighborhoods($db);
    $unitTypes = browse_unit_types($db);
    $db->close();
} catch (Throwable $error) {
    error_log('HomeSC search page: ' . $error->getMessage());
    $errorMessage = 'Search options are temporarily unavailable. Please try again later.';
}
require __DIR__ . '/browse_template.php';
