<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/browse_data.php';
$error = '';
$isAdmin = false;
if ($userId) {
    try {
        $accountDb = browse_database();
        $adminCheck = $accountDb->prepare('SELECT is_admin FROM users WHERE user_id = ?');
        $adminCheck->bind_param('i', $userId); $adminCheck->execute(); $adminCheck->bind_result($adminFlag);
        $isAdmin = $adminCheck->fetch() && (int)$adminFlag === 1;
        $adminCheck->close(); $accountDb->close();
    } catch (Throwable $e) { error_log('HomeSC account role: ' . $e->getMessage()); }
}
$mode = request_text($_GET, 'mode') === 'register' ? 'register' : 'login';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, request_text($_POST, 'csrf_token'))) { http_response_code(403); exit('Invalid session token.'); }
    $action = request_text($_POST, 'action');
    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php', true, 303); exit;
    }
    $mode = $action === 'register' ? 'register' : 'login';
    $email = strtolower(request_text($_POST, 'email'));
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    try {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255 || !$password) {
            throw new InvalidArgumentException('Enter a valid email and password.');
        }
        $db = browse_database();
        if ($mode === 'register') {
            if (strlen($password) < 12 || strlen($password) > 72) { throw new InvalidArgumentException('Use a password between 12 and 72 bytes.'); }
            if ($password !== ($_POST['password_confirm'] ?? null)) { throw new InvalidArgumentException('Passwords do not match.'); }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $username = 'user_' . bin2hex(random_bytes(16));
            $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, is_usc_verified) VALUES (?, ?, ?, 0)');
            $stmt->bind_param('sss', $username, $email, $hash);
            try { $stmt->execute(); } catch (mysqli_sql_exception $e) {
                if ((int)$e->getCode() === 1062) { throw new InvalidArgumentException('An account with that email already exists.'); }
                throw $e;
            }
            $id = $db->insert_id;
        } else {
            $stmt = $db->prepare('SELECT user_id, password_hash FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email); $stmt->execute(); $stmt->bind_result($id, $hash);
            $found = $stmt->fetch();
            if (!$found || !$hash || !password_verify($password, $hash)) { throw new InvalidArgumentException('Incorrect email or password.'); }
        }
        $stmt->close(); $db->close();
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$id;
        header('Location: login.php', true, 303); exit;
    } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
      catch (Throwable $e) { error_log('HomeSC account: ' . $e->getMessage()); $error = 'Account service is temporarily unavailable.'; }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Account — HomeSC</title><link rel="stylesheet" href="assets/fonts/fonts.css"><link rel="stylesheet" href="browse.css"></head>
<body><header class="site-header"><nav class="wrap header-inner"><a class="brand" href="index.php"><img src="assets/logo-header.png" width="110" height="34" alt="HomeSC home"></a><div class="nav-links"><a href="results.php">Browse listings</a></div></nav></header>
<main class="wrap account-page"><h1><?= $userId ? 'Your account' : ($mode === 'register' ? 'Create an account' : 'Log in to HomeSC') ?></h1>
<?php if ($error) { ?><p class="status-message" role="alert"><?= escape($error) ?></p><?php } ?>
<?php if ($userId) { ?><p>You are signed in.</p><?php if ($isAdmin) { ?><p><a href="admin/">Open admin dashboard</a></p><?php } ?><form method="post"><input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>"><input type="hidden" name="action" value="logout"><button class="search-button" type="submit">Log out</button></form>
<?php } else { ?>
<form class="account-form" method="post"><input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>"><input type="hidden" name="action" value="<?= escape($mode) ?>">
<label for="email">Email</label><input id="email" name="email" type="email" maxlength="255" value="<?= escape($email ?? '') ?>" autocomplete="email" required>
<label for="password">Password<?= $mode === 'register' ? ' (at least 12 characters)' : '' ?></label><input id="password" name="password" type="password" autocomplete="<?= $mode === 'register' ? 'new-password' : 'current-password' ?>" required>
<?php if ($mode === 'register') { ?><label for="password_confirm">Confirm password</label><input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required><?php } ?>
<button class="search-button" type="submit"><?= $mode === 'register' ? 'Create account' : 'Log in' ?></button></form>
<p><?= $mode === 'register' ? '<a href="login.php">Already have an account? Log in</a>' : '<a href="login.php?mode=register">Create an account</a>' ?></p>
<?php } ?></main></body></html>
