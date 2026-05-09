-- ============================================================
-- AntiGravity Todo App - Database Schema
-- Import this file in phpMyAdmin to set up the database
-- ============================================================

-- Create and use database
CREATE DATABASE IF NOT EXISTS `antigravity_todo_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `antigravity_todo_db`;

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(100)     NOT NULL,
  `email`          VARCHAR(191)     NOT NULL,
  `password`       VARCHAR(255)     NOT NULL,
  `avatar`         VARCHAR(255)     DEFAULT 'default.png',
  `reset_token`    VARCHAR(64)      DEFAULT NULL,
  `reset_expires`  DATETIME         DEFAULT NULL,
  `created_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: todo_groups
-- ============================================================
CREATE TABLE IF NOT EXISTS `todo_groups` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED  NOT NULL,
  `name`        VARCHAR(150)  NOT NULL,
  `description` TEXT          DEFAULT NULL,
  `color`       VARCHAR(7)    NOT NULL DEFAULT '#6366f1',
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tg_user_id` (`user_id`),
  CONSTRAINT `fk_tg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: todos
-- ============================================================
CREATE TABLE IF NOT EXISTS `todos` (
  `id`          INT UNSIGNED                              NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED                              NOT NULL,
  `group_id`    INT UNSIGNED                              DEFAULT NULL,
  `title`       VARCHAR(255)                              NOT NULL,
  `description` TEXT                                      DEFAULT NULL,
  `priority`    ENUM('low','medium','high')               NOT NULL DEFAULT 'medium',
  `status`      ENUM('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `due_date`    DATE                                      DEFAULT NULL,
  `deleted_at`  DATETIME                                  DEFAULT NULL,
  `created_at`  DATETIME                                  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME                                  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_todos_user_id`  (`user_id`),
  KEY `idx_todos_group_id` (`group_id`),
  KEY `idx_todos_status`   (`status`),
  KEY `idx_todos_priority` (`priority`),
  KEY `idx_todos_deleted`  (`deleted_at`),
  CONSTRAINT `fk_todos_user`  FOREIGN KEY (`user_id`)  REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_todos_group` FOREIGN KEY (`group_id`) REFERENCES `todo_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: sessions (DB-backed sessions for serverless / Vercel)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`         VARCHAR(128) NOT NULL,
  `data`       MEDIUMTEXT   NOT NULL,
  `expires_at` DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sessions_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Sample seed data  (test user: test@example.com / Test@1234)
-- ============================================================
INSERT INTO `users` (`name`, `email`, `password`) VALUES
('Test User', 'test@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- password above is bcrypt hash of "Test@1234"

INSERT INTO `todo_groups` (`user_id`, `name`, `description`, `color`) VALUES
(1, 'Work',     'Work-related tasks',   '#6366f1'),
(1, 'Personal', 'Personal tasks',       '#10b981'),
(1, 'Shopping', 'Shopping list',        '#f59e0b');

INSERT INTO `todos` (`user_id`, `group_id`, `title`, `description`, `priority`, `status`, `due_date`) VALUES
(1, 1, 'Set up development environment',  'Install XAMPP and configure vhosts', 'high',   'completed', CURDATE()),
(1, 1, 'Design database schema',          'Create ER diagram and SQL file',     'high',   'completed', CURDATE()),
(1, 2, 'Buy groceries',                   'Milk, eggs, bread, butter',          'medium', 'pending',   DATE_ADD(CURDATE(), INTERVAL 2 DAY)),
(1, 2, 'Morning workout',                 '30 minutes cardio + stretching',     'low',    'pending',   DATE_ADD(CURDATE(), INTERVAL 1 DAY)),
(1, 3, 'Review project proposal',         'Check all sections before submission','high',  'in_progress',DATE_ADD(CURDATE(), INTERVAL 3 DAY)),
(1, NULL,'Read a book',                   'Atomic Habits – chapter 5-8',        'low',    'pending',   DATE_ADD(CURDATE(), INTERVAL 7 DAY));
