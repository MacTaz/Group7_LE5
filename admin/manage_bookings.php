<?php

// Loads the configuration, authentication, and helper functions.
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Restricts this page to users with the "admin" role.
require_role('admin', '../access_denied.php', '../login.php');

// Defines the booking data file and loads all existing bookings.
$bookings_file = DATA_DIR . 'bookings.xml';
$bookings = read_xml($bookings_file);

// Processes the administrator's approve or reject action for a booking.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['action'])) {

    $id     = (int)$_POST['booking_id'];
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    // Finds the selected booking and updates its status.
    foreach ($bookings as &$b) {
        if ((int)$b['id'] === $id) {
            $b['status'] = $action;
            break;
        }
    }

    unset($b);

    // Saves the updated booking information to the XML file.
    write_xml($bookings_file, $bookings);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Bookings</title>
<link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<nav class="navbar">

    <!-- Displays the admin navigation links and the logged-in administrator's name. -->
    <span> Admin Panel — <?= htmlspecialchars($_SESSION['name']) ?></span>

    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_facilities.php">Manage Facilities</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>

</nav>

<div class="container">

    <div class="page-header">
        <h1>All Bookings</h1>

        <!-- Displays the remaining session time before automatic expiration. -->
        <span class="timeout-badge" id="sessionBadge">
            Session limit: <strong id="sessionTimer" data-seconds="<?= (int)SESSION_TIMEOUT ?>" data-redirect="../login.php?expired=1">15:00</strong>
        </span>
    </div>

    <!-- Displays all booking records and allows the admin to manage pending bookings. -->
    <table>
        <tr>
            <th>ID</th><th>User</th><th>Facility</th><th>Date</th><th>Time</th>
            <th>Participants</th><th>Status</th><th>Action</th>
        </tr>

        <!-- Displays a message when there are no bookings available. -->
        <?php if (empty($bookings)): ?>
            <tr><td colspan="8">No bookings yet.</td></tr>
        <?php endif; ?>

        <?php foreach ($bookings as $b): ?>
        <tr>
            <td><?= (int)$b['id'] ?></td>
            <td><?= htmlspecialchars($b['username']) ?></td>
            <td><?= htmlspecialchars($b['facility']) ?></td>
            <td><?= htmlspecialchars($b['date']) ?></td>
            <td><?= htmlspecialchars($b['time_slot']) ?></td>
            <td><?= (int)$b['participants'] ?></td>
            <td><span class="status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span></td>

            <td>
                <!-- Shows approve and reject buttons only for bookings that are still pending. -->
                <?php if ($b['status'] === 'pending'): ?>
                <form method="POST" action="manage_bookings.php" style="display:inline">
                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                    <button type="submit" name="action" value="approve" class="btn-small">Approve</button>
                    <button type="submit" name="action" value="reject" class="btn-small btn-danger">Reject</button>
                </form>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>

        </tr>
        <?php endforeach; ?>
    </table>

</div>

<!-- Loads the JavaScript session countdown timer. -->
<script src="../assets/timer.js"></script>

</body>
</html>