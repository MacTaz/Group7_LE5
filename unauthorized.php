<?php
require_once __DIR__ . '/includes/security.php';

$backLink = 'login.php';
if (isset($_SESSION['role'])) {
    $backLink = $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Access Denied</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="denied-wrapper">
    <div class="denied-card">
        <div class="icon">⛔</div>
        <h1>ACCESS DENIED</h1>
        <p>You do not have permission to access this page.</p>
        <a class="btn" href="<?= htmlspecialchars($backLink) ?>">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
