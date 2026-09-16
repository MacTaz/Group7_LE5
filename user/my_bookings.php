<?php

// Loads the configuration, authentication, and helper functions.
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Restricts this page to users with the "user" role.
require_role('user', '../access_denied.php', '../login.php');

// Loads all booking records from the XML data file.
$bookings = read_xml(DATA_DIR . 'bookings.xml');

// Filters the bookings so users can only view their own transactions.
$my_bookings = array_filter($bookings, function ($b) {
    return $b['username'] === $_SESSION['username'];
});

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Bookings</title>
<link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<nav class="navbar">

    <!-- Displays the navigation links and the logged-in user's name. -->
    <span>Sports Facility Booking — <?= htmlspecialchars($_SESSION['name']) ?></span>

    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="book_facility.php">Book a Facility</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>

</nav>

<div class="container">

    <div class="page-header">
        <h1>My Bookings</h1>

        <!-- Displays the remaining session time before automatic expiration. -->
        <span class="timeout-badge" id="sessionBadge">
            Session limit: <strong id="sessionTimer" data-seconds="<?= (int)SESSION_TIMEOUT ?>" data-redirect="../login.php?expired=1">15:00</strong>
        </span>
    </div>

    <!-- Displays the user's booking records and their current status. -->
    <table>
        <tr><th>Facility</th><th>Date</th><th>Time</th><th>Participants</th><th>Status</th></tr>

        <!-- Displays a message when the user has no existing bookings. -->
        <?php if (empty($my_bookings)): ?>
            <tr><td colspan="5">You have no bookings yet. <a href="book_facility.php">Book one now.</a></td></tr>
        <?php endif; ?>

        <?php foreach ($my_bookings as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['facility']) ?></td>
            <td><?= htmlspecialchars($b['date']) ?></td>
            <td><?= htmlspecialchars($b['time_slot']) ?></td>
            <td><?= (int)$b['participants'] ?></td>
            <td><span class="status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
    </table>

</div>

<!-- Loads the JavaScript session countdown timer. -->
<script src="../assets/timer.js"></script>

</body>
</html>