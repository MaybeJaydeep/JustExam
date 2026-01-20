<?php
session_start();
require_once("config.php");
require_once("security.php");

// If already logged in, redirect
if (isset($_SESSION['student']['is_logged_in']) && $_SESSION['student']['is_logged_in'] === true) {
    header("location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Forgot Password - JustExam</title>
    <link href="css/sweetalert.css" rel="stylesheet">
    <link href="login-ui/css/main.css" rel="stylesheet">
    <style>
        .forgot-password-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .forgot-password-card {
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
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="forgot-password-card">
            <div class="logo">
                <h2>🎓 JustExam</h2>
                <p>Reset Your Password</p>
            </div>

            <div class="alert alert-info">
                <small>Enter your email address and we'll help you reset your password.</small>
            </div>

            <form id="forgotPasswordForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="Enter your email address"
                           required>
                </div>

                <div class="form-group">
                    <label for="user_type">Account Type</label>
                    <select id="user_type" name="user_type" class="form-control" required>
                        <option value="">Select Account Type</option>
                        <option value="student">Student</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <button type="submit" class="btn">
                    Send Reset Instructions
                </button>
            </form>

            <div class="back-link">
                <a href="index.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <script src="js/jquery.js"></script>
    <script src="js/sweetalert.js"></script>
    <script>
    $(document).ready(function() {
        $("#forgotPasswordForm").on('submit', function(e) {
            e.preventDefault();
            
            let formData = $(this).serialize();
            
            $.post("query/forgotPasswordExe.php", formData, function(res) {
                if (res.res == "success") {
                    Swal.fire({
                        title: "Instructions Sent!",
                        text: res.msg,
                        icon: "success",
                        confirmButtonText: "OK"
                    }).then(() => {
                        window.location.href = "index.php";
                    });
                } else {
                    Swal.fire("Error", res.msg, "error");
                }
            }, 'json').fail(function() {
                Swal.fire("Error", "Something went wrong. Please try again.", "error");
            });
        });
    });
    </script>
</body>
</html>