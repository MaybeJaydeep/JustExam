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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'add_question']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$examId = $_POST['examId'] ?? '';
$question = trim($_POST['question'] ?? '');
$choice_A = trim($_POST['choice_A'] ?? '');
$choice_B = trim($_POST['choice_B'] ?? '');
$choice_C = trim($_POST['choice_C'] ?? '');
$choice_D = trim($_POST['choice_D'] ?? '');
$correctAnswer = trim($_POST['correctAnswer'] ?? '');

// Validate exam ID
if (!validateId($examId)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid exam ID'], 400);
}

// Validate required fields
$errors = validateRequired([
    'question' => $question,
    'choice_A' => $choice_A,
    'choice_B' => $choice_B,
    'choice_C' => $choice_C,
    'choice_D' => $choice_D,
    'correct_answer' => $correctAnswer
]);

if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Validate correct answer is one of the choices
if (!in_array($correctAnswer, [$choice_A, $choice_B, $choice_C, $choice_D])) {
    sendJSON(['res' => 'invalid', 'msg' => 'Correct answer must match one of the choices'], 400);
}

// Validate length
if (strlen($question) > 1000) {
    sendJSON(['res' => 'invalid', 'msg' => 'Question is too long (max 1000 characters)'], 400);
}

try {
    // Verify exam exists
    $stmt = $conn->prepare("SELECT ex_id FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$examId]);
    if ($stmt->rowCount() === 0) {
        sendJSON(['res' => 'invalid', 'msg' => 'Exam does not exist'], 400);
    }
    
    // Check if question already exists for this exam using prepared statement
    $stmt = $conn->prepare("SELECT * FROM exam_question_tbl WHERE exam_id = ? AND exam_question = ?");
    $stmt->execute([$examId, $question]);
    
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'exist', 'msg' => 'This question already exists for this exam'], 400);
    }
    
    // Insert new question using prepared statement
    $stmt = $conn->prepare("INSERT INTO exam_question_tbl(exam_id, exam_question, exam_ch1, exam_ch2, exam_ch3, exam_ch4, exam_answer) VALUES(?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$examId, $question, $choice_A, $choice_B, $choice_C, $choice_D, $correctAnswer]);
    
    logSecurityEvent('QUESTION_ADDED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'exam_id' => $examId,
        'question' => substr($question, 0, 100) // Log first 100 chars
    ]);
    
    $res = ['res' => 'success', 'msg' => 'Question added successfully'];
    
} catch (PDOException $e) {
    error_log("Add Question Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>