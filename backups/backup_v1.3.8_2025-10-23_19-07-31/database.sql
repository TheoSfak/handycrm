-- HandyCRM Database Backup
-- Created: 2025-10-23 19:07:31
-- Database: u858321845_handycrm1

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- Table: appointments
DROP TABLE IF EXISTS `appointments`;
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `technician_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `appointment_date` datetime NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `status` enum('scheduled','confirmed','in_progress','completed','cancelled','no_show') DEFAULT 'scheduled',
  `address` text DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `reminder_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `project_id` (`project_id`),
  KEY `technician_id` (`technician_id`),
  KEY `created_by` (`created_by`),
  KEY `appointment_date` (`appointment_date`),
  KEY `status` (`status`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: customer_communications
DROP TABLE IF EXISTS `customer_communications`;
CREATE TABLE `customer_communications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `communication_type` enum('phone','email','visit','message') NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `description` text NOT NULL,
  `communication_date` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `communication_date` (`communication_date`),
  CONSTRAINT `customer_communications_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_communications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: customers
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `customer_type` enum('individual','company') DEFAULT 'individual',
  `phone` varchar(20) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slug` (`slug`),
  KEY `created_by` (`created_by`),
  KEY `customer_type` (`customer_type`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` VALUES ('1', 'teab-creta-maris-tera-maris', 'TEAB Creta Maris', 'Tera Maris', 'Creta Maris', 'company', '2810000000', '', '', 'Xersoniso', '', '', '', '', '1', '1', '2025-10-22 11:42:23', '2025-10-22 11:42:23');


-- Table: invoice_items
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `item_type` enum('material','labor','service') NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(8,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: invoices
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `quote_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) DEFAULT 24.00,
  `vat_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `payment_method` enum('cash','bank_transfer','card','check') DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`),
  KEY `project_id` (`project_id`),
  KEY `quote_id` (`quote_id`),
  KEY `created_by` (`created_by`),
  KEY `status` (`status`),
  KEY `due_date` (`due_date`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: material_categories
DROP TABLE IF EXISTS `material_categories`;
CREATE TABLE `material_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `material_categories` VALUES ('1', 'Ηλεκτρολογικά', 'Ηλεκτρολογικά υλικά', '2025-10-22 11:38:17', '2025-10-22 11:38:17');
INSERT INTO `material_categories` VALUES ('2', 'Υδραυλικά', 'Υδραυλικά υλικά', '2025-10-22 11:38:17', '2025-10-22 11:38:17');
INSERT INTO `material_categories` VALUES ('3', 'Οικοδομικά', 'Οικοδομικά υλικά', '2025-10-22 11:38:17', '2025-10-22 11:38:17');
INSERT INTO `material_categories` VALUES ('4', 'Άλλα', 'Διάφορα υλικά', '2025-10-22 11:38:17', '2025-10-22 11:38:17');


-- Table: material_movements
DROP TABLE IF EXISTS `material_movements`;
CREATE TABLE `material_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `movement_type` enum('in','out','adjustment') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `total_cost` decimal(10,2) DEFAULT 0.00,
  `reference` varchar(200) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `movement_date` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `material_id` (`material_id`),
  KEY `project_id` (`project_id`),
  KEY `created_by` (`created_by`),
  KEY `movement_date` (`movement_date`),
  CONSTRAINT `material_movements_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `material_movements_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `material_movements_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: materials
DROP TABLE IF EXISTS `materials`;
CREATE TABLE `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'τεμ',
  `current_stock` decimal(10,2) DEFAULT 0.00,
  `min_stock_level` decimal(10,2) DEFAULT 0.00,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `supplier` varchar(200) DEFAULT NULL,
  `supplier_code` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `category` (`category`),
  CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: materials_catalog
DROP TABLE IF EXISTS `materials_catalog`;
CREATE TABLE `materials_catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `default_price` decimal(10,2) DEFAULT 0.00,
  `supplier` varchar(255) DEFAULT NULL,
  `aliases` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `materials_catalog` VALUES ('1', 'Καλώδιο ΝΥΜ 3x1.5', '1', NULL, 'μ', '1.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('2', 'Καλώδιο ΝΥΜ 3x2.5', '1', NULL, 'μ', '1.70', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('3', 'Καλώδιο ΝΥΜ 5x2.5', '1', NULL, 'μ', '2.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('4', 'Καλώδιο ΝΥΜ 5x4', '1', NULL, 'μ', '4.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('5', 'Καλώδιο ΝΥΑ 1x1.5', '1', NULL, 'μ', '0.35', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('6', 'Καλώδιο ΝΥΑ 1x2.5', '1', NULL, 'μ', '0.55', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('7', 'Καλώδιο Η05VV-F 3x1.5', '1', NULL, 'μ', '1.10', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('8', 'Καλώδιο Η07RN-F 3x4', '1', NULL, 'μ', '3.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('9', 'Σωλήνα CONDUR Φ16', '1', NULL, 'μ', '0.28', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('10', 'Σωλήνα CONDUR Φ23', '1', NULL, 'μ', '0.35', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('11', 'Σωλήνα CONDUR Φ32', '1', NULL, 'μ', '0.58', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('12', 'Σωλήνα FLEX Φ20 (εύκαμπτη)', '1', NULL, 'μ', '0.55', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('13', 'Σωλήνα FLEX Φ25 (εύκαμπτη)', '1', NULL, 'μ', '0.70', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('14', 'Μούφα σύνδεσης Φ20', '1', NULL, 'τεμ', '0.15', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('15', 'Μούφα σύνδεσης Φ25', '1', NULL, 'τεμ', '0.18', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('16', 'Γωνία 90° Φ20', '1', NULL, 'τεμ', '0.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('17', 'Γωνία 90° Φ25', '1', NULL, 'τεμ', '0.25', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('18', 'Κουτί διακλάδωσης 10x10', '1', NULL, 'τεμ', '0.85', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('19', 'Κουτί διακλάδωσης 15x15', '1', NULL, 'τεμ', '1.10', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('20', 'Κουτί χωνευτό Φ68', '1', NULL, 'τεμ', '0.25', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('21', 'Διακόπτης μονός Legrand Valena', '1', NULL, 'τεμ', '3.50', 'Legrand', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('22', 'Διακόπτης διπλός Legrand Valena', '1', NULL, 'τεμ', '4.20', 'Legrand', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('23', 'Πρίζα σούκο Legrand Valena', '1', NULL, 'τεμ', '4.50', 'Legrand', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('24', 'Πρίζα τηλεόρασης', '1', NULL, 'τεμ', '5.00', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('25', 'Πρίζα δικτύου RJ45 Cat6', '1', NULL, 'τεμ', '6.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('26', 'Πίνακας διανομής 12 θέσεων', '1', NULL, 'τεμ', '19.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('27', 'Πίνακας διανομής 24 θέσεων', '1', NULL, 'τεμ', '28.00', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('28', 'Αυτόματος διακόπτης 10A', '1', NULL, 'τεμ', '3.00', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('29', 'Αυτόματος διακόπτης 16A', '1', NULL, 'τεμ', '3.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('30', 'Αυτόματος διακόπτης 20A', '1', NULL, 'τεμ', '3.40', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('31', 'Ρελέ διαρροής 30mA 2P 40A', '1', NULL, 'τεμ', '18.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('32', 'Ρελέ διαρροής 30mA 4P 40A', '1', NULL, 'τεμ', '28.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('33', 'Διπλό κουτί τοίχου χωνευτό', '1', NULL, 'τεμ', '0.45', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('34', 'Ράβδος γείωσης χαλκού 1.5 m', '1', NULL, 'τεμ', '6.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('35', 'Σφιγκτήρας γείωσης', '1', NULL, 'τεμ', '1.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('36', 'Κλέμμα σύνδεσης WAGO 3πολική', '1', NULL, 'τεμ', '0.35', 'WAGO', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('37', 'Κλέμμα σύνδεσης WAGO 5πολική', '1', NULL, 'τεμ', '0.55', 'WAGO', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('38', 'Καλωδιοταινία (δεματικά) 100 mm', '1', NULL, 'τεμ', '0.02', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('39', 'Καλωδιοταινία (δεματικά) 200 mm', '1', NULL, 'τεμ', '0.03', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('40', 'Καμπάνα φωτισμού IP65', '1', NULL, 'τεμ', '12.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('41', 'Φωτιστικό LED 18 W Οροφής', '1', NULL, 'τεμ', '9.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('42', 'Λάμπα LED E27 9 W', '1', NULL, 'τεμ', '1.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('43', 'Φωτιστικό Αδιάβροχο Νεον 2x18 W', '1', NULL, 'τεμ', '15.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('44', 'Πυρακτωμένη λάμπα σήμανσης 230 V', '1', NULL, 'τεμ', '0.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('45', 'Κουτί ασφαλείας IP65', '1', NULL, 'τεμ', '7.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('46', 'Πρίζα εξωτερικού χώρου IP44', '1', NULL, 'τεμ', '4.60', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('47', 'Διακόπτης αδιάβροχος IP44', '1', NULL, 'τεμ', '4.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('48', 'Ταινία μονωτική PVC 20 m', '1', NULL, 'τεμ', '0.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('49', 'Καλώδιο τηλεφώνου 2x0.6', '1', NULL, 'μ', '0.28', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('50', 'Καλώδιο συναγερμού 6x0.22', '1', NULL, 'μ', '0.45', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('51', 'Καναλάκι πλαστικό 20x10 λευκό', '1', NULL, 'μ', '0.85', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('52', 'Καναλάκι πλαστικό 40x25 λευκό', '1', NULL, 'μ', '1.25', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('53', 'Καναλάκι πλαστικό 60x40 λευκό', '1', NULL, 'μ', '2.10', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('54', 'Καπάκι καναλιού 40x25', '1', NULL, 'μ', '0.35', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('55', 'Βάση ρελέ DIN 2P', '1', NULL, 'τεμ', '1.60', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('56', 'Ρελέ ισχύος 25A', '1', NULL, 'τεμ', '9.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('57', 'Θερμικό προστασίας 25A', '1', NULL, 'τεμ', '12.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('58', 'Χρονοδιακόπτης μηχανικός 24h', '1', NULL, 'τεμ', '7.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('59', 'Φωτοκύτταρο 230V', '1', NULL, 'τεμ', '6.40', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('60', 'Αισθητήρας κίνησης PIR', '1', '', 'τεμ', '8.90', 'Ηλεκτρονική ΑΕ', 'aisthhthras, kinhshs', '', '1', '2025-10-22 11:38:21', '2025-10-22 11:38:29');
INSERT INTO `materials_catalog` VALUES ('61', 'Διακόπτης ροοστάτης LED', '1', NULL, 'τεμ', '9.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('62', 'Κουτί αυτοματισμού πλαστικό 8 θέσεων', '1', NULL, 'τεμ', '11.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('63', 'Πίνακας επιτοίχιος μεταλλικός 36 θέσεων', '1', NULL, 'τεμ', '38.00', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('64', 'Καλώδιο ισχύος 4x6 ΝΥΥ', '1', NULL, 'μ', '5.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('65', 'Καλώδιο ισχύος 4x10 ΝΥΥ', '1', NULL, 'μ', '9.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('66', 'Καλώδιο ισχύος 5x6 ΝΥΥ', '1', NULL, 'μ', '7.40', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('67', 'Καλώδιο θέρμανσης δαπέδου 10m', '1', NULL, 'τεμ', '18.00', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('68', 'Σφιγκτήρας καλωδίων μεταλλικός M6', '1', NULL, 'τεμ', '0.25', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('69', 'Σφιγκτήρας καλωδίων πλαστικός Φ20', '1', NULL, 'τεμ', '0.15', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('70', 'Καναλάκι αλουμινίου για LED 1m', '1', NULL, 'μ', '4.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('71', 'Τροφοδοτικό LED 12V 100W', '1', NULL, 'τεμ', '14.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('72', 'Τροφοδοτικό LED 24V 200W', '1', NULL, 'τεμ', '22.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('73', 'Ταινία LED 5m 12V IP20', '1', NULL, 'τεμ', '8.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('74', 'Ταινία LED 5m 12V IP65', '1', NULL, 'τεμ', '12.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('75', 'Αντάπτορας GU10 σε E27', '1', NULL, 'τεμ', '1.00', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('76', 'Φωτιστικό σποτ χωνευτό GU10', '1', NULL, 'τεμ', '5.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('77', 'Λάμπα LED GU10 5W', '1', NULL, 'τεμ', '1.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('78', 'Φωτιστικό οροφής 2x36W', '1', NULL, 'τεμ', '16.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('79', 'Διακόπτης ρευματοδότησης εξωτερικός IP65', '1', NULL, 'τεμ', '8.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('80', 'Διακόπτης αλλαγής φάσης 3P 40A', '1', NULL, 'τεμ', '26.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('81', 'Κεντρικός διακόπτης φωτισμού ξενοδοχείου', '1', NULL, 'τεμ', '12.00', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('82', 'Ανιχνευτής καπνού 230V', '1', NULL, 'τεμ', '10.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('83', 'Ανιχνευτής θερμότητας', '1', NULL, 'τεμ', '11.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('84', 'Σειρήνα συναγερμού 12V', '1', NULL, 'τεμ', '8.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('85', 'Μπαταρία 12V 7Ah (UPS/συναγερμού)', '1', NULL, 'τεμ', '14.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('86', 'Μπαλαντέζα 3x1.5 10m', '1', NULL, 'τεμ', '11.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('87', 'Πρίζα σούκο διπλή', '1', NULL, 'τεμ', '5.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('88', 'Πρίζα USB 2xA 5V', '1', NULL, 'τεμ', '12.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('89', 'Πρίζα σούκο εξωτερική IP54', '1', NULL, 'τεμ', '6.40', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('90', 'Αντάπτορας σούκο 3πλός', '1', NULL, 'τεμ', '3.20', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('91', 'Αντάπτορας σούκο με διακόπτη', '1', NULL, 'τεμ', '4.10', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('92', 'Επέκταση σούκο 3 θέσεων 1.5m', '1', NULL, 'τεμ', '6.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('93', 'Πολύπριζο ασφαλείας 5 θέσεων 3m', '1', NULL, 'τεμ', '12.50', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('94', 'Ράγα DIN 35mm 1m', '1', NULL, 'μ', '1.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('95', 'Κλέμμα πίνακα 10mm²', '1', NULL, 'τεμ', '0.60', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('96', 'Μονωτικός σωλήνας θερμοσυστελλόμενος Φ6', '1', NULL, 'μ', '0.80', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('97', 'Μονωτικός σωλήνας θερμοσυστελλόμενος Φ10', '1', NULL, 'μ', '1.10', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('98', 'Μόνωση καλωδίων (ταινία υφασμάτινη)', '1', NULL, 'τεμ', '1.30', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('99', 'Ετικέτες καλωδίων πλαστικές (100τμχ)', '1', NULL, 'σετ', '3.60', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('100', 'Πιστόλι θερμοκόλλας 60W', '1', NULL, 'τεμ', '9.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('103', 'Καλώδιο 3Χ4', '1', 'καλωδιο', 'τεμ', NULL, '', 'kalwdio, kalodio, cable, wire, 3ch4', '', '1', '2025-10-23 11:16:08', '2025-10-23 11:16:19');
INSERT INTO `materials_catalog` VALUES ('104', 'SuperFlex Κίτρινο Φ20', NULL, '', 'τεμ', '0.42', '', 'kitrino, f20', '', '1', '2025-10-23 14:14:29', '2025-10-23 14:15:02');
INSERT INTO `materials_catalog` VALUES ('105', 'SuperFlex Κίτρινο Φ16', '1', '', 'μ', '0.25', '', 'kitrino, f16', '', '1', '2025-10-23 14:26:58', '2025-10-23 14:26:58');


-- Table: migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `executed_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration` (`migration`),
  KEY `idx_migration` (`migration`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` VALUES ('1', '007_create_payments_table.sql', '2025-10-22 11:31:08');
INSERT INTO `migrations` VALUES ('2', 'add_language_column.sql', '2025-10-22 11:31:08');
INSERT INTO `migrations` VALUES ('3', 'add_project_tasks_system.sql', '2025-10-22 11:31:09');
INSERT INTO `migrations` VALUES ('4', 'add_task_photos.sql', '2025-10-22 11:31:09');
INSERT INTO `migrations` VALUES ('5', 'update_user_roles.sql', '2025-10-22 11:31:09');
INSERT INTO `migrations` VALUES ('6', 'verify_1.3.5_ready.sql', '2025-10-22 11:31:09');
INSERT INTO `migrations` VALUES ('7', 'load_electrical_materials.sql', '2025-10-22 11:38:21');
INSERT INTO `migrations` VALUES ('8', 'load_electrical_materials_part2.sql', '2025-10-22 11:38:21');


-- Table: notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('appointment_reminder','project_deadline','low_stock','payment_due','general') NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `related_type` enum('appointment','project','invoice','material') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `send_email` tinyint(1) DEFAULT 0,
  `send_sms` tinyint(1) DEFAULT 0,
  `email_sent` tinyint(1) DEFAULT 0,
  `sms_sent` tinyint(1) DEFAULT 0,
  `scheduled_for` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `is_read` (`is_read`),
  KEY `scheduled_for` (`scheduled_for`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: payments
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `technician_id` int(11) NOT NULL,
  `week_start` date NOT NULL,
  `week_end` date NOT NULL,
  `total_hours` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_at` datetime DEFAULT NULL,
  `paid_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_technician` (`technician_id`),
  KEY `idx_week` (`week_start`,`week_end`),
  KEY `idx_paid` (`paid_at`),
  KEY `fk_payment_user` (`paid_by`),
  KEY `idx_unpaid` (`technician_id`,`paid_at`),
  CONSTRAINT `fk_payment_technician` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payment_user` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: project_files
DROP TABLE IF EXISTS `project_files`;
CREATE TABLE `project_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_type` enum('image','pdf','document','other') NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `project_files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `project_files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: project_tasks
DROP TABLE IF EXISTS `project_tasks`;
CREATE TABLE `project_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `task_type` enum('single_day','date_range') NOT NULL DEFAULT 'single_day',
  `task_date` date DEFAULT NULL COMMENT 'For single_day tasks',
  `date_from` date DEFAULT NULL COMMENT 'For date_range tasks',
  `date_to` date DEFAULT NULL COMMENT 'For date_range tasks',
  `description` text NOT NULL,
  `notes` text DEFAULT NULL,
  `materials_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `labor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `daily_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_task_type` (`task_type`),
  KEY `idx_task_date` (`task_date`),
  KEY `idx_date_range` (`date_from`,`date_to`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_project_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project_tasks` VALUES ('3', '2', 'single_day', '2025-10-23', NULL, NULL, 'Περασμα καλωδίων', '', '165.00', '64.00', '229.00', '2025-10-23 06:00:15', '2025-10-23 06:00:15');
INSERT INTO `project_tasks` VALUES ('5', '2', 'single_day', '2025-10-17', NULL, NULL, 'Φωτοβολταικά', '', '220.00', '60.00', '280.00', '2025-10-23 06:08:07', '2025-10-23 06:18:51');
INSERT INTO `project_tasks` VALUES ('8', '4', 'single_day', '2025-10-15', NULL, NULL, 'ΡΑΝΤΕΒΟΥ ΜΕ ΚΑΣΤΡΙΝΑΚΗ,ΕΛΕΓΧΟΣ ΕΓΚΑΤΑΣΤΑΣΗΣ ΚΑΙ ΑΠΟΞΥΛΩΣΗ ΚΑΛΩΔΙΩΝ ΕΚΑΝΑ ΥΠΟΔΟΜΗ ΓΙΑ ΓΚΡΕΜΙΣΜΑΤΑ', '', '0.00', '30.00', '30.00', '2025-10-23 12:38:58', '2025-10-23 14:03:54');
INSERT INTO `project_tasks` VALUES ('11', '4', 'single_day', '2025-10-23', NULL, NULL, 'ΣΚΑΨΙΜΑΤΑ,ΣΩΛΗΝΩΣΕΙΣ, ΚΑΛΩΔΙΑ', '', '4.94', '128.00', '132.94', '2025-10-23 14:13:27', '2025-10-23 14:16:01');


-- Table: projects
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `assigned_technician` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `project_address` text DEFAULT NULL,
  `category` enum('electrical','plumbing','maintenance','emergency') NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('new','in_progress','completed','invoiced','cancelled') DEFAULT 'new',
  `estimated_hours` decimal(5,2) DEFAULT NULL,
  `actual_hours` decimal(5,2) DEFAULT NULL,
  `material_cost` decimal(10,2) DEFAULT 0.00,
  `labor_cost` decimal(10,2) DEFAULT 0.00,
  `vat_rate` decimal(5,2) DEFAULT 24.00,
  `total_cost` decimal(10,2) DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `invoiced_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `customer_id` (`customer_id`),
  KEY `assigned_technician` (`assigned_technician`),
  KEY `created_by` (`created_by`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`assigned_technician`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` VALUES ('2', 'fotistika-cine-creta-maris', '1', '2', 'Φωτιστικα CINE CRETA MARIS', 'Περασμα Φωτιστηκών', NULL, 'electrical', 'medium', 'in_progress', NULL, NULL, '0.00', '0.00', '24.00', '631.16', '2025-10-08', NULL, '2025-10-23 06:28:22', '', '1', '2025-10-22 15:21:00', '2025-10-23 14:37:55');
INSERT INTO `projects` VALUES ('4', 'nireas', '1', '2', 'ΝΗΡΕΑΣ', 'ΜΕΤΑΦΟΡΑ ΜΠΑΛΚΟΝΟΠΟΡΤΑΣ ΣΕ ΔΩΜΑΤΙΟ ΙΣΟΓΕΙΟΥ', NULL, 'electrical', 'medium', 'in_progress', NULL, NULL, '0.00', '0.00', '24.00', '0.00', '2025-10-15', NULL, NULL, NULL, '2', '2025-10-23 15:33:44', '2025-10-23 12:33:44');


-- Table: quote_items
DROP TABLE IF EXISTS `quote_items`;
CREATE TABLE `quote_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_id` int(11) NOT NULL,
  `item_type` enum('material','labor','service') NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(8,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `quote_id` (`quote_id`),
  CONSTRAINT `quote_items_ibfk_1` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: quotes
DROP TABLE IF EXISTS `quotes`;
CREATE TABLE `quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) DEFAULT 24.00,
  `vat_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','accepted','rejected','expired') DEFAULT 'draft',
  `valid_until` date DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `sent_date` datetime DEFAULT NULL,
  `accepted_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quote_number` (`quote_number`),
  KEY `customer_id` (`customer_id`),
  KEY `project_id` (`project_id`),
  KEY `created_by` (`created_by`),
  KEY `status` (`status`),
  CONSTRAINT `quotes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quotes_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotes_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: settings
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','decimal','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `display_vat_notes` tinyint(1) DEFAULT 1 COMMENT 'Show VAT notes next to prices',
  `prices_include_vat` tinyint(1) DEFAULT 0 COMMENT 'Whether prices include VAT',
  `default_vat_rate` decimal(5,2) DEFAULT 24.00 COMMENT 'Default VAT rate percentage',
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` VALUES ('1', 'company_name', 'ΣΦΑΚΙΑΝΑΚΗΣ Κ. ΚΑΙ ΣΙΑ Ο.Ε', 'string', NULL, NULL, '2025-10-23 11:20:41', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('2', 'company_address', 'ΝΙΚΟΛΑΟΥ ΚΟΚΚΙΝΟΥ 3', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('3', 'company_phone', '2811 113851', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('4', 'company_email', 'info@ecowatt-energy.gr', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('5', 'company_tax_id', '999082634', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('6', 'company_website', 'https://ecowatt-energy.gr', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('7', 'default_vat_rate', '24', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('8', 'display_vat_notes', '1', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('9', 'prices_include_vat', '0', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('10', 'currency', 'EUR', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('11', 'currency_symbol', '€', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('12', 'date_format', 'd/m/Y', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('13', 'items_per_page', '20', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings` VALUES ('27', 'company_logo', 'uploads/company/logo_1761218367_68fa0f3f5c17e.jpg', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');


-- Table: task_labor
DROP TABLE IF EXISTS `task_labor`;
CREATE TABLE `task_labor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `technician_name` varchar(255) DEFAULT NULL,
  `technician_role` varchar(100) DEFAULT NULL,
  `is_temporary` tinyint(1) DEFAULT 0,
  `hours_worked` decimal(10,2) NOT NULL DEFAULT 0.00,
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `hours` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `work_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paid_by` int(10) unsigned DEFAULT NULL COMMENT 'User ID who marked this as paid',
  `paid_at` datetime DEFAULT NULL COMMENT 'When this labor entry was marked as paid',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_task_labor_paid_at` (`paid_at`),
  KEY `idx_technician_id` (`technician_id`),
  KEY `idx_is_temporary` (`is_temporary`),
  KEY `idx_task_labor_tech_paid` (`technician_id`,`paid_at`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `task_labor` VALUES ('4', '2', '0', '4', 'Σπύρος Παπαδόπουλος', 'assistant', '0', '0.00', '00:00:00', '00:00:00', NULL, '0.00', '5.00', '0.00', '', '0000-00-00', '2025-10-23 05:59:44', '2025-10-23 05:59:44', NULL, NULL);
INSERT INTO `task_labor` VALUES ('5', '3', '0', '4', 'Σπύρος Παπαδόπουλος', 'assistant', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-10-23 06:00:15', '2025-10-23 11:24:04', '1', '2025-10-23 11:24:04');
INSERT INTO `task_labor` VALUES ('9', '4', '0', '4', 'Σπύρος Παπαδόπουλος', 'assistant', '0', '0.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '0.00', '', '0000-00-00', '2025-10-23 06:07:42', '2025-10-23 06:07:42', NULL, NULL);
INSERT INTO `task_labor` VALUES ('17', '5', '0', '8', 'Μηνάς Ζαχαριουδάκης', 'technician', '0', '6.00', '08:00:00', '14:00:00', NULL, '0.00', '10.00', '60.00', '', '0000-00-00', '2025-10-23 06:18:51', '2025-10-23 11:21:28', '1', '2025-10-23 11:21:28');
INSERT INTO `task_labor` VALUES ('20', '6', '0', '4', 'Σπύρος Παπαδόπουλος', 'assistant', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-10-23 11:35:21', '2025-10-23 11:35:21', NULL, NULL);
INSERT INTO `task_labor` VALUES ('21', '6', '0', '7', 'Κόστας Ζάρος', 'technician', '0', '10.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '100.00', '', '0000-00-00', '2025-10-23 11:35:21', '2025-10-23 11:35:21', NULL, NULL);
INSERT INTO `task_labor` VALUES ('22', '7', '0', '8', 'Μηνάς Ζαχαριουδάκης', 'technician', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-23 11:36:09', '2025-10-23 11:36:09', NULL, NULL);
INSERT INTO `task_labor` VALUES ('23', '7', '0', '3', 'Μανώλης Πετράκης', 'technician', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-23 11:36:09', '2025-10-23 11:36:09', NULL, NULL);
INSERT INTO `task_labor` VALUES ('27', '8', '0', '2', 'Νίκος Νικολουδάκης', 'admin', '0', '2.50', '07:00:00', '09:30:00', NULL, '0.00', '12.00', '30.00', '', '0000-00-00', '2025-10-23 14:03:54', '2025-10-23 14:03:54', NULL, NULL);
INSERT INTO `task_labor` VALUES ('28', '10', '0', '6', 'Αγης Καλτσίδης', 'technician', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-23 14:06:31', '2025-10-23 14:06:31', NULL, NULL);
INSERT INTO `task_labor` VALUES ('32', '11', '0', '7', 'Κόστας Ζάρος', 'technician', '0', '4.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '40.00', '', '0000-00-00', '2025-10-23 14:16:01', '2025-10-23 14:16:01', NULL, NULL);
INSERT INTO `task_labor` VALUES ('33', '11', '0', '8', 'Μηνάς Ζαχαριουδάκης', 'technician', '0', '4.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '40.00', '', '0000-00-00', '2025-10-23 14:16:01', '2025-10-23 14:16:01', NULL, NULL);
INSERT INTO `task_labor` VALUES ('34', '11', '0', '2', 'Νίκος Νικολουδάκης', 'admin', '0', '4.00', '08:00:00', '12:00:00', NULL, '0.00', '12.00', '48.00', '', '0000-00-00', '2025-10-23 14:16:01', '2025-10-23 14:16:01', NULL, NULL);


-- Table: task_materials
DROP TABLE IF EXISTS `task_materials`;
CREATE TABLE `task_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `unit` varchar(20) DEFAULT 'τεμ',
  `unit_type` varchar(50) DEFAULT 'other',
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `catalog_material_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `catalog_material_id` (`catalog_material_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `task_materials` VALUES ('4', '2', 'Καλώδιο ισχύος 4x6 ΝΥΥ', 'Καλώδιο ισχύος 4x6 ΝΥΥ', 'μ', 'μ', '5.80', '15.00', '87.00', '64', '2025-10-23 05:59:44', '2025-10-23 05:59:44');
INSERT INTO `task_materials` VALUES ('5', '3', 'Καλώδιο Η05VV-F 3x1.5', 'Καλώδιο Η05VV-F 3x1.5', 'μ', 'μ', '1.10', '150.00', '165.00', '7', '2025-10-23 06:00:15', '2025-10-23 06:00:15');
INSERT INTO `task_materials` VALUES ('8', '4', 'Καλώδιο Η05VV-F 3x1.5', 'Καλώδιο Η05VV-F 3x1.5', 'μ', 'μ', '1.10', '50.00', '55.00', '7', '2025-10-23 06:07:42', '2025-10-23 06:07:42');
INSERT INTO `task_materials` VALUES ('15', '5', 'Καλώδιο Η05VV-F 3x1.5', 'Καλώδιο Η05VV-F 3x1.5', 'μ', 'μ', '1.10', '200.00', '220.00', '7', '2025-10-23 06:18:51', '2025-10-23 06:18:51');
INSERT INTO `task_materials` VALUES ('19', '6', 'Καλώδιο 3Χ4', 'Καλώδιο 3Χ4', 'τεμ', 'τεμ', '20.00', '120.00', '2400.00', '103', '2025-10-23 11:35:21', '2025-10-23 11:35:21');
INSERT INTO `task_materials` VALUES ('20', '6', 'Καλώδιο θέρμανσης δαπέδου 10m', 'Καλώδιο θέρμανσης δαπέδου 10m', 'τεμ', 'τεμ', '18.00', '20.00', '360.00', '67', '2025-10-23 11:35:21', '2025-10-23 11:35:21');
INSERT INTO `task_materials` VALUES ('21', '6', 'Καλώδιο Η05VV-F 3x1.5', 'Καλώδιο Η05VV-F 3x1.5', 'μ', 'μ', '1.10', '200.00', '220.00', '7', '2025-10-23 11:35:21', '2025-10-23 11:35:21');
INSERT INTO `task_materials` VALUES ('22', '6', 'Βάση ρελέ DIN 2P', 'Βάση ρελέ DIN 2P', 'τεμ', 'τεμ', '1.60', '30.00', '48.00', '55', '2025-10-23 11:35:21', '2025-10-23 11:35:21');
INSERT INTO `task_materials` VALUES ('23', '7', 'Καλώδιο Η07RN-F 3x4', 'Καλώδιο Η07RN-F 3x4', 'μ', 'μ', '3.90', '100.00', '390.00', '8', '2025-10-23 11:36:09', '2025-10-23 11:36:09');
INSERT INTO `task_materials` VALUES ('25', '10', 'Καλώδιο Η05VV-F 3x1.5', 'Καλώδιο Η05VV-F 3x1.5', 'μ', 'μ', '1.10', '10.00', '11.00', '7', '2025-10-23 14:06:31', '2025-10-23 14:06:31');
INSERT INTO `task_materials` VALUES ('26', '11', 'SuperFlex Κίτρινο Φ20', 'SuperFlex Κίτρινο Φ20', 'τεμ', 'τεμ', '0.38', '13.00', '4.94', '104', '2025-10-23 14:16:01', '2025-10-23 14:16:01');


-- Table: task_photos
DROP TABLE IF EXISTS `task_photos`;
CREATE TABLE `task_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `photo_type` enum('before','after','during','issue','other') DEFAULT 'other',
  `caption` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `photo_type` (`photo_type`),
  KEY `idx_task_photos_task_type` (`task_id`,`photo_type`),
  KEY `idx_task_photos_created` (`created_at`),
  CONSTRAINT `task_photos_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_photos_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `task_photos` VALUES ('2', '5', 'task_5_1761227432_68fa32a8d6891.jpg', '1000003601.jpg', 'uploads/task_photos/2025/10/task_5_1761227432_68fa32a8d6891.jpg', '563771', 'image/jpeg', 'before', '', '0', '1', '2025-10-23 13:50:33');


-- Table: technicians
DROP TABLE IF EXISTS `technicians`;
CREATE TABLE `technicians` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `role` enum('technician','assistant') NOT NULL DEFAULT 'technician',
  `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_role` (`role`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `technicians` VALUES ('1', 'Γιώργος Παπαδόπουλος', 'technician', '20.00', '6912345678', 'giorgos@example.com', '1', NULL, '2025-10-22 11:31:09', '2025-10-22 11:31:09');
INSERT INTO `technicians` VALUES ('2', 'Νίκος Ιωάννου', 'technician', '18.00', '6923456789', 'nikos@example.com', '1', NULL, '2025-10-22 11:31:09', '2025-10-22 11:31:09');
INSERT INTO `technicians` VALUES ('3', 'Κώστας Δημητρίου', 'assistant', '12.00', '6934567890', 'kostas@example.com', '1', NULL, '2025-10-22 11:31:09', '2025-10-22 11:31:09');
INSERT INTO `technicians` VALUES ('4', 'Μιχάλης Γεωργίου', 'assistant', '10.00', '6945678901', 'michalis@example.com', '1', NULL, '2025-10-22 11:31:09', '2025-10-22 11:31:09');


-- Table: users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `language` varchar(2) DEFAULT 'el',
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','supervisor','technician','assistant') DEFAULT 'technician' COMMENT 'User role: admin (full access), supervisor (projects & materials), technician (own profile), assistant (own profile)',
  `hourly_rate` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `company_name` varchar(100) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `company_phone` varchar(20) DEFAULT NULL,
  `company_email` varchar(100) DEFAULT NULL,
  `company_tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES ('1', 'admin', 'admin@ecowatt.gr', 'el', '$2y$10$mAefXgkJMpvl8cwIDXy.mezMp0GLuG.lhnPfKJc9OAuT7i.PN25n2', 'Θεοδωρος', 'Σφακιανακης', '1234567890', 'admin', '0.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 11:32:36', '2025-10-23 19:04:06');
INSERT INTO `users` VALUES ('2', 'nikos', 'nikos@nikos.gr', 'el', '$2y$10$tlYHBEINxZawJuPJ4pqK2e6eT6LyfJdSGjMK4Ep0WIkAN2P7.jOE6', 'Νίκος', 'Νικολουδάκης', '', 'admin', '12.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 11:50:40', '2025-10-23 15:25:29');
INSERT INTO `users` VALUES ('3', 'manolis', 'manolis@petrakis.gr', 'el', '$2y$10$IG2dsrDn1NItE2wF2/EETuOjzEP1sxDuFi0tQvO5rm0XaqvljtZNO', 'Μανώλης', 'Πετράκης', '', 'technician', '10.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:23:05', '2025-10-23 05:59:12');
INSERT INTO `users` VALUES ('4', 'Spyros', 'spyros@spyros.com', 'el', '$2y$10$qWAKzYItgAfDce2fDo4OweZKO6XN3hr/OCJC0OAaojJS60MKbFkZO', 'Σπύρος', 'Παπαδόπουλος', '', 'assistant', '8.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:23:40', '2025-10-23 05:59:29');
INSERT INTO `users` VALUES ('5', 'dimitris', 'dim@dim.gr', 'el', '$2y$10$MlAVlk8ViqPkCb5n7qcKZ.bAmtPE1fMF5/l.TFj/MPHJMTYnjeXy6', 'Δημήτρης', 'Βιδάκης', '', 'assistant', '8.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:24:12', '2025-10-23 05:55:59');
INSERT INTO `users` VALUES ('6', 'agis', 'agis@agis.com', 'el', '$2y$10$vp0lWw9sozMcr2h5M.R21e7.nzrWCuYJO9dVekXBJm4PUbJ3.qBLy', 'Αγης', 'Καλτσίδης', '', 'technician', '10.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:25:01', '2025-10-23 05:55:45');
INSERT INTO `users` VALUES ('7', 'kostas', 'kostas@kostas.gr', 'el', '$2y$10$UXnUWbKUKnwjAHByzWqKG.GzqeN59MfzirlEg5.rUnCc4v7hcA7.y', 'Κόστας', 'Ζάρος', '', 'technician', '10.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:26:24', '2025-10-23 05:59:06');
INSERT INTO `users` VALUES ('8', 'Minas', 'minas@minas.com', 'el', '$2y$10$XqIcpZ0pDrsmsshHj27MhOARKG54/RvBuOEhUOdxSB8tTh7VwNSO.', 'Μηνάς', 'Ζαχαριουδάκης', '', 'technician', '10.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:28:51', '2025-10-23 05:59:19');

SET FOREIGN_KEY_CHECKS = 1;
