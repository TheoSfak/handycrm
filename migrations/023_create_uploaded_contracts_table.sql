-- Migration: Create uploaded_contracts table
-- Date: 2026-05-20
-- Description: Stores manually uploaded contract PDFs with extracted/editable fields.

CREATE TABLE IF NOT EXISTS `uploaded_contracts` (
    `id`                INT(11)         NOT NULL AUTO_INCREMENT,
    `customer_name`     VARCHAR(255)    NOT NULL,
    `file_path`         VARCHAR(500)    NOT NULL,
    `original_filename` VARCHAR(255)    NOT NULL,
    `title`             VARCHAR(500)    DEFAULT NULL COMMENT 'Title of works / contract subject',
    `amount`            DECIMAL(10,2)   DEFAULT NULL COMMENT 'Agreed amount in EUR',
    `start_date`        DATE            DEFAULT NULL COMMENT 'Contract start date',
    `end_date`          DATE            DEFAULT NULL COMMENT 'Contract end / expiry date',
    `description`       TEXT            DEFAULT NULL COMMENT 'Summary of works / scope',
    `notes`             TEXT            DEFAULT NULL COMMENT 'Free notes',
    `extracted_text`    MEDIUMTEXT      DEFAULT NULL COMMENT 'Raw text extracted from PDF',
    `created_by`        INT(11)         DEFAULT NULL,
    `created_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME        DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_customer_name`  (`customer_name`),
    KEY `idx_end_date`       (`end_date`),
    KEY `idx_deleted_at`     (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
