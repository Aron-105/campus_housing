<?php
require __DIR__ . '/bootstrap.php';
$eyebrow = 'Browse listings';
$title = 'Your search starts here.';
$description = 'Full Search Coming Soon.';
$selections = ['Location' => request_text($_GET, 'location') ?: 'Near USC'];
$moveIn = request_text($_GET, 'move_in');
$selections['Move-in'] = preg_match('/\A\d{4}-(0[1-9]|1[0-2])\z/', $moveIn)
    ? DateTime::createFromFormat('!Y-m', $moveIn)->format('F Y') : 'Any time';
$budget = filter_var($_GET['max_rent'] ?? null, FILTER_VALIDATE_INT,
    ['options'=>['min_range'=>1,'max_range'=>100000]]);
$selections['Monthly budget'] = $budget ? 'Up to $' . number_format($budget) : 'Any budget';
$walk = filter_var($_GET['max_walk'] ?? null, FILTER_VALIDATE_INT,
    ['options'=>['min_range'=>1,'max_range'=>180]]);
if ($walk) { $selections['Walk to campus'] = 'Under ' . $walk . ' minutes'; }
$collection = request_text($_GET, 'collection');
$collections = ['shared'=>'Shared apartments', 'private-room'=>'Private rooms'];
if (isset($collections[$collection])) { $selections['Collection'] = $collections[$collection]; }
require __DIR__ . '/placeholder.php';
