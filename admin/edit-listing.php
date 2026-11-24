<?php
$title = 'Edit Listing';
include 'header.php';
include 'sample-listings.php';

$editing = null;
if (isset($_GET['id'])) {
    foreach ($listings as $l) {
        if ($l['id'] == $_GET['id']) {
            $editing = $l;
        }
    }
}
?>

<?php if ($editing) { ?>

    <h1>Edit Listing</h1>
    <p class="intro">Editing <strong><?php echo htmlspecialchars($editing['name']); ?></strong>. <a href="edit-listing.php">Back to all listings</a></p>

    <?php
    $listing = $editing;
    $buttonText = 'Save changes';
    include 'listing-form.php';
    ?>

<?php } else { ?>

    <h1>Edit Listing</h1>
    <p class="intro">Choose a listing to edit.</p>

    <form class="search-bar" method="get" action="">
        <input type="text" name="q" placeholder="Search by name or address">
        <button type="submit" class="btn">Search</button>
    </form>

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
            <td class="actions"><a class="btn btn-small" href="edit-listing.php?id=<?php echo $l['id']; ?>">Edit</a></td>
        </tr>
        <?php } ?>
    </table>

<?php } ?>

<?php include 'footer.php'; ?>
