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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'update_examinee']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$exmne_id = $_POST['exmne_id'] ?? '';
$exFullname = trim($_POST['exFullname'] ?? '');
$exCourse = $_POST['exCourse'] ?? '';
$exGender = $_POST['exGender'] ?? '';
$exBdate = $_POST['exBdate'] ?? '';
$exYrlvl = $_POST['exYrlvl'] ?? '';
$exEmail = trim($_POST['exEmail'] ?? '');
$exPass = $_POST['exPass'] ?? '';

// Validate examinee ID
if (!validateId($exmne_id)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid examinee ID'], 400);
}

// Validate required fields
$errors = validateRequired([
    'fullname' => $exFullname,
    'email' => $exEmail,
    'course' => $exCourse,
    'gender' => $exGender,
    'birthdate' => $exBdate,
    'year_level' => $exYrlvl
]);

if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Validate email format
if (!validateEmail($exEmail)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid email format'], 400);
}

// Validate course and other IDs
if (!validateId($exCourse)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid course selected'], 400);
}

try {
    // Check if examinee exists
    $stmt = $conn->prepare("SELECT exmne_id FROM examinee_tbl WHERE exmne_id = ?");
    $stmt->execute([$exmne_id]);
    if ($stmt->rowCount() === 0) {
        sendJSON(['res' => 'invalid', 'msg' => 'Examinee not found'], 404);
    }
    
    // Check if email is taken by another examinee
    $stmt = $conn->prepare("SELECT exmne_id FROM examinee_tbl WHERE exmne_email = ? AND exmne_id != ?");
    $stmt->execute([$exEmail, $exmne_id]);
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'emailExist', 'msg' => "Email '$exEmail' is already taken"], 400);
    }
    
    // Update examinee - only update password if provided
    if (!empty($exPass)) {
        // Hash new password
        $hashedPassword = hashPassword($exPass);
        $stmt = $conn->prepare("UPDATE examinee_tbl SET exmne_fullname = ?, exmne_course = ?, exmne_gender = ?, exmne_birthdate = ?, exmne_year_level = ?, exmne_email = ?, exmne_password = ? WHERE exmne_id = ?");
        $stmt->execute([$exFullname, $exCourse, $exGender, $exBdate, $exYrlvl, $exEmail, $hashedPassword, $exmne_id]);
    } else {
        // Don't update password
        $stmt = $conn->prepare("UPDATE examinee_tbl SET exmne_fullname = ?, exmne_course = ?, exmne_gender = ?, exmne_birthdate = ?, exmne_year_level = ?, exmne_email = ? WHERE exmne_id = ?");
        $stmt->execute([$exFullname, $exCourse, $exGender, $exBdate, $exYrlvl, $exEmail, $exmne_id]);
    }
    
    logSecurityEvent('EXAMINEE_UPDATED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'examinee_id' => $exmne_id,
        'email' => $exEmail
    ]);
    
    $res = ['res' => 'success', 'exFullname' => $exFullname, 'msg' => "Student '$exFullname' updated successfully"];
    
} catch (PDOException $e) {
    error_log("Update Examinee Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>