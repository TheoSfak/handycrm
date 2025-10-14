<?php
/**
 * Force Update Check - Clears all update caches
 */
session_start();

echo "<h2>Clearing Update Cache...</h2>";

// Clear all update-related session data
$keysCleared = 0;

$updateKeys = [
    'last_update_check',
    'update_available',
    'update_info',
    'cached_version',
    'last_notification_update_check',
    'cached_update_notification'
];

foreach ($updateKeys as $key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
        echo "✓ Cleared: {$key}<br>";
        $keysCleared++;
    }
}

echo "<br><strong>Cleared {$keysCleared} cache keys</strong><br><br>";

// Now check for updates
require_once __DIR__ . '/classes/UpdateChecker.php';
$checker = new UpdateChecker();

echo "Current Version: <strong>" . $checker->getCurrentVersion() . "</strong><br>";

// Force a fresh check
$hasUpdate = $checker->checkForUpdates();
$updateInfo = $checker->getUpdateInfo();

echo "<br>";
if ($hasUpdate && $updateInfo) {
    echo "<div style='padding:20px; background:#4CAF50; color:white; border-radius:5px;'>";
    echo "✅ <strong>UPDATE AVAILABLE!</strong><br><br>";
    echo "Latest Version: <strong>v{$updateInfo['version']}</strong><br>";
    echo "Published: " . date('Y-m-d H:i', strtotime($updateInfo['published_at'])) . "<br>";
    echo "<br><a href='/settings/update' style='color:white; font-weight:bold; text-decoration:underline;'>Go to Update Page →</a>";
    echo "</div>";
} else {
    echo "<div style='padding:20px; background:#2196F3; color:white; border-radius:5px;'>";
    echo "ℹ️ No updates available. You are running the latest version.";
    echo "</div>";
}

echo "<br><br><small>You can delete this file now.</small>";
?>
