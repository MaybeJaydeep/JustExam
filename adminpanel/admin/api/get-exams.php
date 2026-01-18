<?php
/**
 * Get Exams API Endpoint
 * Returns list of exams, optionally filtered by course
 */

session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Check authentication
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    sendJSON(['error' => 'Unauthorized'], 401);
}

if (!checkSessionTimeout()) {
    session_destroy();
    sendJSON(['error' => 'Session expired'], 401);
}

try {
    $courseId = $_GET['course_id'] ?? '';
    
    $sql = "
        SELECT 
            e.ex_id,
            e.ex_title,
            e.ex_time_limit,
            e.ex_questlimit_display,
            c.cou_name,
            (SELECT COUNT(*) FROM exam_attempt WHERE exam_id = e.ex_id) as attempt_count
        FROM exam_tbl e
        LEFT JOIN course_tbl c ON e.cou_id = c.cou_id
    ";
    
    if (!empty($courseId)) {
        $sql .= " WHERE e.cou_id = ?";
        $stmt = $conn->prepare($sql . " ORDER BY e.ex_title ASC");
        $stmt->execute([$courseId]);
    } else {
        $stmt = $conn->prepare($sql . " ORDER BY e.ex_title ASC");
        $stmt->execute();
    }
    
    $exams = $stmt->fetchAll();
    
    // Return as JSON array
    header('Content-Type: application/json');
    echo json_encode($exams);
    
} catch (PDOException $e) {
    error_log("Get Exams API Error: " . $e->getMessage());
    sendJSON(['error' => 'Database error occurred'], 500);
}
?>