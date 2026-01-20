<?php
session_start();
require_once("../config.php");
require_once("../security.php");

// Check if user is logged in
if (!isset($_SESSION['student']['is_logged_in']) || $_SESSION['student']['is_logged_in'] !== true) {
    sendJSON(['res' => 'unauthorized', 'msg' => 'Please login first'], 401);
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    sendJSON(['res' => 'timeout', 'msg' => 'Session expired'], 401);
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'student_password_change']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

$studentId = $_POST['student_id'] ?? '';
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate inputs
if (!validateId($studentId)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid student ID'], 400);
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
if (strlen($newPassword) < 6) {
    sendJSON(['res' => 'invalid', 'msg' => 'Password must be at least 6 characters long'], 400);
}

// Check if student ID matches session
if ($studentId != $_SESSION['student']['user_id']) {
    logSecurityEvent('UNAUTHORIZED_PASSWORD_CHANGE', ['student_id' => $studentId, 'session_id' => $_SESSION['student']['user_id']]);
    sendJSON(['res' => 'unauthorized', 'msg' => 'Unauthorized action'], 403);
}

try {
    // Get current student data
    $stmt = $conn->prepare("SELECT exmne_password FROM examinee_tbl WHERE exmne_id = ?");
    $stmt->execute([$studentId]);
    $studentData = $stmt->fetch();

    if (!$studentData) {
        sendJSON(['res' => 'error', 'msg' => 'Student account not found'], 404);
    }

    // Verify current password
    if (!verifyPassword($currentPassword, $studentData['exmne_password'])) {
        logSecurityEvent('STUDENT_WRONG_PASSWORD', ['student_id' => $studentId]);
        sendJSON(['res' => 'invalid', 'msg' => 'Current password is incorrect'], 400);
    }

    // Check if new password is different from current
    if (verifyPassword($newPassword, $studentData['exmne_password'])) {
        sendJSON(['res' => 'invalid', 'msg' => 'New password must be different from current password'], 400);
    }

    // Hash new password
    $hashedPassword = hashPassword($newPassword);

    // Update password
    $stmt = $conn->prepare("UPDATE examinee_tbl SET exmne_password = ?, updated_at = CURRENT_TIMESTAMP WHERE exmne_id = ?");
    $stmt->execute([$hashedPassword, $studentId]);

    if ($stmt->rowCount() > 0) {
        // Log successful password change
        logSecurityEvent('STUDENT_PASSWORD_CHANGED', ['student_id' => $studentId]);
        
        sendJSON(['res' => 'success', 'msg' => 'Password changed successfully']);
    } else {
        sendJSON(['res' => 'error', 'msg' => 'Failed to update password'], 500);
    }

} catch (PDOException $e) {
    error_log("Student Password Change Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}
?>