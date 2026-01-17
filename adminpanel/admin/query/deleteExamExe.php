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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'delete_exam']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$id = $_POST['id'] ?? '';

// Validate ID
if (!validateId($id)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid exam ID'], 400);
}

try {
    // Check if exam exists
    $stmt = $conn->prepare("SELECT ex_id, ex_title FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$id]);
    $exam = $stmt->fetch();
    
    if (!$exam) {
        sendJSON(['res' => 'invalid', 'msg' => 'Exam not found'], 404);
    }
    
    // Begin transaction to delete related records
    $conn->beginTransaction();
    
    // Delete exam answers
    $stmt = $conn->prepare("DELETE FROM exam_answers WHERE exam_id = ?");
    $stmt->execute([$id]);
    
    // Delete exam attempts
    $stmt = $conn->prepare("DELETE FROM exam_attempt WHERE exam_id = ?");
    $stmt->execute([$id]);
    
    // Delete exam questions
    $stmt = $conn->prepare("DELETE FROM exam_question_tbl WHERE exam_id = ?");
    $stmt->execute([$id]);
    
    // Delete exam
    $stmt = $conn->prepare("DELETE FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$id]);
    
    $conn->commit();
    
    logSecurityEvent('EXAM_DELETED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'exam_id' => $id,
        'exam_title' => $exam['ex_title']
    ]);
    
    $res = ['res' => 'success', 'msg' => 'Exam deleted successfully'];
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Delete Exam Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>