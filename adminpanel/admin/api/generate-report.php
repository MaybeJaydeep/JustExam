<?php
/**
 * Report Generation API Endpoint
 * Handles report generation requests and returns appropriate responses
 */

session_start();
require_once("../../../config.php");
require_once("../../../security.php");
require_once("../reports/ReportGenerator.php");

// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    if (isset($_POST['format']) && $_POST['format'] === 'html') {
        die('<h1>Unauthorized Access</h1><p>Please login as admin to access reports.</p>');
    } else {
        sendJSON(['error' => 'Unauthorized'], 401);
    }
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    if (isset($_POST['format']) && $_POST['format'] === 'html') {
        die('<h1>Session Expired</h1><p>Your session has expired. Please login again.</p>');
    } else {
        sendJSON(['error' => 'Session expired'], 401);
    }
}

try {
    // Validate required parameters
    if (!isset($_POST['report_type']) || !isset($_POST['format'])) {
        throw new Exception("Missing required parameters");
    }
    
    $reportType = $_POST['report_type'];
    $format = $_POST['format'];
    
    // Initialize report generator
    $reportGenerator = new ReportGenerator($conn);
    
    // Generate report based on type
    switch ($reportType) {
        case 'exam_performance':
            if (!isset($_POST['exam_id']) || !validateId($_POST['exam_id'])) {
                throw new Exception("Invalid exam ID");
            }
            $result = $reportGenerator->generateExamPerformanceReport($_POST['exam_id'], $format);
            break;
            
        case 'student_progress':
            if (!isset($_POST['student_id']) || !validateId($_POST['student_id'])) {
                throw new Exception("Invalid student ID");
            }
            $result = $reportGenerator->generateStudentProgressReport($_POST['student_id'], $format);
            break;
            
        case 'course_analytics':
            if (!isset($_POST['course_id']) || !validateId($_POST['course_id'])) {
                throw new Exception("Invalid course ID");
            }
            $result = $reportGenerator->generateCourseAnalyticsReport($_POST['course_id'], $format);
            break;
            
        case 'comparative_analysis':
            if (!isset($_POST['exam_ids']) || !is_array($_POST['exam_ids'])) {
                throw new Exception("Invalid exam IDs for comparison");
            }
            $result = $reportGenerator->generateComparativeReport($_POST['exam_ids'], $format);
            break;
            
        default:
            throw new Exception("Unknown report type: $reportType");
    }
    
    // Handle different output formats
    if ($format === 'html') {
        // For HTML format, output directly
        header('Content-Type: text/html; charset=utf-8');
        echo $result;
        
    } elseif ($format === 'csv') {
        // For CSV format, trigger download
        $filename = basename($result);
        $filepath = __DIR__ . "/../../../" . $result;
        
        if (!file_exists($filepath)) {
            throw new Exception("Generated report file not found");
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        
        readfile($filepath);
        
        // Clean up the temporary file
        unlink($filepath);
        
    } else {
        throw new Exception("Unsupported format: $format");
    }
    
    // Log successful report generation
    logSecurityEvent('REPORT_GENERATED', [
        'report_type' => $reportType,
        'format' => $format,
        'admin_id' => $_SESSION['admin']['admin_id'] ?? 'unknown'
    ]);
    
} catch (Exception $e) {
    error_log("Report Generation Error: " . $e->getMessage());
    
    logSecurityEvent('REPORT_GENERATION_ERROR', [
        'error' => $e->getMessage(),
        'report_type' => $_POST['report_type'] ?? 'unknown',
        'admin_id' => $_SESSION['admin']['admin_id'] ?? 'unknown'
    ]);
    
    if (isset($_POST['format']) && $_POST['format'] === 'html') {
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Report Generation Error</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; }
                .back-btn { margin-top: 20px; }
                .back-btn a { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='error'>
                <h2>❌ Report Generation Failed</h2>
                <p><strong>Error:</strong> " . escape($e->getMessage()) . "</p>
                <p>Please check your selection and try again.</p>
            </div>
            <div class='back-btn'>
                <a href='javascript:history.back()'>← Go Back</a>
            </div>
        </body>
        </html>";
    } else {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}
?>