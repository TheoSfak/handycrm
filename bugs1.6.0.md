# HandyCRM v1.6.0 - Bugs & Issues Report

**Generated:** January 20, 2026  
**Analysis Date:** 2026-01-20 15:07:18  
**Files Scanned:** 141 PHP files  
**Total Issues Found:** 214

---

## 📊 Executive Summary

| Severity | Count | Description |
|----------|-------|-------------|
| 🔴 **CRITICAL** | **12** | Security vulnerabilities, deprecated functions, version mismatches |
| 🟡 **WARNING** | **117** | Empty error handling, debug files, missing configurations |
| 🔵 **NOTICE** | **85** | Code cleanup, TODO markers, naming conventions |
| **TOTAL** | **214** | |

---

## 🔴 CRITICAL Issues (12) - **URGENT**

### 1. Version Inconsistencies (2 issues)

#### Issue #1: config/config.php
- **Problem:** APP_VERSION is 1.5.0 but should be 1.6.0
- **Location:** `config/config.php`
- **Fix:** Update `define('APP_VERSION', '1.6.0');`
- **Impact:** Version mismatch between production and codebase

#### Issue #2: config/config.example.php
- **Problem:** APP_VERSION is 1.4.0 but should be 1.6.0
- **Location:** `config/config.example.php`
- **Fix:** Update `define('APP_VERSION', '1.6.0');`
- **Impact:** New installations will have wrong version

---

### 2. Deprecated PHP Functions (8 issues) - **WILL BREAK IN PHP 8+**

#### Issue #3-4: views/appointments/calendar.php
- **Problem:** Using deprecated `split()` function
- **Lines:** 361, 362
- **Fix:** Replace with `explode()` or `preg_split()`
- **Impact:** Code will fail in PHP 7.0+

#### Issue #5-6: views/maintenances/create.php
- **Problem:** Using deprecated `split()` function
- **Lines:** 135, 213
- **Fix:** Replace with `explode()` or `preg_split()`
- **Impact:** Code will fail in PHP 7.0+

#### Issue #7-8: views/maintenances/edit.php
- **Problem:** Using deprecated `split()` function
- **Lines:** 386, 415
- **Fix:** Replace with `explode()` or `preg_split()`
- **Impact:** Code will fail in PHP 7.0+

#### Issue #9-10: views/materials/index.php
- **Problem:** Using deprecated `split()` function
- **Lines:** 505, 509
- **Fix:** Replace with `explode()` or `preg_split()`
- **Impact:** Code will fail in PHP 7.0+

---

### 3. Security Issues (2 issues)

#### Issue #11: No .htaccess in uploads/
- **Problem:** No .htaccess protection in uploads directory
- **Location:** `uploads/`
- **Fix:** Create `.htaccess` with:
  ```apache
  <FilesMatch "\.php$">
      Deny from all
  </FilesMatch>
  ```
- **Impact:** 🔴 **HIGH RISK** - Attackers could execute PHP files uploaded to uploads directory
- **Severity:** CRITICAL SECURITY VULNERABILITY

#### Issue #12: Database Connection Failure (Development)
- **Problem:** Cannot connect to database
- **Error:** `SQLSTATE[HY000] [1045] Access denied for user 'handycrm_user'@'localhost'`
- **Location:** `config/config.php`
- **Impact:** Local development environment not configured
- **Note:** This is a local issue, production may be configured correctly

---

## 🟡 WARNING Issues (117) - **SHOULD FIX SOON**

### 1. Empty/Silent Exception Handlers (96 issues)

These catch blocks swallow errors without logging, making debugging difficult:

#### Classes (13 issues)
- `classes/AutoMigration.php`: Lines 157, 178, 211, 226
- `classes/Database.php`: Line 43
- `classes/EmailService_OLD.php`: Lines 307, 338
- `classes/MigrationManager.php`: Lines 78, 125, 155, 176, 251
- `classes/VersionManager.php`: Line 76 (debug output)

#### Controllers (69 issues)
- `controllers/AppointmentController.php`: Lines 168, 304, 348
- `controllers/AuthController_OLD.php`: Line 239
- `controllers/CustomerController.php`: Lines 155, 302, 345, 390, 707, 721
- `controllers/DailyTaskController.php`: Line 567
- `controllers/MaterialController.php`: Lines 72, 163, 218, 394, 445
- `controllers/MaterialsController.php`: Lines 554, 597
- `controllers/PaymentsController.php`: Lines 215, 248, 308, 341, 383, 418, 501
- `controllers/ProfileController.php`: Lines 71, 124
- `controllers/ProjectController.php`: Lines 134, 405, 552, 761, 1000, 1014
- `controllers/ProjectReportController.php`: Line 384
- `controllers/SettingsController.php`: Line 79
- `controllers/TransformerMaintenanceController.php`: Lines 1202, 1452, 1562
- `controllers/UpdateController.php`: Lines 43, 181, 249
- `controllers/UserController.php`: Lines 91, 218, 264, 287, 328

#### Views (14 issues)
- `views/appointments/calendar.php`: Line 371
- `views/daily-tasks/edit.php`: Line 443
- `views/daily-tasks/index.php`: Line 281
- `views/maintenances/edit.php`: Line 375
- `views/maintenances/index.php`: Line 348
- `views/maintenances/view.php`: Line 530
- `views/materials/duplicates.php`: Lines 196, 233, 293
- `views/materials/index.php`: Lines 574, 664, 694, 724, 755
- `views/payments/index.php`: Lines 693, 731, 772, 815, 913
- `views/payments/index_old.php`: Lines 302, 342
- `views/projects/show.php`: Lines 1026, 1079
- `views/update/index.php`: Line 256

**Recommendation:** Add proper error logging:
```php
catch (Exception $e) {
    error_log("Error in [context]: " . $e->getMessage());
    // Handle error appropriately
}
```

---

### 2. Hardcoded Old Version References (13 issues)

#### Issue #13-27: Outdated Version Numbers in Code
- `classes/UpdateChecker.php:13` - References v1.0.0
- `controllers/AuthController.php:353` - Footer shows v1.4.0
- `controllers/UpdateController.php:4, 20, 31, 42, 44, 71` - Multiple references to old versions
- `views/auth/login.php:241` - Shows v1.4.0
- `views/includes/footer.php:29` - Shows v1.4.0
- `migrations/migrate_to_1.1.0.php:3, 43, 196` - Historical references (acceptable)

**Fix:** Update all version references to `1.6.0` or use `APP_VERSION` constant

---

### 3. Security Configuration Issues (4 issues)

#### Issue #28: Git config exposed
- **Location:** `.git/config`
- **Fix:** Add to `.htaccess` or nginx config to block access

#### Issue #29: Composer.json accessible
- **Location:** `composer.json`
- **Fix:** Block public access via web server configuration

#### Issue #30: Config file may be accessible
- **Location:** `config/config.php`
- **Fix:** Ensure web server blocks direct PHP access

#### Issue #31: Missing HttpOnly flag
- **Location:** `config/config.php`
- **Fix:** Add `ini_set('session.cookie_httponly', 1);`
- **Impact:** XSS vulnerability protection

---

### 4. Debug Files in Production (11 issues)

These files should NOT be in production:

- `debug-routes.php`
- `debug_current_state.php`
- `debug_email.php`
- `debug_maintenance_photos.php`
- `debug_maintenances.php`
- `debug_maintenances_redirect.php`
- `debug_roles.php`
- `debug_session.php`
- `test_transformer_controller.php`
- `test_trash_system.php`
- `test_users_query.php`

**Fix:** Delete these files or move to a `/dev` directory NOT in production

---

### 5. Missing Language Files (1 issue)

#### Issue #43: No translation files
- **Location:** `languages/` directory
- **Problem:** Directory exists but contains no language files
- **Impact:** Internationalization not working
- **Fix:** Add at least `el.php` (Greek) and `en.php` (English)

---

### 6. Debug Output in Code (2 issues)

#### Issue #44-45: Debug logging
- `controllers/DailyTaskController.php:212-213` - Debug error_log statements
- `classes/VersionManager.php:76` - Debug print_r output

**Fix:** Remove or wrap in `if (DEBUG_MODE)` conditionals

---

## 🔵 NOTICE Issues (85) - **CLEANUP & OPTIMIZATION**

### 1. Code Markers (48 issues)

#### TODO Markers (1 issue)
- `classes/EmailService_OLD.php:443` - TODO: Implement PHPMailer

#### DEBUG/BUG Markers (47 issues)

**AuthController.php** - 18 debug markers (lines 47, 212, 214, 233, 236, 238, 241, 251, 253, 260, 262, 269, 271, 277, 284, 291, 298, 370, 376, 382, 391)

**DailyTaskController.php** - 3 debug markers (lines 738, 743-744, 864)

**PaymentReportController.php** - 3 debug markers (lines 53, 55, 76)

**PaymentsController.php** - 2 debug markers (lines 465-466)

**ProjectReportController.php** - 2 debug markers (lines 196-197)

**QuoteController.php** - 3 debug markers (lines 364, 379, 384)

**TransformerMaintenanceController.php** - 3 debug markers (lines 1022-1024)

**JavaScript Debug** - 5 markers in `views/maintenances/index.php` (lines 286, 287, 300, 307, 349)

**UI Bug Icon** - `views/settings/update.php:1179` - Bug report icon

---

### 2. Backup & Old Files (4 issues)

- `classes/EmailService_OLD.php` - Old email service implementation
- `controllers/AuthController_OLD.php` - Old auth controller
- `controllers/AuthController.php.backup` - Backup file
- `controllers/SettingsController.php.backup` - Backup file
- `config/config-WORKING.php.backup` - Backup config

**Fix:** Remove or archive these files outside the codebase

---

### 3. Abrupt Terminations (3 issues)

Using `die()` or `exit()` without proper error context:
- `controllers/ProjectReportController.php:92`
- `controllers/ProjectTasksController.php:623`
- `migrations/regenerate_material_aliases.php:63`

**Fix:** Replace with proper exception handling or error returns

---

### 4. Migration Naming Convention (25 issues)

These migration files don't follow the recommended naming pattern:

**Numbered Migrations:**
- `007_create_payments_table.sql`
- `008_add_paid_status_to_task_labor.sql`

**Descriptive Migrations (should be vX.X.X_* format):**
- `add_company_display_name.sql`
- `add_daily_task_materials.sql`
- `add_daily_task_technicians.sql`
- `add_email_notification_system.sql`
- `add_language_column.sql`
- `add_maintenance_pricing.sql`
- `add_maintenance_technician_role.sql`
- `add_materials_aliases.sql`
- `add_missing_permissions.sql`
- `add_password_reset_fields.sql`
- `add_project_tasks_system.sql`
- `add_task_photos.sql`
- `add_transformer_type_field.sql`
- `create_transformer_maintenances.sql`
- `ensure_materials_catalog.sql`
- `hotfix_1.3.5_paid_by.sql`
- `load_electrical_materials.sql`
- `load_electrical_materials_part2.sql`
- `remove_duplicate_maintenances.sql`
- `set_admin_only_system_role.sql`
- `update_transformer_maintenances_multiple.sql`
- `update_user_roles.sql`
- `verify_1.3.5_ready.sql`

**Recommended format:** `v1.X.X_feature_name.sql` or `migrate_1.5.0_to_1.6.0.sql`

---

### 5. Security Best Practices (1 issue)

#### Secure Cookie Flag Not Configured
- **Location:** `config/config.php`
- **Fix:** Add `ini_set('session.cookie_secure', 1);` for HTTPS-only cookies
- **Note:** Only enable if site runs on HTTPS

---

## 📋 Action Plan - Priority Order

### Phase 1: CRITICAL - Do Immediately ⚡

1. **Fix Deprecated split() Functions** (8 occurrences)
   - Replace all `split()` with `explode()`
   - Files: `views/appointments/calendar.php`, `views/maintenances/create.php`, `views/maintenances/edit.php`, `views/materials/index.php`

2. **Add .htaccess to uploads/** 
   - Prevent PHP execution in upload directory

3. **Update Version Numbers**
   - `config/config.php`: Update to 1.6.0
   - `config/config.example.php`: Update to 1.6.0

---

### Phase 2: HIGH PRIORITY - This Week 📅

4. **Remove Debug Files** (11 files)
   - Delete all `debug_*.php` and `test_*.php` from root

5. **Add Error Logging to Empty Catch Blocks** (96 occurrences)
   - Add `error_log()` statements to all silent catch blocks
   - Prioritize controllers and critical classes

6. **Security Hardening**
   - Set HttpOnly cookie flag
   - Block access to .git, composer.json, config files
   - Set Secure flag for HTTPS

---

### Phase 3: MEDIUM PRIORITY - This Month 📆

7. **Remove Debug Markers** (48 occurrences)
   - Clean up TODO, DEBUG, BUG comments
   - Remove or wrap debug `error_log()` statements

8. **Update Hardcoded Versions** (13 occurrences)
   - Replace hardcoded versions with APP_VERSION constant

9. **Clean Up Backup Files** (5 files)
   - Remove or archive _OLD and .backup files

---

### Phase 4: LOW PRIORITY - When Time Permits ⏰

10. **Standardize Migration Naming** (25 files)
    - Rename migration files to follow vX.X.X pattern

11. **Add Language Files**
    - Create `languages/el.php` and `languages/en.php`

12. **Improve Error Handling**
    - Replace `die()`/`exit()` with proper exceptions

---

## 🔧 Quick Fixes

### Fix #1: Replace split() with explode()

**Before:**
```php
$parts = split(',', $data);
```

**After:**
```php
$parts = explode(',', $data);
```

### Fix #2: Add .htaccess to uploads/

Create `uploads/.htaccess`:
```apache
# Prevent PHP execution
<FilesMatch "\.(php|php3|php4|php5|phtml)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Prevent directory listing
Options -Indexes
```

### Fix #3: Proper Error Handling

**Before:**
```php
try {
    // code
} catch (Exception $e) {
    // silent
}
```

**After:**
```php
try {
    // code
} catch (Exception $e) {
    error_log("Error in [function_name]: " . $e->getMessage());
    // Show user-friendly error or rethrow
}
```

### Fix #4: Update Version

In `config/config.php` and `config/config.example.php`:
```php
define('APP_VERSION', '1.6.0');
```

---

## 📈 Statistics by Category

| Category | Critical | Warning | Notice | Total |
|----------|----------|---------|--------|-------|
| Code Quality | 8 | 96 | 54 | 158 |
| Version | 2 | 13 | 0 | 15 |
| Security | 2 | 4 | 1 | 7 |
| Cleanup | 0 | 11 | 4 | 15 |
| Configuration | 0 | 1 | 1 | 2 |
| Migrations | 0 | 0 | 25 | 25 |
| Translations | 0 | 1 | 0 | 1 |
| **TOTAL** | **12** | **117** | **85** | **214** |

---

## 📝 Notes

- This report is based on automated static analysis
- Database-related issues could not be fully tested due to local connection error
- Production environment may have different configurations
- Some issues (like migration naming) are cosmetic and don't affect functionality
- All CRITICAL issues should be addressed before deploying to production

---

## ✅ Verification Checklist

After fixes, verify:

- [ ] All `split()` functions replaced with `explode()`
- [ ] `.htaccess` created in `uploads/` directory
- [ ] VERSION file shows 1.6.0
- [ ] config.php shows APP_VERSION 1.6.0
- [ ] config.example.php shows APP_VERSION 1.6.0
- [ ] Debug files removed from root
- [ ] HttpOnly cookie flag enabled
- [ ] Error logging added to critical catch blocks
- [ ] Backup files removed/archived
- [ ] Debug markers cleaned up

---

**Report Generated by:** HandyCRM System Analyzer v1.0.0  
**Full Details:** See `analysis-report-2026-01-20-150718.txt`
