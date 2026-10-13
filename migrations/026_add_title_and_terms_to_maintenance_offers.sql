-- Migration: Add title and terms to maintenance_offers
-- Date: 2026-10-13
-- Description: Adds optional title (scenario) and terms (economic/payment terms) columns

ALTER TABLE `maintenance_offers`
    ADD COLUMN `title` VARCHAR(255) DEFAULT NULL AFTER `company_name`,
    ADD COLUMN `terms` TEXT DEFAULT NULL AFTER `notes`;
