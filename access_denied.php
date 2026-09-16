<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$is_in = is_logged_in();
$back_link = $is_in
    ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php')
    : 'login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Access Denied</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="auth-box">
    <h1>🚫 Access Denied</h1>
    <p>You do not have permission to view that page.</p>
    <?php if ($is_in): ?>
        <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['role']) ?></strong>,
        which does not have access to this section.</p>
    <?php endif; ?>
    <a href="<?= $back_link ?>">Go back</a>
</div>
</body>
</html>
