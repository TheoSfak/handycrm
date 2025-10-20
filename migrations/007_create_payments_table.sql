-- Migration: Create payments table for tracking weekly technician payments
-- Date: 2025-10-20
-- Description: Track labor payments to technicians on a weekly basis

CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `technician_id` INT(11) NOT NULL,
    `week_start` DATE NOT NULL,
    `week_end` DATE NOT NULL,
    `total_hours` DECIMAL(10,2) DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `paid_at` DATETIME DEFAULT NULL,
    `paid_by` INT(11) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_technician` (`technician_id`),
    KEY `idx_week` (`week_start`, `week_end`),
    KEY `idx_paid` (`paid_at`),
    CONSTRAINT `fk_payment_technician` FOREIGN KEY (`technician_id`) REFERENCES `technicians` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payment_user` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for unpaid payments
CREATE INDEX idx_unpaid ON payments(technician_id, paid_at);
