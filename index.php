<?php
/*
    Programmer's Details:
    Name: [Ma. Irene Adel, Agatha Fei Pelayo, Mari Alessandrae Rosero,
    Mico Angelo Tazarte
    Date Created: September 16, 2026
    Problem Description:
    Serves as the entry point of the system and redirects users
    to the appropriate page based on their login status and role.
*/

require_once 'includes/config.php';
require_once 'includes/auth.php';

if (is_logged_in()) {
    check_session_timeout('login.php');
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
} else {
    header('Location: login.php');
}
exit;