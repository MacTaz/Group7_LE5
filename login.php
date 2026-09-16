<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/users_data.php';

// Already logged in? Skip the login form entirely.
if (is_logged_in()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$error   = '';
$notice  = '';

if (isset($_GET['expired'])) {
    $notice = 'Your session has expired. Please log in again.';
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'loggedout') {
    $notice = 'You have been logged out.';
}

// ----- Security Feature 5: Cookie (non-sensitive convenience only) -----
$remembered_username = $_COOKIE['remember_username'] ?? '';

// ----- Security Feature: Max login attempts / lockout -----
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['lockout_until']))  $_SESSION['lockout_until']  = 0;

$locked_out = time() < $_SESSION['lockout_until'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked_out) {

    $username = sanitize_string($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // raw password is only used for verifying, never echoed
    $remember = isset($_POST['remember']);

    // ----- Security Feature 1: Login Validation -----
    if (!validate_required($username) || !validate_required($password)) {
        $error = 'Username and password are required.';
    } else {
        $valid = isset($USERS[$username]) && password_verify($password, $USERS[$username]['password_hash']);

        if ($valid) {
            // ----- Security Feature 2: PHP Session -----
            session_regenerate_id(true); // new session id on login, prevents session fixation
            $_SESSION['username']       = $username;
            $_SESSION['name']           = $USERS[$username]['name'];
            $_SESSION['role']           = $USERS[$username]['role'];
            $_SESSION['last_activity']  = time();
            $_SESSION['login_attempts'] = 0;

            if ($remember) {
                setcookie('remember_username', $username, time() + (30 * 24 * 60 * 60), '/');
            } else {
                setcookie('remember_username', '', time() - 3600, '/');
            }

            header('Location: ' . ($USERS[$username]['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
            exit;
        } else {
            // Generic message only — never say whether username or password was wrong
            $error = 'Invalid username or password.';
            $_SESSION['login_attempts']++;

            if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
                $_SESSION['lockout_until']  = time() + LOCKOUT_TIME;
                $_SESSION['login_attempts'] = 0;
            }
        }
    }
}

if ($locked_out) {
    $error = 'Too many failed login attempts. Please wait a moment before trying again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Sports Facility Booking</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="auth-box">
    <h1>Sports Facility Booking</h1>
    <h2>Login</h2>

    <?php if ($notice): ?><div class="alert alert-notice"><?= htmlspecialchars($notice) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" action="login.php" novalidate>
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($remembered_username) ?>"
               <?= $locked_out ? 'disabled' : 'required' ?>>

        <label>Password</label>
        <input type="password" name="password" <?= $locked_out ? 'disabled' : 'required' ?>>

        <label class="checkbox-label">
            <input type="checkbox" name="remember" <?= $remembered_username ? 'checked' : '' ?>>
            Remember my username
        </label>

        <button type="submit" <?= $locked_out ? 'disabled' : '' ?>>Login</button>
    </form>

    <p class="hint">
        Demo accounts — Admin: <code>admin / admin123</code> &nbsp;|&nbsp;
        User: <code>juan / user123</code>
    </p>
</div>
</body>
</html>
