<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// ----- Security Feature 3 & 4: Restricted Page + Role-Based Access -----
require_role('admin', '../access_denied.php', '../login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<nav class="navbar">
    <span>⚙️ Admin Panel — <?= htmlspecialchars($_SESSION['name']) ?></span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_facilities.php">Manage Facilities</a>
        <a href="manage_bookings.php">Manage Bookings</a>
        <a href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['name']) ?>! From here you can manage facilities and review bookings.</p>

    <div class="card-grid">
        <a class="card" href="manage_facilities.php">
            <h3>Manage Facilities</h3>
            <p>Add or view sports facilities available for booking.</p>
        </a>
        <a class="card" href="manage_bookings.php">
            <h3>Manage Bookings</h3>
            <p>View all bookings and approve or reject them.</p>
        </a>
    </div>

    <p class="hint">
        Note: even if a regular user were to guess this URL
        (<code>admin/dashboard.php</code>) directly, <code>require_role('admin')</code>
        blocks them and sends them to the Access Denied page.
    </p>
</div>
</body>
</html>
