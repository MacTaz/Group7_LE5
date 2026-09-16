<?php
require_once __DIR__ . '/../includes/session_check.php';
require_once __DIR__ . '/../includes/role_check.php';

// STRICT: only 'admin' may pass this line. A Regular User who
// manually types this URL is redirected to unauthorized.php.
requireRole('admin');

$bookingsXml = loadXML(BOOKINGS_XML);
$totalBookings = $bookingsXml !== false ? count($bookingsXml->booking) : 0;

$usersXml = loadXML(USERS_XML);
$totalUsers = 0;
if ($usersXml !== false) {
    foreach ($usersXml->user as $u) {
        if ((string)$u->role === 'user') {
            $totalUsers++;
        }
    }
}

$facilitiesXml = loadXML(FACILITIES_XML);
$totalFacilities = $facilitiesXml !== false ? count($facilitiesXml->facility) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Sports Facility Booking</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>Admin Dashboard</h2>
        <p>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?>! Role: Administrator</p>

        <div class="dash-grid">
            <div class="stat-box">
                <div class="num"><?= $totalBookings ?></div>
                <div class="label">Total Bookings</div>
            </div>
            <div class="stat-box">
                <div class="num"><?= $totalUsers ?></div>
                <div class="label">Registered Users</div>
            </div>
            <div class="stat-box">
                <div class="num"><?= $totalFacilities ?></div>
                <div class="label">Facilities</div>
            </div>
        </div>

        <a class="btn" href="manage.php">Manage Bookings</a>
    </div>
</div>
</body>
</html>
