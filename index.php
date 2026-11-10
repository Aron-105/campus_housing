<?php
require __DIR__ . '/bootstrap.php';
$config = require __DIR__ . '/config.php';

$homes = [];
$savedIds = [];
$errorMessage = '';

// Visitors need an account before saving a home.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$userId) {
    header('Location: login.php?reason=sign-in', true, 303);
    exit;
}

// $step identifies which operation failed if a database error occurs.
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $step = 'Validate favorite form';
        $propertyId = filter_var($_POST['property_id'] ?? null, FILTER_VALIDATE_INT,
            ['options'=>['min_range'=>1, 'max_range'=>2147483647]]);
        $action = request_text($_POST, 'action');
        $token = request_text($_POST, 'csrf_token');
        if (!$propertyId) { throw new RuntimeException('property_id must be a positive integer.'); }
        if (!in_array($action, ['save', 'unsave'], true)) {
            throw new RuntimeException('action must be save or unsave.');
        }
        if (!hash_equals($csrfToken, $token)) {
            throw new RuntimeException('The form token does not match this session. Reload the page.');
        }
    }

    $step = 'Check config.php';
    $missing = [];
    foreach (['host', 'user', 'password', 'database'] as $setting) {
        if ($config[$setting] === '') { $missing[] = $setting; }
    }
    if ($missing) { throw new RuntimeException('Missing database settings: ' . implode(', ', $missing)); }

    $step = 'Connect to MySQL';
    if (!extension_loaded('mysqli')) { throw new RuntimeException('Enable the mysqli PHP extension.'); }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysql = mysqli_init();
    $mysql->options(MYSQLI_OPT_CONNECT_TIMEOUT, 4);
    $mysql->real_connect($config['host'], $config['user'], $config['password'],
                        $config['database'], (int) $config['port']);
    $mysql->set_charset('utf8mb4');

    // Save or remove a favorite using the signed-in user's session ID.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $step = 'Check property in listings';
        $statement = $mysql->prepare("SELECT property_id FROM listings
            WHERE property_id = ? AND status = 'active'
            AND (available_until IS NULL OR available_until >= CURDATE()) LIMIT 1");
        $statement->bind_param('i', $propertyId);
        $statement->execute();
        $statement->store_result();
        $exists = $statement->num_rows > 0;
        $statement->close();
        if (!$exists) { throw new RuntimeException('No active listing for property_id ' . $propertyId); }

        $step = 'Update saved_properties';
        $sql = $action === 'save'
            ? 'INSERT INTO saved_properties (user_id, property_id, saved_at) VALUES (?, ?, NOW())
               ON DUPLICATE KEY UPDATE saved_at = saved_at'
            : 'DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?';
        $statement = $mysql->prepare($sql);
        $statement->bind_param('ii', $userId, $propertyId);
        $statement->execute();
        $statement->close();
        $_SESSION['favorite_notice'] = $action === 'save' ? 'Home saved to your favorites.' : 'Home removed from your favorites.';
        header('Location: index.php#featured', true, 303);
        exit;
    }

    // Load real listings. The view provides names, prices, rooms and review scores.
    $step = 'Load listings from vw_housing_overview, listings and photos';
    $sql = "SELECT h.*, l.created_at AS listing_created_at,
               (SELECT ph.file_url FROM photos ph
                WHERE ph.property_id = h.property_id
                  AND (ph.unit_id = h.unit_id OR ph.unit_id IS NULL)
                  AND ph.review_id IS NULL
                ORDER BY (ph.unit_id = h.unit_id) DESC, ph.photo_id ASC
                LIMIT 1) AS photo_url
            FROM vw_housing_overview h
            INNER JOIN listings l ON l.listing_id = h.listing_id
            WHERE h.listing_status = 'active'
              AND (l.available_until IS NULL OR l.available_until >= CURDATE())";
    $result = $mysql->query($sql);
    while ($row = $result->fetch_assoc()) { $homes[] = $row; }

    if ($userId) {
        $step = 'Read saved_properties';
        $statement = $mysql->prepare('SELECT property_id FROM saved_properties WHERE user_id = ?');
        $statement->bind_param('i', $userId);
        $statement->execute();
        $statement->bind_result($savedId);
        while ($statement->fetch()) { $savedIds[(int) $savedId] = true; }
        $statement->close();
    }
} catch (RuntimeException $error) {
    // Show the actual error during development; leave missing listing data empty.
    $errorMessage = $step . ' — ' . basename($error->getFile()) . ':' . $error->getLine()
        . ' — ' . $error->getMessage();
}
$featured = featured_homes($homes);
$neighborhoods = array_values(array_unique(array_filter(array_column($homes, 'neighborhood'))));
sort($neighborhoods);
$notice = $_SESSION['favorite_notice'] ?? '';
unset($_SESSION['favorite_notice']);

function icon($name) {
    $paths = [
        'search'=>'<circle cx="10.8" cy="10.8" r="6.3"/><path d="m15.5 15.5 4.2 4.2"/>',
        'pin'=>'<path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'arrow'=>'<path d="M5 12h14m-5-5 5 5-5 5"/>',
        'heart'=>'<path d="M20.8 4.8a5.5 5.5 0 0 0-7.8 0L12 5.9l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.4a5.5 5.5 0 0 0 0-7.8Z"/>',
        'people'=>'<circle cx="9" cy="7" r="3"/><path d="M3 20v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"/>',
        'key'=>'<circle cx="7.5" cy="15.5" r="4"/><path d="m10.5 12.5 9-9m-4 4 3 3m-6 0 3 3"/>',
        'wallet'=>'<path d="M20 7H6a3 3 0 0 1 0-6h12v6M4 4v15a2 2 0 0 0 2 2h14V7m0 5h-6v5h6"/><path d="M16 14.5h.01"/>',
    ];
    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Find your home away from home. Explore student housing near USC with HomeSC.">
    <title>HomeSC — Find your home away from home</title>
    <link rel="icon" href="assets/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <style nonce="<?= escape($nonce) ?>">
        :root { --ink:#1f292a; --muted:#69716f; --red:#a00000; --gold:#ffcc00; --cream:#e8e4da; --tile:#f0ede5; --taupe:#b09a89; --line:#b9b2a7; }
        * { box-sizing:border-box; }
        html { scroll-behavior:smooth; scroll-padding-top:24px; }
        body { margin:0; color:var(--ink); background:white; font-family:'DM Sans',Arial,sans-serif; -webkit-font-smoothing:antialiased; }
        a { color:inherit; text-decoration:none; }
        button, input, select { font:inherit; }
        button, select { cursor:pointer; }
        button { color:inherit; }
        img { display:block; max-width:100%; }
        a, button, select { -webkit-tap-highlight-color:transparent; }
        :focus-visible { outline:3px solid var(--red); outline-offset:5px; }
        .wrap { width:calc(100% - 192px); max-width:1408px; margin-inline:auto; }
        .icon { width:20px; height:20px; flex:none; vertical-align:middle; }
        .skip-link { position:absolute; top:-100px; left:20px; z-index:20; background:white; padding:14px; }
        .skip-link:focus { top:12px; }
        .site-header { height:88px; border-top:5px solid var(--red); border-bottom:1px solid var(--red); background:#fff; }
        .header-inner { height:100%; display:flex; align-items:center; justify-content:space-between; gap:32px; }
        .brand { display:block; flex:none; }
        .brand img { width:110px; height:34px; }
        .main-nav { display:flex; align-items:center; gap:40px; margin-left:12px; }
        .main-nav a { font-weight:700; font-size:15px; letter-spacing:2px; color:#626b69; white-space:nowrap; padding-block:14px; }
        .main-nav a:hover { color:var(--red); }
        .button { display:inline-flex; justify-content:center; align-items:center; gap:10px; border:0; border-radius:0; background:var(--red); color:var(--gold); font:700 16px 'Libre Baskerville',Georgia,serif; transition:background .18s; }
        .button:hover { background:#360008; }
        .button:focus-visible { outline-color:var(--ink); }
        .login-button { min-width:110px; min-height:43px; }
        .menu-button { display:none; width:44px; height:44px; border:1px solid var(--line); background:white; padding:10px; }
        .menu-button span { display:block; height:2px; background:var(--ink); margin-block:5px; }
        .hero { height:612px; position:relative; background:var(--cream); }
        .hero-image { position:absolute; left:47.375%; top:0; width:52.625%; height:100%; object-fit:cover; object-position:center; }
        .hero-content { position:relative; padding-top:185px; }
        h1 { margin:0; font:700 80px/1.04 'Libre Baskerville',Georgia,serif; letter-spacing:-4.5px; }
        h1 span, h1 em { display:block; width:max-content; max-width:100%; }
        h1 em { font-weight:400; color:var(--red); letter-spacing:-4.8px; }
        .home-search { display:grid; grid-template-columns:250px 228px 226px 196px; width:900px; max-width:100%; min-height:74px; margin:76px 0 0 16px; background:white; box-shadow:0 8px 22px #3a2d201f; }
        .search-more { display:inline-block; margin:14px 0 0 16px; color:var(--red); font-size:14px; font-weight:700; text-decoration:underline; text-underline-offset:3px; }
        .search-field { position:relative; padding:15px 22px 13px; min-width:0; }
        .search-field + .search-field::before { content:''; position:absolute; top:15px; bottom:15px; left:0; width:1px; background:#e1e1e1; }
        .search-field label { display:block; color:var(--muted); font-size:12px; font-weight:900; letter-spacing:1.1px; text-transform:uppercase; margin-bottom:5px; }
        .input-line { display:flex; align-items:center; gap:7px; }
        .input-line .icon { width:16px; height:16px; }
        .search-field select { width:100%; min-width:0; height:23px; padding:0; border:0; border-radius:0; background:white; color:var(--ink); font-weight:700; font-size:14px; appearance:none; }
        .search-field:focus-within { background:#f8f7f3; }
        .search-field:focus-within select { background:#f8f7f3; }
        .search-submit { min-height:74px; }
        .search-submit .icon { width:19px; height:19px; }
        .featured { padding:110px 0 116px; }
        .section-top { display:flex; align-items:baseline; justify-content:space-between; gap:20px; margin-bottom:36px; }
        h2 { font:400 52px/1.13 'Libre Baskerville',Georgia,serif; letter-spacing:-2px; margin:0; }
        .view-all { display:inline-flex; align-items:center; gap:12px; font:700 16px 'Libre Baskerville',Georgia,serif; white-space:nowrap; }
        .view-all:hover { color:var(--red); }
        .view-all .icon { width:17px; height:17px; }
        .home-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:24px; }
        .home-card { min-width:0; }
        .card-photo { position:relative; aspect-ratio:1.82; background:var(--cream); }
        .card-photo > a { display:block; height:100%; overflow:hidden; }
        .card-photo img { width:100%; height:100%; object-fit:cover; transition:transform .3s ease; }
        .card-photo > a:hover img { transform:scale(1.025); }
        .badge { position:absolute; left:16px; top:16px; padding:8px 11px; background:var(--red); color:white; text-transform:uppercase; font-size:12px; font-weight:900; line-height:1; letter-spacing:1px; pointer-events:none; }
        .favorite-form { position:absolute; right:15px; top:15px; margin:0; }
        .favorite { width:40px; height:40px; padding:10px; border:0; border-radius:50%; background:white; color:#435052; display:grid; place-items:center; box-shadow:0 1px 5px #0000000a; }
        .favorite .icon { width:20px; height:20px; stroke-width:1.6; }
        .favorite:hover { color:var(--red); background:#fff7f0; }
        .favorite[aria-pressed="true"] { background:var(--red); color:white; }
        .favorite[aria-pressed="true"] .icon { fill:currentColor; }
        .favorite:disabled { cursor:wait; opacity:.6; }
        .card-heading { display:flex; align-items:baseline; justify-content:space-between; gap:12px; margin-top:21px; }
        .card-heading h3 { margin:0; font:700 18px/1.4 'Libre Baskerville',Georgia,serif; letter-spacing:-.4px; }
        .card-heading a:hover { color:var(--red); }
        .price { font-size:16px; font-weight:900; margin:0; white-space:nowrap; }
        .price small { color:var(--muted); font-size:11px; font-weight:700; }
        .neighborhood { margin:5px 0 13px; font-size:16px; color:var(--muted); line-height:1.5; }
        .specs { border-top:1px solid #e6e7e5; padding-top:12px; margin:0; font-size:14px; font-weight:700; color:#5d6866; line-height:1.5; }
        .rent-basis { font-size:12px; font-weight:400; }
        .empty-state { border:1px solid var(--line); padding:40px; line-height:1.7; }
        .notice { margin:0 0 24px; padding:12px 16px; border-left:3px solid var(--red); background:var(--cream); }
        .collections { --muted:#5f6764; padding:104px 0; background:var(--cream); }
        .collections-inner { display:grid; grid-template-columns:472px minmax(0,1fr); align-items:center; gap:64px; }
        .collections-copy h2 { line-height:1.05; letter-spacing:-2.2px; }
        .collections-copy p { max-width:450px; font-size:16px; line-height:1.8; color:var(--muted); margin:24px 0 0; }
        .collection-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); border-left:1px solid var(--line); border-top:1px solid var(--line); }
        .collection { position:relative; display:block; min-height:220px; padding:30px; border-right:1px solid var(--line); border-bottom:1px solid var(--line); background:var(--tile); transition:background .2s; }
        .collection:hover { background:#f8f5ed; }
        .collection-number { position:absolute; right:24px; top:22px; color:var(--muted); font-size:11px; }
        .collection-icon { display:grid; place-items:center; width:50px; height:50px; border-radius:50%; background:var(--gold); color:var(--red); margin-bottom:30px; }
        .collection-icon .icon { width:23px; height:23px; }
        .collection h3 { font-size:19px; line-height:1.3; font-weight:700; margin:0 0 8px; }
        .collection p { font-size:16px; line-height:1.4; margin:0; color:var(--muted); }
        .collection-arrow { position:absolute; bottom:25px; right:26px; width:18px; height:18px; }
        .site-footer { border-top:8px solid var(--gold); background:var(--taupe); color:#262b27; padding-top:64px; }
        .footer-top { display:grid; grid-template-columns:1.95fr .9fr .9fr 1.2fr; gap:30px; padding-bottom:60px; }
        .footer-brand p { font-size:12px; margin:1px 0 0; }
        .footer-column { padding-top:32px; }
        .footer-column h2 { font:900 12px/1.4 'DM Sans',Arial,sans-serif; text-transform:uppercase; letter-spacing:1.15px; margin:0 0 16px; }
        .footer-column ul { margin:0; padding:0; list-style:none; }
        .footer-column li { margin-top:12px; font-size:12px; }
        .footer-column a:hover { text-decoration:underline; text-underline-offset:4px; }
        .footer-cta { min-height:43px; padding:0 17px; font:900 12px 'DM Sans',Arial,sans-serif; letter-spacing:.2px; }
        .footer-cta .icon { width:16px; height:16px; }
        .footer-bottom { display:flex; justify-content:space-between; gap:20px; border-top:1px solid #726b60; padding:24px 0; font-size:10px; line-height:1.5; }
        .footer-bottom p { margin:0; }
        .footer-bottom a:hover { text-decoration:underline; }
        @media (min-width:1601px) { .hero-image { left:calc(50% - 42px); width:calc(50% + 42px); } }
        @media (max-width:1300px) {
            .wrap { width:calc(100% - 112px); }
            .main-nav { gap:28px; }
            .main-nav a { font-size:13px; letter-spacing:1.6px; }
            h1 { font-size:68px; letter-spacing:-3.8px; }
            h1 em { letter-spacing:-4px; }
            .hero { height:570px; }
            .hero-content { padding-top:165px; }
            .home-search { grid-template-columns:1.1fr 1fr 1fr 190px; width:850px; margin-top:68px; margin-left:0; }
            .collections-inner { grid-template-columns:minmax(0,.55fr) minmax(0,1fr); gap:40px; }
            .collections-copy h2 { font-size:44px; }
            .collection { padding:28px; }
            .card-heading { align-items:flex-start; flex-wrap:wrap; gap:4px 10px; }
            .card-heading h3 { font-size:16px; }
        }
        @media (max-width:1000px) {
            .wrap { width:calc(100% - 64px); }
            .header-inner { gap:20px; }
            .main-nav { gap:20px; margin-left:0; }
            .main-nav a { font-size:11px; letter-spacing:1px; }
            .login-button { min-width:90px; font-size:14px; }
            h1 { font-size:57px; letter-spacing:-3px; }
            h1 em { letter-spacing:-3.5px; }
            .hero { height:530px; }
            .hero-content { padding-top:150px; }
            .home-search { grid-template-columns:1.1fr 1fr 1fr 172px; margin-top:70px; }
            .search-field { padding-inline:15px; }
            .search-field label { font-size:10px; letter-spacing:.6px; }
            .search-submit { font-size:14px; }
            .featured { padding:80px 0; }
            .home-grid { gap:20px; }
            .neighborhood { font-size:14px; min-height:42px; }
            .badge { left:10px; top:12px; padding:7px; font-size:10px; }
            .favorite-form { right:10px; top:10px; }
            .favorite { width:36px; height:36px; padding:9px; }
            .collections { padding:72px 0; }
            .collections-inner { grid-template-columns:1fr; gap:40px; }
            .collections-copy { display:grid; grid-template-columns:1fr 1fr; align-items:center; gap:32px; }
            .collections-copy h2 { font-size:42px; }
            .collections-copy p { margin:0; }
            .footer-top { grid-template-columns:1.3fr 1fr 1fr; }
            .footer-column:last-child { grid-column:2 / 4; padding-top:0; }
        }
        @media (max-width:700px) {
            .wrap { width:calc(100% - 40px); }
            .site-header { height:76px; position:relative; z-index:3; }
            .header-inner { gap:12px; }
            .brand img { width:105px; }
            .login-button { margin-left:auto; min-width:84px; min-height:40px; }
            .menu-button { display:block; }
            .main-nav { position:absolute; top:75px; left:0; right:0; padding:20px; background:white; border-bottom:1px solid var(--red); display:none; box-shadow:0 8px 12px #0000000a; }
            .main-nav.is-open { display:grid; gap:8px; }
            .main-nav a { padding:12px 4px; font-size:14px; }
            .hero { height:auto; display:flex; flex-direction:column; }
            .hero-image { position:static; order:2; width:100%; height:260px; object-position:center; }
            .hero-content { padding-top:54px; padding-bottom:30px; }
            h1 { font-size:clamp(31px,7.7vw,52px); letter-spacing:-1.8px; line-height:1.16; }
            h1 em { letter-spacing:-2px; }
            .home-search { width:100%; max-width:none; margin:32px 0 0; grid-template-columns:1fr 1fr; }
            .search-field { min-height:78px; padding:16px; border-bottom:1px solid #e6e7e5; }
            .search-field label { font-size:10px; letter-spacing:.8px; }
            .search-field:nth-child(3)::before { display:none; }
            .search-field select { font-size:13px; }
            .search-submit { min-height:78px; padding:12px; font-size:13px; gap:7px; }
            .featured { padding:60px 0 64px; }
            .section-top { margin-bottom:28px; align-items:center; gap:12px; }
            h2 { font-size:38px; letter-spacing:-1.6px; }
            .view-all { font-size:12px; gap:5px; }
            .view-all .icon { width:14px; }
            .home-grid { grid-template-columns:1fr; gap:36px; }
            .card-photo { aspect-ratio:1.65; }
            .card-heading { flex-wrap:nowrap; margin-top:16px; }
            .card-heading h3 { font-size:16px; }
            .price { font-size:15px; }
            .neighborhood { min-height:0; font-size:14px; }
            .specs { font-size:13px; }
            .badge { left:14px; top:14px; font-size:11px; padding:8px 10px; }
            .favorite-form { right:12px; top:12px; }
            .favorite { width:44px; height:44px; padding:12px; }
            .collections { padding:60px 0; }
            .collections-inner { gap:32px; }
            .collections-copy { display:block; }
            .collections-copy h2 { font-size:38px; max-width:380px; }
            .collections-copy p { font-size:15px; margin-top:20px; max-width:400px; }
            .collection { min-height:226px; padding:24px 18px; }
            .collection-number { right:16px; top:16px; }
            .collection-icon { width:44px; height:44px; margin-bottom:23px; }
            .collection h3 { font-size:16px; }
            .collection p { font-size:13px; }
            .collection-arrow { bottom:18px; right:16px; }
            .site-footer { padding-top:40px; border-top-width:6px; }
            .footer-top { grid-template-columns:1fr 1fr; gap:28px 20px; padding-bottom:36px; }
            .footer-brand { grid-column:1 / -1; }
            .footer-column { padding-top:0; }
            .footer-column:last-child { grid-column:1 / -1; }
            .footer-column h2 { font-size:11px; }
            .footer-column li { font-size:13px; margin-top:14px; }
            .footer-bottom { flex-wrap:wrap; gap:12px; font-size:10px; padding-block:22px; }
            .footer-bottom p:nth-child(2) { order:3; width:100%; }
        }
        @media (prefers-reduced-motion:reduce) { html { scroll-behavior:auto; } *, *::before, *::after { transition:none!important; } }
    </style>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="wrap header-inner">
        <a class="brand" href="index.php" aria-label="HomeSC home"><img src="assets/logo-header.png" alt="HomeSC" width="110" height="34"></a>
        <nav class="main-nav" id="main-nav" aria-label="Main navigation">
            <a href="index.php" aria-current="page">Home</a>
            <a href="results.php">Browse Listings</a>
            <a href="page.php?section=post-listing">Post Listing</a>
            <a href="page.php?section=about">About</a>
        </nav>
        <a class="button login-button" href="login.php"><?= $userId ? 'Account' : 'Log In' ?></a>
        <button class="menu-button" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-nav"><span></span><span></span><span></span></button>
    </div>
</header>
<main id="main">
    <section class="hero" aria-labelledby="hero-title">
        <img class="hero-image" src="assets/hero.jpg" alt="Bright living room with a cream sofa and floor-to-ceiling windows" width="900" height="506" fetchpriority="high">
        <div class="wrap hero-content">
            <h1 id="hero-title"><span>Find your home</span><em>away from home.</em></h1>
            <form class="home-search" id="home-search" action="results.php" method="get" aria-label="Find a home">
                <div class="search-field">
                    <label for="location">Location</label>
                    <div class="input-line"><?= icon('pin') ?><select id="location" name="location">
                        <option value="">Near USC</option>
                        <?php foreach ($neighborhoods as $neighborhood) { ?>
                            <option value="<?= escape($neighborhood) ?>"><?= escape($neighborhood) ?></option>
                        <?php } ?>
                    </select></div>
                </div>
                <div class="search-field">
                    <label for="move-in">Move-in</label>
                    <select id="move-in" name="move_in">
                        <option value="">Any time</option>
                        <?php for ($i = 1; $i <= 24; $i++) {
                            $month = strtotime('first day of +' . $i . ' month');
                            $value = date('Y-m', $month); ?>
                            <option value="<?= $value ?>"><?= date('F Y', $month) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="search-field">
                    <label for="budget">Monthly budget</label>
                    <select id="budget" name="max_rent">
                        <option value="">Any budget</option>
                        <?php foreach ([1000,1200,1500,2000,2500,3000] as $budget) { ?>
                            <option value="<?= $budget ?>">Up to $<?= number_format($budget) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button class="button search-submit" type="submit"><?= icon('search') ?>Search homes</button>
            </form>
            <a class="search-more" href="search.php">More search options →</a>
        </div>
    </section>

    <section class="featured wrap" id="featured" aria-labelledby="featured-title">
        <div class="section-top"><h2 id="featured-title">Featured</h2><a class="view-all" href="results.php">View all listings <?= icon('arrow') ?></a></div>
        <?php if ($notice) { ?><p class="notice" role="status"><?= escape($notice) ?></p><?php } ?>
        <?php if ($errorMessage) { ?><p class="notice" role="alert"><?= escape($errorMessage) ?></p><?php } ?>
        <div class="home-grid">
            <?php foreach ($featured as $home) {
                $isSaved = isset($savedIds[(int) $home['property_id']]);
                $photoUrl = photo_source($home['photo_url'] ?? '');
                $detailUrl = 'details.php?property_id=' . (int) $home['property_id'] . '&listing_id=' . (int) $home['listing_id'];
            ?>
            <article class="home-card">
                <div class="card-photo">
                    <a href="<?= escape($detailUrl) ?>" aria-label="View <?= escape($home['property_name']) ?>">
                        <img src="<?= escape($photoUrl ?: DEFAULT_LISTING_PHOTO) ?>" alt="<?= $photoUrl ? escape($home['property_name'] . ' interior') : '' ?>" width="900" height="506" loading="lazy">
                    </a>
                    <span class="badge"><?= escape($home['badge']) ?></span>
                    <form class="favorite-form" action="index.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">
                        <input type="hidden" name="property_id" value="<?= (int) $home['property_id'] ?>">
                        <input type="hidden" name="action" value="<?= $isSaved ? 'unsave' : 'save' ?>">
                        <button class="favorite" type="submit" aria-pressed="<?= $isSaved ? 'true' : 'false' ?>" aria-label="<?= $isSaved ? 'Unsave ' : 'Save ' ?><?= escape($home['property_name']) ?>"><?= icon('heart') ?></button>
                    </form>
                </div>
                <div class="card-heading">
                    <h3><a href="<?= escape($detailUrl) ?>"><?= escape($home['property_name']) ?></a></h3>
                    <p class="price"><?php if (isset($home['monthly_rent'])) { ?>$<?= number_format((float) $home['monthly_rent']) ?><small>/mo</small><?php } ?></p>
                </div>
                <p class="neighborhood"><?= escape($home['neighborhood'] ?? '') ?></p>
                <p class="specs"><?= escape(home_specs($home)) ?><?php if (!empty($home['rent_basis'])) { ?><span class="rent-basis"> · <?= escape($home['rent_basis']) ?></span><?php } ?></p>
            </article>
            <?php } ?>
        </div>
    </section>

    <section class="collections" aria-labelledby="collections-title">
        <div class="wrap collections-inner">
            <div class="collections-copy"><h2 id="collections-title">A place for every<br>student.</h2><p>However you want to live, start with a collection curated around what matters most.</p></div>
            <div class="collection-grid">
                <?php $collections = [
                    ['01','pin','Near Campus','Under a 15-minute walk','max_walk=15'],
                    ['02','key','One Bedroom','A place of your own','unit_type=One+Bedroom'],
                    ['03','people','Larger Apartments','Room for more','bedrooms=2'],
                    ['04','wallet','Budget Friendly','Great homes under $1,200','max_rent=1200'],
                ]; foreach ($collections as $collection) { ?>
                <a class="collection" href="results.php?<?= escape($collection[4]) ?>">
                    <span class="collection-number" aria-hidden="true"><?= $collection[0] ?></span>
                    <span class="collection-icon"><?= icon($collection[1]) ?></span>
                    <h3><?= $collection[2] ?></h3><p><?= $collection[3] ?></p>
                    <span class="collection-arrow"><?= icon('arrow') ?></span>
                </a>
                <?php } ?>
            </div>
        </div>
    </section>
</main>
<footer class="site-footer">
    <div class="wrap">
        <div class="footer-top">
            <div class="footer-brand"><a class="brand" href="index.php" aria-label="HomeSC home"><img src="assets/logo-footer.png" width="110" height="34" alt="HomeSC"></a><p>Your place near campus starts here.</p></div>
            <div class="footer-column"><h2>Explore</h2><ul><li><a href="results.php">Browse listings</a></li><li><a href="page.php?section=neighborhoods">Neighborhoods</a></li><li><a href="page.php?section=how-it-works">How it works</a></li></ul></div>
            <div class="footer-column"><h2>Students</h2><ul><li><a href="page.php?section=housing-guide">Housing guide</a></li><li><a href="page.php?section=safety">Safety tips</a></li><li><a href="page.php?section=help">Help center</a></li></ul></div>
            <div class="footer-column"><h2>Ready to find your place?</h2><a class="button footer-cta" href="#home-search">Start searching <?= icon('arrow') ?></a></div>
        </div>
        <div class="footer-bottom"><p>© <?= date('Y') ?> HomeSC</p><p>Made for students in Los Angeles.</p><p><a href="page.php?section=privacy">Privacy</a> · <a href="page.php?section=terms">Terms</a></p></div>
    </div>
</footer>
<script nonce="<?= escape($nonce) ?>">
    const menuButton = document.querySelector('.menu-button');
    const navigation = document.querySelector('.main-nav');
    function closeMenu() {
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.setAttribute('aria-label', 'Open navigation');
        navigation.classList.remove('is-open');
    }
    menuButton.addEventListener('click', () => {
        const open = menuButton.getAttribute('aria-expanded') !== 'true';
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        navigation.classList.toggle('is-open', open);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') {
            closeMenu();
            menuButton.focus();
        }
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('.site-header')) closeMenu();
    });
    document.querySelectorAll('.favorite-form').forEach(form => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        });
    });
</script>
</body>
</html>
