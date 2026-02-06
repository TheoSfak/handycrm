-- ============================================================================
-- Migration: Ensure Materials Catalog Tables Exist
-- Version: 1.2.0
-- Date: 2025-10-16
-- Description: Creates materials catalog tables if they don't exist (idempotent)
-- ============================================================================

USE handycrm;

-- Create material categories table
CREATE TABLE IF NOT EXISTS `material_categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories if table is empty
INSERT IGNORE INTO `material_categories` (`id`, `name`, `description`) VALUES
(1, 'Ηλεκτρολογικά', 'Καλώδια, διακόπτες, πρίζες, πίνακες'),
(2, 'Υδραυλικά', 'Σωλήνες, βάνες, σιφόνια'),
(3, 'Οικοδομικά', 'Τσιμέντο, άμμος, τούβλα'),
(4, 'Χρώματα & Βερνίκια', 'Ακρυλικά, αλκυδικά, βερνίκια'),
(5, 'Μηχανολογικά', 'Βίδες, μπουλόνια, εργαλεία'),
(6, 'Άλλα', 'Διάφορα υλικά');

-- Create materials catalog table
CREATE TABLE IF NOT EXISTS `materials_catalog` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) DEFAULT NULL,
    `name` varchar(255) NOT NULL,
    `description` text,
    `unit` varchar(50) DEFAULT 'τεμ.',
    `default_price` decimal(10,2) DEFAULT '0.00',
    `supplier` varchar(255) DEFAULT NULL,
    `notes` text,
    `aliases` text COMMENT 'Comma-separated search aliases (auto-generated + manual)',
    `is_active` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category_id`),
    KEY `idx_active` (`is_active`),
    FULLTEXT KEY `idx_name_aliases` (`name`,`aliases`),
    CONSTRAINT `fk_material_category` FOREIGN KEY (`category_id`) 
        REFERENCES `material_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update task_materials to link with catalog (if columns don't exist)
SET @dbname = DATABASE();
SET @tablename = 'task_materials';
SET @columnname1 = 'catalog_material_id';
SET @columnname2 = 'name';
SET @columnname3 = 'unit';

-- Add catalog_material_id if not exists
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE (table_name = @tablename)
       AND (table_schema = @dbname)
       AND (column_name = @columnname1)) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname1, ' INT NULL AFTER task_id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add name if not exists
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE (table_name = @tablename)
       AND (table_schema = @dbname)
       AND (column_name = @columnname2)) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname2, ' VARCHAR(255) NOT NULL AFTER catalog_material_id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add unit if not exists
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE (table_name = @tablename)
       AND (table_schema = @dbname)
       AND (column_name = @columnname3)) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname3, ' VARCHAR(50) NULL AFTER quantity')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add foreign key if not exists
SET @fk_exists = (SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = @dbname 
      AND TABLE_NAME = @tablename 
      AND CONSTRAINT_NAME = 'fk_catalog_material');

SET @preparedStatement = IF(@fk_exists > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE ', @tablename, ' ADD CONSTRAINT fk_catalog_material 
            FOREIGN KEY (catalog_material_id) REFERENCES materials_catalog(id) ON DELETE SET NULL')
);
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SELECT 'Migration completed: Materials catalog system verified/created' as message;
