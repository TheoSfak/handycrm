<?php
/**
 * HandyCRM Migration Manager
 * Handles automatic database migrations during updates
 */

class MigrationManager {
    private $db;
    private $migrationsPath;
    
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = new Database();
        $this->migrationsPath = dirname(__DIR__) . '/database/migrations';
        
        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();
    }
    
    /**
     * Create migrations tracking table
     */
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `version` varchar(20) NOT NULL,
            `migration_name` varchar(255) NOT NULL,
            `executed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `version_migration` (`version`, `migration_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->execute($sql);
        } catch (Exception $e) {
            error_log('Failed to create migrations table: ' . $e->getMessage());
        }
    }
    
    /**
     * Run all pending migrations for a version
     */
    public function runMigrations($fromVersion, $toVersion) {
        $results = [
            'success' => true,
            'migrations_run' => [],
            'errors' => []
        ];
        
        try {
            // Get migration file for this upgrade path
            $migrationFile = $this->getMigrationFile($fromVersion, $toVersion);
            
            if ($migrationFile && file_exists($migrationFile)) {
                $result = $this->executeMigration($migrationFile, $toVersion);
                $results['migrations_run'][] = basename($migrationFile);
                
                if (!$result['success']) {
                    $results['success'] = false;
                    $results['errors'][] = $result['error'];
                }
            }
            
            // Also run any version-specific migrations
            $versionMigrations = $this->getVersionMigrations($toVersion);
            foreach ($versionMigrations as $migration) {
                if (!$this->isMigrationExecuted($toVersion, basename($migration))) {
                    $result = $this->executeMigration($migration, $toVersion);
                    $results['migrations_run'][] = basename($migration);
                    
                    if (!$result['success']) {
                        $results['success'] = false;
                        $results['errors'][] = $result['error'];
                    }
                }
            }
            
        } catch (Exception $e) {
            $results['success'] = false;
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Get migration file for upgrade path
     */
    private function getMigrationFile($fromVersion, $toVersion) {
        // Format: migrate_1.0.6_to_1.1.0.sql
        $fromClean = str_replace('.', '_', $fromVersion);
        $toClean = str_replace('.', '_', $toVersion);
        
        $filename = "migrate_{$fromClean}_to_{$toClean}.sql";
        $filepath = $this->migrationsPath . '/' . $filename;
        
        return file_exists($filepath) ? $filepath : null;
    }
    
    /**
     * Get all migrations for a specific version
     */
    private function getVersionMigrations($version) {
        $migrations = [];
        $versionClean = str_replace('.', '_', $version);
        
        if (!is_dir($this->migrationsPath)) {
            return $migrations;
        }
        
        $files = glob($this->migrationsPath . "/v{$versionClean}_*.sql");
        return $files ?: [];
    }
    
    /**
     * Check if migration was already executed
     */
    private function isMigrationExecuted($version, $migrationName) {
        $sql = "SELECT COUNT(*) as count FROM migrations 
                WHERE version = ? AND migration_name = ?";
        
        try {
            $result = $this->db->fetchOne($sql, [$version, $migrationName]);
            return $result && $result['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Execute a migration file
     */
    private function executeMigration($filepath, $version) {
        try {
            $sql = file_get_contents($filepath);
            
            if (empty($sql)) {
                return ['success' => false, 'error' => 'Empty migration file'];
            }
            
            // Split SQL into individual statements
            $statements = $this->splitSqlStatements($sql);
            
            $errors = [];
            foreach ($statements as $statement) {
                $statement = trim($statement);
                
                // Skip empty statements and comments
                if (empty($statement) || substr($statement, 0, 2) === '--') {
                    continue;
                }
                
                try {
                    $this->db->execute($statement);
                } catch (Exception $e) {
                    // Some errors are acceptable (e.g., column already exists)
                    $errorMsg = $e->getMessage();
                    
                    // Ignore these specific errors (they mean migration already ran)
                    if (strpos($errorMsg, 'Duplicate column') === false &&
                        strpos($errorMsg, 'already exists') === false &&
                        strpos($errorMsg, 'Table') === false) {
                        $errors[] = $errorMsg;
                    }
                }
            }
            
            // Record migration as executed
            $this->recordMigration($version, basename($filepath));
            
            return [
                'success' => count($errors) === 0,
                'error' => implode('; ', $errors)
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Split SQL file into individual statements
     */
    private function splitSqlStatements($sql) {
        // Remove SQL comments
        $sql = preg_replace('/--[^\n]*\n/', '', $sql);
        
        // Split by semicolon (but not inside quotes or parentheses)
        $statements = [];
        $current = '';
        $inQuote = false;
        $quoteChar = '';
        $parenthesesLevel = 0;
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (($char === '"' || $char === "'") && ($i === 0 || $sql[$i-1] !== '\\')) {
                if (!$inQuote) {
                    $inQuote = true;
                    $quoteChar = $char;
                } elseif ($char === $quoteChar) {
                    $inQuote = false;
                }
            }
            
            if (!$inQuote) {
                if ($char === '(') $parenthesesLevel++;
                if ($char === ')') $parenthesesLevel--;
                
                if ($char === ';' && $parenthesesLevel === 0) {
                    $statements[] = $current;
                    $current = '';
                    continue;
                }
            }
            
            $current .= $char;
        }
        
        if (!empty(trim($current))) {
            $statements[] = $current;
        }
        
        return $statements;
    }
    
    /**
     * Record migration as executed
     */
    private function recordMigration($version, $migrationName) {
        $sql = "INSERT IGNORE INTO migrations (version, migration_name) 
                VALUES (?, ?)";
        
        try {
            $this->db->execute($sql, [$version, $migrationName]);
        } catch (Exception $e) {
            error_log('Failed to record migration: ' . $e->getMessage());
        }
    }
    
    /**
     * Get list of executed migrations
     */
    public function getExecutedMigrations() {
        $sql = "SELECT * FROM migrations ORDER BY executed_at DESC";
        
        try {
            $stmt = $this->db->execute($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
