-- =============================================================
-- Rebirth - Addiction Recovery Platform
-- Full Database: Schema + Seed Data
-- Run: mysql -u root -p < database.sql
-- =============================================================

CREATE DATABASE IF NOT EXISTS `rebirth` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rebirth`;

-- ─── DROP ALL TABLES (FK-safe order) ───

DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `treatment_plans`;
DROP TABLE IF EXISTS `relapses`;
DROP TABLE IF EXISTS `clinical_notes`;
DROP TABLE IF EXISTS `message_likes`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `group_members`;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS `user_milestones`;
DROP TABLE IF EXISTS `milestones`;
DROP TABLE IF EXISTS `recovery_progress`;
DROP TABLE IF EXISTS `sos_alerts`;
DROP TABLE IF EXISTS `conversation_messages`;
DROP TABLE IF EXISTS `conversations`;
DROP TABLE IF EXISTS `therapist_resources`;
DROP TABLE IF EXISTS `therapist_availability`;
DROP TABLE IF EXISTS `therapist_patients`;
DROP TABLE IF EXISTS `check_ins`;
DROP TABLE IF EXISTS `journal_entries`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `resources`;
DROP TABLE IF EXISTS `users`;

-- ─── SCHEMA ───

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

CREATE TABLE `therapist_availability` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `therapist_id` INT NOT NULL,
  `day_of_week` TINYINT NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `therapist_resources` (
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

CREATE TABLE `conversations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_pair` (`patient_id`, `therapist_id`)
) ENGINE=InnoDB;

CREATE TABLE `conversation_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `sos_alerts` (
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

CREATE TABLE `recovery_progress` (
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

CREATE TABLE `clinical_notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `content` TEXT NOT NULL,
  `session_date` DATE DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `relapses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `relapse_date` DATETIME NOT NULL,
  `trigger` TEXT DEFAULT NULL,
  `severity` ENUM('mild','moderate','severe') NOT NULL DEFAULT 'moderate',
  `description` TEXT DEFAULT NULL,
  `action_taken` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `treatment_plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `goals` TEXT DEFAULT NULL,
  `objectives` TEXT DEFAULT NULL,
  `activities` TEXT DEFAULT NULL,
  `coping_strategies` TEXT DEFAULT NULL,
  `recommendations` TEXT DEFAULT NULL,
  `status` ENUM('active','completed','archived') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT DEFAULT NULL,
  `link` VARCHAR(500) DEFAULT NULL,
  `related_id` INT DEFAULT NULL,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `read_at`)
) ENGINE=InnoDB;

-- ─── SEED DATA ───

-- Users (password: "password123" for all)
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `stage`, `initials`) VALUES
(1, 'Jamie Doe', 'jamie@example.com', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'member', 'Active', 'JD'),
(2, 'Admin User', 'admin@rebirth.app', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'admin', 'Active', 'AU'),
(3, 'Dr. Sarah Mitchell', 'sarah.mitchell@rebirth.app', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'therapist', 'Active', 'SM'),
(4, 'Marcus Webb', 'mwebb@example.com', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'member', 'Active', 'MW'),
(5, 'Emily Torres', 'emily.t@example.com', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'member', 'Onboarding', 'ET');

-- Therapist-patient assignments
INSERT INTO `therapist_patients` (`therapist_id`, `patient_id`) VALUES
(3, 1), (3, 4);

-- Appointments
INSERT INTO `appointments` (`user_id`, `therapist_id`, `title`, `date_time`, `status`) VALUES
(1, 3, 'Session with Dr. Sarah Mitchell', '2026-06-15 10:00:00', 'confirmed'),
(1, 3, 'Group Therapy Session', '2026-06-22 14:00:00', 'pending'),
(4, 3, 'Follow-up with Dr. Sarah Mitchell', '2026-06-15 14:00:00', 'confirmed'),
(5, 3, 'Initial Consultation', '2026-06-16 09:00:00', 'pending'),
(1, 3, 'Weekly Session', '2026-06-01 10:00:00', 'completed');

-- Check-ins
INSERT INTO `check_ins` (`user_id`, `mood`, `craving_level`, `note`, `check_date`) VALUES
(1, 'great', 10, 'Felt amazing today! Clear mind, full of energy.', CURDATE()),
(1, 'good', 20, 'Productive day at work.', '2026-06-03'),
(1, 'okay', 40, 'Had some tough moments but pushed through.', '2026-06-02'),
(1, 'great', 5, 'Celebrated 30 days!', '2026-06-01'),
(4, 'good', 30, 'Staying on track.', CURDATE()),
(5, 'tough', 60, 'First week is hard but I am committed.', '2026-06-03');

-- Journal entries
INSERT INTO `journal_entries` (`user_id`, `content`, `mood`, `created_at`) VALUES
(1, 'Today felt like a breakthrough. I woke up with a sense of clarity I haven\'t felt in months. The morning meditation really helped center my thoughts before the day began.', 'great', '2026-06-04 08:30:00'),
(1, 'Had a difficult conversation with my family today. It was hard but necessary for my healing journey. They are starting to understand what I am going through.', 'okay', '2026-06-03 20:00:00'),
(1, 'Found myself struggling with cravings this afternoon. Used the breathing techniques from my last session with Dr. Mitchell. They really work if you commit to them.', 'tough', '2026-06-02 18:00:00'),
(1, 'Celebrated 30 days! I can hardly believe it. One day at a time really does work. Looking forward to the next milestone.', 'great', '2026-06-01 21:00:00');

-- Milestones
INSERT INTO `milestones` (`name`, `description`, `target_days`) VALUES
('First Week', 'Complete 7 days of consistent check-ins', 7),
('30 Days', 'One month of dedicated recovery tracking', 30),
('60 Days', 'Two months of consistent progress', 60),
('90 Days', 'Quarter of a year strong in recovery', 90),
('6 Months', 'Half a year of dedication and transformation', 180),
('1 Year', 'One full year of recovery and growth', 365);

-- User milestones
INSERT INTO `user_milestones` (`user_id`, `milestone_id`, `progress`, `achieved`, `achieved_at`) VALUES
(1, 1, 100, TRUE, '2026-05-10'),
(1, 2, 100, TRUE, '2026-06-01'),
(1, 3, 100, TRUE, '2026-06-15'),
(1, 4, 67, FALSE, NULL),
(1, 5, 0, FALSE, NULL),
(4, 1, 100, TRUE, '2026-05-20'),
(4, 2, 100, TRUE, '2026-06-10'),
(4, 3, 40, FALSE, NULL),
(5, 1, 42, FALSE, NULL);

-- Support groups
INSERT INTO `groups` (`id`, `name`, `description`, `tag`, `member_count`, `created_by`) VALUES
(1, 'New Beginnings', 'For those in their first 90 days of recovery. Share your journey, find support, and celebrate small wins together.', 'Support', 234, 2),
(2, 'Family & Friends', 'A safe space for loved ones supporting someone through their recovery journey.', 'Support', 189, 2),
(3, 'Mindfulness Circle', 'Daily meditation sessions, breathing exercises, and mindfulness practices for everyone.', 'Wellness', 312, 2);

-- Group members
INSERT INTO `group_members` (`group_id`, `user_id`) VALUES
(1, 1), (1, 4), (1, 5), (2, 1), (3, 1), (3, 4);

-- Community messages
INSERT INTO `messages` (`user_id`, `group_id`, `text`, `created_at`) VALUES
(1, 1, 'Just hit 60 days! Never thought I\'d make it this far. Grateful for this community.', '2026-06-04 09:00:00'),
(4, 1, 'The breathing exercises from yesterday\'s session really helped me through a tough moment today.', '2026-06-04 08:45:00'),
(5, 1, 'Day 5 and feeling hopeful for the first time in a long time.', '2026-06-04 08:00:00'),
(1, 0, 'Anyone else finding the evening check-in helps with sleep? It\'s been a game changer for me.', '2026-06-03 22:00:00'),
(4, 0, 'Grateful for this community. You all keep me going even on the hard days.', '2026-06-03 20:00:00');

-- Message likes
INSERT INTO `message_likes` (`message_id`, `user_id`) VALUES
(1, 4), (1, 5), (4, 1), (4, 4), (4, 5), (5, 1), (5, 5);

-- Educational resources
INSERT INTO `resources` (`type`, `icon`, `title`, `description`, `tag`, `url`, `created_by`) VALUES
('Article', 'fa-regular fa-file-lines', 'Understanding Triggers', 'Learn to identify and manage common triggers in your recovery journey with practical strategies.', 'Education', NULL, 2),
('Video', 'fa-regular fa-circle-play', 'Guided Breathing Exercise', 'A 10-minute guided breathing exercise to help manage anxiety and cravings.', 'Wellness', NULL, 2),
('Guide', 'fa-regular fa-book', 'Family Support Guide', 'A comprehensive guide for loved ones on how to best support someone through recovery.', 'Support', NULL, 2),
('Article', 'fa-regular fa-file-lines', 'Nutrition & Recovery', 'How proper nutrition supports brain healing and emotional stability during recovery.', 'Education', NULL, 2),
('Video', 'fa-regular fa-circle-play', 'Morning Mindfulness', 'Start your day with intention and calm. A 5-minute morning mindfulness routine.', 'Wellness', NULL, 2),
('Guide', 'fa-regular fa-book', 'Relapse Prevention Plan', 'Build your personal relapse prevention plan with evidence-based strategies.', 'Support', NULL, 2);
