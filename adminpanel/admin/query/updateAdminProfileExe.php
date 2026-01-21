<?php
session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    sendJSON(['res' => 'unauthorized', 'msg' => 'Please login first'], 401);
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    sendJSON(['res' => 'timeout', 'msg' => 'Session expired'], 401);
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'admin_profile_update']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

$adminId = $_POST['admin_id'] ?? '';
$adminUsername = $_POST['admin_username'] ?? '';

// Validate inputs
if (!validateId($adminId)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid admin ID'], 400);
}

$errors = validateRequired(['admin_username' => $adminUsername]);
if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Validate email format
if (!validateEmail($adminUsername)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid email format'], 400);
}

// Check if admin ID matches session
if ($adminId != $_SESSION['admin']['admin_id']) {
    logSecurityEvent('UNAUTHORIZED_PROFILE_UPDATE', ['admin_id' => $adminId, 'session_id' => $_SESSION['admin']['admin_id']]);
    sendJSON(['res' => 'unauthorized', 'msg' => 'Unauthorized action'], 403);
}

try {
    // Check if username already exists (excluding current admin)
    $stmt = $conn->prepare("SELECT admin_id FROM admin_acc WHERE admin_user = ? AND admin_id != ?");
    $stmt->execute([$adminUsername, $adminId]);
    
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'exists', 'msg' => 'Username already exists'], 400);
    }

    // Update admin profile
    $stmt = $conn->prepare("UPDATE admin_acc SET admin_user = ?, updated_at = CURRENT_TIMESTAMP WHERE admin_id = ?");
    $stmt->execute([$adminUsername, $adminId]);

    if ($stmt->rowCount() > 0) {
        // Update session data
        $_SESSION['admin']['admin_user'] = $adminUsername;
        
        // Log successful update
        logSecurityEvent('ADMIN_PROFILE_UPDATED', ['admin_id' => $adminId, 'new_username' => $adminUsername]);
        
        sendJSON(['res' => 'success', 'msg' => 'Profile updated successfully']);
    } else {
        sendJSON(['res' => 'error', 'msg' => 'No changes were made'], 400);
    }

} catch (PDOException $e) {
    error_log("Admin Profile Update Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}
?>