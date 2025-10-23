# HandyCRM v1.3.7 Release Package
## Οδηγίες Εγκατάστασης για ecowatt.gr/crm

---

## 📦 Τι περιέχει αυτό το release:

### 1. **Αρχεία PHP που πρέπει να ανέβουν:**
- `config/config.php` - Διορθωμένη formatCurrency() συνάρτηση
- `controllers/ProjectReportController.php` - Διορθώσεις για PDF reports
- `controllers/ProjectTasksController.php` - Διορθώσεις για task editing
- `views/projects/show.php` - Fix για currency display
- `views/projects/tasks/add.php` - Προαιρετικά labor/materials
- `views/projects/tasks/edit.php` - Προαιρετικά labor/materials
- `views/users/index.php` - Support για 4 roles
- `languages/el.json` - Μεταφράσεις για supervisor/assistant

### 2. **Assets που πρέπει να ανέβουν:**
- `assets/js/date-formatter.js` - Fix για ημερομηνίες DD/MM/YYYY

### 3. **Libraries (ΣΗΜΑΝΤΙΚΟ!):**
- Ολόκληρος ο φάκελος `lib/` με το Dompdf για PDF generation

### 4. **Database Migration:**
- `RELEASE_1.3.7_MIGRATION.sql` - Τρέξε αυτό στη βάση ΠΡΙΝ ανεβάσεις τα αρχεία

---

## 🚀 Βήματα Εγκατάστασης (ΑΚΟΛΟΥΘΗΣΕ ΤΗ ΣΕΙΡΑ!)

### **ΒΗΜΑ 1: Backup**
```bash
# Κάνε backup της βάσης δεδομένων ΚΑΙ των αρχείων!
# Μπορείς να το κάνεις από το Hostinger hPanel
```

### **ΒΗΜΑ 2: Database Migration**
1. Πήγαινε στο **phpMyAdmin** του Hostinger
2. Επέλεξε τη βάση `u858321845_handycrm1`
3. Πήγαινε στο tab **SQL**
4. Κάνε **copy-paste** όλο το `RELEASE_1.3.7_MIGRATION.sql`
5. Πάτα **Go** / **Εκτέλεση**
6. Περίμενε να δεις: `HandyCRM v1.3.7 Migration completed successfully!`

### **ΒΗΜΑ 3: Διόρθωση config.php (ΠΡΟΣΟΧΗ!)**

**ΜΗΝ** ανεβάσεις ολόκληρο το `config.php`!

Άνοιξε το `config/config.php` στο ecowatt.gr και:

1. **Βρες** την συνάρτηση `formatCurrency()`
2. **Άλλαξε** μόνο τη γραμμή:
   ```php
   // ΠΑΛΙΟ (ΛΑΘΟΣ):
   return $formatted . ' ' . CURRENCY_SYMBOL;
   
   // ΝΕΟ (ΣΩΣΤΟ):
   return $formatted . ' €';
   ```

3. **Πρόσθεσε** αυτή τη νέα συνάρτηση μετά την `formatCurrency()`:
   ```php
   function formatNumber($number, $decimals = 2) {
       $number = is_numeric($number) ? (float)$number : 0.0;
       return number_format($number, $decimals, ',', '.');
   }
   ```

### **ΒΗΜΑ 4: Ανέβασμα Controllers**
Ανέβασε αυτά τα αρχεία (REPLACE existing):
```
Desktop/handycrm/controllers/ProjectReportController.php 
  → ecowatt.gr/crm/controllers/

Desktop/handycrm/controllers/ProjectTasksController.php
  → ecowatt.gr/crm/controllers/
```

### **ΒΗΜΑ 5: Ανέβασμα Views**
Ανέβασε αυτά τα αρχεία (REPLACE existing):
```
Desktop/handycrm/views/projects/show.php
  → ecowatt.gr/crm/views/projects/

Desktop/handycrm/views/projects/tasks/add.php
  → ecowatt.gr/crm/views/projects/tasks/

Desktop/handycrm/views/projects/tasks/edit.php
  → ecowatt.gr/crm/views/projects/tasks/

Desktop/handycrm/views/users/index.php
  → ecowatt.gr/crm/views/users/
```

### **ΒΗΜΑ 6: Ανέβασμα Languages**
Ανέβασε αυτό το αρχείο (REPLACE existing):
```
Desktop/handycrm/languages/el.json
  → ecowatt.gr/crm/languages/
```

### **ΒΗΜΑ 7: Ανέβασμα Assets**
Ανέβασε αυτό το αρχείο:
```
Desktop/handycrm/assets/js/date-formatter.js
  → ecowatt.gr/crm/assets/js/
```

### **ΒΗΜΑ 8: Ανέβασμα lib/ Folder (ΣΗΜΑΝΤΙΚΟ!)**
```
Desktop/handycrm/lib/ (ΟΛΟΚΛΗΡΟΣ Ο ΦΑΚΕΛΟΣ!)
  → ecowatt.gr/crm/lib/
```
Αυτό είναι το **Dompdf library** για PDF generation.
Χωρίς αυτό, τα PDF reports ΔΕΝ θα δουλεύουν!

---

## ✅ Έλεγχος Εγκατάστασης

Μετά την εγκατάσταση, έλεγξε:

1. ✅ Οι ημερομηνίες εμφανίζονται σωστά (DD/MM/YYYY)
2. ✅ Τα ποσά εμφανίζονται σωστά (0,00 € αντί για 0,00 262145)
3. ✅ Τα PDF reports δουλεύουν (Projects → Report)
4. ✅ Μπορείς να δημιουργήσεις εργασία χωρίς υλικά ή labor
5. ✅ Όταν κάνεις edit σε εργασία, δεν μηδενίζονται τα labor
6. ✅ Στη λίστα Users εμφανίζονται όλοι οι 4 ρόλοι (Admin, Supervisor, Technician, Assistant)

---

## 🐛 Troubleshooting

### "Το PDF δεν δημιουργείται"
→ Έλεγξε αν ανέβασες το `lib/` folder

### "Οι ημερομηνίες είναι λάθος"
→ Έλεγξε αν υπάρχει το `assets/js/date-formatter.js`

### "Βλέπω 0,00 262145"
→ Έλεγξε αν έκανες το fix στο `config.php` (CURRENCY_SYMBOL → €)

### "Τα labor μηδενίζονται"
→ Έλεγξε αν ανέβασες το `ProjectTasksController.php`

---

## 📝 Σημειώσεις

- **Μη διαγράψεις** το παλιό `config.php`! Απλά άλλαξε 2 συναρτήσεις.
- Το `lib/` folder είναι **μεγάλο** (~10MB). Χρησιμοποίησε FTP αν το File Manager είναι αργό.
- Αν κάτι πάει λάθος, restore το backup!

---

## 🎉 Τελικό Αποτέλεσμα

Μετά από αυτό το release, το HandyCRM θα έχει:
- ✅ 4 ρόλους χρηστών (Admin, Supervisor, Technician, Assistant)
- ✅ Προαιρετικά υλικά και εργατικά στις εργασίες
- ✅ PDF reports που δουλεύουν
- ✅ Σωστή εμφάνιση ποσών (€)
- ✅ Σωστή εμφάνιση ημερομηνιών (DD/MM/YYYY)
- ✅ Fix για το labor editing bug

---

**Version:** 1.3.7  
**Date:** October 23, 2025  
**Author:** Theodore Sfakianakis
