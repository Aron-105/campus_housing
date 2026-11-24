<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$pageTitle = 'For students';
$pageDescription = 'Answers to common student questions about using HomeSC and renting off campus near USC.';
$currentPage = 'students.php';

$usingHomeSC = [
    ['Is HomeSC free?', '<p>Yes. HomeSC is free for students. You can search and view every listing without an account.</p>'],
    ['Do I need an account?', '<p>Only to save homes or write reviews. Sign in with your <strong>@usc.edu</strong> Google account from the <a class="text-link" href="login.php">log in page</a>. Searching, browsing and reading reviews don’t require an account.</p>'],
    ['How do I search for a home?', '<p>Use the search bar on the homepage or the <a class="text-link" href="search.php">full search page</a>. You can filter by neighborhood, maximum rent, number of bedrooms, move-in month and maximum walking time to campus. Results appear as cards and as pins on a map.</p>'],
    ['What does “Not currently available” mean?', '<p>The home has been leased or its listing has expired. We keep it visible so you can still read its reviews and get a sense of the building, but you can’t save it to your favorites.</p>'],
    ['How do I save a home?', '<p>Tap the heart on an active listing. If you aren’t signed in yet, we’ll take you to the log in page first.</p>'],
    ['Who can write reviews?', '<p>Any signed-in USC student. You can write one review per property and edit it any time. Please review only places you’ve lived or toured, and keep it honest and respectful.</p>'],
    ['How do I contact a landlord?', '<p>When we have a link to the original listing, you’ll find it on the listing page. HomeSC doesn’t handle applications, leases or payments; you work directly with the landlord or property manager.</p>'],
];

$renting = [
    ['When should I start looking?', '<p>Earlier than you think. Many popular student buildings and group houses near campus open leasing for the next school year during the fall semester. Starting your search in the fall gives you the most choices; units are still available into spring, with fewer options. See the timeline in our <a class="text-link" href="resources.php#guide">housing guide</a>.</p>'],
    ['What’s the difference between an individual and a joint lease?', '<p>With an <strong>individual lease</strong>, common in student buildings, you’re responsible only for your room and your share of the rent. With a <strong>joint lease</strong>, common in houses and traditional apartments, every roommate is responsible for the full rent, so if one person stops paying, the others can be held responsible.</p>'],
    ['Will I have to pay rent over the summer?', '<p>Usually. Most leases near USC run 12 months. If you’ll be gone, ask whether subletting is allowed <em>before</em> you sign, and get the landlord’s approval in writing before you sublet.</p>'],
    ['How much is a security deposit?', '<p>Since July 1, 2024, California law limits most landlords to a security deposit of no more than one month’s rent, furnished or not. Some small landlords can still charge up to two months. You’ll typically also pay the first month’s rent before you move in.</p>'],
    ['What will I need to apply?', '<p>Applications commonly ask for a photo ID, proof of income or financial aid, and sometimes a credit check. Many students without income or credit history apply with a parent or other guarantor who signs to cover the rent if you can’t.</p>'],
    ['What costs should I budget for besides rent?', '<p>Utilities (electricity, gas, water, trash), internet, parking, renter’s insurance and furniture if the unit is unfurnished. Ask each landlord exactly what rent includes so you can compare homes fairly.</p>'],
];

$roommates = [
    ['How do I pick good roommates?', '<p>Talk before you sign. Compare budgets, sleep schedules, cleanliness, guests, noise and how you’ll split bills. Living with close friends isn’t always easier than living with people whose habits match yours.</p>'],
    ['Should we write a roommate agreement?', '<p>Yes, especially on a joint lease. Write down who pays what and when, how utilities are split, chores, guest rules, and what happens if someone moves out early. Everyone should keep a copy.</p>'],
    ['What if a roommate wants to leave mid-lease?', '<p>Check your lease. On an individual lease they may be able to transfer their room; on a joint lease everyone usually stays responsible until a replacement is approved by the landlord.</p>'],
];

site_header();
page_hero('For students', 'Questions,<br><em>answered.</em>',
    'Everything you need to know about finding a place with HomeSC and renting off campus for the first time.');
?>
<section class="section">
    <div class="wrap narrow">
        <ul class="toc" aria-label="On this page">
            <li><a href="#using-homesc">Using HomeSC</a></li>
            <li><a href="#renting">Renting off campus</a></li>
            <li><a href="#roommates">Roommates</a></li>
        </ul>
        <?php
        faq_group('using-homesc', 'Using HomeSC', $usingHomeSC);
        faq_group('renting', 'Renting off campus', $renting);
        faq_group('roommates', 'Roommates', $roommates);
        ?>
        <div class="callout">
            <p><strong>Something feel off about a listing?</strong> Never send money for a home you haven’t seen. Read our <a class="text-link" href="resources.php#safety">safety tips</a> to learn how to spot a rental scam.</p>
        </div>
        <p class="muted small">These answers are general information, not legal advice. For questions about a specific lease or dispute, contact a tenant rights organization or an attorney.</p>
    </div>
</section>

<section class="cta-band">
    <div class="wrap">
        <div><h2>Ready to start looking?</h2><p>Your first search takes less than a minute.</p></div>
        <a class="button large" href="search.php">Start searching →</a>
    </div>
</section>
<?php site_footer(); ?>
