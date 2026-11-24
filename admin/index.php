<?php
$title = 'Dashboard';
include 'header.php';
?>

<h1>Admin Dashboard</h1>
<p class="intro">Manage the housing listings that show up on HomeSC.</p>

<div class="cards">
    <a class="card" href="add-listing.php">
        <h2>Add Listing</h2>
        <p>Add a new property and unit to the site.</p>
        <span class="card-link">Add a listing &rarr;</span>
    </a>
    <a class="card" href="edit-listing.php">
        <h2>Edit Listing</h2>
        <p>Update rent, availability, details or status for an existing listing.</p>
        <span class="card-link">Edit a listing &rarr;</span>
    </a>
    <a class="card" href="delete-listing.php">
        <h2>Delete Listing</h2>
        <p>Remove a listing that is no longer on the market.</p>
        <span class="card-link">Delete a listing &rarr;</span>
    </a>
</div>

<?php include 'footer.php'; ?>
