-- ====================================================================
-- INNOW Digital Attendance Tracking System - MySQL Database Dump
-- Compatible with MySQL Workbench 8.x, phpMyAdmin, and MySQL Server 5.7+ / 8.0+
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `innow_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `innow_db`;

-- --------------------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `attendance_records`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `pin` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `department` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `emergency_contact` VARCHAR(100) DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'OFFSITE',
  `avatar_url` TEXT DEFAULT NULL,
  `qr_code` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Table structure for `attendance_records`
-- --------------------------------------------------------------------
CREATE TABLE `attendance_records` (
  `id` VARCHAR(50) NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `method` VARCHAR(20) NOT NULL,
  `synced_to_db` TINYINT(1) NOT NULL DEFAULT 1,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_attendance_user` (`user_id`),
  CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Table structure for `sessions`
-- --------------------------------------------------------------------
CREATE TABLE `sessions` (
  `id` VARCHAR(100) NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  `token` VARCHAR(100) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sessions_user` (`user_id`),
  CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Seed Data for `users`
-- --------------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `pin`, `role`, `department`, `phone`, `emergency_contact`, `status`, `avatar_url`, `qr_code`) VALUES
('STF-1001', 'Admin Supervisor', 'admin@innow.com', '1001', 'System Administrator', 'Operations & IT', '+27 21 696 4157', 'INNOW Operations Hotline: +27 82 000 0000', 'ONSITE', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150', 'INNOW-QR-1001-ADMIN'),
('STF-1002', 'Lindiwe Dlamini', 'lindiwe.d@innow.com', '1002', 'Lead Software Engineer', 'Software Engineering', '+27 82 345 6789', 'Sipho Dlamini (Spouse): +27 82 111 2222', 'ONSITE', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150', 'INNOW-QR-1002-LINDIWE'),
('STF-1003', 'Kagiso Mokoena', 'kagiso.m@innow.com', '1003', 'UX Designer & Researcher', 'Design & UX', '+27 83 456 7890', 'Nomsa Mokoena (Mother): +27 83 222 3333', 'ONSITE', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150', 'INNOW-QR-1003-KAGISO'),
('STF-1004', 'Tariq Al-Mansoor', 'tariq.a@innow.com', '1004', 'DevOps & Cloud Specialist', 'Infrastructure', '+27 84 567 8901', 'Fatima Al-Mansoor (Sister): +27 84 333 4444', 'BREAK', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150', 'INNOW-QR-1004-TARIQ'),
('STF-1005', 'Chiamaka Nwosu', 'chiamaka.n@innow.com', '1005', 'Data Scientist', 'Analytics & AI', '+27 82 678 9012', 'Obinna Nwosu (Brother): +27 82 444 5555', 'OFFSITE', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=150', 'INNOW-QR-1005-CHIAMAKA'),
('STF-1006', 'Devon Van Der Merwe', 'devon.vdm@innow.com', '1006', 'QA Automation Lead', 'Quality Assurance', '+27 83 789 0123', 'Annelize Van Der Merwe: +27 83 555 6666', 'OFFSITE', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150', 'INNOW-QR-1006-DEVON');

-- --------------------------------------------------------------------
-- Seed Data for `attendance_records`
-- --------------------------------------------------------------------
INSERT INTO `attendance_records` (`id`, `user_id`, `action`, `timestamp`, `method`, `synced_to_db`, `notes`) VALUES
('LOG-9001', 'STF-1001', 'CLOCK_IN', NOW() - INTERVAL 4 HOUR, 'PIN', 1, 'Morning Shift Check-in'),
('LOG-9002', 'STF-1002', 'CLOCK_IN', NOW() - INTERVAL 3 HOUR, 'QR', 1, 'Main Front Gate Camera Scanner'),
('LOG-9003', 'STF-1003', 'CLOCK_IN', NOW() - INTERVAL 2 HOUR, 'BUTTON', 1, 'Web One-Click Check-in'),
('LOG-9004', 'STF-1004', 'CLOCK_IN', NOW() - INTERVAL 1 HOUR, 'QR', 1, 'Front Gate Camera Scanner'),
('LOG-9005', 'STF-1004', 'BREAK_START', NOW() - INTERVAL 30 MINUTE, 'BUTTON', 1, 'Tea Break');

-- End of INNOW MySQL Dump
