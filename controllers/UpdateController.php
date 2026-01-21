<?php
/**
 * UpdateController - Handles application updates
 * Automatically checks for and applies updates from v1.2.0+ to latest version
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/BaseController.php';

// SECURITY FIX: UpdateController must extend BaseController to enforce authentication
class UpdateController extends BaseController {
    
    /**
     * Get current application version
     */
    public function getCurrentVersion() {
        $versionFile = __DIR__ . '/../VERSION';
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        return '1.0.0';
    }
    
    /**
     * Get database version from migrations table
     */
    public function getDatabaseVersion() {
        try {
            // Check if migrations table exists
            $tables = $this->db->fetchAll("SHOW TABLES LIKE 'migrations'");
            if (empty($tables)) {
                return '1.0.0';
            }
            
            // Get latest migration version
            $result = $this->db->fetchOne("
                SELECT version 
                FROM migrations 
                ORDER BY executed_at DESC 
                LIMIT 1
            ");
            
            return $result ? $result['version'] : '1.0.0';
        } catch (Exception $e) {
            error_log('Failed to get current version in UpdateController::getCurrentVersion: ' . $e->getMessage());
            return '1.0.0';
        }
    }
    
    /**
     * Check if update is available
     */
    public function checkForUpdates() {
        $currentVersion = $this->getCurrentVersion();
        $dbVersion = $this->getDatabaseVersion();
        
        return [
            'current_version' => $currentVersion,
            'database_version' => $dbVersion,
            'update_available' => version_compare($currentVersion, $dbVersion, '>'),
            'updates_needed' => $this->getRequiredUpdates($dbVersion, $currentVersion)
        ];
    }
    
    /**
     * Get list of updates needed between two versions
     */
    private function getRequiredUpdates($fromVersion, $toVersion) {
        $updates = [];
        
        // Define available updates
        $availableUpdates = [
            '1.2.5' => [
                'name' => 'Materials Catalog Enhancement',
                'description' => '100 electrical materials, CSV operations, pagination',
                'migrations' => [
                    'load_electrical_materials.sql',
                    'load_electrical_materials_part2.sql'
                ],
                'scripts' => [
                    'regenerate_material_aliases.php'
                ]
            ]
        ];
        
        foreach ($availableUpdates as $version => $update) {
            if (version_compare($version, $fromVersion, '>') && 
                version_compare($version, $toVersion, '<=')) {
                $updates[] = array_merge(['version' => $version], $update);
            }
        }
        
        return $updates;
    }
    
    /**
     * Apply all pending updates
     */
    public function applyUpdates() {
        $check = $this->checkForUpdates();
        
        if (!$check['update_available']) {
            return [
                'success' => true,
                'message' => 'Η εφαρμογή είναι ήδη ενημερωμένη',
                'updates_applied' => 0
            ];
        }
        
        $results = [];
        $totalSuccess = 0;
        $totalFailed = 0;
        
        foreach ($check['updates_needed'] as $update) {
            $result = $this->applyUpdate($update);
            $results[] = $result;
            
            if ($result['success']) {
                $totalSuccess++;
            } else {
                $totalFailed++;
            }
        }
        
        return [
            'success' => $totalFailed == 0,
            'message' => "Εφαρμόστηκαν {$totalSuccess} ενημερώσεις" . 
                        ($totalFailed > 0 ? ", {$totalFailed} απέτυχαν" : ''),
            'updates_applied' => $totalSuccess,
            'updates_failed' => $totalFailed,
            'details' => $results
        ];
    }
    
    /**
     * Apply a single update
     */
    private function applyUpdate($update) {
        $version = $update['version'];
        $migrationsPath = __DIR__ . '/../migrations/';
        
        try {
            $this->db->beginTransaction();
            
            // Apply SQL migrations
            foreach ($update['migrations'] as $migration) {
                $filepath = $migrationsPath . $migration;
                
                if (!file_exists($filepath)) {
                    throw new Exception("Migration file not found: {$migration}");
                }
                
                $sql = file_get_contents($filepath);
                $this->executeMigration($sql, $migration);
            }
            
            // Run PHP scripts
            foreach ($update['scripts'] as $script) {
                $scriptPath = $migrationsPath . $script;
                
                if (!file_exists($scriptPath)) {
                    throw new Exception("Script file not found: {$script}");
                }
                
                // Execute script and capture output
                ob_start();
                include $scriptPath;
                $output = ob_get_clean();
            }
            
            // Record migration
            $this->recordMigration($version, $update['name']);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'version' => $version,
                'name' => $update['name'],
                'message' => "Επιτυχής ενημέρωση σε έκδοση {$version}"
            ];
            
        } catch (Exception $e) {
            error_log('Update application failed in UpdateController::applyUpdates: ' . $e->getMessage());
            $this->db->rollBack();
            
            return [
                'success' => false,
                'version' => $version,
                'name' => $update['name'],
                'message' => "Αποτυχία ενημέρωσης: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Execute SQL migration file
     */
    private function executeMigration($sql, $migrationName) {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        // Split by semicolon but not inside quotes
        $statements = preg_split('/;(?=(?:[^"\']*["\'][^"\']*["\'])*[^"\']*$)/', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            try {
                $this->db->execute($statement);
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
        }
    }
    
    /**
     * Record migration in database
     */
    private function recordMigration($version, $name) {
        $sql = "
            INSERT INTO migrations (migration, version, executed_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE executed_at = NOW()
        ";
        
        $this->db->execute($sql, [$name, $version]);
    }
    
    /**
     * Show update page
     */
    public function index() {
        $updateInfo = $this->checkForUpdates();
        require __DIR__ . '/../views/update/index.php';
    }
    
    /**
     * Process update request (AJAX)
     */
    public function process() {
        header('Content-Type: application/json');
        
        try {
            $result = $this->applyUpdates();
            echo json_encode($result);
        } catch (Exception $e) {
            error_log('Update process failed in UpdateController::process: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Σφάλμα: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get update status (AJAX)
     */
    public function status() {
        header('Content-Type: application/json');
        echo json_encode($this->checkForUpdates());
    }
}
