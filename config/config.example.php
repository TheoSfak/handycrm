<?php
/**
 * HandyCRM Configuration File
 * 
 * @author Theodore Sfakianakis
 * @email theodore.sfakianakis@gmail.com
 * @copyright 2025 Theodore Sfakianakis. All rights reserved.
 * 
 * Copy this file to config.php and update with your settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'handycrm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_ROOT', __DIR__ . '/..');
define('APP_NAME', 'HandyCRM');
define('APP_VERSION', '1.1.0');
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

// Session Configuration
define('SESSION_LIFETIME', 7200); // 2 hours

// CSRF Token Configuration
define('CSRF_TOKEN_NAME', 'csrf_token');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Timezone
date_default_timezone_set('Europe/Athens');

// Debug Mode
define('DEBUG_MODE', false);

// GitHub Token (Optional - Increases API rate limit from 60 to 5000 requests/hour)
// Create token at: https://github.com/settings/tokens (needs 'public_repo' scope)
define('GITHUB_TOKEN', ''); // Leave empty if not needed

// Error Reporting (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Date and Time Format
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');

// Helper Functions

/**
 * Helper function to format dates in Greek format (DD/MM/YYYY)
 */
function formatDate($date, $includeTime = false) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    if ($timestamp === false) {
        return '-';
    }
    
    $format = $includeTime ? DATETIME_FORMAT : DATE_FORMAT;
    return date($format, $timestamp);
}

/**
 * Helper function to format currency
 */
function formatCurrency($amount) {
    // Ensure amount is a valid number
    $amount = is_numeric($amount) ? (float)$amount : 0.0;
    
    // Format with explicit decimal and thousands separators
    // Using comma for decimal, dot for thousands (European format)
    $formatted = number_format($amount, 2, ',', '.');
    
    return $formatted . ' ' . CURRENCY_SYMBOL;
}

// Define BASE_URL for clean URLs
if (!defined('BASE_URL')) {
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $scriptPath === '/' ? '' : $scriptPath);
}

// Currency Settings
if (!defined('CURRENCY')) define('CURRENCY', 'EUR');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '€');
if (!defined('DEFAULT_VAT_RATE')) define('DEFAULT_VAT_RATE', 24.00);

// Language Settings
if (!defined('DEFAULT_LANGUAGE')) define('DEFAULT_LANGUAGE', 'el'); // Default language (Greek)
if (!defined('LANGUAGES_PATH')) define('LANGUAGES_PATH', APP_ROOT . '/languages/');

// Installation marker
if (!defined('INSTALLATION_COMPLETED')) define('INSTALLATION_COMPLETED', true);

// Initialize Language Manager
require_once APP_ROOT . '/classes/LanguageManager.php';
$currentLang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
$lang = new LanguageManager($currentLang);

/**
 * Helper function for translations
 */
if (!function_exists('__')) {
    function __($key, $default = null) {
        global $lang;
        return $lang->get($key, $default);
    }
}

/**
 * Alias for translation function
 */
if (!function_exists('trans')) {
    function trans($key, $default = null) {
        return __($key, $default);
    }
}