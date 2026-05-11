<?php
/**
 * Check for Updates API Endpoint
 * Returns JSON with update availability
 */

session_start();
require_once __DIR__ . '/../classes/UpdateChecker.php';

header('Content-Type: application/json');

try {
    $updateChecker = new UpdateChecker();
    
    // Check if we should check for updates (throttle to once per hour for notifications)
    $lastCheck = $_SESSION['last_notification_update_check'] ?? 0;
    $checkInterval = 3600; // 1 hour
    
    if (time() - $lastCheck < $checkInterval && isset($_SESSION['cached_update_notification'])) {
        // Return cached result only if it still represents a real newer version.
        $cachedResponse = $_SESSION['cached_update_notification'];
        $latestVersion = $cachedResponse['latest_version']
            ?? ($cachedResponse['update_info']['latest_version'] ?? ($cachedResponse['update_info']['version'] ?? null));
        $installedVersion = $updateChecker->getCurrentVersion();

        if ($latestVersion && version_compare(ltrim($latestVersion, "vV \t\n\r\0\x0B"), $installedVersion, '>')) {
            $cachedResponse['current_version'] = $installedVersion;
            $cachedResponse['installed_version'] = $installedVersion;
            echo json_encode($cachedResponse);
            exit;
        }

        unset($_SESSION['last_notification_update_check']);
        unset($_SESSION['cached_update_notification']);
    }
    
    // Check for updates
    $updateAvailable = $updateChecker->checkForUpdates();
    $updateInfo = $updateChecker->getUpdateInfo();
    
    $response = [
        'success' => true,
        'update_available' => $updateAvailable,
        'current_version' => $updateChecker->getCurrentVersion(),
        'installed_version' => $updateChecker->getCurrentVersion(),
        'latest_version' => $updateInfo['latest_version'] ?? ($updateInfo['version'] ?? null),
        'update_info' => $updateInfo
    ];
    
    // Cache the result
    $_SESSION['last_notification_update_check'] = time();
    $_SESSION['cached_update_notification'] = $response;
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
