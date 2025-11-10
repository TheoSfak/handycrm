-- Migration: Email Notification System - Phase 1
-- Date: 2025-11-03
-- Description: Creates core tables for email notification system

-- Email Notifications Log
CREATE TABLE email_notifications (
    id INT(11) NOT NULL AUTO_INCREMENT,
    type ENUM('maintenance_reminder', 'task_assigned', 'payment_received', 'project_deadline', 'test_email') NOT NULL,
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

-- User notification preferences  
CREATE TABLE notification_settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    enabled BOOLEAN DEFAULT 1,
    email_enabled BOOLEAN DEFAULT 1,
    days_before INT(11) DEFAULT 7,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_user_notification (user_id, notification_type),
    CONSTRAINT fk_notification_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email templates
CREATE TABLE email_templates (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT DEFAULT NULL,
    variables JSON DEFAULT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_template_type (type),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default email templates
INSERT INTO email_templates (name, type, subject, body_html, body_text, variables) VALUES 
('Υπενθύμιση Συντήρησης Υ/Σ', 'maintenance_reminder', 'Υπενθύμιση Συντήρησης - {{customer_name}}', 
'<h2>Υπενθύμιση Συντήρησης Υποσταθμού</h2>
<p>Αγαπητέ/ή <strong>{{customer_name}}</strong>,</p>
<p>Σας ενημερώνουμε ότι η επόμενη συντήρηση του μετασχηματιστή σας είναι προγραμματισμένη για <strong>{{maintenance_date}}</strong> (σε {{days_until}} ημέρες).</p>
<h3>Στοιχεία Συντήρησης:</h3>
<ul>
<li><strong>Ισχύς:</strong> {{transformer_power}} kVA</li>
<li><strong>Διεύθυνση:</strong> {{address}}</li>
<li><strong>Τεχνικός:</strong> {{technician_name}}</li>
<li><strong>Τηλέφωνο επικοινωνίας:</strong> {{company_phone}}</li>
</ul>
<p>Παρακαλούμε να διασφαλίσετε ότι θα υπάρχει πρόσβαση στις εγκαταστάσεις την ημερομηνία της συντήρησης.</p>
<p>Με εκτίμηση,<br>{{company_name}}</p>', 
'Υπενθύμιση Συντήρησης Υποσταθμού

Αγαπητέ/ή {{customer_name}},

Σας ενημερώνουμε ότι η επόμενη συντήρηση του μετασχηματιστή σας είναι προγραμματισμένη για {{maintenance_date}} (σε {{days_until}} ημέρες).

Στοιχεία Συντήρησης:
- Ισχύς: {{transformer_power}} kVA
- Διεύθυνση: {{address}}
- Τεχνικός: {{technician_name}}
- Τηλέφωνο επικοινωνίας: {{company_phone}}

Παρακαλούμε να διασφαλίσετε ότι θα υπάρχει πρόσβαση στις εγκαταστάσεις την ημερομηνία της συντήρησης.

Με εκτίμηση,
{{company_name}}', 
'{"customer_name": "Όνομα πελάτη", "maintenance_date": "Ημερομηνία συντήρησης", "days_until": "Ημέρες", "transformer_power": "Ισχύς", "address": "Διεύθυνση", "technician_name": "Τεχνικός", "company_phone": "Τηλέφωνο", "company_name": "Εταιρεία"}'),

-- Test email template
('Test Email', 'test_email', 'HandyCRM - Test Email από {{sender_name}}',
'<h2>🎯 HandyCRM Email Test</h2>
<p>Αυτό είναι ένα test email από το HandyCRM system!</p>
<div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;">
<p><strong>📧 SMTP Configuration:</strong> Επιτυχής!</p>
<p><strong>📅 Ώρα Αποστολής:</strong> {{current_time}}</p>
<p><strong>👤 Αποστολέας:</strong> {{sender_name}}</p>
</div>
<p>Εάν λάβατε αυτό το email, σημαίνει ότι οι ρυθμίσεις email είναι σωστές και μπορείτε να χρησιμοποιήσετε τις υπενθυμίσεις συντήρησης!</p>
<hr>
<p style="font-size: 12px; color: #666;">Αυτό το email στάλθηκε αυτόματα από το HandyCRM Email System v1.0</p>',
'HandyCRM Email Test

Αυτό είναι ένα test email από το HandyCRM system!

📧 SMTP Configuration: Επιτυχής!
📅 Ώρα Αποστολής: {{current_time}}
👤 Αποστολέας: {{sender_name}}

Εάν λάβατε αυτό το email, σημαίνει ότι οι ρυθμίσεις email είναι σωστές και μπορείτε να χρησιμοποιήσετε τις υπενθυμίσεις συντήρησης!

---
Αυτό το email στάλθηκε αυτόματα από το HandyCRM Email System v1.0',
'{"current_time": "Ημερομηνία και ώρα", "sender_name": "Αποστολέας"}');, 
'Υπενθύμιση Συντήρησης Υποσταθμού

Αγαπητέ/ή {{customer_name}},

Σας ενημερώνουμε ότι η επόμενη συντήρηση του μετασχηματιστή σας είναι προγραμματισμένη για {{maintenance_date}} (σε {{days_until}} ημέρες).

Στοιχεία Συντήρησης:
- Ισχύς: {{transformer_power}} kVA
- Διεύθυνση: {{address}}
- Τεχνικός: {{technician_name}}
- Τηλέφωνο επικοινωνίας: {{company_phone}}

Παρακαλούμε να διασφαλίσετε ότι θα υπάρχει πρόσβαση στις εγκαταστάσεις την ημερομηνία της συντήρησης.

Με εκτίμηση,
{{company_name}}', 
'["customer_name", "maintenance_date", "days_until", "transformer_power", "address", "technician_name", "company_phone", "company_name"]'),

('Test Email', 'test_email', 'HandyCRM - Test Email', 
'<h2>HandyCRM Email Test</h2>
<p>Αυτό είναι ένα test email από το HandyCRM σύστημα.</p>
<p><strong>Χρόνος αποστολής:</strong> {{current_time}}</p>
<p><strong>Αποστολέας:</strong> {{sender_name}}</p>
<p>Αν λαμβάνετε αυτό το email, η διαμόρφωση SMTP λειτουργεί σωστά!</p>', 
'HandyCRM Email Test

Αυτό είναι ένα test email από το HandyCRM σύστημα.

Χρόνος αποστολής: {{current_time}}
Αποστολέας: {{sender_name}}

Αν λαμβάνετε αυτό το email, η διαμόρφωση SMTP λειτουργεί σωστά!', 
'["current_time", "sender_name"]');

-- Insert default notification settings for existing users
INSERT INTO notification_settings (user_id, notification_type, enabled, email_enabled, days_before)
SELECT id, 'maintenance_reminder', 1, 1, 7 FROM users WHERE role IN ('admin', 'supervisor', 'maintenance_technician');

INSERT INTO notification_settings (user_id, notification_type, enabled, email_enabled, days_before)
SELECT id, 'task_assigned', 1, 1, 0 FROM users WHERE role IN ('admin', 'supervisor', 'technician', 'assistant');