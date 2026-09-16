<?php
// ============================================================
// Programmer Names: Agatha Fei Pelayo, Ma Irene Adel, Mari Alessandrae Rosero, Mico Angelo Tazarte
// AUTH / SESSION GUARD FUNCTIONS
// Every protected page must call require_login() or
// require_role() at the very top, BEFORE any HTML is echoed.
// ============================================================

require_once __DIR__ . '/config.php';

function is_logged_in() {
    return isset($_SESSION['username'], $_SESSION['role']);
}

// ----- Security Feature 3: Restricted Pages -----
// Call this at the top of any page that requires a logged-in user.
// $login_path lets pages inside admin/ or user/ point back correctly.
function require_login($login_path = 'login.php') {
    // Prevent the browser from showing a cached protected page after logout
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    if (!is_logged_in()) {
        header('Location: ' . $login_path);
        exit;
    }

    check_session_timeout($login_path);

    // User is active, refresh the timer
    $_SESSION['last_activity'] = time();
}

// ----- Security Feature 4: Role-Based Access -----
// Example: require_role('admin', '../access_denied.php', '../login.php');
function require_role($allowed_role, $denied_path = 'access_denied.php', $login_path = 'login.php') {
    require_login($login_path);

    if ($_SESSION['role'] !== $allowed_role) {
        header('Location: ' . $denied_path);
        exit;
    }
}

// ----- Security Feature 8: Session Timeout -----
function check_session_timeout($login_path = 'login.php') {
    if (isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {

        session_unset();
        session_destroy();
        header('Location: ' . $login_path . '?expired=1');
        exit;
    }
}
