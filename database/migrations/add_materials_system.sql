-- Materials System Migration
-- Creates tables for materials catalog and categories

-- Materials Categories Table
CREATE TABLE IF NOT EXISTS `material_categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Materials Catalog Table
CREATE TABLE IF NOT EXISTS `materials_catalog` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) DEFAULT NULL,
    `name` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `unit` VARCHAR(50) DEFAULT NULL COMMENT 'τεμάχια, μέτρα, κιλά, λίτρα, κ.τ.λ.',
    `default_price` DECIMAL(10,2) DEFAULT NULL,
    `supplier` VARCHAR(200) DEFAULT NULL,
    `notes` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    KEY `is_active` (`is_active`),
    KEY `name` (`name`),
    CONSTRAINT `materials_catalog_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `material_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add catalog_material_id to task_materials for linking
ALTER TABLE `task_materials` 
ADD COLUMN `catalog_material_id` INT(11) DEFAULT NULL AFTER `id`,
ADD KEY `catalog_material_id` (`catalog_material_id`),
ADD CONSTRAINT `task_materials_ibfk_catalog` FOREIGN KEY (`catalog_material_id`) REFERENCES `materials_catalog` (`id`) ON DELETE SET NULL;

-- Insert default categories
INSERT INTO `material_categories` (`name`, `description`) VALUES
('Ηλεκτρολογικά', 'Ηλεκτρολογικό υλικό και εξοπλισμός'),
('Υδραυλικά', 'Υδραυλικό υλικό και εξαρτήματα'),
('Οικοδομικά', 'Οικοδομικά υλικά και εργαλεία'),
('Χρώματα & Βερνίκια', 'Χρώματα, βερνίκια και αναλώσιμα'),
('Μηχανολογικά', 'Μηχανολογικός εξοπλισμός'),
('Άλλα', 'Λοιπά υλικά και αναλώσιμα');
