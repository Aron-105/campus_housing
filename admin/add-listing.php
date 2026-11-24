<?php
require __DIR__ . '/common.php';
$title = 'Add Listing'; $listing = []; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf(); $listing = $_POST;
    try { admin_save($db, $_POST, null); header('Location: edit-listing.php?saved=1', true, 303); exit; }
    catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
    catch (Throwable $e) { error_log('Add listing: '.$e->getMessage()); $error = 'Could not save the listing.'; }
}
include 'header.php';
?>
<h1>Add Listing</h1>
<?php $buttonText = 'Add listing'; include 'listing-form.php'; include 'footer.php'; ?>
