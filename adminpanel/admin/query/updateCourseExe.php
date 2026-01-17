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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'update_course']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$course_id = $_POST['course_id'] ?? '';
$newCourseName = trim($_POST['newCourseName'] ?? '');

// Validate course ID
if (!validateId($course_id)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid course ID'], 400);
}

// Validate required fields
if (empty($newCourseName)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Course name is required'], 400);
}

// Validate length
if (strlen($newCourseName) > 100) {
    sendJSON(['res' => 'invalid', 'msg' => 'Course name is too long (max 100 characters)'], 400);
}

$newCourseName = strtoupper($newCourseName);

try {
    // Check if course exists
    $stmt = $conn->prepare("SELECT cou_id FROM course_tbl WHERE cou_id = ?");
    $stmt->execute([$course_id]);
    if ($stmt->rowCount() === 0) {
        sendJSON(['res' => 'invalid', 'msg' => 'Course not found'], 404);
    }
    
    // Check if new name is taken by another course
    $stmt = $conn->prepare("SELECT cou_id FROM course_tbl WHERE cou_name = ? AND cou_id != ?");
    $stmt->execute([$newCourseName, $course_id]);
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'exist', 'msg' => "Course name '$newCourseName' is already taken"], 400);
    }
    
    // Update course using prepared statement
    $stmt = $conn->prepare("UPDATE course_tbl SET cou_name = ? WHERE cou_id = ?");
    $stmt->execute([$newCourseName, $course_id]);
    
    logSecurityEvent('COURSE_UPDATED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'course_id' => $course_id,
        'new_name' => $newCourseName
    ]);
    
    $res = ['res' => 'success', 'newCourseName' => $newCourseName, 'msg' => "Course updated to '$newCourseName' successfully"];
    
} catch (PDOException $e) {
    error_log("Update Course Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>