# Materials Catalog System - Testing Guide

## 🎯 Σκοπός
Τεστάρισμα του συστήματος καταλόγου υλικών με autocomplete στις εργασίες.

## 📊 Test Data που προστέθηκαν

### Κατηγορίες (6):
1. Ηλεκτρολογικά
2. Υδραυλικά
3. Οικοδομικά
4. Χρώματα & Βερνίκια
5. Μηχανολογικά
6. Άλλα

### Υλικά (8):
1. **Καλώδιο NYM 3x1.5** - Ηλεκτρολογικά - 1.20€/μέτρα
2. **Καλώδιο NYM 3x2.5** - Ηλεκτρολογικά - 1.80€/μέτρα
3. **Πρίζα Σούκο** - Ηλεκτρολογικά - 2.50€/τεμάχια
4. **Σωλήνας PVC Φ32** - Υδραυλικά - 3.20€/μέτρα
5. **Κολάρο Φ32** - Υδραυλικά - 0.80€/τεμάχια
6. **Τσιμέντο 25kg** - Οικοδομικά - 5.50€/τεμάχια
7. **Άμμος** - Οικοδομικά - 25.00€/κ.μ.
8. **Χρώμα Πλαστικό Λευκό** - Χρώματα & Βερνίκια - 8.50€/λίτρα

## 🧪 Test Scenarios

### Test 1: View Materials Catalog
**URL:** http://localhost/handycrm/?route=/materials

**Αναμενόμενα:**
- ✅ Εμφάνιση 8 υλικών σε πίνακα
- ✅ Στατιστικά: 8 Σύνολο, 8 Ενεργά, 6 Κατηγορίες
- ✅ Φίλτρα: Αναζήτηση, Κατηγορία, Κατάσταση

**Ενέργειες:**
1. Δοκίμασε αναζήτηση: "Καλώδιο" → Θα εμφανίσει 2 αποτελέσματα
2. Φίλτρο κατηγορίας: "Ηλεκτρολογικά" → 3 αποτελέσματα
3. Κλικ "Επεξεργασία" σε κάποιο υλικό → Θα ανοίξει form

---

### Test 2: Manage Categories
**URL:** http://localhost/handycrm/?route=/materials/categories

**Αναμενόμενα:**
- ✅ Εμφάνιση 6 κατηγοριών
- ✅ Κάθε κατηγορία δείχνει πόσα υλικά περιέχει
- ✅ Κουμπί "Νέα Κατηγορία"

**Ενέργειες:**
1. Κλικ "Νέα Κατηγορία"
2. Συμπλήρωσε: Όνομα="Ξυλουργικά", Περιγραφή="Υλικά ξυλουργικής"
3. Αποθήκευση → Θα προστεθεί στη λίστα
4. Δοκίμασε επεξεργασία/διαγραφή

---

### Test 3: Add Material
**URL:** http://localhost/handycrm/?route=/materials/add

**Αναμενόμενα:**
- ✅ Form με όλα τα πεδία
- ✅ Dropdown κατηγοριών
- ✅ Datalist για μονάδες μέτρησης
- ✅ Toggle για ενεργό/ανενεργό

**Ενέργειες:**
1. Συμπλήρωσε νέο υλικό:
   - Όνομα: "Διακόπτης Μονός"
   - Κατηγορία: Ηλεκτρολογικά
   - Μονάδα: τεμάχια
   - Τιμή: 3.80€
   - Προμηθευτής: Ηλεκτρονική ΑΕ
2. Αποθήκευση → Redirect στη λίστα με success message

---

### Test 4: 🎯 AUTOCOMPLETE IN TASKS (ΚΥΡΙΟ TEST!)
**URL:** Πήγαινε σε κάποιο έργο → Εργασίες → Νέα Εργασία

**Αναμενόμενα:**
1. ✅ Στο πεδίο "Ονομασία Υλικού" βλέπεις hint: "(Ξεκινήστε να γράφετε για προτάσεις)"
2. ✅ Γράφοντας "Καλώ" → Εμφανίζεται dropdown με 2 καλώδια
3. ✅ Το dropdown δείχνει: Όνομα, Κατηγορία (badge), Μονάδα, Τιμή
4. ✅ Με βελάκια ↑↓ navigaρεις στις επιλογές
5. ✅ Enter ή click → Επιλέγει το υλικό
6. ✅ AUTO-POPULATE: Μονάδα και Τιμή γεμίζουν αυτόματα!
7. ✅ Focus πηγαίνει στο Ποσότητα για γρήγορη εισαγωγή

**Ενέργειες:**
1. Κλικ "Προσθήκη Υλικού"
2. Στο πεδίο όνομα γράψε: "Καλώ"
3. Περίμενε 300ms → Dropdown εμφανίζεται
4. Επίλεξε "Καλώδιο NYM 3x1.5"
5. **VERIFY:**
   - Μονάδα έγινε: "μέτρα" ✅
   - Τιμή έγινε: "1.20" ✅
6. Συμπλήρωσε Ποσότητα: 50
7. Σύνολο υπολογίζεται: 60.00€ ✅
8. Πρόσθεσε άλλο υλικό με autocomplete
9. Αποθήκευσε την εργασία

---

### Test 5: Verify catalog_material_id saved
**Database Check:**

```powershell
C:\xampp\mysql\bin\mysql.exe -u root handycrm -e "SELECT tm.id, tm.name, tm.unit, tm.unit_price, tm.catalog_material_id, mc.name as catalog_name FROM task_materials tm LEFT JOIN materials_catalog mc ON tm.catalog_material_id = mc.id ORDER BY tm.id DESC LIMIT 5;"
```

**Αναμενόμενα:**
- ✅ Το `catalog_material_id` δεν είναι NULL
- ✅ Το `catalog_name` δείχνει το όνομα από τον κατάλογο
- ✅ Το `name` στο task_materials ταιριάζει με το catalog

---

### Test 6: Edit Task with Existing Materials
**Ενέργειες:**
1. Άνοιξε την εργασία που μόλις έκανες
2. Κλικ "Επεξεργασία"
3. Τα υλικά εμφανίζονται με τα σωστά δεδομένα ✅
4. Πρόσθεσε νέο υλικό με autocomplete
5. Αποθήκευση → Όλα αποθηκεύονται σωστά

---

### Test 7: Edit Catalog Material & Check Update
**Ενέργειες:**
1. Πήγαινε στον κατάλογο: /materials
2. Επεξεργασία "Καλώδιο NYM 3x1.5"
3. Άλλαξε την τιμή από 1.20€ → 1.35€
4. Αποθήκευση
5. Πήγαινε σε νέα εργασία
6. Autocomplete για "Καλώ" → Θα δείχνει 1.35€ ✅
7. Επιλογή → Γεμίζει με 1.35€ ✅

---

### Test 8: Try to Delete Used Material
**Ενέργειες:**
1. Στον κατάλογο, κλικ "Διαγραφή" σε υλικό που χρησιμοποιείται
2. Modal εμφανίζει warning: "Αν το υλικό χρησιμοποιείται, θα γίνει ανενεργό"
3. Επιβεβαίωση διαγραφής
4. **VERIFY:** Το υλικό έγινε is_active=0 αντί να διαγραφεί ✅
5. Δεν εμφανίζεται πια στο autocomplete ✅
6. Αλλά οι εργασίες που το χρησιμοποιούν εξακολουθούν να το έχουν ✅

---

### Test 9: Keyboard Navigation
**Ενέργειες:**
1. Νέα εργασία → Προσθήκη υλικού
2. Γράψε "Σωλ"
3. **Keyboard test:**
   - ↓ (Arrow Down) → Highlight επόμενο item
   - ↑ (Arrow Up) → Highlight προηγούμενο
   - Enter → Επιλογή highlighted item
   - Escape → Κλείσιμο dropdown
4. Όλα λειτουργούν ομαλά ✅

---

### Test 10: Performance & Edge Cases
**Ενέργειες:**
1. **Empty search:** Γράψε "xyz123" → "Δεν βρέθηκαν αποτελέσματα"
2. **Short query:** Γράψε "Κ" (1 χαρακτήρας) → Δεν εμφανίζεται dropdown (min 2 chars)
3. **Debounce:** Γράψε γρήγορα "Καλώδιο" → Μόνο 1 API call (όχι 8!)
4. **Click outside:** Άνοιξε dropdown, κλικ έξω → Κλείνει ✅
5. **Multiple rows:** Πρόσθεσε 3 υλικά → Autocomplete λειτουργεί σε όλα ✅

---

## ✅ Success Criteria

Το σύστημα είναι επιτυχές αν:

1. ✅ Ο κατάλογος εμφανίζει όλα τα υλικά
2. ✅ Οι κατηγορίες διαχειρίζονται σωστά
3. ✅ Το autocomplete εμφανίζεται μετά από 2 χαρακτήρες
4. ✅ Η επιλογή υλικού auto-populate τη μονάδα και τιμή
5. ✅ Το `catalog_material_id` αποθηκεύεται στη βάση
6. ✅ Τα υλικά που χρησιμοποιούνται γίνονται ανενεργά αντί να διαγράφονται
7. ✅ Το keyboard navigation λειτουργεί (↑↓ Enter Escape)
8. ✅ Το debouncing αποτρέπει υπερβολικά API calls

---

## 🐛 Troubleshooting

### Autocomplete δεν εμφανίζεται
- Check browser console για errors
- Verify το `material-autocomplete.js` φορτώνεται
- Verify η route `/api/materials/search` λειτουργεί

### Τιμή/Μονάδα δεν auto-populate
- Check το response του API έχει `unit` και `price` fields
- Check το `data-row-index` attribute στο input

### Database errors
- Verify τα migrations τρέξανε: `name`, `unit`, `catalog_material_id` υπάρχουν
- Check foreign key constraints

---

## 📝 Notes

- Η ελάχιστη query είναι 2 χαρακτήρες
- Το debounce delay είναι 300ms
- Το limit των αποτελεσμάτων είναι 10
- Τα ανενεργά υλικά δεν εμφανίζονται στο autocomplete

**Version:** v1.2.0 - Materials Catalog System
**Date:** October 15, 2025
