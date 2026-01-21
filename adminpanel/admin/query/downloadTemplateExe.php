<?php
session_start();
require_once("../../../config.php");
require_once("../../../security.php");

// Check if admin is logged in
if (!isset($_SESSION['admin']['is_logged_in']) || $_SESSION['admin']['is_logged_in'] !== true) {
    die("Unauthorized access");
}

// Verify CSRF Token
if (!isset($_GET['csrf_token']) || !verifyCSRFToken($_GET['csrf_token'])) {
    die("Invalid request");
}

$templateType = $_GET['type'] ?? '';

// Validate template type
if (!in_array($templateType, ['students', 'questions'])) {
    die("Invalid template type");
}

// Set headers for CSV download
$filename = $templateType . '_import_template.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create file pointer connected to the output stream
$output = fopen('php://output', 'w');

if ($templateType === 'students') {
    generateStudentTemplate($output);
} else {
    generateQuestionTemplate($output);
}

fclose($output);

function generateStudentTemplate($output) {
    // CSV headers
    fputcsv($output, [
        'fullname',
        'email',
        'gender',
        'birthdate',
        'year_level',
        'course_id',
        'password',
        'status'
    ]);

    // Sample data rows
    fputcsv($output, [
        'John Doe',
        'john.doe@example.com',
        'male',
        '1995-06-15',
        '2nd Year',
        '1',
        'student123',
        'active'
    ]);

    fputcsv($output, [
        'Jane Smith',
        'jane.smith@example.com',
        'female',
        '1996-03-22',
        '1st Year',
        '1',
        'student123',
        'active'
    ]);

    fputcsv($output, [
        'Alex Johnson',
        'alex.johnson@example.com',
        'other',
        '1994-11-08',
        '3rd Year',
        '2',
        'student123',
        'active'
    ]);

    // Add comment rows (will be ignored during import)
    fputcsv($output, []);
    fputcsv($output, ['# INSTRUCTIONS:']);
    fputcsv($output, ['# - Required columns: fullname, email, gender, birthdate, year_level']);
    fputcsv($output, ['# - Optional columns: course_id, password, status']);
    fputcsv($output, ['# - Gender values: male, female, other']);
    fputcsv($output, ['# - Date format: YYYY-MM-DD (e.g., 1995-06-15)']);
    fputcsv($output, ['# - Status values: active, inactive, suspended (default: active)']);
    fputcsv($output, ['# - If course_id is empty, default course will be used']);
    fputcsv($output, ['# - If password is empty, default password will be used']);
    fputcsv($output, ['# - Delete these instruction rows before importing']);
}

function generateQuestionTemplate($output) {
    // CSV headers
    fputcsv($output, [
        'question',
        'choice_a',
        'choice_b',
        'choice_c',
        'choice_d',
        'correct_answer'
    ]);

    // Sample data rows
    fputcsv($output, [
        'What is the capital of France?',
        'London',
        'Berlin',
        'Paris',
        'Madrid',
        'Paris'
    ]);

    fputcsv($output, [
        'Which programming language is known for web development?',
        'Python',
        'JavaScript',
        'C++',
        'Assembly',
        'JavaScript'
    ]);

    fputcsv($output, [
        'What does HTML stand for?',
        'Hypertext Markup Language',
        'High Tech Modern Language',
        'Home Tool Markup Language',
        'Hyperlink and Text Markup Language',
        'Hypertext Markup Language'
    ]);

    // Add comment rows (will be ignored during import)
    fputcsv($output, []);
    fputcsv($output, ['# INSTRUCTIONS:']);
    fputcsv($output, ['# - All columns are required']);
    fputcsv($output, ['# - Question: Maximum 1000 characters']);
    fputcsv($output, ['# - Choices: Maximum 500 characters each']);
    fputcsv($output, ['# - Correct answer must match exactly one of the choices']);
    fputcsv($output, ['# - Questions will be added to the selected exam']);
    fputcsv($output, ['# - Delete these instruction rows before importing']);
}
?>