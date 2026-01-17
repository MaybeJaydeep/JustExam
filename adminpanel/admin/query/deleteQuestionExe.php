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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'delete_question']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$id = $_POST['id'] ?? '';

// Validate ID
if (!validateId($id)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid question ID'], 400);
}

try {
    // Check if question exists
    $stmt = $conn->prepare("SELECT eqt_id, exam_id, exam_question FROM exam_question_tbl WHERE eqt_id = ?");
    $stmt->execute([$id]);
    $question = $stmt->fetch();
    
    if (!$question) {
        sendJSON(['res' => 'invalid', 'msg' => 'Question not found'], 404);
    }
    
    // Begin transaction to delete related records
    $conn->beginTransaction();
    
    // Delete answers for this question
    $stmt = $conn->prepare("DELETE FROM exam_answers WHERE quest_id = ?");
    $stmt->execute([$id]);
    
    // Delete question
    $stmt = $conn->prepare("DELETE FROM exam_question_tbl WHERE eqt_id = ?");
    $stmt->execute([$id]);
    
    $conn->commit();
    
    logSecurityEvent('QUESTION_DELETED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'question_id' => $id,
        'exam_id' => $question['exam_id']
    ]);
    
    $res = ['res' => 'success', 'msg' => 'Question deleted successfully'];
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Delete Question Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>