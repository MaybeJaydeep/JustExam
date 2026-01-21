-- JustExam Fresh Database Setup
-- Clean installation with security enhancements
-- No sample data included

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS `just_exam` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `just_exam`;

-- --------------------------------------------------------
-- Table structure for table `admin_acc`
-- --------------------------------------------------------

CREATE TABLE `admin_acc` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user` varchar(255) NOT NULL,
  `admin_pass` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `unique_admin_user` (`admin_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin account (CHANGE PASSWORD IMMEDIATELY)
INSERT INTO `admin_acc` (`admin_user`, `admin_pass`) VALUES 
('admin@justexam.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Default password: 'password123' - CHANGE THIS!

-- --------------------------------------------------------
-- Table structure for table `course_tbl`
-- --------------------------------------------------------

CREATE TABLE `course_tbl` (
  `cou_id` int(11) NOT NULL AUTO_INCREMENT,
  `cou_name` varchar(255) NOT NULL,
  `cou_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cou_id`),
  INDEX `idx_course_name` (`cou_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `examinee_tbl`
-- --------------------------------------------------------

CREATE TABLE `examinee_tbl` (
  `exmne_id` int(11) NOT NULL AUTO_INCREMENT,
  `exmne_fullname` varchar(255) NOT NULL,
  `exmne_course` varchar(255) NOT NULL,
  `exmne_gender` enum('male','female','other') NOT NULL,
  `exmne_birthdate` date NOT NULL,
  `exmne_year_level` varchar(50) NOT NULL,
  `exmne_email` varchar(255) NOT NULL,
  `exmne_password` varchar(255) NOT NULL,
  `exmne_status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`exmne_id`),
  UNIQUE KEY `unique_email` (`exmne_email`),
  INDEX `idx_email` (`exmne_email`),
  INDEX `idx_course` (`exmne_course`),
  INDEX `idx_status` (`exmne_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `exam_tbl`
-- --------------------------------------------------------

CREATE TABLE `exam_tbl` (
  `ex_id` int(11) NOT NULL AUTO_INCREMENT,
  `cou_id` int(11) NOT NULL,
  `ex_title` varchar(255) NOT NULL,
  `ex_time_limit` int(11) NOT NULL COMMENT 'Time limit in minutes',
  `ex_questlimit_display` int(11) NOT NULL DEFAULT 10,
  `ex_description` text,
  `ex_status` enum('active','inactive','draft') NOT NULL DEFAULT 'active',
  `ex_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ex_id`),
  INDEX `idx_course_id` (`cou_id`),
  INDEX `idx_status` (`ex_status`),
  FOREIGN KEY (`cou_id`) REFERENCES `course_tbl`(`cou_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `exam_question_tbl`
-- --------------------------------------------------------

CREATE TABLE `exam_question_tbl` (
  `eqt_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `exam_question` text NOT NULL,
  `exam_ch1` varchar(500) NOT NULL,
  `exam_ch2` varchar(500) NOT NULL,
  `exam_ch3` varchar(500) NOT NULL,
  `exam_ch4` varchar(500) NOT NULL,
  `exam_answer` varchar(500) NOT NULL,
  `exam_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`eqt_id`),
  INDEX `idx_exam_id` (`exam_id`),
  INDEX `idx_status` (`exam_status`),
  FOREIGN KEY (`exam_id`) REFERENCES `exam_tbl`(`ex_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `exam_answers`
-- --------------------------------------------------------

CREATE TABLE `exam_answers` (
  `exans_id` int(11) NOT NULL AUTO_INCREMENT,
  `axmne_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `quest_id` int(11) NOT NULL,
  `exans_answer` varchar(500) NOT NULL,
  `exans_status` enum('new','old') NOT NULL DEFAULT 'new',
  `exans_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`exans_id`),
  INDEX `idx_exmne_exam` (`axmne_id`, `exam_id`),
  INDEX `idx_quest_id` (`quest_id`),
  FOREIGN KEY (`axmne_id`) REFERENCES `examinee_tbl`(`exmne_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`exam_id`) REFERENCES `exam_tbl`(`ex_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`quest_id`) REFERENCES `exam_question_tbl`(`eqt_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `exam_attempt`
-- --------------------------------------------------------

CREATE TABLE `exam_attempt` (
  `examat_id` int(11) NOT NULL AUTO_INCREMENT,
  `exmne_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `examat_status` enum('used','unused') NOT NULL DEFAULT 'used',
  `attempt_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `score` decimal(5,2) DEFAULT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `correct_answers` int(11) DEFAULT NULL,
  PRIMARY KEY (`examat_id`),
  UNIQUE KEY `unique_attempt` (`exmne_id`, `exam_id`),
  INDEX `idx_exmne_exam` (`exmne_id`, `exam_id`),
  FOREIGN KEY (`exmne_id`) REFERENCES `examinee_tbl`(`exmne_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`exam_id`) REFERENCES `exam_tbl`(`ex_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `feedbacks_tbl`
-- --------------------------------------------------------

CREATE TABLE `feedbacks_tbl` (
  `fb_id` int(11) NOT NULL AUTO_INCREMENT,
  `exmne_id` int(11) NOT NULL,
  `fb_exmne_as` varchar(255) NOT NULL,
  `fb_feedbacks` text NOT NULL,
  `fb_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `fb_status` enum('new','read','archived') DEFAULT 'new',
  PRIMARY KEY (`fb_id`),
  INDEX `idx_exmne_id` (`exmne_id`),
  INDEX `idx_status` (`fb_status`),
  FOREIGN KEY (`exmne_id`) REFERENCES `examinee_tbl`(`exmne_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Security Enhancement Tables
-- --------------------------------------------------------

-- Login attempts tracking (brute force protection)
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `attempt_count` int(11) DEFAULT 0,
  `last_attempt` timestamp DEFAULT CURRENT_TIMESTAMP,
  `locked_until` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_identifier` (`identifier`),
  INDEX `idx_identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security event logging
CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `user_id` int(11) NULL,
  `user_type` enum('admin', 'student') NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `details` json,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_user_type` (`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Session management table (optional)
CREATE TABLE `user_sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('admin', 'student') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`session_id`),
  INDEX `idx_user_id` (`user_id`, `user_type`),
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Database setup complete!
-- 
-- IMPORTANT NEXT STEPS:
-- 1. Change the default admin password immediately
-- 2. Update config.php with your database credentials
-- 3. Test the application
-- 4. Create your first course and exam
--
-- Default Admin Login:
-- Email: admin@justexam.com
-- Password: password123
-- 
-- SECURITY NOTE: The password is already hashed with bcrypt.
-- You can change it through the admin panel or update the database directly.