# 📦 DAILY TASKS MODULE - DEPLOYMENT CHECKLIST
**Ημερομηνία:** 2025-11-10  
**Έκδοση:** v1.5.0  
**Module:** Εργασίες Ημέρας (Daily Tasks)

---

## 🗄️ 1. ΒΑΣΗ ΔΕΔΟΜΕΝΩΝ (SQL)

### Εκτέλεση στο παραγωγικό:
```sql
-- Τρέξε το αρχείο: DEPLOYMENT_DAILY_TASKS.sql
mysql -u [username] -p [database_name] < DEPLOYMENT_DAILY_TASKS.sql
```

**Τι κάνει:**
- Δημιουργεί τον πίνακα `daily_tasks` με 21 πεδία
- Δημιουργεί 6 indexes για ταχύτητα
- Δημιουργεί 2 foreign keys (technician_id, created_by)

---

## 📁 2. ΑΡΧΕΙΑ ΓΙΑ ΑΝΕΒΑΣΜΑ

### Backend Files (Controllers)
```
controllers/DailyTaskController.php
```
**Τοποθεσία:** `/controllers/DailyTaskController.php`  
**Περιγραφή:** Κύριος controller με 10 methods + email functionality

### Model Files
```
models/DailyTask.php
```
**Τοποθεσία:** `/models/DailyTask.php`  
**Περιγραφή:** Model με CRUD operations, auto task numbering, statistics

### View Files
```
views/daily-tasks/index.php
views/daily-tasks/create.php
views/daily-tasks/edit.php
views/daily-tasks/show.php
views/daily-tasks/email.php
```
**Τοποθεσία:** `/views/daily-tasks/`  
**Περιγραφή:** Όλα τα views για το module

### Updated Files (Existing)
```
index.php                        (προσθήκη 10 routes)
views/includes/header.php        (προσθήκη menu item)
classes/EmailService.php         (fix για key-value settings)
```

---

## 📂 3. ΔΗΜΙΟΥΡΓΙΑ ΦΑΚΕΛΩΝ

### Uploads Directory
Δημιούργησε τον φάκελο για φωτογραφίες:
```bash
mkdir -p uploads/daily-tasks
chmod 755 uploads/daily-tasks
```

**Σημαντικό:** Βεβαιώσου ότι ο web server έχει write permissions!

---

## 🔧 4. ROUTES (index.php)

### Προστέθηκαν οι ακόλουθες routes:
```php
/daily-tasks                    → index()
/daily-tasks/create             → create()
/daily-tasks/store              → store() [POST]
/daily-tasks/view/{id}          → show($id)
/daily-tasks/edit/{id}          → edit($id)
/daily-tasks/update/{id}        → update($id) [POST]
/daily-tasks/delete/{id}        → delete($id) [POST]
/daily-tasks/delete-photo/{id}  → deletePhoto($id) [POST - AJAX]
/daily-tasks/toggle-invoiced/{id} → toggleInvoiced($id) [POST - AJAX]
/daily-tasks/send-email/{id}    → sendEmail($id)
```

---

## 🎨 5. MENU NAVIGATION

**Προστέθηκε στο sidebar (header.php):**
```html
<li class="nav-item">
    <a href="<?= BASE_URL ?>/daily-tasks">
        <i class="fas fa-clipboard-list"></i> Εργασίες Ημέρας
    </a>
</li>
```
**Θέση:** Μετά το "Συντηρήσεις Υ/Σ"

---

## ✅ 6. CHECKLIST ΑΝΕΒΑΣΜΑΤΟΣ

- [ ] **SQL:** Τρέξε το `DEPLOYMENT_DAILY_TASKS.sql` στη βάση
- [ ] **Φάκελος:** Δημιούργησε `uploads/daily-tasks` με permissions 755
- [ ] **Backend:** Ανέβασε `controllers/DailyTaskController.php`
- [ ] **Model:** Ανέβασε `models/DailyTask.php`
- [ ] **Views:** Ανέβασε όλο τον φάκελο `views/daily-tasks/`
- [ ] **Routes:** Ανέβασε ενημερωμένο `index.php`
- [ ] **Menu:** Ανέβασε ενημερωμένο `views/includes/header.php`
- [ ] **Email Fix:** Ανέβασε ενημερωμένο `classes/EmailService.php`
- [ ] **Helper:** Βεβαιώσου ότι υπάρχει `helpers/app_display_name.php`
- [ ] **Settings:** Πήγαινε στο `/settings` και όρισε "Διακριτικό Τίτλο Εφαρμογής"
- [ ] **Test:** Δοκίμασε να δημιουργήσεις μια εργασία
- [ ] **Test:** Δοκίμασε upload φωτογραφίας
- [ ] **Test:** Δοκίμασε αποστολή email (αν έχεις SMTP)

---

## 📋 7. ΛΙΣΤΑ ΑΡΧΕΙΩΝ (Copy-Paste Commands)

### Για Linux/SSH:
```bash
# Upload files
scp controllers/DailyTaskController.php user@server:/path/to/handycrm/controllers/
scp models/DailyTask.php user@server:/path/to/handycrm/models/
scp -r views/daily-tasks/ user@server:/path/to/handycrm/views/
scp index.php user@server:/path/to/handycrm/
scp views/includes/header.php user@server:/path/to/handycrm/views/includes/
scp classes/EmailService.php user@server:/path/to/handycrm/classes/

# Create uploads folder
ssh user@server "mkdir -p /path/to/handycrm/uploads/daily-tasks && chmod 755 /path/to/handycrm/uploads/daily-tasks"

# Run SQL
ssh user@server "mysql -u dbuser -p dbname < /tmp/DEPLOYMENT_DAILY_TASKS.sql"
```

### Για FTP/cPanel:
1. Ανέβασε τα αρχεία με File Manager
2. Δημιούργησε φάκελο `uploads/daily-tasks`
3. Τρέξε το SQL από phpMyAdmin

---

## 🔍 8. TESTING ΜΕΤΑ ΤΟ DEPLOYMENT

### Test Scenarios:
1. **Δημιουργία Εργασίας:**
   - Πήγαινε στο `/daily-tasks/create`
   - Συμπλήρωσε όλα τα πεδία
   - Ανέβασε φωτογραφία
   - Πρόσθεσε δεύτερο τεχνικό
   - Αποθήκευσε

2. **Προβολή & Επεξεργασία:**
   - Άνοιξε την εργασία που δημιούργησες
   - Δοκίμασε edit
   - Διέγραψε μια φωτογραφία (AJAX)
   - Πρόσθεσε νέα φωτογραφία

3. **Φίλτρα & Αναζήτηση:**
   - Δοκίμασε search
   - Δοκίμασε date range filter
   - Δοκίμασε technician filter

4. **Email (αν έχεις SMTP):**
   - Πήγαινε στο view εργασίας
   - Πάτα "Email"
   - Στείλε σε έγκυρο email
   - Έλεγξε ότι έφτασε το PDF

5. **AJAX Functions:**
   - Toggle "Τιμολογήθηκε" checkbox (instant update)
   - Delete photo από edit page

---

## 🆘 9. TROUBLESHOOTING

### Αν δεν φαίνεται το menu:
```php
// Έλεγξε στο header.php αν έχει:
<?php if ($isAdmin || $isSupervisor || can('daily_task.view')): ?>
```

### Αν δεν ανεβαίνουν φωτογραφίες:
```bash
# Έλεγξε permissions:
ls -la uploads/daily-tasks
chmod 755 uploads/daily-tasks
chown www-data:www-data uploads/daily-tasks  # ή nginx/apache user
```

### Αν δεν στέλνει email:
- Έλεγξε ότι τα SMTP settings είναι σωστά στη βάση
- Βεβαιώσου ότι το `company_logo` path είναι σωστό
- Δοκίμασε χωρίς το "Αποστολή αντιγράφου" checkbox

### Αν δεν δουλεύουν τα routes:
```php
// Έλεγξε ότι το index.php έχει όλες τις routes
// Βρες τη γραμμή: } elseif (preg_match('/\/daily-tasks\/view\/(\d+)/', ...
```

---

## 📊 10. FEATURES ΠΟΥ ΠΕΡΙΛΑΜΒΑΝΟΝΤΑΙ

✅ Auto task numbering (DT-YYYY-0001)  
✅ Photo upload με preview και thumbnails  
✅ Multi-technician support  
✅ Time tracking (manual hours OR time range)  
✅ Advanced filtering (7 filters)  
✅ AJAX toggles (invoiced status, photo deletion)  
✅ Email με PDF attachment (on-the-fly generation)  
✅ PDF με company logo και header/footer  
✅ Lightbox photo gallery  
✅ Print-friendly view  
✅ Status tracking (completed/in_progress/cancelled)  
✅ Materials & notes fields  

---

## 🎯 11. ΜΕΛΛΟΝΤΙΚΕΣ ΒΕΛΤΙΩΣΕΙΣ (Optional)

- [ ] Dashboard widget για today's tasks
- [ ] Export to Excel
- [ ] Permissions integration
- [ ] Mobile responsive improvements
- [ ] Email templates customization
- [ ] Recurring tasks feature

---

**Developed by:** GitHub Copilot  
**Date:** November 10, 2025  
**Version:** 1.5.0
