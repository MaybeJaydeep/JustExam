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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'add_course']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$course_name = trim($_POST['course_name'] ?? '');

// Validate required fields
if (empty($course_name)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Course name is required'], 400);
}

// Validate length
if (strlen($course_name) > 100) {
    sendJSON(['res' => 'invalid', 'msg' => 'Course name is too long (max 100 characters)'], 400);
}

$course_name = strtoupper($course_name);

try {
    // Check if course exists using prepared statement
    $stmt = $conn->prepare("SELECT * FROM course_tbl WHERE cou_name = ?");
    $stmt->execute([$course_name]);
    
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'exist', 'course_name' => $course_name, 'msg' => "Course '$course_name' already exists"], 400);
    }
    
    // Insert new course using prepared statement
    $stmt = $conn->prepare("INSERT INTO course_tbl(cou_name) VALUES(?)");
    $stmt->execute([$course_name]);
    
    logSecurityEvent('COURSE_ADDED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'course_name' => $course_name
    ]);
    
    $res = ['res' => 'success', 'course_name' => $course_name, 'msg' => "Course '$course_name' added successfully"];
    
} catch (PDOException $e) {
    error_log("Add Course Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>