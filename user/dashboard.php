<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/role_check.php';

// A regular user OR an admin may view this page.
requireRole(['user', 'admin']);

// Count this user's own bookings from bookings.xml.
$bookings = loadXML(BOOKINGS_XML);
$myBookings = [];
if ($bookings !== false) {
    foreach ($bookings->booking as $b) {
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
<title>User Dashboard - Sports Facility Booking</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?>!</h2>
        <p>Role: Regular User</p>

        <div class="dash-grid">
            <div class="stat-box">
                <div class="num"><?= count($myBookings) ?></div>
                <div class="label">My Bookings</div>
            </div>
            <div class="stat-box">
                <div class="num">5</div>
                <div class="label">Facilities Available</div>
            </div>
        </div>

        <p>Use <strong>Book a Facility</strong> in the menu above to reserve a court or
        pool slot, and to view your own booking history.</p>

        <a class="btn" href="transaction.php">Book a Facility</a>
    </div>
</div>
</body>
</html>
