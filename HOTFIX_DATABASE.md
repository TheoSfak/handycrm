# 🔧 HOTFIX - Database Connection Fix

## ❌ Πρόβλημα
```
Fatal error: Call to undefined method Database::getInstance()
in AuthController.php:218
```

## ✅ Λύση
Η Database class δεν έχει `getInstance()` static method.  
Πρέπει να χρησιμοποιούμε `new Database()` και μετά `connect()`.

## 🔄 Αλλαγές

### ΠΡΙΝ (ΛΑΘΟΣ):
```php
$db = Database::getInstance()->getConnection();
```

### ΜΕΤΑ (ΣΩΣΤΟ):
```php
$database = new Database();
$db = $database->connect();
```

## 📝 Διορθώθηκαν 3 Methods:

### 1. `processForgotPassword()` - Line ~218
```php
// Check if email exists
$database = new Database();
$db = $database->connect();
$stmt = $db->prepare("SELECT id, first_name, email FROM users WHERE email = ? AND is_active = 1");
```

### 2. `resetPassword()` - Line ~274
```php
// Verify token exists and not expired
$database = new Database();
$db = $database->connect();
$stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
```

### 3. `processResetPassword()` - Line ~323
```php
// Verify token still valid
$database = new Database();
$db = $database->connect();
$stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
```

## 🚀 Deployment

Ανέβασε ξανά το **`controllers/AuthController.php`** στο production.

## ✅ Fixed!

Το error έχει διορθωθεί και το password reset θα δουλεύει τώρα σωστά!

---

**Date:** 7 Νοεμβρίου 2025  
**File:** controllers/AuthController.php  
**Lines Changed:** 218, 274, 323  
**Status:** ✅ FIXED & TESTED
