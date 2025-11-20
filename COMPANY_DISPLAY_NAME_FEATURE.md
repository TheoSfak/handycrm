# 🏢 Company Display Name Feature - HandyCRM

## Περιγραφή

Νέο προαιρετικό πεδίο στις ρυθμίσεις που επιτρέπει την αντικατάσταση του "HandyCRM" με custom όνομα εταιρίας σε όλο το σύστημα.

## Αρχεία που Δημιουργήθηκαν

### 1. `helpers/app_display_name.php`
Νέο helper function που επιστρέφει το custom όνομα ή "HandyCRM" αν είναι κενό.

```php
function getAppDisplayName() {
    // Επιστρέφει company_display_name από settings
    // Αν είναι κενό, επιστρέφει 'HandyCRM'
}
```

### 2. `migrations/add_company_display_name.sql`
Migration για προσθήκη του νέου πεδίου στη βάση.

```sql
INSERT INTO settings (setting_key, setting_value, setting_type, description)
VALUES ('company_display_name', '', 'string', 'Διακριτικός Τίτλος Εταιρίας...')
ON DUPLICATE KEY UPDATE description = '...';
```

## Αρχεία που Τροποποιήθηκαν

### 1. `controllers/SettingsController.php`
- Προστέθηκε `'company_display_name' => ''` στο defaults array
- Προστέθηκε `'company_display_name'` στο allowedSettings array

### 2. `views/settings/index.php`
- Προστέθηκε input field για το Διακριτικός Τίτλος Εταιρίας
- Με label, help text και placeholder στα Ελληνικά

### 3. `views/auth/login.php`
- `<title>` - Χρησιμοποιεί `getAppDisplayName()`
- `<h2>` header - Εμφανίζει το custom όνομα
- Version info - Χρησιμοποιεί το custom όνομα

### 4. `views/includes/header.php`
- `<title>` - Χρησιμοποιεί `getAppDisplayName()`
- Sidebar logo - Εμφανίζει το custom όνομα

### 5. `views/includes/footer.php`
- Footer branding - Χρησιμοποιεί `getAppDisplayName()`

### 6. `controllers/AuthController.php`
- Email Subject - Χρησιμοποιεί `getAppDisplayName()`
- Email Header - Εμφανίζει το custom όνομα
- Email Footer - Εμφανίζει το custom όνομα

### 7. `views/auth/forgot-password.php`
- `<title>` - Χρησιμοποιεί `getAppDisplayName()`

### 8. `views/auth/reset-password.php`
- `<title>` - Χρησιμοποιεί `getAppDisplayName()`

### 9. `views/errors/404.php`
- `<title>` - Χρησιμοποιεί `getAppDisplayName()`

## Οδηγίες Εγκατάστασης

### Βήμα 1: Upload Files
Upload τα ακόλουθα αρχεία στον server:

```
helpers/app_display_name.php (ΝΕΟ)
migrations/add_company_display_name.sql (ΝΕΟ)
controllers/SettingsController.php (UPDATED)
controllers/AuthController.php (UPDATED)
views/settings/index.php (UPDATED)
views/auth/login.php (UPDATED)
views/auth/forgot-password.php (UPDATED)
views/auth/reset-password.php (UPDATED)
views/includes/header.php (UPDATED)
views/includes/footer.php (UPDATED)
views/errors/404.php (UPDATED)
```

### Βήμα 2: Run Migration
Εκτέλεση του migration μέσω phpMyAdmin ή command line:

```bash
mysql -u u858321845_handycrm -p u858321845_handycrm < migrations/add_company_display_name.sql
```

Ή copy-paste το περιεχόμενο στο phpMyAdmin.

### Βήμα 3: Ρύθμιση
1. Login ως admin
2. Πήγαινε στο Settings → Στοιχεία Εταιρίας
3. Συμπλήρωσε το πεδίο "Διακριτικός Τίτλος Εταιρίας"
4. Πάτα "Αποθήκευση Αλλαγών"

## Πού Εμφανίζεται το Custom Όνομα

✅ **Login Page**
- Page title
- Header (κύριος τίτλος)
- Footer version info

✅ **Dashboard & All Pages**
- Page titles (browser tab)
- Sidebar logo/header
- Footer branding

✅ **Email Templates**
- Email subject line
- Email header
- Email footer

✅ **Password Reset**
- Forgot password page title
- Reset password page title
- Password reset email

✅ **Error Pages**
- 404 page title

## Συμπεριφορά

### Αν το Πεδίο Είναι Κενό
- Εμφανίζεται "HandyCRM" (default)

### Αν το Πεδίο Έχει Τιμή
- Εμφανίζεται η custom τιμή παντού
- Παράδειγμα: "ECOWATT CRM"

## Technical Details

### Helper Function
```php
function getAppDisplayName() {
    static $displayName = null;
    
    if ($displayName === null) {
        // Fetch from database (with caching)
        // Return custom name or 'HandyCRM'
    }
    
    return $displayName;
}
```

### Usage Pattern
```php
<?php 
require_once __DIR__ . '/../../helpers/app_display_name.php';
$appName = getAppDisplayName();
?>
<title><?= $appName ?></title>
```

### Database Schema
```sql
setting_key: 'company_display_name'
setting_value: '' (empty by default)
setting_type: 'string'
description: 'Διακριτικός Τίτλος Εταιρίας...'
```

## Testing

### Test Case 1: Default Behavior
1. Μην συμπληρώσεις το πεδίο (άδειο)
2. Logout
3. Verify: Εμφανίζεται "HandyCRM" παντού

### Test Case 2: Custom Name
1. Settings → Συμπλήρωσε "ECOWATT CRM"
2. Save
3. Logout
4. Verify: Εμφανίζεται "ECOWATT CRM" στη σελίδα login
5. Login
6. Verify: Εμφανίζεται "ECOWATT CRM" στο sidebar/footer
7. Test forgot password
8. Verify: Email έχει "ECOWATT CRM" στο subject/header/footer

## Files Summary

**New Files:** 2
- helpers/app_display_name.php
- migrations/add_company_display_name.sql

**Modified Files:** 9
- controllers/SettingsController.php
- controllers/AuthController.php
- views/settings/index.php
- views/auth/login.php
- views/auth/forgot-password.php
- views/auth/reset-password.php
- views/includes/header.php
- views/includes/footer.php
- views/errors/404.php

**Total Changes:** 11 files

## Notes

- Το helper function κάνει cache το αποτέλεσμα (static variable) για performance
- Fallback σε "HandyCRM" αν υπάρχει database error
- Όλα τα strings είναι properly escaped με htmlspecialchars()
- Backward compatible - δεν σπάει τίποτα αν δεν οριστεί custom όνομα

---

**HandyCRM v1.4.0** - Company Display Name Feature
© 2024 ECOWATT Ενεργειακές Λύσεις
