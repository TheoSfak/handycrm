-- Migration: Add transformer_power to maintenance_offers
-- Date: 2026-10-13
-- Description: Stores transformer capacity/power rating string (e.g. '630 kVA' or '500 kVA & 630 kVA')

ALTER TABLE `maintenance_offers`
    ADD COLUMN `transformer_power` VARCHAR(100) DEFAULT NULL AFTER `transformers_count`;
