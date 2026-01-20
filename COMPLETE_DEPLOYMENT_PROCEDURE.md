# 🚀 Complete JustExam Deployment Procedure

## 📋 Table of Contents
1. [Choose Free Hosting](#1-choose-free-hosting)
2. [Prepare Your Files](#2-prepare-your-files)
3. [Sign Up & Setup Hosting](#3-sign-up--setup-hosting)
4. [Upload Files](#4-upload-files)
5. [Create Database](#5-create-database)
6. [Configure Application](#6-configure-application)
7. [Test & Go Live](#7-test--go-live)
8. [Post-Deployment Security](#8-post-deployment-security)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Choose Free Hosting

### **🥇 Recommended: InfinityFree**
- **Why**: PHP 8.1+, unlimited MySQL, 5GB storage, free SSL
- **Website**: https://infinityfree.net
- **Best for**: Your JustExam system

### **🥈 Alternative: 000WebHost**
- **Why**: Reliable, 1GB storage, free SSL
- **Website**: https://www.000webhost.com

### **🥉 Alternative: AwardSpace**
- **Why**: Good support, 1GB storage
- **Website**: https://www.awardspace.com

---

## 2. Prepare Your Files

### **Step 2.1: Clean Your Project**
```bash
# Remove unnecessary files
rm -rf .git/
rm -rf logs/*
rm migrate_passwords.php  # Not needed for fresh install
```

### **Step 2.2: Create Deployment Package**
```bash
# Create a zip file with your project
zip -r justexam_deploy.zip . -x "*.git*" "logs/*" "*.md"
```

**Files to include:**
- ✅ All PHP files (index.php, home.php, config.php, etc.)
- ✅ CSS, JS, and asset folders
- ✅ Database folder with fresh_database.sql
- ✅ Admin panel folder
- ✅ Query and pages folders
- ❌ .git folder
- ❌ README files (optional)
- ❌ Old migration scripts

---

## 3. Sign Up & Setup Hosting

### **For InfinityFree (Recommended):**

#### **Step 3.1: Create Account**
1. Go to https://infinityfree.net
2. Click "Sign Up"
3. Fill in your details
4. Verify your email

#### **Step 3.2: Create Hosting Account**
1. Login to your dashboard
2. Click "Create Account"
3. Choose subdomain: `yourname.infinityfreeapp.com`
4. Wait for account activation (5-10 minutes)

#### **Step 3.3: Access Control Panel**
1. Click "Control Panel" next to your account
2. You'll see cPanel interface
3. Note your account details

---

## 4. Upload Files

### **Method 1: File Manager (Recommended)**

#### **Step 4.1: Access File Manager**
1. In cPanel, click "File Manager"
2. Navigate to `htdocs` folder (this is your web root)
3. Delete default files (index.html, etc.)

#### **Step 4.2: Upload Your Files**
1. Click "Upload" button
2. Select your `justexam_deploy.zip`
3. Wait for upload to complete
4. Right-click the zip file → "Extract"
5. Move all files from extracted folder to `htdocs` root
6. Delete the zip file and empty folder

### **Method 2: FTP (Alternative)**
```bash
# Use FTP client like FileZilla
Host: files.infinityfree.net
Username: your_ftp_username
Password: your_ftp_password
Port: 21
```

---

## 5. Create Database

### **Step 5.1: Create MySQL Database**
1. In cPanel, click "MySQL Databases"
2. Create new database:
   - Database Name: `just_exam`
   - Click "Create Database"
3. Create database user:
   - Username: `examuser`
   - Password: Generate strong password
   - Click "Create User"
4. Add user to database:
   - Select user and database
   - Grant "All Privileges"
   - Click "Add"

### **Step 5.2: Note Database Details**
Write down these details (you'll need them):
```
DB Host: sql123.infinityfree.net (check your cPanel)
DB Name: if0_12345678_just_exam
DB User: if0_12345678_examuser
DB Pass: your_generated_password
```

### **Step 5.3: Import Database Schema**
1. In cPanel, click "phpMyAdmin"
2. Select your database from left sidebar
3. Click "Import" tab
4. Choose file: `database/fresh_database.sql`
5. Click "Go"
6. Wait for "Import has been successfully finished"

---

## 6. Configure Application

### **Step 6.1: Update config.php**
1. In File Manager, open `config.php`
2. Update database settings:

```php
<?php
// Database Configuration - UPDATE WITH YOUR DETAILS
define('DB_HOST', 'sql123.infinityfree.net');        // From cPanel
define('DB_USER', 'if0_12345678_examuser');          // Your DB user
define('DB_PASS', 'your_strong_password');           // Your DB password
define('DB_NAME', 'if0_12345678_just_exam');         // Your DB name

// CRITICAL: Generate random JWT secret
define('JWT_SECRET', 'a8f5f167f44f4964e6c998dee827110c'); // Change this!

// Security Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Production settings
if ($_SERVER['SERVER_NAME'] !== 'localhost') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-errors.log');
}

// Secure Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);     // HTTPS required
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Database Connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact administrator.");
}
?>
```

### **Step 6.2: Generate JWT Secret**
Use one of these methods:
- **Online**: https://www.random.org/strings/ (32 characters)
- **Command**: `openssl rand -hex 32`
- **Example**: `a8f5f167f44f4964e6c998dee827110c`

### **Step 6.3: Create Logs Directory**
1. In File Manager, create folder: `logs`
2. Set permissions to 755 (usually automatic)

### **Step 6.4: Setup SSL (Free)**
1. In cPanel, find "SSL/TLS"
2. Click "Let's Encrypt SSL"
3. Select your domain
4. Click "Issue"
5. Wait for SSL activation

---

## 7. Test & Go Live

### **Step 7.1: Test Database Connection**
Create a test file `test_db.php`:
```php
<?php
require_once 'config.php';
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM admin_acc");
    echo "✅ Database connection successful!<br>";
    echo "Admin accounts found: " . $stmt->fetchColumn();
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>
```

### **Step 7.2: Test Your Application**
1. **Visit your site**: `https://yourname.infinityfreeapp.com`
2. **Should see**: Student login page
3. **Test admin access**: `https://yourname.infinityfreeapp.com/adminpanel/admin/`

### **Step 7.3: Default Login Credentials**
**Admin Login:**
- Email: `admin@justexam.com`
- Password: `password123`

**Student Login:**
- No default students (create via admin panel)

### **Step 7.4: First Login Test**
1. Go to admin panel
2. Login with default credentials
3. ✅ **SUCCESS**: You see admin dashboard
4. ❌ **FAILURE**: Check troubleshooting section

---

## 8. Post-Deployment Security

### **Step 8.1: Change Admin Password**
1. Login to admin panel
2. Go to admin profile/settings
3. Change password immediately
4. Use strong password (12+ characters)

### **Step 8.2: Create Your Content**
1. **Add Courses**:
   - Admin → Manage Course
   - Add subjects like "Computer Science", "Mathematics"

2. **Add Students**:
   - Admin → Manage Examinee
   - Create student accounts

3. **Create Exams**:
   - Admin → Manage Exam
   - Add exams with questions

### **Step 8.3: Security Hardening**
1. **Delete test file**: Remove `test_db.php`
2. **Check file permissions**: Ensure config.php is not publicly readable
3. **Monitor logs**: Check `logs/security.log` regularly
4. **Backup setup**: Export database weekly

### **Step 8.4: Create .htaccess (Optional)**
Create `.htaccess` in root directory:
```apache
# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options SAMEORIGIN
Header always set X-XSS-Protection "1; mode=block"

# Hide sensitive files
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "security.php">
    Order allow,deny
    Deny from all
</Files>

# Disable directory browsing
Options -Indexes

# Force HTTPS (if SSL is enabled)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 9. Troubleshooting

### **Database Connection Issues**
```
Error: "Database connection failed"
```
**Solutions:**
1. Check DB credentials in `config.php`
2. Verify database exists in cPanel
3. Ensure user has proper privileges
4. Check if PDO MySQL is enabled

### **Login Issues**
```
Error: "Invalid credentials" or blank page
```
**Solutions:**
1. Clear browser cache/cookies
2. Check if database was imported correctly:
   ```sql
   SELECT * FROM admin_acc;
   ```
3. Verify sessions are working
4. Check error logs

### **SSL/HTTPS Issues**
```
Error: "Not secure" or mixed content warnings
```
**Solutions:**
1. Wait for SSL activation (up to 24 hours)
2. Force HTTPS in .htaccess
3. Update any hardcoded HTTP links
4. If no SSL, set `session.cookie_secure = 0` in config.php

### **File Upload Issues**
```
Error: Files not uploading or extracting
```
**Solutions:**
1. Check file size limits
2. Try uploading smaller chunks
3. Use FTP instead of web upload
4. Check disk space quota

### **Permission Errors**
```
Error: "Permission denied" or "Cannot write to logs"
```
**Solutions:**
1. Set logs directory to 755 permissions
2. Check if web server can write files
3. Contact hosting support if needed

### **Performance Issues**
```
Error: Slow loading or timeouts
```
**Solutions:**
1. Optimize database queries
2. Enable caching if available
3. Compress images and assets
4. Consider upgrading to paid hosting

---

## 🎉 Deployment Complete!

### **Your JustExam System is Now Live:**
- ✅ **Student Portal**: `https://yourname.infinityfreeapp.com`
- ✅ **Admin Panel**: `https://yourname.infinityfreeapp.com/adminpanel/admin/`
- ✅ **Database**: Fresh and secure
- ✅ **SSL**: Encrypted connections
- ✅ **Security**: All protections active

### **Next Steps:**
1. Change admin password
2. Add your courses and students
3. Create your first exam
4. Test with a student account
5. Set up regular backups

### **Support:**
- Check logs in `logs/` directory
- Monitor security events
- Keep database backed up
- Update content regularly

**🚀 Your online examination system is ready for production use!**