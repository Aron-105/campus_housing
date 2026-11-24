<?php
$title = 'Delete Listing';
include 'header.php';
include 'sample-listings.php';

$deleting = null;
if (isset($_GET['id'])) {
    foreach ($listings as $l) {
        if ($l['id'] == $_GET['id']) {
            $deleting = $l;
        }
    }
}
?>

<h1>Delete Listing</h1>

<?php if ($deleting) { ?>

    <div class="confirm-box">
        <h2>Delete this listing?</h2>
        <p><strong><?php echo htmlspecialchars($deleting['name']); ?></strong><br><?php echo htmlspecialchars($deleting['address']); ?></p>
        <p>This will remove the listing from the site. This can't be undone.</p>
        <form method="post" action="">
            <input type="hidden" name="id" value="<?php echo $deleting['id']; ?>">
            <button type="submit" class="btn btn-danger">Yes, delete it</button>
            <a class="btn btn-outline" href="delete-listing.php">Cancel</a>
        </form>
    </div>

<?php } else { ?>

    <p class="intro">Choose a listing to remove from the site.</p>

    <table class="admin-table">
        <tr>
            <th>Property</th>
            <th>Neighborhood</th>
            <th>Rent</th>
            <th>Status</th>
            <th></th>
        </tr>
        <?php foreach ($listings as $l) { ?>
        <tr>
            <td><strong><?php echo $l['name']; ?></strong><br><span class="muted"><?php echo $l['address']; ?></span></td>
            <td><?php echo $l['neighborhood']; ?></td>
            <td>$<?php echo number_format($l['rent']); ?>/mo</td>
            <td><span class="status <?php echo $l['status']; ?>"><?php echo ucfirst($l['status']); ?></span></td>
            <td class="actions"><a class="btn btn-small btn-danger" href="delete-listing.php?id=<?php echo $l['id']; ?>">Delete</a></td>
        </tr>
        <?php } ?>
    </table>

<?php } ?>

<?php include 'footer.php'; ?>
