<?php
require_once __DIR__ . '/includes/session_check.php';

// This file just routes a logged-in user to the dashboard that
// matches their role. The ACTUAL access control for each
// destination is enforced again inside admin/dashboard.php and
// user/dashboard.php via role_check.php - this redirect alone
// is not what protects those pages.
if ($_SESSION['role'] === 'admin') {
    header('Location: admin/dashboard.php');
} else {
    header('Location: user/dashboard.php');
}
exit;
