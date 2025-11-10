-- HandyCRM v1.5.0 - Upgrade Script from v1.4.x
-- Date: November 10, 2025
-- Description: Upgrades existing HandyCRM v1.4.x installations to v1.5.0
--
-- This migration adds:
-- 1. Email Infrastructure (SMTP settings, templates, notifications)
-- 2. Role & Permission System (RBAC)
-- 3. Updated user roles enum
-- 4. Email notification types for maintenance and project reports

-- ============================================================================
-- PART 1: EMAIL INFRASTRUCTURE
-- ============================================================================

-- SMTP Settings Table
CREATE TABLE IF NOT EXISTS smtp_settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    host VARCHAR(255) NOT NULL,
    port INT(11) NOT NULL DEFAULT 587,
    encryption ENUM('none', 'ssl', 'tls') DEFAULT 'tls',
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    from_email VARCHAR(255) NOT NULL,
    from_name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Templates Table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html TEXT NOT NULL,
    body_plain TEXT,
    variables TEXT COMMENT 'JSON array of available variables',
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_template_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check if email_notifications table exists and update ENUM if needed
CREATE TABLE IF NOT EXISTS email_notifications (
    id INT(11) NOT NULL AUTO_INCREMENT,
    type ENUM('maintenance_reminder', 'task_assigned', 'payment_received', 'project_deadline', 'test_email', 'maintenance_report', 'project_report') NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255) DEFAULT NULL,
    subject VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    sent_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT DEFAULT NULL,
    related_id INT(11) DEFAULT NULL,
    related_type VARCHAR(50) DEFAULT NULL,
    created_by INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_status (status),
    KEY idx_type (type),
    KEY idx_recipient (recipient_email),
    KEY idx_created_at (created_at),
    CONSTRAINT fk_email_notifications_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update email_notifications ENUM if table already exists
-- This adds maintenance_report and project_report to the type enum
SET @check_email_notif_table = (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'email_notifications');
SET @alter_email_notif = IF(@check_email_notif_table > 0, 
    'ALTER TABLE email_notifications MODIFY COLUMN type ENUM(''maintenance_reminder'', ''task_assigned'', ''payment_received'', ''project_deadline'', ''test_email'', ''maintenance_report'', ''project_report'') NOT NULL',
    'SELECT "email_notifications table does not exist yet"');
PREPARE stmt FROM @alter_email_notif;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- PART 2: ROLE & PERMISSION SYSTEM
-- ============================================================================

-- Roles Table
CREATE TABLE IF NOT EXISTS roles (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_role_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions Table
CREATE TABLE IF NOT EXISTS permissions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_permission (module, action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role-Permission Pivot Table
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    role_id INT(11) NOT NULL,
    permission_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User-Role Pivot Table
CREATE TABLE IF NOT EXISTS user_role (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    role_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_role (user_id, role_id),
    CONSTRAINT fk_user_role_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_role_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PART 3: UPDATE USER ROLES ENUM
-- ============================================================================

-- Add maintenance_technician to users.role enum
ALTER TABLE users MODIFY COLUMN role ENUM('admin','supervisor','technician','assistant','maintenance_technician') DEFAULT 'technician';

-- ============================================================================
-- PART 4: INSERT DEFAULT PERMISSIONS
-- ============================================================================

-- Delete any old invoice permissions if they exist
DELETE FROM permissions WHERE module = 'invoices';

-- Customers permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('customers', 'view', 'Προβολή Πελατών', 'Δικαίωμα προβολής λίστας πελατών'),
('customers', 'create', 'Δημιουργία Πελάτη', 'Δικαίωμα δημιουργίας νέου πελάτη'),
('customers', 'edit', 'Επεξεργασία Πελάτη', 'Δικαίωμα επεξεργασίας στοιχείων πελάτη'),
('customers', 'delete', 'Διαγραφή Πελάτη', 'Δικαίωμα διαγραφής πελάτη'),
('customers', 'export', 'Εξαγωγή Πελατών', 'Δικαίωμα εξαγωγής λίστας πελατών');

-- Projects permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('projects', 'view', 'Προβολή Έργων', 'Δικαίωμα προβολής λίστας έργων'),
('projects', 'create', 'Δημιουργία Έργου', 'Δικαίωμα δημιουργίας νέου έργου'),
('projects', 'edit', 'Επεξεργασία Έργου', 'Δικαίωμα επεξεργασίας έργου'),
('projects', 'delete', 'Διαγραφή Έργου', 'Δικαίωμα διαγραφής έργου'),
('projects', 'export', 'Εξαγωγή Έργων', 'Δικαίωμα εξαγωγής λίστας έργων');

-- Tasks permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('tasks', 'view', 'Προβολή Εργασιών', 'Δικαίωμα προβολής λίστας εργασιών'),
('tasks', 'create', 'Δημιουργία Εργασίας', 'Δικαίωμα δημιουργίας νέας εργασίας'),
('tasks', 'edit', 'Επεξεργασία Εργασίας', 'Δικαίωμα επεξεργασίας εργασίας'),
('tasks', 'delete', 'Διαγραφή Εργασίας', 'Δικαίωμα διαγραφής εργασίας'),
('tasks', 'assign', 'Ανάθεση Εργασίας', 'Δικαίωμα ανάθεσης εργασιών σε τεχνικούς');

-- Appointments permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('appointments', 'view', 'Προβολή Ραντεβού', 'Δικαίωμα προβολής λίστας ραντεβού'),
('appointments', 'create', 'Δημιουργία Ραντεβού', 'Δικαίωμα δημιουργίας νέου ραντεβού'),
('appointments', 'edit', 'Επεξεργασία Ραντεβού', 'Δικαίωμα επεξεργασίας ραντεβού'),
('appointments', 'delete', 'Διαγραφή Ραντεβού', 'Δικαίωμα διαγραφής ραντεβού');

-- Materials permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('materials', 'view', 'Προβολή Υλικών', 'Δικαίωμα προβολής καταλόγου υλικών'),
('materials', 'create', 'Δημιουργία Υλικού', 'Δικαίωμα προσθήκης νέου υλικού'),
('materials', 'edit', 'Επεξεργασία Υλικού', 'Δικαίωμα επεξεργασίας υλικού'),
('materials', 'delete', 'Διαγραφή Υλικού', 'Δικαίωμα διαγραφής υλικού'),
('materials', 'export', 'Εξαγωγή Υλικών', 'Δικαίωμα εξαγωγής καταλόγου υλικών');

-- Transformer Maintenances permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('maintenances', 'view', 'Προβολή Συντηρήσεων Μ/Σ', 'Δικαίωμα προβολής συντηρήσεων μετασχηματιστών'),
('maintenances', 'create', 'Δημιουργία Συντήρησης', 'Δικαίωμα δημιουργίας νέας συντήρησης'),
('maintenances', 'edit', 'Επεξεργασία Συντήρησης', 'Δικαίωμα επεξεργασίας συντήρησης'),
('maintenances', 'delete', 'Διαγραφή Συντήρησης', 'Δικαίωμα διαγραφής συντήρησης'),
('maintenances', 'export', 'Εξαγωγή Αναφοράς', 'Δικαίωμα εξαγωγής αναφοράς συντήρησης σε PDF');

-- Reports permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('reports', 'view', 'Προβολή Αναφορών', 'Δικαίωμα προβολής αναφορών και στατιστικών'),
('reports', 'export', 'Εξαγωγή Αναφορών', 'Δικαίωμα εξαγωγής αναφορών');

-- Users & Roles permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('users', 'view', 'Προβολή Χρηστών', 'Δικαίωμα προβολής λίστας χρηστών'),
('users', 'create', 'Δημιουργία Χρήστη', 'Δικαίωμα δημιουργίας νέου χρήστη'),
('users', 'edit', 'Επεξεργασία Χρήστη', 'Δικαίωμα επεξεργασίας χρήστη'),
('users', 'delete', 'Διαγραφή Χρήστη', 'Δικαίωμα διαγραφής χρήστη'),
('roles', 'view', 'Προβολή Ρόλων', 'Δικαίωμα προβολής ρόλων'),
('roles', 'create', 'Δημιουργία Ρόλου', 'Δικαίωμα δημιουργίας νέου ρόλου'),
('roles', 'edit', 'Επεξεργασία Ρόλου', 'Δικαίωμα επεξεργασίας ρόλου'),
('roles', 'delete', 'Διαγραφή Ρόλου', 'Δικαίωμα διαγραφής ρόλου'),
('roles', 'permissions', 'Διαχείριση Δικαιωμάτων', 'Δικαίωμα διαχείρισης δικαιωμάτων ρόλων');

-- Settings permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('settings', 'view', 'Προβολή Ρυθμίσεων', 'Δικαίωμα προβολής ρυθμίσεων συστήματος'),
('settings', 'edit', 'Επεξεργασία Ρυθμίσεων', 'Δικαίωμα επεξεργασίας ρυθμίσεων συστήματος');

-- ============================================================================
-- PART 5: CREATE DEFAULT ROLES (OPTIONAL)
-- ============================================================================

-- Create default roles if they don't exist
INSERT IGNORE INTO roles (name, display_name, description) VALUES
('admin', 'Διαχειριστής', 'Πλήρη δικαιώματα στο σύστημα'),
('supervisor', 'Επόπτης', 'Διαχείριση έργων και εργασιών'),
('technician', 'Τεχνικός', 'Προβολή και ενημέρωση εργασιών'),
('maintenance_tech', 'Τεχνικός Συντηρήσεων Μ/Σ', 'Διαχείριση συντηρήσεων μετασχηματιστών');

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================

-- Note: You need to manually configure SMTP settings after this migration
-- Go to Settings > Email Settings in the admin panel

SELECT 'HandyCRM v1.5.0 upgrade completed successfully!' AS status;
SELECT 'Please configure SMTP settings in the admin panel.' AS next_step;
SELECT 'You can now assign roles and permissions to users.' AS note;
