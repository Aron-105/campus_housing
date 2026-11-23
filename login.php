<?php
require __DIR__ . '/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, request_text($_POST, 'csrf_token'))) { http_response_code(403); exit('Invalid session token.'); }
    $_SESSION = [];
    session_destroy();
    header('Location: index.php', true, 303); exit;
}
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Log in — HomeSC</title><link rel="stylesheet" href="assets/fonts/fonts.css"><link rel="stylesheet" href="browse.css"></head>
<body><header class="site-header"><nav class="wrap header-inner"><a class="brand" href="index.php"><img src="assets/logo-header.png" width="110" height="34" alt="HomeSC home"></a><div class="nav-links"><a href="results.php">Browse listings</a></div></nav></header>
<main class="wrap account-page"><h1><?= $userId ? 'Your account' : 'Log in to HomeSC' ?></h1>
<?php if ($error) { ?><p class="status-message" role="alert"><?= escape($error) ?></p><?php } ?>
<?php if ($userId) { ?><p>You are signed in.</p><form method="post"><input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>"><button class="search-button" type="submit">Log out</button></form>
<?php } else { ?>
<?php if (request_text($_GET, 'reason') === 'sign-in') { ?><p>Log in to save homes or write a review.</p><?php } ?>
<section><h2>Students</h2><p>Email and password sign-in is coming soon. Students will need to verify their @usc.edu email address.</p>
<form class="account-form" aria-label="Student sign-in">
<label for="email">USC email</label><input id="email" type="email" placeholder="you@usc.edu" autocomplete="email" disabled>
<label for="password">Password</label><input id="password" type="password" autocomplete="current-password" disabled>
<button class="search-button" type="button" disabled>Log in</button>
</form><p>Registration and password reset are coming soon.</p></section>
<section><h2>Administrators</h2><p>Administrator sign-in is coming soon.</p></section>
<?php } ?></main></body></html>
