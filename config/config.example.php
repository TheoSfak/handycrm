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
define('DB_NAME', 'handycrm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'HandyCRM');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://yourdomain.com'); // Update for production
define('APP_ROOT', dirname(__FILE__, 2));

// Security Configuration
define('SECRET_KEY', 'REPLACE_WITH_RANDOM_STRING_ON_INSTALL');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('CSRF_TOKEN_NAME', 'csrf_token');

// File Upload Configuration
define('UPLOAD_PATH', APP_ROOT . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Email Configuration (Configure later if needed)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', ''); // Set your email
define('SMTP_PASS', ''); // Set your app password
define('SMTP_FROM_EMAIL', 'noreply@handycrm.local');
define('SMTP_FROM_NAME', 'HandyCRM System');

// SMS Configuration (Configure later if needed)
define('SMS_PROVIDER', 'none'); // none, cosmote, vodafone
define('SMS_API_KEY', '');
define('SMS_API_SECRET', '');

// Timezone
define('DEFAULT_TIMEZONE', 'Europe/Athens');
date_default_timezone_set(DEFAULT_TIMEZONE);

// Debug Mode (DISABLE in production)
define('DEBUG_MODE', true); // Set to true for debugging

// Error Reporting (for production)
if (!DEBUG_MODE) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    
    // Set session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}

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
    $amount = $amount ?? 0; // Convert null to 0
    return number_format((float)$amount, 2, ',', '.') . ' ' . CURRENCY_SYMBOL;
}

// Define BASE_URL for clean URLs
if (!defined('BASE_URL')) {
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $scriptPath === '/' ? '' : $scriptPath);
}

// Currency Settings
define('CURRENCY', 'EUR');
define('CURRENCY_SYMBOL', '€');
define('DEFAULT_VAT_RATE', 24.00);

// Language Settings
define('DEFAULT_LANGUAGE', 'el'); // Default language (Greek)
define('LANGUAGES_PATH', APP_ROOT . '/languages/');

// Installation marker
define('INSTALLATION_COMPLETED', true);

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