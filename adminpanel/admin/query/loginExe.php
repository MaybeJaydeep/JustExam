<?php 
session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'admin_login']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Validate Input
$username = $_POST['username'] ?? '';
$pass = $_POST['pass'] ?? '';

$errors = validateRequired(['username' => $username, 'password' => $pass]);
if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Check Login Attempts (Brute Force Protection)
if (!checkLoginAttempts('admin_' . $username)) {
    logSecurityEvent('BRUTE_FORCE_ATTEMPT', ['username' => $username, 'type' => 'admin']);
    sendJSON(['res' => 'locked', 'msg' => 'Too many failed attempts. Try again in 15 minutes.'], 429);
}

try {
    // Use Prepared Statement to Prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM admin_acc WHERE admin_user = ?");
    $stmt->execute([$username]);
    $selAccRow = $stmt->fetch();

    if ($selAccRow && verifyPassword($pass, $selAccRow['admin_pass'])) {
        // Clear failed login attempts
        clearLoginAttempts('admin_' . $username);
        
        // Regenerate session ID to prevent session fixation
        regenerateSession();
        
        // Set session variables with best practice naming
        $_SESSION['admin'] = [
            'user_id' => $selAccRow['admin_id'],
            'username' => $selAccRow['admin_user'],
            'is_logged_in' => true
        ];
        $_SESSION['last_activity'] = time();
        
        logSecurityEvent('LOGIN_SUCCESS', ['user_id' => $selAccRow['admin_id'], 'type' => 'admin']);
        
        $res = ['res' => 'success'];
    } else {
        // Record failed login attempt
        recordFailedLogin('admin_' . $username);
        logSecurityEvent('LOGIN_FAILED', ['username' => $username, 'type' => 'admin']);
        
        $res = ['res' => 'invalid', 'msg' => 'Invalid username or password'];
    }
} catch (PDOException $e) {
    error_log("Admin Login Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}




 echo json_encode($res);
 ?>


echo json_encode($res);
?>
