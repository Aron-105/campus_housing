<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/browse_data.php';
$propertyId = filter_var($_GET['property_id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1, 'max_range'=>2147483647]]);
$listingId = filter_var($_GET['listing_id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1, 'max_range'=>2147483647]]);
$home = null; $photos = []; $reviews = []; $ownReview = null; $errorMessage = ''; $notice = '';
if (!$propertyId || !$listingId) { http_response_code(404); $errorMessage = 'Listing not found.'; }
else try {
    $db = browse_database();
    $stmt = $db->prepare('SELECT h.*, p.description, p.city, p.state, p.zip_code, l.available_until, l.listing_url, l.security_deposit, l.application_fee, l.utilities_included FROM vw_housing_overview h INNER JOIN properties p ON p.property_id = h.property_id INNER JOIN listings l ON l.listing_id = h.listing_id WHERE h.property_id = ? AND h.listing_id = ? LIMIT 1');
    $stmt->bind_param('ii', $propertyId, $listingId); $stmt->execute(); $rows = browse_statement_rows($stmt); $home = $rows[0] ?? null; $stmt->close();
    if (!$home) { http_response_code(404); $errorMessage = 'Listing not found.'; }
    else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$userId) { header('Location: login.php?reason=sign-in', true, 303); exit; }
            if (!hash_equals($csrfToken, request_text($_POST, 'csrf_token'))) { http_response_code(403); throw new RuntimeException('Invalid session token. Reload the page and try again.'); }
            $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1, 'max_range'=>5]]);
            $body = isset($_POST['body']) && is_string($_POST['body']) ? trim($_POST['body']) : '';
            if (!$rating || $body === '' || strlen($body) > 16000) { $errorMessage = 'Choose a rating and write a review of up to 4,000 characters.'; }
            else {
                // A per-user/property lock prevents concurrent submissions from creating duplicates.
                $lock = 'review_' . $userId . '_' . $propertyId;
                $stmt = $db->prepare('SELECT GET_LOCK(?, 5)'); $stmt->bind_param('s', $lock); $stmt->execute(); $stmt->bind_result($locked); $stmt->fetch(); $stmt->close();
                if ((int)$locked !== 1) { throw new RuntimeException('Could not save the review. Please try again.'); }
                try {
                    $stmt = $db->prepare('SELECT review_id FROM reviews WHERE user_id = ? AND property_id = ? AND source_id IS NULL ORDER BY review_id LIMIT 1');
                    $stmt->bind_param('ii', $userId, $propertyId); $stmt->execute(); $stmt->bind_result($reviewId); $exists = $stmt->fetch(); $stmt->close();
                    if ($exists) {
                        $stmt = $db->prepare("UPDATE reviews SET overall_rating = ?, body = ?, review_date = CURDATE(), status = 'published' WHERE review_id = ? AND user_id = ?");
                        $stmt->bind_param('isii', $rating, $body, $reviewId, $userId);
                    } else {
                        $stmt = $db->prepare("INSERT INTO reviews (property_id, user_id, overall_rating, body, review_date, status) VALUES (?, ?, ?, ?, CURDATE(), 'published')");
                        $stmt->bind_param('iiis', $propertyId, $userId, $rating, $body);
                    }
                    $stmt->execute(); $stmt->close();
                } finally {
                    $stmt = $db->prepare('SELECT RELEASE_LOCK(?)'); $stmt->bind_param('s', $lock); $stmt->execute(); $stmt->close();
                }
                $_SESSION['review_notice'] = 'Your review was saved.';
                header('Location: details.php?property_id=' . $propertyId . '&listing_id=' . $listingId . '#reviews', true, 303); exit;
            }
        }
        $stmt = $db->prepare('SELECT file_url, caption FROM photos WHERE property_id = ? AND (unit_id = ? OR unit_id IS NULL) AND review_id IS NULL ORDER BY photo_id');
        $unitId = (int)$home['unit_id']; $stmt->bind_param('ii', $propertyId, $unitId); $stmt->execute(); $photos = browse_statement_rows($stmt); $stmt->close();
        $stmt = $db->prepare("SELECT r.user_id, r.source_id, r.body, r.overall_rating, r.review_date, r.is_anonymous_publicly, u.username FROM reviews r LEFT JOIN users u ON u.user_id = r.user_id WHERE r.property_id = ? AND r.status = 'published' ORDER BY r.review_date DESC, r.review_id DESC");
        $stmt->bind_param('i', $propertyId); $stmt->execute(); $reviews = browse_statement_rows($stmt); $stmt->close();
        if ($userId) { foreach ($reviews as $review) { if ((int)$review['user_id'] === $userId && $review['source_id'] === null) { $ownReview = $review; break; } } }
        $notice = $_SESSION['review_notice'] ?? ''; unset($_SESSION['review_notice']);
    }
    $db->close();
} catch (Throwable $error) {
    error_log('HomeSC details: ' . $error->getMessage());
    if (http_response_code() !== 403) { http_response_code(503); $errorMessage = 'Listing details are temporarily unavailable. Please try again later.'; }
    else { $errorMessage = 'Invalid session token. Reload the page and try again.'; }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= escape($home['property_name'] ?? 'Listing') ?> — HomeSC</title><link rel="stylesheet" href="assets/fonts/fonts.css"><link rel="stylesheet" href="browse.css"></head>
<body><header class="site-header"><nav class="wrap header-inner"><a class="brand" href="index.php"><img src="assets/logo-header.png" width="110" height="34" alt="HomeSC home"></a><div class="nav-links"><a href="results.php">Browse listings</a><a href="login.php"><?= $userId ? 'Account' : 'Log in' ?></a></div></nav></header>
<main class="wrap detail-page"><a class="back-link" href="results.php">← Browse listings</a>
<?php if ($errorMessage) { ?><p class="status-message" role="alert"><?= escape($errorMessage) ?></p><?php } ?>
<?php if ($home) { ?>
<h1><?= escape($home['property_name']) ?></h1><p><?= escape(implode(', ', array_filter([$home['street_address'], $home['city'], $home['state'], $home['zip_code']]))) ?></p>
<?php if (($home['listing_status'] ?? '') !== 'active' || (!empty($home['available_until']) && $home['available_until'] < date('Y-m-d'))) { ?><p class="availability">Not currently available</p><?php } ?>
<div class="detail-photos"><?php $shown = 0; foreach ($photos as $photo) { $src = photo_source($photo['file_url']); if (!$src) { continue; } $shown++; ?><img src="<?= escape($src) ?>" alt="<?= escape($photo['caption'] ?: $home['property_name']) ?>" loading="lazy"><?php } if (!$shown) { ?><img src="assets/no-photo.svg" alt="No listing photo available"><?php } ?></div>
<div class="detail-columns"><div><section><h2>About this home</h2><?php if (!empty($home['description'])) { ?><p><?= nl2br(escape($home['description'])) ?></p><?php } else { ?><p>No description is available yet.</p><?php } ?>
<p><?= escape(home_specs($home) ?: 'Home details unavailable') ?></p>
<p><?= isset($home['usc_distance_meters']) ? escape(rtrim(rtrim(number_format((float)$home['usc_distance_meters'] / 1609.344, 1), '0'), '.') . ' miles to USC') : 'Distance to USC unavailable' ?></p>
<?php if (!empty($home['amenities'])) { ?><p><strong>Amenities:</strong> <?= escape($home['amenities']) ?></p><?php } ?></section>
<section id="reviews"><h2>Student reviews</h2><?php if ($notice) { ?><p role="status"><?= escape($notice) ?></p><?php } ?>
<?php if (!$reviews) { ?><p>No reviews yet.</p><?php } foreach ($reviews as $review) { ?><article class="review"><p><strong><?= isset($review['overall_rating']) ? escape($review['overall_rating']) . ' / 5' : 'Unrated' ?></strong> · <?= escape($review['review_date'] ?: 'Date unavailable') ?></p><p><?= nl2br(escape($review['body'] ?? '')) ?></p></article><?php } ?>
<?php if ($userId) { ?><form method="post" class="review-form"><h3><?= $ownReview ? 'Edit your review' : 'Write a review' ?></h3><input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>"><label for="rating">Rating</label><select id="rating" name="rating" required><option value="">Choose a rating</option><?php for ($i=1;$i<=5;$i++) { ?><option value="<?= $i ?>" <?= (int)($ownReview['overall_rating'] ?? 0) === $i ? 'selected' : '' ?>><?= $i ?> / 5</option><?php } ?></select><label for="body">Your review</label><textarea id="body" name="body" required maxlength="4000" rows="5"><?= escape($ownReview['body'] ?? '') ?></textarea><button class="search-button" type="submit">Save review</button></form><?php } else { ?><p><a class="back-link" href="login.php?reason=sign-in">Log in with USC Google</a> to write a review.</p><?php } ?></section></div>
<aside><h2>Listing details</h2><?php if (isset($home['monthly_rent'])) { ?><p><strong>$<?= number_format((float)$home['monthly_rent']) ?> / month</strong><?php if (!empty($home['rent_basis'])) { ?> · <?= escape($home['rent_basis']) ?><?php } ?></p><?php } ?>
<?php if (!empty($home['available_from'])) { ?><p>Available from <?= escape($home['available_from']) ?></p><?php } ?><?php if (!empty($home['available_until'])) { ?><p>Available until <?= escape($home['available_until']) ?></p><?php } ?>
<?php $external = photo_source($home['listing_url'] ?? ''); if ($external && filter_var($external, FILTER_VALIDATE_URL)) { ?><p><a class="action-link" href="<?= escape($external) ?>" rel="noopener noreferrer">View original listing</a></p><?php } ?></aside></div>
<?php } ?></main></body></html>
