<?php
require_once __DIR__ . '/common.php';
$page = basename($_SERVER['PHP_SELF']);
$amenitiesPage = $page === 'edit-listing.php' && ($_GET['task'] ?? '') === 'amenities';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape($title) ?> - HomeSC Admin</title>
    <link rel="icon" href="../assets/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../assets/fonts/fonts.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<header class="admin-header">
    <div class="container">
        <a class="logo" href="index.php"><img src="../assets/logo-header.png" alt="HomeSC" width="110" height="34"> <span>Admin</span></a>
        <a class="back-link" href="../index.php">View site &rarr;</a>
    </div>
</header>

<nav class="admin-nav">
    <div class="container">
        <a href="index.php" <?php if ($page == 'index.php') echo 'class="active"'; ?>>Dashboard</a>
        <a href="add-listing.php" <?php if ($page == 'add-listing.php') echo 'class="active"'; ?>>Add Listing</a>
        <a href="edit-listing.php"<?= $page === 'edit-listing.php' && !$amenitiesPage ? ' class="active"' : '' ?>>Edit Listing</a>
        <a href="delete-listing.php" <?php if ($page == 'delete-listing.php') echo 'class="active"'; ?>>Delete Listing</a>
        <a href="properties.php"<?= $page === 'properties.php' ? ' class="active"' : '' ?>>Properties</a>
        <a href="units.php"<?= $page === 'units.php' ? ' class="active"' : '' ?>>Units</a>
        <a href="photos.php"<?= $page === 'photos.php' ? ' class="active"' : '' ?>>Photos</a>
        <a href="edit-listing.php?task=amenities"<?= $amenitiesPage ? ' class="active"' : '' ?>>Amenities</a>
    </div>
</nav>

<main class="container">
<?php if ($adminPreview) { ?>
    <p role="status">Local admin preview. Changes are disabled.</p>
<?php } ?>
