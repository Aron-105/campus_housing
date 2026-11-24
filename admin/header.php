<?php
require_once __DIR__ . '/common.php';
$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title; ?> - HomeSC Admin</title>
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
        <a href="edit-listing.php" <?php if ($page == 'edit-listing.php') echo 'class="active"'; ?>>Edit Listing</a>
        <a href="delete-listing.php" <?php if ($page == 'delete-listing.php') echo 'class="active"'; ?>>Delete Listing</a>
    </div>
</nav>

<main class="container">
