<?php
/**
 * Advanced Report Generator
 * Handles PDF and Excel report generation with comprehensive analytics
 */

require_once("../../../config.php");
require_once("../../../security.php");

class ReportGenerator {
    private $conn;
    private $reportTypes = [
        'exam_performance',
        'student_progress', 
        'course_analytics',
        'comparative_analysis',
        'detailed_results'
    ];
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Generate Exam Performance Report
     */
    public function generateExamPerformanceReport($examId, $format = 'pdf') {
        try {
            $data = $this->getExamPerformanceData($examId);
            
            if ($format === 'pdf') {
                return $this->generatePDFReport($data, 'exam_performance');
            } elseif ($format === 'excel') {
                return $this->generateExcelReport($data, 'exam_performance');
            }
            
            throw new Exception("Unsupported format: $format");
            
        } catch (Exception $e) {
            logSecurityEvent('REPORT_GENERATION_ERROR', [
                'exam_id' => $examId,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate Student Progress Report
     */
    public function generateStudentProgressReport($studentId, $format = 'pdf') {
        try {
            $data = $this->getStudentProgressData($studentId);
            
            if ($format === 'pdf') {
                return $this->generatePDFReport($data, 'student_progress');
            } elseif ($format === 'excel') {
                return $this->generateExcelReport($data, 'student_progress');
            }
            
            throw new Exception("Unsupported format: $format");
            
        } catch (Exception $e) {
            logSecurityEvent('REPORT_GENERATION_ERROR', [
                'student_id' => $studentId,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate Course Analytics Report
     */
    public function generateCourseAnalyticsReport($courseId, $format = 'pdf') {
        try {
            $data = $this->getCourseAnalyticsData($courseId);
            
            if ($format === 'pdf') {
                return $this->generatePDFReport($data, 'course_analytics');
            } elseif ($format === 'excel') {
                return $this->generateExcelReport($data, 'course_analytics');
            }
            
            throw new Exception("Unsupported format: $format");
            
        } catch (Exception $e) {
            logSecurityEvent('REPORT_GENERATION_ERROR', [
                'course_id' => $courseId,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate Comparative Analysis Report
     */
    public function generateComparativeReport($examIds, $format = 'pdf') {
        try {
            $data = $this->getComparativeAnalysisData($examIds);
            
            if ($format === 'pdf') {
                return $this->generatePDFReport($data, 'comparative_analysis');
            } elseif ($format === 'excel') {
                return $this->generateExcelReport($data, 'comparative_analysis');
            }
            
            throw new Exception("Unsupported format: $format");
            
        } catch (Exception $e) {
            logSecurityEvent('REPORT_GENERATION_ERROR', [
                'exam_ids' => implode(',', $examIds),
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get Exam Performance Data
     */
    private function getExamPerformanceData($examId) {
        // Validate exam ID
        if (!validateId($examId)) {
            throw new Exception("Invalid exam ID");
        }
        
        $data = [];
        
        // Basic exam info
        $stmt = $this->conn->prepare("
            SELECT e.*, c.cou_name 
            FROM exam_tbl e 
            JOIN course_tbl c ON e.cou_id = c.cou_id 
            WHERE e.ex_id = ?
        ");
        $stmt->execute([$examId]);
        $data['exam_info'] = $stmt->fetch();
        
        if (!$data['exam_info']) {
            throw new Exception("Exam not found");
        }
        
        // Exam statistics
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_attempts,
                COUNT(CASE WHEN score IS NOT NULL THEN 1 END) as completed_attempts,
                AVG(score) as avg_score,
                MAX(score) as max_score,
                MIN(score) as min_score,
                STDDEV(score) as score_stddev
            FROM exam_attempt 
            WHERE exam_id = ?
        ");
        $stmt->execute([$examId]);
        $data['statistics'] = $stmt->fetch();
        
        // Score distribution
        $stmt = $this->conn->prepare("
            SELECT 
                CASE 
                    WHEN score >= 90 THEN 'A (90-100%)'
                    WHEN score >= 80 THEN 'B (80-89%)'
                    WHEN score >= 70 THEN 'C (70-79%)'
                    WHEN score >= 60 THEN 'D (60-69%)'
                    ELSE 'F (Below 60%)'
                END as grade,
                COUNT(*) as count
            FROM exam_attempt 
            WHERE exam_id = ? AND score IS NOT NULL
            GROUP BY 
                CASE 
                    WHEN score >= 90 THEN 'A (90-100%)'
                    WHEN score >= 80 THEN 'B (80-89%)'
                    WHEN score >= 70 THEN 'C (70-79%)'
                    WHEN score >= 60 THEN 'D (60-69%)'
                    ELSE 'F (Below 60%)'
                END
            ORDER BY MIN(score) DESC
        ");
        $stmt->execute([$examId]);
        $data['score_distribution'] = $stmt->fetchAll();
        
        // Student results
        $stmt = $this->conn->prepare("
            SELECT 
                et.exmne_fullname,
                et.exmne_email,
                ea.score,
                ea.examStarted,
                ea.examSubmitted,
                TIMESTAMPDIFF(MINUTE, ea.examStarted, ea.examSubmitted) as duration_minutes
            FROM exam_attempt ea
            JOIN examinee_tbl et ON ea.exmne_id = et.exmne_id
            WHERE ea.exam_id = ? AND ea.score IS NOT NULL
            ORDER BY ea.score DESC, ea.examSubmitted ASC
        ");
        $stmt->execute([$examId]);
        $data['student_results'] = $stmt->fetchAll();
        
        // Question analysis
        $stmt = $this->conn->prepare("
            SELECT 
                eq.exam_question,
                eq.exam_ch1, eq.exam_ch2, eq.exam_ch3, eq.exam_ch4,
                eq.exam_answer,
                COUNT(ea.exans_answer) as total_responses,
                COUNT(CASE WHEN ea.exans_answer = eq.exam_answer THEN 1 END) as correct_responses,
                ROUND((COUNT(CASE WHEN ea.exans_answer = eq.exam_answer THEN 1 END) / COUNT(ea.exans_answer)) * 100, 2) as success_rate
            FROM exam_question_tbl eq
            LEFT JOIN exam_answers ea ON eq.eqt_id = ea.quest_id
            WHERE eq.exam_id = ?
            GROUP BY eq.eqt_id
            ORDER BY success_rate ASC
        ");
        $stmt->execute([$examId]);
        $data['question_analysis'] = $stmt->fetchAll();
        
        return $data;
    }
    
    /**
     * Get Student Progress Data
     */
    private function getStudentProgressData($studentId) {
        if (!validateId($studentId)) {
            throw new Exception("Invalid student ID");
        }
        
        $data = [];
        
        // Student info
        $stmt = $this->conn->prepare("SELECT * FROM examinee_tbl WHERE exmne_id = ?");
        $stmt->execute([$studentId]);
        $data['student_info'] = $stmt->fetch();
        
        if (!$data['student_info']) {
            throw new Exception("Student not found");
        }
        
        // Overall statistics
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_attempts,
                COUNT(CASE WHEN score IS NOT NULL THEN 1 END) as completed_exams,
                AVG(score) as avg_score,
                MAX(score) as best_score,
                MIN(score) as lowest_score
            FROM exam_attempt 
            WHERE exmne_id = ?
        ");
        $stmt->execute([$studentId]);
        $data['overall_stats'] = $stmt->fetch();
        
        // Exam history
        $stmt = $this->conn->prepare("
            SELECT 
                e.ex_title,
                c.cou_name,
                ea.score,
                ea.examStarted,
                ea.examSubmitted,
                TIMESTAMPDIFF(MINUTE, ea.examStarted, ea.examSubmitted) as duration_minutes
            FROM exam_attempt ea
            JOIN exam_tbl e ON ea.exam_id = e.ex_id
            JOIN course_tbl c ON e.cou_id = c.cou_id
            WHERE ea.exmne_id = ? AND ea.score IS NOT NULL
            ORDER BY ea.examStarted DESC
        ");
        $stmt->execute([$studentId]);
        $data['exam_history'] = $stmt->fetchAll();
        
        // Performance by course
        $stmt = $this->conn->prepare("
            SELECT 
                c.cou_name,
                COUNT(*) as attempts,
                AVG(ea.score) as avg_score,
                MAX(ea.score) as best_score
            FROM exam_attempt ea
            JOIN exam_tbl e ON ea.exam_id = e.ex_id
            JOIN course_tbl c ON e.cou_id = c.cou_id
            WHERE ea.exmne_id = ? AND ea.score IS NOT NULL
            GROUP BY c.cou_id, c.cou_name
            ORDER BY avg_score DESC
        ");
        $stmt->execute([$studentId]);
        $data['course_performance'] = $stmt->fetchAll();
        
        // Progress trend (monthly)
        $stmt = $this->conn->prepare("
            SELECT 
                DATE_FORMAT(ea.examStarted, '%Y-%m') as month,
                COUNT(*) as attempts,
                AVG(ea.score) as avg_score
            FROM exam_attempt ea
            WHERE ea.exmne_id = ? AND ea.score IS NOT NULL
            GROUP BY DATE_FORMAT(ea.examStarted, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute([$studentId]);
        $data['progress_trend'] = $stmt->fetchAll();
        
        return $data;
    }
    
    /**
     * Get Course Analytics Data
     */
    private function getCourseAnalyticsData($courseId) {
        if (!validateId($courseId)) {
            throw new Exception("Invalid course ID");
        }
        
        $data = [];
        
        // Course info
        $stmt = $this->conn->prepare("SELECT * FROM course_tbl WHERE cou_id = ?");
        $stmt->execute([$courseId]);
        $data['course_info'] = $stmt->fetch();
        
        if (!$data['course_info']) {
            throw new Exception("Course not found");
        }
        
        // Course statistics
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(DISTINCT e.ex_id) as total_exams,
                COUNT(DISTINCT ea.exmne_id) as unique_students,
                COUNT(ea.attemptId) as total_attempts,
                AVG(ea.score) as avg_score,
                MAX(ea.score) as max_score,
                MIN(ea.score) as min_score
            FROM exam_tbl e
            LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
            WHERE e.cou_id = ?
        ");
        $stmt->execute([$courseId]);
        $data['course_stats'] = $stmt->fetch();
        
        // Exam performance in course
        $stmt = $this->conn->prepare("
            SELECT 
                e.ex_title,
                e.ex_time_limit,
                COUNT(ea.attemptId) as attempts,
                AVG(ea.score) as avg_score,
                MAX(ea.score) as max_score,
                MIN(ea.score) as min_score
            FROM exam_tbl e
            LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
            WHERE e.cou_id = ? AND ea.score IS NOT NULL
            GROUP BY e.ex_id
            ORDER BY avg_score DESC
        ");
        $stmt->execute([$courseId]);
        $data['exam_performance'] = $stmt->fetchAll();
        
        // Student performance in course
        $stmt = $this->conn->prepare("
            SELECT 
                et.exmne_fullname,
                et.exmne_email,
                COUNT(ea.attemptId) as attempts,
                AVG(ea.score) as avg_score,
                MAX(ea.score) as best_score
            FROM examinee_tbl et
            JOIN exam_attempt ea ON et.exmne_id = ea.exmne_id
            JOIN exam_tbl e ON ea.exam_id = e.ex_id
            WHERE e.cou_id = ? AND ea.score IS NOT NULL
            GROUP BY et.exmne_id
            ORDER BY avg_score DESC
            LIMIT 20
        ");
        $stmt->execute([$courseId]);
        $data['top_students'] = $stmt->fetchAll();
        
        return $data;
    }
    
    /**
     * Get Comparative Analysis Data
     */
    private function getComparativeAnalysisData($examIds) {
        if (empty($examIds) || !is_array($examIds)) {
            throw new Exception("Invalid exam IDs");
        }
        
        // Validate all exam IDs
        foreach ($examIds as $examId) {
            if (!validateId($examId)) {
                throw new Exception("Invalid exam ID: $examId");
            }
        }
        
        $data = [];
        $placeholders = str_repeat('?,', count($examIds) - 1) . '?';
        
        // Exam comparison
        $stmt = $this->conn->prepare("
            SELECT 
                e.ex_id,
                e.ex_title,
                c.cou_name,
                COUNT(ea.attemptId) as attempts,
                AVG(ea.score) as avg_score,
                MAX(ea.score) as max_score,
                MIN(ea.score) as min_score,
                STDDEV(ea.score) as score_stddev
            FROM exam_tbl e
            JOIN course_tbl c ON e.cou_id = c.cou_id
            LEFT JOIN exam_attempt ea ON e.ex_id = ea.exam_id
            WHERE e.ex_id IN ($placeholders) AND ea.score IS NOT NULL
            GROUP BY e.ex_id
            ORDER BY avg_score DESC
        ");
        $stmt->execute($examIds);
        $data['exam_comparison'] = $stmt->fetchAll();
        
        // Difficulty analysis
        $stmt = $this->conn->prepare("
            SELECT 
                e.ex_title,
                AVG(ea.score) as avg_score,
                CASE 
                    WHEN AVG(ea.score) >= 80 THEN 'Easy'
                    WHEN AVG(ea.score) >= 60 THEN 'Medium'
                    ELSE 'Hard'
                END as difficulty_level
            FROM exam_tbl e
            JOIN exam_attempt ea ON e.ex_id = ea.exam_id
            WHERE e.ex_id IN ($placeholders) AND ea.score IS NOT NULL
            GROUP BY e.ex_id
        ");
        $stmt->execute($examIds);
        $data['difficulty_analysis'] = $stmt->fetchAll();
        
        return $data;
    }
    
    /**
     * Generate PDF Report (placeholder - will implement with TCPDF)
     */
    private function generatePDFReport($data, $reportType) {
        // This will be implemented with TCPDF library
        return $this->generateHTMLReport($data, $reportType);
    }
    
    /**
     * Generate Excel Report (placeholder - will implement with PhpSpreadsheet)
     */
    private function generateExcelReport($data, $reportType) {
        // This will be implemented with PhpSpreadsheet library
        return $this->generateCSVReport($data, $reportType);
    }
    
    /**
     * Generate HTML Report (for preview and PDF conversion)
     */
    private function generateHTMLReport($data, $reportType) {
        ob_start();
        
        switch ($reportType) {
            case 'exam_performance':
                include 'templates/exam_performance_template.php';
                break;
            case 'student_progress':
                include 'templates/student_progress_template.php';
                break;
            case 'course_analytics':
                include 'templates/course_analytics_template.php';
                break;
            case 'comparative_analysis':
                include 'templates/comparative_analysis_template.php';
                break;
            default:
                throw new Exception("Unknown report type: $reportType");
        }
        
        return ob_get_clean();
    }
    
    /**
     * Generate CSV Report
     */
    private function generateCSVReport($data, $reportType) {
        $filename = "reports/{$reportType}_" . date('Y-m-d_H-i-s') . ".csv";
        $filepath = __DIR__ . "/../../../" . $filename;
        
        // Ensure reports directory exists
        $reportsDir = dirname($filepath);
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        switch ($reportType) {
            case 'exam_performance':
                $this->writeExamPerformanceCSV($file, $data);
                break;
            case 'student_progress':
                $this->writeStudentProgressCSV($file, $data);
                break;
            case 'course_analytics':
                $this->writeCourseAnalyticsCSV($file, $data);
                break;
            case 'comparative_analysis':
                $this->writeComparativeAnalysisCSV($file, $data);
                break;
        }
        
        fclose($file);
        return $filename;
    }
    
    /**
     * Write Exam Performance CSV
     */
    private function writeExamPerformanceCSV($file, $data) {
        // Header
        fputcsv($file, ['Exam Performance Report']);
        fputcsv($file, ['Generated on: ' . date('Y-m-d H:i:s')]);
        fputcsv($file, []);
        
        // Exam info
        fputcsv($file, ['Exam Information']);
        fputcsv($file, ['Title', $data['exam_info']['ex_title']]);
        fputcsv($file, ['Course', $data['exam_info']['cou_name']]);
        fputcsv($file, ['Description', $data['exam_info']['ex_description']]);
        fputcsv($file, []);
        
        // Statistics
        fputcsv($file, ['Statistics']);
        fputcsv($file, ['Total Attempts', $data['statistics']['total_attempts']]);
        fputcsv($file, ['Completed Attempts', $data['statistics']['completed_attempts']]);
        fputcsv($file, ['Average Score', round($data['statistics']['avg_score'], 2) . '%']);
        fputcsv($file, ['Highest Score', round($data['statistics']['max_score'], 2) . '%']);
        fputcsv($file, ['Lowest Score', round($data['statistics']['min_score'], 2) . '%']);
        fputcsv($file, []);
        
        // Student results
        fputcsv($file, ['Student Results']);
        fputcsv($file, ['Student Name', 'Email', 'Score (%)', 'Start Time', 'Submit Time', 'Duration (min)']);
        
        foreach ($data['student_results'] as $result) {
            fputcsv($file, [
                $result['exmne_fullname'],
                $result['exmne_email'],
                $result['score'],
                $result['examStarted'],
                $result['examSubmitted'],
                $result['duration_minutes']
            ]);
        }
    }
    
    /**
     * Write Student Progress CSV
     */
    private function writeStudentProgressCSV($file, $data) {
        // Header
        fputcsv($file, ['Student Progress Report']);
        fputcsv($file, ['Generated on: ' . date('Y-m-d H:i:s')]);
        fputcsv($file, []);
        
        // Student info
        fputcsv($file, ['Student Information']);
        fputcsv($file, ['Name', $data['student_info']['exmne_fullname']]);
        fputcsv($file, ['Email', $data['student_info']['exmne_email']]);
        fputcsv($file, []);
        
        // Overall stats
        fputcsv($file, ['Overall Statistics']);
        fputcsv($file, ['Total Attempts', $data['overall_stats']['total_attempts']]);
        fputcsv($file, ['Completed Exams', $data['overall_stats']['completed_exams']]);
        fputcsv($file, ['Average Score', round($data['overall_stats']['avg_score'], 2) . '%']);
        fputcsv($file, ['Best Score', round($data['overall_stats']['best_score'], 2) . '%']);
        fputcsv($file, []);
        
        // Exam history
        fputcsv($file, ['Exam History']);
        fputcsv($file, ['Exam Title', 'Course', 'Score (%)', 'Date', 'Duration (min)']);
        
        foreach ($data['exam_history'] as $exam) {
            fputcsv($file, [
                $exam['ex_title'],
                $exam['cou_name'],
                $exam['score'],
                $exam['examStarted'],
                $exam['duration_minutes']
            ]);
        }
    }
    
    /**
     * Write Course Analytics CSV
     */
    private function writeCourseAnalyticsCSV($file, $data) {
        // Header
        fputcsv($file, ['Course Analytics Report']);
        fputcsv($file, ['Generated on: ' . date('Y-m-d H:i:s')]);
        fputcsv($file, []);
        
        // Course info
        fputcsv($file, ['Course Information']);
        fputcsv($file, ['Name', $data['course_info']['cou_name']]);
        fputcsv($file, ['Description', $data['course_info']['cou_description']]);
        fputcsv($file, []);
        
        // Course stats
        fputcsv($file, ['Course Statistics']);
        fputcsv($file, ['Total Exams', $data['course_stats']['total_exams']]);
        fputcsv($file, ['Unique Students', $data['course_stats']['unique_students']]);
        fputcsv($file, ['Total Attempts', $data['course_stats']['total_attempts']]);
        fputcsv($file, ['Average Score', round($data['course_stats']['avg_score'], 2) . '%']);
        fputcsv($file, []);
        
        // Exam performance
        fputcsv($file, ['Exam Performance']);
        fputcsv($file, ['Exam Title', 'Attempts', 'Average Score (%)', 'Highest Score (%)', 'Lowest Score (%)']);
        
        foreach ($data['exam_performance'] as $exam) {
            fputcsv($file, [
                $exam['ex_title'],
                $exam['attempts'],
                round($exam['avg_score'], 2),
                round($exam['max_score'], 2),
                round($exam['min_score'], 2)
            ]);
        }
    }
    
    /**
     * Write Comparative Analysis CSV
     */
    private function writeComparativeAnalysisCSV($file, $data) {
        // Header
        fputcsv($file, ['Comparative Analysis Report']);
        fputcsv($file, ['Generated on: ' . date('Y-m-d H:i:s')]);
        fputcsv($file, []);
        
        // Exam comparison
        fputcsv($file, ['Exam Comparison']);
        fputcsv($file, ['Exam Title', 'Course', 'Attempts', 'Average Score (%)', 'Highest Score (%)', 'Lowest Score (%)']);
        
        foreach ($data['exam_comparison'] as $exam) {
            fputcsv($file, [
                $exam['ex_title'],
                $exam['cou_name'],
                $exam['attempts'],
                round($exam['avg_score'], 2),
                round($exam['max_score'], 2),
                round($exam['min_score'], 2)
            ]);
        }
    }
}
?>