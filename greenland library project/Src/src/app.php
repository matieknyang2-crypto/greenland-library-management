<?php
/**
 * Application Configuration
 */

return [
    'app_name' => 'Greenland Secondary School Library Management System',
    'app_short_name' => 'GSSLMS',
    'app_version' => '1.0.0',
    'app_url' => 'http://localhost/library/public',
    
    // Session configuration
    'session_lifetime' => 7200, // 2 hours in seconds
    'session_name' => 'GSSLMS_SESSION',
    
    // Timezone
    'timezone' => 'Africa/Nairobi',
    
    // Date format
    'date_format' => 'Y-m-d',
    'datetime_format' => 'Y-m-d H:i:s',
    'display_date_format' => 'd M Y',
    'display_datetime_format' => 'd M Y H:i',
    
    // Pagination
    'items_per_page' => 10,
    'max_pagination_links' => 5,
    
    // File upload
    'upload_path' => __DIR__ . '/../public/uploads/',
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif'],
    'max_upload_size' => 2097152, // 2MB in bytes
    
    // Security
    'password_min_length' => 8,
    'session_regenerate_time' => 300, // 5 minutes
    'max_login_attempts' => 5,
    'login_lockout_time' => 900, // 15 minutes
    
    // Library settings (defaults - can be overridden in database)
    'borrowing_period_days' => 14,
    'max_books_per_student' => 3,
    'max_books_per_teacher' => 5,
    'fine_per_day' => 10.00,
    'max_renewal_count' => 2,
    'reservation_expiry_days' => 3,
    
    // Email settings (if implementing email notifications)
    'email_enabled' => false,
    'email_from' => 'library@greenlandsecondary.edu',
    'email_from_name' => 'Greenland Library',
    
    // SMS settings (if implementing SMS notifications)
    'sms_enabled' => false,
    'sms_api_key' => '',
    
    // Maintenance mode
    'maintenance_mode' => false,
    'maintenance_message' => 'System is under maintenance. Please check back later.',
    
    // Debug mode (set to false in production)
    'debug' => true,
    'log_errors' => true,
    'error_log_file' => __DIR__ . '/../logs/error.log',
];