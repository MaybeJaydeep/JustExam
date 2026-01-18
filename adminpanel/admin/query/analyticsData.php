<?php 
/**
 * Enhanced Analytics Data Provider
 * Provides comprehensive statistics for the admin dashboard
 */

// This file is included in pages, session and auth should already be checked

try {
    // Basic Counts
    $stmt = $conn->query("SELECT COUNT(cou_id) as totCourse FROM course_tbl");
    $selCourse = $stmt->fetch();

    $stmt = $conn->query("SELECT COUNT(ex_id) as totExam FROM exam_tbl");
    $selExam = $stmt->fetch();
    
    $stmt = $conn->query("SELECT COUNT(exmne_id) as totStudent FROM examinee_tbl");
    $selStudent = $stmt->fetch();
    
    $stmt = $conn->query("SELECT COUNT(eqt_id) as totQuestions FROM exam_question_tbl");
    $selQuestions = $stmt->fetch();

    // Exam Attempts and Results
    $stmt = $conn->query("SELECT COUNT(DISTINCT exmne_id) as activeStudents FROM exam_attempt");
    $activeStudents = $stmt->fetch();
    
    $stmt = $conn->query("SELECT COUNT(*) as totalAttempts FROM exam_attempt");
    $totalAttempts = $stmt->fetch();
    
    // Recent Activity (Last 7 days)
    $stmt = $conn->query("
        SELECT COUNT(*) as recentAttempts 
        FROM exam_attempt 
        WHERE examStarted >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $recentActivity = $stmt->fetch();
    
    // Average Scores
    $stmt = $conn->query("
        SELECT 
            AVG(score) as avgScore,
            MAX(score) as maxScore,
            MIN(score) as minScore
        FROM exam_attempt 
        WHERE score IS NOT NULL
    ");
    $scoreStats = $stmt->fetch();
    
    // Exam Completion Rate
    $stmt = $conn->query("
        SELECT 
            COUNT(CASE WHEN score IS NOT NULL THEN 1 END) as completed,
            COUNT(*) as total
        FROM exam_attempt
    ");
    $completionStats = $stmt->fetch();
    $completionRate = $completionStats['total'] > 0 ? 
        round(($completionStats['completed'] / $completionStats['total']) * 100, 1) : 0;
    
    // Top Performing Exams
    $stmt = $conn->query("
        SELECT 
            e.ex_title,
            COUNT(ea.attemptId) as attempts,
            AVG(ea.score) as avg_score
        FROM exam_tbl e
        LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
        WHERE ea.score IS NOT NULL
        GROUP BY e.ex_id, e.ex_title
        ORDER BY avg_score DESC
        LIMIT 5
    ");
    $topExams = $stmt->fetchAll();
    
    // Recent Exam Results (Last 10)
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
        LIMIT 10
    ");
    $recentResults = $stmt->fetchAll();
    
    // Monthly Exam Trends (Last 6 months)
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
    $monthlyTrends = $stmt->fetchAll();
    
    // Course Performance
    $stmt = $conn->query("
        SELECT 
            c.cou_name,
            COUNT(DISTINCT e.ex_id) as exam_count,
            COUNT(ea.attemptId) as total_attempts,
            AVG(ea.score) as avg_score
        FROM course_tbl c
        LEFT JOIN exam_tbl e ON c.cou_id = e.cou_id
        LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
        WHERE ea.score IS NOT NULL
        GROUP BY c.cou_id, c.cou_name
        ORDER BY avg_score DESC
    ");
    $coursePerformance = $stmt->fetchAll();
    
    // Student Activity Distribution
    $stmt = $conn->query("
        SELECT 
            CASE 
                WHEN attempt_count = 0 THEN 'No Attempts'
                WHEN attempt_count = 1 THEN '1 Attempt'
                WHEN attempt_count BETWEEN 2 AND 5 THEN '2-5 Attempts'
                WHEN attempt_count BETWEEN 6 AND 10 THEN '6-10 Attempts'
                ELSE '10+ Attempts'
            END as activity_level,
            COUNT(*) as student_count
        FROM (
            SELECT 
                et.exmne_id,
                COUNT(ea.attemptId) as attempt_count
            FROM examinee_tbl et
            LEFT JOIN exam_attempt ea ON et.exmne_id = ea.exmne_id
            GROUP BY et.exmne_id
        ) student_attempts
        GROUP BY activity_level
        ORDER BY 
            CASE activity_level
                WHEN 'No Attempts' THEN 1
                WHEN '1 Attempt' THEN 2
                WHEN '2-5 Attempts' THEN 3
                WHEN '6-10 Attempts' THEN 4
                ELSE 5
            END
    ");
    $activityDistribution = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Analytics Data Error: " . $e->getMessage());
    
    // Fallback data
    $selCourse = ['totCourse' => 0];
    $selExam = ['totExam' => 0];
    $selStudent = ['totStudent' => 0];
    $selQuestions = ['totQuestions' => 0];
    $activeStudents = ['activeStudents' => 0];
    $totalAttempts = ['totalAttempts' => 0];
    $recentActivity = ['recentAttempts' => 0];
    $scoreStats = ['avgScore' => 0, 'maxScore' => 0, 'minScore' => 0];
    $completionRate = 0;
    $topExams = [];
    $recentResults = [];
    $monthlyTrends = [];
    $coursePerformance = [];
    $activityDistribution = [];
}
?>