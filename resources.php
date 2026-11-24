<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$pageTitle = 'Resources';
$pageDescription = 'Neighborhood guide, renting checklist, safety numbers and USC housing resources for students living off campus.';
$currentPage = 'resources.php';

// Neighborhoods around the University Park Campus. Commute times are approximate.
$areas = [
    ['North University Park', 'Historic Victorian and Craftsman homes just north of campus, many rented to groups of students.', 'About a 5–20 min walk', 'Groups who want a house'],
    ['Figueroa Corridor', 'Newer purpose-built student apartments along Figueroa and Jefferson, often furnished with per-bedroom leases.', 'About a 5–15 min walk', 'Students who want amenities'],
    ['Exposition Park', 'South of campus near the museums, Rose Garden and Coliseum, with Metro E Line stations close by.', 'About a 5–20 min walk', 'Transit riders'],
    ['West Adams', 'Large historic homes and quieter, tree-lined streets west of campus.', 'About a 10–15 min bike ride', 'More space for the money'],
    ['Downtown LA (South Park)', 'High-rise apartments about three miles north, connected to campus by the Metro E Line.', 'About 15–25 min by Metro', 'City living and internships'],
    ['Koreatown', 'Dense and lively, with studios and one-bedrooms that can cost less than near campus.', 'About 25–40 min by bus', 'Solo renters on a budget'],
];

// USC Department of Public Safety numbers (verified November 2026 at dps.usc.edu / safety.usc.edu).
$phones = [
    ['Emergencies anywhere (police, fire, medical)', '911'],
    ['USC Department of Public Safety: emergency', '213-740-4321'],
    ['USC Department of Public Safety: non-emergency', '213-740-6000'],
    ['USC Emergency Information Line', '213-740-9233'],
    ['USC Campus Support & Intervention', '213-740-0411'],
];

site_header();
page_hero('Resources', 'Everything you need<br><em>to rent with confidence.</em>',
    'Neighborhood overviews, a step-by-step renting guide, safety numbers and USC resources, all in one place.');
?>
<section class="section">
    <div class="wrap">
        <ul class="toc" aria-label="On this page">
            <li><a href="#neighborhoods">Neighborhoods</a></li>
            <li><a href="#guide">Renting guide</a></li>
            <li><a href="#safety">Safety</a></li>
            <li><a href="#usc">USC resources</a></li>
        </ul>
        <div class="section-intro" id="neighborhoods">
            <p class="eyebrow">Neighborhood guide</p>
            <h2>Get to know the areas around campus.</h2>
            <p>Where you live shapes your commute, your budget and your whole year. Here’s a quick look at where USC students live.</p>
        </div>
        <div class="card-grid">
            <?php foreach ($areas as [$name, $about, $commute, $bestFor]) { ?>
            <article class="card">
                <h3><?= escape($name) ?></h3>
                <p><?= escape($about) ?></p>
                <dl class="facts compact">
                    <div><dt>To campus</dt><dd><?= escape($commute) ?></dd></div>
                    <div><dt>Best for</dt><dd><?= escape($bestFor) ?></dd></div>
                </dl>
            </article>
            <?php } ?>
        </div>
        <p class="muted small spaced-top">Commute times are rough estimates. Use the walking-time filter in <a class="text-link" href="search.php">search</a> to compare specific homes.</p>
    </div>
</section>

<section class="section alt" id="guide">
    <div class="wrap split top">
        <div>
            <p class="eyebrow">Renting guide</p>
            <h2>From first search to move-in day.</h2>
            <ol class="timeline">
                <li><strong>Early fall</strong><h3>Pick your roommates</h3><p>Agree on budget, cleanliness, guests and schedules before you look at listings.</p></li>
                <li><strong>Fall to winter</strong><h3>Search and tour</h3><p>Popular buildings and houses often open leasing for next year during the fall semester.</p></li>
                <li><strong>Winter to spring</strong><h3>Apply and sign</h3><p>Compare lease terms, apply, and get every promise in writing.</p></li>
                <li><strong>Summer</strong><h3>Get ready to move</h3><p>Set up utilities and internet, buy renter’s insurance, and photograph the unit on move-in day.</p></li>
            </ol>
        </div>
        <div>
            <h3>Build a real budget</h3>
            <p class="muted">Rent is only the start. Add utilities, internet, parking, renter’s insurance and furniture, and check whether rent is per bedroom or for the whole unit.</p>
            <div class="callout gold"><p><strong>Deposit limit:</strong> since July 1, 2024, California’s AB 12 limits most landlords to a security deposit of one month’s rent, furnished or not. Some small landlords may charge up to two months.</p></div>
            <h3>Touring checklist</h3>
            <ul class="checklist">
                <li>Water pressure, hot water, outlets and lights</li>
                <li>Signs of pests, mold or water damage</li>
                <li>Locks on doors, windows and bedrooms</li>
                <li>Smoke and carbon monoxide detectors</li>
                <li>Laundry, trash, parking and package delivery</li>
                <li>The commute at the time you’d actually go to class</li>
            </ul>
            <p class="muted small">Have more questions about leases? See the <a class="text-link" href="students.php#renting">student FAQ</a>.</p>
        </div>
    </div>
</section>

<section class="section" id="safety">
    <div class="wrap split top">
        <div>
            <p class="eyebrow">Safety</p>
            <h2>Save these numbers today.</h2>
            <p class="muted">The USC Department of Public Safety (DPS) is available 24/7. Call DPS first for anything on or near campus; call 911 anywhere else.</p>
            <ul class="phone-list">
                <?php foreach ($phones as [$label, $number]) { ?>
                <li><span><?= escape($label) ?></span><a href="tel:<?= escape(str_replace('-', '', $number)) ?>"><?= escape($number) ?></a></li>
                <?php } ?>
            </ul>
            <h3 class="spaced-top">Getting home at night</h3>
            <p class="muted">The USC Lyft Rides Program offers free shared rides to students within a set zone around campus in the evening. Check USC Transportation for current hours. USC’s LiveSafe app can call DPS with one tap or share your location with a friend while you walk.</p>
        </div>
        <div>
            <h3>Spot a rental scam</h3>
            <div class="callout"><p><strong>Never send money for a home you haven’t seen in person or on a live video tour, and never pay by wire transfer, gift card or cryptocurrency.</strong></p></div>
            <ul class="checklist">
                <li>Rent far below similar homes nearby</li>
                <li>The “landlord” is away and can’t show the unit</li>
                <li>Pressure to pay today to hold the place</li>
                <li>Payment requested before a lease is signed</li>
                <li>A lease that doesn’t name the property owner</li>
                <li>The same photos appear on another listing</li>
            </ul>
        </div>
    </div>
</section>

<section class="section alt" id="usc">
    <div class="wrap">
        <div class="section-intro">
            <p class="eyebrow">USC resources</p>
            <h2>Support from the university.</h2>
        </div>
        <div class="card-grid">
            <article class="card"><h3>Off-Campus Housing 101</h3><p>USC Housing partners with Off-Campus Housing 101 to list rentals from private landlords near the University Park and Health Sciences campuses.</p></article>
            <article class="card"><h3>Student Basic Needs</h3><p>Offers off-campus housing referrals and support for students facing housing insecurity, including help reviewing campus and community resources.</p></article>
            <article class="card"><h3>Campus Support &amp; Intervention</h3><p>Helps students through difficult situations, including financial or personal challenges that affect housing. Call 213-740-0411.</p></article>
        </div>
        <p class="muted small spaced-top">HomeSC is a student project and not an official USC service. This page is general information, not legal advice.</p>
    </div>
</section>

<section class="cta-band">
    <div class="wrap">
        <div><h2>Ready to find your place?</h2><p>Search homes by neighborhood, budget and walking time.</p></div>
        <a class="button large" href="search.php">Start searching →</a>
    </div>
</section>
<?php site_footer(); ?>
