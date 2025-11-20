-- ========================================
-- HANDYCRM - DAILY TASK MATERIALS MIGRATION
-- Ημερομηνία: 2025-11-13
-- Σκοπός: Σύνδεση daily tasks με materials catalog
-- ========================================

-- 1. Δημιουργία πίνακα daily_task_materials
CREATE TABLE IF NOT EXISTS `daily_task_materials` (
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
  CONSTRAINT `fk_daily_task_materials_task` FOREIGN KEY (`daily_task_id`) REFERENCES `daily_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_daily_task_materials_catalog` FOREIGN KEY (`catalog_material_id`) REFERENCES `materials_catalog` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Μεταφορά υπαρχόντων δεδομένων από το text field 'materials' (προαιρετικό)
-- Αν υπάρχουν daily tasks με υλικά στο text field, μπορείς να τα μεταφέρεις χειροκίνητα
-- ή να τα κρατήσεις ως notes στο παλιό field

-- 3. Indices για performance
CREATE INDEX idx_daily_task_material_lookup ON daily_task_materials(daily_task_id, catalog_material_id);

-- 4. Σχόλια
-- Το πεδίο 'materials' στον πίνακα daily_tasks παραμένει ως text field για backward compatibility
-- Μπορεί να χρησιμοποιηθεί για επιπλέον σημειώσεις ή να αφαιρεθεί στο μέλλον
