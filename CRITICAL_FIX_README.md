# CRITICAL FIX - Language System Deployment Issue

## Problem Description

When deploying HandyCRM v1.0.3 to a fresh server, users encountered this error when accessing the Settings page:

```
Fatal error: Uncaught Error: Call to undefined function __() 
in /controllers/SettingsController.php:53
```

## Root Cause

The `config/config.example.php` file (used as a template by the installer) was missing the LanguageManager initialization code. When `install.php` created a new `config.php` from this template, it didn't include:

1. LanguageManager class initialization
2. The `__()` translation helper function
3. The `trans()` translation alias function
4. Language-related constants (DEFAULT_LANGUAGE, LANGUAGES_PATH)

This caused all pages using translation functions (like Settings) to crash with "undefined function" errors.

## Files Fixed

### 1. config/config.example.php
**Added:**
- Language system constants:
  ```php
  define('DEFAULT_LANGUAGE', 'el');
  define('LANGUAGES_PATH', APP_ROOT . '/languages/');
  ```
- LanguageManager initialization:
  ```php
  require_once APP_ROOT . '/classes/LanguageManager.php';
  $currentLang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
  $lang = new LanguageManager($currentLang);
  ```
- Translation helper functions:
  ```php
  function __($key, $default = null) {
      global $lang;
      return $lang->get($key, $default);
  }
  
  function trans($key, $default = null) {
      return __($key, $default);
  }
  ```
- Updated all constants to match production config.php
- Fixed APP_ROOT path definition
- Added SESSION_LIFETIME handling
- Added CURRENCY and VAT settings

### 2. install.php
**Added:**
- Random SECRET_KEY generation during installation:
  ```php
  $secretKey = bin2hex(random_bytes(32));
  $config = preg_replace("/define\('SECRET_KEY', '.*?'\);/", 
                         "define('SECRET_KEY', '$secretKey');", $config);
  ```

### 3. index.php
**Fixed:**
- Database connection errors now redirect to install.php instead of showing error messages:
  ```php
  } catch (Exception $e) {
      header('Location: install.php');
      exit;
  }
  ```

## Impact

### Before Fix:
- ✗ New installations crashed on Settings page
- ✗ Translation system completely non-functional
- ✗ User couldn't change language or access settings
- ✗ Had to manually edit config.php to add missing code

### After Fix:
- ✓ Clean installations work perfectly
- ✓ Translation system fully functional from start
- ✓ Settings page accessible immediately
- ✓ Language switching works out of the box
- ✓ No manual configuration needed

## Version Information

- **Fixed in Commit:** c527470
- **Date:** October 10, 2025
- **Branch:** main
- **Affected Versions:** v1.0.3 and earlier
- **Fixed Version:** v1.0.3+ (after this commit)

## Installation Instructions

### For New Installations:
1. Download the latest code from GitHub (after commit c527470)
2. Upload to your server
3. Visit your-domain.com/handycrm
4. Follow the installation wizard
5. ✅ Everything works!

### For Existing Broken Installations:
If you already installed HandyCRM and are getting the `__()` function error:

**Option 1: Re-run Installer (Recommended)**
1. Download the updated code from GitHub
2. Delete `config/config.php` (backup database credentials first!)
3. Visit your-domain.com/handycrm
4. Re-run the installation wizard

**Option 2: Manually Update config.php**
Add this code to your existing `config/config.php` file (after the session_start section):

```php
// Language Settings
define('DEFAULT_LANGUAGE', 'el');
define('LANGUAGES_PATH', APP_ROOT . '/languages/');

// Initialize Language Manager
require_once APP_ROOT . '/classes/LanguageManager.php';
$currentLang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
$lang = new LanguageManager($currentLang);

/**
 * Helper function for translations
 */
function __($key, $default = null) {
    global $lang;
    return $lang->get($key, $default);
}

/**
 * Alias for translation function
 */
function trans($key, $default = null) {
    return __($key, $default);
}
```

Also ensure you have:
```php
define('APP_ROOT', dirname(__FILE__, 2));
```

## Testing Checklist

After applying this fix, verify:

- [ ] Fresh installation completes successfully
- [ ] Can access Settings page without errors
- [ ] Can change language from Greek to English
- [ ] Language preference saves to database
- [ ] All translated pages show correct language
- [ ] Menu items switch languages correctly
- [ ] No "undefined function" errors in logs

## Technical Details

### Why This Happened:
1. During multi-language feature development, we updated the working `config/config.php`
2. We forgot to update `config/config.example.php` (the template file)
3. The installer uses `config.example.php` as a template
4. New installations got a config without language support

### Prevention:
- Always update both `config.php` AND `config.example.php` together
- Test fresh installations, not just existing development environment
- Use deployment package testing before releasing

## Related Issues

This fix also resolves:
- Database connection errors not redirecting to installer
- Missing UPLOAD_PATH constant
- Inconsistent session timeout handling
- Missing SECRET_KEY generation

## Support

If you encounter any issues after applying this fix:

1. Clear browser cache and cookies
2. Check that `languages/` folder exists with `en.json` and `el.json`
3. Verify `classes/LanguageManager.php` exists
4. Check PHP error logs for detailed error messages

For additional support:
- GitHub Issues: https://github.com/TheoSfak/handycrm/issues
- Email: theodore.sfakianakis@gmail.com

---

**Author:** Theodore Sfakianakis  
**Date:** October 10, 2025  
**Status:** RESOLVED ✓
