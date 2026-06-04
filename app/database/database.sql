CREATE DATABASE IF NOT EXISTS `rebirth` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rebirth`;

DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `message_likes`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `group_members`;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS `conversation_messages`;
DROP TABLE IF EXISTS `conversations`;
DROP TABLE IF EXISTS `therapist_resources`;
DROP TABLE IF EXISTS `therapist_patients`;
DROP TABLE IF EXISTS `sos_alerts`;
DROP TABLE IF EXISTS `resources`;
DROP TABLE IF EXISTS `journal_entries`;
DROP TABLE IF EXISTS `check_ins`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('member','therapist','admin') NOT NULL DEFAULT 'member',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `initials` VARCHAR(4) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `check_ins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `mood` ENUM('great','good','neutral','difficult','struggling') NOT NULL,
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
  `mood` ENUM('great','good','neutral','difficult','struggling') NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
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
  `parent_id` INT DEFAULT NULL,
  `text` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE
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

-- ─── SEED DATA ─────────────────────────────────────

-- Users: passwords are all "password123" (bcrypt hash)
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `initials`) VALUES
(1, 'Jamie Doe', 'jamie@example.com', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'member', 'JD'),
(2, 'Admin User', 'admin@rebirth.app', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'admin', 'AU'),
(3, 'Dr. Sarah Mitchell', 'sarah.mitchell@rebirth.app', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'therapist', 'SM'),
(4, 'Marcus Webb', 'mwebb@example.com', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'member', 'MW'),
(5, 'Emily Torres', 'emily.t@example.com', '$2y$12$vXTF6SQHXmBqK4Rgne.4oe8MEBuisi6evzvzgytRfLuJTXXK/i.km', 'member', 'ET');

-- Therapist-patient assignments
INSERT INTO `therapist_patients` (`therapist_id`, `patient_id`) VALUES
(3, 1), (3, 4);

-- Check-ins
INSERT INTO `check_ins` (`user_id`, `mood`, `craving_level`, `note`, `check_date`) VALUES
(1, 'great', 10, 'Felt amazing today! Clear mind, full of energy.', CURDATE()),
(1, 'good', 20, 'Productive day at work.', '2026-06-03'),
(1, 'neutral', 40, 'Had some tough moments but pushed through.', '2026-06-02'),
(1, 'great', 5, 'Celebrated 30 days!', '2026-06-01'),
(4, 'good', 30, 'Staying on track.', CURDATE()),
(5, 'difficult', 60, 'First week is hard but I am committed.', '2026-06-03');

-- Journal entries
INSERT INTO `journal_entries` (`user_id`, `content`, `mood`, `created_at`) VALUES
(1, 'Today felt like a breakthrough. I woke up with a sense of clarity I haven\'t felt in months. The morning meditation really helped center my thoughts before the day began.', 'great', '2026-06-04 08:30:00'),
(1, 'Had a difficult conversation with my family today. It was hard but necessary for my healing journey. They are starting to understand what I am going through.', 'neutral', '2026-06-03 20:00:00'),
(1, 'Found myself struggling with cravings this afternoon. Used the breathing techniques from my last session with Dr. Mitchell. They really work if you commit to them.', 'difficult', '2026-06-02 18:00:00'),
(1, 'Celebrated 30 days! I can hardly believe it. One day at a time really does work. Looking forward to the next milestone.', 'great', '2026-06-01 21:00:00');

-- Groups
INSERT INTO `groups` (`id`, `name`, `description`, `tag`, `member_count`, `created_by`) VALUES
(1, 'Recovery', 'Support group for everyone in recovery. Share your journey, find support, and celebrate wins together.', 'Support', 234, 2),
(2, 'Motivation', 'Daily motivation, quotes, and success stories to keep you going.', 'Support', 189, 2),
(3, 'Relapse Prevention', 'Strategies, tools, and community support for preventing relapse.', 'Education', 312, 2),
(4, 'Success Stories', 'Share your wins, milestones, and success stories with the community.', 'Support', 156, 2);

-- Group members
INSERT INTO `group_members` (`group_id`, `user_id`) VALUES
(1, 1), (1, 4), (1, 5), (2, 1), (3, 1), (3, 4), (4, 1), (4, 5);

-- Messages (community posts)
INSERT INTO `messages` (`id`, `user_id`, `group_id`, `text`, `created_at`) VALUES
(1, 1, 1, 'Just hit 60 days! Never thought I\'d make it this far. Grateful for this community.', '2026-06-04 09:00:00'),
(2, 4, 1, 'The breathing exercises from yesterday\'s session really helped me through a tough moment today.', '2026-06-04 08:45:00'),
(3, 5, 1, 'Day 5 and feeling hopeful for the first time in a long time.', '2026-06-04 08:00:00'),
(4, 1, 0, 'Anyone else finding the evening check-in helps with sleep? It\'s been a game changer for me.', '2026-06-03 22:00:00'),
(5, 4, 0, 'Grateful for this community. You all keep me going even on the hard days.', '2026-06-03 20:00:00');

-- Message likes
INSERT INTO `message_likes` (`message_id`, `user_id`) VALUES
(1, 4), (1, 5), (4, 1), (4, 4), (4, 5), (5, 1), (5, 5);

-- Resources
INSERT INTO `resources` (`type`, `icon`, `title`, `description`, `tag`, `url`, `created_by`) VALUES
('Article', 'fa-regular fa-file-lines', 'Understanding Triggers', 'Learn to identify and manage common triggers in your recovery journey with practical strategies.', 'Education', NULL, 2),
('Video', 'fa-regular fa-circle-play', 'Guided Breathing Exercise', 'A 10-minute guided breathing exercise to help manage anxiety and cravings.', 'Wellness', NULL, 2),
('Guide', 'fa-regular fa-book', 'Family Support Guide', 'A comprehensive guide for loved ones on how to best support someone through recovery.', 'Support', NULL, 2),
('Article', 'fa-regular fa-file-lines', 'Nutrition & Recovery', 'How proper nutrition supports brain healing and emotional stability during recovery.', 'Education', NULL, 2),
('Video', 'fa-regular fa-circle-play', 'Morning Mindfulness', 'Start your day with intention and calm. A 5-minute morning mindfulness routine.', 'Wellness', NULL, 2),
('Guide', 'fa-regular fa-book', 'Relapse Prevention Plan', 'Build your personal relapse prevention plan with evidence-based strategies.', 'Support', NULL, 2);
