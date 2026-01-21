<?php
// InfinityFree Database Configuration
// REPLACE THESE WITH YOUR ACTUAL INFINITYFREE DETAILS

define('DB_HOST', 'sql123.infinityfree.net');        // Your DB host from control panel
define('DB_USER', 'if0_12345678');                   // Your DB username from control panel  
define('DB_PASS', 'your_database_password_here');    // Your DB password you set
define('DB_NAME', 'if0_12345678_just_exam');         // Your full database name

// CRITICAL: Generate a random 32+ character string for security
// Use: https://www.random. org/strings/ or generate your own
define('JWT_SECRET', 'CHANGE_THIS_TO_RANDOM_32_CHAR_STRING');

// Security Configuration (keep these)
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Error Reporting (InfinityFree specific)
if ($_SERVER['SERVER_NAME'] === 'localhost' || strpos($_SERVER['SERVER_NAME'], 'infinityfreeapp.com') !== false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-errors.log');
}

// Session Configuration for InfinityFree
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if you have SSL enabled
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Database Connection with PDO
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact administrator.");
}
?>