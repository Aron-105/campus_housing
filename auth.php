<?php
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/browse_data.php';
$config = require __DIR__ . '/config.php';
$clientId = $config['google_client_id'] ?? '';
$clientSecret = $config['google_client_secret'] ?? '';
$redirect = $config['google_redirect_uri'] ?? '';

function google_request($url, $data = null, $token = '') {
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => $token ? ['Authorization: Bearer ' . $token] : []]);
    if ($data !== null) { curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data)); }
    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $json = json_decode($response ?: '', true);
    if ($status !== 200 || !is_array($json)) { throw new RuntimeException('Google authentication failed.'); }
    return $json;
}

try {
    if (!$clientId || !$clientSecret || !$redirect || !extension_loaded('curl')) { throw new RuntimeException('Google sign-in is not configured.'); }
    if (isset($_GET['start'])) {
        $_SESSION['oauth_state'] = bin2hex(random_bytes(32));
        $_SESSION['oauth_verifier'] = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $_SESSION['oauth_verifier'], true)), '+/', '-_'), '=');
        $params = ['client_id'=>$clientId, 'redirect_uri'=>$redirect, 'response_type'=>'code',
            'scope'=>'openid email', 'state'=>$_SESSION['oauth_state'], 'code_challenge'=>$challenge,
            'code_challenge_method'=>'S256', 'hd'=>'usc.edu'];
        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
        exit;
    }
    $state = request_text($_GET, 'state');
    $expected = $_SESSION['oauth_state'] ?? '';
    $verifier = $_SESSION['oauth_verifier'] ?? '';
    unset($_SESSION['oauth_state'], $_SESSION['oauth_verifier']);
    if (!$expected || !$verifier || !hash_equals($expected, $state) || empty($_GET['code']) || !is_string($_GET['code'])) { throw new RuntimeException('Sign-in was cancelled or expired. Please try again.'); }
    $tokens = google_request('https://oauth2.googleapis.com/token', ['code'=>$_GET['code'], 'client_id'=>$clientId,
        'client_secret'=>$clientSecret, 'redirect_uri'=>$redirect, 'grant_type'=>'authorization_code', 'code_verifier'=>$verifier]);
    if (empty($tokens['access_token']) || !is_string($tokens['access_token'])) { throw new RuntimeException('Google sign-in failed.'); }
    $profile = google_request('https://openidconnect.googleapis.com/v1/userinfo', null, $tokens['access_token']);
    $email = strtolower($profile['email'] ?? '');
    $subject = $profile['sub'] ?? '';
    if (($profile['email_verified'] ?? false) !== true || !is_string($subject) || $subject === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || substr($email, -8) !== '@usc.edu') {
        throw new RuntimeException('Sign in with a verified @usc.edu Google account.');
    }
    $db = browse_database();
    $db->begin_transaction();
    // Google subjects, not mutable email addresses, identify returning accounts.
    $stmt = $db->prepare('SELECT user_id FROM google_identities WHERE google_subject = ?');
    $stmt->bind_param('s', $subject); $stmt->execute(); $stmt->bind_result($id); $found = $stmt->fetch(); $stmt->close();
    if (!$found) {
        $stmt = $db->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email); $stmt->execute(); $stmt->bind_result($id); $found = $stmt->fetch(); $stmt->close();
        if (!$found) {
            $username = 'student_' . bin2hex(random_bytes(12));
            $stmt = $db->prepare('INSERT INTO users (username, email, is_usc_verified) VALUES (?, ?, 1)');
            $stmt->bind_param('ss', $username, $email); $stmt->execute(); $id = $db->insert_id; $stmt->close();
        }
        $stmt = $db->prepare('INSERT INTO google_identities (google_subject, user_id) VALUES (?, ?)');
        $stmt->bind_param('si', $subject, $id); $stmt->execute(); $stmt->close();
    }
    $db->commit(); $db->close();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$id;
    header('Location: login.php', true, 303); exit;
} catch (Throwable $error) {
    error_log('HomeSC sign-in: ' . $error->getMessage());
    $_SESSION['login_error'] = in_array($error->getMessage(), ['Google sign-in is not configured.', 'Sign-in was cancelled or expired. Please try again.', 'Sign in with a verified @usc.edu Google account.'], true)
        ? $error->getMessage() : 'Sign-in is temporarily unavailable. Please try again later.';
    header('Location: login.php', true, 303); exit;
}
