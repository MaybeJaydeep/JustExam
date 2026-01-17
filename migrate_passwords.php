<?php
/**
 * Password Migration Script
 * Run this ONCE to hash all existing plaintext passwords
 * 
 * Usage: php migrate_passwords.php
 * Or visit: http://localhost/JustExam/migrate_passwords.php
 */

require_once("config.php");
require_once("security.php");

echo "<h2>Password Migration Script</h2>";
echo "<p>This will hash all plaintext passwords in the database.</p>";

try {
    $conn->beginTransaction();
    
    // Migrate Admin Passwords
    echo "<h3>Migrating Admin Passwords...</h3>";
    $stmt = $conn->query("SELECT admin_id, admin_pass FROM admin_acc");
    $admins = $stmt->fetchAll();
    
    $updateStmt = $conn->prepare("UPDATE admin_acc SET admin_pass = ? WHERE admin_id = ?");
    
    foreach ($admins as $admin) {
        // Check if already hashed (bcrypt hashes start with $2y$)
        if (substr($admin['admin_pass'], 0, 4) !== '$2y$') {
            $hashedPassword = hashPassword($admin['admin_pass']);
            $updateStmt->execute([$hashedPassword, $admin['admin_id']]);
            echo "✓ Migrated admin ID: {$admin['admin_id']}<br>";
        } else {
            echo "- Admin ID {$admin['admin_id']} already hashed<br>";
        }
    }
    
    // Migrate Student Passwords
    echo "<h3>Migrating Student Passwords...</h3>";
    $stmt = $conn->query("SELECT exmne_id, exmne_password FROM examinee_tbl");
    $students = $stmt->fetchAll();
    
    $updateStmt = $conn->prepare("UPDATE examinee_tbl SET exmne_password = ? WHERE exmne_id = ?");
    
    foreach ($students as $student) {
        // Check if already hashed
        if (substr($student['exmne_password'], 0, 4) !== '$2y$') {
            $hashedPassword = hashPassword($student['exmne_password']);
            $updateStmt->execute([$hashedPassword, $student['exmne_id']]);
            echo "✓ Migrated student ID: {$student['exmne_id']}<br>";
        } else {
            echo "- Student ID {$student['exmne_id']} already hashed<br>";
        }
    }
    
    $conn->commit();
    
    echo "<h3 style='color: green;'>✓ Migration Completed Successfully!</h3>";
    echo "<p><strong>IMPORTANT:</strong> Delete this file (migrate_passwords.php) after migration!</p>";
    echo "<p>New default credentials:</p>";
    echo "<ul>";
    echo "<li>Admin: admin@username / admin@password</li>";
    echo "<li>Students: Use their existing emails with their old passwords</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<h3 style='color: red;'>✗ Migration Failed!</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("Password Migration Error: " . $e->getMessage());
}
?>
