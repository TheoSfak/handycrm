<?php
/**
 * Debug Update Check
 */
session_start();

echo "<h2>Update Check Debug</h2>";
echo "<pre>";

// Clear cache first
unset($_SESSION['last_update_check']);
unset($_SESSION['update_available']);
unset($_SESSION['update_info']);
unset($_SESSION['cached_version']);
unset($_SESSION['last_notification_update_check']);
unset($_SESSION['cached_update_notification']);

echo "1. Cache cleared\n\n";

// Load config
require_once __DIR__ . '/config/config.php';
echo "2. Current APP_VERSION: " . (defined('APP_VERSION') ? APP_VERSION : 'NOT DEFINED') . "\n\n";

// Check UpdateChecker
require_once __DIR__ . '/classes/UpdateChecker.php';
$checker = new UpdateChecker();

echo "3. UpdateChecker current version: " . $checker->getCurrentVersion() . "\n\n";

// Make API call directly
echo "4. Making direct GitHub API call...\n";
$url = "https://api.github.com/repos/TheoSfak/handycrm/releases/latest";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: HandyCRM-Update-Checker',
            'Accept: application/json'
        ],
        'timeout' => 10
    ]
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "   ERROR: Failed to fetch from GitHub API\n";
    echo "   Error: " . error_get_last()['message'] . "\n\n";
} else {
    $data = json_decode($response, true);
    echo "   Latest release tag: " . ($data['tag_name'] ?? 'N/A') . "\n";
    echo "   Latest version: " . ltrim($data['tag_name'] ?? '', 'v') . "\n\n";
}

// Now use UpdateChecker
echo "5. Using UpdateChecker->checkForUpdates()...\n";
$hasUpdate = $checker->checkForUpdates();
echo "   Update available: " . ($hasUpdate ? 'YES' : 'NO') . "\n\n";

$updateInfo = $checker->getUpdateInfo();
if ($updateInfo) {
    echo "6. Update Info:\n";
    print_r($updateInfo);
} else {
    echo "6. No update info available\n";
}

echo "\n7. Session data:\n";
echo "   last_update_check: " . ($_SESSION['last_update_check'] ?? 'not set') . "\n";
echo "   update_available: " . (isset($_SESSION['update_available']) ? ($_SESSION['update_available'] ? 'true' : 'false') : 'not set') . "\n";

echo "</pre>";

if ($hasUpdate && $updateInfo) {
    echo "<div style='padding:20px; background:#4CAF50; color:white; margin:20px 0;'>";
    echo "<h2>✅ UPDATE AVAILABLE!</h2>";
    echo "<p>Current: v" . $checker->getCurrentVersion() . "</p>";
    echo "<p>Latest: v" . $updateInfo['version'] . "</p>";
    echo "<p><a href='/settings/update' style='color:white; text-decoration:underline;'>Go to Update Page</a></p>";
    echo "</div>";
} else {
    echo "<div style='padding:20px; background:#f44336; color:white; margin:20px 0;'>";
    echo "<h2>❌ NO UPDATE DETECTED</h2>";
    echo "<p>Current: v" . $checker->getCurrentVersion() . "</p>";
    echo "<p>This is the problem we need to fix!</p>";
    echo "</div>";
}
?>
