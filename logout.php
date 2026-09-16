<?php
require_once 'includes/config.php';

// ----- Security Feature 7: Logout -----
$_SESSION = [];              // clear session data
session_unset();
session_destroy();           // destroy the session on the server

// Also remove the session cookie from the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

header('Location: login.php?msg=loggedout');
exit;
