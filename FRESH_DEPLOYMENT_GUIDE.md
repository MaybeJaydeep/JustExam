# 🚀 JustExam Fresh Deployment Guide

## Quick Setup for New Database (No Migration)

### 📋 Pre-Requirements
- PHP 8.0+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.4+
- Web server (Apache/Nginx)
- SSL certificate (recommended)

---

## 🎯 **Best Free Hosting Options**

### **1. InfinityFree (Recommended)**
- **PHP**: 8.1+ ✅
- **MySQL**: Unlimited databases
- **Storage**: 5GB
- **SSL**: Free Let's Encrypt
- **Perfect for**: Your exam system

### **2. 000WebHost**
- **PHP**: 8.0+
- **MySQL**: 2 databases
- **Storage**: 1GB
- **SSL**: Free

---

## 🚀 **Step-by-Step Deployment**

### **Step 1: Upload Files**
1. Zip your project files (exclude `.git`, `logs/*`)
2. Upload to your hosting provider
3. Extract in public_html or web root

### **Step 2: Create Fresh Database**
1. **Create MySQL database** named `just_exam`
2. **Import fresh schema**:
   ```sql
   -- In phpMyAdmin or MySQL command line
   SOURCE database/fresh_database.sql;
   ```
   
   **OR upload via phpMyAdmin:**
   - Go to phpMyAdmin
   - Select your database
   - Click "Import"
   - Choose `database/fresh_database.sql`
   - Click "Go"

### **Step 3: Update Configuration**
Edit `config.php` with your hosting details:

```php
<?php
// Database Configuration - UPDATE THESE
define('DB_HOST', 'sql123.infinityfree.net');     // Your DB host
define('DB_USER', 'if0_12345678');                // Your DB username  
define('DB_PASS', 'your_database_password');      // Your DB password
define('DB_NAME', 'if0_12345678_just_exam');      // Your DB name

// CRITICAL: Change this to a random 32+ character string
define('JWT_SECRET', 'your_random_secret_key_here_32_chars_min');

// Security Configuration (keep these)
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Enable HTTPS cookies if using SSL (recommended)
ini_set('session.cookie_secure', 1); // Set to 1 for HTTPS
?>
```

### **Step 4: Generate JWT Secret**
Create a random 32+ character string for JWT_SECRET:
- Use online generator: https://www.random.org/strings/
- Or use: `openssl rand -base64 32` (Linux/Mac)
- Example: `a8f5f167f44f4964e6c998dee827110c`

### **Step 5: Test Your Installation**

1. **Visit your site**: `https://yourdomain.com`
2. **Default Admin Login**:
   - Email: `admin@justexam.com`
   - Password: `password123`
3. **IMMEDIATELY change the admin password**

### **Step 6: Create Your Content**

1. **Add Courses**:
   - Go to Admin → Manage Course
   - Add your subjects (e.g., "Computer Science", "Mathematics")

2. **Add Students**:
   - Go to Admin → Manage Examinee
   - Add student accounts

3. **Create Exams**:
   - Go to Admin → Manage Exam
   - Create exams with questions

---

## 🔒 **Security Checklist**

✅ **Immediate Actions:**
- [ ] Change default admin password
- [ ] Update JWT_SECRET in config.php
- [ ] Enable HTTPS (SSL certificate)
- [ ] Test login functionality
- [ ] Verify database connection

✅ **Optional Enhancements:**
- [ ] Set up automated backups
- [ ] Configure error logging
- [ ] Add .htaccess security headers
- [ ] Monitor security logs

---

## 📁 **What's Included in Fresh Database**

### **Tables Created:**
- `admin_acc` - Admin accounts (1 default admin)
- `course_tbl` - Courses (empty, ready for your content)
- `examinee_tbl` - Students (empty)
- `exam_tbl` - Exams (empty)
- `exam_question_tbl` - Questions (empty)
- `exam_answers` - Student answers (empty)
- `exam_attempt` - Exam attempts (empty)
- `feedbacks_tbl` - Student feedback (empty)
- `login_attempts` - Brute force protection (empty)
- `security_logs` - Security audit trail (empty)
- `user_sessions` - Session management (empty)

### **Security Features:**
✅ Bcrypt password hashing (cost factor 12)
✅ SQL injection protection (PDO prepared statements)
✅ CSRF token protection
✅ XSS prevention (output escaping)
✅ Session security (timeout, regeneration)
✅ Brute force protection (5 attempts, 15min lockout)
✅ Input validation throughout
✅ Security event logging
✅ Foreign key constraints for data integrity

---

## 🎓 **Default Credentials**

**Admin Panel**: `/adminpanel/admin/`
- Email: `admin@justexam.com`
- Password: `password123`

**Student Portal**: `/` (root)
- No default students (create via admin panel)

---

## 🆘 **Troubleshooting**

### **Database Connection Error:**
- Check DB credentials in `config.php`
- Verify database exists and is accessible
- Check if PDO MySQL extension is enabled

### **Login Issues:**
- Clear browser cache/cookies
- Check if sessions are working: `<?php session_start(); echo session_id(); ?>`
- Verify database tables were created properly

### **Permission Errors:**
- Ensure web server can write to `logs/` directory
- Check file permissions (644 for files, 755 for directories)

### **HTTPS Issues:**
- If no SSL, set `ini_set('session.cookie_secure', 0);` in config.php
- Get free SSL from Let's Encrypt or your hosting provider

---

## 📞 **Support**

If you encounter issues:
1. Check browser console for JavaScript errors
2. Check server error logs
3. Verify all files uploaded correctly
4. Test database connection separately

---

**🎉 Your JustExam system is now ready for production use!**

The fresh database includes all security enhancements and is ready for your content. No migration needed - just start adding courses, students, and exams through the admin panel.