# 🗑️ Database Reset Feature

## Τι είναι;

Ένα εργαλείο μηδενισμού δεδομένων που επιτρέπει στον διαχειριστή να διαγράψει **ΟΛΕΣ** τις εγγραφές χρηστών (πελάτες, έργα, τιμολόγια κτλ) και να ξεκινήσει από την αρχή.

## Πού το βρίσκω;

**Ρυθμίσεις → Επικίνδυνη Ζώνη → Μηδενισμός Βάσης**

ή άμεσα:
```
https://yourdomain.com/settings/reset-data
```

## Τι διαγράφει;

- ✅ Πελάτες (customers)
- ✅ Έργα (projects)
- ✅ Τιμολόγια (invoices)
- ✅ Προσφορές (quotes)
- ✅ Ραντεβού (appointments)
- ✅ Υλικά (materials)
- ✅ Επικοινωνίες (customer_communications)
- ✅ Αρχεία έργων (project_files)
- ✅ Κινήσεις υλικών (material_movements)
- ✅ Tasks έργων (project_tasks)
- ✅ Items τιμολογίων (invoice_items)
- ✅ Items προσφορών (quote_items)
- ✅ Ειδοποιήσεις (notifications)

## Τι ΔΕΝ διαγράφει;

- ❌ Χρήστες (users) - Κρατάει τον admin και όλους τους users
- ❌ Ρυθμίσεις (settings) - Κρατάει όλες τις ρυθμίσεις της εταιρείας

## Πώς λειτουργεί;

### 1. Πληροφορίες
Η σελίδα δείχνει:
- Πόσοι πελάτες θα διαγραφούν
- Πόσα έργα θα διαγραφούν
- Πόσα τιμολόγια θα διαγραφούν
- Πόσες προσφορές θα διαγραφούν
- Πόσα ραντεβού θα διαγραφούν
- Πόσα υλικά θα διαγραφούν

### 2. Επιβεβαίωση
Για να προχωρήσεις πρέπει:
- Να πληκτρολογήσεις: **RESET DATA**
- Να πατήσεις: "ΜΗΔΕΝΙΣΜΟΣ ΒΑΣΗΣ"
- Να επιβεβαιώσεις στο JavaScript confirm dialog

### 3. Εκτέλεση
Το σύστημα:
- Διαγράφει όλες τις εγγραφές (με σειρά που σέβεται τα foreign keys)
- Κάνει reset τα AUTO_INCREMENT σε όλους τους πίνακες
- Χρησιμοποιεί transaction (rollback αν πάει κάτι στραβά)

### 4. Επιτυχία
Μετά το reset:
- Εμφανίζει μήνυμα επιτυχίας
- Προτείνει επιστροφή στο dashboard

## Ασφάλεια

### Προστασίες:
- ✅ Μόνο admin έχει πρόσβαση
- ✅ Απαιτεί ακριβή κωδικό επιβεβαίωσης
- ✅ JavaScript confirmation dialog
- ✅ Transaction-based (rollback on error)
- ✅ Προειδοποιήσεις σε κάθε βήμα

### Συμβουλή:
**ΠΑΝΤΑ κάνε backup της βάσης πριν το χρησιμοποιήσεις!**

## Πότε να το χρησιμοποιήσω;

### Κατάλληλες περιπτώσεις:
- 🧪 Μετά από testing με demo data
- 🔄 Όταν θέλεις να ξεκινήσεις από την αρχή
- 🎓 Μετά από εκπαίδευση χρηστών
- 🧹 Spring cleaning της βάσης

### ΜΗ το χρησιμοποιήσεις αν:
- ❌ Έχεις production data που χρειάζεσαι
- ❌ Δεν έχεις κάνει backup
- ❌ Δεν είσαι 100% σίγουρος

## Τεχνικά Χαρακτηριστικά

### Files:
```
views/settings/reset-data.php    - UI page
controllers/SettingsController.php - resetData() method
index.php                         - Route: /settings/reset-data
```

### Database Operations:
```sql
DELETE FROM invoice_items;
DELETE FROM invoices;
DELETE FROM quote_items;
DELETE FROM quotes;
DELETE FROM project_files;
DELETE FROM material_movements;
DELETE FROM project_tasks;
DELETE FROM projects;
DELETE FROM appointments;
DELETE FROM customer_communications;
DELETE FROM customers;
DELETE FROM materials;
DELETE FROM notifications;

ALTER TABLE [table] AUTO_INCREMENT = 1;
```

### Transaction Safety:
```php
$conn->beginTransaction();
try {
    // Delete all data
    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
}
```

## UI/UX

### Χρώματα:
- 🔴 Κόκκινο για danger zone
- 🟡 Κίτρινο για warnings
- 🔵 Μπλε για info
- 🟢 Πράσινο για success

### Feedback:
- Εμφανίζει counts πριν τη διαγραφή
- Live feedback στο input field (πράσινο όταν σωστό)
- Alert messages μετά την ενέργεια

## Changelog

**v1.0.0** - Initial Implementation
- Reset all user data tables
- Keep users and settings
- Admin-only access
- Confirmation system
- Transaction safety
- Beautiful UI with warnings

---

**Author:** Theodore Sfakianakis  
**Email:** theodore.sfakianakis@gmail.com  
**Date:** October 9, 2025
