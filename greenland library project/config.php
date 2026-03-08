<?php
/**
 * Greenland Library Management System
 * Database Configuration
 * 
 * @author Matik Nyang 667161
 * @date February 2026
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'greenland_library');
define('DB_USER', 'greenland_user');
define('DB_PASS', 'your_secure_password_here'); // Change this!
define('DB_CHARSET', 'utf8mb4');

// System configuration
define('SITE_NAME', 'Greenland Secondary Library Management System');
define('SITE_URL', 'http://localhost/greenland_lms');
define('TIMEZONE', 'Africa/Juba');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Database connection class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    public $conn;
    
    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please contact administrator.'
            ]));
        }
        
        return $this->conn;
    }
}

/**
 * Send JSON response
 */
function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate required fields
 */
function validateRequired($fields, $data) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Log activity
 */
function logActivity($conn, $userId, $activityType, $description, $ipAddress = null) {
    try {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $stmt = $conn->prepare("
            INSERT INTO activity_log (user_id, activity_type, description, ip_address) 
            VALUES (:user_id, :activity_type, :description, :ip_address)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':activity_type' => $activityType,
            ':description' => $description,
            ':ip_address' => $ipAddress
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Activity Log Error: " . $e->getMessage());
        return false;
    }
}
?>
