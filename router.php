<?php
/**
 * Router script for PHP built-in server
 * This file enables clean URLs when using php -S localhost:8000
 * 
 * Usage: php -S localhost:8000 router.php
 */

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Remove trailing slash (except for root)
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

// If the file exists and is not a directory, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Serve the requested resource as-is
}

// Check if it's a static file in uploads or assets
if (preg_match('/^\/(uploads|assets)\//', $uri)) {
    if (file_exists(__DIR__ . $uri)) {
        return false; // Serve static files
    } else {
        http_response_code(404);
        echo "File not found";
        return true;
    }
}

// Set the route parameter for index.php
$_GET['route'] = $uri === '' || $uri === '/' ? '/' : $uri;

// Include index.php
require __DIR__ . '/index.php';
