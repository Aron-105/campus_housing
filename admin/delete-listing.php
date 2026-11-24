<?php
require __DIR__ . '/common.php';
$title = 'Delete Listing'; $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]); $listing = $id ? admin_listing($db, $id) : null; $error = '';
if ($id && !$listing) { http_response_code(404); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $listing) {
    try { $stmt=$db->prepare('DELETE FROM listings WHERE listing_id = ?'); $stmt->bind_param('i', $id); $stmt->execute(); $stmt->close(); header('Location: delete-listing.php?deleted=1', true, 303); exit; }
    catch (mysqli_sql_exception $e) { error_log('Delete listing: '.$e->getMessage()); $error = (int)$e->getCode() === 1451 ? 'This listing has related records and cannot be deleted until they are handled.' : 'Could not delete this listing.'; }
}
include 'header.php';
?>
<h1>Delete Listing</h1>
<?php if ($error) { ?><p role="alert"><?= escape($error) ?></p><?php } ?>
<?php if ($id && !$listing) { ?><p>Listing not found.</p><?php } elseif ($listing) { ?><div class="confirm-box"><h2>Delete this listing?</h2><p><?= escape($listing['title']) ?></p><p>The property and unit will be kept. This cannot be undone.</p><form method="post"><input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>"><button class="btn btn-danger"<?= $adminPreview ? ' disabled' : '' ?>>Delete listing</button> <a href="delete-listing.php">Cancel</a></form></div>
<?php } else { ?><?php if (isset($_GET['deleted'])) { ?><p role="status">Listing deleted.</p><?php } ?><table class="admin-table"><tr><th>Listing</th><th>Property</th><th>Rent</th><th>Status</th><th></th></tr><?php foreach (admin_listings($db) as $row) { ?><tr><td><?= escape($row['title']) ?></td><td><?= escape($row['property_name']) ?></td><td>$<?= escape($row['monthly_rent']) ?></td><td><?= escape($row['status']) ?></td><td><a href="delete-listing.php?id=<?= (int)$row['listing_id'] ?>">Delete</a></td></tr><?php } ?></table><?php } include 'footer.php'; ?>
