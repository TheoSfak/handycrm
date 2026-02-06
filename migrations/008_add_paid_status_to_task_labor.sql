-- Migration: Add payment tracking to task_labor
-- Date: 2025-10-20
-- Description: Add paid_at and paid_by columns to track individual labor entry payments

ALTER TABLE `task_labor` 
ADD COLUMN `paid_at` DATETIME DEFAULT NULL AFTER `notes`,
ADD COLUMN `paid_by` INT(11) DEFAULT NULL AFTER `paid_at`,
ADD KEY `idx_paid` (`paid_at`),
ADD CONSTRAINT `fk_task_labor_paid_by` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add index for filtering unpaid entries
CREATE INDEX idx_task_labor_unpaid ON task_labor(technician_id, paid_at);
