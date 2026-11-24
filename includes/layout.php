<?php
// Shared header, navigation and footer for HomeSC content pages.
// Usage: set $pageTitle, $pageDescription and $currentPage, then
//   require __DIR__ . '/includes/layout.php'; site_header(); ... site_footer();
// Requires bootstrap.php (for $nonce and $userId) to be loaded first.

// Main navigation. Keys are file names; used to mark the current page.
const SITE_NAV = [
    'index.php' => 'Home',
    'results.php' => 'Browse Listings',
    'students.php' => 'Students',
    'parents.php' => 'Parents',
    'resources.php' => 'Resources',
    'about.php' => 'About',
];

// Footer columns: heading => [link => label].
const SITE_FOOTER = [
    'Explore' => [
        'results.php' => 'Browse listings',
        'search.php' => 'Search',
        'about.php' => 'About HomeSC',
        'login.php' => 'Log in',
    ],
    'Guides' => [
        'students.php' => 'For students',
        'parents.php' => 'For parents',
        'resources.php#neighborhoods' => 'Neighborhoods',
        'resources.php#safety' => 'Safety tips',
    ],
];

function site_header() {
    global $nonce, $userId, $pageTitle, $pageDescription, $currentPage;
    $title = $pageTitle ?? 'HomeSC';
    $current = $currentPage ?? '';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= escape($pageDescription ?? 'Student housing near USC, all in one place.') ?>">
    <title><?= escape($title) ?> — HomeSC</title>
    <link rel="icon" href="assets/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <link rel="stylesheet" href="content.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="wrap header-inner">
        <a class="brand" href="index.php" aria-label="HomeSC home"><img src="assets/logo-header.png" alt="HomeSC" width="110" height="34"></a>
        <nav class="main-nav" id="main-nav" aria-label="Main navigation">
            <?php foreach (SITE_NAV as $file => $label) { ?>
            <a href="<?= escape($file) ?>"<?= $file === $current ? ' aria-current="page"' : '' ?>><?= escape($label) ?></a>
            <?php } ?>
        </nav>
        <a class="button login-button" href="login.php"><?= $userId ? 'Account' : 'Log In' ?></a>
        <button class="menu-button" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-nav"><span></span><span></span><span></span></button>
    </div>
</header>
<main id="main">
<?php
}

function site_footer() {
    global $nonce;
    ?>
</main>
<footer class="site-footer">
    <div class="wrap">
        <div class="footer-top">
            <div class="footer-brand"><a class="brand" href="index.php" aria-label="HomeSC home"><img src="assets/logo-footer.png" width="110" height="34" alt="HomeSC"></a><p>Your place near campus starts here.</p></div>
            <?php foreach (SITE_FOOTER as $heading => $links) { ?>
            <div class="footer-column"><h2><?= escape($heading) ?></h2><ul>
                <?php foreach ($links as $file => $label) { ?><li><a href="<?= escape($file) ?>"><?= escape($label) ?></a></li><?php } ?>
            </ul></div>
            <?php } ?>
            <div class="footer-column"><h2>Ready to find your place?</h2><a class="button footer-cta" href="search.php">Start searching <span aria-hidden="true">→</span></a></div>
        </div>
        <div class="footer-bottom"><p>© <?= date('Y') ?> HomeSC · A student project, not an official USC service.</p><p>Made for students in Los Angeles.</p><p><a href="page.php?section=privacy">Privacy</a> · <a href="page.php?section=terms">Terms</a></p></div>
    </div>
</footer>
<script nonce="<?= escape($nonce) ?>">
    const menuButton = document.querySelector('.menu-button');
    const navigation = document.querySelector('.main-nav');
    function setMenu(open) {
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        navigation.classList.toggle('is-open', open);
    }
    menuButton.addEventListener('click', () => setMenu(menuButton.getAttribute('aria-expanded') !== 'true'));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') { setMenu(false); menuButton.focus(); }
    });
    document.addEventListener('click', event => { if (!event.target.closest('.site-header')) setMenu(false); });
</script>
</body>
</html>
<?php
}

// Page hero used at the top of every content page.
function page_hero($eyebrow, $title, $intro) {
    ?>
    <section class="page-hero">
        <div class="wrap">
            <p class="eyebrow"><?= escape($eyebrow) ?></p>
            <h1><?= $title /* trusted markup written in the page file */ ?></h1>
            <p class="lede"><?= escape($intro) ?></p>
        </div>
    </section>
<?php
}

// FAQ group: $items is a list of [question, answer]. Answers are trusted
// HTML written in the page file (so they can contain links); questions are escaped.
function faq_group($id, $heading, array $items) {
    ?>
    <section class="faq-group" id="<?= escape($id) ?>" aria-labelledby="<?= escape($id) ?>-title">
        <h2 id="<?= escape($id) ?>-title"><?= escape($heading) ?></h2>
        <?php foreach ($items as [$question, $answer]) { ?>
        <details><summary><?= escape($question) ?></summary><div><?= $answer ?></div></details>
        <?php } ?>
    </section>
<?php
}
