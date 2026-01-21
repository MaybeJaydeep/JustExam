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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'delete_examinee']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$id = $_POST['id'] ?? '';

// Validate ID
if (!validateId($id)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid examinee ID'], 400);
}

try {
    // Check if examinee exists
    $stmt = $conn->prepare("SELECT exmne_id, exmne_fullname FROM examinee_tbl WHERE exmne_id = ?");
    $stmt->execute([$id]);
    $examinee = $stmt->fetch();
    
    if (!$examinee) {
        sendJSON(['res' => 'invalid', 'msg' => 'Examinee not found'], 404);
    }
    
    // Begin transaction to delete related records
    $conn->beginTransaction();
    
    // Delete exam answers
    $stmt = $conn->prepare("DELETE FROM exam_answers WHERE axmne_id = ?");
    $stmt->execute([$id]);
    
    // Delete exam attempts
    $stmt = $conn->prepare("DELETE FROM exam_attempt WHERE exmne_id = ?");
    $stmt->execute([$id]);
    
    // Delete feedbacks
    $stmt = $conn->prepare("DELETE FROM feedbacks_tbl WHERE exmne_id = ?");
    $stmt->execute([$id]);
    
    // Delete examinee
    $stmt = $conn->prepare("DELETE FROM examinee_tbl WHERE exmne_id = ?");
    $stmt->execute([$id]);
    
    $conn->commit();
    
    logSecurityEvent('EXAMINEE_DELETED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'examinee_id' => $id,
        'examinee_name' => $examinee['exmne_fullname']
    ]);
    
    $res = ['res' => 'success', 'msg' => 'Student deleted successfully'];
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Delete Examinee Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>