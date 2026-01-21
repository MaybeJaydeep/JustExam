<?php
/**
 * Dashboard Data API Endpoint
 * Provides real-time analytics data for AJAX updates
 */

session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    sendJSON(['error' => 'Unauthorized'], 401);
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    sendJSON(['error' => 'Session expired'], 401);
}

try {
    // Get the requested data type
    $dataType = $_GET['type'] ?? 'all';
    
    $response = [];
    
    if ($dataType === 'all' || $dataType === 'basic') {
        // Basic counts
        $stmt = $conn->query("SELECT COUNT(cou_id) as totCourse FROM course_tbl");
        $response['courses'] = $stmt->fetch()['totCourse'];

        $stmt = $conn->query("SELECT COUNT(ex_id) as totExam FROM exam_tbl");
        $response['exams'] = $stmt->fetch()['totExam'];
        
        $stmt = $conn->query("SELECT COUNT(exmne_id) as totStudent FROM examinee_tbl");
        $response['students'] = $stmt->fetch()['totStudent'];
        
        $stmt = $conn->query("SELECT COUNT(eqt_id) as totQuestions FROM exam_question_tbl");
        $response['questions'] = $stmt->fetch()['totQuestions'];
    }
    
    if ($dataType === 'all' || $dataType === 'performance') {
        // Performance metrics
        $stmt = $conn->query("SELECT COUNT(DISTINCT exmne_id) as activeStudents FROM exam_attempt");
        $response['activeStudents'] = $stmt->fetch()['activeStudents'];
        
        $stmt = $conn->query("SELECT COUNT(*) as totalAttempts FROM exam_attempt");
        $response['totalAttempts'] = $stmt->fetch()['totalAttempts'];
        
        // Average scores
        $stmt = $conn->query("
            SELECT 
                AVG(score) as avgScore,
                MAX(score) as maxScore,
                MIN(score) as minScore
            FROM exam_attempt 
            WHERE score IS NOT NULL
        ");
        $scoreStats = $stmt->fetch();
        $response['avgScore'] = round($scoreStats['avgScore'] ?? 0, 1);
        $response['maxScore'] = round($scoreStats['maxScore'] ?? 0, 1);
        $response['minScore'] = round($scoreStats['minScore'] ?? 0, 1);
        
        // Completion rate
        $stmt = $conn->query("
            SELECT 
                COUNT(CASE WHEN score IS NOT NULL THEN 1 END) as completed,
                COUNT(*) as total
            FROM exam_attempt
        ");
        $completionStats = $stmt->fetch();
        $response['completionRate'] = $completionStats['total'] > 0 ? 
            round(($completionStats['completed'] / $completionStats['total']) * 100, 1) : 0;
    }
    
    if ($dataType === 'all' || $dataType === 'recent') {
        // Recent activity (last 7 days)
        $stmt = $conn->query("
            SELECT COUNT(*) as recentAttempts 
            FROM exam_attempt 
            WHERE examStarted >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $response['recentAttempts'] = $stmt->fetch()['recentAttempts'];
        
        // Recent results
        $stmt = $conn->query("
            SELECT 
                et.exmne_fullname,
                e.ex_title,
                ea.score,
                ea.examStarted
            FROM exam_attempt ea
            JOIN examinee_tbl et ON ea.exmne_id = et.exmne_id
            JOIN exam_tbl e ON ea.exam_id = e.ex_id
            WHERE ea.score IS NOT NULL
            ORDER BY ea.examStarted DESC
            LIMIT 5
        ");
        $response['recentResults'] = $stmt->fetchAll();
    }
    
    if ($dataType === 'all' || $dataType === 'trends') {
        // Monthly trends (last 6 months)
        $stmt = $conn->query("
            SELECT 
                DATE_FORMAT(examStarted, '%Y-%m') as month,
                COUNT(*) as attempts,
                AVG(score) as avg_score
            FROM exam_attempt 
            WHERE examStarted >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            AND score IS NOT NULL
            GROUP BY DATE_FORMAT(examStarted, '%Y-%m')
            ORDER BY month ASC
        ");
        $response['monthlyTrends'] = $stmt->fetchAll();
    }
    
    // Add timestamp
    $response['timestamp'] = date('Y-m-d H:i:s');
    $response['success'] = true;
    
    sendJSON($response);
    
} catch (PDOException $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    sendJSON(['error' => 'Database error occurred'], 500);
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    sendJSON(['error' => 'An error occurred'], 500);
}
?>