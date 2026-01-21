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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'update_question']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$question_id = $_POST['question_id'] ?? '';
$question = trim($_POST['question'] ?? '');
$exam_ch1 = trim($_POST['exam_ch1'] ?? '');
$exam_ch2 = trim($_POST['exam_ch2'] ?? '');
$exam_ch3 = trim($_POST['exam_ch3'] ?? '');
$exam_ch4 = trim($_POST['exam_ch4'] ?? '');
$correctAnswer = trim($_POST['correctAnswer'] ?? '');

// Validate question ID
if (!validateId($question_id)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid question ID'], 400);
}

// Validate required fields
$errors = validateRequired([
    'question' => $question,
    'choice_1' => $exam_ch1,
    'choice_2' => $exam_ch2,
    'choice_3' => $exam_ch3,
    'choice_4' => $exam_ch4,
    'correct_answer' => $correctAnswer
]);

if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Validate correct answer is one of the choices
if (!in_array($correctAnswer, [$exam_ch1, $exam_ch2, $exam_ch3, $exam_ch4])) {
    sendJSON(['res' => 'invalid', 'msg' => 'Correct answer must match one of the choices'], 400);
}

// Validate length
if (strlen($question) > 1000) {
    sendJSON(['res' => 'invalid', 'msg' => 'Question is too long (max 1000 characters)'], 400);
}

try {
    // Check if question exists
    $stmt = $conn->prepare("SELECT eqt_id, exam_id FROM exam_question_tbl WHERE eqt_id = ?");
    $stmt->execute([$question_id]);
    $existingQuestion = $stmt->fetch();
    
    if (!$existingQuestion) {
        sendJSON(['res' => 'invalid', 'msg' => 'Question not found'], 404);
    }
    
    // Update question using prepared statement
    $stmt = $conn->prepare("UPDATE exam_question_tbl SET exam_question = ?, exam_ch1 = ?, exam_ch2 = ?, exam_ch3 = ?, exam_ch4 = ?, exam_answer = ? WHERE eqt_id = ?");
    $stmt->execute([$question, $exam_ch1, $exam_ch2, $exam_ch3, $exam_ch4, $correctAnswer, $question_id]);
    
    logSecurityEvent('QUESTION_UPDATED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'question_id' => $question_id,
        'exam_id' => $existingQuestion['exam_id']
    ]);
    
    $res = ['res' => 'success', 'msg' => 'Question updated successfully'];
    
} catch (PDOException $e) {
    error_log("Update Question Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>