# HandyCRM v1.3.5 - EMERGENCY HOTFIX GUIDE

## 🚨 CRITICAL ERRORS FOUND

### Error 1: Missing `paid_by` column
```
Fatal error: Column not found: 1054 Unknown column 'tl.paid_by'
```

### Error 2: Missing translation
```
Language key "menu.payments" instead of "Πληρωμές"
```

---

## ⚡ INSTANT FIX (5 minutes)

### Step 1: Run Hotfix SQL (phpMyAdmin)

1. Login to phpMyAdmin: https://1stop.gr:2083/cpsess.../phpMyAdmin
2. Select database: `u858321845_handycrm`
3. Click **SQL** tab
4. Copy-paste this SQL:

```sql
-- HOTFIX v1.3.5 - Add missing paid_by column

SET @dbname = DATABASE();
SET @tablename = 'task_labor';
SET @columnname = 'paid_by';

SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = @columnname
);

SET @sql = IF(@column_exists = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT UNSIGNED NULL COMMENT ''User ID who marked this as paid'''),
    'SELECT ''Column already exists'' as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'HOTFIX COMPLETE!' as status;
```

5. Click **Go**
6. Should see: "HOTFIX COMPLETE!"

### Step 2: Upload Missing Files (FTP/cPanel File Manager)

#### Upload these files from local to production:

**File 1**: `languages/el.json`
- **Local**: `C:\Users\user\Desktop\handycrm\languages\el.json`
- **Remote**: `/home/u858321845/domains/1stop.gr/public_html/languages/el.json`
- **Why**: Contains "payments": "Πληρωμές" translation

**File 2**: `migrations/hotfix_1.3.5_paid_by.sql`
- **Local**: `C:\Users\user\Desktop\handycrm\migrations\hotfix_1.3.5_paid_by.sql`
- **Remote**: `/home/u858321845/domains/1stop.gr/public_html/migrations/hotfix_1.3.5_paid_by.sql`
- **Why**: For future reference

### Step 3: Verify Fix

1. **Clear browser cache**: Ctrl+F5
2. **Go to**: https://1stop.gr/payments
3. **Should work**: No errors, "Πληρωμές" shows correctly

---

## 🔍 ROOT CAUSE ANALYSIS

### Why This Happened

1. **Migration didn't run automatically**:
   - The migration system requires manual trigger
   - AutoMigration class may not be activated on production

2. **Missing files not uploaded**:
   - `languages/el.json` was updated locally but not on git/production
   - Git pull doesn't include `languages/` folder (not in repo?)

---

## 📝 FULL MIGRATION (if hotfix not enough)

If you want to run the COMPLETE migration:

```sql
-- Run this in phpMyAdmin SQL tab:

-- 1. Update users role column
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'supervisor', 'technician', 'assistant') 
DEFAULT 'technician';

-- 2. Add paid_by column
SET @dbname = DATABASE();
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = 'task_labor' 
    AND COLUMN_NAME = 'paid_by'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE task_labor ADD COLUMN paid_by INT UNSIGNED NULL',
    'SELECT ''Already exists'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Add is_active column to users
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'is_active'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1',
    'SELECT ''Already exists'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Done!
SELECT 'FULL MIGRATION COMPLETE!' as status;
```

---

## ✅ VERIFICATION CHECKLIST

After applying fixes:

- [ ] Payments page loads without errors
- [ ] Menu shows "Πληρωμές" (not language key)
- [ ] Supervisor role available in user forms
- [ ] Payment statistics show correctly
- [ ] Bulk payment button works
- [ ] No console errors in browser

---

## 🆘 If Still Not Working

### Check 1: Database Columns
```sql
SHOW COLUMNS FROM task_labor LIKE 'paid_by';
```
Should return 1 row. If not, column is missing.

### Check 2: Language File
```bash
cat /home/u858321845/domains/1stop.gr/public_html/languages/el.json | grep payments
```
Should show: `"payments": "Πληρωμές"`

### Check 3: PHP Errors
Check error log:
```bash
tail -50 /home/u858321845/domains/1stop.gr/public_html/error_log
```

---

## 📞 Support

If problems persist:
- **Email**: theodore.sfakianakis@gmail.com
- **Provide**: Error log, phpMyAdmin screenshot, browser console errors

---

**Created**: October 21, 2025  
**Priority**: 🔥 CRITICAL  
**Estimated Fix Time**: 5-10 minutes
