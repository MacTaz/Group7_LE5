<?php
// Loads the required system configuration and session settings.
require_once 'includes/config.php';

// Clears all session data and destroys the current session.
$_SESSION = [];
session_unset();
session_destroy();

// Removes the session cookie from the user's browser.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Redirects the user to the login page with a logout notification.
header('Location: login.php?msg=loggedout');
exit;