USE `rebirth`;

-- Users: passwords are all "password123" (bcrypt hash)
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

-- Groups
INSERT INTO `groups` (`id`, `name`, `description`, `tag`, `member_count`, `created_by`) VALUES
(1, 'New Beginnings', 'For those in their first 90 days of recovery. Share your journey, find support, and celebrate small wins together.', 'Support', 234, 2),
(2, 'Family & Friends', 'A safe space for loved ones supporting someone through their recovery journey.', 'Support', 189, 2),
(3, 'Mindfulness Circle', 'Daily meditation sessions, breathing exercises, and mindfulness practices for everyone.', 'Wellness', 312, 2);

-- Group members
INSERT INTO `group_members` (`group_id`, `user_id`) VALUES
(1, 1), (1, 4), (1, 5), (2, 1), (3, 1), (3, 4);

-- Messages
INSERT INTO `messages` (`user_id`, `group_id`, `text`, `created_at`) VALUES
(1, 1, 'Just hit 60 days! Never thought I\'d make it this far. Grateful for this community.', '2026-06-04 09:00:00'),
(4, 1, 'The breathing exercises from yesterday\'s session really helped me through a tough moment today.', '2026-06-04 08:45:00'),
(5, 1, 'Day 5 and feeling hopeful for the first time in a long time.', '2026-06-04 08:00:00'),
(1, 0, 'Anyone else finding the evening check-in helps with sleep? It\'s been a game changer for me.', '2026-06-03 22:00:00'),
(4, 0, 'Grateful for this community. You all keep me going even on the hard days.', '2026-06-03 20:00:00');

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
