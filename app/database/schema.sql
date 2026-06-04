CREATE DATABASE IF NOT EXISTS `rebirth` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rebirth`;

DROP TABLE IF EXISTS `message_likes`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `group_members`;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS `user_milestones`;
DROP TABLE IF EXISTS `milestones`;
DROP TABLE IF EXISTS `therapist_patients`;
DROP TABLE IF EXISTS `check_ins`;
DROP TABLE IF EXISTS `journal_entries`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `resources`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('member','therapist','admin') NOT NULL DEFAULT 'member',
  `stage` ENUM('Onboarding','Active','Maintenance','Alumni') NOT NULL DEFAULT 'Onboarding',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `initials` VARCHAR(4) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `check_ins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `mood` ENUM('great','good','okay','tough','struggling') NOT NULL,
  `craving_level` TINYINT UNSIGNED DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `check_date` DATE NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_checkin` (`user_id`, `check_date`)
) ENGINE=InnoDB;

CREATE TABLE `journal_entries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `content` TEXT NOT NULL,
  `mood` ENUM('great','good','okay','tough','struggling') NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `therapist_id` INT DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `date_time` DATETIME NOT NULL,
  `status` ENUM('confirmed','pending','cancelled','completed') NOT NULL DEFAULT 'pending',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `milestones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `target_days` INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `user_milestones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `milestone_id` INT NOT NULL,
  `progress` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `achieved` BOOLEAN NOT NULL DEFAULT FALSE,
  `achieved_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`milestone_id`) REFERENCES `milestones`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_milestone` (`user_id`, `milestone_id`)
) ENGINE=InnoDB;

CREATE TABLE `groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `tag` VARCHAR(50) NOT NULL,
  `member_count` INT NOT NULL DEFAULT 0,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `group_members` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_membership` (`group_id`, `user_id`)
) ENGINE=InnoDB;

CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `group_id` INT DEFAULT 0,
  `text` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `message_likes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `message_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_like` (`message_id`, `user_id`)
) ENGINE=InnoDB;

CREATE TABLE `resources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(50) NOT NULL,
  `icon` VARCHAR(100) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `tag` VARCHAR(50) NOT NULL,
  `url` VARCHAR(500) DEFAULT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `therapist_patients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `therapist_id` INT NOT NULL,
  `patient_id` INT NOT NULL,
  `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_assignment` (`therapist_id`, `patient_id`)
) ENGINE=InnoDB;

USE `rebirth`;

CREATE TABLE IF NOT EXISTS `therapist_availability` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `therapist_id` INT NOT NULL,
  `day_of_week` TINYINT NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `therapist_resources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `therapist_id` INT NOT NULL,
  `patient_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `type` ENUM('video','pdf','article','image') NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `conversations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_pair` (`patient_id`, `therapist_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `conversation_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `sos_alerts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `status` ENUM('active','acknowledged','resolved') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `recovery_progress` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `stage_name` VARCHAR(100) NOT NULL,
  `stage_order` TINYINT NOT NULL DEFAULT 0,
  `status` ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  `therapist_notes` TEXT DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
