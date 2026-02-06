-- Sample data for HandyCRM
-- Insert default admin user and sample data

USE `handycrm`;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('app_name', 'HandyCRM', 'string', 'Application name'),
('app_version', '1.0.0', 'string', 'Application version'),
('default_vat_rate', '24.00', 'decimal', 'Default VAT rate in Greece'),
('currency', 'EUR', 'string', 'Default currency'),
('currency_symbol', '€', 'string', 'Currency symbol'),
('timezone', 'Europe/Athens', 'string', 'Default timezone'),
('date_format', 'd/m/Y', 'string', 'Date display format'),
('datetime_format', 'd/m/Y H:i', 'string', 'DateTime display format'),
('items_per_page', '20', 'integer', 'Default items per page in listings'),
('email_notifications', '1', 'boolean', 'Enable email notifications'),
('sms_notifications', '0', 'boolean', 'Enable SMS notifications'),
('auto_quote_numbering', '1', 'boolean', 'Automatic quote numbering'),
('auto_invoice_numbering', '1', 'boolean', 'Automatic invoice numbering'),
('quote_validity_days', '30', 'integer', 'Default quote validity in days'),
('invoice_due_days', '30', 'integer', 'Default invoice due days'),
('backup_enabled', '1', 'boolean', 'Enable automatic backups'),
('max_file_upload_size', '5242880', 'integer', 'Max file upload size in bytes (5MB)'),
('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx', 'string', 'Allowed file upload types');

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `phone`, `role`, `company_name`, `company_address`, `company_phone`, `company_email`, `company_tax_id`) VALUES
('admin', 'admin@handycrm.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Διαχειριστής', 'Συστήματος', '2101234567', 'admin', 'HandyCRM Services', 'Λεωφ. Κηφισίας 123, Μαρούσι 15124', '2101234567', 'info@handycrm.gr', '123456789');

-- Insert sample technician users
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `phone`, `role`) VALUES
('yannis', 'yannis@handycrm.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Γιάννης', 'Παπαδόπουλος', '6971234567', 'technician'),
('maria', 'maria@handycrm.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Μαρία', 'Γεωργίου', '6971234568', 'technician'),
('kostas', 'kostas@handycrm.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Κώστας', 'Αντωνίου', '6971234569', 'technician');

-- Insert sample customers
INSERT INTO `customers` (`first_name`, `last_name`, `company_name`, `customer_type`, `phone`, `mobile`, `email`, `address`, `city`, `postal_code`, `tax_id`, `notes`, `created_by`) VALUES
('Νίκος', 'Κωνσταντίνου', NULL, 'individual', '2106789012', '6971111111', 'nikos@example.gr', 'Πατησίων 45, Αθήνα', 'Αθήνα', '10434', NULL, 'Τακτικός πελάτης για ηλεκτρολογικές εργασίες', 1),
('Ελένη', 'Μιχαηλίδου', NULL, 'individual', '2310555666', '6972222222', 'eleni@example.gr', 'Τσιμισκή 100, Θεσσαλονίκη', 'Θεσσαλονίκη', '54622', NULL, 'Υδραυλικές επισκευές', 1),
('Πέτρος', 'Δημητρίου', 'Δημητρίου Α.Ε.', 'company', '2109876543', '6973333333', 'info@dimitriou.gr', 'Κηφισίας 200, Μαρούσι', 'Μαρούσι', '15124', '999888777', 'Εταιρεία κατασκευών - μεγάλα έργα', 1),
('Αννα', 'Παπαγεωργίου', NULL, 'individual', '2741012345', '6974444444', 'anna@example.gr', 'Κεντρική Πλατεία 5, Κεφαλονιά', 'Αργοστόλι', '28100', NULL, 'Εξοχική κατοικία', 1),
('Γιώργος', 'Αλεξίου', 'TechnoBuilds Ε.Π.Ε.', 'company', '2610123456', '6975555555', 'contact@technobuilds.gr', 'Ρίου-Πατρών 50, Πάτρα', 'Πάτρα', '26504', '111222333', 'Εταιρεία συντήρησης κτιρίων', 1);

-- Insert sample projects
INSERT INTO `projects` (`customer_id`, `assigned_technician`, `title`, `description`, `project_address`, `category`, `priority`, `status`, `estimated_hours`, `material_cost`, `labor_cost`, `total_cost`, `start_date`, `notes`, `created_by`) VALUES
(1, 2, 'Εγκατάσταση νέων διακοπτών', 'Αντικατάσταση παλιών διακοπτών με έξυπνους διακόπτες', 'Πατησίων 45, Αθήνα', 'electrical', 'medium', 'completed', 3.00, 120.50, 90.00, 254.04, '2024-09-15', 'Ολοκληρώθηκε επιτυχώς', 1),
(2, 3, 'Επισκευή διαρροής βρύσης', 'Αντικατάσταση παλιάς βρύσης κουζίνας', 'Τσιμισκή 100, Θεσσαλονίκη', 'plumbing', 'high', 'in_progress', 2.00, 85.00, 60.00, 179.80, '2024-10-01', 'Σε εξέλιξη', 1),
(3, 2, 'Ηλεκτρολογική εγκατάσταση γραφείων', 'Πλήρης ηλεκτρολογική εγκατάσταση νέου κτιρίου γραφείων', 'Κηφισίας 200, Μαρούσι', 'electrical', 'high', 'new', 40.00, 2500.00, 1200.00, 4588.00, '2024-10-10', 'Μεγάλο έργο - χρειάζεται προσεκτικός προγραμματισμός', 1),
(4, 3, 'Συντήρηση υδραυλικών εξοχικής', 'Ετήσια συντήρηση υδραυλικού συστήματος', 'Κεντρική Πλατεία 5, Κεφαλονιά', 'maintenance', 'low', 'new', 4.00, 150.00, 120.00, 334.80, '2024-10-20', 'Ετήσια συντήρηση', 1),
(5, 2, 'Επείγουσα επισκευή ηλεκτρολογικού πίνακα', 'Αντικατάσταση καμένου ηλεκτρολογικού πίνακα', 'Ρίου-Πατρών 50, Πάτρα', 'electrical', 'urgent', 'completed', 6.00, 450.00, 180.00, 781.20, '2024-09-25', 'Επείγουσα κατάσταση - ολοκληρώθηκε γρήγορα', 1);

-- Insert sample appointments
INSERT INTO `appointments` (`customer_id`, `project_id`, `technician_id`, `title`, `description`, `appointment_date`, `duration_minutes`, `status`, `address`, `notes`, `created_by`) VALUES
(1, 1, 2, 'Εγκατάσταση διακοπτών', 'Τελική εγκατάσταση έξυπνων διακοπτών', '2024-10-08 10:00:00', 180, 'completed', 'Πατησίων 45, Αθήνα', 'Ολοκληρώθηκε επιτυχώς', 1),
(2, 2, 3, 'Επισκευή βρύσης', 'Αντικατάσταση βρύσης κουζίνας', '2024-10-07 14:00:00', 120, 'in_progress', 'Τσιμισκή 100, Θεσσαλονίκη', 'Σε εξέλιξη', 1),
(3, 3, 2, 'Επίσκεψη αξιολόγησης', 'Αξιολόγηση απαιτήσεων ηλεκτρολογικής εγκατάστασης', '2024-10-10 09:00:00', 90, 'scheduled', 'Κηφισίας 200, Μαρούσι', 'Πρώτη επίσκεψη αξιολόγησης', 1),
(4, NULL, 3, 'Προγραμματισμένη συντήρηση', 'Ετήσιος έλεγχος υδραυλικών', '2024-10-15 11:00:00', 240, 'scheduled', 'Κεντρική Πλατεία 5, Κεφαλονιά', 'Ετήσια συντήρηση', 1),
(5, 5, 2, 'Επείγουσα επισκευή', 'Επισκευή ηλεκτρολογικού πίνακα', '2024-09-25 08:00:00', 360, 'completed', 'Ρίου-Πατρών 50, Πάτρα', 'Επείγουσα κατάσταση - ολοκληρώθηκε', 1);

-- Insert sample quotes
INSERT INTO `quotes` (`quote_number`, `customer_id`, `project_id`, `title`, `description`, `subtotal`, `vat_amount`, `total_amount`, `status`, `valid_until`, `created_by`) VALUES
('PRO-2024-001', 3, 3, 'Προσφορά ηλεκτρολογικής εγκατάστασης', 'Πλήρης ηλεκτρολογική εγκατάσταση νέου κτιρίου γραφείων', 3700.00, 888.00, 4588.00, 'sent', '2024-11-10', 1),
('PRO-2024-002', 4, 4, 'Προσφορά συντήρησης υδραυλικών', 'Ετήσια συντήρηση υδραυλικού συστήματος εξοχικής κατοικίας', 270.00, 64.80, 334.80, 'draft', '2024-11-15', 1);

-- Insert sample quote items
INSERT INTO `quote_items` (`quote_id`, `item_type`, `description`, `quantity`, `unit_price`, `total_price`, `sort_order`) VALUES
(1, 'material', 'Καλώδια ΝΥΜ 3x2.5mm', 500.00, 2.50, 1250.00, 1),
(1, 'material', 'Διακόπτες και πρίζες', 25.00, 15.00, 375.00, 2),
(1, 'material', 'Ηλεκτρολογικός πίνακας', 1.00, 350.00, 350.00, 3),
(1, 'material', 'Διάφορα υλικά εγκατάστασης', 1.00, 525.00, 525.00, 4),
(1, 'labor', 'Εργασία ηλεκτρολόγου (40 ώρες)', 40.00, 30.00, 1200.00, 5),
(2, 'material', 'Υλικά συντήρησης υδραυλικών', 1.00, 150.00, 150.00, 1),
(2, 'labor', 'Εργασία υδραυλικού (4 ώρες)', 4.00, 30.00, 120.00, 2);

-- Insert sample materials (for inventory)
INSERT INTO `materials` (`name`, `description`, `category`, `unit`, `current_stock`, `min_stock_level`, `unit_price`, `supplier`, `created_by`) VALUES
('Καλώδιο ΝΥΜ 3x1.5mm', 'Καλώδιο ΝΥΜ 3x1.5mm για οικιακές εγκαταστάσεις', 'Καλώδια', 'μέτρο', 500.00, 100.00, 1.80, 'Ηλεκτρολογικά Υλικά Α.Ε.', 1),
('Καλώδιο ΝΥΜ 3x2.5mm', 'Καλώδιο ΝΥΜ 3x2.5mm για κυκλώματα πριζών', 'Καλώδια', 'μέτρο', 300.00, 50.00, 2.50, 'Ηλεκτρολογικά Υλικά Α.Ε.', 1),
('Διακόπτης απλός', 'Διακόπτης απλός λευκός', 'Διακόπτες', 'τεμ', 50.00, 10.00, 5.50, 'ElectroShop', 1),
('Πρίζα σούκο', 'Πρίζα σούκο με γείωση λευκή', 'Πρίζες', 'τεμ', 30.00, 10.00, 8.00, 'ElectroShop', 1),
('Σωλήνας PVC 32mm', 'Σωλήνας PVC 32mm για υδραυλικές εγκαταστάσεις', 'Σωλήνες', 'μέτρο', 200.00, 30.00, 3.20, 'Υδραυλικά Υλικά Β.Ε.', 1),
('Βρύση κουζίνας', 'Βρύση κουζίνας μονόχερη χρωμέ', 'Βρύσες', 'τεμ', 15.00, 5.00, 45.00, 'Υδραυλικά Υλικά Β.Ε.', 1),
('Σιφόνι νεροχύτη', 'Σιφόνι νεροχύτη πλαστικό', 'Αξεσουάρ', 'τεμ', 20.00, 5.00, 12.50, 'Υδραυλικά Υλικά Β.Ε.', 1);

-- Insert sample notifications
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `related_id`, `related_type`, `scheduled_for`) VALUES
(2, 'appointment_reminder', 'Υπενθύμιση ραντεβού', 'Έχετε ραντεβού σήμερα στις 10:00 με τον πελάτη Νίκος Κωνσταντίνου', 1, 'appointment', '2024-10-08 09:00:00'),
(3, 'appointment_reminder', 'Υπενθύμιση ραντεβού', 'Έχετε ραντεβού αύριο στις 14:00 με την πελάτισσα Ελένη Μιχαηλίδου', 2, 'appointment', '2024-10-06 09:00:00'),
(1, 'low_stock', 'Χαμηλό απόθεμα', 'Το υλικό "Πρίζα σούκο" έχει χαμηλό απόθεμα (30 τεμ)', 4, 'material', NOW());

-- Insert sample customer communications
INSERT INTO `customer_communications` (`customer_id`, `user_id`, `communication_type`, `subject`, `description`, `communication_date`) VALUES
(1, 2, 'phone', 'Συζήτηση για νέους διακόπτες', 'Ο πελάτης ρώτησε για έξυπνους διακόπτες και τις δυνατότητές τους', '2024-09-10 15:30:00'),
(2, 3, 'visit', 'Επίσκεψη για εκτίμηση', 'Επίσκεψη για εκτίμηση του προβλήματος με τη βρύση', '2024-09-28 10:00:00'),
(3, 1, 'email', 'Αποστολή προσφοράς', 'Στάλθηκε προσφορά για ηλεκτρολογική εγκατάσταση γραφείων', '2024-10-05 16:00:00'),
(1, 2, 'phone', 'Επιβεβαίωση ολοκλήρωσης', 'Επιβεβαίωση ότι η εργασία ολοκληρώθηκε επιτυχώς', '2024-09-15 17:00:00');