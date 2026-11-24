<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$pageTitle = 'For parents';
$pageDescription = 'Answers for parents and families helping a USC student find off-campus housing: safety, costs, co-signing and leases.';
$currentPage = 'parents.php';

$gettingStarted = [
    ['How can I help my student with the search?', '<p>Help set a realistic total budget, review listings together, and read the lease before anyone signs. Your student can share listing links from HomeSC with you. No account is needed to view them.</p>'],
    ['Can I use HomeSC myself?', '<p>Yes. Anyone can search, view listings and read reviews. Saving homes and writing reviews are limited to verified USC students, so reviews come only from students who have lived or toured there.</p>'],
    ['When should my student start looking?', '<p>Most students begin during the fall semester for the following school year, because popular buildings and houses near campus fill early. There are still homes available in spring and summer, but with fewer choices.</p>'],
    ['Is HomeSC affiliated with USC?', '<p>No. HomeSC is a student-built project and not an official USC service. USC’s own resources, including Off-Campus Housing 101 and Student Basic Needs, are listed on our <a class="text-link" href="resources.php#usc">resources page</a>.</p>'],
];

$money = [
    ['What will off-campus housing cost?', '<p>Costs vary widely by neighborhood, building type and whether rent is per bedroom or per unit. Beyond rent, plan for utilities, internet, parking, renter’s insurance and furniture. Use the rent filter on our <a class="text-link" href="search.php">search page</a> to see current listings in your budget.</p>'],
    ['How much is due before move-in?', '<p>Typically the first month’s rent plus a security deposit, and sometimes application fees.</p>'],
    ['What does it mean to be a guarantor or co-signer?', '<p>Many landlords ask students without income or credit history for a guarantor, often a parent, who agrees to pay rent or damages if the student doesn’t.</p>'],
    ['Will we pay rent during the summer?', '<p>Most leases near campus run for 12 months. If your student will be away, ask whether the landlord allows subletting before signing.</p>'],
];

$safety = [
    ['Is the area around USC safe?', '<p>Like any city neighborhood, it depends on the block and the time of day. The USC Department of Public Safety (DPS) patrols the areas around campus and is available 24/7 at <a class="text-link" href="tel:2137404321">213-740-4321</a>. Visit at night as well as during the day, and see our <a class="text-link" href="resources.php#neighborhoods">neighborhood guide</a>.</p>'],
    ['How will my student get home at night?', '<p>USC offers free shared Lyft rides for students within a set zone around campus during evening and overnight hours. Encourage your student to download USC’s LiveSafe app and register for TrojansAlert emergency texts.</p>'],
    ['What should we look for when touring?', '<p>Working locks on doors and windows, good lighting at entrances and parking, smoke and carbon monoxide detectors, and how maintenance requests are handled. Our <a class="text-link" href="resources.php#guide">housing guide</a> has a full touring checklist.</p>'],
    ['How can we avoid rental scams?', '<p>Be wary of rent far below similar homes, landlords who can’t show the unit, pressure to pay immediately, or requests for payment before a lease is signed. Confirm the person you’re dealing with owns or manages the property.</p>'],
    ['Does my homeowner’s insurance cover my student?', '<p>Sometimes, partially. Check with your insurer. Many families buy a separate renter’s insurance policy for an off-campus apartment; the landlord’s insurance doesn’t cover a tenant’s belongings.</p>'],
];

site_header();
page_hero('For parents & families', 'Helping your student<br><em>find home.</em>',
    'Moving off campus is a big step. Here are answers to the questions parents ask most.');
?>
<section class="section">
    <div class="wrap narrow">
        <ul class="toc" aria-label="On this page">
            <li><a href="#getting-started">Getting started</a></li>
            <li><a href="#money">Costs &amp; leases</a></li>
            <li><a href="#parent-safety">Safety</a></li>
        </ul>
        <?php
        faq_group('getting-started', 'Getting started', $gettingStarted);
        faq_group('money', 'Costs, deposits & leases', $money);
        faq_group('parent-safety', 'Safety & peace of mind', $safety);
        ?>
        <p class="muted small">These answers are general information, not legal or financial advice. Review any lease or guarantor agreement carefully before signing.</p>
    </div>
</section>

<section class="section alt">
    <div class="wrap">
        <div class="section-intro">
            <p class="eyebrow">Before you sign</p>
            <h2>A quick checklist for families.</h2>
        </div>
        <div class="card-grid">
            <article class="card"><h3>Read the lease together</h3><ul><li>Lease length and start date</li><li>Individual or joint responsibility</li><li>All fees beyond rent</li><li>Subletting and early termination rules</li></ul></article>
            <article class="card"><h3>Confirm who you’re paying</h3><ul><li>Landlord or management company name</li><li>A written lease before any payment</li><li>Traceable payment method</li><li>Receipts for every payment</li></ul></article>
            <article class="card"><h3>Plan move-in day</h3><ul><li>Photograph every room on arrival</li><li>Test locks, detectors and appliances</li><li>Set up utilities and internet</li><li>Start renter’s insurance</li></ul></article>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="wrap">
        <div><h2>See what’s available.</h2><p>Browse current homes near campus, no account needed.</p></div>
        <a class="button large" href="results.php">Browse listings →</a>
    </div>
</section>
<?php site_footer(); ?>
