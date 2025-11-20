-- Migration: Add pricing to transformer maintenances
-- Date: 2025-11-13

-- Add total_amount column to transformer_maintenances
ALTER TABLE `transformer_maintenances` 
ADD COLUMN `total_amount` DECIMAL(10,2) NULL DEFAULT NULL AFTER `is_invoiced`;

-- Add maintenance pricing settings to smtp_settings (using as general settings table)
INSERT INTO `smtp_settings` (`setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
('maintenance_price_1_transformer', '400.00', NOW(), NOW()),
('maintenance_price_2_transformers', '600.00', NOW(), NOW()),
('maintenance_price_3_transformers', '900.00', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    `setting_value` = VALUES(`setting_value`),
    `updated_at` = NOW();

-- Note: Prices are in EUR without VAT
-- 1 transformer = 400€
-- 2 transformers = 600€  
-- 3+ transformers = 900€
