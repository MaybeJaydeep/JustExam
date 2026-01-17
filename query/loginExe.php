<?php 
session_start();
require_once("../config.php");
require_once("../security.php");

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'student_login']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Validate Input
$username = $_POST['username'] ?? '';
$pass = $_POST['pass'] ?? '';

$errors = validateRequired(['username' => $username, 'password' => $pass]);
if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Validate Email Format
if (!validateEmail($username)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid email format'], 400);
}

// Check Login Attempts (Brute Force Protection)
if (!checkLoginAttempts($username)) {
    logSecurityEvent('BRUTE_FORCE_ATTEMPT', ['email' => $username]);
    sendJSON(['res' => 'locked', 'msg' => 'Too many failed attempts. Try again in 15 minutes.'], 429);
}

try {
    // Use Prepared Statement to Prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM examinee_tbl WHERE exmne_email = ? AND exmne_status = 'active'");
    $stmt->execute([$username]);
    $selAccRow = $stmt->fetch();

    if ($selAccRow && verifyPassword($pass, $selAccRow['exmne_password'])) {
        // Clear failed login attempts
        clearLoginAttempts($username);
        
        // Regenerate session ID to prevent session fixation
        regenerateSession();
        
        // Set session variables with best practice naming
        $_SESSION['student'] = [
            'user_id' => $selAccRow['exmne_id'],
            'email' => $selAccRow['exmne_email'],
            'fullname' => $selAccRow['exmne_fullname'],
            'is_logged_in' => true
        ];
        $_SESSION['last_activity'] = time();
        
        logSecurityEvent('LOGIN_SUCCESS', ['user_id' => $selAccRow['exmne_id'], 'type' => 'student']);
        
        $res = ['res' => 'success'];
    } else {
        // Record failed login attempt
        recordFailedLogin($username);
        logSecurityEvent('LOGIN_FAILED', ['email' => $username, 'type' => 'student']);
        
        $res = ['res' => 'invalid', 'msg' => 'Invalid email or password'];
    }
} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}




 echo json_encode($res);
 ?>


echo json_encode($res);
?>
