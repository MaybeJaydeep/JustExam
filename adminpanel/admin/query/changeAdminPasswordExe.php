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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'admin_password_change']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

$adminId = $_POST['admin_id'] ?? '';
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate inputs
if (!validateId($adminId)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid admin ID'], 400);
}

$errors = validateRequired([
    'current_password' => $currentPassword,
    'new_password' => $newPassword,
    'confirm_password' => $confirmPassword
]);

if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Check if new passwords match
if ($newPassword !== $confirmPassword) {
    sendJSON(['res' => 'invalid', 'msg' => 'New passwords do not match'], 400);
}

// Check password length
if (strlen($newPassword) < 8) {
    sendJSON(['res' => 'invalid', 'msg' => 'Password must be at least 8 characters long'], 400);
}

// Check if admin ID matches session
if ($adminId != $_SESSION['admin']['admin_id']) {
    logSecurityEvent('UNAUTHORIZED_PASSWORD_CHANGE', ['admin_id' => $adminId, 'session_id' => $_SESSION['admin']['admin_id']]);
    sendJSON(['res' => 'unauthorized', 'msg' => 'Unauthorized action'], 403);
}

try {
    // Get current admin data
    $stmt = $conn->prepare("SELECT admin_pass FROM admin_acc WHERE admin_id = ?");
    $stmt->execute([$adminId]);
    $adminData = $stmt->fetch();

    if (!$adminData) {
        sendJSON(['res' => 'error', 'msg' => 'Admin account not found'], 404);
    }

    // Verify current password
    if (!verifyPassword($currentPassword, $adminData['admin_pass'])) {
        logSecurityEvent('ADMIN_WRONG_PASSWORD', ['admin_id' => $adminId]);
        sendJSON(['res' => 'invalid', 'msg' => 'Current password is incorrect'], 400);
    }

    // Check if new password is different from current
    if (verifyPassword($newPassword, $adminData['admin_pass'])) {
        sendJSON(['res' => 'invalid', 'msg' => 'New password must be different from current password'], 400);
    }

    // Hash new password
    $hashedPassword = hashPassword($newPassword);

    // Update password
    $stmt = $conn->prepare("UPDATE admin_acc SET admin_pass = ?, updated_at = CURRENT_TIMESTAMP WHERE admin_id = ?");
    $stmt->execute([$hashedPassword, $adminId]);

    if ($stmt->rowCount() > 0) {
        // Log successful password change
        logSecurityEvent('ADMIN_PASSWORD_CHANGED', ['admin_id' => $adminId]);
        
        sendJSON(['res' => 'success', 'msg' => 'Password changed successfully']);
    } else {
        sendJSON(['res' => 'error', 'msg' => 'Failed to update password'], 500);
    }

} catch (PDOException $e) {
    error_log("Admin Password Change Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}
?>