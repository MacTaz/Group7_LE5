<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_role('user', '../access_denied.php', '../login.php');

$facilities_file = DATA_DIR . 'facilities.xml';
$bookings_file   = DATA_DIR . 'bookings.xml';
$facilities      = read_xml($facilities_file);
$facility_names  = array_column($facilities, 'name');

// Fixed list so a user can't submit an arbitrary time string
$time_slots = ['8:00 AM - 10:00 AM', '10:00 AM - 12:00 PM', '1:00 PM - 3:00 PM', '3:00 PM - 5:00 PM'];

$errors  = [];
$success = '';

// Keep entered values so the form doesn't clear on error
$full_name    = '';
$email        = '';
$contact      = '';
$facility     = '';
$booking_date = '';
$time_slot    = '';
$participants = '';

// ----- Security Feature 6 & minimum validation requirements -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name    = sanitize_string($_POST['full_name'] ?? '');
    $email        = sanitize_string($_POST['email'] ?? '');
    $contact      = sanitize_string($_POST['contact'] ?? '');
    $facility     = sanitize_string($_POST['facility'] ?? '');
    $booking_date = sanitize_string($_POST['booking_date'] ?? '');
    $time_slot    = sanitize_string($_POST['time_slot'] ?? '');
    $participants = trim($_POST['participants'] ?? '');

    // Required field
    if (!validate_required($full_name)) {
        $errors[] = 'Full name is required.';
    } elseif (!validate_min_length($full_name, 2)) {
        $errors[] = 'Full name must be at least 2 characters.';
    }

    // Email validation
    if (!validate_required($email)) {
        $errors[] = 'Email is required.';
    } elseif (!validate_email_format($email)) {
        $errors[] = 'Invalid email address.';
    }

    // "Enter a number" -> numeric input, validated as a real number/string of digits
    if (!validate_required($contact)) {
        $errors[] = 'Contact number is required.';
    } elseif (!validate_phone($contact)) {
        $errors[] = 'Contact number must contain 7-15 digits only.';
    }

    // Facility must be one actually offered (never trust the dropdown blindly)
    if (!validate_required($facility)) {
        $errors[] = 'Please select a facility.';
    } elseif (!in_array($facility, $facility_names, true)) {
        $errors[] = 'Selected facility is not valid.';
    }

    // Date validation: booking date cannot be in the past ("common sense": no yesterday bookings)
    if (!validate_required($booking_date)) {
        $errors[] = 'Booking date is required.';
    } elseif (!validate_date_not_past($booking_date)) {
        $errors[] = 'Booking date cannot be in the past.';
    }

    // Time slot must be one of the fixed options
    if (!validate_required($time_slot)) {
        $errors[] = 'Please select a time slot.';
    } elseif (!in_array($time_slot, $time_slots, true)) {
        $errors[] = 'Selected time slot is not valid.';
    }

    // Number validation
    if (!validate_required($participants)) {
        $errors[] = 'Number of participants is required.';
    } elseif (!validate_number_range($participants, 1, 20)) {
        $errors[] = 'Number of participants must be between 1 and 20.';
    }

    if (empty($errors)) {
        $bookings = read_xml($bookings_file);
        $new_id = count($bookings) ? max(array_column($bookings, 'id')) + 1 : 1;

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

        write_xml($bookings_file, $bookings);
        $success = 'Booking submitted! It is now pending admin approval.';

        // Clear the form after a successful submit
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
    <span> Sports Facility Booking — <?= htmlspecialchars($_SESSION['name']) ?></span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="book_facility.php">Book a Facility</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>Book a Facility</h1>

    <?php foreach ($errors as $e): ?>
        <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST" action="book_facility.php" novalidate>
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>">

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">

        <label>Contact Number</label>
        <input type="text" name="contact" placeholder="e.g. 09171234567" value="<?= htmlspecialchars($contact) ?>">

        <label>Facility</label>
        <select name="facility">
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
</body>
</html>
