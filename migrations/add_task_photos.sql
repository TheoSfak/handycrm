-- Migration: Add Task Photos System
-- Version: 1.2.0
-- Date: 2025-10-15
-- Description: Adds photo gallery support for project tasks

-- Create task_photos table
CREATE TABLE IF NOT EXISTS `task_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `photo_type` enum('before','after','during','issue','other') DEFAULT 'other',
  `caption` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `photo_type` (`photo_type`),
  CONSTRAINT `task_photos_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_photos_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for faster queries
CREATE INDEX idx_task_photos_task_type ON task_photos(task_id, photo_type);
CREATE INDEX idx_task_photos_created ON task_photos(created_at);
