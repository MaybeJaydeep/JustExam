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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'update_exam']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$examId = $_POST['examId'] ?? '';
$courseId = $_POST['courseId'] ?? '';
$examTitle = trim($_POST['examTitle'] ?? '');
$examLimit = $_POST['examLimit'] ?? '';
$examQuestDipLimit = trim($_POST['examQuestDipLimit'] ?? '');
$examDesc = trim($_POST['examDesc'] ?? '');

// Validate IDs
if (!validateId($examId)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid exam ID'], 400);
}

if (!validateId($courseId)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid course ID'], 400);
}

if (!validateId($examLimit)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid time limit'], 400);
}

// Validate required fields
$errors = validateRequired([
    'exam_title' => $examTitle,
    'question_display_limit' => $examQuestDipLimit
]);

if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Validate numeric values
if (!is_numeric($examQuestDipLimit) || $examQuestDipLimit < 1) {
    sendJSON(['res' => 'invalid', 'msg' => 'Question display limit must be a positive number'], 400);
}

// Validate length
if (strlen($examTitle) > 200) {
    sendJSON(['res' => 'invalid', 'msg' => 'Exam title is too long (max 200 characters)'], 400);
}

try {
    // Check if exam exists
    $stmt = $conn->prepare("SELECT ex_id FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$examId]);
    if ($stmt->rowCount() === 0) {
        sendJSON(['res' => 'invalid', 'msg' => 'Exam not found'], 404);
    }
    
    // Verify course exists
    $stmt = $conn->prepare("SELECT cou_id FROM course_tbl WHERE cou_id = ?");
    $stmt->execute([$courseId]);
    if ($stmt->rowCount() === 0) {
        sendJSON(['res' => 'invalid', 'msg' => 'Selected course does not exist'], 400);
    }
    
    // Check if title is taken by another exam
    $stmt = $conn->prepare("SELECT ex_id FROM exam_tbl WHERE ex_title = ? AND ex_id != ?");
    $stmt->execute([$examTitle, $examId]);
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'exist', 'msg' => "Exam title '$examTitle' is already taken"], 400);
    }
    
    // Update exam using prepared statement
    $stmt = $conn->prepare("UPDATE exam_tbl SET cou_id = ?, ex_title = ?, ex_time_limit = ?, ex_questlimit_display = ?, ex_description = ? WHERE ex_id = ?");
    $stmt->execute([$courseId, $examTitle, $examLimit, $examQuestDipLimit, $examDesc, $examId]);
    
    logSecurityEvent('EXAM_UPDATED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'exam_id' => $examId,
        'exam_title' => $examTitle
    ]);
    
    $res = ['res' => 'success', 'msg' => "Exam '$examTitle' updated successfully"];
    
} catch (PDOException $e) {
    error_log("Update Exam Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>