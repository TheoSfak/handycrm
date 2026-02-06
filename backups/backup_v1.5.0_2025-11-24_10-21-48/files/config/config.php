<?php
/**
 * HandyCRM Configuration File
 * Generated automatically during installation
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u858321845_handycrm1');
define('DB_USER', 'u858321845_handy1');
define('DB_PASS', 'Tgyhuj123&*(');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'HandyCRM');
define('APP_VERSION', '1.5.0');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']));
define('APP_ROOT', dirname(__FILE__, 2));

// Security Configuration
define('SECRET_KEY', 'bc1f141c8057dd6e26486d4f4c0d9396a4aa79ff11e8fdb348505f1a4c229380');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('CSRF_TOKEN_NAME', 'csrf_token');

// File Upload Configuration
define('UPLOAD_PATH', APP_ROOT . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx');

// Pagination
define('ITEMS_PER_PAGE', 20);

// Date and Time
define('DEFAULT_TIMEZONE', 'Europe/Athens');
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');

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

// Currency Settings
define('CURRENCY', 'EUR');
define('CURRENCY_SYMBOL', '€');
define('DEFAULT_VAT_RATE', 24.00);

// Language Settings
if (!defined('DEFAULT_LANGUAGE')) define('DEFAULT_LANGUAGE', 'el'); // Default language (Greek)
if (!defined('LANGUAGES_PATH')) define('LANGUAGES_PATH', APP_ROOT . '/languages/');

// Development Settings
define('DEBUG_MODE', true); // Set to false in production
define('ERROR_REPORTING', true);

// Installation marker
if (!defined('INSTALLATION_COMPLETED')) define('INSTALLATION_COMPLETED', true);

// Set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Error reporting for development
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
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
    
     return $formatted . ' €';
}

/**
 * Helper function to format numbers (without currency symbol)
 * Prevents issues like "0,00 262145" by using explicit separators
 */
function formatNumber($number, $decimals = 2) {
    // Ensure number is valid
    $number = is_numeric($number) ? (float)$number : 0.0;
    
    // Format with European style: comma for decimals, dot for thousands
    return number_format($number, $decimals, ',', '.');
}


/**
 * Format currency with optional VAT note
 * @param float $amount The amount to format
 * @param bool|null $showNote Whether to show VAT note (null = read from settings)
 * @return string Formatted currency with VAT note
 */
function formatCurrencyWithVAT($amount, $showNote = null) {
    // Format the base amount
    $formatted = number_format($amount, 2, ',', '.') . ' €';
    
    // Determine if we should show the note
    if ($showNote === null) {
        // Read from database settings
        try {
            $database = new Database();
            $db = $database->connect();
            $stmt = $db->query("SELECT display_vat_notes, prices_include_vat FROM settings WHERE id = 1 LIMIT 1");
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($settings && $settings['display_vat_notes']) {
                $showNote = true;
                $includesVat = $settings['prices_include_vat'];
            } else {
                $showNote = false;
            }
        } catch (Exception $e) {
            // If settings table doesn't exist or error, don't show note
            $showNote = false;
        }
    }
    
    // Add VAT note if enabled
    if ($showNote) {
        $includesVat = $includesVat ?? false;
        $note = $includesVat ? '(με ΦΠΑ)' : '(χωρίς ΦΠΑ)';
        $formatted .= ' ' . $note;
    }
    
    return $formatted;
}

/**
 * Get label with VAT note appended
 * @param string $label The original label
 * @param bool|null $showNote Whether to show VAT note (null = read from settings)
 * @return string Label with VAT note
 */
function withoutVatLabel($label, $showNote = null) {
    // Determine if we should show the note
    if ($showNote === null) {
        // Read from database settings
        try {
            $database = new Database();
            $db = $database->connect();
            $stmt = $db->query("SELECT display_vat_notes, prices_include_vat FROM settings WHERE id = 1 LIMIT 1");
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($settings && $settings['display_vat_notes']) {
                $showNote = true;
                $includesVat = $settings['prices_include_vat'];
            } else {
                $showNote = false;
            }
        } catch (Exception $e) {
            // If settings table doesn't exist or error, don't show note
            $showNote = false;
        }
    }
    
    // Add VAT note if enabled
    if ($showNote) {
        $includesVat = $includesVat ?? false;
        $note = $includesVat ? '(με ΦΠΑ)' : '(χωρίς ΦΠΑ)';
        return $label . ' ' . $note;
    }
    
    return $label;
}

/**
 * Get VAT disclaimer note
 * @return string VAT disclaimer text
 */
function getVatNote() {
    try {
        $database = new Database();
        $db = $database->connect();
        $stmt = $db->query("SELECT prices_include_vat, default_vat_rate FROM settings WHERE id = 1 LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($settings) {
            $includesVat = $settings['prices_include_vat'];
            $vatRate = $settings['default_vat_rate'];
            
            if ($includesVat) {
                return "Όλες οι τιμές περιλαμβάνουν ΦΠΑ {$vatRate}%";
            } else {
                return "Όλες οι τιμές δεν περιλαμβάνουν ΦΠΑ {$vatRate}%";
            }
        }
    } catch (Exception $e) {
        // Return default note
        return "Όλες οι τιμές δεν περιλαμβάνουν ΦΠΑ 24%";
    }
    
    return "Όλες οι τιμές δεν περιλαμβάνουν ΦΠΑ 24%";
}





