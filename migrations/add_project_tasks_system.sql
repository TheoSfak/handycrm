-- ============================================================================
-- HandyCRM Project Tasks System Migration
-- Version: 1.1.0
-- Date: 2025-10-14
-- Description: Adds complete project tasks tracking with materials, labor,
--              and technicians management
-- ============================================================================

-- Disable foreign key checks for clean migration
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- TABLE 1: technicians
-- Stores all technicians and assistants with their hourly rates
-- ============================================================================

CREATE TABLE IF NOT EXISTS `technicians` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `role` ENUM('technician', 'assistant') NOT NULL DEFAULT 'technician',
  `hourly_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_role` (`role`),
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE 2: project_tasks
-- Main table for project tasks (single day or date range)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `project_tasks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `project_id` INT(11) NOT NULL,
  `task_type` ENUM('single_day', 'date_range') NOT NULL DEFAULT 'single_day',
  `task_date` DATE DEFAULT NULL COMMENT 'For single_day tasks',
  `date_from` DATE DEFAULT NULL COMMENT 'For date_range tasks',
  `date_to` DATE DEFAULT NULL COMMENT 'For date_range tasks',
  `description` TEXT NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `materials_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `labor_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `daily_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_project_id` (`project_id`),
  INDEX `idx_task_type` (`task_type`),
  INDEX `idx_task_date` (`task_date`),
  INDEX `idx_date_range` (`date_from`, `date_to`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_project_tasks_project` 
    FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE 3: task_materials
-- Materials used in each task
-- ============================================================================

CREATE TABLE IF NOT EXISTS `task_materials` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `task_id` INT(11) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `unit_type` ENUM('meters', 'pieces', 'kg', 'liters', 'boxes', 'other') NOT NULL DEFAULT 'pieces',
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_task_id` (`task_id`),
  CONSTRAINT `fk_task_materials_task` 
    FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE 4: task_labor
-- Labor/technician work hours for each task
-- ============================================================================

CREATE TABLE IF NOT EXISTS `task_labor` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `task_id` INT(11) NOT NULL,
  `technician_id` INT(11) DEFAULT NULL COMMENT 'NULL if temporary technician',
  `technician_name` VARCHAR(100) NOT NULL,
  `technician_role` ENUM('technician', 'assistant') DEFAULT NULL COMMENT 'Cached from technicians table',
  `is_temporary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if not in technicians table',
  `hours_worked` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `time_from` TIME DEFAULT NULL COMMENT 'Optional: Start time',
  `time_to` TIME DEFAULT NULL COMMENT 'Optional: End time',
  `hourly_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_task_id` (`task_id`),
  INDEX `idx_technician_id` (`technician_id`),
  INDEX `idx_is_temporary` (`is_temporary`),
  CONSTRAINT `fk_task_labor_task` 
    FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_task_labor_technician` 
    FOREIGN KEY (`technician_id`) REFERENCES `technicians` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SAMPLE DATA (Optional - for testing)
-- ============================================================================

-- Insert sample technicians
INSERT INTO `technicians` (`name`, `role`, `hourly_rate`, `phone`, `email`, `is_active`) VALUES
('Γιώργος Παπαδόπουλος', 'technician', 20.00, '6912345678', 'giorgos@example.com', 1),
('Νίκος Ιωάννου', 'technician', 18.00, '6923456789', 'nikos@example.com', 1),
('Κώστας Δημητρίου', 'assistant', 12.00, '6934567890', 'kostas@example.com', 1),
('Μιχάλης Γεωργίου', 'assistant', 10.00, '6945678901', 'michalis@example.com', 1);

-- ============================================================================
-- Re-enable foreign key checks
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- ROLLBACK SCRIPT (Run this to undo migration)
-- ============================================================================

/*
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `task_labor`;
DROP TABLE IF EXISTS `task_materials`;
DROP TABLE IF EXISTS `project_tasks`;
DROP TABLE IF EXISTS `technicians`;
SET FOREIGN_KEY_CHECKS = 1;
*/
