<?php
/**
 * Database Configuration Template
 * Copy this file to database.php and update with your credentials
 */

return [
    'host' => 'localhost',
    'database' => 'greenland_library',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'port' => 3306,
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];