<?php
/**
 * HandyCRM Version Manager
 * Handles automated version installation and rollback
 */

class VersionManager {
    private $tempDir;
    private $rootDir;
    private $backupManager;
    
    public function __construct() {
        $this->tempDir = sys_get_temp_dir() . '/handycrm_versions';
        $this->rootDir = dirname(__DIR__);
        
        require_once __DIR__ . '/BackupManager.php';
        $this->backupManager = new BackupManager();
        
        $this->ensureTempDirectory();
    }
    
    /**
     * Ensure temp directory exists
     */
    private function ensureTempDirectory() {
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }
    
    /**
     * Install version from GitHub (fully automated)
     */
    public function installVersion($downloadUrl, $version) {
        try {
            $currentVersion = $this->getCurrentVersion();
            
            // Step 1: Create backup first
            $backupResult = $this->backupManager->createBackup($currentVersion);
            if (!$backupResult['success']) {
                throw new Exception('Failed to create backup: ' . $backupResult['message']);
            }
            
            // Step 2: Download version
            $zipFile = $this->downloadVersion($downloadUrl, $version);
            if (!$zipFile) {
                throw new Exception('Failed to download version');
            }
            
            // Step 3: Extract version
            $extractPath = $this->extractVersion($zipFile, $version);
            if (!$extractPath) {
                throw new Exception('Failed to extract version');
            }
            
            // Step 4: Install files
            $installResult = $this->installFiles($extractPath);
            if (!$installResult) {
                throw new Exception('Failed to install files');
            }
            
            // Step 5: Run database migrations using AutoMigration
            require_once __DIR__ . '/Database.php';
            require_once __DIR__ . '/AutoMigration.php';
            
            $db = new Database();
            $autoMigration = new AutoMigration($db);
            $migrationResult = $autoMigration->checkAndRun();
            
            $migrationMessage = '';
            if ($migrationResult['executed'] > 0) {
                $migrationMessage = ' Εκτελέστηκαν ' . $migrationResult['executed'] . ' migrations.';
            }
            
            if (!empty($migrationResult['errors'])) {
                error_log('VersionManager - Migration warnings: ' . implode(', ', $migrationResult['errors']));
            }
            
            // Step 6: Update version in config.php
            $versionUpdateResult = $this->updateConfigVersion($version);
            if (!$versionUpdateResult) {
                error_log('Warning: Failed to update version in config.php');
            }
            
            // Step 7: Cleanup
            $this->cleanup($zipFile, $extractPath);
            
            return [
                'success' => true,
                'message' => "Η έκδοση v{$version} εγκαταστάθηκε επιτυχώς!{$migrationMessage} Το backup αποθηκεύτηκε.",
                'backup_name' => $backupResult['backup_name'],
                'migrations' => $migrationResult
            ];
            
        } catch (Exception $e) {
            error_log('Version installation failed: ' . $e->getMessage());
            
            // Try to restore from backup if installation failed
            if (isset($backupResult['backup_name'])) {
                $this->backupManager->restoreBackup($backupResult['backup_name']);
            }
            
            return [
                'success' => false,
                'message' => 'Αποτυχία εγκατάστασης: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Download version from GitHub
     */
    private function downloadVersion($url, $version) {
        try {
            $zipFile = $this->tempDir . "/handycrm_v{$version}.zip";
            
            // Use cURL for better reliability
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                $fp = fopen($zipFile, 'wb');
                
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);
                curl_setopt($ch, CURLOPT_USERAGENT, 'HandyCRM-Version-Manager');
                
                $success = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                curl_close($ch);
                fclose($fp);
                
                if ($success && $httpCode == 200 && file_exists($zipFile) && filesize($zipFile) > 0) {
                    return $zipFile;
                }
            } else {
                // Fallback to file_get_contents
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => 'User-Agent: HandyCRM-Version-Manager',
                        'follow_location' => true,
                        'timeout' => 300
                    ]
                ]);
                
                $data = @file_get_contents($url, false, $context);
                
                if ($data !== false) {
                    file_put_contents($zipFile, $data);
                    return $zipFile;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('Download failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extract version ZIP file
     */
    private function extractVersion($zipFile, $version) {
        try {
            if (!class_exists('ZipArchive')) {
                throw new Exception('ZipArchive extension not available');
            }
            
            $extractPath = $this->tempDir . "/handycrm_v{$version}_extracted";
            
            // Remove old extraction if exists
            if (file_exists($extractPath)) {
                $this->recursiveDelete($extractPath);
            }
            
            mkdir($extractPath, 0755, true);
            
            $zip = new ZipArchive();
            if ($zip->open($zipFile) === true) {
                $zip->extractTo($extractPath);
                $zip->close();
                
                // GitHub creates a subdirectory, find it
                $dirs = glob($extractPath . '/*', GLOB_ONLYDIR);
                if (!empty($dirs)) {
                    return $dirs[0]; // Return the actual content directory
                }
                
                return $extractPath;
            }
            
            throw new Exception('Failed to open ZIP file');
            
        } catch (Exception $e) {
            error_log('Extraction failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Install files from extracted version
     */
    private function installFiles($extractPath) {
        try {
            // Files and directories to install
            $itemsToInstall = [
                'classes/',
                'controllers/',
                'models/',
                'views/',
                'public/',
                'index.php',
                '.htaccess'
            ];
            
            // Files and directories to NEVER overwrite
            $protectedItems = [
                'config/config.php',
                'uploads/',
                'backups/',
                '.git/',
                '.env'
            ];
            
            foreach ($itemsToInstall as $item) {
                $source = $extractPath . '/' . $item;
                $dest = $this->rootDir . '/' . $item;
                
                if (file_exists($source)) {
                    if (is_dir($source)) {
                        $this->recursiveCopy($source, $dest, $protectedItems);
                    } else {
                        // Check if file is protected
                        $isProtected = false;
                        foreach ($protectedItems as $protected) {
                            if (strpos($item, $protected) !== false) {
                                $isProtected = true;
                                break;
                            }
                        }
                        
                        if (!$isProtected) {
                            $destDir = dirname($dest);
                            if (!file_exists($destDir)) {
                                mkdir($destDir, 0755, true);
                            }
                            copy($source, $dest);
                        }
                    }
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Installation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Recursive copy with protection
     */
    private function recursiveCopy($src, $dst, $protectedItems = []) {
        $dir = opendir($src);
        
        // Check if destination is protected
        foreach ($protectedItems as $protected) {
            $protectedPath = $this->rootDir . '/' . $protected;
            if (strpos($dst, $protectedPath) === 0) {
                return; // Skip protected directories
            }
        }
        
        @mkdir($dst, 0755, true);
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;
                
                // Check if this specific file is protected
                $isProtected = false;
                foreach ($protectedItems as $protected) {
                    $protectedPath = $this->rootDir . '/' . $protected;
                    if (strpos($dstFile, $protectedPath) === 0) {
                        $isProtected = true;
                        break;
                    }
                }
                
                if (!$isProtected) {
                    if (is_dir($srcFile)) {
                        $this->recursiveCopy($srcFile, $dstFile, $protectedItems);
                    } else {
                        copy($srcFile, $dstFile);
                    }
                }
            }
        }
        
        closedir($dir);
    }
    
    /**
     * Cleanup temporary files
     */
    private function cleanup($zipFile, $extractPath) {
        try {
            if (file_exists($zipFile)) {
                unlink($zipFile);
            }
            
            if (file_exists($extractPath)) {
                $this->recursiveDelete($extractPath);
            }
        } catch (Exception $e) {
            error_log('Cleanup warning: ' . $e->getMessage());
        }
    }
    
    /**
     * Recursive delete directory
     */
    private function recursiveDelete($dir) {
        if (!file_exists($dir)) {
            return;
        }
        
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->recursiveDelete($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    /**
     * Get current version
     */
    private function getCurrentVersion() {
        require_once __DIR__ . '/UpdateChecker.php';
        $checker = new UpdateChecker();
        return $checker->getCurrentVersion();
    }
    
    /**
     * Update APP_VERSION in config.php
     */
    private function updateConfigVersion($newVersion) {
        try {
            $configFile = $this->rootDir . '/config/config.php';
            
            if (!file_exists($configFile)) {
                error_log('Config file not found: ' . $configFile);
                return false;
            }
            
            // Read the config file
            $configContent = file_get_contents($configFile);
            
            if ($configContent === false) {
                error_log('Failed to read config file');
                return false;
            }
            
            // Update APP_VERSION constant
            // Match: define('APP_VERSION', '1.0.3');
            $pattern = "/(define\s*\(\s*['\"]APP_VERSION['\"]\s*,\s*['\"])([^'\"]+)(['\"])/";
            $replacement = '${1}' . $newVersion . '${3}';
            
            $newContent = preg_replace($pattern, $replacement, $configContent);
            
            if ($newContent === null || $newContent === $configContent) {
                // Pattern didn't match, log warning but don't fail
                error_log('Could not find APP_VERSION pattern in config.php');
                return false;
            }
            
            // Write the updated config
            $result = file_put_contents($configFile, $newContent);
            
            if ($result === false) {
                error_log('Failed to write updated config file');
                return false;
            }
            
            error_log('Successfully updated APP_VERSION to ' . $newVersion);
            return true;
            
        } catch (Exception $e) {
            error_log('Failed to update config version: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify installation
     */
    public function verifyInstallation($version) {
        // Check if key files exist
        $keyFiles = [
            'index.php',
            'classes/UpdateChecker.php',
            'controllers/DashboardController.php',
            'views/dashboard/index.php'
        ];
        
        foreach ($keyFiles as $file) {
            if (!file_exists($this->rootDir . '/' . $file)) {
                return false;
            }
        }
        
        return true;
    }
}
