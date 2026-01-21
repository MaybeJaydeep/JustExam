<?php
session_start();
require_once("config.php");
require_once("security.php");

// Get token from URL
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("location: index.php");
    exit;
}

// Validate token
$stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE token = ? AND used = 0 AND expires_at > NOW()");
$stmt->execute([$token]);
$resetData = $stmt->fetch();

if (!$resetData) {
    $error = "Invalid or expired reset token. Please request a new password reset.";
}

// Handle password reset form submission
if ($_POST && !isset($error)) {
    try {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate passwords
        if (empty($newPassword) || empty($confirmPassword)) {
            throw new Exception('Please fill in all fields');
        }

        if (strlen($newPassword) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }

        if ($newPassword !== $confirmPassword) {
            throw new Exception('Passwords do not match');
        }

        // Hash new password
        $hashedPassword = hashPassword($newPassword);

        // Update password in appropriate table
        $table = ($resetData['user_type'] === 'admin') ? 'admin_acc' : 'examinee_tbl';
        $emailField = ($resetData['user_type'] === 'admin') ? 'admin_email' : 'exmne_email';
        $passwordField = ($resetData['user_type'] === 'admin') ? 'admin_pass' : 'exmne_password';

        $stmt = $conn->prepare("UPDATE $table SET $passwordField = ? WHERE $emailField = ?");
        $stmt->execute([$hashedPassword, $resetData['email']]);

        // Mark token as used
        $stmt = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);

        // Log security event
        logSecurityEvent('password_reset_completed', [
            'email' => $resetData['email'],
            'user_type' => $resetData['user_type']
        ]);

        $success = "Password reset successfully! You can now login with your new password.";

    } catch (Exception $e) {
        $error = $e->getMessage();
        logSecurityEvent('password_reset_failed', [
            'email' => $resetData['email'] ?? 'unknown',
            'error' => $e->getMessage()
        ]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reset Password - JustExam</title>
    <link href="css/sweetalert.css" rel="stylesheet">
    <link href="login-ui/css/main.css" rel="stylesheet">
    <style>
        .reset-password-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .reset-password-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h2 {
            color: #667eea;
            font-weight: bold;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-card">
            <div class="logo">
                <h2>🎓 JustExam</h2>
                <p>Reset Your Password</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo escape($success); ?>
                </div>
                <div class="back-link">
                    <a href="index.php">← Back to Login</a>
                </div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo escape($error); ?>
                </div>
                <div class="back-link">
                    <a href="pages/forgot-password.php">← Request New Reset</a> |
                    <a href="index.php">Back to Login</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="form-control" 
                               placeholder="Enter new password"
                               required>
                        <div class="password-requirements">
                            Password must be at least 6 characters long
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control" 
                               placeholder="Confirm new password"
                               required>
                    </div>

                    <button type="submit" class="btn">
                        Reset Password
                    </button>
                </form>

                <div class="back-link">
                    <a href="index.php">← Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>