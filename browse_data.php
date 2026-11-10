<?php
require_once __DIR__ . '/helpers.php';

function browse_filters(array $input) {
    $month = request_text($input, 'move_in');
    if (!preg_match('/\A\d{4}-(0[1-9]|1[0-2])\z/', $month)) { $month = ''; }
    return [
        'location' => request_text($input, 'location'),
        'move_in' => $month,
        'max_rent' => filter_var($input['max_rent'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 100000]]) ?: null,
        'max_walk' => filter_var($input['max_walk'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 180]]) ?: null,
        'bedrooms' => filter_var($input['bedrooms'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 10]]) ?: null,
        'unit_type' => request_text($input, 'unit_type'),
    ];
}

function browse_database() {
    $config = require __DIR__ . '/config.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = mysqli_init();
    $db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 4);
    $db->real_connect($config['host'], $config['user'], $config['password'],
        $config['database'], (int) $config['port']);
    $db->set_charset('utf8mb4');
    return $db;
}

function browse_neighborhoods(mysqli $db) {
    $result = $db->query("SELECT DISTINCT h.neighborhood
        FROM vw_housing_overview h
        INNER JOIN listings l ON l.listing_id = h.listing_id
        WHERE h.neighborhood IS NOT NULL AND h.neighborhood <> ''
        ORDER BY h.neighborhood");
    return array_column($result->fetch_all(MYSQLI_ASSOC), 'neighborhood');
}

function browse_unit_types(mysqli $db) {
    $result = $db->query("SELECT DISTINCT unit_type FROM vw_housing_overview
        WHERE unit_type IS NOT NULL AND unit_type <> '' ORDER BY unit_type");
    return array_column($result->fetch_all(MYSQLI_ASSOC), 'unit_type');
}

function browse_listings(mysqli $db, array $filters) {
    $where = [];
    $params = [];

    if ($filters['location'] !== '') {
        $where[] = 'h.neighborhood = ?';
        $params[] = $filters['location'];
    }
    if ($filters['move_in'] !== '') {
        $start = $filters['move_in'] . '-01';
        $end = date('Y-m-t', strtotime($start));
        $where[] = '(l.available_until IS NULL OR l.available_until >= ?)';
        $params[] = $start;
        $where[] = '(l.available_from IS NULL OR l.available_from <= ?)';
        $params[] = $end;
    }
    if ($filters['max_rent'] !== null) {
        $where[] = 'h.monthly_rent <= ?';
        $params[] = $filters['max_rent'];
    }
    if ($filters['max_walk'] !== null) {
        // Approximate walking time at 80 meters per minute.
        $where[] = 'h.usc_distance_meters <= ?';
        $params[] = $filters['max_walk'] * 80;
    }
    if ($filters['bedrooms'] !== null) {
        $where[] = 'h.bedrooms >= ?';
        $params[] = $filters['bedrooms'];
    }
    if ($filters['unit_type'] !== '') {
        $where[] = 'h.unit_type = ?';
        $params[] = $filters['unit_type'];
    }

    $sql = "SELECT h.*, l.created_at AS listing_created_at,
        l.available_until AS listing_available_until,
        (SELECT ph.file_url FROM photos ph
         WHERE ph.property_id = h.property_id
           AND (ph.unit_id = h.unit_id OR ph.unit_id IS NULL)
           AND ph.review_id IS NULL
         ORDER BY (ph.unit_id = h.unit_id) DESC, ph.photo_id ASC
         LIMIT 1) AS photo_url
        FROM vw_housing_overview h
        INNER JOIN listings l ON l.listing_id = h.listing_id " .
        ($where ? ' WHERE ' . implode(' AND ', $where) : '') .
        ' ORDER BY l.created_at DESC, h.listing_id DESC';
    $statement = $db->prepare($sql);
    $statement->execute($params);
    $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    $statement->close();
    return $rows;
}
