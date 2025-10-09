<?php
/**
 * HandyCRM Update Checker
 * Checks GitHub for new versions and provides update notifications
 */

class UpdateChecker {
    private $currentVersion = '1.0.0';
    public $githubRepo = 'TheoSfak/handycrm'; // GitHub repository
    private $updateCheckInterval = 86400; // 24 hours in seconds
    
    /**
     * Check if update is available
     */
    public function checkForUpdates() {
        // Check if we should check for updates (throttle to once per day)
        $lastCheck = $_SESSION['last_update_check'] ?? 0;
        if (time() - $lastCheck < $this->updateCheckInterval) {
            return $_SESSION['update_available'] ?? false;
        }
        
        try {
            // Fetch latest release from GitHub API
            $url = "https://api.github.com/repos/{$this->githubRepo}/releases/latest";
            
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
                return false;
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['tag_name'])) {
                $latestVersion = ltrim($data['tag_name'], 'v');
                $updateInfo = [
                    'version' => $latestVersion,
                    'release_notes' => $data['body'] ?? '',
                    'download_url' => $data['zipball_url'] ?? '',
                    'published_at' => $data['published_at'] ?? '',
                    'release_url' => $data['html_url'] ?? ''
                ];
                
                // Compare versions
                $isUpdateAvailable = version_compare($latestVersion, $this->currentVersion, '>');
                
                // Cache results
                $_SESSION['last_update_check'] = time();
                $_SESSION['update_available'] = $isUpdateAvailable;
                $_SESSION['update_info'] = $updateInfo;
                
                return $isUpdateAvailable;
            }
            
        } catch (Exception $e) {
            error_log('Update check failed: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Get update information
     */
    public function getUpdateInfo() {
        return $_SESSION['update_info'] ?? null;
    }
    
    /**
     * Get current version
     */
    public function getCurrentVersion() {
        return $this->currentVersion;
    }
    
    /**
     * Download and extract update
     */
    public function downloadUpdate($downloadUrl) {
        $tmpFile = sys_get_temp_dir() . '/handycrm_update.zip';
        
        // Download update
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: HandyCRM-Update-Downloader',
                'timeout' => 300 // 5 minutes for large files
            ]
        ]);
        
        $data = @file_get_contents($downloadUrl, false, $context);
        
        if ($data === false) {
            throw new Exception('Failed to download update');
        }
        
        file_put_contents($tmpFile, $data);
        
        return $tmpFile;
    }
    
    /**
     * Get update notification HTML
     */
    public function getUpdateNotification() {
        if (!$this->checkForUpdates()) {
            return '';
        }
        
        $info = $this->getUpdateInfo();
        if (!$info) {
            return '';
        }
        
        return '
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-cloud-download-alt"></i> 
                Νέα Έκδοση Διαθέσιμη: v' . htmlspecialchars($info['version']) . '
            </h5>
            <p class="mb-2">Η τρέχουσα έκδοση είναι v' . $this->currentVersion . '</p>
            <hr>
            <div class="d-flex gap-2">
                <a href="?route=/settings/update" class="btn btn-primary btn-sm">
                    <i class="fas fa-download"></i> Προβολή Ενημέρωσης
                </a>
                <a href="' . htmlspecialchars($info['release_url']) . '" target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-external-link-alt"></i> Release Notes
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>';
    }
}
