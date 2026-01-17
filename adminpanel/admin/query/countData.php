<?php 
// This file is included in pages, session and auth should already be checked

try {
    // Count All Course using prepared statement (no parameters needed but good practice)
    $stmt = $conn->query("SELECT COUNT(cou_id) as totCourse FROM course_tbl");
    $selCourse = $stmt->fetch();

    // Count All Exam
    $stmt = $conn->query("SELECT COUNT(ex_id) as totExam FROM exam_tbl");
    $selExam = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log("Count Data Error: " . $e->getMessage());
    $selCourse = ['totCourse' => 0];
    $selExam = ['totExam' => 0];
}
?>