<?php
/**
 * role_check.php
 * ---------------------------------------------------------
 * Include this AFTER session_check.php on any page that is
 * restricted to a specific role (e.g. admin-only pages).
 *
 * This is the piece that satisfies the activity's most
 * important test: a logged-in Regular User must NOT be able
 * to open an admin page just by typing its URL. The check
 * happens here, in PHP, not by hiding a button in HTML.
 *
 * Usage:
 *   require_once __DIR__ . '/../includes/session_check.php';
 *   require_once __DIR__ . '/../includes/role_check.php';
 *   requireRole('admin');
 * ---------------------------------------------------------
 */

require_once __DIR__ . '/security.php';

/**
 * Redirect to the Access Denied page unless the logged-in
 * user's role matches (one of) the allowed role(s).
 *
 * @param string|array $allowedRoles  e.g. 'admin' or ['admin','user']
 */
function requireRole($allowedRoles) {
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
        header('Location: ' . basePrefix() . 'unauthorized.php');
        exit;
    }
}
