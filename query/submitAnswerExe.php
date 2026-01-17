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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'submit_answer']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

$exmne_id = $_SESSION['student']['user_id'];
$exam_id = $_POST['exam_id'] ?? '';
$answers = $_POST['answer'] ?? [];

// Validate exam_id
if (!validateId($exam_id)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid exam ID'], 400);
}

// Validate answers array
if (empty($answers) || !is_array($answers)) {
    sendJSON(['res' => 'invalid', 'msg' => 'No answers provided'], 400);
}

try {
    // Check if exam exists and is active
    $stmt = $conn->prepare("SELECT ex_id FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$exam_id]);
    if ($stmt->rowCount() === 0) {
        sendJSON(['res' => 'invalid', 'msg' => 'Exam not found'], 404);
    }

    // Check if already taken (Prepared Statement)
    $stmt = $conn->prepare("SELECT * FROM exam_attempt WHERE exmne_id = ? AND exam_id = ?");
    $stmt->execute([$exmne_id, $exam_id]);
    
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'alreadyTaken', 'msg' => 'You have already taken this exam']);
    }

    // Begin transaction
    $conn->beginTransaction();

    // Check if there are existing answers
    $stmt = $conn->prepare("SELECT * FROM exam_answers WHERE axmne_id = ? AND exam_id = ?");
    $stmt->execute([$exmne_id, $exam_id]);
    
    if ($stmt->rowCount() > 0) {
        // Mark old answers as old
        $stmt = $conn->prepare("UPDATE exam_answers SET exans_status = 'old' WHERE axmne_id = ? AND exam_id = ?");
        $stmt->execute([$exmne_id, $exam_id]);
    }

    // Insert new answers
    $stmt = $conn->prepare("INSERT INTO exam_answers(axmne_id, exam_id, quest_id, exans_answer) VALUES(?, ?, ?, ?)");
    
    foreach ($answers as $quest_id => $answer) {
        // Validate question ID
        if (!validateId($quest_id)) {
            $conn->rollBack();
            sendJSON(['res' => 'invalid', 'msg' => 'Invalid question ID'], 400);
        }
        
        // Verify question belongs to this exam
        $verifyStmt = $conn->prepare("SELECT eqt_id FROM exam_question_tbl WHERE eqt_id = ? AND exam_id = ?");
        $verifyStmt->execute([$quest_id, $exam_id]);
        if ($verifyStmt->rowCount() === 0) {
            $conn->rollBack();
            sendJSON(['res' => 'invalid', 'msg' => 'Invalid question for this exam'], 400);
        }
        
        $answer_text = $answer['correct'] ?? '';
        $stmt->execute([$exmne_id, $exam_id, $quest_id, $answer_text]);
    }

    // Record exam attempt
    $stmt = $conn->prepare("INSERT INTO exam_attempt(exmne_id, exam_id) VALUES(?, ?)");
    $stmt->execute([$exmne_id, $exam_id]);

    // Commit transaction
    $conn->commit();
    
    logSecurityEvent('EXAM_SUBMITTED', ['user_id' => $exmne_id, 'exam_id' => $exam_id]);
    
    $res = ['res' => 'success', 'msg' => 'Exam submitted successfully'];

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Submit Answer Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>