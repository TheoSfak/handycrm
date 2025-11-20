# 🗑️ HandyCRM Trash System - Quick Reference

## 🎯 Γρήγορη Αναφορά

### Πρόσβαση
- **URL:** `http://localhost/handycrm/?route=/trash`
- **Δικαιώματα:** Admin μόνο
- **Menu:** Sidebar → "Κάδος Απορριμμάτων" (με badge counter)

---

## 📋 6 Κατηγορίες Στοιχείων

1. **Έργα** (projects)
2. **Εργασίες Έργων** (project_tasks)
3. **Ημερομίσθια** (task_labor)
4. **Ημερήσιες Εργασίες** (daily_tasks)
5. **Συντηρήσεις Μ/Σ** (transformer_maintenances)
6. **Υλικά** (materials)

---

## ⚡ Κύριες Λειτουργίες

### 1. Soft Delete (Προσωρινή Διαγραφή)
- Διαγραφή από κανονική λίστα → μεταφορά στον κάδο
- Δεδομένα παραμένουν στη βάση με `deleted_at` timestamp
- Καταγράφεται ποιος διέγραψε και πότε

### 2. Restore (Επαναφορά)
- Επαναφορά από κάδο → επιστροφή στην κανονική λίστα
- Single item: πράσινο κουμπί ↻
- Bulk: checkboxes + "Επαναφορά Επιλεγμένων"
- **Projects:** Επαναφέρονται αυτόματα και οι εργασίες τους

### 3. Permanent Delete (Οριστική Διαγραφή)
- Οριστική αφαίρεση από βάση (ΔΕΝ αναιρείται!)
- Single item: κόκκινο κουμπί 🗑️ + confirmation
- Bulk: checkboxes + "Οριστική Διαγραφή Επιλεγμένων" + confirmation
- **Projects:** Διαγράφονται οριστικά και οι εργασίες τους

### 4. Empty Trash (Άδειασμα Κάδου)
- Διαγράφει οριστικά ΟΛΕΣ τις εγγραφές της τρέχουσας κατηγορίας
- Κουμπί: "Άδειασμα Κάδου"
- Double confirmation required

### 5. Deletion Log (Ιστορικό)
- Πλήρες audit trail όλων των ενεργειών
- Link: "Ιστορικό Διαγραφών" (πάνω δεξιά)
- Φίλτρα: Τύπος, Ενέργεια
- Shows: Ποιος, Τι, Πότε, Πώς

---

## 🔄 Cascade Logic

### Διαγραφή Έργου (Project)
```
DELETE Project #123
  ├─ Soft Delete: 5 Project Tasks
  └─ Soft Delete: 12 Task Labor records
```

### Επαναφορά Έργου
```
RESTORE Project #123
  ├─ Restore: 5 Project Tasks
  └─ Restore: 12 Task Labor records
```

### Οριστική Διαγραφή Έργου
```
PERMANENT DELETE Project #123
  ├─ DELETE: 12 Task Labor records (first)
  ├─ DELETE: 5 Project Tasks (second)
  └─ DELETE: Project #123 (last)
```

---

## 🎨 UI Elements

### Badges
- 🔴 Κόκκινο με αριθμό = deleted items count
- 🟡 Κίτρινο "Διαγράφηκε" = soft deleted action
- 🟢 Πράσινο "Επαναφέρθηκε" = restored action
- 🔴 Κόκκινο "Οριστική Διαγραφή" = permanent delete action

### Buttons
- 🟢 Undo icon = Restore
- 🔴 Trash icon = Permanent Delete
- 📋 History icon = Deletion Log

### Filters
- 🔍 Search box = αναζήτηση στο όνομα
- 📅 Date From = από ημερομηνία
- 📅 Date To = έως ημερομηνία

---

## 💻 Database Schema

### Soft Delete Columns (σε όλους τους πίνακες)
```sql
deleted_at DATETIME NULL      -- Timestamp διαγραφής
deleted_by INT NULL            -- User ID που διέγραψε
INDEX idx_deleted_at           -- Index για performance
```

### deletion_log Table
```sql
id                  INT AUTO_INCREMENT
item_type           ENUM(...)              -- Τύπος στοιχείου
item_id             INT                    -- ID του στοιχείου
item_name           VARCHAR(255)           -- Όνομα για reference
action              ENUM(deleted/restored/permanent)
user_id             INT                    -- Ποιος έκανε την ενέργεια
user_name           VARCHAR(255)           -- Username για ιστορικό
item_details        JSON                   -- Extra metadata
created_at          TIMESTAMP              -- Πότε έγινε
```

---

## 🔐 Permissions

### 4 Trash Permissions (admin-only)
1. `trash.view` - Προβολή κάδου
2. `trash.restore` - Επαναφορά στοιχείων
3. `trash.delete_permanent` - Οριστική διαγραφή
4. `trash.view_log` - Προβολή ιστορικού

---

## 📊 Queries

### Get Active Records (δεν περιλαμβάνει διαγραμμένα)
```sql
SELECT * FROM table_name WHERE deleted_at IS NULL;
```

### Get Deleted Records (μόνο διαγραμμένα)
```sql
SELECT * FROM table_name WHERE deleted_at IS NOT NULL;
```

### Soft Delete Record
```sql
UPDATE table_name 
SET deleted_at = NOW(), deleted_by = 1 
WHERE id = 123;
```

### Restore Record
```sql
UPDATE table_name 
SET deleted_at = NULL, deleted_by = NULL 
WHERE id = 123;
```

### Permanent Delete
```sql
DELETE FROM table_name WHERE id = 123;
```

---

## 🔧 Code Examples

### PHP: Soft Delete
```php
$model->update($id, [
    'deleted_at' => date('Y-m-d H:i:s'),
    'deleted_by' => $_SESSION['user_id']
]);
```

### PHP: Restore via Trash Model
```php
$trashModel = new Trash($db->connect());
$trashModel->restoreItem('project', $id, $userId, $userName);
```

### PHP: Get Deleted Count
```php
$counts = $trashModel->getDeletedCountByType();
// ['project' => 5, 'daily_task' => 3, ...]
```

### PHP: Check if Item is Deleted
```php
$sql = "SELECT deleted_at FROM projects WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$result = $stmt->fetch();
$isDeleted = !empty($result['deleted_at']);
```

---

## 🚨 Important Notes

### ⚠️ Cascade Behavior
- **Projects only** have cascade delete/restore
- Other items are independent
- Future: Can add cascade for Customers → Projects

### ⚠️ Performance
- Indexes on `deleted_at` ensure fast queries
- Queries with `WHERE deleted_at IS NULL` use index
- Minimal overhead (<1% on large tables)

### ⚠️ Storage
- Soft deleted records remain in database
- Use "Empty Trash" periodically
- Consider auto-expiry (future enhancement)

### ⚠️ Security
- Admin-only access enforced
- All POST actions require authentication
- Confirmation dialogs for destructive actions

---

## 📁 File Locations

### Models
- `models/Trash.php` - Main trash logic

### Controllers
- `controllers/TrashController.php` - Trash routes handler

### Views
- `views/trash/index.php` - Main trash interface
- `views/trash/log.php` - Deletion log view

### Modified Files
- `index.php` - Routes
- `views/includes/header.php` - Sidebar menu
- 5 Controllers - Soft delete implementation
- 5 Models - Filtering deleted records

---

## 🧪 Testing

### Manual Test Steps
1. Login as admin
2. Delete a project from projects list
3. Visit `/trash` → see project in "Έργα" tab
4. Check "Εργασίες Έργων" tab → see project's tasks
5. Click restore on project
6. Go back to projects list → project is back
7. Check tasks → tasks are restored too

### Automated Tests
```bash
php test_trash_system.php
```
Expected: 16/16 PASSED ✓

---

## 🐛 Troubleshooting

### Badge Counter Not Showing
- Check if `$GLOBALS['db']` is set
- Verify Trash Model is included in header.php
- Check database connection

### Items Not Appearing in Trash
- Verify `deleted_at` column exists
- Check if item has `deleted_at IS NOT NULL`
- Try refreshing page

### Restore Not Working
- Check admin permissions
- Verify `restoreItem()` method
- Check database logs for errors

### Cascade Not Working
- Verify item type is 'project'
- Check if tasks exist for project
- Review ProjectController cascade code

---

## 📚 Documentation Files

- `TRASH_IMPLEMENTATION_SUMMARY.md` - Full implementation details
- `TRASH_SYSTEM_TESTING.md` - Testing checklist
- `test_trash_system.php` - Automated test suite
- `TRASH_QUICK_REFERENCE.md` - This file

---

## 🎓 Best Practices

1. **Always use soft delete first** - Δώσε χρόνο για μετάνοια
2. **Review trash regularly** - Μη το αφήσεις να γεμίσει
3. **Use deletion log** - Παρακολούθησε ποιος διαγράφει τι
4. **Train users** - Εξήγησε τη διαφορά soft vs permanent
5. **Backup before empty** - Πριν το "Άδειασμα Κάδου"

---

**Last Updated:** November 19, 2025
**Version:** 1.0.0
**HandyCRM:** v1.4.0
