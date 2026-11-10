<?php
require __DIR__ . '/bootstrap.php';
$eyebrow = 'Log in';
$title = 'Keep your favorites close.';
$description = 'Account sign-in is coming soon.';
$reason = request_text($_GET, 'reason');
if ($reason === 'sign-in') {
    $message = 'Please log in to save this home.';
}

require __DIR__ . '/placeholder.php';
