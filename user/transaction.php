<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/role_check.php';

requireRole(['user', 'admin']);

$facilitiesXml = loadXML(FACILITIES_XML);
$facilities = $facilitiesXml !== false ? $facilitiesXml->facility : [];

$errors = [];
$success = '';

// Sticky form values (kept on validation failure so the user
// doesn't have to retype everything).
$form = [
    'fullname' => $_SESSION['fullname'] ?? '',
    'email'    => '',
    'contact'  => '',
    'facility' => '',
    'date'     => '',
    'hours'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form['fullname'] = trim($_POST['fullname'] ?? '');
    $form['email']    = trim($_POST['email'] ?? '');
    $form['contact']  = trim($_POST['contact'] ?? '');
    $form['facility'] = trim($_POST['facility'] ?? '');
    $form['date']     = trim($_POST['date'] ?? '');
    $form['hours']    = trim($_POST['hours'] ?? '');

    // ---- Security Feature 6 / Minimum Validation Requirements ----

    // Required field
    if ($form['fullname'] === '') {
        $errors['fullname'] = 'Name cannot be empty.';
    // Length validation
    } elseif (strlen($form['fullname']) < 3) {
        $errors['fullname'] = 'Name must contain at least 3 characters.';
    }

    // Email validation
    if ($form['email'] === '') {
        $errors['email'] = 'Email cannot be empty.';
    } elseif (!isValidEmail($form['email'])) {
        $errors['email'] = 'Invalid email address.';
    }

    // Contact number validation
    if ($form['contact'] === '') {
        $errors['contact'] = 'Contact number cannot be empty.';
    } elseif (!isValidPhone($form['contact'])) {
        $errors['contact'] = 'Enter a valid contact number (7-15 digits).';
    }

    // Facility must be a required selection that actually exists
    $facilityFound = null;
    if ($form['facility'] === '') {
        $errors['facility'] = 'Please select a facility.';
    } else {
        foreach ($facilities as $f) {
            if ((string)$f->id === $form['facility']) {
                $facilityFound = $f;
                break;
            }
        }
        if ($facilityFound === null) {
            $errors['facility'] = 'Selected facility is not valid.';
        }
    }

    // Date validation - required, and cannot be in the past
    if ($form['date'] === '') {
        $errors['date'] = 'Booking date cannot be empty.';
    } elseif (!isValidFutureDate($form['date'])) {
        $errors['date'] = 'Booking date cannot be in the past.';
    }

    // Number validation - number of hours between 1 and 10
    if ($form['hours'] === '') {
        $errors['hours'] = 'Number of hours cannot be empty.';
    } elseif (!ctype_digit((string)$form['hours']) || (int)$form['hours'] < 1 || (int)$form['hours'] > 10) {
        $errors['hours'] = 'Number of hours must be between 1 and 10.';
    }

    if (empty($errors)) {
        $bookings = loadXML(BOOKINGS_XML);
        if ($bookings === false) {
            $bookings = new SimpleXMLElement('<bookings></bookings>');
        }

        $newId = 1;
        foreach ($bookings->booking as $b) {
            if ((int)$b->id >= $newId) {
                $newId = (int)$b->id + 1;
            }
        }

        $booking = $bookings->addChild('booking');
        $booking->addChild('id', (string)$newId);
        $booking->addChild('username', htmlspecialchars($_SESSION['username']));
        $booking->addChild('fullname', htmlspecialchars($form['fullname']));
        $booking->addChild('email', htmlspecialchars($form['email']));
        $booking->addChild('contact', htmlspecialchars($form['contact']));
        $booking->addChild('facility_name', htmlspecialchars((string)$facilityFound->name));
        $booking->addChild('date', htmlspecialchars($form['date']));
        $booking->addChild('hours', htmlspecialchars($form['hours']));
        $booking->addChild('status', 'Confirmed');
        $booking->addChild('created_at', date('Y-m-d H:i:s'));

        saveXML($bookings, BOOKINGS_XML);

        $success = 'Booking confirmed! You booked ' . htmlspecialchars((string)$facilityFound->name) . ' for ' . htmlspecialchars($form['date']) . '.';

        // Reset the form after a successful, validated submission.
        $form = ['fullname' => $_SESSION['fullname'], 'email' => '', 'contact' => '', 'facility' => '', 'date' => '', 'hours' => ''];
    }
}

// Reload bookings (in case one was just added) for "My Transactions".
$bookingsXml = loadXML(BOOKINGS_XML);
$myBookings = [];
if ($bookingsXml !== false) {
    foreach ($bookingsXml->booking as $b) {
        if ((string)$b->username === $_SESSION['username']) {
            $myBookings[] = $b;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book a Facility - Sports Facility Booking</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>Book a Sports Facility</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">Please fix the errors below.</div>
        <?php endif; ?>

        <form method="POST" action="transaction.php" novalidate>
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($form['fullname']) ?>">
            <?php if (isset($errors['fullname'])): ?><div class="field-error"><?= $errors['fullname'] ?></div><?php endif; ?>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($form['email']) ?>">
            <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>

            <label for="contact">Contact Number</label>
            <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($form['contact']) ?>" placeholder="e.g. 09171234567">
            <?php if (isset($errors['contact'])): ?><div class="field-error"><?= $errors['contact'] ?></div><?php endif; ?>

            <label for="facility">Facility</label>
            <select id="facility" name="facility">
                <option value="">-- Select a facility --</option>
                <?php foreach ($facilities as $f): ?>
                    <option value="<?= (string)$f->id ?>" <?= $form['facility'] === (string)$f->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$f->name) ?> (₱<?= htmlspecialchars((string)$f->rate_per_hour) ?>/hr)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['facility'])): ?><div class="field-error"><?= $errors['facility'] ?></div><?php endif; ?>

            <label for="date">Booking Date</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($form['date']) ?>">
            <?php if (isset($errors['date'])): ?><div class="field-error"><?= $errors['date'] ?></div><?php endif; ?>

            <label for="hours">Number of Hours (1-10)</label>
            <input type="number" id="hours" name="hours" min="1" max="10" value="<?= htmlspecialchars($form['hours']) ?>">
            <?php if (isset($errors['hours'])): ?><div class="field-error"><?= $errors['hours'] ?></div><?php endif; ?>

            <button type="submit">Confirm Booking</button>
        </form>
    </div>

    <div class="card" style="margin-top:24px;">
        <h2>My Transactions</h2>
        <?php if (empty($myBookings)): ?>
            <p>You have no bookings yet.</p>
        <?php else: ?>
        <table>
            <tr><th>Facility</th><th>Date</th><th>Hours</th><th>Status</th><th>Booked On</th></tr>
            <?php foreach ($myBookings as $b): ?>
            <tr>
                <td><?= htmlspecialchars((string)$b->facility_name) ?></td>
                <td><?= htmlspecialchars((string)$b->date) ?></td>
                <td><?= htmlspecialchars((string)$b->hours) ?></td>
                <td><span class="status-pill status-confirmed"><?= htmlspecialchars((string)$b->status) ?></span></td>
                <td><?= htmlspecialchars((string)$b->created_at) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
