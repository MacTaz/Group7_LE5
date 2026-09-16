<?php
/**
 * navbar.php
 * ---------------------------------------------------------
 * Shared navigation bar. Displays different links depending
 * on $_SESSION['role'].
 *
 * REMINDER (Section 8 - "Important test"): hiding these links
 * from a Regular User is only a convenience/UI feature. It is
 * NOT what stops a Regular User from opening an admin page.
 * That real protection lives in role_check.php -> requireRole(),
 * which every admin/*.php file calls on its own.
 * ---------------------------------------------------------
 */
$prefix = basePrefix();
$role = $_SESSION['role'] ?? '';
$fullname = $_SESSION['fullname'] ?? ($_SESSION['username'] ?? '');
?>
<div class="navbar">
    <div class="brand">🏟️ Sports Facility Booking</div>
    <nav>
        <?php if ($role === 'admin'): ?>
            <a href="<?= $prefix ?>admin/dashboard.php">Dashboard</a>
            <a href="<?= $prefix ?>admin/manage.php">Manage Bookings</a>
        <?php elseif ($role === 'user'): ?>
            <a href="<?= $prefix ?>user/dashboard.php">Dashboard</a>
            <a href="<?= $prefix ?>user/transaction.php">Book a Facility</a>
        <?php endif; ?>
        <a href="<?= $prefix ?>logout.php">Logout</a>
        <span>Welcome, <?= htmlspecialchars($fullname) ?>
            <span class="role-badge"><?= htmlspecialchars(strtoupper($role)) ?></span>
        </span>
    </nav>
</div>
