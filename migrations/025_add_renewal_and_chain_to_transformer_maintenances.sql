-- Migration: Add renewal and history chaining to transformer_maintenances
-- Date: 2026-10-02
-- Description: Adds previous_id, renewed_by_id, is_renewed and renewed_at to transformer_maintenances

ALTER TABLE `transformer_maintenances`
    ADD COLUMN IF NOT EXISTS `previous_id` INT(11) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `renewed_by_id` INT(11) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `is_renewed` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `renewed_at` DATETIME DEFAULT NULL;

ALTER TABLE `transformer_maintenances`
    ADD INDEX IF NOT EXISTS `idx_tm_previous_id` (`previous_id`),
    ADD INDEX IF NOT EXISTS `idx_tm_renewed_by_id` (`renewed_by_id`),
    ADD INDEX IF NOT EXISTS `idx_tm_is_renewed` (`is_renewed`),
    ADD INDEX IF NOT EXISTS `idx_tm_customer_name` (`customer_name`);
