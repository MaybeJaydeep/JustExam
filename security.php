<?php
/**
 * Security Helper Functions
 */

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    return true;
}

/**
 * Sanitize Output (Prevent XSS)
 */
function escape($data) {
    if (is_array($data)) {
        return array_map('escape', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate Email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate Required Fields
 */
function validateRequired($fields) {
    $errors = [];
    foreach ($fields as $field => $value) {
        if (empty(trim($value))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
    }
    return $errors;
}

/**
 * Hash Password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify Password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check Session Timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        if ($elapsed > SESSION_LIFETIME) {
            session_unset();
            session_destroy();
            return false;
        }
    }
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Regenerate Session ID
 */
function regenerateSession() {
    session_regenerate_id(true);
}

/**
 * Check Login Attempts (Brute Force Protection)
 */
function checkLoginAttempts($identifier) {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    if (!isset($_SESSION['login_attempts'][$identifier])) {
        $_SESSION['login_attempts'][$identifier] = [
            'count' => 0,
            'last_attempt' => time()
        ];
    }
    
    $attempts = $_SESSION['login_attempts'][$identifier];
    
    // Reset after timeout
    if (time() - $attempts['last_attempt'] > LOGIN_TIMEOUT) {
        $_SESSION['login_attempts'][$identifier] = [
            'count' => 0,
            'last_attempt' => time()
        ];
        return true;
    }
    
    // Check if locked out
    if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
        return false;
    }
    
    return true;
}

/**
 * Record Failed Login Attempt
 */
function recordFailedLogin($identifier) {
    if (!isset($_SESSION['login_attempts'][$identifier])) {
        $_SESSION['login_attempts'][$identifier] = [
            'count' => 0,
            'last_attempt' => time()
        ];
    }
    
    $_SESSION['login_attempts'][$identifier]['count']++;
    $_SESSION['login_attempts'][$identifier]['last_attempt'] = time();
}

/**
 * Clear Login Attempts
 */
function clearLoginAttempts($identifier) {
    if (isset($_SESSION['login_attempts'][$identifier])) {
        unset($_SESSION['login_attempts'][$identifier]);
    }
}

/**
 * Validate Integer ID
 */
function validateId($id) {
    return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
}

/**
 * Send JSON Response
 */
function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Log Security Event
 */
function logSecurityEvent($event, $details = []) {
    $logFile = __DIR__ . '/logs/security.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'event' => $event,
        'details' => $details
    ];
    
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
}
?>
