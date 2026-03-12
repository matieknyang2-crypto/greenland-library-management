<?php
/**
 * Database Configuration
 * IMPORTANT: Update these credentials for your environment
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