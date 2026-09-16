<?php

// Loads the configuration and authentication functions.
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Restricts this page to users with the "user" role.
require_role('user', '../access_denied.php', '../login.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard</title>
<link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<nav class="navbar">

    <!-- Displays the navigation links and the logged-in user's name. -->
    <span> Sports Facility Booking — <?= htmlspecialchars($_SESSION['name']) ?></span>

    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="book_facility.php">Book a Facility</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>

</nav>

<div class="container">

    <div class="page-header">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>

        <!-- Displays the remaining session time before automatic expiration. -->
        <span class="timeout-badge" id="sessionBadge">
            Session limit: <strong id="sessionTimer" data-seconds="<?= (int)SESSION_TIMEOUT ?>" data-redirect="../login.php?expired=1">15:00</strong>
        </span>
    </div>

    <!-- Provides a brief introduction to the available user functions. -->
    <p>Use the links above to book a facility or check your existing bookings.</p>

    <!-- Provides quick access to facility booking and existing bookings. -->
    <div class="card-grid">
        <a class="card" href="book_facility.php">
            <h3>Book a Facility</h3>
            <p>Reserve a court, pool, or field for your activity.</p>
        </a>

        <a class="card" href="my_bookings.php">
            <h3>My Bookings</h3>
            <p>See the status of the facilities you've booked.</p>
        </a>
    </div>

</div>

<!-- Loads the JavaScript session countdown timer. -->
<script src="../assets/timer.js"></script>

</body>
</html>