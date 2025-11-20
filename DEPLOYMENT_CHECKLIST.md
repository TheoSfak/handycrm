# Deployment Checklist - Maintenance Flags Feature
**Ημερομηνία:** 10 Νοεμβρίου 2025  
**Feature:** Προσθήκη flags για τιμολόγηση και αποστολή δελτίου συντήρησης

## 📋 Βήματα Deployment

### 1️⃣ Backup (ΠΡΩΤΑ!)
- [ ] Backup της βάσης δεδομένων (mysqldump)
- [ ] Backup των αρχείων που θα αντικατασταθούν:
  - `models/TransformerMaintenance.php`
  - `views/maintenances/index.php`
  - `controllers/TransformerMaintenanceController.php`
  - `index.php`

### 2️⃣ Database Migration
- [ ] Σύνδεση στη βάση του παραγωγικού
- [ ] Εκτέλεση του SQL script:
```sql
ALTER TABLE transformer_maintenances 
ADD COLUMN is_invoiced TINYINT(1) DEFAULT 0 COMMENT 'Αν έχει τιμολογηθεί η συντήρηση' AFTER next_maintenance_date,
ADD COLUMN report_sent TINYINT(1) DEFAULT 0 COMMENT 'Αν έχει σταλεί το δελτίο συντήρησης' AFTER is_invoiced;
```
- [ ] Επιβεβαίωση ότι οι στήλες προστέθηκαν:
```sql
DESCRIBE transformer_maintenances;
```

### 3️⃣ Upload Αρχείων (μέσω FTP/SFTP ή cPanel)
- [ ] `models/TransformerMaintenance.php` → `/models/`
- [ ] `views/maintenances/index.php` → `/views/maintenances/`
- [ ] `controllers/TransformerMaintenanceController.php` → `/controllers/`
- [ ] `index.php` → `/` (root)

### 4️⃣ Testing
- [ ] Άνοιγμα https://ecowatt.gr/crm/maintenances
- [ ] Επιβεβαίωση ότι εμφανίζονται οι 2 νέες στήλες:
  - "Τιμολογήθηκε"
  - "Δελτίο Συντ."
- [ ] Δοκιμή toggle ενός checkbox για τιμολόγηση
- [ ] Δοκιμή toggle ενός checkbox για δελτίο
- [ ] Έλεγχος στο Developer Console (F12) για errors
- [ ] Επιβεβαίωση ότι οι αλλαγές σώζονται (refresh σελίδας)

### 5️⃣ Rollback Plan (σε περίπτωση προβλήματος)
```sql
-- Αν χρειαστεί rollback:
ALTER TABLE transformer_maintenances 
DROP COLUMN is_invoiced,
DROP COLUMN report_sent;
```

Και επαναφορά των backup αρχείων.

## 📝 Αλλαγές που έγιναν

### Database Schema
- **transformer_maintenances** table:
  - Νέα στήλη: `is_invoiced` (TINYINT, default 0)
  - Νέα στήλη: `report_sent` (TINYINT, default 0)

### Code Changes
1. **TransformerMaintenance Model** - Προστέθηκαν μέθοδοι:
   - `updateInvoicedStatus($id, $status)`
   - `updateReportSentStatus($id, $status)`

2. **TransformerMaintenanceController** - Νέο endpoint:
   - `toggleStatus($id)` - AJAX endpoint για toggle των flags

3. **maintenances/index.php View**:
   - 2 νέες στήλες στον πίνακα
   - Toggle switches (checkboxes)
   - JavaScript για AJAX calls

4. **index.php Routing**:
   - Νέο hardcoded route: `/maintenances/toggle-status/{id}` (POST)

## ⚠️ Σημειώσεις
- Η λειτουργία είναι instant (AJAX) - δεν κάνει reload τη σελίδα
- Υπάρχει error handling για αποτυχημένα requests
- Τα checkboxes disable προσωρινά κατά την αποστολή του request
