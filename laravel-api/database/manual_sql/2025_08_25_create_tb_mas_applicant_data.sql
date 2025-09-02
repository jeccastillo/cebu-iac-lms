-- Manual DDL for tb_mas_applicant_data (run in your MySQL database if Laravel migrations cannot run)
-- Preferred with JSON column (MySQL 5.7+). If your server does not support JSON, replace `JSON` with `LONGTEXT`.

CREATE TABLE IF NOT EXISTS `tb_mas_applicant_data` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `data` JSON NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'new',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_applicant_user_id` (`user_id`),
  CONSTRAINT `fk_applicant_user_id` FOREIGN KEY (`user_id`)
    REFERENCES `tb_mas_users`(`intID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fallback if JSON is unavailable:
-- CREATE TABLE IF NOT EXISTS `tb_mas_applicant_data` (
--   `id` INT AUTO_INCREMENT PRIMARY KEY,
--   `user_id` INT NOT NULL,
--   `data` LONGTEXT NULL,
--   `status` VARCHAR(20) NOT NULL DEFAULT 'new',
--   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
--   `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--   INDEX `idx_applicant_user_id` (`user_id`),
--   CONSTRAINT `fk_applicant_user_id` FOREIGN KEY (`user_id`)
--     REFERENCES `tb_mas_users`(`intID`) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note:
-- If you see "errno: 150" issues when adding the FK, verify tb_mas_users table and intID column exist,
-- and that both use the same engine/charset and collation.
