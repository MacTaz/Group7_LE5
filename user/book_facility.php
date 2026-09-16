<?php

// Loads the configuration, authentication, and helper functions required by the booking page.
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Restricts access to logged-in users with the user role.
require_role('user', '../access_denied.php', '../login.php');

// Defines the XML files used to store facility and booking information.
$facilities_file = DATA_DIR . 'facilities.xml';
$bookings_file   = DATA_DIR . 'bookings.xml';

// Loads the available facilities and extracts their names for validation.
$facilities      = read_xml($facilities_file);
$facility_names  = array_column($facilities, 'name');

// Defines the valid time slots that users can select for their bookings.
$time_slots = ['8:00 AM - 10:00 AM', '10:00 AM - 12:00 PM', '1:00 PM - 3:00 PM', '3:00 PM - 5:00 PM'];

// Initializes the error and success message containers.
$errors  = [];
$success = '';

// Initializes form values so entered information can be retained after validation errors.
$full_name    = '';
$email        = '';
$contact      = '';
$facility     = '';
$booking_date = '';
$time_slot    = '';
$participants = '';

// Processes the booking form when it is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieves and sanitizes the values submitted through the booking form.
    $full_name    = sanitize_string($_POST['full_name'] ?? '');
    $email        = sanitize_string($_POST['email'] ?? '');
    $contact      = sanitize_string($_POST['contact'] ?? '');
    $facility     = sanitize_string($_POST['facility'] ?? '');
    $booking_date = sanitize_string($_POST['booking_date'] ?? '');
    $time_slot    = sanitize_string($_POST['time_slot'] ?? '');
    $participants = trim($_POST['participants'] ?? '');

    // Validates the full name and checks its minimum length.
    if (!validate_required($full_name)) {
        $errors[] = 'Full name is required.';
    } elseif (!validate_min_length($full_name, 2)) {
        $errors[] = 'Full name must be at least 2 characters.';
    }

    // Validates that the email address is provided and properly formatted.
    if (!validate_required($email)) {
        $errors[] = 'Email is required.';
    } elseif (!validate_email_format($email)) {
        $errors[] = 'Invalid email address.';
    }

    // Validates that the contact number contains only the required number of digits.
    if (!validate_required($contact)) {
        $errors[] = 'Contact number is required.';
    } elseif (!validate_phone($contact)) {
        $errors[] = 'Contact number must contain 7-15 digits only.';
    }

    // Ensures that the selected facility is one of the available facilities.
    if (!validate_required($facility)) {
        $errors[] = 'Please select a facility.';
    } elseif (!in_array($facility, $facility_names, true)) {
        $errors[] = 'Selected facility is not valid.';
    }

    // Validates that the booking date is provided and is not in the past.
    if (!validate_required($booking_date)) {
        $errors[] = 'Booking date is required.';
    } elseif (!validate_date_not_past($booking_date)) {
        $errors[] = 'Booking date cannot be in the past.';
    }

    // Ensures that the selected time slot is one of the predefined options.
    if (!validate_required($time_slot)) {
        $errors[] = 'Please select a time slot.';
    } elseif (!in_array($time_slot, $time_slots, true)) {
        $errors[] = 'Selected time slot is not valid.';
    }

    // Validates that the number of participants is between 1 and 20.
    if (!validate_required($participants)) {
        $errors[] = 'Number of participants is required.';
    } elseif (!validate_number_range($participants, 1, 20)) {
        $errors[] = 'Number of participants must be between 1 and 20.';
    }

    // Creates and saves the booking when all submitted information passes validation.
    if (empty($errors)) {

        // Loads existing bookings and generates a unique ID for the new booking.
        $bookings = read_xml($bookings_file);
        $new_id = count($bookings) ? max(array_column($bookings, 'id')) + 1 : 1;

        // Adds the new booking information with a pending status.
        $bookings[] = [
            'id'           => $new_id,
            'username'     => $_SESSION['username'],
            'full_name'    => $full_name,
            'email'        => $email,
            'contact'      => $contact,
            'facility'     => $facility,
            'date'         => $booking_date,
            'time_slot'    => $time_slot,
            'participants' => (int)$participants,
            'status'       => 'pending',
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        // Saves the updated booking information to the XML file.
        write_xml($bookings_file, $bookings);

        // Displays a success message and clears the form after a successful booking.
        $success = 'Booking submitted! It is now pending admin approval.';
        $full_name = $email = $contact = $facility = $booking_date = $time_slot = $participants = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book a Facility</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar">

    <!-- Displays the navigation bar and the currently logged-in user's name. -->
    <span> Sports Facility Booking — <?= htmlspecialchars($_SESSION['name']) ?></span>

    <!-- Provides navigation links to the user's available pages and logout function. -->
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="book_facility.php">Book a Facility</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Book a Facility</h1>

        <!-- Displays the remaining session time before automatic expiration. -->
        <span class="timeout-badge" id="sessionBadge">
            Session limit: <strong id="sessionTimer" data-seconds="<?= (int)SESSION_TIMEOUT ?>" data-redirect="../login.php?expired=1">15:00</strong>
        </span>
    </div>

    <!-- Displays validation errors when the submitted booking information is invalid. -->
    <?php foreach ($errors as $e): ?>
        <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <!-- Displays a success message after a booking has been submitted. -->
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <!-- Provides the form for entering and submitting a facility booking. -->
    <form method="POST" action="book_facility.php" novalidate>

        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">

        <label>Contact Number</label>
        <input type="text" name="contact" placeholder="e.g. 09171234567" value="<?= htmlspecialchars($contact) ?>">

        <label>Facility</label>
        <select name="facility">

            <!-- Displays the available facilities and their hourly rates. -->
            <option value="">-- Select Facility --</option>
            <?php foreach ($facilities as $f): ?>
                <option value="<?= htmlspecialchars($f['name']) ?>" <?= $facility === $f['name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($f['name']) ?> (₱<?= number_format($f['rate_per_hour'], 2) ?>/hr)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Booking Date</label>
        <input type="date" name="booking_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($booking_date) ?>">

        <label>Time Slot</label>
        <select name="time_slot">

            <!-- Displays the predefined time slots available for booking. -->
            <option value="">-- Select Time Slot --</option>
            <?php foreach ($time_slots as $slot): ?>
                <option value="<?= htmlspecialchars($slot) ?>" <?= $time_slot === $slot ? 'selected' : '' ?>>
                    <?= htmlspecialchars($slot) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Number of Participants (1-20)</label>
        <input type="number" name="participants" min="1" max="20" value="<?= htmlspecialchars($participants) ?>">

        <button type="submit">Submit Booking</button>
    </form>
</div>

<!-- Loads the JavaScript responsible for the session countdown timer. -->
<script src="../assets/timer.js"></script>
</body>
</html>