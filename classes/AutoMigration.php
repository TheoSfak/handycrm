<?php
/**
 * Auto Migration System
 * Automatically runs pending migrations on application load
 * 
 * @author Theodore Sfakianakis
 * @version 1.2.0
 */

class AutoMigration {
    private $db;
    private $migrationsPath;
    
    public function __construct($db) {
        $this->db = $db;
        $this->migrationsPath = __DIR__ . '/../migrations/';
        
        // Create migrations tracking table if it doesn't exist
        $this->createMigrationsTable();
    }
    
    /**
     * Create migrations tracking table
     */
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_migration (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        try {
            $this->db->execute($sql);
        } catch (Exception $e) {
            error_log('AutoMigration: Failed to create migrations table - ' . $e->getMessage());
        }
    }
    
    /**
     * Check if there are pending migrations and run them
     * 
     * @return array Status information
     */
    public function checkAndRun() {
        $pending = $this->getPendingMigrations();
        
        if (empty($pending)) {
            return [
                'has_pending' => false,
                'executed' => 0,
                'errors' => []
            ];
        }
        
        $results = [
            'has_pending' => true,
            'executed' => 0,
            'errors' => []
        ];
        
        foreach ($pending as $migration) {
            $result = $this->executeMigration($migration);
            
            if ($result['success']) {
                $results['executed']++;
                $this->markAsExecuted($migration);
            } else {
                $results['errors'][] = [
                    'file' => $migration,
                    'error' => $result['message']
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get list of pending migrations
     * 
     * @return array List of migration filenames
     */
    private function getPendingMigrations() {
        // Get executed migrations
        try {
            $sql = "SELECT migration FROM migrations";
            $stmt = $this->db->query($sql);
            $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log('AutoMigration: Failed to get executed migrations - ' . $e->getMessage());
            $executed = [];
        }
        
        // Get all migration files
        $files = glob($this->migrationsPath . '*.sql');
        $pending = [];
        
        if ($files === false) {
            return $pending;
        }
        
        foreach ($files as $file) {
            $filename = basename($file);
            
            // Skip if already executed
            if (in_array($filename, $executed)) {
                continue;
            }
            
            $pending[] = $filename;
        }
        
        // Sort by filename (chronological order)
        sort($pending);
        
        return $pending;
    }
    
    /**
     * Execute a migration file
     * 
     * @param string $filename Migration filename
     * @return array [success => bool, message => string]
     */
    private function executeMigration($filename) {
        $filePath = $this->migrationsPath . $filename;
        
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'message' => "Migration file not found: $filename"
            ];
        }
        
        try {
            // Read SQL file
            $sql = file_get_contents($filePath);
            
            // Remove comments
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            
            // Split into statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $executedCount = 0;
            
            foreach ($statements as $statement) {
                if (empty($statement) || strlen($statement) < 5) {
                    continue;
                }
                
                try {
                    $this->db->execute($statement);
                    $executedCount++;
                } catch (PDOException $e) {
                    $errorMsg = $e->getMessage();
                    
                    // Ignore "already exists" and "duplicate" errors
                    if (stripos($errorMsg, 'already exists') !== false ||
                        stripos($errorMsg, 'duplicate') !== false ||
                        stripos($errorMsg, 'Duplicate column') !== false ||
                        stripos($errorMsg, 'Duplicate key') !== false) {
                        continue;
                    }
                    
                    // Log other errors but continue
                    error_log("AutoMigration: Error in $filename - " . $errorMsg);
                }
            }
            
            return [
                'success' => true,
                'message' => "Executed $executedCount statements"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Mark migration as executed
     * 
     * @param string $filename Migration filename
     */
    private function markAsExecuted($filename) {
        try {
            $sql = "INSERT IGNORE INTO migrations (migration) VALUES (?)";
            $this->db->execute($sql, [$filename]);
        } catch (Exception $e) {
            error_log('AutoMigration: Failed to mark as executed - ' . $e->getMessage());
        }
    }
    
    /**
     * Check if migration was executed
     * 
     * @param string $filename Migration filename
     * @return bool
     */
    public function isExecuted($filename) {
        try {
            $sql = "SELECT COUNT(*) FROM migrations WHERE migration = ?";
            $stmt = $this->db->query($sql, [$filename]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all executed migrations
     * 
     * @return array
     */
    public function getExecutedMigrations() {
        try {
            $sql = "SELECT migration, executed_at FROM migrations ORDER BY executed_at DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
