<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/browse_data.php';
require __DIR__ . '/includes/layout.php';
$pageTitle = 'Reviews';
$pageDescription = 'Reviews of homes near USC.';
$currentPage = 'reviews.php';

$reviews = [];
$errorMessage = '';
try {
    $db = browse_database();
    $reviews = $db->query("SELECT r.overall_rating, r.body, r.review_date,
            h.property_id, h.listing_id, h.property_name, h.neighborhood
        FROM reviews r
        JOIN vw_housing_overview h ON h.property_id = r.property_id
            AND h.listing_id = (
                SELECT MAX(public_home.listing_id)
                FROM vw_housing_overview public_home
                WHERE public_home.property_id = r.property_id
            )
        WHERE r.status = 'published'
        ORDER BY r.review_date DESC, r.review_id DESC")->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    error_log('Reviews page: ' . $e->getMessage());
    http_response_code(503);
    $errorMessage = 'Reviews are temporarily unavailable. Please try again later.';
}

site_header();
page_hero('Student reviews', 'Hear it from<br><em>the people who lived there.</em>',
    'Read about homes near campus before you sign.');
?>
<section class="section">
    <div class="wrap">
        <?php if ($errorMessage) { ?>
        <p role="status"><?= escape($errorMessage) ?></p>
        <?php } elseif (!$reviews) { ?>
        <p>No reviews have been published yet.</p>
        <?php } else { ?>
        <div class="card-grid">
            <?php foreach ($reviews as $review) {
                $rating = $review['overall_rating'];
                $wholeStars = filter_var($rating, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
                $listingUrl = 'details.php?property_id=' . (int)$review['property_id']
                    . '&listing_id=' . (int)$review['listing_id'] . '#reviews';
            ?>
            <article class="card review-card">
                <?php if ($wholeStars !== false) { ?>
                <p class="stars" aria-label="<?= $wholeStars ?> out of 5 stars"><?= str_repeat('★', $wholeStars) ?><span><?= str_repeat('★', 5 - $wholeStars) ?></span></p>
                <?php } else { ?>
                <p class="stars"><?= isset($rating) ? escape($rating) . ' / 5' : 'Unrated' ?></p>
                <?php } ?>
                <h3><a href="<?= escape($listingUrl) ?>"><?= escape($review['property_name'] ?: 'Home near USC') ?></a></h3>
                <?php if (!empty($review['neighborhood'])) { ?>
                <p class="review-area"><?= escape($review['neighborhood']) ?></p>
                <?php } ?>
                <?php if (trim($review['body'] ?? '') !== '') { ?>
                <blockquote><?= nl2br(escape($review['body'])) ?></blockquote>
                <?php } ?>
                <p class="review-meta"><?= escape($review['review_date'] ?: 'Date unavailable') ?></p>
            </article>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</section>

<section class="section alt">
    <div class="wrap split">
        <div>
            <h2>Lived somewhere near campus?</h2>
            <p class="muted">Help the next Trojan find home. Sign in with your HomeSC account and leave a review on any listing’s page.</p>
        </div>
        <div><a class="button large secondary" href="results.php">Browse listings to review →</a></div>
    </div>
</section>

<section class="cta-band">
    <div class="wrap">
        <div><h2>Ready to find your place?</h2><p>Search homes by neighborhood, budget and walking time.</p></div>
        <a class="button large" href="search.php">Start searching →</a>
    </div>
</section>
<?php site_footer(); ?>
