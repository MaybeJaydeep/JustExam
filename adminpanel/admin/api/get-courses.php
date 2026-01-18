<?php
/**
 * Get Courses API Endpoint
 * Returns list of all courses for report filters
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
    $stmt = $conn->query("
        SELECT 
            cou_id,
            cou_name,
            cou_created,
            (SELECT COUNT(*) FROM exam_tbl WHERE cou_id = course_tbl.cou_id) as exam_count
        FROM course_tbl 
        ORDER BY cou_name ASC
    ");
    
    $courses = $stmt->fetchAll();
    
    // Return as JSON array (not wrapped in success object for direct use)
    header('Content-Type: application/json');
    echo json_encode($courses);
    
} catch (PDOException $e) {
    error_log("Get Courses API Error: " . $e->getMessage());
    sendJSON(['error' => 'Database error occurred'], 500);
}
?>