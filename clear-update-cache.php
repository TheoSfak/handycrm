<?php
/**
 * Clear update check cache
 * This will force the system to re-check for updates
 */

session_start();

echo "=== Clearing Update Cache ===\n\n";

// Show what's cached
echo "Current session data:\n";
echo "- last_update_check: " . ($_SESSION['last_update_check'] ?? 'not set') . "\n";
echo "- update_available: " . (isset($_SESSION['update_available']) ? ($_SESSION['update_available'] ? 'TRUE' : 'FALSE') : 'not set') . "\n";
if (isset($_SESSION['update_info'])) {
    echo "- update_info version: " . ($_SESSION['update_info']['version'] ?? 'not set') . "\n";
}

// Clear update-related session variables
unset($_SESSION['last_update_check']);
unset($_SESSION['update_available']);
unset($_SESSION['update_info']);

echo "\n✓ Update cache cleared!\n\n";

// Now verify current version
require_once 'c:/xampp/htdocs/handycrm/config/config.php';
require_once 'c:/xampp/htdocs/handycrm/classes/UpdateChecker.php';

$checker = new UpdateChecker();
echo "Current version: " . $checker->getCurrentVersion() . "\n";

// Force a fresh check
echo "\nForcing fresh update check from GitHub...\n";
$updateAvailable = $checker->checkForUpdates();

if ($updateAvailable) {
    $info = $checker->getUpdateInfo();
    echo "⚠ Update available: " . $info['version'] . "\n";
} else {
    echo "✓ You have the latest version!\n";
}

echo "\n=== Done ===\n";
echo "Now refresh your browser to see the correct status.\n";
?>
