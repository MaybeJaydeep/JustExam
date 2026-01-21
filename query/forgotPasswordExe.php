<?php
session_start();
require_once("../config.php");
require_once("../security.php");

header('Content-Type: application/json');

try {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        sendJSON(['res' => 'error', 'msg' => 'Invalid security token'], 403);
    }

    // Validate required fields
    $required = ['email', 'user_type'];
    $errors = validateRequired($_POST);
    if (!empty($errors)) {
        sendJSON(['res' => 'error', 'msg' => implode(', ', $errors)]);
    }

    $email = trim($_POST['email']);
    $userType = trim($_POST['user_type']);

    // Validate email format
    if (!validateEmail($email)) {
        sendJSON(['res' => 'error', 'msg' => 'Please enter a valid email address']);
    }

    // Validate user type
    if (!in_array($userType, ['student', 'admin'])) {
        sendJSON(['res' => 'error', 'msg' => 'Invalid account type']);
    }

    // Check if email exists in the appropriate table
    $table = ($userType === 'admin') ? 'admin_acc' : 'examinee_tbl';
    $emailField = ($userType === 'admin') ? 'admin_email' : 'exmne_email';
    $nameField = ($userType === 'admin') ? 'admin_name' : 'exmne_fullname';

    $stmt = $conn->prepare("SELECT $emailField, $nameField FROM $table WHERE $emailField = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Don't reveal if email exists or not for security
        sendJSON([
            'res' => 'success', 
            'msg' => 'If an account with this email exists, password reset instructions have been sent.'
        ]);
    }

    // Generate secure reset token
    $resetToken = bin2hex(random_bytes(32));
    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store reset token in database
    $resetTable = 'password_reset_tokens';
    
    // Create table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            user_type ENUM('student', 'admin') NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_email_type (email, user_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $conn->exec($createTableSQL);

    // Delete any existing tokens for this email/user_type
    $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ? AND user_type = ?");
    $stmt->execute([$email, $userType]);

    // Insert new reset token
    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, user_type, token, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $userType, $resetToken, $tokenExpiry]);

    // Log security event
    logSecurityEvent('password_reset_requested', [
        'email' => $email,
        'user_type' => $userType,
        'token_expires' => $tokenExpiry
    ]);

    // In a real implementation, you would send an email here
    // For now, we'll simulate the email sending process
    
    /*
    // Email sending code would go here:
    $resetLink = "https://yourdomain.com/reset-password.php?token=" . $resetToken;
    $subject = "Password Reset - JustExam";
    $message = "
        Hello " . $user[$nameField] . ",
        
        You requested a password reset for your JustExam account.
        
        Click the link below to reset your password:
        $resetLink
        
        This link will expire in 1 hour.
        
        If you didn't request this reset, please ignore this email.
        
        Best regards,
        JustExam Team
    ";
    
    // Send email using your preferred method (PHPMailer, mail(), etc.)
    $emailSent = sendEmail($email, $subject, $message);
    
    if (!$emailSent) {
        sendJSON(['res' => 'error', 'msg' => 'Failed to send reset email. Please try again.']);
    }
    */

    // For development/demo purposes, we'll return success
    // In production, remove this and uncomment the email sending code above
    sendJSON([
        'res' => 'success',
        'msg' => 'Password reset instructions have been sent to your email address.',
        'dev_note' => 'Email functionality not configured. Reset token: ' . $resetToken
    ]);

} catch (Exception $e) {
    error_log("Password Reset Error: " . $e->getMessage());
    logSecurityEvent('password_reset_error', ['error' => $e->getMessage()]);
    sendJSON(['res' => 'error', 'msg' => 'An error occurred. Please try again later.']);
}
?>