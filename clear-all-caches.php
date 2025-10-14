<?php
/**
 * Force clear all PHP caches and restart session
 */

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache cleared\n";
} else {
    echo "- OPcache not available\n";
}

// Clear realpath cache
clearstatcache(true);
echo "✓ Stat cache cleared\n";

// Clear session
session_start();
session_unset();
session_destroy();
echo "✓ Session cleared\n";

// Check version after clearing
require_once 'c:/xampp/htdocs/handycrm/config/config.php';
echo "\nAPP_VERSION after cache clear: " . APP_VERSION . "\n";

require_once 'c:/xampp/htdocs/handycrm/classes/UpdateChecker.php';
$checker = new UpdateChecker();
echo "UpdateChecker version: " . $checker->getCurrentVersion() . "\n";

echo "\n✓ All caches cleared. Now restart Apache in XAMPP and refresh your browser.\n";
?>
