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
    logSecurityEvent('CSRF_ATTEMPT', ['type' => 'add_examinee']);
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid request'], 403);
}

// Get and validate input
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$gender = $_POST['gender'] ?? '0';
$course = $_POST['course'] ?? '0';
$year_level = $_POST['year_level'] ?? '0';
$bdate = $_POST['bdate'] ?? '';

// Validate required fields
$errors = validateRequired([
    'fullname' => $fullname,
    'email' => $email,
    'password' => $password,
    'birthdate' => $bdate
]);

if (!empty($errors)) {
    sendJSON(['res' => 'invalid', 'msg' => implode(', ', $errors)], 400);
}

// Validate selections
if ($gender == "0") {
    sendJSON(['res' => 'noGender', 'msg' => 'Please select gender'], 400);
}

if ($course == "0") {
    sendJSON(['res' => 'noCourse', 'msg' => 'Please select course'], 400);
}

if ($year_level == "0") {
    sendJSON(['res' => 'noLevel', 'msg' => 'Please select year level'], 400);
}

// Validate email format
if (!validateEmail($email)) {
    sendJSON(['res' => 'invalid', 'msg' => 'Invalid email format'], 400);
}

// Validate password strength (minimum 4 characters)
if (strlen($password) < 4) {
    sendJSON(['res' => 'invalid', 'msg' => 'Password must be at least 4 characters'], 400);
}

try {
    // Check if fullname exists using prepared statement
    $stmt = $conn->prepare("SELECT * FROM examinee_tbl WHERE exmne_fullname = ?");
    $stmt->execute([$fullname]);
    
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'fullnameExist', 'msg' => "Fullname '$fullname' already exists"], 400);
    }
    
    // Check if email exists using prepared statement
    $stmt = $conn->prepare("SELECT * FROM examinee_tbl WHERE exmne_email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        sendJSON(['res' => 'emailExist', 'msg' => "Email '$email' already exists"], 400);
    }
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert new examinee using prepared statement
    $stmt = $conn->prepare("INSERT INTO examinee_tbl(exmne_fullname, exmne_course, exmne_gender, exmne_birthdate, exmne_year_level, exmne_email, exmne_password) VALUES(?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fullname, $course, $gender, $bdate, $year_level, $email, $hashedPassword]);
    
    logSecurityEvent('EXAMINEE_ADDED', [
        'admin_id' => $_SESSION['admin']['user_id'],
        'email' => $email
    ]);
    
    $res = ['res' => 'success', 'msg' => "Student '$email' added successfully"];
    
} catch (PDOException $e) {
    error_log("Add Examinee Error: " . $e->getMessage());
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again.'], 500);
}

echo json_encode($res);
?>