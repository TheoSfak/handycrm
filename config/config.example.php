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
define('BASE_PATH', '/'); // Change to '/subdirectory/' if in subfolder
define('APP_ROOT', __DIR__ . '/../'); // Application root directory

// Security Configuration
define('FORCE_HTTPS', false); // Set to true only if you have SSL certificate
define('SESSION_TIMEOUT', 7200); // 2 hours in seconds
define('CSRF_TOKEN_NAME', 'csrf_token');

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB in bytes
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Email Configuration (for notifications)
define('MAIL_FROM', 'noreply@yourdomain.com');
define('MAIL_FROM_NAME', 'HandyCRM System');

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

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', FORCE_HTTPS ? 1 : 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper Functions
function formatCurrency($amount) {
    return number_format($amount, 2) . '€';
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Auto-redirect to HTTPS in production
if (FORCE_HTTPS && !isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTP_HOST'])) {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit();
}

// Define BASE_URL for clean URLs
if (!defined('BASE_URL')) {
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $scriptPath === '/' ? '' : $scriptPath);
}