-- Migration: Add scheduled_date to maintenance_offers (if missing)
-- Date: 2026-05-13
-- Description: Ensures scheduled_date column exists for installs where the
--              table was created before this column was added to migration 020.

ALTER TABLE `maintenance_offers`
    ADD COLUMN `scheduled_date` DATE DEFAULT NULL;
