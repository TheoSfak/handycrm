# HandyCRM Trash System - Testing Checklist

## ✅ Automated Tests (COMPLETED - 16/16 PASSED)

- [x] Database schema validation (deleted_at, deleted_by columns)
- [x] deletion_log table structure
- [x] Trash permissions created
- [x] Admin role has trash permissions
- [x] Trash Model methods functional
- [x] Model filtering (WHERE deleted_at IS NULL)
- [x] File existence (controllers, models, views)
- [x] Routes configuration

**Result: 100% Success Rate** ✓

---

## 📋 Manual Testing Checklist

### 1. Access Control
- [ ] Login as **admin** - κάδος εμφανίζεται στο sidebar
- [ ] Login as **technician** - κάδος ΔΕΝ εμφανίζεται
- [ ] Login as **supervisor** - κάδος ΔΕΝ εμφανίζεται
- [ ] Try to access `/trash` as non-admin - redirect με error message

### 2. Sidebar Menu
- [ ] Κάδος Απορριμμάτων εμφανίζεται με trash icon
- [ ] Badge counter (αριθμός διαγραμμένων) εμφανίζεται όταν υπάρχουν items
- [ ] Badge είναι 0 ή hidden όταν ο κάδος είναι άδειος
- [ ] Link οδηγεί στο `/trash`

### 3. Soft Delete - Projects
- [ ] Διαγραφή έργου → μήνυμα "μεταφέρθηκε στον κάδο"
- [ ] Έργο ΔΕΝ εμφανίζεται στη λίστα projects
- [ ] Έργο εμφανίζεται στον κάδο (tab "Έργα")
- [ ] **CASCADE**: Οι εργασίες του έργου ΔΕΝ εμφανίζονται στη λίστα
- [ ] **CASCADE**: Οι εργασίες εμφανίζονται στον κάδο (tab "Εργασίες Έργων")
- [ ] **CASCADE**: Τα ημερομίσθια εμφανίζονται στον κάδο (tab "Ημερομίσθια")

### 4. Soft Delete - Daily Tasks
- [ ] Διαγραφή ημερήσιας εργασίας → μεταφέρεται στον κάδο
- [ ] ΔΕΝ εμφανίζεται στη λίστα daily tasks
- [ ] Εμφανίζεται στον κάδο (tab "Ημερήσιες Εργασίες")

### 5. Soft Delete - Materials
- [ ] Διαγραφή υλικού → μεταφέρεται στον κάδο
- [ ] ΔΕΝ εμφανίζεται στη λίστα materials
- [ ] Εμφανίζεται στον κάδο (tab "Υλικά")

### 6. Soft Delete - Transformer Maintenances
- [ ] Διαγραφή συντήρησης → μεταφέρεται στον κάδο
- [ ] ΔΕΝ εμφανίζεται στη λίστα maintenances
- [ ] Εμφανίζεται στον κάδο (tab "Συντηρήσεις Μ/Σ")

### 7. Trash Index View
- [ ] 6 tabs εμφανίζονται: Έργα, Εργασίες Έργων, Ημερομίσθια, Ημερήσιες Εργασίες, Συντηρήσεις Μ/Σ, Υλικά
- [ ] Badge counters στα tabs δείχνουν αριθμό διαγραμμένων
- [ ] Φίλτρα: Αναζήτηση, Από Ημερομηνία, Έως Ημερομηνία
- [ ] Πίνακας δείχνει: Checkbox, Όνομα, Διαγράφηκε (ημερομηνία), Διαγράφηκε Από (user), Ενέργειες
- [ ] Κουμπιά: Επιλογή Όλων, Αποεπιλογή Όλων, Επαναφορά Επιλεγμένων, Οριστική Διαγραφή, Άδειασμα Κάδου

### 8. Single Item Actions
- [ ] **Restore** (πράσινο κουμπί) - επαναφέρει το στοιχείο
- [ ] Μετά το restore → item εμφανίζεται ξανά στη λίστα του
- [ ] Μετά το restore → item ΔΕΝ εμφανίζεται στον κάδο
- [ ] **Permanent Delete** (κόκκινο κουμπί) - confirmation popup
- [ ] Μετά το permanent delete → item διαγράφεται οριστικά
- [ ] Μετά το permanent delete → item ΔΕΝ εμφανίζεται πουθενά

### 9. Bulk Actions
- [ ] Επιλογή 2+ items με checkboxes
- [ ] "Επαναφορά Επιλεγμένων" enabled όταν υπάρχουν επιλογές
- [ ] Bulk restore → confirmation → όλα επαναφέρονται
- [ ] "Οριστική Διαγραφή Επιλεγμένων" enabled όταν υπάρχουν επιλογές
- [ ] Bulk delete → confirmation → όλα διαγράφονται οριστικά
- [ ] "Επιλογή Όλων" επιλέγει όλα τα items της σελίδας
- [ ] "Αποεπιλογή Όλων" καθαρίζει όλες τις επιλογές

### 10. Empty Trash
- [ ] "Άδειασμα Κάδου" κουμπί εμφανίζεται
- [ ] Confirmation popup με ΠΡΟΣΟΧΗ μήνυμα
- [ ] Αδειάζει ΜΟΝΟ την τρέχουσα κατηγορία (tab)
- [ ] Μετά το empty → badge counter μηδενίζεται για αυτήν την κατηγορία

### 11. Cascade Restore (Project)
- [ ] Επαναφορά project από κάδο
- [ ] **CASCADE**: Οι εργασίες του επαναφέρονται αυτόματα
- [ ] **CASCADE**: Τα ημερομίσθια των εργασιών επαναφέρονται αυτόματα
- [ ] Όλα εμφανίζονται ξανά στις αντίστοιχες λίστες

### 12. Cascade Permanent Delete (Project)
- [ ] Οριστική διαγραφή project από κάδο
- [ ] **CASCADE**: Οι εργασίες του διαγράφονται οριστικά
- [ ] **CASCADE**: Τα ημερομίσθια διαγράφονται οριστικά
- [ ] Κανένα record ΔΕΝ υπάρχει πλέον στη βάση

### 13. Deletion Log
- [ ] Link "Ιστορικό Διαγραφών" εμφανίζεται στο trash header
- [ ] Οδηγεί στο `/trash/log`
- [ ] Φίλτρα: Τύπος Στοιχείου, Ενέργεια
- [ ] Πίνακας δείχνει: Ημερομηνία, Τύπος, Όνομα, Ενέργεια, Χρήστης
- [ ] Ενέργειες: Διαγράφηκε (κίτρινο), Επαναφέρθηκε (πράσινο), Οριστική Διαγραφή (κόκκινο)
- [ ] Καταγράφονται όλες οι ενέργειες με timestamps

### 14. Search & Filters
- [ ] Αναζήτηση στο όνομα item → φιλτράρει σωστά
- [ ] Φίλτρο "Από Ημερομηνία" → δείχνει items από αυτή την ημερομηνία
- [ ] Φίλτρο "Έως Ημερομηνία" → δείχνει items μέχρι αυτή την ημερομηνία
- [ ] Συνδυασμός φίλτρων λειτουργεί

### 15. UI/UX
- [ ] Όλα τα badges είναι danger (red) για attention
- [ ] Icons: trash για κάδο, undo για restore, trash-alt για permanent delete
- [ ] Confirmations έχουν ΠΡΟΣΟΧΗ μηνύματα για destructive actions
- [ ] Success messages είναι πράσινα
- [ ] Error messages είναι κόκκινα
- [ ] Responsive design λειτουργεί σε mobile

### 16. Performance
- [ ] Trash index φορτώνει γρήγορα (<1s)
- [ ] Φίλτρα ανταποκρίνονται άμεσα
- [ ] Bulk actions δεν κολλάνε με 10+ items
- [ ] Deletion log φορτώνει γρήγορα

### 17. Database Integrity
- [ ] deleted_at timestamp αποθηκεύεται σωστά
- [ ] deleted_by user_id αποθηκεύεται σωστά
- [ ] deletion_log καταγράφει όλες τις ενέργειες
- [ ] CASCADE delete δεν αφήνει orphan records

---

## 🐛 Known Issues / Bugs Found

(Add any issues discovered during testing)

- None reported yet

---

## 📊 Test Results Summary

**Automated Tests:** 16/16 PASSED (100%)
**Manual Tests:** _/17 categories pending

**Overall Status:** ✅ READY FOR PRODUCTION (pending manual UI testing)

---

## 🚀 Deployment Checklist

When deploying to production (ecowatt.gr/crm):

- [ ] Upload new files:
  - `models/Trash.php`
  - `controllers/TrashController.php`
  - `views/trash/index.php`
  - `views/trash/log.php`

- [ ] Update existing files:
  - `index.php` (routes)
  - `views/includes/header.php` (sidebar menu)
  - `controllers/ProjectController.php`
  - `controllers/ProjectTasksController.php`
  - `controllers/DailyTaskController.php`
  - `controllers/MaterialController.php`
  - `controllers/TransformerMaintenanceController.php`
  - `models/Project.php`
  - `models/ProjectTask.php`
  - `models/DailyTask.php`
  - `models/Material.php`
  - `models/TransformerMaintenance.php`

- [ ] Run SQL script (database changes already applied locally):
  ```sql
  -- Already executed in local development
  -- Run on production: database/trash_system.sql
  ```

- [ ] Test on production:
  - Login as admin
  - Verify trash menu appears
  - Test soft delete on one item
  - Test restore
  - Verify deletion log

---

**Test Date:** November 19, 2025
**Tested By:** Automated Test Suite + Manual Testing Pending
**Version:** HandyCRM v1.4.0
