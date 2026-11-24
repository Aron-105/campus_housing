<?php
require __DIR__ . '/common.php';
$title = 'Add Listing'; $listing = []; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing = $_POST;
    try {
        admin_transaction($db, fn() => admin_save($db, $_POST, null));
        admin_redirect('edit-listing.php?saved=1');
    }
    catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
    catch (Throwable $e) { error_log('Add listing: '.$e->getMessage()); $error = 'Could not save the listing.'; }
}
include 'header.php';
?>
<h1>Add Listing</h1>
<?php $buttonText = 'Add listing'; include 'listing-form.php'; include 'footer.php'; ?>
