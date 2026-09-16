<?php
/**
 * security.php
 * ---------------------------------------------------------
 * Central security/config file for the Sports Facility
 * Booking System.
 *
 * Responsibilities:
 *  - Start the PHP session with safe cookie parameters
 *  - Define the session timeout (Security Feature 8)
 *  - Provide input sanitization / validation helpers
 *  - Provide XML read/write helpers (Security Feature: XML data source)
 *  - Provide the login verification function
 *
 * Rule followed: "Do not trust the browser." All checks in
 * this file run on the SERVER, not just in the HTML/JS.
 * ---------------------------------------------------------
 */

// ---- Session timeout setting (Security Feature 8) ----
// 15 minutes, in seconds, as recommended by the activity sheet.
define('SESSION_TIMEOUT', 15 * 60);

// ---- Paths to the XML data source (Section 18 requirement) ----
define('USERS_XML', __DIR__ . '/../xml/users.xml');
define('FACILITIES_XML', __DIR__ . '/../xml/facilities.xml');
define('BOOKINGS_XML', __DIR__ . '/../xml/bookings.xml');

/**
 * Works out the relative path prefix needed to reach the
 * project root from wherever the current script lives, so
 * that redirects work whether the file is in / , /admin/ or
 * /user/. Avoids hardcoding an absolute site URL.
 */
function basePrefix() {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    if (strpos($script, '/admin/') !== false || strpos($script, '/user/') !== false) {
        return '../';
    }
    return '';
}

// ---- Start session securely before any output ----
if (session_status() === PHP_SESSION_NONE) {
    // Harden the session cookie a little (basic security practice).
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,   // JS cannot read the session cookie
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Sanitize a piece of text input.
 * Trims whitespace and converts special characters to HTML
 * entities to help prevent stored/reflected XSS.
 */
function sanitize($value) {
    $value = trim($value ?? '');
    $value = stripslashes($value);
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    return $value;
}

/**
 * Load an XML file into a SimpleXMLElement.
 * Returns false on failure instead of throwing a fatal error.
 */
function loadXML($path) {
    if (!file_exists($path)) {
        return false;
    }
    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($path);
    return $xml ?: false;
}

/**
 * Save a SimpleXMLElement back to disk with nice formatting.
 */
function saveXML(SimpleXMLElement $xml, $path) {
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    return $dom->save($path);
}

/**
 * Look up a user record in users.xml by username.
 * Returns a SimpleXMLElement for the user, or null if not found.
 */
function findUserByUsername($username) {
    $xml = loadXML(USERS_XML);
    if ($xml === false) {
        return null;
    }
    foreach ($xml->user as $user) {
        if ((string)$user->username === $username) {
            return $user;
        }
    }
    return null;
}

/**
 * Verify login credentials against users.xml.
 *
 * Returns an array ['username' => ..., 'role' => ..., 'fullname' => ...]
 * on success, or false on failure. A single generic failure value is
 * returned deliberately -- the caller must NOT reveal whether the
 * username or the password was the problem (Security Feature 1).
 */
function verifyLogin($username, $password) {
    $user = findUserByUsername($username);
    if ($user === null) {
        return false; // account does not exist
    }
    if (!password_verify($password, (string)$user->password)) {
        return false; // wrong password
    }
    return [
        'username' => (string)$user->username,
        'role'     => (string)$user->role,
        'fullname' => (string)$user->fullname,
    ];
}

/**
 * Basic validators used by the booking form (Security Feature 6).
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone($phone) {
    // Simple PH-style contact number check: 7-15 digits, optional +
    return (bool) preg_match('/^\+?[0-9]{7,15}$/', $phone);
}

function isValidFutureDate($date) {
    $today = strtotime(date('Y-m-d'));
    $given = strtotime($date);
    if ($given === false) {
        return false;
    }
    return $given >= $today;
}
