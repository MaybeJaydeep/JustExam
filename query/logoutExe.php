<?php 
session_start();
require_once("../config.php");
require_once("../security.php");

// Log the logout event before destroying session
if (isset($_SESSION['student']['user_id'])) {
    logSecurityEvent('LOGOUT', [
        'user_id' => $_SESSION['student']['user_id'],
        'type' => 'student'
    ]);
}

// Clear session data
session_unset();
session_destroy();

// Redirect to login page
header("location: ../");
exit;
?>