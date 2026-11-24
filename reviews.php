<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$pageTitle = 'Reviews';
$pageDescription = 'Reviews of homes near USC.';
$currentPage = 'reviews.php';

// PLACEHOLDER: sample reviews to show the layout. In the final build these
// will come from the reviews table in the HomeSC database.
$reviews = [
    [5, 'Sample 4-bedroom house', 'North University Park', 'Five-minute walk to class and plenty of space for four of us. The landlord fixed our water heater within a day.', 'Verified USC student, senior', 'Oct 2026'],
    [4, 'Sample studio apartment', 'Figueroa Corridor', 'The gym and study rooms are great and it’s fully furnished. Rent is on the higher side and the fees add up.', 'Verified USC student, junior', 'Sep 2026'],
    [4, 'Sample 2-bedroom apartment', 'Exposition Park', 'Right by the Metro, and the park is perfect for morning runs. Game days get loud and parking is tough.', 'Verified USC student, graduate student', 'Aug 2026'],
    [3, 'Sample 3-bedroom house', 'West Adams', 'Big rooms and a quiet street for the price. I bike to campus, but I wouldn’t want to walk home late.', 'Verified USC student, senior', 'Jun 2026'],
    [5, 'Sample 1-bedroom apartment', 'Downtown LA (South Park)', 'I love living downtown and the E Line gets me to campus easily. Worth it if you have an internship nearby.', 'Verified USC student, graduate student', 'May 2026'],
    [4, 'Sample private room', 'Koreatown', 'Affordable and the food nearby is amazing. The bus ride takes a while, so plan ahead for early classes.', 'Verified USC student, sophomore', 'Apr 2026'],
];

site_header();
?>
<div class="notice-banner" role="note">
    <div class="wrap"><p><strong>Placeholder content.</strong> The reviews below are sample cards to show the layout and will be replaced with real student reviews from the HomeSC database in the final build.</p></div>
</div>
<?php
page_hero('Student reviews', 'Hear it from<br><em>the people who lived there.</em>',
    'Read about homes near campus before you sign.');
?>
<section class="section">
    <div class="wrap">
        <div class="card-grid">
            <?php foreach ($reviews as [$rating, $property, $area, $text, $reviewer, $date]) { ?>
            <article class="card review-card">
                <p class="stars" aria-label="<?= $rating ?> out of 5 stars"><?= str_repeat('★', $rating) ?><span><?= str_repeat('★', 5 - $rating) ?></span></p>
                <h3><?= escape($property) ?></h3>
                <p class="review-area"><?= escape($area) ?></p>
                <blockquote><?= escape($text) ?></blockquote>
                <p class="review-meta"><?= escape($reviewer) ?> · <?= escape($date) ?></p>
            </article>
            <?php } ?>
        </div>
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
