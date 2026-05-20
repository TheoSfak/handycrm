-- Migration: Add contract_end_date to maintenance_offers
-- Date: 2026-06-01
-- Description: Adds contract_end_date field for tracking when accepted maintenance
--              contracts expire, enabling dashboard reminders.

ALTER TABLE `maintenance_offers`
    ADD COLUMN IF NOT EXISTS `contract_end_date` DATE DEFAULT NULL COMMENT 'Date when the accepted maintenance contract expires';

-- Auto-populate for existing accepted records: accepted_at + 1 year
UPDATE `maintenance_offers`
SET `contract_end_date` = DATE_ADD(DATE(`accepted_at`), INTERVAL 1 YEAR)
WHERE `accepted` = 1
  AND `accepted_at` IS NOT NULL
  AND `contract_end_date` IS NULL;

-- Add index for fast dashboard queries
ALTER TABLE `maintenance_offers`
    ADD INDEX IF NOT EXISTS `idx_contract_end_date` (`contract_end_date`);
