<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../browse_data.php';

function admin_preview_enabled(): bool {
    if (PHP_SAPI !== 'cli-server' || getenv('HOMESC_ENV', true) !== 'development') {
        return false;
    }

    $loopbackHosts = ['localhost', '127.0.0.1', '::1'];
    if (!in_array($_SERVER['SERVER_NAME'] ?? '', $loopbackHosts, true)
        || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
        || !preg_match('/\A(?:localhost|127\.0\.0\.1|\[::1\])(?::[0-9]+)?\z/i', $_SERVER['HTTP_HOST'] ?? '')) {
        return false;
    }

    $configPath = __DIR__ . '/../config.php';
    $config = is_file($configPath) ? require $configPath : [];
    return ($config['admin_preview'] ?? false) === true;
}

$adminPreview = admin_preview_enabled();
if ($adminPreview && !in_array($_SERVER['REQUEST_METHOD'] ?? '', ['GET', 'HEAD'], true)) {
    header('Allow: GET, HEAD');
    http_response_code(405);
    exit('Admin preview is read-only.');
}
if (!$adminPreview && !$userId) { header('Location: ../login.php', true, 303); exit; }
try {
    $db = browse_database();
    if (!$adminPreview) {
        $check = $db->prepare('SELECT is_admin FROM users WHERE user_id = ?');
        $check->bind_param('i', $userId); $check->execute(); $check->bind_result($isAdmin);
        $allowed = $check->fetch() && (int)$isAdmin === 1; $check->close();
        if (!$allowed) { http_response_code(403); exit('Administrator access required.'); }
    }
} catch (Throwable $e) { error_log('Admin access: ' . $e->getMessage()); http_response_code(503); exit('Admin service is unavailable.'); }
function admin_csrf() {
    global $csrfToken;
    if (!hash_equals($csrfToken, request_text($_POST, 'csrf_token'))) { http_response_code(403); exit('Invalid session token.'); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf();
}

function admin_text(array $input, string $key): string {
    if (!isset($input[$key])) { return ''; }
    if (!is_string($input[$key])) { throw new InvalidArgumentException('Enter valid form values.'); }
    return trim($input[$key]);
}

function admin_query(mysqli $db, string $sql, array $values = []): mysqli_stmt {
    $stmt = $db->prepare($sql);
    $stmt->execute($values);
    return $stmt;
}

function admin_rows(mysqli $db, string $sql, array $values = []): array {
    $stmt = admin_query($db, $sql, $values);
    $rows = browse_statement_rows($stmt);
    $stmt->close();
    return $rows;
}

function admin_transaction(mysqli $db, callable $work): void {
    $db->begin_transaction();
    try {
        $work();
        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

function admin_redirect(string $url): void {
    header('Location: ' . $url, true, 303);
    exit;
}
function admin_listing(mysqli $db, int $id) {
    $stmt = $db->prepare('SELECT * FROM listings WHERE listing_id = ?');
    $stmt->bind_param('i', $id); $stmt->execute(); $rows = browse_statement_rows($stmt); $stmt->close();
    return $rows[0] ?? null;
}
function admin_listings(mysqli $db) {
    return $db->query('SELECT l.listing_id, l.title, l.monthly_rent, l.status, p.name AS property_name FROM listings l JOIN properties p ON p.property_id = l.property_id ORDER BY l.listing_id DESC')->fetch_all(MYSQLI_ASSOC);
}
function admin_save(mysqli $db, array $input, ?int $id) {
    $title = admin_text($input, 'title');
    $unitId = filter_var($input['unit_id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    $basisId = filter_var($input['rent_basis_id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    $rent = admin_text($input, 'monthly_rent');
    $status = admin_text($input, 'status');
    $from = admin_text($input, 'available_from'); $until = admin_text($input, 'available_until');
    $url = admin_text($input, 'listing_url');
    if ($title === '' || strlen($title) > 255 || !$unitId || !$basisId || !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $rent) || !in_array($status, ['pending','active','leased','expired','inactive'], true)) { throw new InvalidArgumentException('Enter a title, unit, rent basis, valid rent, and status.'); }
    foreach ([$from, $until] as $date) { if ($date !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate((int)substr($date,5,2), (int)substr($date,8,2), (int)substr($date,0,4)))) { throw new InvalidArgumentException('Enter valid dates.'); } }
    if ($from && $until && $until < $from) { throw new InvalidArgumentException('End date must not be earlier than start date.'); }
    if ($url !== '' && (strlen($url) > 500 || !filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http','https'], true))) { throw new InvalidArgumentException('Enter a valid HTTP or HTTPS listing link.'); }
    $unit = admin_rows($db, 'SELECT property_id FROM units WHERE unit_id = ? FOR UPDATE', [$unitId])[0] ?? null;
    if (!$unit) { throw new InvalidArgumentException('Choose an existing unit.'); }
    $propertyId = (int)$unit['property_id'];
    $stmt = $db->prepare('SELECT rent_basis_id FROM rent_bases WHERE rent_basis_id = ?'); $stmt->bind_param('i', $basisId); $stmt->execute(); $stmt->store_result(); $found = $stmt->num_rows > 0; $stmt->close();
    if (!$found) { throw new InvalidArgumentException('Choose a valid rent basis.'); }
    $from = $from ?: null; $until = $until ?: null; $url = $url ?: null;
    if ($id) {
        $stmt = $db->prepare('UPDATE listings SET property_id=?, unit_id=?, title=?, monthly_rent=?, rent_basis_id=?, status=?, available_from=?, available_until=?, listing_url=? WHERE listing_id=?');
        $stmt->bind_param('iississssi', $propertyId, $unitId, $title, $rent, $basisId, $status, $from, $until, $url, $id);
    } else {
        $stmt = $db->prepare('INSERT INTO listings (property_id, unit_id, title, monthly_rent, rent_basis_id, status, available_from, available_until, listing_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('iississss', $propertyId, $unitId, $title, $rent, $basisId, $status, $from, $until, $url);
    }
    $stmt->execute(); $stmt->close();
    admin_save_amenities($db, $input, $unitId);
}

require_once __DIR__ . '/amenity-data.php';
