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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'delete_course']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$id = $_POST['id'] ?? '';

// Validate ID
if (!validateId($id)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid course ID'], 400);
}

try {
    // Check if course exists
    $stmt = $conn->prepare("SELECT cou_id, cou_name FROM course_tbl WHERE cou_id = ?");
    $stmt->execute([$id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        sendJSON(['res' => 'invalid', 'msg' => 'Course not found'], 404);
    }
    
    // Check if course has exams
    $stmt = $conn->prepare("SELECT COUNT(*) as exam_count FROM exam_tbl WHERE cou_id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result['exam_count'] > 0) {
        sendJSON(['res' => 'hasExams', 'msg' => 'Cannot delete course with existing exams. Delete exams first.'], 400);
    }
    
    // Check if course has students
    $stmt = $conn->prepare("SELECT COUNT(*) as student_count FROM examinee_tbl WHERE exmne_course = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    
    if ($result['student_count'] > 0) {
        sendJSON(['res' => 'hasStudents', 'msg' => 'Cannot delete course with enrolled students. Reassign students first.'], 400);
    }
    
    // Delete course
    $stmt = $conn->prepare("DELETE FROM course_tbl WHERE cou_id = ?");
    $stmt->execute([$id]);
    
    logSecurityEvent('COURSE_DELETED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'course_id' => $id,
        'course_name' => $course['cou_name']
    ]);
    
    $res = ['res' => 'success', 'msg' => 'Course deleted successfully'];
    
} catch (PDOException $e) {
    error_log("Delete Course Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>