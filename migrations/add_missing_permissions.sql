-- Add missing permissions for all modules
-- This ensures all permission checks in controllers have corresponding DB entries

-- Customers permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('customers', 'view', 'Προβολή Πελατών', 'Δικαίωμα προβολής λίστας πελατών'),
('customers', 'create', 'Δημιουργία Πελάτη', 'Δικαίωμα δημιουργίας νέου πελάτη'),
('customers', 'edit', 'Επεξεργασία Πελάτη', 'Δικαίωμα επεξεργασίας στοιχείων πελάτη'),
('customers', 'delete', 'Διαγραφή Πελάτη', 'Δικαίωμα διαγραφής πελάτη'),
('customers', 'export', 'Εξαγωγή Πελατών', 'Δικαίωμα εξαγωγής λίστας πελατών');

-- Projects permissions (already exist, but adding for completeness)
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('projects', 'view', 'Προβολή Έργων', 'Δικαίωμα προβολής λίστας έργων'),
('projects', 'create', 'Δημιουργία Έργου', 'Δικαίωμα δημιουργίας νέου έργου'),
('projects', 'edit', 'Επεξεργασία Έργου', 'Δικαίωμα επεξεργασίας έργου'),
('projects', 'delete', 'Διαγραφή Έργου', 'Δικαίωμα διαγραφής έργου'),
('projects', 'export', 'Εξαγωγή Έργων', 'Δικαίωμα εξαγωγής λίστας έργων');

-- Appointments permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('appointments', 'view', 'Προβολή Ραντεβού', 'Δικαίωμα προβολής λίστας ραντεβού'),
('appointments', 'create', 'Δημιουργία Ραντεβού', 'Δικαίωμα δημιουργίας νέου ραντεβού'),
('appointments', 'edit', 'Επεξεργασία Ραντεβού', 'Δικαίωμα επεξεργασίας ραντεβού'),
('appointments', 'delete', 'Διαγραφή Ραντεβού', 'Δικαίωμα διαγραφής ραντεβού');

-- Quotes permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('quotes', 'view', 'Προβολή Προσφορών', 'Δικαίωμα προβολής λίστας προσφορών'),
('quotes', 'create', 'Δημιουργία Προσφοράς', 'Δικαίωμα δημιουργίας νέας προσφοράς'),
('quotes', 'edit', 'Επεξεργασία Προσφοράς', 'Δικαίωμα επεξεργασίας προσφοράς'),
('quotes', 'delete', 'Διαγραφή Προσφοράς', 'Δικαίωμα διαγραφής προσφοράς'),
('quotes', 'export', 'Εξαγωγή Προσφορών', 'Δικαίωμα εξαγωγής προσφορών σε PDF');

-- Materials permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('materials', 'view', 'Προβολή Υλικών', 'Δικαίωμα προβολής καταλόγου υλικών'),
('materials', 'create', 'Δημιουργία Υλικού', 'Δικαίωμα προσθήκης νέου υλικού'),
('materials', 'edit', 'Επεξεργασία Υλικού', 'Δικαίωμα επεξεργασίας υλικού'),
('materials', 'delete', 'Διαγραφή Υλικού', 'Δικαίωμα διαγραφής υλικού'),
('materials', 'export', 'Εξαγωγή Υλικών', 'Δικαίωμα εξαγωγής καταλόγου υλικών');

-- Reports permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('reports', 'view', 'Προβολή Αναφορών', 'Δικαίωμα προβολής αναφορών και στατιστικών'),
('reports', 'export', 'Εξαγωγή Αναφορών', 'Δικαίωμα εξαγωγής αναφορών');

-- Technicians permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('technicians', 'view', 'Προβολή Τεχνικών', 'Δικαίωμα προβολής λίστας τεχνικών'),
('technicians', 'create', 'Δημιουργία Τεχνικού', 'Δικαίωμα προσθήκης νέου τεχνικού'),
('technicians', 'edit', 'Επεξεργασία Τεχνικού', 'Δικαίωμα επεξεργασίας τεχνικού'),
('technicians', 'delete', 'Διαγραφή Τεχνικού', 'Δικαίωμα διαγραφής τεχνικού');

-- Maintenances permissions
INSERT IGNORE INTO permissions (module, action, display_name, description) VALUES
('maintenances', 'view', 'Προβολή Συντηρήσεων', 'Δικαίωμα προβολής συντηρήσεων'),
('maintenances', 'create', 'Δημιουργία Συντήρησης', 'Δικαίωμα δημιουργίας νέας συντήρησης'),
('maintenances', 'edit', 'Επεξεργασία Συντήρησης', 'Δικαίωμα επεξεργασίας συντήρησης'),
('maintenances', 'delete', 'Διαγραφή Συντήρησης', 'Δικαίωμα διαγραφής συντήρησης');
