<?php
/**
 * Report Generation Engine
 * Handles all report types and formats
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

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$reportType = $input['type'] ?? '';
$filters = $input['filters'] ?? [];
$format = $input['format'] ?? 'pdf';

if (empty($reportType)) {
    sendJSON(['error' => 'Report type is required'], 400);
}

try {
    $reportGenerator = new ReportGenerator($conn);
    $result = $reportGenerator->generateReport($reportType, $filters, $format);
    
    logSecurityEvent('REPORT_GENERATED', [
        'type' => $reportType,
        'format' => $format,
        'filters' => $filters
    ]);
    
    sendJSON($result);
    
} catch (Exception $e) {
    error_log("Report Generation Error: " . $e->getMessage());
    sendJSON(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
}

class ReportGenerator {
    private $conn;
    private $reportsDir;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->reportsDir = __DIR__ . '/generated/';
        
        // Create reports directory if it doesn't exist
        if (!is_dir($this->reportsDir)) {
            mkdir($this->reportsDir, 0755, true);
        }
    }
    
    public function generateReport($type, $filters, $format) {
        // Get report data based on type
        $data = $this->getReportData($type, $filters);
        
        // Generate filename
        $filename = $this->generateFilename($type, $format);
        $filepath = $this->reportsDir . $filename;
        
        // Generate report based on format
        switch ($format) {
            case 'pdf':
                $this->generatePDF($data, $filepath, $type);
                break;
            case 'excel':
                $this->generateExcel($data, $filepath, $type);
                break;
            case 'csv':
                $this->generateCSV($data, $filepath, $type);
                break;
            case 'json':
                $this->generateJSON($data, $filepath, $type);
                break;
            default:
                throw new Exception('Unsupported format: ' . $format);
        }
        
        // Get file size
        $fileSize = $this->formatFileSize(filesize($filepath));
        
        return [
            'success' => true,
            'type' => $type,
            'format' => $format,
            'filename' => $filename,
            'file_url' => 'reports/generated/' . $filename,
            'size' => $fileSize,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getReportData($type, $filters) {
        switch ($type) {
            case 'student-performance':
                return $this->getStudentPerformanceData($filters);
            case 'exam-analytics':
                return $this->getExamAnalyticsData($filters);
            case 'course-summary':
                return $this->getCourseSummaryData($filters);
            case 'time-analytics':
                return $this->getTimeAnalyticsData($filters);
            case 'question-analysis':
                return $this->getQuestionAnalysisData($filters);
            default:
                throw new Exception('Unknown report type: ' . $type);
        }
    }
    
    private function getStudentPerformanceData($filters) {
        $whereClause = $this->buildWhereClause($filters);
        
        $sql = "
            SELECT 
                et.exmne_id,
                et.exmne_fullname,
                et.exmne_email,
                COUNT(ea.attemptId) as total_attempts,
                AVG(ea.score) as avg_score,
                MAX(ea.score) as best_score,
                MIN(ea.score) as worst_score,
                COUNT(CASE WHEN ea.score >= 70 THEN 1 END) as passed_exams,
                COUNT(CASE WHEN ea.score < 70 THEN 1 END) as failed_exams,
                MAX(ea.examStarted) as last_exam_date
            FROM examinee_tbl et
            LEFT JOIN exam_attempt ea ON et.exmne_id = ea.exmne_id
            LEFT JOIN exam_tbl e ON ea.exam_id = e.ex_id
            $whereClause
            GROUP BY et.exmne_id, et.exmne_fullname, et.exmne_email
            ORDER BY avg_score DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        $this->bindFilterParams($stmt, $filters);
        $stmt->execute();
        
        return [
            'title' => 'Student Performance Report',
            'data' => $stmt->fetchAll(),
            'summary' => $this->getStudentPerformanceSummary($filters),
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getExamAnalyticsData($filters) {
        $whereClause = $this->buildWhereClause($filters, 'e');
        
        $sql = "
            SELECT 
                e.ex_id,
                e.ex_title,
                c.cou_name,
                e.ex_time_limit,
                COUNT(ea.attemptId) as total_attempts,
                AVG(ea.score) as avg_score,
                MAX(ea.score) as highest_score,
                MIN(ea.score) as lowest_score,
                STDDEV(ea.score) as score_stddev,
                COUNT(CASE WHEN ea.score >= 70 THEN 1 END) as pass_count,
                COUNT(CASE WHEN ea.score < 70 THEN 1 END) as fail_count,
                AVG(TIMESTAMPDIFF(MINUTE, ea.examStarted, ea.examEnded)) as avg_duration
            FROM exam_tbl e
            LEFT JOIN course_tbl c ON e.cou_id = c.cou_id
            LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
            $whereClause
            GROUP BY e.ex_id, e.ex_title, c.cou_name, e.ex_time_limit
            ORDER BY total_attempts DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        $this->bindFilterParams($stmt, $filters);
        $stmt->execute();
        
        return [
            'title' => 'Exam Analytics Report',
            'data' => $stmt->fetchAll(),
            'summary' => $this->getExamAnalyticsSummary($filters),
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getCourseSummaryData($filters) {
        $whereClause = $this->buildWhereClause($filters, 'c');
        
        $sql = "
            SELECT 
                c.cou_id,
                c.cou_name,
                c.cou_created,
                COUNT(DISTINCT e.ex_id) as total_exams,
                COUNT(DISTINCT ea.exmne_id) as unique_students,
                COUNT(ea.attemptId) as total_attempts,
                AVG(ea.score) as avg_score,
                COUNT(CASE WHEN ea.score >= 70 THEN 1 END) as pass_count,
                COUNT(CASE WHEN ea.score < 70 THEN 1 END) as fail_count
            FROM course_tbl c
            LEFT JOIN exam_tbl e ON c.cou_id = e.cou_id
            LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
            $whereClause
            GROUP BY c.cou_id, c.cou_name, c.cou_created
            ORDER BY avg_score DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        $this->bindFilterParams($stmt, $filters);
        $stmt->execute();
        
        return [
            'title' => 'Course Summary Report',
            'data' => $stmt->fetchAll(),
            'summary' => $this->getCourseSummary($filters),
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getTimeAnalyticsData($filters) {
        $dateRange = $this->getDateRange($filters);
        
        $sql = "
            SELECT 
                DATE(ea.examStarted) as exam_date,
                COUNT(ea.attemptId) as daily_attempts,
                AVG(ea.score) as daily_avg_score,
                COUNT(DISTINCT ea.exmne_id) as unique_students,
                COUNT(CASE WHEN ea.score >= 70 THEN 1 END) as daily_passes
            FROM exam_attempt ea
            WHERE ea.examStarted BETWEEN ? AND ?
            GROUP BY DATE(ea.examStarted)
            ORDER BY exam_date DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$dateRange['start'], $dateRange['end']]);
        
        return [
            'title' => 'Time-Based Analytics Report',
            'data' => $stmt->fetchAll(),
            'summary' => $this->getTimeAnalyticsSummary($filters),
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function getQuestionAnalysisData($filters) {
        $whereClause = $this->buildWhereClause($filters, 'eq');
        
        $sql = "
            SELECT 
                eq.eqt_id,
                eq.exam_question,
                eq.exam_ch1,
                eq.exam_ch2,
                eq.exam_ch3,
                eq.exam_ch4,
                eq.exam_answer,
                e.ex_title,
                COUNT(ea.attemptId) as times_answered,
                -- This would need answer tracking table for detailed analysis
                'N/A' as correct_percentage,
                'Medium' as difficulty_level
            FROM exam_question_tbl eq
            LEFT JOIN exam_tbl e ON eq.exam_id = e.ex_id
            LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
            $whereClause
            GROUP BY eq.eqt_id, eq.exam_question, e.ex_title
            ORDER BY times_answered DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        $this->bindFilterParams($stmt, $filters);
        $stmt->execute();
        
        return [
            'title' => 'Question Bank Analysis Report',
            'data' => $stmt->fetchAll(),
            'summary' => $this->getQuestionAnalysisSummary($filters),
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function buildWhereClause($filters, $tableAlias = '') {
        $conditions = [];
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        
        if (!empty($filters['course_id'])) {
            $conditions[] = "{$prefix}cou_id = :course_id";
        }
        
        if (!empty($filters['exam_id'])) {
            $conditions[] = "e.ex_id = :exam_id";
        }
        
        // Add date range condition
        $dateRange = $this->getDateRange($filters);
        if ($dateRange) {
            $conditions[] = "ea.examStarted BETWEEN :start_date AND :end_date";
        }
        
        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }
    
    private function bindFilterParams($stmt, $filters) {
        if (!empty($filters['course_id'])) {
            $stmt->bindValue(':course_id', $filters['course_id']);
        }
        
        if (!empty($filters['exam_id'])) {
            $stmt->bindValue(':exam_id', $filters['exam_id']);
        }
        
        $dateRange = $this->getDateRange($filters);
        if ($dateRange) {
            $stmt->bindValue(':start_date', $dateRange['start']);
            $stmt->bindValue(':end_date', $dateRange['end']);
        }
    }
    
    private function getDateRange($filters) {
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            return [
                'start' => $filters['start_date'],
                'end' => $filters['end_date']
            ];
        }
        
        $days = intval($filters['date_range'] ?? 30);
        return [
            'start' => date('Y-m-d', strtotime("-{$days} days")),
            'end' => date('Y-m-d')
        ];
    }
    
    private function generateFilename($type, $format) {
        $timestamp = date('Y-m-d_H-i-s');
        return "{$type}_report_{$timestamp}.{$format}";
    }
    
    private function generatePDF($data, $filepath, $type) {
        // Simple HTML to PDF conversion (you can use libraries like TCPDF or mPDF)
        $html = $this->generateHTMLReport($data, $type);
        
        // For now, save as HTML (in production, use proper PDF library)
        $pdfPath = str_replace('.pdf', '.html', $filepath);
        file_put_contents($pdfPath, $html);
        
        // Rename to .pdf for download
        rename($pdfPath, $filepath);
    }
    
    private function generateExcel($data, $filepath, $type) {
        // Simple CSV format (in production, use PhpSpreadsheet)
        $csv = $this->generateCSVContent($data);
        file_put_contents($filepath, $csv);
    }
    
    private function generateCSV($data, $filepath, $type) {
        $csv = $this->generateCSVContent($data);
        file_put_contents($filepath, $csv);
    }
    
    private function generateJSON($data, $filepath, $type) {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($filepath, $json);
    }
    
    private function generateHTMLReport($data, $type) {
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>{$data['title']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .summary { background: #f8f9fa; padding: 15px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .footer { margin-top: 30px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>{$data['title']}</h1>
                <p>Generated on: {$data['generated_at']}</p>
            </div>
        ";
        
        // Add summary if available
        if (isset($data['summary'])) {
            $html .= "<div class='summary'><h3>Summary</h3>";
            foreach ($data['summary'] as $key => $value) {
                $html .= "<p><strong>" . ucwords(str_replace('_', ' ', $key)) . ":</strong> {$value}</p>";
            }
            $html .= "</div>";
        }
        
        // Add data table
        if (!empty($data['data'])) {
            $html .= "<table><thead><tr>";
            
            // Table headers
            $firstRow = $data['data'][0];
            foreach (array_keys($firstRow) as $header) {
                $html .= "<th>" . ucwords(str_replace('_', ' ', $header)) . "</th>";
            }
            $html .= "</tr></thead><tbody>";
            
            // Table data
            foreach ($data['data'] as $row) {
                $html .= "<tr>";
                foreach ($row as $cell) {
                    $html .= "<td>" . htmlspecialchars($cell ?? 'N/A') . "</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        }
        
        $html .= "
            <div class='footer'>
                <p>Report generated by JustExam Advanced Reporting System</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    private function generateCSVContent($data) {
        if (empty($data['data'])) {
            return "No data available\n";
        }
        
        $csv = '';
        
        // Headers
        $firstRow = $data['data'][0];
        $headers = array_keys($firstRow);
        $csv .= implode(',', array_map(function($h) {
            return '"' . ucwords(str_replace('_', ' ', $h)) . '"';
        }, $headers)) . "\n";
        
        // Data rows
        foreach ($data['data'] as $row) {
            $csvRow = [];
            foreach ($row as $cell) {
                $csvRow[] = '"' . str_replace('"', '""', $cell ?? 'N/A') . '"';
            }
            $csv .= implode(',', $csvRow) . "\n";
        }
        
        return $csv;
    }
    
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    // Summary methods (simplified for now)
    private function getStudentPerformanceSummary($filters) {
        return [
            'total_students' => 'Calculated from data',
            'avg_performance' => 'Calculated from data',
            'top_performer' => 'Calculated from data'
        ];
    }
    
    private function getExamAnalyticsSummary($filters) {
        return [
            'total_exams' => 'Calculated from data',
            'avg_difficulty' => 'Calculated from data',
            'most_popular' => 'Calculated from data'
        ];
    }
    
    private function getCourseSummary($filters) {
        return [
            'total_courses' => 'Calculated from data',
            'best_performing' => 'Calculated from data',
            'enrollment_rate' => 'Calculated from data'
        ];
    }
    
    private function getTimeAnalyticsSummary($filters) {
        return [
            'peak_activity_day' => 'Calculated from data',
            'trend_direction' => 'Calculated from data',
            'growth_rate' => 'Calculated from data'
        ];
    }
    
    private function getQuestionAnalysisSummary($filters) {
        return [
            'total_questions' => 'Calculated from data',
            'avg_difficulty' => 'Calculated from data',
            'most_challenging' => 'Calculated from data'
        ];
    }
}
?>