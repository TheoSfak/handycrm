-- Migration: Create maintenance_offers table
-- Date: 2026-05-12
-- Description: Stores maintenance offer proposals sent to companies

CREATE TABLE IF NOT EXISTS `maintenance_offers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `offer_number` VARCHAR(30) NOT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `transformers_count` INT(11) NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `offer_expiry_date` DATE DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `accepted` TINYINT(1) NOT NULL DEFAULT 0,
    `accepted_at` DATETIME DEFAULT NULL,
    `scheduled_date` DATE DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_company_name` (`company_name`),
    KEY `idx_accepted` (`accepted`),
    KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
