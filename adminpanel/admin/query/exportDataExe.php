<?php
session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    die("Unauthorized access");
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    die("Session expired");
}

// Verify CSRF Token
if (!isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'data_export']);
    die("Invalid request");
}

$exportType = $_GET['type'] ?? '';

// Validate export type
if (!in_array($exportType, ['students', 'results', 'courses', 'exams'])) {
    die("Invalid export type");
}

try {
    // Set headers for CSV download
    $filename = $exportType . '_export_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    switch ($exportType) {
        case 'students':
            exportStudents($conn, $output);
            break;
        case 'results':
            exportResults($conn, $output);
            break;
        case 'courses':
            exportCourses($conn, $output);
            break;
        case 'exams':
            exportExams($conn, $output);
            break;
    }

    fclose($output);

    // Log export activity
    logSecurityEvent('DATA_EXPORT', [
        'type' => $exportType,
        'admin_id' => $_SESSION['admin']['admin_id'],
        'filename' => $filename
    ]);

} catch (Exception $e) {
    error_log("Export Error: " . $e->getMessage());
    die("Export failed. Please try again.");
}

function exportStudents($conn, $output) {
    // CSV headers
    fputcsv($output, [
        'Student ID',
        'Full Name',
        'Email',
        'Gender',
        'Birth Date',
        'Course',
        'Year Level',
        'Status',
        'Created Date',
        'Last Updated'
    ]);

    // Get students data with course names
    $stmt = $conn->query("
        SELECT 
            e.exmne_id,
            e.exmne_fullname,
            e.exmne_email,
            e.exmne_gender,
            e.exmne_birthdate,
            c.cou_name,
            e.exmne_year_level,
            e.exmne_status,
            e.created_at,
            e.updated_at
        FROM examinee_tbl e
        LEFT JOIN course_tbl c ON e.exmne_course = c.cou_id
        ORDER BY e.exmne_id ASC
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['exmne_id'],
            $row['exmne_fullname'],
            $row['exmne_email'],
            ucfirst($row['exmne_gender']),
            $row['exmne_birthdate'],
            $row['cou_name'] ?? 'N/A',
            $row['exmne_year_level'],
            ucfirst($row['exmne_status']),
            $row['created_at'] ? date('Y-m-d H:i:s', strtotime($row['created_at'])) : 'N/A',
            $row['updated_at'] ? date('Y-m-d H:i:s', strtotime($row['updated_at'])) : 'N/A'
        ]);
    }
}

function exportResults($conn, $output) {
    // CSV headers
    fputcsv($output, [
        'Attempt ID',
        'Student ID',
        'Student Name',
        'Student Email',
        'Course',
        'Exam ID',
        'Exam Title',
        'Score (%)',
        'Correct Answers',
        'Total Questions',
        'Attempt Date',
        'Status'
    ]);

    // Get results data
    $stmt = $conn->query("
        SELECT 
            ea.examat_id,
            e.exmne_id,
            e.exmne_fullname,
            e.exmne_email,
            c.cou_name,
            et.ex_id,
            et.ex_title,
            ea.score,
            ea.correct_answers,
            ea.total_questions,
            ea.attempt_date,
            ea.examat_status
        FROM exam_attempt ea
        INNER JOIN examinee_tbl e ON ea.exmne_id = e.exmne_id
        INNER JOIN exam_tbl et ON ea.exam_id = et.ex_id
        LEFT JOIN course_tbl c ON et.cou_id = c.cou_id
        ORDER BY ea.attempt_date DESC
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['examat_id'],
            $row['exmne_id'],
            $row['exmne_fullname'],
            $row['exmne_email'],
            $row['cou_name'] ?? 'N/A',
            $row['ex_id'],
            $row['ex_title'],
            $row['score'] ? number_format($row['score'], 2) : 'N/A',
            $row['correct_answers'] ?? 'N/A',
            $row['total_questions'] ?? 'N/A',
            $row['attempt_date'] ? date('Y-m-d H:i:s', strtotime($row['attempt_date'])) : 'N/A',
            ucfirst($row['examat_status'])
        ]);
    }
}

function exportCourses($conn, $output) {
    // CSV headers
    fputcsv($output, [
        'Course ID',
        'Course Name',
        'Total Students',
        'Total Exams',
        'Created Date',
        'Last Updated'
    ]);

    // Get courses data with counts
    $stmt = $conn->query("
        SELECT 
            c.cou_id,
            c.cou_name,
            c.cou_created,
            c.updated_at,
            COUNT(DISTINCT e.exmne_id) as student_count,
            COUNT(DISTINCT et.ex_id) as exam_count
        FROM course_tbl c
        LEFT JOIN examinee_tbl e ON c.cou_id = e.exmne_course
        LEFT JOIN exam_tbl et ON c.cou_id = et.cou_id
        GROUP BY c.cou_id, c.cou_name, c.cou_created, c.updated_at
        ORDER BY c.cou_id ASC
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['cou_id'],
            $row['cou_name'],
            $row['student_count'],
            $row['exam_count'],
            $row['cou_created'] ? date('Y-m-d H:i:s', strtotime($row['cou_created'])) : 'N/A',
            $row['updated_at'] ? date('Y-m-d H:i:s', strtotime($row['updated_at'])) : 'N/A'
        ]);
    }
}

function exportExams($conn, $output) {
    // CSV headers
    fputcsv($output, [
        'Exam ID',
        'Course',
        'Exam Title',
        'Description',
        'Time Limit (minutes)',
        'Question Display Limit',
        'Total Questions',
        'Total Attempts',
        'Status',
        'Created Date',
        'Last Updated'
    ]);

    // Get exams data with counts
    $stmt = $conn->query("
        SELECT 
            et.ex_id,
            c.cou_name,
            et.ex_title,
            et.ex_description,
            et.ex_time_limit,
            et.ex_questlimit_display,
            et.ex_status,
            et.ex_created,
            et.updated_at,
            COUNT(DISTINCT eq.eqt_id) as question_count,
            COUNT(DISTINCT ea.examat_id) as attempt_count
        FROM exam_tbl et
        LEFT JOIN course_tbl c ON et.cou_id = c.cou_id
        LEFT JOIN exam_question_tbl eq ON et.ex_id = eq.exam_id
        LEFT JOIN exam_attempt ea ON et.ex_id = ea.exam_id
        GROUP BY et.ex_id, c.cou_name, et.ex_title, et.ex_description, 
                 et.ex_time_limit, et.ex_questlimit_display, et.ex_status, 
                 et.ex_created, et.updated_at
        ORDER BY et.ex_id ASC
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['ex_id'],
            $row['cou_name'] ?? 'N/A',
            $row['ex_title'],
            $row['ex_description'],
            $row['ex_time_limit'],
            $row['ex_questlimit_display'],
            $row['question_count'],
            $row['attempt_count'],
            ucfirst($row['ex_status'] ?? 'active'),
            $row['ex_created'] ? date('Y-m-d H:i:s', strtotime($row['ex_created'])) : 'N/A',
            $row['updated_at'] ? date('Y-m-d H:i:s', strtotime($row['updated_at'])) : 'N/A'
        ]);
    }
}
?>