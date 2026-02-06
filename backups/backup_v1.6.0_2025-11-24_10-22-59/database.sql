-- HandyCRM Database Backup
-- Created: 2025-11-24 10:22:59
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` VALUES ('1', 'teab-creta-maris-tera-maris', 'TEAB Creta Maris', 'Tera Maris', '', 'individual', '2810000000', '', '', 'Xersonisos', '', '', '', '', '1', '1', '2025-10-22 11:42:23', '2025-10-29 11:21:50');
INSERT INTO `customers` VALUES ('2', 'asdasda', 'ασδασδα', '', 'ασδασδσα', 'company', '2811113851', '', 'irmaiden@gmail.com', 'Atnoniou Kastrinaki 65', 'Heraklion', '71305', '', '', '1', '0', '2025-10-29 11:05:35', '2025-10-29 11:09:52');
INSERT INTO `customers` VALUES ('3', 'nikos-aleksakis', 'ΝΙΚΟΣ', 'ΑΛΕΞΑΚΗΣ', '', 'individual', '6979443357', '', '', 'ΑΓΙΑ ΣΕΜΝΗ-ΑΡΚΑΛΟΧΩΡΙ', 'ΑΡΚΑΛΟΧΩΡΙ', '', '', '', '2', '1', '2025-10-29 13:51:00', '2025-10-29 13:51:00');
INSERT INTO `customers` VALUES ('4', 'stama-ae-stamatioy', 'ΣΤΑΜΑ ΑΕ', 'ΣΤΑΜΑΤΙΟΥ', '', 'individual', '6944949523', '', '', 'ΕΙΡΗΝΗΣ ΚΑΙ ΦΙΛΙΑΣ', 'ΗΡΑΚΛΕΙΟ', '', '', '', '2', '1', '2025-11-12 13:34:27', '2025-11-12 13:34:27');
INSERT INTO `customers` VALUES ('5', 'blue-bay-valarakis-ae', 'BLUE BAY', 'ΒΑΛΑΡΑΚΗΣ ΑΕ', '', 'individual', '99999999999', '', '', 'ΑΓΙΑ ΠΕΛΑΓΙΑ-ΜΟΝΟΝΑΥΤΗΣ', '', '', '', '', '2', '1', '2025-11-12 17:53:42', '2025-11-12 17:53:42');


-- Table: daily_task_materials
DROP TABLE IF EXISTS `daily_task_materials`;
CREATE TABLE `daily_task_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daily_task_id` int(11) NOT NULL,
  `catalog_material_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_daily_task_id` (`daily_task_id`),
  KEY `idx_catalog_material_id` (`catalog_material_id`),
  KEY `idx_daily_task_material_lookup` (`daily_task_id`,`catalog_material_id`),
  CONSTRAINT `fk_daily_task_materials_catalog` FOREIGN KEY (`catalog_material_id`) REFERENCES `materials_catalog` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_daily_task_materials_task` FOREIGN KEY (`daily_task_id`) REFERENCES `daily_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: daily_task_technicians
DROP TABLE IF EXISTS `daily_task_technicians`;
CREATE TABLE `daily_task_technicians` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `daily_task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hours_worked` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_daily_task_id` (`daily_task_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_task_user` (`daily_task_id`,`user_id`),
  KEY `idx_is_primary` (`is_primary`),
  CONSTRAINT `fk_dtt_daily_task` FOREIGN KEY (`daily_task_id`) REFERENCES `daily_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dtt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `daily_task_technicians` VALUES ('61', '22', '1', '8.00', '1', '2025-11-21 12:01:26');
INSERT INTO `daily_task_technicians` VALUES ('62', '22', '10', '8.00', '0', '2025-11-21 12:01:26');
INSERT INTO `daily_task_technicians` VALUES ('63', '22', '5', '8.00', '0', '2025-11-21 12:01:26');


-- Table: daily_tasks
DROP TABLE IF EXISTS `daily_tasks`;
CREATE TABLE `daily_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_number` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `address` varchar(500) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `task_type` enum('electrical','inspection','fault_repair','other') NOT NULL DEFAULT 'electrical',
  `description` text NOT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `materials` text DEFAULT NULL,
  `is_invoiced` tinyint(1) NOT NULL DEFAULT 0,
  `technician_id` int(11) NOT NULL,
  `additional_technicians` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_technicians`)),
  `notes` text DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `status` enum('completed','in_progress','cancelled') NOT NULL DEFAULT 'completed',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_number` (`task_number`),
  KEY `idx_date` (`date`),
  KEY `idx_customer` (`customer_name`),
  KEY `idx_technician` (`technician_id`),
  KEY `idx_invoiced` (`is_invoiced`),
  KEY `idx_task_type` (`task_type`),
  KEY `idx_status` (`status`),
  KEY `fk_daily_tasks_creator` (`created_by`),
  KEY `idx_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_daily_tasks_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_daily_tasks_technician` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `daily_tasks` VALUES ('22', 'DT-2025-0001', '2025-11-21', 'ΘΕΟΔΩΡΟΣ ΣΦΑΚΙΑΝΑΚΗΣ', 'ΑΝΤΩΝΙΟΥ ΚΑΣΤΡΙΝΑΚΗ 65', '6945139015', 'electrical', 'adsdasd', '5.00', '00:00:00', '00:00:00', NULL, '0', '1', '[\"10\",\"5\"]', '', '[\"uploads\\/daily-tasks\\/task_1763726175_6920535f9ceac.jpg\"]', 'completed', '1', '2025-11-21 11:56:15', '2025-11-21 12:01:26', NULL, NULL);


-- Table: deletion_log
DROP TABLE IF EXISTS `deletion_log`;
CREATE TABLE `deletion_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_type` enum('project','project_task','task_labor','daily_task','maintenance') NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `action` enum('deleted','restored','permanent') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `item_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`item_details`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_item_type` (`item_type`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `deletion_log` VALUES ('1', 'maintenance', '11', 'test', 'restored', '1', 'admin', NULL, '2025-11-19 12:58:27');
INSERT INTO `deletion_log` VALUES ('2', 'maintenance', '11', 'test', 'permanent', '1', 'admin', NULL, '2025-11-19 12:58:37');
INSERT INTO `deletion_log` VALUES ('3', 'maintenance', '12', 'lido soccer-παπαδοπουλος', 'permanent', '1', 'admin', NULL, '2025-11-21 12:58:26');
INSERT INTO `deletion_log` VALUES ('4', 'maintenance', '14', 'dokimastiko', 'permanent', '1', 'admin', NULL, '2025-11-21 12:58:26');
INSERT INTO `deletion_log` VALUES ('5', 'maintenance', '13', 'dokimastiko', 'permanent', '1', 'admin', NULL, '2025-11-21 12:58:26');
INSERT INTO `deletion_log` VALUES ('6', 'maintenance', '17', 'ΘΕΟΔΩΡΟΣ ΣΦΑΚΙΑΝΑΚΗΣ', 'permanent', '1', 'admin', NULL, '2025-11-21 13:25:19');
INSERT INTO `deletion_log` VALUES ('7', 'maintenance', '15', 'ΘΕΟΔΩΡΟΣ ΣΦΑΚΙΑΝΑΚΗΣ', 'permanent', '1', 'admin', NULL, '2025-11-21 13:25:19');


-- Table: email_notifications
DROP TABLE IF EXISTS `email_notifications`;
CREATE TABLE `email_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('maintenance_report','project_report','task_assigned','task_completed','system_notification','test_email') NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `body` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `email_notifications` VALUES ('1', 'test_email', 'theodore.sfakianakis@gmail.com', 'HandyCRM - SMTP Test Email (2025-11-06 13:57:00)', 'Test Email', 'sent', NULL, NULL, '2025-11-06 11:57:02');
INSERT INTO `email_notifications` VALUES ('2', 'test_email', 'theodore.sfakianakis@gmail.com', 'Αναφορά Έργου - ΑΓΙΑ ΣΕΜΝΗ', 'Αγαπητέ/ή,\r\n\r\nΣας αποστέλλουμε την αναφορά του έργου \"ΑΓΙΑ ΣΕΜΝΗ\".\r\n\r\nΜε εκτίμηση,\r\nΗ Ομάδα ECOWATT', 'sent', NULL, NULL, '2025-11-06 12:02:57');
INSERT INTO `email_notifications` VALUES ('3', 'test_email', 'theodore.sfakianakis@gmail.com', 'Αναφορά Έργου - ΑΓΙΑ ΣΕΜΝΗ', 'Αγαπητέ/ή,\r\n\r\nΣας αποστέλλουμε την αναφορά του έργου \"ΑΓΙΑ ΣΕΜΝΗ\".\r\n\r\nΜε εκτίμηση,\r\nΗ Ομάδα ECOWATT', 'sent', NULL, NULL, '2025-11-06 12:07:14');
INSERT INTO `email_notifications` VALUES ('4', 'test_email', 'theodore.sfakianakis@gmail.com', 'Αναφορά Έργου - ΑΓΙΑ ΣΕΜΝΗ', 'Αγαπητέ/ή,\r\n\r\nΣας αποστέλλουμε την αναφορά του έργου \"ΑΓΙΑ ΣΕΜΝΗ\".\r\n\r\nΜε εκτίμηση,\r\nΗ Ομάδα ECOWATT', 'sent', NULL, NULL, '2025-11-06 12:11:25');
INSERT INTO `email_notifications` VALUES ('5', 'test_email', 'theodore.sfakianakis@gmail.com', 'Ανάκτηση Κωδικού - HandyCRM', 'Password Reset', 'sent', NULL, NULL, '2025-11-07 13:00:50');
INSERT INTO `email_notifications` VALUES ('6', 'test_email', 'theodore.sfakianakis@gmail.com', 'Ανάκτηση Κωδικού - HandyCRM', 'Password Reset', 'sent', NULL, NULL, '2025-11-07 13:04:05');
INSERT INTO `email_notifications` VALUES ('7', 'test_email', 'theodore.sfakianakis@gmail.com', 'Ανάκτηση Κωδικού - HandyCRM', 'Password Reset', 'sent', NULL, NULL, '2025-11-07 13:08:03');
INSERT INTO `email_notifications` VALUES ('8', 'test_email', 'theodore.sfakianakis@gmail.com', 'Ανάκτηση Κωδικού - HandyCRM', 'Password Reset', 'sent', NULL, NULL, '2025-11-07 13:17:56');
INSERT INTO `email_notifications` VALUES ('9', 'test_email', 'theodore.sfakianakis@gmail.com', 'Ανάκτηση Κωδικού - HandyCRM', 'Password Reset', 'sent', NULL, NULL, '2025-11-07 13:20:38');
INSERT INTO `email_notifications` VALUES ('10', 'test_email', 'theodore.sfakianakis@gmail.com', 'Ανάκτηση Κωδικού - Ecowatt Energy', 'Password Reset', 'sent', NULL, NULL, '2025-11-07 13:36:22');
INSERT INTO `email_notifications` VALUES ('11', 'test_email', 'evageliasar@gmail.com', 'Αναφορά Έργου - ΑΓΙΑ ΣΕΜΝΗ', 'Αγαπητέ/ή,\r\n\r\nΣας αποστέλλουμε την αναφορά του έργου \"ΑΓΙΑ ΣΕΜΝΗ\".\r\n\r\nΜε εκτίμηση,\r\nΗ Ομάδα ECOWATT', 'sent', NULL, NULL, '2025-11-09 06:37:57');
INSERT INTO `email_notifications` VALUES ('12', 'test_email', 'theodore.sfakianakis@gmail.com', 'Ανάκτηση Κωδικού - Ecowatt Energy', 'Password Reset', 'sent', NULL, NULL, '2025-11-10 12:08:15');
INSERT INTO `email_notifications` VALUES ('13', 'test_email', 'theodore.sfakianakis@gmail.com', 'Αναφορά Έργου - ΞΕΝΟΔΟΧΕΙΟ', 'Αγαπητέ/ή,\r\n\r\nΣας αποστέλλουμε την αναφορά του έργου \"ΞΕΝΟΔΟΧΕΙΟ\".\r\n\r\nΜε εκτίμηση,\r\nΗ Ομάδα ECOWATT', 'sent', NULL, NULL, '2025-11-20 07:56:47');


-- Table: email_templates
DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `body` text NOT NULL,
  `type` enum('maintenance_report','project_report','task_assigned','system_notification') NOT NULL,
  `variables` text DEFAULT NULL COMMENT 'JSON array of available variables',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `material_categories` VALUES ('1', 'Ηλεκτρολογικά', 'Ηλεκτρολογικά υλικά', '2025-10-22 11:38:17', '2025-10-22 11:38:17');
INSERT INTO `material_categories` VALUES ('2', 'Υδραυλικά', 'Υδραυλικά υλικά', '2025-10-22 11:38:17', '2025-10-22 11:38:17');
INSERT INTO `material_categories` VALUES ('3', 'Οικοδομικά', 'Οικοδομικά υλικά', '2025-10-22 11:38:17', '2025-10-22 11:38:17');
INSERT INTO `material_categories` VALUES ('4', 'Άλλα', 'Διάφορα υλικά', '2025-10-22 11:38:17', '2025-10-22 11:38:17');
INSERT INTO `material_categories` VALUES ('5', 'Φωτισμός', '', '2025-10-30 09:55:50', '2025-10-30 09:55:50');
INSERT INTO `material_categories` VALUES ('6', 'Πρίζες & Διακόπτες', '', '2025-10-30 09:56:22', '2025-10-30 09:56:22');
INSERT INTO `material_categories` VALUES ('7', 'Καλώδια', '', '2025-10-30 09:56:41', '2025-10-30 09:56:41');


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
) ENGINE=InnoDB AUTO_INCREMENT=518 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `materials_catalog` VALUES ('1', 'Καλώδιο ΝΥΜ 3x1.5', '1', NULL, 'μ', '1.20', 'Ηλεκτρονική ΑΕ', 'kalwdio, kalodio, cable, wire, nym', NULL, '0', '2025-10-22 11:38:21', '2025-10-30 09:18:00');
INSERT INTO `materials_catalog` VALUES ('2', 'Καλώδιο ΝΥΜ 3x2.5', '1', NULL, 'μ', '1.70', 'Ηλεκτρονική ΑΕ', 'kalwdio, kalodio, cable, wire, nym', NULL, '0', '2025-10-22 11:38:21', '2025-10-30 09:18:00');
INSERT INTO `materials_catalog` VALUES ('5', 'Καλώδιο ΝΥΑ 1x1.5', '1', NULL, 'μ', '0.35', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('6', 'Καλώδιο ΝΥΑ 1x2.5', '1', NULL, 'μ', '0.55', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('7', 'Καλώδιο Η05VV-F 3x1.5', '1', NULL, 'μ', '1.10', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
INSERT INTO `materials_catalog` VALUES ('8', 'Καλώδιο Η07RN-F 3x4', '1', NULL, 'μ', '3.90', 'Ηλεκτρονική ΑΕ', NULL, NULL, '1', '2025-10-22 11:38:21', '2025-10-22 11:38:21');
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
INSERT INTO `materials_catalog` VALUES ('34', 'Ράβδος γείωσης χαλκού 1.5 m', '1', '', 'τεμ', '50.00', 'Ηλεκτρονική ΑΕ', 'rabdos, geiwshs, geioshs, chalkoy', '', '1', '2025-10-22 11:38:21', '2025-10-30 07:53:29');
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
INSERT INTO `materials_catalog` VALUES ('103', 'Καλώδιο NYL H05VV-F 3Χ4', '1', 'καλωδιο', 'μ', NULL, '', 'kalwdio, kalodio, cable, wire, 3ch4', '', '1', '2025-10-23 11:16:08', '2025-10-23 19:15:16');
INSERT INTO `materials_catalog` VALUES ('104', 'SuperFlex Κίτρινο Φ20', '1', '', 'μ', '0.42', '', 'kitrino, f20', '', '1', '2025-10-23 14:14:29', '2025-10-23 21:31:26');
INSERT INTO `materials_catalog` VALUES ('105', 'SuperFlex Κίτρινο Φ16', '1', '', 'μ', '0.25', '', 'kitrino, f16', '', '1', '2025-10-23 14:26:58', '2025-10-23 14:26:58');
INSERT INTO `materials_catalog` VALUES ('106', 'SuperFlex Κίτρινο Φ25', '1', '', 'μ', NULL, '', 'kitrino, f25', '', '1', '2025-10-23 19:11:51', '2025-10-23 19:11:51');
INSERT INTO `materials_catalog` VALUES ('107', 'SuperFlex Κίτρινο Φ32', '1', '', 'μ', NULL, '', 'kitrino, f32', '', '1', '2025-10-23 19:13:08', '2025-10-23 19:13:08');
INSERT INTO `materials_catalog` VALUES ('108', 'κουτι διακοπτου χωνευτο', '1', '', 'τεμ', NULL, '', 'kouti, box, diakoptou, chwneuto, choneuto', '', '1', '2025-10-23 19:17:00', '2025-10-23 19:17:00');
INSERT INTO `materials_catalog` VALUES ('109', 'Κουτί Διακλαδώσεως Χωνευτό Στρογγυλό', NULL, '', 'τεμ', '0.30', '', 'kouti, box, diakladwsews, diakladoseos, chwneuto, choneuto, stroggylo', '', '1', '2025-10-23 19:17:45', '2025-11-13 07:17:28');
INSERT INTO `materials_catalog` VALUES ('110', 'κουτι διακλαδωσεως χωνευτο 7,5*7,5', '1', '', 'τεμ', NULL, '', 'kouti, box, diakladwsews, diakladoseos, chwneuto, choneuto', '', '1', '2025-10-23 19:18:33', '2025-10-23 19:18:33');
INSERT INTO `materials_catalog` VALUES ('111', 'σπιραλ conflex Φ16', '1', '', 'μ', NULL, '', 'spiral, f16', '', '1', '2025-10-23 19:21:28', '2025-10-23 19:21:28');
INSERT INTO `materials_catalog` VALUES ('112', 'σπιραλ conflex Φ20', '1', '', 'μ', NULL, '', 'spiral, f20', '', '1', '2025-10-23 19:22:15', '2025-10-23 19:22:15');
INSERT INTO `materials_catalog` VALUES ('113', 'ετοιμη λασπη κτισιματος', '3', '', 'σακί', NULL, '', 'etoimh, lasph, ktisimatos', '', '1', '2025-10-23 19:39:44', '2025-10-23 19:39:44');
INSERT INTO `materials_catalog` VALUES ('223', 'Σωλήνας ηλ.γραμμών πλαστικός Κίτρινος Ελεύθερα Αλογόνου Superflex Plus Φ20', '1', '', 'μ', '4.32', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, kitrinos, eleythera, alogonou, f20', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('224', 'Σωλήνας ηλ.γραμμών πλαστικός Κίτρινος Ελεύθερα Αλογόνου Superflex Plus Φ25', '1', '', 'μ', '5.29', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, kitrinos, eleythera, alogonou, f25', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('226', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ16', '1', '', 'μ', '4.86', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f16', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('227', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ20', '1', '', 'μ', '5.94', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f20', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('228', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ25', '1', '', 'μ', '7.02', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f25', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('229', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ32', '1', '', 'μ', '9.29', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f32', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('230', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ40', '1', '', 'μ', '10.58', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f40', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('231', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ50', '1', '', 'μ', '11.88', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f50', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('232', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ63', '1', '', 'μ', '13.50', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f63', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('233', 'Πλαστικός σωλήνας PE GEOSUB Φ32', '1', '', 'μ', '6.05', '', 'plastikos, swlhnas, solhnas, pipe, tube, solinas, f32', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('234', 'Πλαστικός σωλήνας PE GEOSUB Φ50', '1', '', 'μ', '7.34', '', 'plastikos, swlhnas, solhnas, pipe, tube, solinas, f50', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('235', 'Πλαστικός σωλήνας PE GEOSUB Φ63', '1', '', 'μ', '8.21', '', 'plastikos, swlhnas, solhnas, pipe, tube, solinas, f63', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('236', 'Πλαστικός σωλήνας PE GEOSUB Φ75', '1', '', 'μ', '9.40', '', 'plastikos, swlhnas, solhnas, pipe, tube, solinas, f75', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('237', 'Πλαστικός σωλήνας PE GEOSUB Φ90', '1', '', 'μ', '10.69', '', 'plastikos, swlhnas, solhnas, pipe, tube, solinas, f90', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('238', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Duroflex PVC Φ13.5', '1', '', 'μ', '3.46', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f13.5', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('239', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Duroflex PVC Φ16', '1', '', 'μ', '4.10', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f16', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('240', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Duroflex PVC Φ23', '1', '', 'μ', '4.86', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f23', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('241', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Duroflex PVC Φ29', '1', '', 'μ', '7.99', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f29', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('242', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Duroflex PVC Φ32', '1', '', 'μ', '9.29', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f32', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('243', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Duroflex PVC Φ40', '1', '', 'μ', '10.58', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f40', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('244', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Duroflex PVC Φ50', '1', '', 'μ', '11.88', '', 'swlhnas, solhnas, pipe, tube, solinas, hl.grammwn, hl.grammon, plastikos, euthys, barews, bareos, typou, end.typou, f50', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('245', 'Κυτίο διακλάδωσης', '1', '', '', '10.80', '', 'kytio, diakladwshs, diakladoshs', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('246', 'Εσχάρα διέλευσης καλωδιώσεων 100x60', '1', '', '', '19.00', '', 'eschara, dieleushs, kalwdiwsewn, kalodioseon', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('247', 'Εσχάρα διέλευσης καλωδιώσεων 200x60', '1', '', '', '25.00', '', 'eschara, dieleushs, kalwdiwsewn, kalodioseon', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('248', 'Εσχάρα διέλευσης καλωδιώσεων 300x60', '1', '', '', '31.00', '', 'eschara, dieleushs, kalwdiwsewn, kalodioseon', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('249', 'Εσχάρα διέλευσης καλωδιώσεων 400x60', '1', '', '', '35.00', '', 'eschara, dieleushs, kalwdiwsewn, kalodioseon', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('250', 'Καπάκι Εσχάρας 100x60', '1', '', '', '6.40', '', 'kapaki, escharas', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('251', 'Καπάκι Εσχάρας 200x60', '1', '', '', '9.00', '', 'kapaki, escharas', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('252', 'Καπάκι Εσχάρας 300x60', '1', '', '', '14.00', '', 'kapaki, escharas', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('253', 'Καπάκι Εσχάρας 400x60', '1', '', '', '17.00', '', 'kapaki, escharas', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('254', 'Ειδικό Εξάρτημα Εσχάρας 100x60', '1', '', '', '17.28', '', 'eidiko, eksarthma, escharas', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('255', 'Ειδικό Εξάρτημα Εσχάρας 200x60', '1', '', '', '23.33', '', 'eidiko, eksarthma, escharas', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('256', 'Ειδικό Εξάρτημα Εσχάρας 300x60', '1', '', '', '26.68', '', 'eidiko, eksarthma, escharas', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('257', 'Ειδικό Εξάρτημα Εσχάρας 400x60', '1', '', '', '299.81', '', 'eidiko, eksarthma, escharas', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('258', 'Κανάλι πλαστικό ενδ. Τύπου Legrand DLP 105x35', '1', '', '', '19.44', '', 'kanali, plastiko, end., typou', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('259', 'Κανάλι πλαστικό ενδ. Τύπου Legrand DLP 150x50', '1', '', '', '21.38', '', 'kanali, plastiko, end., typou', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('260', 'Διαχωριστικό στοιχείο ενδ.τύπου Legrand για κανάλια 150x50', '1', '', '', '7.56', '', 'diachwristiko, diachoristiko, stoicheio, end.typou, gia, kanalia', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('261', 'Διαχωριστικό στοιχείο ενδ.τύπου Legrand για κανάλια 105x35', '1', '', '', '7.56', '', 'diachwristiko, diachoristiko, stoicheio, end.typou, gia, kanalia', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('262', 'Ειδικά εξαρτήματα ενδ. τύπου Legrand πλαστικού καναλιού 105x35 (4 τύπων)', '1', '', '', '12.96', '', 'eidika, eksarthmata, end., typou, plastikoy, kanalioy, typwn), typon)', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('263', 'Ειδικά εξαρτήματα ενδ. τύπου Legrand πλαστικού καναλιού 150x50 (4 τύπων)', '1', '', '', '12.96', '', 'eidika, eksarthmata, end., typou, plastikoy, kanalioy, typwn), typon)', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('264', 'Καλώδιο ΝΥΜ 2x1.5mm2', '1', '', 'μ', '2.70', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('265', 'Καλώδιο ΝΥΜ 2x2.5mm2', '1', '', 'μ', '3.13', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('266', 'Καλώδιο ΝΥΜ 3x1.5mm2', '1', '', 'μ', '3.13', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('267', 'Καλώδιο ΝΥΜ 3x2.5mm2', '1', '', 'μ', '4.21', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('268', 'Καλώδιο ΝΥΜ 3x4mm2', '1', '', 'μ', '5.40', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('269', 'Καλώδιο ΝΥΜ 3x6mm2', '1', '', 'μ', '7.56', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('270', 'Καλώδιο ΝΥΜ 3x10mm2', '1', '', 'μ', '10.26', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('271', 'Καλώδιο ΝΥΜ 3x16mm2', '1', '', 'μ', '11.99', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('272', 'Καλώδιο ΝΥΜ 4x1.5mm2', '1', '', 'μ', '3.67', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('273', 'Καλώδιο ΝΥΜ 4x2.5mm2', '1', '', 'μ', '4.75', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('274', 'Καλώδιο ΝΥΜ 5x1.5mm2', '1', '', 'μ', '4.10', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('275', 'Καλώδιο ΝΥΜ 5x2.5mm2', '1', '', 'μ', '7.56', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('276', 'Καλώδιο ΝΥΜ 5x4mm2', '1', '', 'μ', '14.04', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('277', 'Καλώδιο ΝΥΜ 5x6mm2', '1', '', 'μ', '18.36', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('278', 'Καλώδιο ΝΥΜ 5x10mm2', '1', '', 'μ', '21.38', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('279', 'Καλώδιο ΝΥΜ 5x16mm2', '1', '', 'μ', '26.78', '', 'kalwdio, kalodio, cable, wire, nym', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('280', 'Καλώδιο ΝΥY 1x16mm2', '1', '', 'μ', '6.48', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('281', 'Καλώδιο ΝΥY 1x25mm2', '1', '', 'μ', '8.64', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('282', 'Καλώδιο ΝΥY 1x35mm2', '1', '', 'μ', '11.88', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('283', 'Καλώδιο ΝΥY 1x50mm2', '1', '', 'μ', '14.04', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('284', 'Καλώδιο ΝΥY 1x70mm2', '1', '', 'μ', '17.28', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('285', 'Καλώδιο ΝΥY 1x95mm2', '1', '', 'μ', '19.44', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('286', 'Καλώδιο ΝΥY 1x120mm2', '1', '', 'μ', '28.08', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('287', 'Καλώδιο ΝΥY 1x150mm2', '1', '', 'μ', '32.51', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('288', 'Καλώδιο ΝΥY 1x185mm2', '1', '', 'μ', '36.94', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('289', 'Καλώδιο ΝΥY 1x240mm2', '1', '', 'μ', '45.47', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('290', 'Καλώδιο ΝΥY 3x1.5mm2', '1', '', 'μ', '3.13', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('291', 'Καλώδιο ΝΥY 3x2.5mm2', '1', '', 'μ', '4.21', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('292', 'Καλώδιο ΝΥY 3x4mm2', '1', '', 'μ', '5.40', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('293', 'Καλώδιο ΝΥY 3x6mm2', '1', '', 'μ', '7.56', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('294', 'Καλώδιο ΝΥY 3x10mm2', '1', '', 'μ', '10.26', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('295', 'Καλώδιο ΝΥY 3x16mm2', '1', '', 'μ', '11.99', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('296', 'Καλώδιο ΝΥY 3x25mm2', '1', '', 'μ', '23.76', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('297', 'Καλώδιο ΝΥY 3x25+16mm2', '1', '', 'μ', '30.24', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('298', 'Καλώδιο ΝΥY 3x35+16mm2', '1', '', 'μ', '38.88', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('299', 'Καλώδιο ΝΥY 3x50+25mm2', '1', '', 'μ', '47.52', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('300', 'Καλώδιο ΝΥY 3x70+35mm2', '1', '', 'μ', '64.80', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('301', 'Καλώδιο ΝΥY 3x95+50mm2', '1', '', 'μ', '77.76', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('302', 'Καλώδιο ΝΥY 3x120+70mm2', '1', '', 'μ', '103.68', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('303', 'Καλώδιο ΝΥY 3x150+70mm2', '1', '', 'μ', '138.24', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('304', 'Καλώδιο ΝΥY 3 Χ 185 + 95 mm3', '1', '', 'μ', '151.20', '', 'kalwdio, kalodio, cable, wire, nyy, ch', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('305', 'Καλώδιο ΝΥY 3 Χ 240 + 120  mm4', '1', '', 'μ', '199.80', '', 'kalwdio, kalodio, cable, wire, nyy, ch', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('306', 'Καλώδιο ΝΥY 4x50+25mm2', '1', '', 'μ', '54.00', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('307', 'Καλώδιο ΝΥY 4x185+95mm2', '1', '', 'μ', '171.72', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('308', 'Καλώδιο ΝΥY 4x240+120mm2', '1', '', 'μ', '245.27', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('309', 'Καλώδιο ΝΥY 5x1.5mm2', '1', '', 'μ', '4.10', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('310', 'Καλώδιο ΝΥY 5x2.5mm2', '1', '', 'μ', '7.02', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('311', 'Καλώδιο ΝΥY 5x4mm2', '1', '', 'μ', '13.50', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('312', 'Καλώδιο ΝΥY 5x6mm2', '1', '', 'μ', '17.28', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('313', 'Καλώδιο ΝΥY 5x10mm2', '1', '', 'μ', '20.30', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('314', 'Καλώδιο ΝΥY 5x16mm2', '1', '', 'μ', '26.35', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('315', 'Καλώδιο ΝΥY 5x25mm2', '1', '', 'μ', '29.59', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('316', 'Καλώδιο ΝΥY 5x35mm2', '1', '', 'μ', '37.37', '', 'kalwdio, kalodio, cable, wire, nyy', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('317', 'Καλώδιο ΝΗΧΜΗ 1x16mm2', '1', '', 'μ', '4.97', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('318', 'Καλώδιο ΝΗΧΜΗ 1x25mm2', '1', '', 'μ', '7.02', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('319', 'Καλώδιο ΝΗΧΜΗ 1x35mm2', '1', '', 'μ', '9.18', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('320', 'Καλώδιο ΝΗΧΜΗ 1x50mm2', '1', '', 'μ', '12.42', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('321', 'Καλώδιο ΝΗΧΜΗ 1x70mm2', '1', '', 'μ', '16.74', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('322', 'Καλώδιο ΝΗΧΜΗ 1x95mm2', '1', '', 'μ', '19.98', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('323', 'Καλώδιο ΝΗΧΜΗ 1x120mm2', '1', '', 'μ', '27.97', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('324', 'Καλώδιο ΝΗΧΜΗ 1x150mm2', '1', '', 'μ', '32.94', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('325', 'Καλώδιο ΝΗΧΜΗ 1x185mm2', '1', '', 'μ', '37.26', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('326', 'Καλώδιο ΝΗΧΜΗ 1x240mm2', '1', '', 'μ', '47.52', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('327', 'Καλώδιο ΝΗΧΜΗ 3x1.5mm2', '1', '', 'μ', '3.13', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('328', 'Καλώδιο ΝΗΧΜΗ 3x2.5mm2', '1', '', 'μ', '3.67', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('329', 'Καλώδιο ΝΗΧΜΗ 3x4mm2', '1', '', 'μ', '5.18', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('330', 'Καλώδιο ΝΗΧΜΗ 3x6mm2', '1', '', 'μ', '6.70', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('331', 'Καλώδιο ΝΗΧΜΗ 3x10mm2', '1', '', 'μ', '11.02', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('332', 'Καλώδιο ΝΗΧΜΗ 3x16mm2', '1', '', 'μ', '16.20', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('333', 'Καλώδιο ΝΗΧΜΗ 3x25mm2', '1', '', 'μ', '23.22', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('334', 'Καλώδιο ΝΗΧΜΗ 3x25+16mm2', '1', '', 'μ', '28.19', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('335', 'Καλώδιο ΝΗΧΜΗ 3x35+16mm2', '1', '', 'μ', '38.45', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('336', 'Καλώδιο ΝΗΧΜΗ 3x50+25mm2', '1', '', 'μ', '44.28', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('337', 'Καλώδιο ΝΗΧΜΗ 3x70+35mm2', '1', '', 'μ', '59.40', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('338', 'Καλώδιο ΝΗΧΜΗ 3x95+50mm2', '1', '', 'μ', '72.36', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('339', 'Καλώδιο ΝΗΧΜΗ 3x120+70mm2', '1', '', 'μ', '100.66', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('340', 'Καλώδιο ΝΗΧΜΗ 3x150+70mm2', '1', '', 'μ', '115.56', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('341', 'Καλώδιο ΝΗΧΜΗ 3 Χ 185 + 95 mm3', '1', '', 'μ', '131.76', '', 'kalwdio, kalodio, cable, wire, nhchmh, ch', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('342', 'Καλώδιο ΝΗΧΜΗ 3 Χ 240 + 120  mm4', '1', '', 'μ', '164.05', '', 'kalwdio, kalodio, cable, wire, nhchmh, ch', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('343', 'Καλώδιο ΝΗΧΜΗ 4x50+25mm2', '1', '', 'μ', '56.70', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('344', 'Καλώδιο ΝΗΧΜΗ 4x185+95mm2', '1', '', 'μ', '168.48', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('345', 'Καλώδιο ΝΗΧΜΗ 4x240+120mm2', '1', '', 'μ', '209.41', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('346', 'Καλώδιο ΝΗΧΜΗ 5x1.5mm2', '1', '', 'μ', '4.32', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('347', 'Καλώδιο ΝΗΧΜΗ 5x2.5mm2', '1', '', 'μ', '7.56', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('348', 'Καλώδιο ΝΗΧΜΗ 5x4mm2', '1', '', 'μ', '12.10', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('349', 'Καλώδιο ΝΗΧΜΗ 5x6mm2', '1', '', 'μ', '18.36', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('350', 'Καλώδιο ΝΗΧΜΗ 5x10mm2', '1', '', 'μ', '20.52', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('351', 'Καλώδιο ΝΗΧΜΗ 5x16mm2', '1', '', 'μ', '24.84', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:50', '2025-10-24 06:24:50');
INSERT INTO `materials_catalog` VALUES ('352', 'Καλώδιο ΝΗΧΜΗ 5x25mm2', '1', '', 'μ', '29.16', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('353', 'Καλώδιο ΝΗΧΜΗ 5x35mm2', '1', '', 'μ', '36.72', '', 'kalwdio, kalodio, cable, wire, nhchmh', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('354', 'Χάλκινος μονόκλωνος αγωγός 50mm2 για σύνδεση δικτύου γειώσεως', '1', '', '', '10.80', '', 'chalkinos, monoklwnos, monoklonos, agwgos, agogos, gia, syndesh, diktyou, geiwsews, geioseos', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('355', 'Περιμετρική λάμα γείωσης χάλκινη 40Χ4 για σύνδεση δικτύου γειώσεως', '1', '', '', '39.96', '', 'perimetrikh, lama, geiwshs, geioshs, chalkinh, 40ch4, gia, syndesh, diktyou, geiwsews, geioseos', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('356', 'Καλώδιο Ν2ΧSY 1X50 mm2', '1', '', 'μ', '21.60', '', 'kalwdio, kalodio, cable, wire, n2chsy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('357', 'Καλώδιο Ν2ΧSY 1X70 mm2', '1', '', 'μ', '23.76', '', 'kalwdio, kalodio, cable, wire, n2chsy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('358', 'Καλώδιο Ν2ΧSY 1X95 mm2', '1', '', 'μ', '25.92', '', 'kalwdio, kalodio, cable, wire, n2chsy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('359', 'Καλώδιο Ν2ΧSY 1X120 mm2', '1', '', 'μ', '30.24', '', 'kalwdio, kalodio, cable, wire, n2chsy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('360', 'Καλώδιο Ν2ΧSY 1X150 mm2', '1', '', 'μ', '34.56', '', 'kalwdio, kalodio, cable, wire, n2chsy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('361', 'Καλώδιο Liycy 2x1mm2', '1', '', 'μ', '2.38', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('362', 'Καλώδιο Liycy 2x1.5mm2', '1', '', 'μ', '2.59', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('363', 'Καλώδιο Liycy 4x1.5mm2', '1', '', 'μ', '3.24', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('364', 'Καλώδιο Liycy 2x2.5mm2', '1', '', 'μ', '3.13', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('365', 'Καλώδιο Liycy 4x2.5mm2', '1', '', 'μ', '5.18', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('366', 'Καλωδιο πυραντοχο πυρανιχνευσης FTE4OHM1', '1', '', 'μ', '4.86', '', 'kalwdio, kalodio, cable, wire, pyrantocho, pyranichneushs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('367', 'Καλώδιο UTP6/4”', '1', '', 'μ', '2.70', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('368', 'Καλώδιο PET CAT 6 Ανθυγρό για κάμερες', '1', '', 'μ', '3.24', '', 'kalwdio, kalodio, cable, wire, anthygro, gia, kameres', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('369', 'Καλώδιο οπτικής ίνας πολύτροπης 4 ζευγών', '1', '', 'μ', '6.05', '', 'kalwdio, kalodio, cable, wire, optikhs, inas, polytrophs, zeugwn, zeugon', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('370', 'Καλώδιο οπτικής ίνας πολύτροπης 8 ζευγών', '1', '', 'μ', '6.05', '', 'kalwdio, kalodio, cable, wire, optikhs, inas, polytrophs, zeugwn, zeugon', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('371', 'Καλώδιο TV ομοαξονικό 75Ohm', '1', '', 'μ', '2.70', '', 'kalwdio, kalodio, cable, wire, omoaksoniko', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('372', 'Καλώδιο RG59', '1', '', 'μ', '2.70', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('373', 'Καλώδιο RG11', '1', '', 'μ', '5.40', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('374', 'Χάλκινος αγωγός μονόκλωνος ή πολύκλωνος διατομής 25mm2', '1', '', '', '6.48', '', 'chalkinos, agwgos, agogos, monoklwnos, monoklonos, polyklwnos, polyklonos, diatomhs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('375', 'Χάλκινος αγωγός μονόκλωνος ή πολύκλωνος διατομής 35mm2', '1', '', '', '8.64', '', 'chalkinos, agwgos, agogos, monoklwnos, monoklonos, polyklwnos, polyklonos, diatomhs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('376', 'Χάλκινος αγωγός μονόκλωνος ή πολύκλωνος διατομής 50mm2', '1', '', '', '10.80', '', 'chalkinos, agwgos, agogos, monoklwnos, monoklonos, polyklwnos, polyklonos, diatomhs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('377', 'Χάλκινος αγωγός μονόκλωνος ή πολύκλωνος διατομής 75mm2', '1', '', '', '13.39', '', 'chalkinos, agwgos, agogos, monoklwnos, monoklonos, polyklwnos, polyklonos, diatomhs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('378', 'Χάλκινος αγωγός μονόκλωνος ή πολύκλωνος διατομής 90mm2', '1', '', '', '19.76', '', 'chalkinos, agwgos, agogos, monoklwnos, monoklonos, polyklwnos, polyklonos, diatomhs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('379', 'Χάλκινος αγωγός μονόκλωνος ή πολύκλωνος διατομής 120mm2', '1', '', '', '26.68', '', 'chalkinos, agwgos, agogos, monoklwnos, monoklonos, polyklwnos, polyklonos, diatomhs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('380', 'Καλώδιο BUS 2X2X0', '1', '8mm', '', '3.56', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('381', 'Καλώδιο HRNF καουτσούκ 2x1', '1', '5mm', '', '3.78', '', 'kalwdio, kalodio, cable, wire, kaoutsoyk', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('382', 'Καλώδιο HRNF καουτσούκ 3x1', '1', '5mm', '', '5.18', '', 'kalwdio, kalodio, cable, wire, kaoutsoyk', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('383', 'Καλώδιο HRNF καουτσούκ 4x1', '1', '5mm', '', '5.94', '', 'kalwdio, kalodio, cable, wire, kaoutsoyk', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('384', 'Καλώδιο HRNF καουτσούκ 5x1', '1', '5mm', '', '6.48', '', 'kalwdio, kalodio, cable, wire, kaoutsoyk', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('385', 'Καλώδιο PYROFILL FIRE SAFETY', '1', '', '', '4.43', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('386', 'Καλώδιο RG6 CU DATACOM TV', '1', '', '', '6.48', '', 'kalwdio, kalodio, cable, wire', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('387', 'Καλώδιο σιλικόνης 2x1mm2', '1', '', '', '3.24', '', 'kalwdio, kalodio, cable, wire, silikonhs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('388', 'Καλώδιο σιλικόνης 2x0.75mm2', '1', '', '', '3.24', '', 'kalwdio, kalodio, cable, wire, silikonhs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('389', 'Καλώδιο ήχου 2x1.5mm2', '1', '', '', '4.75', '', 'kalwdio, kalodio, cable, wire, hchou', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('390', 'Εγκατάσταση εσωτερικών ηλεκτρολογικών εγκαταστάσεων τυπικού  δωματίου δύο χώρων συμπεριλαμβανομένου του πίνακα δωματίου', '1', '', '', NULL, '', 'egkatastash, eswterikwn, esoterikon, hlektrologikwn, hlektrologikon, egkatastasewn, egkatastaseon, typikoy, dwmatiou, domatiou, dyo, chwrwn, choron, symperilambanomenou, tou, pinaka', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('391', 'Εγκατάσταση εσωτερικών ηλεκτρολογικών εγκαταστάσεων τυπικού δωματίου ενός χώρου  συμπεριλαμβανομένου του πίνακα δωματίου', '1', '', '', '2.21', '', 'egkatastash, eswterikwn, esoterikon, hlektrologikwn, hlektrologikon, egkatastasewn, egkatastaseon, typikoy, dwmatiou, domatiou, enos, chwrou, chorou, symperilambanomenou, tou, pinaka', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('392', 'Τοποθέτηση κάθε είδους πυρανιχνευτού  μπουτόν συναγερμού  αισθητηρίου ασφαλείας', '1', '', '', '16.20', '', 'topothethsh, kathe, eidous, pyranichneutoy, mpouton, synagermoy, aisthhthriou, asfaleias', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('393', 'Τοποθέτηση κάθε είδους λήψεων (τηλ TV ρευματοδότες 220V)', '1', '', '', NULL, '', 'topothethsh, kathe, eidous, lhpsewn, lhpseon, (thl, reumatodotes', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('394', 'Τοποθέτηση φωτιστικών σωμάτων', '1', '', '', NULL, '', 'topothethsh, fwtistikwn, fotistikon, swmatwn, somaton', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('395', 'Τοποθέτηση φωτιστικών σωμάτων πισίνας-στεγανών', '1', '', '', '32.40', '', 'topothethsh, fwtistikwn, fotistikon, swmatwn, somaton, pisinas, steganwn, steganon', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('396', 'Τοποθέτηση εξωτερικών φωτιστικών κήπου- χωνευτά', '1', '', '', '32.40', '', 'topothethsh, ekswterikwn, eksoterikon, fwtistikwn, fotistikon, khpou, chwneuta, choneuta', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('397', 'Τοποθέτηση εξωτερικών φωτιστικών τοίχου- επίτοιχα', '1', '', '', '16.20', '', 'topothethsh, ekswterikwn, eksoterikon, fwtistikwn, fotistikon, toichou, epitoicha', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('398', 'Τοποθέτηση ledοταινίας', '1', '', '', NULL, '', 'topothethsh, ledotainias, tape, tenia, tainia', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('399', 'Τοποθέτηση γύψινων φωτιστικών', '1', '', '', '21.60', '', 'topothethsh, gypsinwn, gypsinon, fwtistikwn, fotistikon', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('400', 'Εργασία σύνδεσης θύρας patch panel', '1', '', '', '0.00', '', 'ergasia, syndeshs, thyras', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('401', 'Τοποθέτηση – σύνδεση πίνακα έως 10 γραμμές προμήθειας ιδιοκτήτη χωνευτού ή εξωτερικού κοινού ή στεγανού', '1', '', '', '0.00', '', 'topothethsh, syndesh, pinaka, ews, eos, grammes, promhtheias, idiokthth, chwneutoy, choneutoy, ekswterikoy, eksoterikoy, koinoy, steganoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('402', 'Τοποθέτηση – σύνδεση πίνακα έως 20 γραμμές προμήθειας ιδιοκτήτη χωνευτού ή εξωτερικού κοινού ή στεγανού', '1', '', '', '0.00', '', 'topothethsh, syndesh, pinaka, ews, eos, grammes, promhtheias, idiokthth, chwneutoy, choneutoy, ekswterikoy, eksoterikoy, koinoy, steganoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('403', 'Τοποθέτηση – σύνδεση πίνακα έως 30 γραμμές προμήθειας ιδιοκτήτη χωνευτού ή εξωτερικού κοινού ή στεγανού', '1', '', '', '0.00', '', 'topothethsh, syndesh, pinaka, ews, eos, grammes, promhtheias, idiokthth, chwneutoy, choneutoy, ekswterikoy, eksoterikoy, koinoy, steganoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('404', 'Τοποθέτηση – σύνδεση πίνακα έως 40 γραμμές προμήθειας ιδιοκτήτη χωνευτού ή εξωτερικού κοινού ή στεγανού', '1', '', '', '0.00', '', 'topothethsh, syndesh, pinaka, ews, eos, grammes, promhtheias, idiokthth, chwneutoy, choneutoy, ekswterikoy, eksoterikoy, koinoy, steganoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('405', 'Τοποθέτηση – σύνδεση πίνακα έως 50 γραμμές προμήθειας ιδιοκτήτη χωνευτού ή εξωτερικού κοινού ή στεγανού', '1', '', '', '0.00', '', 'topothethsh, syndesh, pinaka, ews, eos, grammes, promhtheias, idiokthth, chwneutoy, choneutoy, ekswterikoy, eksoterikoy, koinoy, steganoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('406', 'Τοποθέτηση – σύνδεση πίνακα έως 60 γραμμές προμήθειας ιδιοκτήτη χωνευτού ή εξωτερικού κοινού ή στεγανού', '1', '', '', '0.00', '', 'topothethsh, syndesh, pinaka, ews, eos, grammes, promhtheias, idiokthth, chwneutoy, choneutoy, ekswterikoy, eksoterikoy, koinoy, steganoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('407', 'Τοποθέτηση – σύνδεση πίνακα έως 70 γραμμές προμήθειας ιδιοκτήτη χωνευτού ή εξωτερικού κοινού ή στεγανού', '1', '', '', '0.00', '', 'topothethsh, syndesh, pinaka, ews, eos, grammes, promhtheias, idiokthth, chwneutoy, choneutoy, ekswterikoy, eksoterikoy, koinoy, steganoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('408', 'Τοποθέτηση – σύνδεση πίνακα έως 80 γραμμές προμήθειας ιδιοκτήτη χωνευτού ή εξωτερικού κοινού ή στεγανού', '1', '', '', '0.00', '', 'topothethsh, syndesh, pinaka, ews, eos, grammes, promhtheias, idiokthth, chwneutoy, choneutoy, ekswterikoy, eksoterikoy, koinoy, steganoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('409', 'Τοποθέτηση απλού διακόπτη', '1', '', '', NULL, '', 'topothethsh, aploy, diakopth', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('410', 'Τοποθέτηση διακόπτη αλέ-ρετούρ', '1', '', '', NULL, '', 'topothethsh, diakopth, ale, retoyr', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('411', 'Προμήθεια και τοποθέτηση ακροκιβωτίου σύνδεσης καλωδίου Μ/Τ', '1', '', '', '162.00', '', 'promhtheia, kai, topothethsh, akrokibwtiou, akrokibotiou, syndeshs, kalwdiou, kalodiou, m/t', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('412', 'Σύνδεση Γενικού Πίνακα Χαμηλής Τάσης', '1', '', '', '0.00', '', 'syndesh, genikoy, pinaka, chamhlhs, tashs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('413', 'Σύνδεση Πεδίων Μέσης Τάσης', '1', '', '', '0.00', '', 'syndesh, pediwn, pedion, meshs, tashs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('414', 'Σύνδεση Γεννήτριας', '1', '', '', '0.00', '', 'syndesh, gennhtrias', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('415', 'Σύνδεση μετασχηματιστών - τροφοδοτικών', '1', '', '', '21.60', '', 'syndesh, metaschhmatistwn, metaschhmatiston, trofodotikwn, trofodotikon', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('416', 'Σύνδεση εξαερισμού', '1', '', '', '16.20', '', 'syndesh, eksaerismoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('417', 'Σύνδεση εξοπλισμού A/C - BOX', '1', '', '', '16.20', '', 'syndesh, eksoplismoy', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('418', 'Ηλεκτρική Σύνδεση εξωτερικής Μονάδας VRV έως 20 HP', '1', '', '', '27.00', '', 'hlektrikh, syndesh, ekswterikhs, eksoterikhs, monadas, ews, eos', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('419', 'Συνδεση Πιεστικού Υδρευσης', '1', '', '', '21.60', '', 'syndesh, piestikoy, ydreushs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('420', 'Συνδεση Πιεστικού Πυρόσβεσης', '1', '', '', '64.80', '', 'syndesh, piestikoy, pyrosbeshs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('421', 'Συνδεση Αντίστροφης Όσμωσης', '1', '', '', '64.80', '', 'syndesh, antistrofhs, osmwshs, osmoshs', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('422', 'Σύνδεση ψυκτικού θαλάμου', '1', '', '', '27.00', '', 'syndesh, psyktikoy, thalamou', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('423', 'Σύνδεση κυκλοφορητή έως ΝΥΜ5x2', '1', '', '', '16.20', '', 'syndesh, kykloforhth, ews, eos, nym5x2', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('424', 'Συνδέσεις αισθητηρίων και επαφών  BMS', '1', '', '', NULL, '', 'syndeseis, aisthhthriwn, aisthhthrion, kai, epafwn, epafon', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('425', 'Συνδεση Κινητηρα εως 12ΗΡ και συσκευών κουζίνας έως 15KW', '1', '', '', '16.20', '', 'syndesh, kinhthra, ews, eos, 12hr, kai, syskeuwn, syskeuon, kouzinas', NULL, '1', '2025-10-24 06:24:51', '2025-10-24 06:24:51');
INSERT INTO `materials_catalog` VALUES ('426', 'καλωδιο συναγερμου 8 αγωγων', '1', '', 'μ', '2.00', '', 'kalwdio, kalodio, cable, wire, synagermou, agwgwn, agogon', '', '1', '2025-10-29 12:51:50', '2025-10-29 12:51:50');
INSERT INTO `materials_catalog` VALUES ('427', 'Fix Ring Φ20 διπλο', NULL, '', 'τεμ', NULL, '', 'f20, diplo', '', '1', '2025-10-29 12:52:29', '2025-11-13 07:16:51');
INSERT INTO `materials_catalog` VALUES ('428', 'καλωδιο ΝΥΛ 2*0,75', '1', '', 'μ', '0.50', '', 'kalwdio, kalodio, cable, wire, nyl', '', '1', '2025-10-29 12:54:37', '2025-10-29 12:54:37');
INSERT INTO `materials_catalog` VALUES ('429', 'ντουι ε27', '1', '', 'τεμ', '1.02', '', 'ntoui, e27', '', '1', '2025-10-29 13:03:27', '2025-10-29 13:03:27');
INSERT INTO `materials_catalog` VALUES ('430', 'διακοπτης διπλος aleretour χωνευτος', '1', '', 'τεμ', NULL, '', 'diakopths, switch, diakoptis, diplos, chwneutos, choneutos', '', '1', '2025-10-29 13:08:47', '2025-10-29 13:08:47');
INSERT INTO `materials_catalog` VALUES ('431', 'πριζα σουκο χωνευτη', '1', '', 'τεμ', NULL, '', 'priza, socket, outlet, souko, chwneuth, choneuth', '', '1', '2025-10-29 13:09:37', '2025-10-29 13:09:37');
INSERT INTO `materials_catalog` VALUES ('432', 'Καλωδιο Μεσής Τάσης 1Χ70mm', '1', '', 'μ', NULL, '', 'kalwdio, kalodio, cable, wire, meshs, tashs, 1ch70mm', '', '1', '2025-10-30 07:52:29', '2025-10-30 07:52:59');
INSERT INTO `materials_catalog` VALUES ('433', 'Αγωγός Χάλκινος 1Χ35mm', '1', '', 'μ', NULL, '', 'agwgos, agogos, chalkinos, 1ch35mm', '', '1', '2025-10-30 07:56:36', '2025-10-30 07:58:12');
INSERT INTO `materials_catalog` VALUES ('434', 'Αγωγός Χάλκινος 1Χ50mm', '1', '', 'μ', NULL, '', 'agwgos, agogos, chalkinos, 1ch50mm', '', '1', '2025-10-30 07:58:24', '2025-10-30 07:58:24');
INSERT INTO `materials_catalog` VALUES ('435', 'Αγωγός Χάλκινος 1Χ70mm', '1', '', 'μ', NULL, '', 'agwgos, agogos, chalkinos, 1ch70mm', '', '1', '2025-10-30 07:58:38', '2025-10-30 07:58:38');
INSERT INTO `materials_catalog` VALUES ('436', 'Διακλαδωτήρας Τ/A 60x60x2,5: Ταινίας 30x4 με Αγωγό Φ8-12|3 Πλάκες|St/Tzn', '1', '', 'τεμ', NULL, '', 'diakladwthras, diakladothras, t/a, tainias, tape, tenia, tainia, me, agwgo, agogo, f8, plakes|st/tzn', '', '1', '2025-10-30 08:13:20', '2025-10-30 08:13:20');
INSERT INTO `materials_catalog` VALUES ('437', 'Διακλαδωτήρας Τ/T 50x50x2,5: 2 Ταινίες 30x4|3 Πλάκες|St/Tzn', '1', '', 'τεμ', NULL, '', 'diakladwthras, diakladothras, t/t, tainies, plakes|st/tzn', '', '1', '2025-10-30 08:13:44', '2025-10-30 08:13:44');
INSERT INTO `materials_catalog` VALUES ('438', 'Πλακέ Ταινία 30x3,5mm St/Tzn 500Gr/M²|Kgr/Mt 0,83|Ρολλό 31m', '1', '', 'μ', NULL, '', 'plake, tainia, tape, tenia, 0,83|rollo', '', '1', '2025-10-30 08:14:19', '2025-10-30 08:14:19');
INSERT INTO `materials_catalog` VALUES ('439', 'Ηλεκτρόδιο Φ15', '1', '', 'τεμ', NULL, 'Elemko', 'hlektrodio, f15', '', '1', '2025-10-30 08:54:02', '2025-10-30 08:54:27');
INSERT INTO `materials_catalog` VALUES ('440', 'Χαλκός 50mm', '1', '', 'τεμ', NULL, '', 'chalkos', '', '1', '2025-10-30 08:55:50', '2025-10-30 08:55:50');
INSERT INTO `materials_catalog` VALUES ('441', 'Τραβέρσα', '1', '', 'τεμ', NULL, '', 'trabersa', '', '1', '2025-10-30 08:58:43', '2025-10-30 08:58:43');
INSERT INTO `materials_catalog` VALUES ('442', 'Ακροκιβώτιο Εσωτερικό', '1', '', 'τεμ', NULL, '', 'akrokibwtio, akrokibotio, eswteriko, esoteriko', '', '1', '2025-10-30 09:00:16', '2025-10-30 09:00:16');
INSERT INTO `materials_catalog` VALUES ('443', 'Ακροκιβώτιο Εξωτερικό', '1', '', 'τεμ', NULL, '', 'akrokibwtio, akrokibotio, ekswteriko, eksoteriko', '', '1', '2025-10-30 09:00:32', '2025-10-30 09:00:32');
INSERT INTO `materials_catalog` VALUES ('444', 'Κολάρο 3/4 με Λάστιχο', '1', '', 'τεμ', NULL, '', 'kolaro, me, lasticho', '', '1', '2025-10-30 09:01:30', '2025-10-30 09:01:30');
INSERT INTO `materials_catalog` VALUES ('445', 'ΝΥΥ 3Χ120+70mm', '1', '', 'μ', NULL, '', 'nyy, 3ch120+70mm', '', '1', '2025-10-30 09:02:23', '2025-10-30 09:02:23');
INSERT INTO `materials_catalog` VALUES ('446', 'ΝΥΥ 1Χ120mm', '1', '', 'μ', NULL, '', 'nyy, 1ch120mm', '', '1', '2025-10-30 09:02:50', '2025-10-30 09:02:50');
INSERT INTO `materials_catalog` VALUES ('447', 'Σχάρα 200/110', '1', '', 'μ', NULL, '', 'schara', '', '1', '2025-10-30 09:03:51', '2025-10-30 09:03:51');
INSERT INTO `materials_catalog` VALUES ('448', 'Σχάρα 300/60', '1', '', 'μ', NULL, '', 'schara', '', '1', '2025-10-30 09:04:09', '2025-10-30 09:04:09');
INSERT INTO `materials_catalog` VALUES ('449', 'Φωτιστικό Τ8/2Χ150', '1', '', 'τεμ', NULL, '', 'fwtistiko, fotistiko, t8/2ch150', '', '1', '2025-10-30 09:04:30', '2025-10-30 09:04:30');
INSERT INTO `materials_catalog` VALUES ('450', 'Φωτιστικό Τ8/150', '1', '', 'τεμ', NULL, '', 'fwtistiko, fotistiko, t8/150', '', '1', '2025-10-30 09:05:00', '2025-10-30 09:26:56');
INSERT INTO `materials_catalog` VALUES ('451', 'Λιπαντικό Καλωδίων Heavy', '1', '', 'τεμ', NULL, '', 'lipantiko, kalwdiwn, kalodion', '', '1', '2025-10-30 09:05:53', '2025-10-30 09:05:53');
INSERT INTO `materials_catalog` VALUES ('452', 'Ταινία Χαλκού 30Χ3', '1', '', 'μ', NULL, '', 'tainia, tape, tenia, chalkoy, 30ch3', '', '1', '2025-10-30 09:06:16', '2025-10-30 09:07:48');
INSERT INTO `materials_catalog` VALUES ('453', 'Στήριγαμ Ταινίας Χάλκινα', '1', '', 'τεμ', NULL, '', 'sthrigam, tainias, tape, tenia, tainia, chalkina', '', '1', '2025-10-30 09:09:59', '2025-10-30 09:09:59');
INSERT INTO `materials_catalog` VALUES ('454', 'Διακόπτης Forix', '1', '', 'τεμ', NULL, '', 'diakopths, switch, diakoptis', '', '1', '2025-10-30 09:10:18', '2025-10-30 09:10:18');
INSERT INTO `materials_catalog` VALUES ('455', 'Πρίζα Σούκο Forix', '1', '', 'τεμ', NULL, '', 'priza, socket, outlet, soyko', '', '1', '2025-10-30 09:10:40', '2025-10-30 09:10:40');
INSERT INTO `materials_catalog` VALUES ('456', 'ΝΥΛ 3Χ1,5mm', '1', '', 'μ', NULL, '', 'nyl, 3ch1,5mm', '', '1', '2025-10-30 09:12:07', '2025-10-30 09:12:07');
INSERT INTO `materials_catalog` VALUES ('457', 'ΝΥΛ 5Χ1,5mm', '1', '', 'μ', NULL, '', 'nyl, 5ch1,5mm', '', '1', '2025-10-30 09:12:20', '2025-10-30 09:12:20');
INSERT INTO `materials_catalog` VALUES ('458', 'ΝΥΛ 4Χ1,5mm', '1', '', 'μ', NULL, '', 'nyl, 4ch1,5mm', '', '1', '2025-10-30 09:13:46', '2025-10-30 09:13:46');
INSERT INTO `materials_catalog` VALUES ('459', 'ΝΥΛ 4Χ2,5mm', '1', '', 'μ', NULL, '', 'nyl, 4ch2,5mm', '', '1', '2025-10-30 09:13:58', '2025-10-30 09:13:58');
INSERT INTO `materials_catalog` VALUES ('460', 'Βάση Πίνακα Μ.Τ', '1', '', 'τεμ', NULL, '', 'bash, pinaka, m.t', '', '1', '2025-10-30 09:14:29', '2025-10-30 09:14:29');
INSERT INTO `materials_catalog` VALUES ('461', 'Πίνακας Προστασίας Μ/Σ', '1', '', 'τεμ', NULL, '', 'pinakas, prostasias, m/s', '', '1', '2025-10-30 09:14:53', '2025-10-30 09:14:53');
INSERT INTO `materials_catalog` VALUES ('463', 'Μούφες', '1', '', 'τεμ', NULL, '', 'moyfes', '', '1', '2025-10-30 09:16:09', '2025-10-30 09:16:09');
INSERT INTO `materials_catalog` VALUES ('464', 'Κολάρα', '1', '', 'τεμ', NULL, '', 'kolara', '', '1', '2025-10-30 09:16:20', '2025-10-30 09:16:20');
INSERT INTO `materials_catalog` VALUES ('465', 'UTP Cat 6', '1', '', 'μ', NULL, '', '', '', '1', '2025-10-30 09:17:24', '2025-10-30 09:17:24');
INSERT INTO `materials_catalog` VALUES ('466', 'DLP 105X50', '1', '', 'μ', NULL, '', '', '', '1', '2025-10-30 09:18:53', '2025-10-30 09:18:53');
INSERT INTO `materials_catalog` VALUES ('467', 'DLP 80X50', '1', '', 'μ', NULL, '', '', '', '1', '2025-10-30 09:19:09', '2025-10-30 09:19:09');
INSERT INTO `materials_catalog` VALUES ('468', 'Mosaic A/R', '1', '', 'τεμ', NULL, '', '', '', '1', '2025-10-30 09:19:32', '2025-10-30 09:19:32');
INSERT INTO `materials_catalog` VALUES ('469', 'Mosaic Μπουτόν', '1', '', 'τεμ', NULL, '', 'mpouton', '', '1', '2025-10-30 09:19:44', '2025-10-30 09:19:44');
INSERT INTO `materials_catalog` VALUES ('470', 'Mosaic Ρολό', '1', '', 'τεμ', NULL, '', 'rolo', '', '1', '2025-10-30 09:20:09', '2025-10-30 09:20:09');
INSERT INTO `materials_catalog` VALUES ('471', 'Τάπα 105Χ50', '1', '', 'τεμ', NULL, '', 'tapa, 105ch50', '', '1', '2025-10-30 09:20:28', '2025-10-30 09:20:28');
INSERT INTO `materials_catalog` VALUES ('472', 'Τάπα 80Χ50', '1', '', 'τεμ', NULL, '', 'tapa, 80ch50', '', '1', '2025-10-30 09:20:53', '2025-10-30 09:20:53');
INSERT INTO `materials_catalog` VALUES ('473', 'Βάση & Πλαίσιο 105Χ50', '1', '', 'μ', NULL, '', 'bash, plaisio, 105ch50', '', '1', '2025-10-30 09:21:27', '2025-10-30 09:21:27');
INSERT INTO `materials_catalog` VALUES ('474', 'Βάση & Πλαίσιο 80Χ50', NULL, '', 'τεμ', NULL, '', 'bash, plaisio, 80ch50', '', '1', '2025-10-30 09:21:41', '2025-10-30 09:21:41');
INSERT INTO `materials_catalog` VALUES ('475', 'Σούκο Χωνευτή Niloe + Πλαίσιο', '1', '', 'τεμ', NULL, '', 'soyko, chwneuth, choneuth, plaisio', '', '1', '2025-10-30 09:22:32', '2025-10-30 09:22:32');
INSERT INTO `materials_catalog` VALUES ('476', 'Σούκο Εξωτερική διπλή Forex', '1', '', 'τεμ', NULL, '', 'soyko, ekswterikh, eksoterikh, diplh', '', '1', '2025-10-30 09:23:08', '2025-10-30 09:23:08');
INSERT INTO `materials_catalog` VALUES ('477', 'Mosaic Σούκο Μονή', '1', '', 'τεμ', NULL, '', 'soyko, monh', '', '1', '2025-10-30 09:23:27', '2025-10-30 09:23:27');
INSERT INTO `materials_catalog` VALUES ('478', 'Mosaic Σούκο Διπλή', '1', '', 'τεμ', NULL, '', 'soyko, diplh', '', '1', '2025-10-30 09:24:05', '2025-10-30 09:24:05');
INSERT INTO `materials_catalog` VALUES ('479', 'Mosaic Σούκο Τριπλή', '1', '', 'τεμ', NULL, '', 'soyko, triplh', '', '1', '2025-10-30 09:24:17', '2025-10-30 09:24:17');
INSERT INTO `materials_catalog` VALUES ('480', 'Φωτιστικό Ασφαλείας GR8', '1', '', 'τεμ', NULL, '', 'fwtistiko, fotistiko, asfaleias', '', '1', '2025-10-30 09:24:34', '2025-10-30 09:24:34');
INSERT INTO `materials_catalog` VALUES ('481', 'Φωτιστικό Τ8/120', '1', '', 'τεμ', NULL, '', 'fwtistiko, fotistiko, t8/120', '', '1', '2025-10-30 09:25:21', '2025-10-30 09:26:38');
INSERT INTO `materials_catalog` VALUES ('482', 'Πάνελ LED Φ200 / 20Watt', '1', '', 'τεμ', NULL, '', 'panel, f200', '', '1', '2025-10-30 09:25:57', '2025-10-30 09:25:57');
INSERT INTO `materials_catalog` VALUES ('483', 'Φωτιστικό Τ8/2Χ120', '1', '', 'τεμ', NULL, '', 'fwtistiko, fotistiko, t8/2ch120', '', '1', '2025-10-30 09:27:17', '2025-10-30 09:27:17');
INSERT INTO `materials_catalog` VALUES ('484', 'Ρακόρ', '1', '', 'τεμ', NULL, '', 'rakor', '', '1', '2025-10-30 09:27:37', '2025-10-30 09:27:37');
INSERT INTO `materials_catalog` VALUES ('485', 'Κουτί 16/20', '1', '', 'τεμ', NULL, 'Κουβίδης', 'kouti, box', '', '1', '2025-10-30 09:28:01', '2025-10-30 09:28:01');
INSERT INTO `materials_catalog` VALUES ('486', 'Κουτί 20/16', '1', '', 'τεμ', NULL, 'Κουβίδης', 'kouti, box', '', '1', '2025-10-30 09:28:17', '2025-10-30 09:28:17');
INSERT INTO `materials_catalog` VALUES ('487', 'Κουτί 25/32', '1', '', 'τεμ', NULL, 'Κουβίδης', 'kouti, box', '', '1', '2025-10-30 09:28:38', '2025-10-30 09:28:38');
INSERT INTO `materials_catalog` VALUES ('488', 'Καμπάνα LED', '1', '', 'τεμ', NULL, 'SMK', 'kampana', '', '1', '2025-10-30 09:29:48', '2025-10-30 09:29:48');
INSERT INTO `materials_catalog` VALUES ('489', 'Φωτοκύτταρο', '1', '', 'τεμ', NULL, 'Hager', 'fwtokyttaro, fotokyttaro', '', '1', '2025-10-30 09:30:15', '2025-10-30 09:30:15');
INSERT INTO `materials_catalog` VALUES ('490', 'Καλώδιο DC 6mm2', '1', '', 'τεμ', NULL, '', 'kalwdio, kalodio, cable, wire', '', '1', '2025-10-30 09:30:43', '2025-10-30 09:30:43');
INSERT INTO `materials_catalog` VALUES ('491', 'Φυσσίγιο PL 20A', '1', '', 'τεμ', NULL, '', 'fyssigio', '', '1', '2025-10-30 09:31:12', '2025-10-30 09:31:12');
INSERT INTO `materials_catalog` VALUES ('492', 'Στροφή 80Χ50', '1', '', 'τεμ', NULL, '', 'strofh, 80ch50', '', '1', '2025-10-30 09:31:52', '2025-10-30 09:31:52');
INSERT INTO `materials_catalog` VALUES ('493', 'ΤΑΦ 100Χ50', '1', '', 'τεμ', NULL, '', 'taf, 100ch50', '', '1', '2025-10-30 09:32:04', '2025-10-30 09:32:04');
INSERT INTO `materials_catalog` VALUES ('494', 'Niloe απλός + πλαίσιο', '1', '', 'τεμ', NULL, '', 'aplos, plaisio', '', '1', '2025-10-30 09:32:26', '2025-10-30 09:32:26');
INSERT INTO `materials_catalog` VALUES ('495', 'Γωνία Σχάρας 150', '1', '', 'τεμ', NULL, '', 'gwnia, gonia, scharas', '', '1', '2025-10-30 09:32:45', '2025-10-30 09:32:45');
INSERT INTO `materials_catalog` VALUES ('496', 'Γωνία Σχάρας 400', '1', '', 'τεμ', NULL, '', 'gwnia, gonia, scharas', '', '1', '2025-10-30 09:33:08', '2025-10-30 09:33:08');
INSERT INTO `materials_catalog` VALUES ('497', 'Προφίλ 40Χ20', '1', '', 'τεμ', NULL, '', 'profil, 40ch20', '', '1', '2025-10-30 09:33:27', '2025-10-30 09:33:27');
INSERT INTO `materials_catalog` VALUES ('498', 'Ντίζα Μ10', '1', '', 'τεμ', NULL, '', 'ntiza, m10', '', '1', '2025-10-30 09:33:58', '2025-10-30 09:33:58');
INSERT INTO `materials_catalog` VALUES ('499', 'Κουτί Εξωτερικό Mosaic 2m', '1', '', 'τεμ', NULL, '', 'kouti, box, ekswteriko, eksoteriko', '', '1', '2025-10-30 09:34:33', '2025-10-30 09:34:33');
INSERT INTO `materials_catalog` VALUES ('500', 'Forix Απλός', '1', '', 'τεμ', NULL, '', 'aplos', '', '1', '2025-10-30 09:35:00', '2025-10-30 09:35:00');
INSERT INTO `materials_catalog` VALUES ('501', 'Forix Σούκο Μονή', '1', '', 'τεμ', NULL, '', 'soyko, monh', '', '1', '2025-10-30 09:35:38', '2025-10-30 09:35:38');
INSERT INTO `materials_catalog` VALUES ('502', 'Φυσίγγιο BOX 80Α', '1', '', 'τεμ', NULL, '', 'fysiggio, 80a', '', '1', '2025-10-30 09:37:05', '2025-10-30 09:37:05');
INSERT INTO `materials_catalog` VALUES ('503', 'Καπάκι Σκάφης + Σκάφη', '1', '', 'τεμ', NULL, '', 'kapaki, skafhs, skafh', '', '1', '2025-10-30 10:15:12', '2025-10-30 10:15:12');
INSERT INTO `materials_catalog` VALUES ('504', 'Λάμπα Τ8/150', '1', '', 'τεμ', NULL, '', 'lampa, lamp, bulb, light, t8/150', '', '1', '2025-10-30 10:24:03', '2025-10-30 10:24:03');
INSERT INTO `materials_catalog` VALUES ('505', 'Λάμπα Τ8/120', '1', '', 'τεμ', NULL, '', 'lampa, lamp, bulb, light, t8/120', '', '1', '2025-10-30 10:24:13', '2025-10-30 10:24:13');
INSERT INTO `materials_catalog` VALUES ('506', 'ΝΥΥ 1Χ150 Αλουμινίου', '7', '', 'μ', NULL, '', 'nyy, 1ch150, alouminiou', '', '1', '2025-10-30 10:41:49', '2025-10-30 10:41:49');
INSERT INTO `materials_catalog` VALUES ('507', 'OFlex 120mm2', '1', '', 'μ', NULL, '', '', '', '1', '2025-10-30 10:42:22', '2025-10-30 10:42:22');
INSERT INTO `materials_catalog` VALUES ('508', 'Κως 120mm2', '1', '', 'τεμ', NULL, '', 'kws, kos', '', '1', '2025-10-30 10:42:42', '2025-10-30 10:42:42');
INSERT INTO `materials_catalog` VALUES ('509', 'Διακόπτης Ισχύος 3P/400A', '6', '', 'τεμ', NULL, '', 'diakopths, switch, diakoptis, ischyos', '', '1', '2025-10-30 10:43:18', '2025-10-30 10:43:18');
INSERT INTO `materials_catalog` VALUES ('510', 'Νοβοπανόβιδες  5*80', NULL, '', 'κουτί', '825.00', '', 'nobopanobides', '', '1', '2025-10-30 18:29:50', '2025-11-13 07:16:40');
INSERT INTO `materials_catalog` VALUES ('511', 'θερμοστατης χωρου siemens απλος', '1', '', 'τεμ', '19.00', '', 'thermostaths, chwrou, chorou, aplos', '', '1', '2025-11-19 06:34:36', '2025-11-19 06:34:36');
INSERT INTO `materials_catalog` VALUES ('512', 'λαμπα led opalina φ110/18W/4000K', '1', '', 'τεμ', NULL, '', 'lampa, lamp, bulb, light, f110/18w/4000k, f110/18o/4000k', '', '1', '2025-11-19 06:35:45', '2025-11-19 06:35:45');
INSERT INTO `materials_catalog` VALUES ('513', 'αγωγος χαλκινος ΝΥΥ 1*95', '1', '', 'μ', NULL, '', 'agwgos, agogos, chalkinos, nyy', '', '1', '2025-11-19 06:43:12', '2025-11-19 06:43:12');
INSERT INTO `materials_catalog` VALUES ('514', 'αγωγος γειωσεως στρογγυλος γαλβανιζε Φ10', '1', '', 'μ', NULL, '', 'agwgos, agogos, geiwsews, geioseos, stroggylos, galbanize, f10', '', '1', '2025-11-19 07:00:45', '2025-11-19 07:00:45');
INSERT INTO `materials_catalog` VALUES ('515', 'συνδεσμος ταινιας-αγωγου 50-120mm- inox', '1', '', 'τεμ', NULL, '', 'syndesmos, tainias, tape, tenia, agwgou, agogou', '', '1', '2025-11-19 07:04:44', '2025-11-19 07:04:44');
INSERT INTO `materials_catalog` VALUES ('516', 'nyaf 4mm', '1', '', 'μ', NULL, '', '', '', '1', '2025-11-19 07:29:58', '2025-11-19 07:29:58');
INSERT INTO `materials_catalog` VALUES ('517', 'καλωδιο ΝΥΥ 3*6', '7', '', 'μ', NULL, '', 'kalwdio, kalodio, cable, wire, nyy', '', '1', '2025-11-19 07:34:53', '2025-11-19 07:34:53');


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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` VALUES ('1', '7', '2025-10-01', '2025-10-31', '4.00', '40.00', '2025-10-25 08:02:54', '1', NULL, '2025-10-25 08:02:54', '2025-10-25 08:02:54');


-- Table: permissions
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission` (`module`,`action`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` VALUES ('1', 'users', 'view', 'Προβολή Χρηστών', 'Δικαίωμα προβολής λίστας χρηστών', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('2', 'users', 'create', 'Δημιουργία Χρήστη', 'Δικαίωμα δημιουργίας νέου χρήστη', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('3', 'users', 'edit', 'Επεξεργασία Χρήστη', 'Δικαίωμα επεξεργασίας υπάρχοντος χρήστη', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('4', 'users', 'delete', 'Διαγραφή Χρήστη', 'Δικαίωμα διαγραφής χρήστη', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('5', 'customers', 'view', 'Προβολή Πελατών', 'Δικαίωμα προβολής λίστας πελατών', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('6', 'customers', 'create', 'Δημιουργία Πελάτη', 'Δικαίωμα δημιουργίας νέου πελάτη', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('7', 'customers', 'edit', 'Επεξεργασία Πελάτη', 'Δικαίωμα επεξεργασίας πελάτη', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('8', 'customers', 'delete', 'Διαγραφή Πελάτη', 'Δικαίωμα διαγραφής πελάτη', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('9', 'projects', 'view', 'Προβολή Έργων', 'Δικαίωμα προβολής λίστας έργων', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('10', 'projects', 'create', 'Δημιουργία Έργου', 'Δικαίωμα δημιουργίας νέου έργου', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('11', 'projects', 'edit', 'Επεξεργασία Έργου', 'Δικαίωμα επεξεργασίας έργου', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('12', 'projects', 'delete', 'Διαγραφή Έργου', 'Δικαίωμα διαγραφής έργου', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('13', 'tasks', 'view', 'Προβολή Εργασιών', 'Δικαίωμα προβολής εργασιών', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('14', 'tasks', 'create', 'Δημιουργία Εργασίας', 'Δικαίωμα δημιουργίας νέας εργασίας', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('15', 'tasks', 'edit', 'Επεξεργασία Εργασίας', 'Δικαίωμα επεξεργασίας εργασίας', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('16', 'tasks', 'delete', 'Διαγραφή Εργασίας', 'Δικαίωμα διαγραφής εργασίας', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('17', 'transformer_maintenance', 'view', 'Προβολή Συντηρήσεων', 'Δικαίωμα προβολής συντηρήσεων Μ/Σ', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('18', 'transformer_maintenance', 'create', 'Δημιουργία Συντήρησης', 'Δικαίωμα δημιουργίας νέας συντήρησης', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('19', 'transformer_maintenance', 'edit', 'Επεξεργασία Συντήρησης', 'Δικαίωμα επεξεργασίας συντήρησης', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('20', 'transformer_maintenance', 'delete', 'Διαγραφή Συντήρησης', 'Δικαίωμα διαγραφής συντήρησης', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('21', 'transformer_maintenance', 'send_email', 'Αποστολή Email Συντήρησης', 'Δικαίωμα αποστολής email με αναφορά συντήρησης', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('22', 'reports', 'view', 'Προβολή Αναφορών', 'Δικαίωμα προβολής αναφορών', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('23', 'reports', 'generate', 'Δημιουργία PDF', 'Δικαίωμα δημιουργίας PDF αναφορών', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('24', 'reports', 'send_email', 'Αποστολή Email Αναφοράς', 'Δικαίωμα αποστολής αναφορών με email', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('25', 'email_templates', 'view', 'Προβολή Email Templates', 'Δικαίωμα προβολής email templates', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('26', 'email_templates', 'create', 'Δημιουργία Email Template', 'Δικαίωμα δημιουργίας template', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('27', 'email_templates', 'edit', 'Επεξεργασία Email Template', 'Δικαίωμα επεξεργασίας template', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('28', 'email_templates', 'delete', 'Διαγραφή Email Template', 'Δικαίωμα διαγραφής template', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('29', 'smtp_settings', 'view', 'Προβολή SMTP Ρυθμίσεων', 'Δικαίωμα προβολής SMTP ρυθμίσεων', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('30', 'smtp_settings', 'edit', 'Επεξεργασία SMTP', 'Δικαίωμα επεξεργασίας SMTP ρυθμίσεων', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('31', 'roles', 'view', 'Προβολή Ρόλων', 'Δικαίωμα προβολής ρόλων', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('32', 'roles', 'create', 'Δημιουργία Ρόλου', 'Δικαίωμα δημιουργίας ρόλου', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('33', 'roles', 'edit', 'Επεξεργασία Ρόλου', 'Δικαίωμα επεξεργασίας ρόλου', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('34', 'roles', 'delete', 'Διαγραφή Ρόλου', 'Δικαίωμα διαγραφής ρόλου', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('35', 'roles', 'manage_permissions', 'Διαχείριση Δικαιωμάτων', 'Δικαίωμα διαχείρισης δικαιωμάτων ρόλων', '2025-11-06 09:00:01');
INSERT INTO `permissions` VALUES ('94', 'customers', 'export', 'Εξαγωγή Πελατών', 'Δικαίωμα εξαγωγής λίστας πελατών', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('95', 'appointments', 'view', 'Προβολή Ραντεβού', 'Δικαίωμα προβολής λίστας ραντεβού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('96', 'appointments', 'create', 'Δημιουργία Ραντεβού', 'Δικαίωμα δημιουργίας νέου ραντεβού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('97', 'appointments', 'edit', 'Επεξεργασία Ραντεβού', 'Δικαίωμα επεξεργασίας ραντεβού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('98', 'appointments', 'delete', 'Διαγραφή Ραντεβού', 'Δικαίωμα διαγραφής ραντεβού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('99', 'quotes', 'view', 'Προβολή Προσφορών', 'Δικαίωμα προβολής λίστας προσφορών', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('100', 'quotes', 'create', 'Δημιουργία Προσφοράς', 'Δικαίωμα δημιουργίας νέας προσφοράς', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('101', 'quotes', 'edit', 'Επεξεργασία Προσφοράς', 'Δικαίωμα επεξεργασίας προσφοράς', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('102', 'quotes', 'delete', 'Διαγραφή Προσφοράς', 'Δικαίωμα διαγραφής προσφοράς', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('103', 'quotes', 'export', 'Εξαγωγή Προσφορών', 'Δικαίωμα εξαγωγής προσφορών σε PDF', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('104', 'materials', 'view', 'Προβολή Υλικών', 'Δικαίωμα προβολής καταλόγου υλικών', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('105', 'materials', 'create', 'Δημιουργία Υλικού', 'Δικαίωμα προσθήκης νέου υλικού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('106', 'materials', 'edit', 'Επεξεργασία Υλικού', 'Δικαίωμα επεξεργασίας υλικού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('107', 'materials', 'delete', 'Διαγραφή Υλικού', 'Δικαίωμα διαγραφής υλικού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('108', 'materials', 'export', 'Εξαγωγή Υλικών', 'Δικαίωμα εξαγωγής καταλόγου υλικών', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('109', 'reports', 'export', 'Εξαγωγή Αναφορών', 'Δικαίωμα εξαγωγής αναφορών', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('110', 'technicians', 'view', 'Προβολή Τεχνικών', 'Δικαίωμα προβολής λίστας τεχνικών', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('111', 'technicians', 'create', 'Δημιουργία Τεχνικού', 'Δικαίωμα προσθήκης νέου τεχνικού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('112', 'technicians', 'edit', 'Επεξεργασία Τεχνικού', 'Δικαίωμα επεξεργασίας τεχνικού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('113', 'technicians', 'delete', 'Διαγραφή Τεχνικού', 'Δικαίωμα διαγραφής τεχνικού', '2025-11-07 09:41:41');
INSERT INTO `permissions` VALUES ('123', 'daily_task', 'view', 'Προβολή Εργασιών Ημέρας', 'Δικαίωμα προβολής...', '2025-11-10 12:28:36');
INSERT INTO `permissions` VALUES ('124', 'daily_task', 'create', 'Δημιουργία Εργασιών Ημέρας', 'Δικαίωμα δημιουργίας...', '2025-11-10 12:28:36');
INSERT INTO `permissions` VALUES ('125', 'daily_task', 'edit', 'Επεξεργασία Εργασιών Ημέρας', 'Δικαίωμα επεξεργασίας...', '2025-11-10 12:28:36');
INSERT INTO `permissions` VALUES ('126', 'daily_task', 'delete', 'Διαγραφή Εργασιών Ημέρας', 'Δικαίωμα διαγραφής...', '2025-11-10 12:28:36');
INSERT INTO `permissions` VALUES ('127', 'trash', 'view', '', 'Προβολή κάδου απορριμμάτων', '2025-11-19 12:48:41');
INSERT INTO `permissions` VALUES ('128', 'trash', 'restore', '', 'Επαναφορά διαγραμμένων στοιχείων', '2025-11-19 12:48:41');
INSERT INTO `permissions` VALUES ('129', 'trash', 'delete_permanent', '', 'Οριστική διαγραφή στοιχείων', '2025-11-19 12:48:41');
INSERT INTO `permissions` VALUES ('130', 'trash', 'view_log', '', 'Προβολή ιστορικού διαγραφών', '2025-11-19 12:48:41');


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
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_task_type` (`task_type`),
  KEY `idx_task_date` (`task_date`),
  KEY `idx_date_range` (`date_from`,`date_to`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_project_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `project_tasks` VALUES ('8', '4', 'single_day', '2025-10-15', NULL, NULL, 'ΡΑΝΤΕΒΟΥ ΜΕ ΚΑΣΤΡΙΝΑΚΗ,ΕΛΕΓΧΟΣ ΕΓΚΑΤΑΣΤΑΣΗΣ ΚΑΙ ΑΠΟΞΥΛΩΣΗ ΚΑΛΩΔΙΩΝ ΕΚΑΝΑ ΥΠΟΔΟΜΗ ΓΙΑ ΓΚΡΕΜΙΣΜΑΤΑ', '', '0.00', '30.00', '30.00', '2025-10-23 12:38:58', '2025-10-23 14:03:54', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('11', '4', 'single_day', '2025-10-23', NULL, NULL, 'ΣΚΑΨΙΜΑΤΑ,ΣΩΛΗΝΩΣΕΙΣ, ΚΑΛΩΔΙΑ', '', '4088.81', '128.00', '4216.81', '2025-10-23 14:13:27', '2025-10-29 12:56:17', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('12', '2', 'single_day', '2025-08-11', NULL, NULL, 'ΠΑΡΑΛΑΒΗ ΦΩΤΙΣΤΙΚΩΝ ΑΠΟ ΚΕΝΤΡΙΚΗ ΑΠΟΘΗΚΗ,ΕΛΕΓΧΟΣ ΓΡΑΜΜΩΝ ΚΑΙ ΑΣΦΑΛΕΙΩΝ,ΚΑΤΑΓΡΑΦΗ ΑΝΑΓΚΩΝ ,ΤΟΠΟΘΕΤΗΣΗ 5 ΦΩΤΙΣΤΙΚΩΝ', '', '0.00', '108.40', '108.40', '2025-10-24 05:33:57', '2025-10-24 05:33:57', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('13', '2', 'single_day', '2025-08-12', NULL, NULL, 'ΤΟΠΟΘΕΤΗΣΗ ΦΩΤΙΣΤΙΚΩΝ', '', '0.00', '160.00', '160.00', '2025-10-24 05:37:37', '2025-10-24 05:37:37', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('14', '2', 'single_day', '2025-08-13', NULL, NULL, 'ΤΟΠΟΘΕΤΗΣΗ ΦΩΤΙΣΤΙΚΩΝ', '', '0.00', '160.00', '160.00', '2025-10-24 05:39:52', '2025-10-24 05:39:52', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('15', '2', 'date_range', '2025-10-24', '2025-09-01', '2025-09-02', 'ΤΟΠΟΘΕΤΗΣΗ ΦΩΤΙΣΤΙΚΩΝ', '', '0.00', '241.68', '241.68', '2025-10-24 05:43:56', '2025-10-24 06:03:18', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('16', '2', 'date_range', '2025-10-24', '2025-09-08', '2025-09-09', 'ΤΟΠΟΘΕΤΗΣΗ ΦΩΤΙΣΤΙΚΩΝ', '', '0.00', '0.00', '0.00', '2025-10-24 06:00:47', '2025-10-24 06:00:47', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('21', '4', 'single_day', '2025-10-30', NULL, NULL, 'καλωδιωσεις ,ματισεις ,τοποθετηση πριζοδιακοπτες και φωτα', '', '354.62', '80.00', '434.62', '2025-10-29 13:02:28', '2025-10-30 17:33:21', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('22', '5', 'date_range', '2024-08-09', '2024-08-09', '2025-09-24', 'Ανακατασκευη Υποσταθμού Μέσης Τάσης', '', '18849.78', '0.00', '18849.78', '2025-10-30 10:10:54', '2025-10-30 10:54:28', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('23', '2', 'single_day', '2025-10-20', NULL, NULL, 'τοποθετηση φωτιστικων κατω απο καθισματα', '', '0.00', '280.00', '280.00', '2025-10-30 18:09:39', '2025-10-30 18:09:39', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('24', '2', 'date_range', '2025-10-30', '2025-10-21', '2025-10-22', 'τοποθετηση φωτιστικων', '', '0.00', '256.00', '256.00', '2025-10-30 18:14:38', '2025-10-30 18:14:38', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('25', '2', 'single_day', '2025-10-30', NULL, NULL, 'ελεγχος καλωδιωσης σε δυο διαζωματα για βραχυκυκλωμα', '', '0.00', '180.00', '180.00', '2025-10-30 18:23:16', '2025-10-30 18:23:16', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('26', '4', 'single_day', '2025-11-07', NULL, NULL, 'τοποθετηση φωτιστικων σε δωματιο ισογειου και δωματιο οροφου', '', '0.00', '45.00', '45.00', '2025-11-12 13:20:41', '2025-11-12 13:21:56', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('27', '2', 'single_day', '2025-11-07', NULL, NULL, 'τοποθετηση 10 φωτιστικων που παραλαβαμε απο συντηρηση,εχουμε εκρεμοτητες,να δουμε προβληματικες γραμμες και πως θα διορθωσουμε τις βλαβες,οτι φωτιστικο περισεψε το επιστρεψαμε στην συντηρηση', '', '165.00', '90.00', '255.00', '2025-11-12 13:27:11', '2025-11-12 13:27:11', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('28', '6', 'single_day', '2025-05-27', NULL, NULL, 'ΤΟΠΟΘΕΤΗΣΗ ΛΑΜΑΣ ΓΕΙΩΣΕΩΣ ΣΕ ΘΕΜΕΛΙΟ ΑΠΟΘΗΚΗΣ', '', '0.00', '270.00', '270.00', '2025-11-12 13:39:10', '2025-11-12 13:39:10', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('29', '6', 'single_day', '2025-06-04', NULL, NULL, 'ΤΟΠΟΘΕΤΗΣΗ ΛΑΜΑΣ ΓΕΙΩΣΕΩΣ ΚΑΙ ΑΓΩΓΩΝ ΣΤΟ ΘΕΜΕΛΙΟ ΤΗΣ ΑΠΟΘΗΚΗΣ,ΠΕΡΑΣΜΑ ΚΑΛΩΔΙΟΥ ΓΙΑ ΕΡΓΟΤΑΞΙΑΚΟ ΠΙΝΑΚΑ', '', '76176.00', '244.00', '76420.00', '2025-11-12 13:47:43', '2025-11-19 07:34:05', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('30', '6', 'single_day', '2025-11-08', NULL, NULL, 'ΤΟΠΟΘΕΤΗΣΗ ΛΑΜΑΣ ΓΕΙΩΣΕΩΣ ΣΕ ΤΕΛΙΚΗ ΠΛΑΚΑ ,ΤΑΚΤΟΠΟΙΗΣΗ ΓΑΛΒΑΝΙΣΜΕΝΩΝ ΑΓΩΓΩΝ ΣΕ ΚΟΛΩΝΕΣ ΜΕΤΑΛΛΙΚΕΣ', '', '18531512.50', '108.00', '18531620.50', '2025-11-12 13:52:55', '2025-11-19 07:07:12', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('31', '6', 'single_day', '2025-11-12', NULL, NULL, 'ΤΟΠΟΘΕΤΗΣΗ ΛΑΜΑΣ ΓΕΙΩΣΕΩΣ ΣΕ ΤΕΛΙΚΗ ΠΛΑΚΑ,ΣΠΙΡΑΛ ΣΕ ΜΠΕΤΟ ΓΙΑ ΠΑΡΟΧΗ    ΠΙΝΑΚΑ', '', '2786001.96', '44.00', '2786045.96', '2025-11-12 13:57:23', '2025-11-19 07:05:54', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('32', '9', 'single_day', '2025-11-18', NULL, NULL, 'αλλαγη καμενων λαμπτηρων και συντηρηση φωτιστικων,αλλαγη θερμοστατη χωρου για θερμανση', '', '26.30', '62.50', '88.80', '2025-11-19 06:33:43', '2025-11-19 06:37:31', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('33', '6', 'single_day', '2025-06-07', NULL, NULL, 'αλλαγη αγωγου γειωσεως στον γερανο,στηριξη και τσιμενταρισμα με μπετο για την εργοταξιακη παροχη,συνδεση στον πινακα του πυροσβεστικου', '', '0.00', '72.00', '72.00', '2025-11-19 07:29:22', '2025-11-19 07:29:22', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('34', '8', 'single_day', '2025-07-07', '2025-07-07', '2025-07-11', 'σωληνωσεις δωματιων', '', '0.00', '144.00', '144.00', '2025-11-19 13:04:49', '2025-11-19 13:04:49', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('35', '8', 'single_day', '2025-07-08', NULL, NULL, 'σωληνωσεις δωματιων', '', '0.00', '304.00', '304.00', '2025-11-19 13:09:45', '2025-11-20 06:10:44', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('36', '8', 'single_day', '2025-07-09', NULL, NULL, 'σωληνωσεισ δωματιων', '', '0.00', '208.00', '208.00', '2025-11-19 13:11:10', '2025-11-19 13:11:10', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('37', '8', 'single_day', '2025-07-10', NULL, NULL, 'σωληνωσεις δωματιων', '', '0.00', '144.00', '144.00', '2025-11-19 13:12:27', '2025-11-19 13:12:27', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('38', '8', 'single_day', '2025-07-11', NULL, NULL, 'σωληνωσεις', '', '0.00', '144.00', '144.00', '2025-11-19 13:14:15', '2025-11-19 13:14:15', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('39', '8', 'single_day', '2025-07-14', NULL, NULL, 'σωληνωσεις,σημαδεματα', '', '0.00', '480.00', '480.00', '2025-11-19 13:24:22', '2025-11-19 13:24:22', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('40', '8', 'single_day', '2025-07-15', NULL, NULL, 'δωματια ισογειου', '', '0.00', '240.00', '240.00', '2025-11-19 13:26:23', '2025-11-19 13:26:23', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('41', '8', 'single_day', '2025-07-16', NULL, NULL, 'δωματια ισογειου', '', '0.00', '224.00', '224.00', '2025-11-19 13:27:35', '2025-11-19 13:27:35', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('42', '8', 'single_day', '2025-07-19', NULL, NULL, 'δωματια ισογειο', '', '0.00', '224.00', '224.00', '2025-11-19 13:28:26', '2025-11-19 13:32:27', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('43', '8', 'single_day', '2025-07-18', NULL, NULL, 'δωματια ισογειου', '', '0.00', '288.00', '288.00', '2025-11-19 13:29:48', '2025-11-19 13:29:48', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('44', '8', 'date_range', '2025-11-19', '2025-07-21', '2025-07-22', 'δωματια', '', '0.00', '224.00', '224.00', '2025-11-19 13:31:55', '2025-11-19 13:31:55', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('45', '8', 'date_range', '2025-11-19', '2025-07-23', '2025-07-25', 'δωματια', '', '0.00', '144.00', '144.00', '2025-11-19 13:34:43', '2025-11-19 13:34:43', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('46', '8', 'date_range', '2025-11-19', '2025-07-29', '2025-07-31', 'δωματια', '', '0.00', '224.00', '224.00', '2025-11-19 13:39:36', '2025-11-19 13:39:36', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('47', '8', 'single_day', '2025-08-01', NULL, NULL, 'δωματια', '', '0.00', '304.00', '304.00', '2025-11-19 13:42:07', '2025-11-19 13:42:07', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('48', '8', 'single_day', '2025-08-06', NULL, NULL, 'δωματια', '', '0.00', '110.00', '110.00', '2025-11-19 14:02:51', '2025-11-20 06:25:01', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('49', '8', 'date_range', '2025-11-19', '2025-08-28', '2025-08-29', 'δωματια', '', '0.00', '224.00', '224.00', '2025-11-19 14:06:08', '2025-11-19 14:06:08', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('50', '8', 'date_range', '2025-11-19', '2025-08-18', '2025-08-22', 'δωματια', '', '0.00', '160.00', '160.00', '2025-11-19 14:08:25', '2025-11-19 14:08:25', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('51', '8', 'single_day', '2025-07-17', NULL, NULL, 'δωματια', '', '0.00', '224.00', '224.00', '2025-11-20 06:14:53', '2025-11-20 06:14:53', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('52', '8', 'single_day', '2025-09-01', NULL, NULL, 'δωματια', '', '0.00', '224.00', '224.00', '2025-11-20 06:27:11', '2025-11-20 06:27:11', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('53', '8', 'single_day', '2025-09-02', NULL, NULL, 'δωματια', '', '0.00', '160.00', '160.00', '2025-11-20 06:27:50', '2025-11-20 06:28:08', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('54', '8', 'date_range', '2025-11-20', '2025-09-03', '2025-09-05', 'δωματια', '', '0.00', '224.00', '224.00', '2025-11-20 06:29:13', '2025-11-20 06:29:13', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('55', '8', 'single_day', '2025-09-08', '2025-09-08', '2025-09-09', 'δωματια', '', '0.00', '224.00', '224.00', '2025-11-20 06:30:20', '2025-11-22 13:19:00', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('56', '6', 'single_day', '2025-11-22', NULL, NULL, 'geosub σπιραλ σε χαντακι απο πινακα εως διαταξη δεη', '', '2129.70', '51.00', '2180.70', '2025-11-22 13:15:33', '2025-11-22 13:16:01', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('57', '8', 'single_day', '2025-09-09', NULL, NULL, 'δωματια', '', '0.00', '256.00', '256.00', '2025-11-22 13:19:55', '2025-11-22 13:20:34', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('58', '8', 'single_day', '2025-09-10', NULL, NULL, 'σημαδεμα και κοψιμο σποτ,καλωδια σωληνες κλιμακοστασιο', '', '0.00', '454.00', '454.00', '2025-11-22 13:22:34', '2025-11-22 13:24:17', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('59', '8', 'single_day', '2025-09-11', NULL, NULL, 'δωματια', '', '0.00', '464.00', '464.00', '2025-11-22 13:33:50', '2025-11-22 13:33:50', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('60', '8', 'single_day', '2025-09-12', NULL, NULL, 'δωματια', '', '0.00', '368.00', '368.00', '2025-11-22 13:35:27', '2025-11-22 13:35:27', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('61', '8', 'single_day', '2025-09-15', NULL, NULL, 'δωματια καλωδια πυρ+δικτυο\r\nσωληνες ι1', '', '0.00', '304.00', '304.00', '2025-11-22 13:38:32', '2025-11-22 13:38:32', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('62', '8', 'single_day', '2025-09-16', NULL, NULL, 'δωματια', '', '0.00', '144.00', '144.00', '2025-11-22 13:48:49', '2025-11-22 13:48:49', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('63', '8', 'single_day', '2025-09-17', NULL, NULL, 'δωματια', '', '0.00', '379.00', '379.00', '2025-11-22 13:51:56', '2025-11-22 13:51:56', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('64', '8', 'single_day', '2025-09-18', NULL, NULL, 'δωματια', '', '0.00', '224.00', '224.00', '2025-11-22 13:53:14', '2025-11-22 13:53:14', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('65', '8', 'single_day', '2025-09-19', NULL, NULL, 'δωματια', '', '0.00', '144.00', '144.00', '2025-11-22 13:54:28', '2025-11-22 13:54:28', NULL, NULL);
INSERT INTO `project_tasks` VALUES ('66', '8', 'single_day', '2025-09-22', NULL, NULL, 'δωματια', '', '0.00', '144.00', '144.00', '2025-11-22 14:01:25', '2025-11-22 14:01:25', NULL, NULL);


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
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `customer_id` (`customer_id`),
  KEY `assigned_technician` (`assigned_technician`),
  KEY `created_by` (`created_by`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  KEY `idx_deleted_at` (`deleted_at`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`assigned_technician`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` VALUES ('2', 'fotistika-cine-creta-maris', '1', '2', 'Φωτιστικα CINE CRETA MARIS', 'Περασμα Φωτιστηκών', NULL, 'electrical', 'medium', 'in_progress', NULL, NULL, '0.00', '0.00', '24.00', '631.16', '2025-10-08', NULL, '2025-10-23 06:28:22', '', '1', '2025-10-22 15:21:00', '2025-10-23 14:37:55', NULL, NULL);
INSERT INTO `projects` VALUES ('4', 'nireas', '1', '2', 'ΝΗΡΕΑΣ', 'ΜΕΤΑΦΟΡΑ ΜΠΑΛΚΟΝΟΠΟΡΤΑΣ ΣΕ ΔΩΜΑΤΙΟ ΙΣΟΓΕΙΟΥ', NULL, 'electrical', 'medium', 'completed', NULL, NULL, '0.00', '0.00', '24.00', '0.00', '2025-10-15', '2025-10-30', NULL, NULL, '2', '2025-10-23 15:33:44', '2025-10-30 17:35:05', NULL, NULL);
INSERT INTO `projects` VALUES ('5', 'agia-semni', '3', '3', 'ΑΓΙΑ ΣΕΜΝΗ', 'ΚΑΤΑΣΚΕΥΗ ΥΠΟΣΤΑΘΜΟΥ', NULL, 'electrical', 'medium', 'completed', NULL, NULL, '0.00', '0.00', '24.00', '0.00', '2024-08-09', '2025-10-30', NULL, '', '2', '2025-10-29 16:14:07', '2025-10-30 11:36:31', NULL, NULL);
INSERT INTO `projects` VALUES ('6', 'apothiki-a-themeliakes-geioseis', '4', '2', 'ΑΠΟΘΗΚΗ Α\'-ΘΕΜΕΛΙΑΚΕΣ ΓΕΙΩΣΕΙΣ', '', NULL, 'electrical', 'medium', 'in_progress', NULL, NULL, '0.00', '0.00', '24.00', '0.00', '2025-05-27', '2025-11-12', NULL, NULL, '2', '2025-11-12 15:37:08', '2025-11-13 06:34:09', NULL, NULL);
INSERT INTO `projects` VALUES ('8', 'ksenodocheio', '5', '2', 'ΞΕΝΟΔΟΧΕΙΟ', 'ΤΡΠΟΠΟΙΗΣΕΙΣ - ΑΝΑΚΑΙΝΙΣΕΙΣ ΔΩΜΑΤΙΩΝ +ΚΟΙΝΟΧΡΗΣΤΩΝ ΧΩΡΩΝ', NULL, 'electrical', 'medium', 'in_progress', NULL, NULL, '0.00', '0.00', '24.00', '0.00', NULL, NULL, NULL, NULL, '2', '2025-11-12 20:02:33', '2025-11-12 18:05:13', NULL, NULL);
INSERT INTO `projects` VALUES ('9', 'oikia-metaksa-analipsi-irakleioy', '1', '2', 'οικια Μεταξα ,Αναληψη ηρακλειου', '', NULL, 'electrical', 'medium', '', NULL, NULL, '0.00', '0.00', '24.00', '0.00', '2025-11-18', '2025-11-18', NULL, NULL, '2', '2025-11-19 08:28:09', '2025-11-19 06:28:09', NULL, NULL);


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


-- Table: role_permissions
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `role_permissions` VALUES ('93', '15', '124', '2025-11-13 13:46:52');
INSERT INTO `role_permissions` VALUES ('94', '15', '126', '2025-11-13 13:46:52');
INSERT INTO `role_permissions` VALUES ('95', '15', '125', '2025-11-13 13:46:52');
INSERT INTO `role_permissions` VALUES ('96', '15', '123', '2025-11-13 13:46:52');
INSERT INTO `role_permissions` VALUES ('97', '15', '18', '2025-11-13 13:46:52');
INSERT INTO `role_permissions` VALUES ('98', '15', '19', '2025-11-13 13:46:52');
INSERT INTO `role_permissions` VALUES ('99', '15', '17', '2025-11-13 13:46:52');
INSERT INTO `role_permissions` VALUES ('100', '1', '129', '2025-11-19 12:48:41');
INSERT INTO `role_permissions` VALUES ('101', '1', '128', '2025-11-19 12:48:41');
INSERT INTO `role_permissions` VALUES ('102', '1', '127', '2025-11-19 12:48:41');
INSERT INTO `role_permissions` VALUES ('103', '1', '130', '2025-11-19 12:48:41');


-- Table: roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0 COMMENT 'System roles cannot be deleted',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` VALUES ('1', 'admin', 'Διαχειριστής', 'Πλήρη δικαιώματα σε όλες τις λειτουργίες', '1', '2025-11-06 09:00:01', '2025-11-07 10:10:16');
INSERT INTO `roles` VALUES ('2', 'supervisor', 'Επόπτης', 'Δικαιώματα διαχείρισης έργων και εργασιών', '0', '2025-11-06 09:00:01', '2025-11-06 09:00:01');
INSERT INTO `roles` VALUES ('3', 'technician', 'Τεχνικός', 'Βασικά δικαιώματα προβολής και επεξεργασίας εργασιών', '0', '2025-11-06 09:00:01', '2025-11-06 09:00:01');
INSERT INTO `roles` VALUES ('15', 'maintenance_tech', 'Τεχνικός Υποσταθμών', 'Μπορει να ανεβασει μια συντηρηση και να την επεξεργαστεί', '0', '2025-11-07 10:21:52', '2025-11-07 10:21:52');
INSERT INTO `roles` VALUES ('16', 'assistant_tech', 'Βοηθός Τεχνικού', 'Βοηθός Τεχνικού', '0', '2025-11-21 09:43:35', '2025-11-21 09:43:35');


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
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` VALUES ('1', 'smtp_host', 'smtp.titan.email', 'string', 'SMTP Server Host', NULL, '2025-11-06 11:15:31');
INSERT INTO `settings` VALUES ('2', 'smtp_port', '465', 'integer', 'SMTP Server Port', NULL, '2025-11-06 11:15:31');
INSERT INTO `settings` VALUES ('3', 'smtp_username', 'notifications@ecowatt.gr', 'string', 'SMTP Username', NULL, '2025-11-06 11:15:31');
INSERT INTO `settings` VALUES ('4', 'smtp_password', '', 'string', 'SMTP Password (encrypted)', NULL, '2025-11-06 11:15:31');
INSERT INTO `settings` VALUES ('5', 'smtp_encryption', 'ssl', 'string', 'SMTP Encryption (tls/ssl)', NULL, '2025-11-06 11:15:31');
INSERT INTO `settings` VALUES ('6', 'smtp_from_email', 'notifications@ecowatt.gr', 'string', 'From Email Address', NULL, '2025-11-06 11:15:31');
INSERT INTO `settings` VALUES ('7', 'smtp_from_name', 'ECOWATT CRM', 'string', 'From Name', NULL, '2025-11-06 11:15:31');
INSERT INTO `settings` VALUES ('8', 'email_enabled', '1', 'boolean', 'Enable/Disable Email Functionality', NULL, '2025-11-06 11:15:31');
INSERT INTO `settings` VALUES ('9', 'company_name', 'Σφακιανάκης Κώστας & ΣΙΑ Ο.Ε', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('10', 'company_address', 'ΝΙΚΟΛΑΟΥ ΚΟΚΚΙΝΟΥ 3', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('11', 'company_phone', '2811 113851', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('12', 'company_email', 'info@ecowatt-energy.gr', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('13', 'company_tax_id', '999082634', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('14', 'company_website', 'https://ecowatt-energy.gr', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('15', 'default_vat_rate', '24', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('16', 'display_vat_notes', '1', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('17', 'prices_include_vat', '0', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('18', 'currency', 'EUR', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('19', 'currency_symbol', '€', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('20', 'date_format', 'd/m/Y', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('21', 'items_per_page', '20', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('22', 'company_logo', 'uploads/company/logo_1762502720_690da840b380d.jpg', 'string', NULL, NULL, '2025-11-07 08:05:20');
INSERT INTO `settings` VALUES ('23', 'company_display_name', 'Ecowatt Energy', 'string', 'Διακριτικός Τίτλος Εταιρίας (αν είναι κενό χρησιμοποιείται το HandyCRM)', NULL, '2025-11-07 13:29:12');
INSERT INTO `settings` VALUES ('52', 'maintenance_price_1_transformer', '400', 'decimal', NULL, NULL, '2025-11-13 13:23:34');
INSERT INTO `settings` VALUES ('53', 'maintenance_price_2_transformers', '600', 'decimal', NULL, NULL, '2025-11-13 13:23:27');
INSERT INTO `settings` VALUES ('54', 'maintenance_price_3_transformers', '900', 'decimal', NULL, NULL, '2025-11-13 13:23:27');


-- Table: settings_backup
DROP TABLE IF EXISTS `settings_backup`;
CREATE TABLE `settings_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','decimal','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `display_vat_notes` tinyint(1) DEFAULT 1 COMMENT 'Show VAT notes next to prices',
  `prices_include_vat` tinyint(1) DEFAULT 0 COMMENT 'Whether prices include VAT',
  `default_vat_rate` decimal(5,2) DEFAULT 24.00 COMMENT 'Default VAT rate percentage'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings_backup` VALUES ('1', 'company_name', 'ΣΦΑΚΙΑΝΑΚΗΣ Κ. ΚΑΙ ΣΙΑ Ο.Ε', 'string', NULL, NULL, '2025-10-23 11:20:41', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('2', 'company_address', 'ΝΙΚΟΛΑΟΥ ΚΟΚΚΙΝΟΥ 3', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('3', 'company_phone', '2811 113851', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('4', 'company_email', 'info@ecowatt-energy.gr', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('5', 'company_tax_id', '999082634', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('6', 'company_website', 'https://ecowatt-energy.gr', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('7', 'default_vat_rate', '24', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('8', 'display_vat_notes', '1', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('9', 'prices_include_vat', '0', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('10', 'currency', 'EUR', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('11', 'currency_symbol', '€', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('12', 'date_format', 'd/m/Y', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('13', 'items_per_page', '20', 'string', NULL, NULL, '2025-10-23 11:10:03', '1', '0', '24.00');
INSERT INTO `settings_backup` VALUES ('27', 'company_logo', 'uploads/company/logo_1761218367_68fa0f3f5c17e.jpg', 'string', NULL, NULL, '2025-10-23 11:19:27', '1', '0', '24.00');


-- Table: smtp_settings
DROP TABLE IF EXISTS `smtp_settings`;
CREATE TABLE `smtp_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(255) NOT NULL,
  `port` int(11) NOT NULL DEFAULT 587,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `encryption` enum('tls','ssl','none') DEFAULT 'tls',
  `from_email` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `smtp_settings` VALUES ('1', 'smtp.titan.email', '465', 'notifications@ecowatt.gr', '1q2w3e&*(', 'ssl', 'notifications@ecowatt.gr', 'Ecowatt CRM', '1', '2025-11-06 11:45:48', '2025-11-07 12:42:03');


-- Table: task_labor
DROP TABLE IF EXISTS `task_labor`;
CREATE TABLE `task_labor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `technician_name` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
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
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_task_labor_paid_at` (`paid_at`),
  KEY `idx_technician_id` (`technician_id`),
  KEY `idx_is_temporary` (`is_temporary`),
  KEY `idx_task_labor_tech_paid` (`technician_id`,`paid_at`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `fk_task_labor_role` (`role_id`),
  CONSTRAINT `fk_task_labor_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=273 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `task_labor` VALUES ('4', '2', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '0.00', '00:00:00', '00:00:00', NULL, '0.00', '5.00', '0.00', '', '0000-00-00', '2025-10-23 05:59:44', '2025-10-23 05:59:44', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('5', '3', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-10-23 06:00:15', '2025-10-23 11:24:04', '1', '2025-10-23 11:24:04', NULL, NULL);
INSERT INTO `task_labor` VALUES ('9', '4', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '0.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '0.00', '', '0000-00-00', '2025-10-23 06:07:42', '2025-10-23 06:07:42', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('17', '5', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '6.00', '08:00:00', '14:00:00', NULL, '0.00', '10.00', '60.00', '', '0000-00-00', '2025-10-23 06:18:51', '2025-11-20 07:36:21', '1', '2025-10-23 11:21:28', NULL, NULL);
INSERT INTO `task_labor` VALUES ('20', '6', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-10-23 11:35:21', '2025-10-23 11:35:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('21', '6', '0', '7', 'Κόστας Ζάρος', '3', '0', '10.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '100.00', '', '0000-00-00', '2025-10-23 11:35:21', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('22', '7', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-23 11:36:09', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('23', '7', '0', '3', 'Μανώλης Πετράκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-23 11:36:09', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('28', '10', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-23 14:06:31', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('47', '12', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '5.42', '10:35:00', '16:00:00', NULL, '0.00', '12.00', '65.04', '', '0000-00-00', '2025-10-24 05:33:57', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('48', '12', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '5.42', '10:35:00', '16:00:00', NULL, '0.00', '8.00', '43.36', '', '0000-00-00', '2025-10-24 05:33:57', '2025-10-24 05:33:57', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('49', '13', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-10-24 05:37:37', '2025-10-24 05:37:37', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('50', '13', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-10-24 05:37:37', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('51', '14', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-10-24 05:39:52', '2025-10-24 05:39:52', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('52', '14', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-10-24 05:39:52', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('59', '15', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.21', '65.68', '', '0000-00-00', '2025-10-24 06:03:18', '2025-10-24 06:03:18', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('60', '15', '0', '3', 'Μανώλης Πετράκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-24 06:03:18', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('61', '15', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-10-24 06:03:18', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('63', '17', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-10-24 09:47:30', '2025-10-24 09:47:30', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('65', '18', '0', '7', 'Κόστας Ζάρος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-24 09:48:35', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('66', '20', '0', '3', 'Μανώλης Πετράκης', '3', '0', '5.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '50.00', '', '0000-00-00', '2025-10-24 11:30:22', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('67', '8', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '2.50', '07:00:00', '09:30:00', NULL, '0.00', '12.00', '30.00', '', '0000-00-00', '2025-10-29 12:42:38', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('77', '11', '0', '7', 'Κόστας Ζάρος', '3', '0', '4.00', '08:00:00', '12:00:00', NULL, '0.00', '10.00', '40.00', '', '0000-00-00', '2025-10-29 12:56:17', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('78', '11', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '4.00', '08:00:00', '12:00:00', NULL, '0.00', '10.00', '40.00', '', '0000-00-00', '2025-10-29 12:56:17', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('79', '11', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '4.00', '08:00:00', '12:00:00', NULL, '0.00', '12.00', '48.00', '', '0000-00-00', '2025-10-29 12:56:17', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('84', '21', '0', '7', 'Κώστας Ζάρος', '3', '0', '4.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '40.00', '', '0000-00-00', '2025-10-30 17:33:21', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('85', '21', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '4.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '40.00', '', '0000-00-00', '2025-10-30 17:33:21', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('86', '23', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-10-30 18:09:39', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('87', '23', '0', '7', 'Κώστας Ζάρος', '3', '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-30 18:09:39', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('88', '23', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-10-30 18:09:39', '2025-10-30 18:09:39', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('89', '23', '0', '3', 'Μανώλης Πετράκης', '3', '0', '4.00', '12:00:00', '16:00:00', NULL, '0.00', '10.00', '40.00', '', '0000-00-00', '2025-10-30 18:09:39', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('90', '24', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-30 18:14:38', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('91', '24', '0', '7', 'Κώστας Ζάρος', '3', '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-10-30 18:14:38', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('92', '24', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '8.00', '08:00:00', '16:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-10-30 18:14:38', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('93', '25', '0', '7', 'Κώστας Ζάρος', '3', '0', '3.00', '12:00:00', '15:00:00', NULL, '0.00', '10.00', '30.00', '', '0000-00-00', '2025-10-30 18:23:16', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('94', '25', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '15.00', '12:00:00', '03:00:00', NULL, '0.00', '10.00', '150.00', '', '0000-00-00', '2025-10-30 18:23:16', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('97', '26', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '2.50', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '25.00', '', '0000-00-00', '2025-11-12 13:21:56', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('98', '26', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '2.50', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '20.00', '', '0000-00-00', '2025-11-12 13:21:56', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('99', '27', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '5.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '40.00', '', '0000-00-00', '2025-11-12 13:27:11', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('100', '27', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '5.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '50.00', '', '0000-00-00', '2025-11-12 13:27:11', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('110', '32', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '2.50', '09:00:00', '11:30:00', NULL, '0.00', '25.00', '62.50', '', '0000-00-00', '2025-11-19 06:37:31', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('113', '31', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '2.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '20.00', '', '0000-00-00', '2025-11-19 07:05:54', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('114', '31', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '2.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '24.00', '', '0000-00-00', '2025-11-19 07:05:54', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('115', '30', '0', '6', 'Αγης Καλτσίδης', '3', '0', '6.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '60.00', '', '0000-00-00', '2025-11-19 07:07:12', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('116', '30', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '6.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '48.00', '', '0000-00-00', '2025-11-19 07:07:12', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('121', '28', '0', '6', 'Αγης Καλτσίδης', '3', '0', '7.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '70.00', '', '0000-00-00', '2025-11-19 07:15:25', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('122', '28', '0', '7', 'Κώστας Ζάρος', '3', '0', '7.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '70.00', '', '0000-00-00', '2025-11-19 07:15:25', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('123', '28', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '7.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '70.00', '', '0000-00-00', '2025-11-19 07:15:25', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('124', '28', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '5.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '60.00', '', '0000-00-00', '2025-11-19 07:15:25', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('126', '33', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '6.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '72.00', '', '0000-00-00', '2025-11-19 07:31:37', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('131', '29', '0', '7', 'Κώστας Ζάρος', '3', '0', '7.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '70.00', '', '0000-00-00', '2025-11-19 07:36:33', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('132', '29', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '7.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '70.00', '', '0000-00-00', '2025-11-19 07:36:33', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('133', '29', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '7.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '56.00', '', '0000-00-00', '2025-11-19 07:36:33', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('134', '29', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '4.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '48.00', '', '0000-00-00', '2025-11-19 07:36:33', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('140', '36', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:11:10', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('141', '36', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:11:10', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('142', '36', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:11:10', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('143', '37', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:12:27', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('144', '37', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:12:27', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('145', '38', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:14:15', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('146', '38', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:14:15', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('153', '40', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:26:23', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('154', '40', '0', '7', 'Κώστας Ζάρος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:26:23', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('155', '40', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:26:23', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('156', '41', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:27:35', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('157', '41', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:27:35', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('158', '41', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:27:35', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('162', '43', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:29:48', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('163', '43', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:29:48', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('164', '43', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:29:48', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('165', '43', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:29:48', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('166', '44', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:31:55', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('167', '44', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:31:55', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('168', '44', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:31:55', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('169', '42', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:32:27', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('170', '42', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:32:27', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('171', '42', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:32:27', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('172', '45', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:34:43', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('173', '45', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:34:43', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('174', '46', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:39:36', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('175', '46', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 13:39:36', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('176', '46', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 13:39:36', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('183', '49', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 14:06:08', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('184', '49', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 14:06:08', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('185', '49', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-19 14:06:08', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('186', '50', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 14:08:25', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('187', '50', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-19 14:08:25', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('188', '39', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:09:47', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('189', '39', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-11-20 06:09:47', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('190', '39', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:09:47', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('191', '39', '0', '7', 'Κώστας Ζάρος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:09:47', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('192', '39', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-20 06:09:47', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('193', '39', '0', '3', 'Μανώλης Πετράκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:09:47', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('194', '35', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:10:44', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('195', '35', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-20 06:10:44', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('196', '35', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-11-20 06:10:44', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('197', '35', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-20 06:10:44', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('198', '34', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:11:02', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('199', '34', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-20 06:11:02', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('200', '51', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:14:53', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('201', '51', '0', '5', 'Δημήτρης Βιδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-20 06:14:53', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('202', '51', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:14:53', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('203', '47', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:23:18', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('204', '47', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:23:18', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('205', '47', '0', '3', 'Μανώλης Πετράκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:23:18', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('206', '47', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-20 06:23:18', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('207', '48', '0', '2', 'Νίκος Νικολουδάκης', '1', '0', '5.50', '08:00:00', '13:30:00', NULL, '0.00', '12.00', '66.00', '', '0000-00-00', '2025-11-20 06:25:01', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('208', '48', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '5.50', '08:00:00', '13:30:00', NULL, '0.00', '8.00', '44.00', '', '0000-00-00', '2025-11-20 06:25:01', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('209', '52', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:27:11', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('210', '52', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-20 06:27:11', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('211', '52', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:27:11', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('214', '53', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:28:08', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('215', '53', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:28:08', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('216', '54', '0', '4', 'Σπύρος Παπαδόπουλος', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-20 06:29:13', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('217', '54', '0', '6', 'Αγης Καλτσίδης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:29:13', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('218', '54', '0', '8', 'Μηνάς Ζαχαριουδάκης', '3', '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-20 06:29:13', '2025-11-20 07:36:21', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('223', '56', '0', '2', 'Νίκος Νικολουδάκης', NULL, '0', '4.25', '08:00:00', '12:15:00', NULL, '0.00', '12.00', '51.00', '', '0000-00-00', '2025-11-22 13:16:01', '2025-11-22 13:16:01', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('224', '55', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:19:00', '2025-11-22 13:19:00', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('225', '55', '0', '8', 'Μηνάς Ζαχαριουδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:19:00', '2025-11-22 13:19:00', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('226', '55', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:19:00', '2025-11-22 13:19:00', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('230', '57', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:20:34', '2025-11-22 13:20:34', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('231', '57', '0', '8', 'Μηνάς Ζαχαριουδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:20:34', '2025-11-22 13:20:34', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('232', '57', '0', '2', 'Νίκος Νικολουδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-11-22 13:20:34', '2025-11-22 13:20:34', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('238', '58', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:24:17', '2025-11-22 13:24:17', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('239', '58', '0', '7', 'Κώστας Ζάρος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:24:17', '2025-11-22 13:24:17', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('240', '58', '0', '8', 'Μηνάς Ζαχαριουδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:24:17', '2025-11-22 13:24:17', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('241', '58', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:24:17', '2025-11-22 13:24:17', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('242', '58', '0', '2', 'Νίκος Νικολουδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-11-22 13:24:17', '2025-11-22 13:24:17', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('243', '58', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '3.00', '13:00:00', '16:00:00', NULL, '0.00', '8.00', '24.00', '', '0000-00-00', '2025-11-22 13:24:17', '2025-11-22 13:24:17', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('244', '58', '0', '3', 'Μανώλης Πετράκης', NULL, '0', '3.00', '13:00:00', '16:00:00', NULL, '0.00', '10.00', '30.00', '', '0000-00-00', '2025-11-22 13:24:17', '2025-11-22 13:24:17', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('245', '59', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:33:50', '2025-11-22 13:33:50', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('246', '59', '0', '3', 'Μανώλης Πετράκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:33:50', '2025-11-22 13:33:50', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('247', '59', '0', '8', 'Μηνάς Ζαχαριουδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:33:50', '2025-11-22 13:33:50', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('248', '59', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:33:50', '2025-11-22 13:33:50', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('249', '59', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:33:50', '2025-11-22 13:33:50', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('250', '59', '0', '2', 'Νίκος Νικολουδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '12.00', '96.00', '', '0000-00-00', '2025-11-22 13:33:50', '2025-11-22 13:33:50', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('251', '60', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:35:27', '2025-11-22 13:35:27', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('252', '60', '0', '8', 'Μηνάς Ζαχαριουδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:35:27', '2025-11-22 13:35:27', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('253', '60', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:35:27', '2025-11-22 13:35:27', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('254', '60', '0', '5', 'Δημήτρης Βιδάκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:35:27', '2025-11-22 13:35:27', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('255', '60', '0', '3', 'Μανώλης Πετράκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:35:27', '2025-11-22 13:35:27', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('256', '61', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:38:32', '2025-11-22 13:38:32', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('257', '61', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:38:32', '2025-11-22 13:38:32', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('258', '61', '0', '3', 'Μανώλης Πετράκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:38:32', '2025-11-22 13:38:32', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('259', '61', '0', '7', 'Κώστας Ζάρος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:38:32', '2025-11-22 13:38:32', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('260', '62', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:48:49', '2025-11-22 13:48:49', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('261', '62', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:48:49', '2025-11-22 13:48:49', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('262', '63', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:51:56', '2025-11-22 13:51:56', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('263', '63', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:51:56', '2025-11-22 13:51:56', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('264', '63', '0', '3', 'Μανώλης Πετράκης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:51:56', '2025-11-22 13:51:56', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('265', '63', '0', '7', 'Κώστας Ζάρος', NULL, '0', '15.50', '00:30:00', '16:00:00', NULL, '0.00', '10.00', '155.00', '', '0000-00-00', '2025-11-22 13:51:56', '2025-11-22 13:51:56', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('266', '64', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:53:14', '2025-11-22 13:53:14', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('267', '64', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:53:14', '2025-11-22 13:53:14', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('268', '64', '0', '7', 'Κώστας Ζάρος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:53:14', '2025-11-22 13:53:14', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('269', '65', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 13:54:28', '2025-11-22 13:54:28', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('270', '65', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 13:54:28', '2025-11-22 13:54:28', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('271', '66', '0', '6', 'Αγης Καλτσίδης', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '10.00', '80.00', '', '0000-00-00', '2025-11-22 14:01:25', '2025-11-22 14:01:25', NULL, NULL, NULL, NULL);
INSERT INTO `task_labor` VALUES ('272', '66', '0', '4', 'Σπύρος Παπαδόπουλος', NULL, '0', '8.00', '00:00:00', '00:00:00', NULL, '0.00', '8.00', '64.00', '', '0000-00-00', '2025-11-22 14:01:25', '2025-11-22 14:01:25', NULL, NULL, NULL, NULL);


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
) ENGINE=InnoDB AUTO_INCREMENT=617 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
INSERT INTO `task_materials` VALUES ('54', '17', 'Καλώδιο HRNF καουτσούκ 4x1', 'Καλώδιο HRNF καουτσούκ 4x1', 'τεμ', 'τεμ', '5.18', '10.00', '51.80', '383', '2025-10-24 09:47:30', '2025-10-24 09:47:30');
INSERT INTO `task_materials` VALUES ('56', '18', 'Καλώδιο Liycy 4x1.5mm2', 'Καλώδιο Liycy 4x1.5mm2', 'μ', 'μ', '3.24', '10.00', '32.40', '363', '2025-10-24 09:48:35', '2025-10-24 09:48:35');
INSERT INTO `task_materials` VALUES ('57', '20', 'Εσχάρα διέλευσης καλωδιώσεων 100x60', 'Εσχάρα διέλευσης καλωδιώσεων 100x60', 'τεμ', 'τεμ', '19.00', '34.00', '646.00', '246', '2025-10-24 11:30:22', '2025-10-24 11:30:22');
INSERT INTO `task_materials` VALUES ('86', '11', 'SuperFlex Κίτρινο Φ20', 'SuperFlex Κίτρινο Φ20', 'τεμ', 'τεμ', '0.38', '13.00', '4.94', '104', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('87', '11', 'SuperFlex Κίτρινο Φ16', 'SuperFlex Κίτρινο Φ16', 'μ', 'μ', '0.25', '9.00', '2.25', '105', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('88', '11', 'Καλώδιο ΝΥΑ 1x1.5', 'Καλώδιο ΝΥΑ 1x1.5', 'μέτρα', 'μέτρα', '55.47', '33.00', '1830.51', '5', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('89', '11', 'Καλώδιο ΝΥΜ 3x2.5', 'Καλώδιο ΝΥΜ 3x2.5', 'μ', 'μ', '1.70', '4.00', '6.80', '2', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('90', '11', 'Καλώδιο ΝΥΜ 3x1.5', 'Καλώδιο ΝΥΜ 3x1.5', 'μ', 'μ', '1.20', '8.00', '9.60', '1', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('91', '11', 'ετοιμη λασπη κτισιματος', 'ετοιμη λασπη κτισιματος', 'σακί', 'σακί', '5.00', '0.50', '2.50', '113', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('92', '11', 'κουτι διακλαδωσεως χωνευτο 7,5*7,5', 'κουτι διακλαδωσεως χωνευτο 7,5*7,5', 'τεμ', 'τεμ', '555.00', '2.00', '1110.00', '110', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('93', '11', 'κουτι διακλαδωσεως χωνευτο στρογγυλο', 'κουτι διακλαδωσεως χωνευτο στρογγυλο', 'τεμ', 'τεμ', '555.00', '2.00', '1110.00', '109', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('94', '11', 'κουτι διακοποτου κουβιδης', 'κουτι διακοποτου κουβιδης', 'τεμάχια', 'τεμάχια', '0.20', '3.00', '0.60', NULL, '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('95', '11', 'καλωδιο ΝΥΛ 2*0,75', 'καλωδιο ΝΥΛ 2*0,75', 'μέτρα', 'μέτρα', '3.78', '2.00', '7.56', '428', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('96', '11', 'fix ring Φ20 διπλο', 'fix ring Φ20 διπλο', 'τεμ', 'τεμ', '0.27', '15.00', '4.05', '427', '2025-10-29 12:56:17', '2025-10-29 12:56:17');
INSERT INTO `task_materials` VALUES ('534', '21', 'Καλώδιο ΝΥΑ 1x2.5', 'Καλώδιο ΝΥΑ 1x2.5', 'μ', 'μ', '0.55', '6.00', '3.30', '6', '2025-10-30 17:33:21', '2025-10-30 17:33:21');
INSERT INTO `task_materials` VALUES ('535', '21', 'Καλώδιο ΝΥΑ 1x1.5', 'Καλώδιο ΝΥΑ 1x1.5', 'μ', 'μ', '0.35', '4.00', '1.40', '5', '2025-10-30 17:33:21', '2025-10-30 17:33:21');
INSERT INTO `task_materials` VALUES ('536', '21', 'διακοπτης διπλος aleretour χωνευτος', 'διακοπτης διπλος aleretour χωνευτος', 'τεμ', 'τεμ', '0.20', '2.00', '0.40', '430', '2025-10-30 17:33:21', '2025-10-30 17:33:21');
INSERT INTO `task_materials` VALUES ('537', '21', 'ντουι ε27', 'ντουι ε27', 'τεμ', 'τεμ', '1.02', '1.00', '1.02', '429', '2025-10-30 17:33:21', '2025-10-30 17:33:21');
INSERT INTO `task_materials` VALUES ('538', '21', 'Λάμπα LED E27 9 W', 'Λάμπα LED E27 9 W', 'τεμ', 'τεμ', '1.50', '1.00', '1.50', '42', '2025-10-30 17:33:21', '2025-10-30 17:33:21');
INSERT INTO `task_materials` VALUES ('539', '21', 'πριζα σουκο χωνευτη', 'πριζα σουκο χωνευτη', 'τεμ', 'τεμ', '111.00', '3.00', '333.00', '431', '2025-10-30 17:33:21', '2025-10-30 17:33:21');
INSERT INTO `task_materials` VALUES ('540', '21', 'Λάμπα LED GU10 5W', 'Λάμπα LED GU10 5W', 'τεμ', 'τεμ', '1.80', '2.00', '3.60', '77', '2025-10-30 17:33:21', '2025-10-30 17:33:21');
INSERT INTO `task_materials` VALUES ('541', '21', 'Φωτιστικό σποτ χωνευτό GU10', 'Φωτιστικό σποτ χωνευτό GU10', 'τεμ', 'τεμ', '5.20', '2.00', '10.40', '76', '2025-10-30 17:33:21', '2025-10-30 17:33:21');
INSERT INTO `task_materials` VALUES ('542', '22', 'Πλαστικός σωλήνας PE GEOSUB Φ63', 'Πλαστικός σωλήνας PE GEOSUB Φ63', 'μ', 'μ', '8.21', '90.00', '738.90', '235', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('543', '22', 'Ηλεκτρόδιο Φ15', 'Ηλεκτρόδιο Φ15', 'τεμ', 'τεμ', '50.00', '6.00', '300.00', '439', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('544', '22', 'Χαλκός 50mm', 'Χαλκός 50mm', 'τεμ', 'τεμ', '10.80', '40.00', '432.00', '440', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('545', '22', 'Καλωδιο Μεσής Τάσης 1Χ70mm', 'Καλωδιο Μεσής Τάσης 1Χ70mm', 'μ', 'μ', '22.00', '180.00', '3960.00', '432', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('546', '22', 'Τραβέρσα', 'Τραβέρσα', 'τεμ', 'τεμ', '100.00', '1.00', '100.00', '441', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('547', '22', 'Καπάκι Σκάφης + Σκάφη', 'Καπάκι Σκάφης + Σκάφη', 'τεμ', 'τεμ', '200.00', '1.00', '200.00', '503', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('548', '22', 'Ακροκιβώτιο Εξωτερικό', 'Ακροκιβώτιο Εξωτερικό', 'τεμ', 'τεμ', '120.00', '4.00', '480.00', '443', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('549', '22', 'Ακροκιβώτιο Εσωτερικό', 'Ακροκιβώτιο Εσωτερικό', 'τεμ', 'τεμ', '100.00', '10.00', '1000.00', '442', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('550', '22', 'Κολάρο 3/4 με Λάστιχο', 'Κολάρο 3/4 με Λάστιχο', 'τεμ', 'τεμ', '0.00', '6.00', '0.00', '444', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('551', '22', 'ΝΥΥ 3Χ120+70mm', 'ΝΥΥ 3Χ120+70mm', 'μ', 'μ', '103.68', '75.00', '7776.00', '445', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('552', '22', 'ΝΥΥ 1Χ120mm', 'ΝΥΥ 1Χ120mm', 'μ', 'μ', '28.08', '75.00', '2106.00', '446', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('553', '22', 'Σχάρα 200/110', 'Σχάρα 200/110', 'μ', 'μ', '25.00', '2.00', '50.00', '447', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('554', '22', 'Σχάρα 300/60', 'Σχάρα 300/60', 'μ', 'μ', '31.00', '7.00', '217.00', '448', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('555', '22', 'Φωτιστικό Τ8/2Χ150', 'Φωτιστικό Τ8/2Χ150', 'τεμ', 'τεμ', '25.00', '3.00', '75.00', '449', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('556', '22', 'Λάμπα Τ8/150', 'Λάμπα Τ8/150', 'τεμ', 'τεμ', '5.00', '6.00', '30.00', '504', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('557', '22', 'Λιπαντικό Καλωδίων Heavy', 'Λιπαντικό Καλωδίων Heavy', 'τεμ', 'τεμ', '0.00', '1.00', '0.00', '451', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('558', '22', 'Ταινία Χαλκού 30Χ3', 'Ταινία Χαλκού 30Χ3', 'μ', 'μ', '30.00', '33.00', '990.00', '452', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('559', '22', 'Διακλαδωτήρας Τ/T 50x50x2,5: 2 Ταινίες 30x4|3 Πλάκες|St/Tzn', 'Διακλαδωτήρας Τ/T 50x50x2,5: 2 Ταινίες 30x4|3 Πλάκες|St/Tzn', 'τεμ', 'τεμ', '0.00', '14.00', '0.00', '437', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('560', '22', 'Διακλαδωτήρας Τ/A 60x60x2,5: Ταινίας 30x4 με Αγωγό Φ8-12|3 Πλάκες|St/Tzn', 'Διακλαδωτήρας Τ/A 60x60x2,5: Ταινίας 30x4 με Αγωγό Φ8-12|3 Πλάκες|St/Tzn', 'τεμ', 'τεμ', '0.00', '6.99', '0.00', '436', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('561', '22', 'Στήριγαμ Ταινίας Χάλκινα', 'Στήριγαμ Ταινίας Χάλκινα', 'τεμ', 'τεμ', '0.00', '70.00', '0.00', '453', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('562', '22', 'Διακόπτης Forix', 'Διακόπτης Forix', 'τεμ', 'τεμ', '15.00', '3.00', '45.00', '454', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('563', '22', 'Πρίζα Σούκο Forix', 'Πρίζα Σούκο Forix', 'τεμ', 'τεμ', '15.00', '3.00', '45.00', '455', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('564', '22', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ20', 'Σωλήνας ηλ.γραμμών πλαστικός ευθύς βαρέως τύπου ενδ.τύπου Condur PVC Φ20', 'μ', 'μ', '5.94', '24.00', '142.56', '227', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('565', '22', 'ΝΥΛ 3Χ1,5mm', 'ΝΥΛ 3Χ1,5mm', 'μ', 'μ', '3.13', '12.00', '37.56', '456', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('566', '22', 'ΝΥΛ 5Χ1,5mm', 'ΝΥΛ 5Χ1,5mm', 'μ', 'μ', '4.10', '10.00', '41.00', '457', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('567', '22', 'σπιραλ conflex Φ20', 'σπιραλ conflex Φ20', 'μ', 'μ', '5.94', '4.00', '23.76', '112', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('568', '22', 'Καπάκι Εσχάρας 200x60', 'Καπάκι Εσχάρας 200x60', 'μ', 'μ', '9.00', '2.00', '18.00', '251', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('569', '22', 'Καπάκι Εσχάρας 300x60', 'Καπάκι Εσχάρας 300x60', 'μ', 'μ', '14.00', '3.00', '42.00', '252', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('570', '22', 'ΝΥΛ 4Χ1,5mm', 'ΝΥΛ 4Χ1,5mm', 'μ', 'μ', '0.00', '10.00', '0.00', '458', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('571', '22', 'Βάση Πίνακα Μ.Τ', 'Βάση Πίνακα Μ.Τ', 'τεμ', 'τεμ', '0.00', '1.00', '0.00', '460', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('572', '22', 'Πίνακας Προστασίας Μ/Σ', 'Πίνακας Προστασίας Μ/Σ', 'τεμ', 'τεμ', '0.00', '1.00', '0.00', '461', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('573', '22', 'Μούφες', 'Μούφες', 'τεμ', 'τεμ', '0.00', '10.03', '0.00', '463', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('574', '22', 'Κολάρα', 'Κολάρα', 'τεμ', 'τεμ', '0.00', '10.00', '0.00', '464', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('575', '22', 'ΝΥΥ 1Χ150 Αλουμινίου', 'ΝΥΥ 1Χ150 Αλουμινίου', 'μ', 'μ', '0.00', '45.00', '0.00', '506', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('576', '22', 'OFlex 120mm2', 'OFlex 120mm2', 'μ', 'μ', '0.00', '93.00', '0.00', '507', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('577', '22', 'Κως 120mm2', 'Κως 120mm2', 'τεμ', 'τεμ', '0.00', '36.00', '0.00', '508', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('578', '22', 'Διακόπτης Ισχύος 3P/400A', 'Διακόπτης Ισχύος 3P/400A', 'τεμ', 'τεμ', '0.00', '1.00', '0.00', '509', '2025-10-31 05:18:56', '2025-10-31 05:18:56');
INSERT INTO `task_materials` VALUES ('579', '27', 'νοβοπανοβιδες  5*80', 'νοβοπανοβιδες  5*80', 'κουτί', 'κουτί', '8.25', '20.00', '165.00', '510', '2025-11-12 13:27:11', '2025-11-12 13:27:11');
INSERT INTO `task_materials` VALUES ('587', '32', 'Λάμπα LED E27 9 W', 'Λάμπα LED E27 9 W', 'τεμ', 'τεμ', '1.50', '3.00', '4.50', '42', '2025-11-19 06:37:31', '2025-11-19 06:37:31');
INSERT INTO `task_materials` VALUES ('588', '32', 'Λάμπα LED GU10 5W', 'Λάμπα LED GU10 5W', 'τεμ', 'τεμ', '1.80', '1.00', '1.80', '77', '2025-11-19 06:37:31', '2025-11-19 06:37:31');
INSERT INTO `task_materials` VALUES ('589', '32', 'θερμοστατης χωρου siemens απλος', 'θερμοστατης χωρου siemens απλος', 'τεμ', 'τεμ', '19.00', '1.00', '19.00', '511', '2025-11-19 06:37:31', '2025-11-19 06:37:31');
INSERT INTO `task_materials` VALUES ('590', '32', 'λαμπα led opalina φ110/18W/4000K', 'λαμπα led opalina φ110/18W/4000K', 'τεμ', 'τεμ', '1.00', '1.00', '1.00', '512', '2025-11-19 06:37:31', '2025-11-19 06:37:31');
INSERT INTO `task_materials` VALUES ('593', '31', 'Πλακέ Ταινία 30x3,5mm St/Tzn 500Gr/M²|Kgr/Mt 0,83|Ρολλό 31m', 'Πλακέ Ταινία 30x3,5mm St/Tzn 500Gr/M²|Kgr/Mt 0,83|Ρολλό 31m', 'μ', 'μ', '22332.00', '21.20', '473438.40', '438', '2025-11-19 07:05:54', '2025-11-19 07:05:54');
INSERT INTO `task_materials` VALUES ('594', '31', 'Πλαστικός σωλήνας PE GEOSUB Φ75', 'Πλαστικός σωλήνας PE GEOSUB Φ75', 'μ', 'μ', '9.40', '2.40', '22.56', '236', '2025-11-19 07:05:54', '2025-11-19 07:05:54');
INSERT INTO `task_materials` VALUES ('595', '31', 'συνδεσμος ταινιας-αγωγου 50-120mm- inox', 'συνδεσμος ταινιας-αγωγου 50-120mm- inox', 'τεμ', 'τεμ', '2312541.00', '1.00', '2312541.00', '515', '2025-11-19 07:05:54', '2025-11-19 07:05:54');
INSERT INTO `task_materials` VALUES ('596', '30', 'Πλακέ Ταινία 30x3,5mm St/Tzn 500Gr/M²|Kgr/Mt 0,83|Ρολλό 31m', 'Πλακέ Ταινία 30x3,5mm St/Tzn 500Gr/M²|Kgr/Mt 0,83|Ρολλό 31m', 'μ', 'μ', '252545.00', '72.50', '18309512.50', '438', '2025-11-19 07:07:12', '2025-11-19 07:07:12');
INSERT INTO `task_materials` VALUES ('597', '30', 'συνδεσμος ταινιας-αγωγου 50-120mm- inox', 'συνδεσμος ταινιας-αγωγου 50-120mm- inox', 'τεμ', 'τεμ', '222000.00', '1.00', '222000.00', '515', '2025-11-19 07:07:12', '2025-11-19 07:07:12');
INSERT INTO `task_materials` VALUES ('601', '33', 'nyaf 4mm', 'nyaf 4mm', 'μ', 'μ', '0.00', '32.00', '0.00', '516', '2025-11-19 07:31:37', '2025-11-19 07:31:37');
INSERT INTO `task_materials` VALUES ('606', '29', 'Πλακέ Ταινία 30x3,5mm St/Tzn 500Gr/M²|Kgr/Mt 0,83|Ρολλό 31m', 'Πλακέ Ταινία 30x3,5mm St/Tzn 500Gr/M²|Kgr/Mt 0,83|Ρολλό 31m', 'μ', 'μ', '276.00', '276.00', '76176.00', '438', '2025-11-19 07:36:33', '2025-11-19 07:36:33');
INSERT INTO `task_materials` VALUES ('607', '29', 'αγωγος χαλκινος ΝΥΥ 1*95', 'αγωγος χαλκινος ΝΥΥ 1*95', 'μ', 'μ', '0.00', '8.00', '0.00', '513', '2025-11-19 07:36:33', '2025-11-19 07:36:33');
INSERT INTO `task_materials` VALUES ('608', '29', 'αγωγος γειωσεως στρογγυλος γαλβανιζε Φ10', 'αγωγος γειωσεως στρογγυλος γαλβανιζε Φ10', 'μ', 'μ', '0.00', '75.00', '0.00', '514', '2025-11-19 07:36:33', '2025-11-19 07:36:33');
INSERT INTO `task_materials` VALUES ('609', '29', 'SuperFlex Κίτρινο Φ25', 'SuperFlex Κίτρινο Φ25', 'μ', 'μ', '0.00', '25.00', '0.00', '106', '2025-11-19 07:36:33', '2025-11-19 07:36:33');
INSERT INTO `task_materials` VALUES ('610', '29', 'καλωδιο ΝΥΥ 3*6', 'καλωδιο ΝΥΥ 3*6', 'μ', 'μ', '0.00', '25.00', '0.00', '517', '2025-11-19 07:36:33', '2025-11-19 07:36:33');
INSERT INTO `task_materials` VALUES ('614', '56', 'Πλαστικός σωλήνας PE GEOSUB Φ50', 'Πλαστικός σωλήνας PE GEOSUB Φ50', 'μ', 'μ', '7.34', '62.00', '455.08', '234', '2025-11-22 13:16:01', '2025-11-22 13:16:01');
INSERT INTO `task_materials` VALUES ('615', '56', 'Πλαστικός σωλήνας PE GEOSUB Φ63', 'Πλαστικός σωλήνας PE GEOSUB Φ63', 'μ', 'μ', '8.21', '62.00', '509.02', '235', '2025-11-22 13:16:01', '2025-11-22 13:16:01');
INSERT INTO `task_materials` VALUES ('616', '56', 'Πλαστικός σωλήνας PE GEOSUB Φ75', 'Πλαστικός σωλήνας PE GEOSUB Φ75', 'μ', 'μ', '9.40', '124.00', '1165.60', '236', '2025-11-22 13:16:01', '2025-11-22 13:16:01');


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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `task_photos` VALUES ('4', '11', 'task_11_1761293761_68fb35c1108db.jpg', 'IMG_20250924_102830.jpg', 'uploads/task_photos/2025/10/task_11_1761293761_68fb35c1108db.jpg', '300700', 'image/jpeg', 'before', '', '0', '1', '2025-10-24 08:16:01');
INSERT INTO `task_photos` VALUES ('8', '11', 'task_11_1761293791_68fb35dfd93f7.jpg', '1.jpg', 'uploads/task_photos/2025/10/task_11_1761293791_68fb35dfd93f7.jpg', '242117', 'image/jpeg', 'after', '', '0', '1', '2025-10-24 08:16:31');
INSERT INTO `task_photos` VALUES ('11', '11', 'task_11_1761294066_68fb36f284a3b.jpg', '1695027212411.jpg', 'uploads/task_photos/2025/10/task_11_1761294066_68fb36f284a3b.jpg', '900808', 'image/jpeg', 'issue', '', '0', '1', '2025-10-24 08:21:07');


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


-- Table: transformer_maintenances
DROP TABLE IF EXISTS `transformer_maintenances`;
CREATE TABLE `transformer_maintenances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `other_details` text DEFAULT NULL,
  `maintenance_date` date NOT NULL,
  `next_maintenance_date` date NOT NULL,
  `is_invoiced` tinyint(1) DEFAULT 0,
  `invoiced_at` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `report_sent` tinyint(1) DEFAULT 0,
  `report_sent_at` datetime DEFAULT NULL,
  `transformer_power` varchar(50) NOT NULL,
  `transformer_type` enum('oil','dry') DEFAULT 'oil',
  `insulation_measurements` text DEFAULT NULL,
  `coil_resistance_measurements` text DEFAULT NULL,
  `grounding_measurement` varchar(50) DEFAULT NULL,
  `oil_breakdown_v1` varchar(50) DEFAULT NULL,
  `oil_breakdown_v2` varchar(50) DEFAULT NULL,
  `oil_breakdown_v3` varchar(50) DEFAULT NULL,
  `oil_breakdown_v4` varchar(50) DEFAULT NULL,
  `oil_breakdown_v5` varchar(50) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `transformers_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`transformers_data`)),
  `created_by` int(11) DEFAULT NULL,
  `additional_technicians` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_technicians`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_maintenance_user` (`created_by`),
  KEY `idx_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_maintenance_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `transformer_maintenances` VALUES ('2', 'Ζάρα', '21 0300 3864', 'DAIDALOU, Βυζαντίου &, Ηράκλειο 712 02', '', '2025-10-24', '2026-10-24', '0', NULL, NULL, '1', '2025-11-21 13:29:00', '630', 'oil', '187ΜΩ', '9.1Ω', '0.9Ω', '36.2', '37.9', '38.3', '40.1', '41.5', NULL, 'uploads/maintenances/maintenance_690c4871ec2ef.jpg', '[\"uploads/maintenances/maintenance_690c4871ec2ef.jpg\"]', '[{\"power\":\"630\",\"type\":\"oil\",\"insulation\":\"187\\u039c\\u03a9\",\"coil_resistance\":\"9.1\\u03a9\",\"grounding\":\"0.9\\u03a9\",\"oil_v1\":\"36.2\",\"oil_v2\":\"37.9\",\"oil_v3\":\"38.3\",\"oil_v4\":\"40.1\",\"oil_v5\":\"41.5\"}]', '1', NULL, '2025-11-06 07:04:17', '2025-11-21 13:29:00', NULL, NULL);
INSERT INTO `transformer_maintenances` VALUES ('3', 'Bershka', '281 028 0255', 'Δικαιοσύνης 55, Ηράκλειο 712 01', '', '2025-10-24', '2026-10-24', '0', NULL, NULL, '1', '2025-11-21 13:28:59', '400', 'oil', '285ΜΩ', '17Ω', '0.9', '32', '32.5', '33.6', '34.2', '35.9', NULL, 'uploads/maintenances/maintenance_690c49c0b002e.jpg', '[\"uploads/maintenances/maintenance_690c49c0b002e.jpg\"]', '[{\"power\":\"400\",\"type\":\"oil\",\"insulation\":\"285\\u039c\\u03a9\",\"coil_resistance\":\"17\\u03a9\",\"grounding\":\"0.9\",\"oil_v1\":\"32\",\"oil_v2\":\"32.5\",\"oil_v3\":\"33.6\",\"oil_v4\":\"34.2\",\"oil_v5\":\"35.9\"}]', '1', NULL, '2025-11-06 07:09:28', '2025-11-21 13:28:59', NULL, NULL);
INSERT INTO `transformer_maintenances` VALUES ('8', 'Επιμελητηριο Ηρακλειου', '', '', '', '2025-11-15', '2026-11-15', '0', NULL, NULL, '0', NULL, '400', 'oil', '16gΩ', '16.2Ω', '0.9Ω', '', '', '', '', '', '', NULL, '[\"uploads\\/maintenances\\/transformer_1_1763788730_692147bad6c79.jpg\"]', '[{\"power\":\"400\",\"type\":\"oil\",\"insulation\":\"16g\\u03a9\",\"coil_resistance\":\"16.2\\u03a9\",\"grounding\":\"0.9\\u03a9\",\"oil_v1\":\"\",\"oil_v2\":\"\",\"oil_v3\":\"\",\"oil_v4\":\"\",\"oil_v5\":\"\",\"materials\":\"\",\"observations\":\"\",\"photos\":[\"uploads\\/maintenances\\/transformer_1_1763788730_692147bad6c79.jpg\"]}]', '3', '[\"5\"]', '2025-11-15 07:36:41', '2025-11-24 06:46:42', NULL, NULL);
INSERT INTO `transformer_maintenances` VALUES ('9', 'Φήμη αναψυκτικά', '', ': Ἁγίαι Παρασκίαι Πεδιάδος, Αγιές Παρασκές 701 00', '281 074 1654', '2025-11-15', '2026-11-15', '1', '2025-11-20 10:48:25', '600.00', '1', '2025-11-20 10:48:24', '630..παλαιός', 'oil', '3725', '8.5Ω', '0.70Ω', '39.1', '40.6', '40.8', '41.6', '42.7', '', NULL, '[\"uploads\\/maintenances\\/transformer_1_1763788890_6921485a2a86b.jpg\",\"uploads\\/maintenances\\/transformer_1_1763788890_6921485a49296.jpg\",\"uploads\\/maintenances\\/transformer_1_1763788890_6921485a6a97a.jpg\"]', '[{\"power\":\"630..\\u03c0\\u03b1\\u03bb\\u03b1\\u03b9\\u03cc\\u03c2\",\"type\":\"oil\",\"insulation\":\"3725\",\"coil_resistance\":\"8.5\\u03a9\",\"grounding\":\"0.70\\u03a9\",\"oil_v1\":\"39.1\",\"oil_v2\":\"40.6\",\"oil_v3\":\"40.8\",\"oil_v4\":\"41.6\",\"oil_v5\":\"42.7\",\"materials\":\"\",\"observations\":\"\",\"photos\":[\"uploads\\/maintenances\\/transformer_1_1763788890_6921485a2a86b.jpg\",\"uploads\\/maintenances\\/transformer_1_1763788890_6921485a49296.jpg\",\"uploads\\/maintenances\\/transformer_1_1763788890_6921485a6a97a.jpg\"]},{\"power\":\"800\",\"type\":\"oil\",\"insulation\":\"3425\",\"coil_resistance\":\"7.9\\u03a9\",\"grounding\":\"0.70\\u03a9\",\"oil_v1\":\"41.1\",\"oil_v2\":\"42.3\",\"oil_v3\":\"43.8\",\"oil_v4\":\"45.6\",\"oil_v5\":\"45.9\",\"materials\":\"\",\"observations\":\"\",\"photos\":[\"uploads\\/maintenances\\/transformer_2_1763788890_6921485a900a3.jpg\",\"uploads\\/maintenances\\/transformer_2_1763788890_6921485aaef55.jpg\",\"uploads\\/maintenances\\/transformer_2_1763788890_6921485acbbec.jpg\"]}]', '3', '[\"5\"]', '2025-11-15 11:01:48', '2025-11-22 05:21:30', NULL, NULL);
INSERT INTO `transformer_maintenances` VALUES ('16', 'lido soccer-παπαδοπουλος', '281 037 3344', ' Παναγίας Μαλεβή 54, Ηράκλειο 714 10', '', '2025-11-21', '2026-11-21', '0', NULL, NULL, '0', NULL, '400', 'dry', '109.3 GΩ', '14 Ω', '3.7 Ω', '', '', '', '', '', '', NULL, '[\"uploads\\/maintenances\\/transformer_1_1763729368_69205fd86f5dc.jpg\",\"uploads\\/maintenances\\/transformer_1_1763729368_69205fd88fdb3.jpg\",\"uploads\\/maintenances\\/transformer_1_1763730363_692063bbe79ff.jpg\"]', '[{\"power\":\"400\",\"type\":\"dry\",\"insulation\":\"109.3 G\\u03a9\",\"coil_resistance\":\"14 \\u03a9\",\"grounding\":\"3.7 \\u03a9\",\"oil_v1\":\"\",\"oil_v2\":\"\",\"oil_v3\":\"\",\"oil_v4\":\"\",\"oil_v5\":\"\",\"materials\":\"\",\"observations\":\"\",\"photos\":[\"uploads\\/maintenances\\/transformer_1_1763729368_69205fd86f5dc.jpg\",\"uploads\\/maintenances\\/transformer_1_1763729368_69205fd88fdb3.jpg\",\"uploads\\/maintenances\\/transformer_1_1763730363_692063bbe79ff.jpg\"]}]', '2', '[\"10\"]', '2025-11-21 12:49:28', '2025-11-21 13:26:04', NULL, NULL);


-- Table: user_role
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `language` varchar(2) DEFAULT 'el',
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
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
  UNIQUE KEY `email` (`email`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_reset_token` (`reset_token`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES ('1', 'admin', 'theodore.sfakianakis@gmail.com', 'el', '$2y$10$R1Fng.7y3u6.Ep7j68IzoOY0aLWEj1kV1aaw33fU.T/8C5odyNpeO', 'a76a36ed78db3aad2e45df331f8a7667a1d2deea34fc30a5278d1c0459a85ecc', '2025-11-10 15:08:14', 'Θεοδωρος', 'Σφακιανακης', '6945139015', '1', '0.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 11:32:36', '2025-11-24 09:59:15');
INSERT INTO `users` VALUES ('2', 'nikos', 'nikos@nikos.gr', 'el', '$2y$10$tlYHBEINxZawJuPJ4pqK2e6eT6LyfJdSGjMK4Ep0WIkAN2P7.jOE6', NULL, NULL, 'Νίκος', 'Νικολουδάκης', '', '1', '12.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 11:50:40', '2025-11-22 16:02:13');
INSERT INTO `users` VALUES ('3', 'manolis', 'manolis@petrakis.gr', 'el', '$2y$10$c0mkKG3pKwXzBLrw4ugYxeKa.46peoBus.NzWeaL/uQLUhbWumf/O', NULL, NULL, 'Μανώλης', 'Πετράκης', '', '15', '10.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:23:05', '2025-11-22 07:12:59');
INSERT INTO `users` VALUES ('4', 'Spyros', 'spyros@spyros.com', 'el', '$2y$10$qWAKzYItgAfDce2fDo4OweZKO6XN3hr/OCJC0OAaojJS60MKbFkZO', NULL, NULL, 'Σπύρος', 'Παπαδόπουλος', '', '16', '8.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:23:40', '2025-11-21 09:44:59');
INSERT INTO `users` VALUES ('5', 'dimitris', 'dim@dim.gr', 'el', '$2y$10$Op.km3coDTv9A8vbl2CADeHB5m8DkgQiJzkXtc8SzvYZlYaU9sol2', NULL, NULL, 'Δημήτρης', 'Βιδάκης', '', '3', '8.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:24:12', '2025-11-12 09:40:36');
INSERT INTO `users` VALUES ('6', 'agis', 'agis@agis.com', 'el', '$2y$10$9bKBqf.3z5RvAI4vqvfPse/w1c5pCleJNY6nuZkHsSZj5HVFFZ/ui', NULL, NULL, 'Αγης', 'Καλτσίδης', '', '3', '10.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:25:01', '2025-11-12 09:40:27');
INSERT INTO `users` VALUES ('7', 'kostasz', 'kostas@kostas.gr', 'el', '$2y$10$UXnUWbKUKnwjAHByzWqKG.GzqeN59MfzirlEg5.rUnCc4v7hcA7.y', NULL, NULL, 'Κώστας', 'Ζάρος', '', '3', '10.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:26:24', '2025-11-06 12:49:05');
INSERT INTO `users` VALUES ('8', 'Minas', 'minas@minas.com', 'el', '$2y$10$XqIcpZ0pDrsmsshHj27MhOARKG54/RvBuOEhUOdxSB8tTh7VwNSO.', NULL, NULL, 'Μηνάς', 'Ζαχαριουδάκης', '', '3', '10.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-22 12:28:51', '2025-11-06 12:49:05');
INSERT INTO `users` VALUES ('9', 'kostas', 'kostas@gmail.com', 'el', '$2y$10$OyexMMLvNq28/dx17ZMgxefsiHSkyUWqQvSBrvXciwOTzO1elU4mm', NULL, NULL, 'Κώστας', 'Σφακιανάκης', '6977206905', '1', '0.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-10-29 07:08:18', '2025-11-06 12:49:05');
INSERT INTO `users` VALUES ('10', 'vagelis', 'koukots7@gmail.com', 'el', '$2y$10$avgooAY5UyaOj8mzCvRF3.odzsETboxl0JA8a7./ubllhGkKYNpKC', NULL, NULL, 'Βαγγελης', 'κουμποτσικας', '6946346241', '16', '2.00', '1', NULL, NULL, NULL, NULL, NULL, '2025-11-21 08:14:23', '2025-11-21 09:43:51');

SET FOREIGN_KEY_CHECKS = 1;
