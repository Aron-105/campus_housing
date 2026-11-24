<?php
// Relative links in this directory need the canonical trailing slash.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (is_string($path) && substr($path, -6) === '/admin') {
    header('Location: ' . $path . '/' . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']), true, 302);
    exit;
}
$title = 'Dashboard';
include 'header.php';
?>

<h1>Admin Dashboard</h1>
<p class="intro">Manage the housing listings that show up on HomeSC.</p>

<div class="cards">
    <a class="card" href="add-listing.php">
        <h2>Add Listing</h2>
        <p>Add a listing for an existing property and unit.</p>
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

<h2>Housing management</h2>
<div class="cards">
    <a class="card" href="properties.php">
        <h2>Properties</h2>
        <p>Add, edit, or delete housing properties.</p>
    </a>
    <a class="card" href="units.php">
        <h2>Units</h2>
        <p>Manage the units associated with each property.</p>
    </a>
    <a class="card" href="photos.php">
        <h2>Photos</h2>
        <p>Manage property and unit images and captions.</p>
    </a>
    <a class="card" href="edit-listing.php?task=amenities">
        <h2>Amenities</h2>
        <p>Choose a listing to update its unit amenities.</p>
    </a>
</div>

<?php include 'footer.php'; ?>
