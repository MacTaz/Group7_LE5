<?php
// Loads the required configuration, authentication, functions, and user data.
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/users_data.php';

// Redirects already logged-in users to their appropriate dashboard.
if (is_logged_in()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

// Initializes the error and notification messages.
$error  = '';
$notice = '';

// Displays a notification when the session expires or the user logs out.
if (isset($_GET['expired'])) {
    $notice = 'Your session has expired. Please log in again.';
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'loggedout') {
    $notice = 'You have been logged out.';
}

// Retrieves the remembered username from the browser cookie.
$remembered_username = $_COOKIE['remember_username'] ?? '';

// Initializes login attempt tracking and determines if the user is locked out.
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['lockout_until']))  $_SESSION['lockout_until']  = 0;

$locked_out = time() < $_SESSION['lockout_until'];

// Processes the login form when it is submitted and the user is not locked out.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked_out) {

    $username = sanitize_string($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validates that the username and password fields are not empty.
    if (!validate_required($username) || !validate_required($password)) {
        $error = 'Username and password are required.';
    } else {

        // Verifies the username and password against the stored account information.
        $valid = isset($USERS[$username]) && password_verify($password, $USERS[$username]['password_hash']);

        if ($valid) {
            // Creates a secure session and stores the authenticated user's information.
            session_regenerate_id(true);
            $_SESSION['username']       = $username;
            $_SESSION['name']           = $USERS[$username]['name'];
            $_SESSION['role']           = $USERS[$username]['role'];
            $_SESSION['last_activity']  = time();
            $_SESSION['login_attempts'] = 0;

            // Saves or removes the username cookie based on the user's preference.
            if ($remember) {
                setcookie('remember_username', $username, time() + (30 * 24 * 60 * 60), '/');
            } else {
                setcookie('remember_username', '', time() - 3600, '/');
            }

            // Redirects the user to the dashboard corresponding to their role.
            header('Location: ' . ($USERS[$username]['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
            exit;
        } else {
            // Displays a generic error and records the failed login attempt.
            $error = 'Invalid username or password.';
            $_SESSION['login_attempts']++;

            // Temporarily locks the user out after too many failed login attempts.
            if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
                $_SESSION['lockout_until']  = time() + LOCKOUT_TIME;
                $_SESSION['login_attempts'] = 0;
            }
        }
    }
}

// Displays a lockout message when the maximum login attempts have been exceeded.
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

    <!-- Displays login notifications and error messages. -->
    <?php if ($notice): ?>
        <div class="alert alert-notice">
            <?= htmlspecialchars($notice) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Provides the username, password, and remember-me options for login. -->
    <form method="POST" action="login.php" novalidate>

        <label>Username</label>
        <input
            type="text"
            name="username"
            value="<?= htmlspecialchars($remembered_username) ?>"
            <?= $locked_out ? 'disabled' : 'required' ?>
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            <?= $locked_out ? 'disabled' : 'required' ?>
        >

        <label class="checkbox-label">
            <input
                type="checkbox"
                name="remember"
                <?= $remembered_username ? 'checked' : '' ?>
            >
            Remember my username
        </label>

        <button type="submit" <?= $locked_out ? 'disabled' : '' ?>>
            Login
        </button>

    </form>

    <!-- Displays the demo accounts available for testing the system. -->
    <p class="hint">
        Demo accounts — Admin: <code>admin / admin123</code>
        &nbsp;|&nbsp;
        User: <code>juan / user123</code>
    </p>

</div>

</body>
</html>