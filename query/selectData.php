<?php 
// Check if user is logged in
if (!isset($_SESSION['student'])) {
    die("Unauthorized access");
}

$exmneId = $_SESSION['student']['user_id'];

// Validate ID
if (!validateId($exmneId)) {
    die("Invalid session data");
}

try {
    // Select Data of logged in examinee using prepared statement
    $stmt = $conn->prepare("SELECT * FROM examinee_tbl WHERE exmne_id = ?");
    $stmt->execute([$exmneId]);
    $selExmneeData = $stmt->fetch();
    
    if (!$selExmneeData) {
        die("User not found");
    }
    
    $exmneCourse = $selExmneeData['exmne_course'];
    
    // Validate course ID
    if (!validateId($exmneCourse)) {
        die("Invalid course data");
    }
    
    // Select and show exam depended on course and login using prepared statement
    $stmt = $conn->prepare("SELECT * FROM exam_tbl WHERE cou_id = ? ORDER BY ex_id DESC");
    $stmt->execute([$exmneCourse]);
    $selExam = $stmt;
    
} catch (PDOException $e) {
    error_log("Select Data Error: " . $e->getMessage());
    die("An error occurred while loading data");
}
?>