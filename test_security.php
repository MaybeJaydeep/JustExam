<?php
/**
 * Security Test Script
 * Run this to verify security features are working
 * 
 * Usage: php test_security.php
 * Or visit: http://localhost/JustExam/test_security.php
 */

require_once("config.php");
require_once("security.php");

// Start output
echo "<h1>JustExam Security Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .pass { color: green; font-weight: bold; }
    .fail { color: red; font-weight: bold; }
    .test { margin: 10px 0; padding: 10px; border: 1px solid #ddd; }
    h2 { color: #333; border-bottom: 2px solid #333; padding-bottom: 5px; }
</style>";

$passCount = 0;
$failCount = 0;

function testResult($name, $passed, $message = '') {
    global $passCount, $failCount;
    if ($passed) {
        $passCount++;
        echo "<div class='test'><span class='pass'>✓ PASS:</span> $name";
    } else {
        $failCount++;
        echo "<div class='test'><span class='fail'>✗ FAIL:</span> $name";
    }
    if ($message) {
        echo "<br><small>$message</small>";
    }
    echo "</div>";
}

// Test 1: Database Connection
echo "<h2>1. Database Connection</h2>";
try {
    $stmt = $conn->query("SELECT 1");
    testResult("Database connection", true, "Connected to database successfully");
} catch (Exception $e) {
    testResult("Database connection", false, "Error: " . $e->getMessage());
}

// Test 2: Security Functions
echo "<h2>2. Security Functions</h2>";

// Test CSRF Token Generation
session_start();
$token = generateCSRFToken();
testResult("CSRF token generation", !empty($token) && strlen($token) == 64, "Token: " . substr($token, 0, 20) . "...");

// Test CSRF Token Verification
$isValid = verifyCSRFToken($token);
testResult("CSRF token verification", $isValid === true, "Token verified successfully");

// Test Password Hashing
$password = "test123";
$hash = hashPassword($password);
testResult("Password hashing", substr($hash, 0, 4) === '$2y$', "Hash: " . substr($hash, 0, 30) . "...");

// Test Password Verification
$verified = verifyPassword($password, $hash);
testResult("Password verification", $verified === true, "Password verified successfully");

// Test XSS Escape
$dangerous = "<script>alert('XSS')</script>";
$safe = escape($dangerous);
testResult("XSS escape function", $safe === htmlspecialchars($dangerous, ENT_QUOTES, 'UTF-8'), "Escaped: $safe");

// Test Email Validation
$validEmail = validateEmail("test@example.com");
$invalidEmail = validateEmail("invalid-email");
testResult("Email validation", $validEmail === true && $invalidEmail === false, "Valid email accepted, invalid rejected");

// Test ID Validation
$validId = validateId("123");
$invalidId = validateId("-1");
testResult("ID validation", $validId === 123 && $invalidId === false, "Valid ID accepted, invalid rejected");

// Test 3: Database Structure
echo "<h2>3. Database Structure</h2>";

// Check password field length
try {
    $stmt = $conn->query("DESCRIBE admin_acc admin_pass");
    $field = $stmt->fetch();
    $isCorrect = strpos($field['Type'], 'varchar(255)') !== false;
    testResult("Admin password field length", $isCorrect, "Type: " . $field['Type']);
} catch (Exception $e) {
    testResult("Admin password field length", false, "Error: " . $e->getMessage());
}

try {
    $stmt = $conn->query("DESCRIBE examinee_tbl exmne_password");
    $field = $stmt->fetch();
    $isCorrect = strpos($field['Type'], 'varchar(255)') !== false;
    testResult("Student password field length", $isCorrect, "Type: " . $field['Type']);
} catch (Exception $e) {
    testResult("Student password field length", false, "Error: " . $e->getMessage());
}

// Check for indexes
try {
    $stmt = $conn->query("SHOW INDEX FROM examinee_tbl WHERE Key_name = 'idx_email'");
    $hasIndex = $stmt->rowCount() > 0;
    testResult("Email index exists", $hasIndex, "Performance optimization in place");
} catch (Exception $e) {
    testResult("Email index exists", false, "Index not found - run security_updates.sql");
}

// Test 4: Password Migration Status
echo "<h2>4. Password Migration Status</h2>";

try {
    // Check admin passwords
    $stmt = $conn->query("SELECT admin_id, admin_pass FROM admin_acc LIMIT 1");
    $admin = $stmt->fetch();
    $isHashed = substr($admin['admin_pass'], 0, 4) === '$2y$';
    testResult("Admin passwords hashed", $isHashed, $isHashed ? "Passwords are secure" : "WARNING: Run migrate_passwords.php!");
} catch (Exception $e) {
    testResult("Admin passwords hashed", false, "Error: " . $e->getMessage());
}

try {
    // Check student passwords
    $stmt = $conn->query("SELECT exmne_id, exmne_password FROM examinee_tbl LIMIT 1");
    if ($stmt->rowCount() > 0) {
        $student = $stmt->fetch();
        $isHashed = substr($student['exmne_password'], 0, 4) === '$2y$';
        testResult("Student passwords hashed", $isHashed, $isHashed ? "Passwords are secure" : "WARNING: Run migrate_passwords.php!");
    } else {
        testResult("Student passwords hashed", true, "No students in database yet");
    }
} catch (Exception $e) {
    testResult("Student passwords hashed", false, "Error: " . $e->getMessage());
}

// Test 5: File Permissions
echo "<h2>5. File Permissions</h2>";

$configReadable = is_readable("config.php");
testResult("config.php readable", $configReadable, "Configuration file accessible");

$securityReadable = is_readable("security.php");
testResult("security.php readable", $securityReadable, "Security functions accessible");

$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}
$logsWritable = is_writable($logsDir);
testResult("logs directory writable", $logsWritable, "Security logging enabled");

// Test 6: Session Configuration
echo "<h2>6. Session Configuration</h2>";

$httpOnly = ini_get('session.cookie_httponly');
testResult("HttpOnly cookies", $httpOnly == 1, "Prevents JavaScript access to cookies");

$sameSite = ini_get('session.cookie_samesite');
testResult("SameSite cookies", $sameSite === 'Strict', "Prevents CSRF attacks");

$sessionLifetime = SESSION_LIFETIME;
testResult("Session timeout configured", $sessionLifetime > 0, "Timeout: " . ($sessionLifetime / 60) . " minutes");

// Test 7: Security Files Exist
echo "<h2>7. Security Files</h2>";

$files = [
    'config.php' => 'Database configuration',
    'security.php' => 'Security helper functions',
    'database/security_updates.sql' => 'Database security updates',
    'SECURITY_UPGRADE_GUIDE.md' => 'Installation guide',
    'DEPLOYMENT_GUIDE.md' => 'Deployment instructions'
];

foreach ($files as $file => $description) {
    $exists = file_exists($file);
    testResult("$file exists", $exists, $description);
}

// Test 8: Query Files Security
echo "<h2>8. Query Files Security Check</h2>";

$queryFiles = [
    'query/loginExe.php',
    'query/submitAnswerExe.php',
    'adminpanel/admin/query/loginExe.php',
    'adminpanel/admin/query/addExamineeExe.php'
];

foreach ($queryFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $hasCSRF = strpos($content, 'verifyCSRFToken') !== false;
        $hasPrepared = strpos($content, 'prepare(') !== false;
        $noExtract = strpos($content, 'extract($_POST)') === false;
        
        $secure = $hasCSRF && $hasPrepared && $noExtract;
        testResult(basename($file) . " secured", $secure, 
            "CSRF: " . ($hasCSRF ? "✓" : "✗") . 
            " | Prepared: " . ($hasPrepared ? "✓" : "✗") . 
            " | No extract: " . ($noExtract ? "✓" : "✗")
        );
    }
}

// Summary
echo "<h2>Test Summary</h2>";
echo "<div class='test'>";
echo "<strong>Total Tests:</strong> " . ($passCount + $failCount) . "<br>";
echo "<span class='pass'>Passed: $passCount</span><br>";
echo "<span class='fail'>Failed: $failCount</span><br>";

$percentage = ($passCount / ($passCount + $failCount)) * 100;
echo "<br><strong>Success Rate: " . number_format($percentage, 1) . "%</strong><br>";

if ($failCount == 0) {
    echo "<br><span class='pass'>🎉 All tests passed! System is secure and ready.</span>";
} else if ($percentage >= 80) {
    echo "<br><span style='color: orange;'>⚠️ Most tests passed. Review failed tests above.</span>";
} else {
    echo "<br><span class='fail'>❌ Multiple tests failed. Review security setup.</span>";
}
echo "</div>";

// Recommendations
if ($failCount > 0) {
    echo "<h2>Recommendations</h2>";
    echo "<div class='test'>";
    echo "<ol>";
    echo "<li>Run database updates: <code>mysql -u root -p just_exam < database/security_updates.sql</code></li>";
    echo "<li>Run password migration: <code>php migrate_passwords.php</code></li>";
    echo "<li>Review failed tests above and fix issues</li>";
    echo "<li>Check SECURITY_UPGRADE_GUIDE.md for detailed instructions</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<br><p><strong>Note:</strong> Delete this file (test_security.php) after testing!</p>";
?>
