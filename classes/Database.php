<?php
/**
 * Database Connection Class
 * Handles all database connections and operations
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $pdo;
    private static ?PDO $sharedPdo = null;
    
    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }
    
    /**
     * Get shared PDO connection (singleton per HTTP request)
     */
    public static function getSharedPdo(): PDO {
        $instance = new self();
        return $instance->connect();
    }

    /**
     * Reset shared connection (e.g. for testing or long-running workers)
     */
    public static function resetConnection(): void {
        self::$sharedPdo = null;
    }

    /**
     * Create database connection (reuses shared connection across models)
     */
    public function connect() {
        if (self::$sharedPdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    PDO::ATTR_PERSISTENT => false
                ];
                
                self::$sharedPdo = new PDO($dsn, $this->username, $this->password, $options);
                
                // Extra safety: Execute SET NAMES after connection
                self::$sharedPdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$sharedPdo->exec("SET CHARACTER SET utf8mb4");
                
            } catch (PDOException $e) {
                error_log("Database::connect - Connection failed: " . $e->getMessage());
                if (DEBUG_MODE) {
                    die("Database connection failed: " . $e->getMessage());
                } else {
                    die("Database connection failed. Please try again later.");
                }
            }
        }
        
        $this->pdo = self::$sharedPdo;
        return $this->pdo;
    }
    
    /**
     * Get PDO instance
     */
    public function getPdo() {
        return $this->connect();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connect()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connect()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connect()->rollBack();
    }
    
    /**
     * Execute a prepared statement
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connect()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database::execute - Query error: " . $e->getMessage() . " | SQL: " . $sql);
            if (DEBUG_MODE) {
                throw new Exception("Database error: " . $e->getMessage() . " SQL: " . $sql);
            } else {
                throw new Exception("Database error occurred");
            }
        }
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId() {
        return $this->connect()->lastInsertId();
    }
    
    /**
     * Get row count from last statement
     */
    public function rowCount($stmt) {
        return $stmt->rowCount();
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
    }
}