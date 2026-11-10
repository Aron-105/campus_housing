<?php
require_once __DIR__ . '/helpers.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('zend.exception_ignore_args', '1');
ini_set('log_errors', '1');
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$nonce = base64_encode(random_bytes(18));
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' https:; font-src 'self'; style-src 'self' 'nonce-$nonce'; script-src 'self' 'nonce-$nonce'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
header('Cache-Control: no-store');
if ($https) { header('Strict-Transport-Security: max-age=31536000'); }
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('homesc_session');
session_set_cookie_params(['httponly'=>true, 'secure'=>$https, 'samesite'=>'Lax', 'path'=>'/']);
if (!session_start()) { throw new RuntimeException('Could not start the PHP session. Check the server session settings.'); }
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
// A future login page must set this ONLY after verifying the user's password.
$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT,
    ['options'=>['min_range'=>1, 'max_range'=>2147483647]]);
