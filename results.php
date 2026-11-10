<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/browse_data.php';
$filters = browse_filters($_GET);
$neighborhoods = [];
$unitTypes = [];
$homes = [];
$isResults = true;
$errorMessage = '';
try {
    $db = browse_database();
    $neighborhoods = browse_neighborhoods($db);
    $unitTypes = browse_unit_types($db);
    $homes = browse_listings($db, $filters);
    $db->close();
} catch (Throwable $error) {
    error_log('HomeSC results page: ' . $error->getMessage());
    http_response_code(503);
    $errorMessage = 'Listings are temporarily unavailable. Please try again later.';
}
require __DIR__ . '/browse_template.php';
