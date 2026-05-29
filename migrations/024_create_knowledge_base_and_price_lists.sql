-- Migration: Knowledge Base and Price Lists
-- Date: 2026-05-29

CREATE TABLE IF NOT EXISTS `knowledge_articles` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `content` MEDIUMTEXT NOT NULL,
    `created_by` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_kb_created_by` (`created_by`),
    KEY `idx_kb_created_at` (`created_at`),
    CONSTRAINT `fk_kb_article_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `knowledge_categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(120) NOT NULL,
    `created_by` INT(11) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_kb_category_name` (`name`),
    KEY `idx_kb_cat_created_by` (`created_by`),
    CONSTRAINT `fk_kb_category_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `knowledge_article_categories` (
    `article_id` INT(11) NOT NULL,
    `category_id` INT(11) NOT NULL,
    PRIMARY KEY (`article_id`, `category_id`),
    KEY `idx_kb_ac_category` (`category_id`),
    CONSTRAINT `fk_kb_ac_article` FOREIGN KEY (`article_id`) REFERENCES `knowledge_articles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_kb_ac_category` FOREIGN KEY (`category_id`) REFERENCES `knowledge_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `knowledge_attachments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `article_id` INT(11) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` BIGINT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_kb_attach_article` (`article_id`),
    CONSTRAINT `fk_kb_attachment_article` FOREIGN KEY (`article_id`) REFERENCES `knowledge_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `price_lists` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` BIGINT NOT NULL,
    `created_by` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pricelist_created_by` (`created_by`),
    KEY `idx_pricelist_created_at` (`created_at`),
    CONSTRAINT `fk_pricelist_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `permissions` (`module`, `action`, `display_name`, `description`) VALUES
('knowledge_base', 'view', 'Προβολή Knowledge Base', 'Προβολή άρθρων γνώσης'),
('knowledge_base', 'create', 'Δημιουργία άρθρων γνώσης', 'Δημιουργία νέων άρθρων'),
('knowledge_base', 'edit', 'Επεξεργασία άρθρων γνώσης', 'Επεξεργασία άρθρων'),
('knowledge_base', 'delete', 'Διαγραφή άρθρων γνώσης', 'Διαγραφή άρθρων'),
('knowledge_base', 'export', 'Εξαγωγή άρθρων γνώσης', 'PDF export και εκτύπωση'),
('knowledge_categories', 'manage', 'Διαχείριση κατηγοριών γνώσης', 'Δημιουργία και διαγραφή κατηγοριών'),
('price_lists', 'view', 'Προβολή τιμοκαταλόγων', 'Προβολή τιμοκαταλόγων'),
('price_lists', 'create', 'Ανέβασμα τιμοκαταλόγων', 'Ανέβασμα νέων τιμοκαταλόγων'),
('price_lists', 'delete', 'Διαγραφή τιμοκαταλόγων', 'Διαγραφή τιμοκαταλόγων'),
('price_lists', 'export', 'Εξαγωγή τιμοκαταλόγων', 'Εκτύπωση/εξαγωγή λίστας τιμοκαταλόγων');

INSERT IGNORE INTO `knowledge_categories` (`name`, `created_by`) VALUES
('Inverters', NULL),
('Μετασχηματιστές', NULL),
('Commissioning', NULL),
('Service Manuals', NULL),
('Troubleshooting', NULL);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.module = 'knowledge_base'
WHERE r.name IN ('admin', 'supervisor');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.module = 'knowledge_categories' AND p.action = 'manage'
WHERE r.name IN ('admin', 'supervisor');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.module = 'price_lists'
WHERE r.name IN ('admin', 'supervisor');

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.module = 'knowledge_base' AND p.action IN ('view', 'create', 'edit', 'delete', 'export')
WHERE r.name = 'technician';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.module = 'price_lists' AND p.action IN ('view', 'create', 'delete', 'export')
WHERE r.name = 'technician';
