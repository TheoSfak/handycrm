<?php
/**
 * HandyCRM Backup Manager
 * Handles automatic backups before updates and rollbacks
 */

class BackupManager {
    private $backupDir;
    private $maxBackups = 5; // Keep last 5 backups
    
    public function __construct() {
        $this->backupDir = __DIR__ . '/../backups';
        $this->ensureBackupDirectory();
    }
    
    /**
     * Ensure backup directory exists
     */
    private function ensureBackupDirectory() {
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        
        // Create .htaccess to protect backups
        $htaccess = $this->backupDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all");
        }
    }
    
    /**
     * Create full system backup
     */
    public function createBackup($version = null) {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $version = $version ?? 'unknown';
            $backupName = "backup_v{$version}_{$timestamp}";
            $backupPath = $this->backupDir . '/' . $backupName;
            
            // Create backup directory
            if (!mkdir($backupPath, 0755, true)) {
                throw new Exception('Failed to create backup directory');
            }
            
            // Backup files
            $this->backupFiles($backupPath);
            
            // Backup database
            $this->backupDatabase($backupPath);
            
            // Create backup info file
            $this->createBackupInfo($backupPath, $version);
            
            // Clean old backups
            $this->cleanOldBackups();
            
            return [
                'success' => true,
                'backup_name' => $backupName,
                'backup_path' => $backupPath,
                'message' => 'Backup created successfully'
            ];
            
        } catch (Exception $e) {
            error_log('Backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Backup important files
     */
    private function backupFiles($backupPath) {
        $rootDir = dirname(__DIR__);
        $filesBackupDir = $backupPath . '/files';
        mkdir($filesBackupDir, 0755, true);
        
        // Files and directories to backup
        $itemsToBackup = [
            'config/config.php',
            'classes/',
            'controllers/',
            'models/',
            'views/',
            'public/',
            'uploads/'
        ];
        
        foreach ($itemsToBackup as $item) {
            $source = $rootDir . '/' . $item;
            $dest = $filesBackupDir . '/' . $item;
            
            if (file_exists($source)) {
                if (is_dir($source)) {
                    $this->recursiveCopy($source, $dest);
                } else {
                    $destDir = dirname($dest);
                    if (!file_exists($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    copy($source, $dest);
                }
            }
        }
    }
    
    /**
     * Recursive copy directory
     */
    private function recursiveCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst, 0755, true);
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        
        closedir($dir);
    }
    
    /**
     * Backup database
     */
    private function backupDatabase($backupPath) {
        try {
            // Load database config
            require_once __DIR__ . '/../config/config.php';
            
            $backupFile = $backupPath . '/database.sql';
            
            // Get database credentials from config
            $host = DB_HOST;
            $database = DB_NAME;
            $username = DB_USER;
            $password = DB_PASS;
            
            // Try mysqldump first
            $mysqldumpPath = $this->findMysqldump();
            
            if ($mysqldumpPath) {
                $command = sprintf(
                    '"%s" --host=%s --user=%s --password=%s %s > "%s" 2>&1',
                    $mysqldumpPath,
                    escapeshellarg($host),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    $backupFile
                );
                
                exec($command, $output, $returnVar);
                
                if ($returnVar === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
                    return true;
                }
            }
            
            // Fallback to PHP-based backup
            $this->phpDatabaseBackup($backupFile);
            
        } catch (Exception $e) {
            error_log('Database backup failed: ' . $e->getMessage());
            // Continue even if DB backup fails
        }
    }
    
    /**
     * Find mysqldump executable
     */
    private function findMysqldump() {
        $possiblePaths = [
            'C:\xampp\mysql\bin\mysqldump.exe',
            'C:\wamp\bin\mysql\mysql8.0.27\bin\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'mysqldump' // Try system PATH
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Try to find it using 'where' or 'which'
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('where mysqldump 2>nul', $output, $returnVar);
        } else {
            exec('which mysqldump 2>/dev/null', $output, $returnVar);
        }
        
        if ($returnVar === 0 && !empty($output[0])) {
            return $output[0];
        }
        
        return null;
    }
    
    /**
     * PHP-based database backup (fallback)
     */
    private function phpDatabaseBackup($backupFile) {
        require_once __DIR__ . '/../config/config.php';
        
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception('Database connection failed');
        }
        
        $sql = "-- HandyCRM Database Backup\n";
        $sql .= "-- Created: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Get all tables
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        foreach ($tables as $table) {
            $sql .= "\n-- Table: {$table}\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            
            // Get CREATE TABLE statement
            $result = $conn->query("SHOW CREATE TABLE `{$table}`");
            $row = $result->fetch_array();
            $sql .= $row[1] . ";\n\n";
            
            // Get table data
            $result = $conn->query("SELECT * FROM `{$table}`");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $values = array_map(function($value) use ($conn) {
                        return $value === null ? 'NULL' : "'" . $conn->real_escape_string($value) . "'";
                    }, $row);
                    
                    $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }
        
        file_put_contents($backupFile, $sql);
        $conn->close();
    }
    
    /**
     * Create backup info file
     */
    private function createBackupInfo($backupPath, $version) {
        $info = [
            'version' => $version,
            'created_at' => date('Y-m-d H:i:s'),
            'php_version' => phpversion(),
            'backup_type' => 'full'
        ];
        
        file_put_contents(
            $backupPath . '/backup_info.json',
            json_encode($info, JSON_PRETTY_PRINT)
        );
    }
    
    /**
     * Clean old backups (keep only last N)
     */
    private function cleanOldBackups() {
        $backups = glob($this->backupDir . '/backup_*');
        
        if (count($backups) > $this->maxBackups) {
            // Sort by modification time
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remove oldest backups
            $toRemove = array_slice($backups, 0, count($backups) - $this->maxBackups);
            foreach ($toRemove as $backup) {
                $this->recursiveDelete($backup);
            }
        }
    }
    
    /**
     * Delete a specific backup
     */
    public function deleteBackup($backupName) {
        try {
            $backupPath = $this->backupDir . '/' . $backupName;
            
            if (!file_exists($backupPath)) {
                throw new Exception('Backup not found');
            }
            
            // Don't allow deleting if it's the only backup
            $allBackups = glob($this->backupDir . '/backup_*');
            if (count($allBackups) <= 1) {
                throw new Exception('Cannot delete the last backup');
            }
            
            // Delete the backup directory
            $this->recursiveDelete($backupPath);
            
            return [
                'success' => true,
                'message' => 'Το backup διαγράφηκε επιτυχώς'
            ];
            
        } catch (Exception $e) {
            error_log('Backup deletion failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Αποτυχία διαγραφής: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all available backups
     */
    public function getBackups() {
        $backups = glob($this->backupDir . '/backup_*');
        $backupList = [];
        
        foreach ($backups as $backup) {
            $infoFile = $backup . '/backup_info.json';
            if (file_exists($infoFile)) {
                $info = json_decode(file_get_contents($infoFile), true);
                $info['path'] = $backup;
                $info['name'] = basename($backup);
                $info['size'] = $this->getDirectorySize($backup);
                $backupList[] = $info;
            }
        }
        
        // Sort by creation time (newest first)
        usort($backupList, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $backupList;
    }
    
    /**
     * Restore from backup
     */
    public function restoreBackup($backupName) {
        try {
            $backupPath = $this->backupDir . '/' . $backupName;
            
            if (!file_exists($backupPath)) {
                throw new Exception('Backup not found');
            }
            
            // Create safety backup of current state
            $this->createBackup('pre_restore');
            
            // Restore files
            $this->restoreFiles($backupPath);
            
            // Restore database
            $this->restoreDatabase($backupPath);
            
            return [
                'success' => true,
                'message' => 'System restored successfully'
            ];
            
        } catch (Exception $e) {
            error_log('Restore failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Restore files from backup
     */
    private function restoreFiles($backupPath) {
        $rootDir = dirname(__DIR__);
        $filesBackupDir = $backupPath . '/files';
        
        if (!file_exists($filesBackupDir)) {
            throw new Exception('Backup files not found');
        }
        
        // Copy files back
        $this->recursiveCopy($filesBackupDir, $rootDir);
    }
    
    /**
     * Restore database from backup
     */
    private function restoreDatabase($backupPath) {
        $sqlFile = $backupPath . '/database.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception('Database backup not found');
        }
        
        require_once __DIR__ . '/../config/config.php';
        
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception('Database connection failed');
        }
        
        // Read and execute SQL file
        $sql = file_get_contents($sqlFile);
        
        // Split into individual queries
        $queries = array_filter(
            explode(";\n", $sql),
            function($query) {
                return trim($query) !== '' && !preg_match('/^--/', trim($query));
            }
        );
        
        foreach ($queries as $query) {
            if (trim($query) !== '') {
                if (!$conn->query($query)) {
                    error_log('Query failed: ' . $conn->error);
                }
            }
        }
        
        $conn->close();
    }
    
    /**
     * Get directory size
     */
    private function getDirectorySize($path) {
        $size = 0;
        
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }
        
        return $this->formatBytes($size);
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
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
}
