-- Migration: Add daily_task_technicians table for tracking individual technician hours
-- Date: 2025-11-13

-- Create table for tracking technicians and their hours per daily task
CREATE TABLE IF NOT EXISTS `daily_task_technicians` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daily_task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hours_worked` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_daily_task_id` (`daily_task_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_task_user` (`daily_task_id`, `user_id`),
  CONSTRAINT `fk_dtt_daily_task` FOREIGN KEY (`daily_task_id`) REFERENCES `daily_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dtt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for better performance on queries
CREATE INDEX `idx_is_primary` ON `daily_task_technicians` (`is_primary`);

-- Note: This table replaces the old 'additional_technicians' JSON field
-- Migration script will copy existing data from daily_tasks table
