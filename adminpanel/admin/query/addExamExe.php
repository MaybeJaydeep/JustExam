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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'add_exam']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$courseSelected = $_POST['courseSelected'] ?? '0';
$examTitle = trim($_POST['examTitle'] ?? '');
$timeLimit = $_POST['timeLimit'] ?? '0';
$examQuestDipLimit = trim($_POST['examQuestDipLimit'] ?? '');
$examDesc = trim($_POST['examDesc'] ?? '');

// Validate selections
if ($courseSelected == "0") {
    sendJSON(['res' => 'noSelectedCourse', 'msg' => 'Please select a course'], 400);
}

if ($timeLimit == "0") {
    sendJSON(['res' => 'noSelectedTime', 'msg' => 'Please select time limit'], 400);
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
if (!validateId($courseSelected)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid course selected'], 400);
}

if (!validateId($timeLimit)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid time limit'], 400);
}

if (!is_numeric($examQuestDipLimit) || $examQuestDipLimit < 1) {
    sendJSON(['res' => 'noDisplayLimit', 'msg' => 'Question display limit must be a positive number'], 400);
}

// Validate length
if (strlen($examTitle) > 200) {
    sendJSON(['res' => 'invalid', 'msg' => 'Exam title is too long (max 200 characters)'], 400);
}

try {
    // Verify course exists
    $stmt = $conn->prepare("SELECT cou_id FROM course_tbl WHERE cou_id = ?");
    $stmt->execute([$courseSelected]);
    if ($stmt->rowCount() === 0) {
        sendJSON(['res' => 'invalid', 'msg' => 'Selected course does not exist'], 400);
    }
    
    // Check if exam title exists using prepared statement
    $stmt = $conn->prepare("SELECT * FROM exam_tbl WHERE ex_title = ?");
    $stmt->execute([$examTitle]);
    
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'exist', 'examTitle' => $examTitle, 'msg' => "Exam '$examTitle' already exists"], 400);
    }
    
    // Insert new exam using prepared statement
    $stmt = $conn->prepare("INSERT INTO exam_tbl(cou_id, ex_title, ex_time_limit, ex_questlimit_display, ex_description) VALUES(?, ?, ?, ?, ?)");
    $stmt->execute([$courseSelected, $examTitle, $timeLimit, $examQuestDipLimit, $examDesc]);
    
    logSecurityEvent('EXAM_ADDED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'exam_title' => $examTitle,
        'course_id' => $courseSelected
    ]);
    
    $res = ['res' => 'success', 'examTitle' => $examTitle, 'msg' => "Exam '$examTitle' added successfully"];
    
} catch (PDOException $e) {
    error_log("Add Exam Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>