<?php
require __DIR__ . '/common.php';
$title = 'Edit Listing'; $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]); $listing = $id ? admin_listing($db, $id) : null; $error = '';
if ($id && !$listing) { http_response_code(404); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $listing) {
    admin_csrf(); $listing = $_POST;
    try { admin_save($db, $_POST, $id); header('Location: edit-listing.php?saved=1', true, 303); exit; }
    catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
    catch (Throwable $e) { error_log('Edit listing: '.$e->getMessage()); $error = 'Could not save the listing.'; }
}
include 'header.php';
?>
<h1>Edit Listing</h1>
<?php if ($id && !$listing) { ?><p>Listing not found.</p><?php } elseif ($listing) { $buttonText = 'Save changes'; include 'listing-form.php'; } else { ?>
<?php if (isset($_GET['saved'])) { ?><p role="status">Listing saved.</p><?php } ?>
<table class="admin-table"><tr><th>Listing</th><th>Property</th><th>Rent</th><th>Status</th><th></th></tr><?php foreach (admin_listings($db) as $row) { ?><tr><td><?= escape($row['title']) ?></td><td><?= escape($row['property_name']) ?></td><td>$<?= escape($row['monthly_rent']) ?></td><td><?= escape($row['status']) ?></td><td><a href="edit-listing.php?id=<?= (int)$row['listing_id'] ?>">Edit</a></td></tr><?php } ?></table>
<?php } include 'footer.php'; ?>
