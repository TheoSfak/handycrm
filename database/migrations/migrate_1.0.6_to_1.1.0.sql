-- Migration from v1.0.6 to v1.1.0
-- Run this script on your existing database to upgrade

-- Add hourly_rate to users table
ALTER TABLE `users` 
ADD COLUMN `hourly_rate` DECIMAL(10,2) DEFAULT 0.00 AFTER `role`;

-- Update role enum to include 'assistant'
ALTER TABLE `users` 
MODIFY COLUMN `role` ENUM('admin','manager','technician','assistant') NOT NULL DEFAULT 'technician';

-- Create project_tasks table
CREATE TABLE IF NOT EXISTS `project_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `task_type` enum('single_day','date_range') NOT NULL DEFAULT 'single_day',
  `task_date` date DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `materials_total` decimal(10,2) DEFAULT 0.00,
  `labor_total` decimal(10,2) DEFAULT 0.00,
  `total_cost` decimal(10,2) DEFAULT 0.00,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `task_date` (`task_date`),
  KEY `date_from` (`date_from`),
  KEY `date_to` (`date_to`),
  CONSTRAINT `project_tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create task_materials table
CREATE TABLE IF NOT EXISTS `task_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_type` enum('pieces','meters','kilos','liters','hours','other') NOT NULL DEFAULT 'pieces',
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `task_materials_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create task_labor table
CREATE TABLE IF NOT EXISTS `task_labor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `technician_name` varchar(255) NOT NULL,
  `hours` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `technician_id` (`technician_id`),
  CONSTRAINT `task_labor_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_labor_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Done! Your database is now ready for v1.1.0
