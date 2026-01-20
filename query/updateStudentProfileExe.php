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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'student_profile_update']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

$studentId = $_POST['student_id'] ?? '';
$fullname = $_POST['student_fullname'] ?? '';
$email = $_POST['student_email'] ?? '';
$gender = $_POST['student_gender'] ?? '';
$birthdate = $_POST['student_birthdate'] ?? '';
$course = $_POST['student_course'] ?? '';
$yearLevel = $_POST['student_year_level'] ?? '';

// Validate inputs
if (!validateId($studentId)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid student ID'], 400);
}

$errors = validateRequired([
    'fullname' => $fullname,
    'email' => $email,
    'gender' => $gender,
    'birthdate' => $birthdate,
    'course' => $course,
    'year_level' => $yearLevel
]);

if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Validate email format
if (!validateEmail($email)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid email format'], 400);
}

// Validate gender
if (!in_array($gender, ['male', 'female', 'other'])) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid gender selection'], 400);
}

// Validate course ID
if (!validateId($course)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid course selection'], 400);
}

// Check if student ID matches session
if ($studentId != $_SESSION['student']['user_id']) {
    logSecurityEvent('UNAUTHORIZED_PROFILE_UPDATE', ['student_id' => $studentId, 'session_id' => $_SESSION['student']['user_id']]);
    sendJSON(['res' => 'unauthorized', 'msg' => 'Unauthorized action'], 403);
}

try {
    // Check if email already exists (excluding current student)
    $stmt = $conn->prepare("SELECT exmne_id FROM examinee_tbl WHERE exmne_email = ? AND exmne_id != ?");
    $stmt->execute([$email, $studentId]);
    
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'exists', 'msg' => 'Email address already exists'], 400);
    }

    // Verify course exists
    $stmt = $conn->prepare("SELECT cou_id FROM course_tbl WHERE cou_id = ?");
    $stmt->execute([$course]);
    
    if ($stmt->rowCount() == 0) {
        sendJSON(['res' => 'invalid', 'msg' => 'Selected course does not exist'], 400);
    }

    // Update student profile
    $stmt = $conn->prepare("
        UPDATE examinee_tbl 
        SET exmne_fullname = ?, 
            exmne_email = ?, 
            exmne_gender = ?, 
            exmne_birthdate = ?, 
            exmne_course = ?, 
            exmne_year_level = ?, 
            updated_at = CURRENT_TIMESTAMP 
        WHERE exmne_id = ?
    ");
    
    $stmt->execute([$fullname, $email, $gender, $birthdate, $course, $yearLevel, $studentId]);

    if ($stmt->rowCount() > 0) {
        // Update session data
        $_SESSION['student']['fullname'] = $fullname;
        $_SESSION['student']['email'] = $email;
        
        // Log successful update
        logSecurityEvent('STUDENT_PROFILE_UPDATED', [
            'student_id' => $studentId, 
            'new_email' => $email,
            'new_course' => $course
        ]);
        
        sendJSON(['res' => 'success', 'msg' => 'Profile updated successfully']);
    } else {
        sendJSON(['res' => 'error', 'msg' => 'No changes were made'], 400);
    }

} catch (PDOException $e) {
    error_log("Student Profile Update Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}
?>