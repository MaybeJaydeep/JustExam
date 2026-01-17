-- Security Updates for JustExam Database
-- Run this script to add security improvements

-- 1. Update password field length to accommodate bcrypt hashes
ALTER TABLE `admin_acc` 
MODIFY `admin_pass` VARCHAR(255) NOT NULL;

ALTER TABLE `examinee_tbl` 
MODIFY `exmne_password` VARCHAR(255) NOT NULL;

-- 2. Add unique constraint on email
ALTER TABLE `examinee_tbl` 
ADD UNIQUE KEY `unique_email` (`exmne_email`);

-- 3. Add indexes for better performance
ALTER TABLE `exam_answers` 
ADD INDEX `idx_exmne_exam` (`axmne_id`, `exam_id`);

ALTER TABLE `exam_attempt` 
ADD INDEX `idx_exmne_exam` (`exmne_id`, `exam_id`);

ALTER TABLE `exam_question_tbl` 
ADD INDEX `idx_exam_id` (`exam_id`);

ALTER TABLE `examinee_tbl` 
ADD INDEX `idx_email` (`exmne_email`);

-- 4. Add foreign key constraints (optional but recommended)
-- Note: This requires existing data to be valid

-- ALTER TABLE `exam_tbl` 
-- ADD CONSTRAINT `fk_exam_course` 
-- FOREIGN KEY (`cou_id`) REFERENCES `course_tbl`(`cou_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE `exam_question_tbl` 
-- ADD CONSTRAINT `fk_question_exam` 
-- FOREIGN KEY (`exam_id`) REFERENCES `exam_tbl`(`ex_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE `exam_answers` 
-- ADD CONSTRAINT `fk_answer_examinee` 
-- FOREIGN KEY (`axmne_id`) REFERENCES `examinee_tbl`(`exmne_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE,
-- ADD CONSTRAINT `fk_answer_exam` 
-- FOREIGN KEY (`exam_id`) REFERENCES `exam_tbl`(`ex_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE `exam_attempt` 
-- ADD CONSTRAINT `fk_attempt_examinee` 
-- FOREIGN KEY (`exmne_id`) REFERENCES `examinee_tbl`(`exmne_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE,
-- ADD CONSTRAINT `fk_attempt_exam` 
-- FOREIGN KEY (`exam_id`) REFERENCES `exam_tbl`(`ex_id`) 
-- ON DELETE CASCADE ON UPDATE CASCADE;

-- 5. Add audit fields (created_at, updated_at)
ALTER TABLE `examinee_tbl` 
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `exam_tbl` 
ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 6. Add NOT NULL constraints where appropriate
ALTER TABLE `examinee_tbl` 
MODIFY `exmne_fullname` VARCHAR(1000) NOT NULL,
MODIFY `exmne_email` VARCHAR(1000) NOT NULL,
MODIFY `exmne_password` VARCHAR(255) NOT NULL;

ALTER TABLE `admin_acc` 
MODIFY `admin_user` VARCHAR(1000) NOT NULL,
MODIFY `admin_pass` VARCHAR(255) NOT NULL;

-- 7. Create login attempts tracking table (for brute force protection)
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `identifier` VARCHAR(255) NOT NULL,
  `attempt_count` INT(11) DEFAULT 0,
  `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `locked_until` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_identifier` (`identifier`),
  INDEX `idx_identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Create security log table
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(100) NOT NULL,
  `user_id` INT(11) NULL,
  `user_type` ENUM('admin', 'student') NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT,
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_event_type` (`event_type`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Done! Database security updates applied.
