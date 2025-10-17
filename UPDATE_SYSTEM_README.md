# 🔄 HandyCRM Automatic Update System

## Overview

The HandyCRM Automatic Update System allows administrators to check for and apply database updates directly from the application's Settings page. This eliminates the need for manual SQL execution and ensures smooth version upgrades.

---

## Features

- ✅ **Automatic Update Detection** - Checks if database needs updates
- ✅ **Version Tracking** - Compares application version with database version
- ✅ **One-Click Updates** - Apply all pending updates with a single button
- ✅ **Progress Tracking** - Real-time progress indicators during updates
- ✅ **Transaction Safety** - All updates run in database transactions
- ✅ **Error Handling** - Graceful error handling with rollback on failure
- ✅ **Migration History** - Tracks which migrations have been applied

---

## How It Works

### 1. Version Detection
- Application version is read from `/VERSION` file
- Database version is read from `migrations` table
- System compares versions to determine if updates are needed

### 2. Update Definitions
Updates are defined in `UpdateController::getRequiredUpdates()`:

```php
$availableUpdates = [
    '1.2.5' => [
        'name' => 'Materials Catalog Enhancement',
        'description' => '100 electrical materials, CSV operations, pagination',
        'migrations' => [
            'load_electrical_materials.sql',
            'load_electrical_materials_part2.sql'
        ],
        'scripts' => [
            'regenerate_material_aliases.php'
        ]
    ]
];
```

### 3. Update Application
When admin clicks "Apply Updates":
1. System runs all SQL migrations
2. Executes PHP scripts
3. Records migration in database
4. Shows success/error messages

---

## User Guide

### Accessing the Update System

1. Log in as **Admin**
2. Go to **Settings** page
3. Look for **"Application Updates"** card
4. Click **"Check for Updates"**

### Applying Updates

1. On the Update page, you'll see:
   - Current application version
   - Database version
   - List of pending updates
   
2. Review the updates to be applied
3. Click **"Apply All Updates"** button
4. Wait for progress modal to complete
5. Page will reload when done

### Update Status Indicators

- **🟢 Up to Date** - No updates needed
- **🟡 Updates Available** - Updates pending
- **🔵 In Progress** - Updates being applied
- **✅ Success** - Updates completed
- **❌ Error** - Update failed (check error message)

---

## Developer Guide

### Adding a New Update

To add support for a new version (e.g., v1.3.0):

1. **Update VERSION file:**
   ```
   1.3.0
   ```

2. **Create migration files** in `/migrations/`:
   - `new_feature.sql`
   - `another_feature.sql`

3. **Add to UpdateController.php:**
   ```php
   $availableUpdates = [
       '1.3.0' => [
           'name' => 'New Feature Release',
           'description' => 'Description of what this update does',
           'migrations' => [
               'new_feature.sql',
               'another_feature.sql'
           ],
           'scripts' => [
               'post_update_script.php'  // optional
           ]
       ]
   ];
   ```

4. **Test the update:**
   - Set database version back to previous version
   - Access /update page
   - Verify update is listed
   - Apply and verify success

###Migration File Format

**SQL Migrations** (`migrations/*.sql`):
```sql
-- Description of what this migration does

-- Create table (if not exists)
CREATE TABLE IF NOT EXISTS new_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add column (safe to run multiple times)
ALTER TABLE existing_table 
ADD COLUMN IF NOT EXISTS new_column VARCHAR(100);

-- Insert data
INSERT INTO table_name (field1, field2)
VALUES ('value1', 'value2');
```

**PHP Scripts** (`migrations/*.php`):
```php
<?php
/**
 * Post-migration script
 * Runs after SQL migrations complete
 */

require_once __DIR__ . '/../config/database.php';

// Your logic here
echo "Processing data...\n";

// Example: Update existing records
$db = new Database();
$items = $db->fetchAll("SELECT * FROM table WHERE condition");

foreach ($items as $item) {
    // Process each item
    $db->execute("UPDATE table SET field = ? WHERE id = ?", [$newValue, $item['id']]);
}

echo "Complete!\n";
```

---

## Routes

The update system adds these routes:

| Route | Method | Description |
|-------|--------|-------------|
| `/update` | GET | Show update page |
| `/update/status` | GET | Get update status (JSON) |
| `/update/process` | POST | Apply all updates (JSON) |

---

## Database Schema

### migrations Table

```sql
CREATE TABLE migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    version VARCHAR(20),
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

This table tracks which migrations have been executed.

---

## Security

- ✅ **Admin Only** - Only users with role='admin' can access
- ✅ **Session Check** - Requires active login session
- ✅ **Transaction Safety** - All updates run in transactions
- ✅ **Error Rollback** - Failed updates don't leave partial changes
- ✅ **No Remote Execution** - Updates are local files only

---

## API Response Format

### GET /update/status

```json
{
    "current_version": "1.2.5",
    "database_version": "1.2.0",
    "update_available": true,
    "updates_needed": [
        {
            "version": "1.2.5",
            "name": "Materials Catalog Enhancement",
            "description": "100 electrical materials, CSV operations",
            "migrations": ["load_electrical_materials.sql"],
            "scripts": ["regenerate_material_aliases.php"]
        }
    ]
}
```

### POST /update/process

```json
{
    "success": true,
    "message": "Εφαρμόστηκαν 1 ενημερώσεις",
    "updates_applied": 1,
    "updates_failed": 0,
    "details": [
        {
            "success": true,
            "version": "1.2.5",
            "name": "Materials Catalog Enhancement",
            "message": "Επιτυχής ενημέρωση σε έκδοση 1.2.5"
        }
    ]
}
```

---

## Troubleshooting

### Update Fails

1. **Check Error Message** - The system provides specific error details
2. **Check File Paths** - Ensure migration files exist in `/migrations/`
3. **Check Permissions** - Ensure files are readable
4. **Check Database** - Verify database connection works
5. **Check Logs** - Look for PHP errors in error logs

### Version Mismatch

If version shows incorrectly:
1. Check `/VERSION` file exists and contains correct version
2. Check `migrations` table has correct records
3. Manually update if needed:
   ```sql
   UPDATE migrations SET version = '1.2.5' WHERE migration = 'latest_migration';
   ```

### Rollback

If an update needs to be rolled back:
1. Restore database from backup
2. Revert VERSION file to previous version
3. Remove migration record:
   ```sql
   DELETE FROM migrations WHERE version = '1.2.5';
   ```

---

## Best Practices

1. **Always Backup** - Create database backup before updating
2. **Test First** - Test updates on development environment
3. **Read Release Notes** - Review what each update does
4. **Monitor Progress** - Watch the progress indicators
5. **Check Results** - Verify application works after update

---

## Future Enhancements

Potential improvements for future versions:

- 🔄 **Automatic Backups** - Create backup before each update
- 📦 **Update Packages** - Download updates from remote server
- 🔔 **Update Notifications** - Email admins when updates available
- ⏮️ **Rollback Feature** - One-click rollback to previous version
- 📊 **Update History** - View log of all applied updates
- 🧪 **Dry Run Mode** - Test updates without applying them

---

## Files

- `controllers/UpdateController.php` - Main update logic
- `views/update/index.php` - Update UI
- `migrations/*.sql` - SQL migration files
- `migrations/*.php` - PHP post-migration scripts
- `VERSION` - Current application version

---

## Support

For questions or issues with the update system:
- Email: theodore.sfakianakis@gmail.com
- GitHub: https://github.com/TheoSfak/handycrm/issues

---

**Version:** 1.2.5  
**Last Updated:** October 17, 2025  
**Author:** Theodore Sfakianakis
