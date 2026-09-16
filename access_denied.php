<?php
// Loads the configuration and authentication functions.
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Checks whether the user is logged in and determines the appropriate return link.
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
    <!-- Displays the access denied message. -->
    <h1>🚫 Access Denied</h1>
    <p>You do not have permission to view that page.</p>

    <!-- Displays the user's current role when they are logged in. -->
    <?php if ($is_in): ?> 
        <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['role']) ?></strong>, 
        which does not have access to this section.</p> 
    <?php endif; ?>

    <!-- Provides a link back to the appropriate page. -->
    <a href="<?= $back_link ?>">Go back</a>
</div>
</body>
</html>