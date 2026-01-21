<?php
session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    sendJSON(['res' => 'unauthorized', 'msg' => 'Please login first'], 401);
}

// Check session timeout
if (!checkSessionTimeout()) {
    session_destroy();
    sendJSON(['res' => 'timeout', 'msg' => 'Session expired'], 401);
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'bulk_import']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

$importType = $_POST['import_type'] ?? '';

// Validate import type
if (!in_array($importType, ['students', 'questions'])) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid import type'], 400);
}

// Check if file was uploaded
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    sendJSON(['res' => 'error', 'msg' => 'File upload failed'], 400);
}

$file = $_FILES['csv_file'];

// Validate file type
if ($file['type'] !== 'text/csv' && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
    sendJSON(['res' => 'invalid', 'msg' => 'Please upload a CSV file'], 400);
}

// Validate file size
$maxSize = $importType === 'students' ? 2 * 1024 * 1024 : 5 * 1024 * 1024; // 2MB for students, 5MB for questions
if ($file['size'] > $maxSize) {
    sendJSON(['res' => 'invalid', 'msg' => 'File size exceeds limit'], 400);
}

try {
    if ($importType === 'students') {
        $result = importStudents($conn, $file, $_POST);
    } else {
        $result = importQuestions($conn, $file, $_POST);
    }

    // Log import activity
    logSecurityEvent('BULK_IMPORT', [
        'type' => $importType,
        'admin_id' => $_SESSION['admin']['admin_id'],
        'filename' => $file['name'],
        'records_processed' => $result['processed'],
        'records_success' => $result['success'],
        'records_failed' => $result['failed']
    ]);

    sendJSON([
        'res' => 'success', 
        'msg' => $result['message'],
        'details' => $result
    ]);

} catch (Exception $e) {
    error_log("Bulk Import Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'Import failed: ' . $e->getMessage()], 500);
}

function importStudents($conn, $file, $postData) {
    $defaultCourse = $postData['default_course'] ?? '';
    $defaultPassword = $postData['default_password'] ?? 'student123';
    $sendWelcomeEmail = isset($postData['send_welcome_email']);

    // Validate default course
    if (!validateId($defaultCourse)) {
        throw new Exception("Invalid default course selected");
    }

    // Verify course exists
    $stmt = $conn->prepare("SELECT cou_id FROM course_tbl WHERE cou_id = ?");
    $stmt->execute([$defaultCourse]);
    if ($stmt->rowCount() === 0) {
        throw new Exception("Default course not found");
    }

    $processed = 0;
    $success = 0;
    $failed = 0;
    $errors = [];

    // Open CSV file
    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
        // Read header row
        $headers = fgetcsv($handle, 1000, ",");
        
        // Validate required headers
        $requiredHeaders = ['fullname', 'email', 'gender', 'birthdate', 'year_level'];
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers)) {
                throw new Exception("Missing required column: $required");
            }
        }

        // Process each row
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $processed++;
            
            try {
                // Map CSV data to array
                $student = array_combine($headers, $data);
                
                // Validate required fields
                foreach ($requiredHeaders as $field) {
                    if (empty(trim($student[$field]))) {
                        throw new Exception("Row $processed: Missing $field");
                    }
                }

                // Validate email format
                if (!validateEmail($student['email'])) {
                    throw new Exception("Row $processed: Invalid email format");
                }

                // Validate gender
                if (!in_array(strtolower($student['gender']), ['male', 'female', 'other'])) {
                    throw new Exception("Row $processed: Invalid gender (must be male, female, or other)");
                }

                // Validate birthdate
                $birthdate = date('Y-m-d', strtotime($student['birthdate']));
                if (!$birthdate || $birthdate === '1970-01-01') {
                    throw new Exception("Row $processed: Invalid birthdate format (use YYYY-MM-DD)");
                }

                // Use course from CSV or default
                $courseId = !empty($student['course_id']) ? $student['course_id'] : $defaultCourse;
                if (!validateId($courseId)) {
                    $courseId = $defaultCourse;
                }

                // Use password from CSV or default
                $password = !empty($student['password']) ? $student['password'] : $defaultPassword;
                $hashedPassword = hashPassword($password);

                // Use status from CSV or default
                $status = !empty($student['status']) ? strtolower($student['status']) : 'active';
                if (!in_array($status, ['active', 'inactive', 'suspended'])) {
                    $status = 'active';
                }

                // Check if email already exists
                $stmt = $conn->prepare("SELECT exmne_id FROM examinee_tbl WHERE exmne_email = ?");
                $stmt->execute([$student['email']]);
                if ($stmt->rowCount() > 0) {
                    throw new Exception("Row $processed: Email already exists");
                }

                // Insert student
                $stmt = $conn->prepare("
                    INSERT INTO examinee_tbl 
                    (exmne_fullname, exmne_email, exmne_gender, exmne_birthdate, 
                     exmne_course, exmne_year_level, exmne_password, exmne_status, 
                     created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ");
                
                $stmt->execute([
                    $student['fullname'],
                    $student['email'],
                    strtolower($student['gender']),
                    $birthdate,
                    $courseId,
                    $student['year_level'],
                    $hashedPassword,
                    $status
                ]);

                $success++;

                // TODO: Send welcome email if requested
                if ($sendWelcomeEmail) {
                    // Email functionality would go here
                }

            } catch (Exception $e) {
                $failed++;
                $errors[] = $e->getMessage();
            }
        }
        
        fclose($handle);
    } else {
        throw new Exception("Could not open CSV file");
    }

    $message = "Import completed: $success successful, $failed failed out of $processed records";
    if (!empty($errors)) {
        $message .= ". Errors: " . implode("; ", array_slice($errors, 0, 5));
        if (count($errors) > 5) {
            $message .= " (and " . (count($errors) - 5) . " more)";
        }
    }

    return [
        'processed' => $processed,
        'success' => $success,
        'failed' => $failed,
        'errors' => $errors,
        'message' => $message
    ];
}

function importQuestions($conn, $file, $postData) {
    $examId = $postData['target_exam'] ?? '';
    $replaceQuestions = isset($postData['replace_questions']);

    // Validate exam ID
    if (!validateId($examId)) {
        throw new Exception("Invalid exam selected");
    }

    // Verify exam exists
    $stmt = $conn->prepare("SELECT ex_id FROM exam_tbl WHERE ex_id = ?");
    $stmt->execute([$examId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception("Target exam not found");
    }

    // Delete existing questions if requested
    if ($replaceQuestions) {
        $stmt = $conn->prepare("DELETE FROM exam_question_tbl WHERE exam_id = ?");
        $stmt->execute([$examId]);
    }

    $processed = 0;
    $success = 0;
    $failed = 0;
    $errors = [];

    // Open CSV file
    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
        // Read header row
        $headers = fgetcsv($handle, 1000, ",");
        
        // Validate required headers
        $requiredHeaders = ['question', 'choice_a', 'choice_b', 'choice_c', 'choice_d', 'correct_answer'];
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers)) {
                throw new Exception("Missing required column: $required");
            }
        }

        // Process each row
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $processed++;
            
            try {
                // Map CSV data to array
                $question = array_combine($headers, $data);
                
                // Validate required fields
                foreach ($requiredHeaders as $field) {
                    if (empty(trim($question[$field]))) {
                        throw new Exception("Row $processed: Missing $field");
                    }
                }

                // Validate question length
                if (strlen($question['question']) > 1000) {
                    throw new Exception("Row $processed: Question too long (max 1000 characters)");
                }

                // Validate choice lengths
                $choices = ['choice_a', 'choice_b', 'choice_c', 'choice_d'];
                foreach ($choices as $choice) {
                    if (strlen($question[$choice]) > 500) {
                        throw new Exception("Row $processed: $choice too long (max 500 characters)");
                    }
                }

                // Validate correct answer matches one of the choices
                $correctAnswer = trim($question['correct_answer']);
                $choiceValues = [
                    trim($question['choice_a']),
                    trim($question['choice_b']),
                    trim($question['choice_c']),
                    trim($question['choice_d'])
                ];

                if (!in_array($correctAnswer, $choiceValues)) {
                    throw new Exception("Row $processed: Correct answer must match exactly one of the choices");
                }

                // Insert question
                $stmt = $conn->prepare("
                    INSERT INTO exam_question_tbl 
                    (exam_id, exam_question, exam_ch1, exam_ch2, exam_ch3, exam_ch4, 
                     exam_answer, exam_status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)
                ");
                
                $stmt->execute([
                    $examId,
                    $question['question'],
                    $question['choice_a'],
                    $question['choice_b'],
                    $question['choice_c'],
                    $question['choice_d'],
                    $correctAnswer
                ]);

                $success++;

            } catch (Exception $e) {
                $failed++;
                $errors[] = $e->getMessage();
            }
        }
        
        fclose($handle);
    } else {
        throw new Exception("Could not open CSV file");
    }

    $message = "Import completed: $success questions imported, $failed failed out of $processed records";
    if (!empty($errors)) {
        $message .= ". Errors: " . implode("; ", array_slice($errors, 0, 3));
        if (count($errors) > 3) {
            $message .= " (and " . (count($errors) - 3) . " more)";
        }
    }

    return [
        'processed' => $processed,
        'success' => $success,
        'failed' => $failed,
        'errors' => $errors,
        'message' => $message
    ];
}
?>