<?php

// Loads the configuration, authentication, and helper functions.
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Restricts this page to users with the "admin" role.
require_role('admin', '../access_denied.php', '../login.php');

// Loads the facility data from the XML file.
$facilities_file = DATA_DIR . 'facilities.xml';
$facilities = read_xml($facilities_file);

// Initializes the error and success message containers.
$errors = [];
$success = '';

// Processes the facility form when it is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieves and sanitizes the submitted facility information.
    $name     = sanitize_string($_POST['name'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');
    $rate     = trim($_POST['rate_per_hour'] ?? '');

    // Validates the facility name and its minimum length.
    if (!validate_required($name)) {
        $errors[] = 'Facility name is required.';
    } elseif (!validate_min_length($name, 3)) {
        $errors[] = 'Facility name must be at least 3 characters.';
    }

    // Validates that the facility capacity is between 1 and 500.
    if (!validate_required($capacity)) {
        $errors[] = 'Capacity is required.';
    } elseif (!validate_number_range($capacity, 1, 500)) {
        $errors[] = 'Capacity must be a number between 1 and 500.';
    }

    // Validates that the hourly rate is within the allowed range.
    if (!validate_required($rate)) {
        $errors[] = 'Rate per hour is required.';
    } elseif (!validate_number_range($rate, 1, 100000)) {
        $errors[] = 'Rate per hour must be a valid positive number.';
    }

    // Creates and saves the new facility when all validation checks pass.
    if (empty($errors)) {
        $new_id = count($facilities) ? max(array_column($facilities, 'id')) + 1 : 1;

        $facilities[] = [
            'id'            => $new_id,
            'name'          => $name,
            'capacity'      => (int)$capacity,
            'rate_per_hour' => (float)$rate,
        ];

        // Saves the updated facility list to the XML file.
        write_xml($facilities_file, $facilities);

        $success = 'Facility added successfully.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Facilities</title>
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
        <h1>Manage Facilities</h1>

        <!-- Displays the remaining session time before automatic expiration. -->
        <span class="timeout-badge" id="sessionBadge">
            Session limit: <strong id="sessionTimer" data-seconds="<?= (int)SESSION_TIMEOUT ?>" data-redirect="../login.php?expired=1">15:00</strong>
        </span>
    </div>

    <!-- Displays validation errors from the submitted facility form. -->
    <?php foreach ($errors as $e): ?>
        <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <!-- Displays a success message after a facility is added. -->
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <h2>Add Facility</h2>

    <!-- Provides the form for adding a new sports facility. -->
    <form method="POST" action="manage_facilities.php" novalidate>
        <label>Facility Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

        <label>Capacity</label>
        <input type="number" name="capacity" min="1" max="500" value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>">

        <label>Rate per Hour (₱)</label>
        <input type="number" step="0.01" name="rate_per_hour" value="<?= htmlspecialchars($_POST['rate_per_hour'] ?? '') ?>">

        <button type="submit">Add Facility</button>
    </form>

    <h2>Existing Facilities</h2>

    <!-- Displays the facilities currently stored in the system. -->
    <table>
        <tr><th>ID</th><th>Name</th><th>Capacity</th><th>Rate/Hour</th></tr>

        <?php foreach ($facilities as $f): ?>
        <tr>
            <td><?= (int)$f['id'] ?></td>
            <td><?= htmlspecialchars($f['name']) ?></td>
            <td><?= (int)$f['capacity'] ?></td>
            <td>₱<?= number_format((float)$f['rate_per_hour'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

</div>

<!-- Loads the JavaScript session countdown timer. -->
<script src="../assets/timer.js"></script>

</body>
</html>