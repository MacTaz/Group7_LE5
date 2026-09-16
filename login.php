<?php
require_once __DIR__ . '/includes/security.php';

// If already logged in, don't show the login form again.
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$errors = [];
$usernameValue = $_COOKIE['remember_username'] ?? '';

// A message can arrive from session_check.php (e.g. "Please login first.",
// "Your session has expired. Please login again.") via the query string.
$infoMessage = isset($_GET['msg']) ? sanitize($_GET['msg']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    // ---- Security Feature 1: Login Validation ----
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $result = verifyLogin($username, $password);

        if ($result === false) {
            // Generic message on purpose: never reveal WHICH field was wrong.
            $errors[] = 'Invalid username or password.';
        } else {
            // ---- Security Feature 2: PHP Session ----
            session_regenerate_id(true); // prevent session fixation
            $_SESSION['username']      = $result['username'];
            $_SESSION['role']          = $result['role'];
            $_SESSION['fullname']      = $result['fullname'];
            $_SESSION['last_activity'] = time();

            // ---- Security Feature 5: Cookie (non-sensitive only) ----
            if ($remember) {
                // 30 days, username ONLY. Never the password.
                setcookie('remember_username', $result['username'], time() + (30 * 24 * 60 * 60), '/');
            } else {
                setcookie('remember_username', '', time() - 3600, '/');
            }

            header('Location: ' . ($result['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
            exit;
        }
    }

    $usernameValue = sanitize($username);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Sports Facility Booking System</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <h1>🏟️ Sports Facility Booking</h1>
        <div class="subtitle">Please sign in to continue</div>

        <?php if ($infoMessage): ?>
            <div class="alert alert-info"><?= htmlspecialchars($infoMessage) ?></div>
        <?php endif; ?>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="login.php" novalidate>
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($usernameValue) ?>" autocomplete="username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password">

            <div class="checkbox-row">
                <input type="checkbox" id="remember" name="remember" value="1"
                       <?= !empty($usernameValue) ? 'checked' : '' ?>>
                <label for="remember" style="margin:0;">Remember my username</label>
            </div>

            <button type="submit">LOGIN</button>
        </form>

        <p class="small-note">
            Sample accounts (classroom testing only):<br>
            Admin: DOOM1 &nbsp;|&nbsp; User: Agathahaha
        </p>
    </div>
</div>
</body>
</html>
