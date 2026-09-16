<?php
// ============================================================
// Programmer Names: Agatha Fei Pelayo, Ma Irene Adel, Mari Alessandrae Rosero, Mico Angelo Tazarte
// DEMO ACCOUNTS
// For a real system these would be rows in a database table,
// with the password_hash column already stored. Here we hash
// the demo passwords on the fly just so this works out of the
// box with zero setup.
//
// Demo login credentials:
//   Admin ->  username: admin   password: admin123
//   User  ->  username: juan    password: user123
// ============================================================

$USERS = [
    'admin' => [
        'name'          => 'Admin User',
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'role'          => 'admin',
    ],
    'juan' => [
        'name'          => 'Juan Dela Cruz',
        'password_hash' => password_hash('user123', PASSWORD_DEFAULT),
        'role'          => 'user',
    ],
];
