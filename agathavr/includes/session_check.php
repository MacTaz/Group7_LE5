<?php
/**
 * session_check.php
 * ---------------------------------------------------------
 * Include this file at the TOP of every page that requires
 * the visitor to be logged in (Security Feature 3).
 *
 * It does two things:
 *   1. Confirms $_SESSION['username'] / ['role'] exist.
 *      If not -> the visitor is just a Guest -> send to login.
 *   2. Confirms the session has not expired from inactivity
 *      (Security Feature 8 - Session Timeout).
 *
 * IMPORTANT: This check happens in PHP on the server. Even if
 * a user types the page URL directly into the browser, this
 * file still runs first and still blocks them. Hiding a menu
 * link on the front-end is NOT enough (Rule 2).
 * ---------------------------------------------------------
 */

require_once __DIR__ . '/security.php';

// ---- 1. Is anyone logged in at all? ----
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header('Location: ' . basePrefix() . 'login.php?msg=' . urlencode('Please login first.'));
    exit;
}

// ---- 2. Has the session been idle too long? ----
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {

    // Destroy the expired session completely.
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']);
    }
    session_destroy();

    header('Location: ' . basePrefix() . 'login.php?msg=' .
        urlencode('Your session has expired. Please login again.'));
    exit;
}

// Still active -> refresh the "last activity" timestamp.
$_SESSION['last_activity'] = time();
