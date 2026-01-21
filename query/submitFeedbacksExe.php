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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'submit_feedback']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

$exmneSess = $_SESSION['student']['user_id'];
$asMe = $_POST['asMe'] ?? '';
$myFeedbacks = $_POST['myFeedbacks'] ?? '';

// Validate input
$errors = validateRequired([
    'name' => $asMe,
    'feedback' => $myFeedbacks
]);

if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Sanitize input (limit length)
if (strlen($myFeedbacks) > 1000) {
    sendJSON(['res' => 'invalid', 'msg' => 'Feedback is too long (max 1000 characters)'], 400);
}

try {
    // Check feedback limit using prepared statement
    $stmt = $conn->prepare("SELECT * FROM feedbacks_tbl WHERE exmne_id = ?");
    $stmt->execute([$exmneSess]);
    
    if ($stmt->rowCount() >= 3) {
        $res = ['res' => 'limit', 'msg' => 'You have reached the maximum feedback limit (3)'];
    } else {
        $date = date("F d, Y");
        
        // Insert feedback using prepared statement
        $stmt = $conn->prepare("INSERT INTO feedbacks_tbl(exmne_id, fb_exmne_as, fb_feedbacks, fb_date) VALUES(?, ?, ?, ?)");
        $stmt->execute([$exmneSess, $asMe, $myFeedbacks, $date]);
        
        logSecurityEvent('FEEDBACK_SUBMITTED', ['user_id' => $exmneSess]);
        $res = ['res' => 'success', 'msg' => 'Feedback submitted successfully'];
    }
    
} catch (PDOException $e) {
    error_log("Submit Feedback Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>