<?php
require_once __DIR__ . '/includes/security.php';

// ---- Security Feature 7: Logout ----
// 1. Clear all session variables.
$_SESSION = [];

// 2. Remove the session cookie from the browser.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']);
}

// 3. Destroy the session on the server.
session_destroy();

// Note: remember_username cookie is intentionally left alone here
// (it's a non-sensitive convenience cookie, not part of authentication).

// 4. Return to the login page. Once here, pressing Back on a
// protected page will NOT work because session_check.php on
// that page will detect there is no active session and bounce
// the user back to login.php again.
header('Location: login.php?msg=' . urlencode('You have been logged out.'));
exit;
