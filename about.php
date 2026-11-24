<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$pageTitle = 'About';
$pageDescription = 'HomeSC brings off-campus housing near USC into one place, built by students who have been through the search.';
$currentPage = 'about.php';


site_header();
page_hero('About HomeSC', 'Built by students,<br><em>for students.</em>',
        'HomeSC brings off-campus housing near USC into one place, so finding your next home takes an afternoon instead of a semester.');
?>
    <section class="section">
        <div class="wrap split">
            <div>
                <p class="eyebrow">Why we built it</p>
                <h2>The student housing search shouldn’t be this hard.</h2>
                <p>Every year, thousands of Trojans move off campus. The search usually means a dozen browser tabs, group chats full of screenshots, landlord listings with no photos, and no easy way to tell how far a place really is from campus.</p>
                <p>We’ve lived that search ourselves. HomeSC is our answer: one place to browse homes near the University Park Campus, compare them side by side, and read honest reviews from students who actually lived there.</p>
            </div>
            <img src="assets/ellendale.jpg" alt="Bright apartment living room with a gray sofa and an open kitchen" width="900" height="600" loading="lazy">
        </div>
    </section>

    <section class="section alt">
        <div class="wrap">
            <div class="section-intro">
                <p class="eyebrow">What we believe</p>
                <h2>Three ideas guide everything on HomeSC.</h2>
            </div>
            <div class="card-grid">
                <article class="card"><span class="card-number" aria-hidden="true">1</span><h3>Everything in one place</h3><p>Rent, bedrooms, distance to campus, photos and reviews sit together on every listing, so you never have to collect fragmented information.</p></article>
                <article class="card"><span class="card-number" aria-hidden="true">2</span><h3>Students trust students</h3><p>Signed-in users can write one review per property.</p></article>
                <article class="card"><span class="card-number" aria-hidden="true">3</span><h3>Distance is measured honestly</h3><p>We show how far each home is from campus so you can weigh a cheaper place farther out against a shorter walk to class.</p></article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="section-intro">
                <p class="eyebrow">What you can do</p>
                <h2>From first search to signed lease.</h2>
            </div>
            <div class="card-grid four">
                <div class="stat"><strong>Search</strong><span>Filter by neighborhood, budget, bedrooms, move-in month and walking time.</span></div>
                <div class="stat"><strong>Map</strong><span>See every home pinned on a map around campus.</span></div>
                <div class="stat"><strong>Save</strong><span>Sign in to keep a shortlist.</span></div>
                <div class="stat"><strong>Review</strong><span>Share what living there was really like to help the next student.</span></div>
            </div>
            <p class="muted spaced-top">New to renting? Start with our <a class="text-link" href="students.php">student FAQ</a> or the <a class="text-link" href="resources.php">housing resources</a>. Parents and families, see <a class="text-link" href="parents.php">our guide for you</a>.</p>
        </div>
    </section>

    <section class="section alt">
        <div class="wrap">
            <div class="section-intro">
                <p class="eyebrow">The team</p>
                <h2>Who’s behind HomeSC.</h2>
                <p>HomeSC is a student project created by a team of four in ACAD 276 (Dev II) at the USC Iovine and Young Academy. We’re students who have been through the off-campus housing search ourselves, and we built the tool we wished we’d had.</p>
            </div>
        </div>
    </section>

    <section class="cta-band">
        <div class="wrap">
            <div><h2>Ready to find your place?</h2><p>Browse homes near campus, no account needed.</p></div>
            <a class="button large" href="results.php">Browse listings →</a>
        </div>
    </section>
<?php site_footer(); ?>