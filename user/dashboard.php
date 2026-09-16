<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// ----- Security Feature 3 & 4: Restricted Page + Role-Based Access -----
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
        <span class="timeout-badge" id="sessionBadge">
            Session limit: <strong id="sessionTimer" data-seconds="<?= (int)SESSION_TIMEOUT ?>" data-redirect="../login.php?expired=1">15:00</strong>
        </span>
    </div>
    <p>Use the links above to book a facility or check your existing bookings.</p>

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
<script src="../assets/timer.js"></script>
</body>
</html>
