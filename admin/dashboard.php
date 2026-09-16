<?php

// Loads the configuration and authentication functions.
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Restricts this page to users with the "admin" role.
require_role('admin', '../access_denied.php', '../login.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<nav class="navbar">

    <!-- Displays the admin navigation links and the logged-in administrator's name. -->
    <span>Admin Panel — <?= htmlspecialchars($_SESSION['name']) ?></span>

    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_facilities.php">Manage Facilities</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>

</nav>

<div class="container">

    <div class="page-header">
        <h1>Admin Dashboard</h1>

        <!-- Displays the remaining session time before automatic expiration. -->
        <span class="timeout-badge" id="sessionBadge">
            Session limit: <strong id="sessionTimer" data-seconds="<?= (int)SESSION_TIMEOUT ?>" data-redirect="../login.php?expired=1">15:00</strong>
        </span>
    </div>

    <!-- Provides an overview of the functions available to the administrator. -->
    <p>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>! From here you can manage facilities and review bookings.</p>

    <!-- Provides quick access to facility and booking management. -->
    <div class="card-grid">
        <a class="card" href="manage_facilities.php">
            <h3>Manage Facilities</h3>
            <p>Add or view sports facilities available for booking.</p>
        </a>

        <a class="card" href="manage_bookings.php">
            <h3>Manage Bookings</h3>
            <p>View all bookings and approve or reject them.</p>
        </a>
    </div>

</div>

<!-- Loads the JavaScript session countdown timer. -->
<script src="../assets/timer.js"></script>

</body>
</html>