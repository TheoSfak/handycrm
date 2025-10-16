# HandyCRM v1.2.0 - Update Testing Guide

## Testing Auto-Migration System

### Scenario 1: Fresh Installation (New Users)

1. **Setup Clean Database:**
   ```sql
   DROP DATABASE IF EXISTS handycrm_test;
   CREATE DATABASE handycrm_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Install HandyCRM:**
   - Extract v1.2.0 ZIP to web directory
   - Visit `http://localhost/handycrm/install.php`
   - Enter database credentials
   - Click "Install"

3. **Expected Results:**
   - ✅ Base schema installed from `database/handycrm.sql`
   - ✅ All migrations in `/migrations/*.sql` executed automatically
   - ✅ `migrations` table created with records
   - ✅ Success message shows migration count

4. **Verify:**
   ```sql
   USE handycrm_test;
   
   -- Check migrations table exists
   SHOW TABLES LIKE 'migrations';
   
   -- Check all migrations executed
   SELECT * FROM migrations ORDER BY executed_at;
   
   -- Verify new features exist
   SHOW TABLES LIKE 'materials_catalog';
   SHOW TABLES LIKE 'project_photos';
   DESC materials_catalog;  -- Should have 'aliases' column
   ```

---

### Scenario 2: Upgrade from v1.1.0 (Existing Users)

1. **Backup Current Database:**
   ```bash
   mysqldump -u root handycrm > backup_v1.1.0.sql
   ```

2. **Replace Files:**
   - Download v1.2.0 ZIP
   - Extract over existing installation
   - **IMPORTANT:** Keep your `config/config.php` file!

3. **First Page Load:**
   - Visit `http://localhost/handycrm/`
   - Login with your credentials

4. **Expected Behavior:**
   - ✅ Application loads normally (no errors)
   - ✅ Migrations run automatically in background
   - ✅ No user action required!
   - ✅ Check error log: "HandyCRM: Auto-executed X pending migrations"

5. **Verify Migrations:**
   - Go to **Settings → Database Migrations**
   - Should show:
     - ✅ "All migrations are up to date!"
     - ✅ List of executed migrations with timestamps
     - ✅ Green "Executed Migrations" section

6. **Test New Features:**

   **A. Materials Catalog:**
   - Go to **Materials → Catalog**
   - Add material: "Καλώδιο NYM 3x1.5"
   - Check aliases auto-generated: "kalodio, kalwdio, cable, wire, NYM, nym, 3x1.5"

   **B. Greeklish Search:**
   - Go to **Projects → Tasks → New Task**
   - Add Material row
   - Type "kalodio" → Should find "Καλώδιο"
   - Type "nym" → Should find "Καλώδιο NYM 3x1.5"
   - Type "cable" → Should find "Καλώδιο"

   **C. Project Photos:**
   - Open any project
   - Go to **Photos** tab
   - Upload images (drag & drop or click)
   - Click photo → Should open in lightbox

7. **Verify Database Changes:**
   ```sql
   -- Check new tables exist
   SHOW TABLES LIKE 'materials_catalog';
   SHOW TABLES LIKE 'material_categories';
   SHOW TABLES LIKE 'project_photos';
   
   -- Check aliases column added
   DESC materials_catalog;
   
   -- Check FULLTEXT index
   SHOW INDEX FROM materials_catalog WHERE Key_name = 'idx_name_aliases';
   
   -- Check migrations tracked
   SELECT * FROM migrations ORDER BY executed_at DESC;
   ```

---

### Scenario 3: Partial Migration (Simulated Failure)

This tests resilience when migrations were partially applied.

1. **Setup:**
   - Start with v1.1.0 database
   - Manually run ONLY `add_language_column.sql`
   - Upgrade to v1.2.0

2. **Expected Behavior:**
   - ✅ System detects already-executed migration
   - ✅ Skips duplicate migration
   - ✅ Runs only pending migrations
   - ✅ No errors about existing columns/tables

3. **Verify:**
   ```sql
   SELECT * FROM migrations;
   -- Should show all migrations, including manually executed one
   ```

---

### Scenario 4: Rollback Test

**⚠️ IMPORTANT:** Always backup before updates!

1. **If Something Goes Wrong:**
   ```sql
   -- Restore backup
   mysql -u root handycrm < backup_v1.1.0.sql
   ```

2. **Restore Files:**
   - Delete v1.2.0 files
   - Restore v1.1.0 from backup
   - Application will work with v1.1.0 schema

---

## Migration Files Overview

Current migrations in `/migrations/`:

1. **add_language_column.sql**
   - Adds `language` column to users table
   - From: v1.0.x → v1.1.0

2. **add_project_tasks_system.sql**
   - Creates `project_tasks`, `task_materials`, `task_labor` tables
   - From: v1.0.x → v1.1.0

3. **add_task_photos.sql**
   - Creates `project_photos` table
   - From: v1.1.0 → v1.2.0

4. **add_materials_aliases.sql**
   - Adds `aliases` column to `materials_catalog`
   - Adds FULLTEXT index on (name, aliases)
   - From: v1.1.0 → v1.2.0

---

## How Auto-Migration Works

### On Fresh Install (`install.php`):
```
1. Create database
2. Import base schema (database/handycrm.sql)
3. Scan /migrations/*.sql files
4. Execute each migration in alphabetical order
5. Ignore "already exists" errors
6. Show success with count
```

### On Update (every `index.php` load):
```
1. Check if migrations table exists → Create if not
2. Get list of executed migrations from database
3. Scan /migrations/*.sql for new files
4. Compare and find pending migrations
5. Execute pending migrations automatically
6. Log results to error_log
7. Mark as executed in migrations table
8. Continue application load normally
```

### Error Handling:
- **Table already exists** → Skip silently
- **Column already exists** → Skip silently  
- **Duplicate key** → Skip silently
- **Critical errors** → Log to error_log, continue app
- **Never blocks** user from using the application

---

## Admin Tools

### View Migration Status:
1. Go to **Settings → Database Migrations**
2. See:
   - Pending migrations (with Run button)
   - Executed migrations (with timestamps)
   - How it works explanation

### Force Run Migrations:
```
Settings → Database Migrations → "Run Pending Migrations Now"
```

### Regenerate Material Aliases:
```
Visit: /materials/regenerate-aliases
```
This regenerates aliases for all existing materials.

---

## Common Issues & Solutions

### Issue: "Migrations not running"
**Solution:**
1. Check PHP error log
2. Verify `/migrations/` folder exists and contains .sql files
3. Check database permissions (user needs CREATE, ALTER rights)
4. Manually visit `/settings/migrations` and click "Run Pending Migrations"

### Issue: "Migration failed: Duplicate column"
**Solution:**
- This is **normal** if column already exists
- Migration system skips and continues
- Check migrations table: `SELECT * FROM migrations`

### Issue: "Can't see new features"
**Solution:**
1. Clear browser cache (Ctrl+F5)
2. Verify migrations executed: `/settings/migrations`
3. Check if table exists: `SHOW TABLES LIKE 'materials_catalog'`

### Issue: "Upgrade broke my site"
**Solution:**
1. Restore database backup
2. Restore v1.1.0 files
3. Report issue on GitHub with error log

---

## Performance Notes

- **Migrations run on EVERY page load** but only if pending
- **First load after update:** ~2-5 seconds (depends on migrations)
- **Subsequent loads:** <0.1 seconds (no pending migrations)
- **Database query:** Single SELECT to check pending migrations
- **No impact** on users after initial migration

---

## For Developers

### Creating New Migrations:

1. **Create SQL file in `/migrations/`:**
   ```sql
   -- migrations/add_new_feature_v1.3.0.sql
   -- Description: Adds XYZ feature
   
   ALTER TABLE projects ADD COLUMN new_field VARCHAR(255);
   
   CREATE TABLE IF NOT EXISTS new_table (
       id INT PRIMARY KEY AUTO_INCREMENT,
       name VARCHAR(255)
   );
   ```

2. **Test locally:**
   - Delete `migrations` table
   - Reload page
   - Check all migrations execute

3. **Commit to repo:**
   ```bash
   git add migrations/add_new_feature_v1.3.0.sql
   git commit -m "Add migration for XYZ feature"
   ```

4. **Users upgrade:**
   - Download new version
   - Extract files
   - Migration runs automatically!

### Best Practices:

- ✅ Use `CREATE TABLE IF NOT EXISTS`
- ✅ Use `ALTER TABLE` with `ADD COLUMN IF NOT EXISTS` (MySQL 8.0+) or handle errors
- ✅ Name files descriptively: `add_feature_name.sql`
- ✅ Add comment with description at top of SQL
- ✅ Test on fresh database AND upgrade scenario
- ✅ Keep migrations small and focused
- ❌ Never edit executed migrations
- ❌ Never delete executed migration files

---

## Version Compatibility

| From Version | To Version | Migration Required | Auto? |
|--------------|------------|-------------------|-------|
| v1.0.x       | v1.1.0     | Yes               | ✅ Yes |
| v1.1.0       | v1.2.0     | Yes               | ✅ Yes |
| v1.2.0       | Future     | Yes               | ✅ Yes |

All migrations are **backward compatible** and **non-destructive**.

---

## Success Checklist

After upgrading to v1.2.0, verify:

- [ ] Application loads without errors
- [ ] Login works
- [ ] Materials Catalog accessible at `/materials`
- [ ] Greeklish search works ("kalodio" finds "Καλώδιο")
- [ ] Project photos upload works
- [ ] Settings → Migrations shows "All up to date"
- [ ] No errors in PHP error log (except skipped duplicates)
- [ ] All existing data intact (projects, customers, etc.)

---

## Get Help

- **GitHub Issues:** https://github.com/TheoSfak/handycrm/issues
- **Email:** theodore.sfakianakis@gmail.com
- **Check error log:** `/path/to/php/error.log`

---

**Last Updated:** 2025-10-16  
**Version:** 1.2.0
