<?php
/**
 * HandyCRM Update Checker
 * Checks GitHub for new versions and provides update notifications
 */

class UpdateChecker {
    private $currentVersion;
    public $githubRepo = 'TheoSfak/handycrm'; // GitHub repository
    private $updateCheckInterval = 86400; // 24 hours in seconds
    
    public function __construct() {
        $this->currentVersion = defined('APP_VERSION') ? APP_VERSION : '1.0.0';
        
        // Clear cached update info if version changed (handles upgrades)
        if (isset($_SESSION['cached_version']) && $_SESSION['cached_version'] !== $this->currentVersion) {
            unset($_SESSION['last_update_check']);
            unset($_SESSION['update_available']);
            unset($_SESSION['update_info']);
            unset($_SESSION['last_notification_update_check']);
            unset($_SESSION['cached_update_notification']);
        }
        $_SESSION['cached_version'] = $this->currentVersion;
    }
    
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
     * Get all available versions from GitHub
     */
    public function getAllVersions() {
        try {
            $url = "https://api.github.com/repos/{$this->githubRepo}/releases";
            
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
                return [];
            }
            
            $releases = json_decode($response, true);
            $versions = [];
            
            foreach ($releases as $release) {
                if (isset($release['tag_name'])) {
                    $version = ltrim($release['tag_name'], 'v');
                    $versions[] = [
                        'version' => $version,
                        'tag_name' => $release['tag_name'],
                        'release_notes' => $release['body'] ?? '',
                        'download_url' => $release['zipball_url'] ?? '',
                        'published_at' => $release['published_at'] ?? '',
                        'release_url' => $release['html_url'] ?? '',
                        'is_current' => version_compare($version, $this->currentVersion, '=='),
                        'is_newer' => version_compare($version, $this->currentVersion, '>'),
                        'is_older' => version_compare($version, $this->currentVersion, '<')
                    ];
                }
            }
            
            return $versions;
            
        } catch (Exception $e) {
            error_log('Failed to fetch version history: ' . $e->getMessage());
            return [];
        }
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
     * Get update notification HTML - Beautiful version
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
        <div class="alert alert-dismissible fade show p-0 border-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; overflow: hidden;">
            <div class="row g-0 align-items-center">
                <div class="col-auto" style="background: rgba(255,255,255,0.1); padding: 20px;">
                    <div class="text-white text-center">
                        <i class="fas fa-rocket fa-3x mb-2" style="animation: pulse 2s infinite;"></i>
                        <div class="fw-bold" style="font-size: 0.9rem;">ΕΝΗΜΕΡΩΣΗ</div>
                    </div>
                </div>
                <div class="col text-white" style="padding: 20px;">
                    <h5 class="mb-2 fw-bold">
                        <i class="fas fa-star me-2"></i>
                        Νέα Έκδοση Διαθέσιμη!
                    </h5>
                    <div class="mb-2">
                        <span class="badge bg-light text-dark me-2">
                            <i class="fas fa-clock"></i> Τρέχουσα: v' . $this->currentVersion . '
                        </span>
                        <i class="fas fa-arrow-right mx-2"></i>
                        <span class="badge bg-success">
                            <i class="fas fa-crown"></i> Νέα: v' . htmlspecialchars($info['version']) . '
                        </span>
                    </div>
                    <p class="mb-3 small opacity-75">
                        ' . (isset($info['description']) ? htmlspecialchars(substr($info['description'], 0, 100)) . '...' : 'Νέες δυνατότητες και βελτιώσεις διαθέσιμες!') . '
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="?route=/settings/update" class="btn btn-light btn-sm shadow-sm">
                            <i class="fas fa-download me-1"></i> Προβολή Λεπτομερειών
                        </a>
                        <a href="' . htmlspecialchars($info['release_url']) . '" target="_blank" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i> Release Notes
                        </a>
                        <a href="' . htmlspecialchars($info['download_url']) . '" class="btn btn-success btn-sm shadow-sm">
                            <i class="fas fa-cloud-download-alt me-1"></i> Κατέβασμα Τώρα
                        </a>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <style>
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
        </style>';
    }
}
