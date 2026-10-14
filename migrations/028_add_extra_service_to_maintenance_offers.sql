-- Migration: Add extra_service fields to maintenance_offers
-- Date: 2026-10-14
-- Description: Adds optional second service fields (name, qty, price) to maintenance_offers

ALTER TABLE `maintenance_offers`
    ADD COLUMN `extra_service_name` VARCHAR(255) DEFAULT NULL AFTER `price`,
    ADD COLUMN `extra_service_qty` INT DEFAULT 1 AFTER `extra_service_name`,
    ADD COLUMN `extra_service_price` DECIMAL(10,2) DEFAULT NULL AFTER `extra_service_qty`;
