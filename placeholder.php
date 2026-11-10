<?php

if (!isset($nonce)) { require __DIR__ . '/bootstrap.php'; }
$title = $title ?? 'More to come.';
$description = $description ?? 'This part of HomeSC is coming soon.';
$eyebrow = $eyebrow ?? 'HomeSC';
$selections = $selections ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape($eyebrow) ?> — HomeSC</title>
    <link rel="icon" href="assets/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/fonts/fonts.css">
    <style nonce="<?= escape($nonce) ?>">
        * { box-sizing:border-box; }
        body { margin:0; color:#1f292a; background:#e8e4da; font:16px/1.7 'DM Sans',Arial,sans-serif; }
        a { color:inherit; }
        :focus-visible { outline:3px solid #a00000; outline-offset:5px; }
        header { border-top:5px solid #a00000; border-bottom:1px solid #a00000; background:white; }
        nav { max-width:1408px; margin:auto; min-height:82px; padding:16px 32px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
        nav a { font-weight:700; font-size:14px; text-decoration:none; }
        nav img { display:block; width:110px; height:34px; }
        main { width:calc(100% - 48px); max-width:800px; margin:100px auto; }
        .eyebrow { text-transform:uppercase; font-size:12px; font-weight:900; letter-spacing:2px; color:#a00000; }
        h1 { font:400 clamp(34px,6vw,54px)/1.15 'Libre Baskerville',Georgia,serif; letter-spacing:-1.5px; margin:20px 0; }
        .description { max-width:650px; color:#4e5855; }
        .notice { background:#fffaf0; border-left:3px solid #a00000; padding:14px 20px; }
        dl { margin:30px 0; padding:24px; border:1px solid #b9b2a7; background:#f0ede5; }
        dl div { display:grid; grid-template-columns:150px 1fr; gap:20px; padding:6px 0; }
        dt { font-weight:700; }
        dd { margin:0; overflow-wrap:anywhere; }
        .button { display:inline-block; margin:16px 20px 0 0; background:#a00000; color:#ffcc00; padding:14px 24px; text-decoration:none; font:700 15px 'Libre Baskerville',Georgia,serif; }
        .button:hover { background:#360008; }
        .back { display:inline-block; font-size:14px; font-weight:700; }
        footer { max-width:800px; width:calc(100% - 48px); margin:0 auto 36px; font-size:12px; color:#4e5855; }
        @media(max-width:500px) { main { margin:60px auto; } nav { padding-inline:24px; } dl div { grid-template-columns:1fr; gap:0; padding-block:8px; } }
    </style>
</head>
<body>
<header><nav aria-label="Main navigation"><a href="index.php" aria-label="HomeSC home"><img src="assets/logo-header.png" alt="HomeSC" width="110" height="34"></a><a href="index.php#home-search">Find a home →</a></nav></header>
<main>
    <p class="eyebrow"><?= escape($eyebrow) ?></p>
    <h1><?= escape($title) ?></h1>
    <?php if (!empty($message)) { ?><p class="notice" role="status"><?= escape($message) ?></p><?php } ?>
    <p class="description"><?= escape($description) ?></p>
    <?php if ($selections) { ?><dl><?php foreach ($selections as $label => $value) { ?><div><dt><?= escape($label) ?></dt><dd><?= escape($value) ?></dd></div><?php } ?></dl><?php } ?>
    <a class="button" href="index.php#featured">Explore featured homes</a><a class="back" href="index.php#home-search">Back to search</a>
</main>
<footer>HomeSC · Made for students in Los Angeles.</footer>
</body>
</html>
