<?php
// ============================================================
// Programmer Names: Agatha Fei Pelayo, Ma Irene Adel, Mari Alessandrae Rosero, Mico Angelo Tazarte
// GLOBAL CONFIG
// Starts the PHP session and defines settings used everywhere.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----- Security Feature: Session Timeout -----
define('SESSION_TIMEOUT', 15 * 60); // 15 minutes, in seconds

// ----- Security Feature: Max login tries / lockout -----
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_TIME', 60); // seconds to wait after too many failed tries

// Where our "database" xml files live
define('DATA_DIR', __DIR__ . '/../data/');
