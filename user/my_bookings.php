<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_role('user', '../access_denied.php', '../login.php');

$bookings = read_xml(DATA_DIR . 'bookings.xml');

// ----- Users can only see their OWN transactions, not everyone's -----
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
    <span>Sports Facility Booking — <?= htmlspecialchars($_SESSION['name']) ?></span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="book_facility.php">Book a Facility</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>My Bookings</h1>
    <table>
        <tr><th>Facility</th><th>Date</th><th>Time</th><th>Participants</th><th>Status</th></tr>
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
</body>
</html>
