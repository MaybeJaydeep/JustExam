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

$exmneId = $_SESSION['student']['user_id'];
$thisId = $_POST['thisId'] ?? '';

// Validate exam ID
if (!validateId($thisId)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid exam ID'], 400);
}

try {
    // Check if exam attempt exists using prepared statement
    $stmt = $conn->prepare("SELECT * FROM exam_attempt WHERE exmne_id = ? AND exam_id = ?");
    $stmt->execute([$exmneId, $thisId]);
    
    if ($stmt->rowCount() > 0) {
        $res = ['res' => 'alreadyExam', 'msg' => $thisId];
    } else {
        $res = ['res' => 'takeNow'];
    }
    
} catch (PDOException $e) {
    error_log("Exam Attempt Check Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred'], 500);
}

echo json_encode($res);
?>