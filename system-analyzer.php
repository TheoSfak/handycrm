<?php
/**
 * HandyCRM System Analyzer & Bug Detector
 * Comprehensive testing and organization tool
 * Version: 1.0.0
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes

class SystemAnalyzer {
    private $issues = [];
    private $stats = [];
    private $baseDir;
    private $currentVersion = '1.6.0';
    
    // Directories to scan
    private $scanDirs = ['classes', 'controllers', 'models', 'views', 'helpers', 'api', 'migrations'];
    
    // File extensions to analyze
    private $phpExtensions = ['php'];
    private $allExtensions = ['php', 'js', 'sql', 'md'];
    
    public function __construct() {
        $this->baseDir = __DIR__;
        $this->stats = [
            'files_scanned' => 0,
            'total_issues' => 0,
            'critical' => 0,
            'warning' => 0,
            'notice' => 0,
            'info' => 0
        ];
    }
    
    public function runFullAnalysis() {
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║          HandyCRM System Analyzer v1.0.0                      ║\n";
        echo "║          Comprehensive Bug Detection & Organization           ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";
        
        echo "Starting comprehensive analysis...\n\n";
        
        // Run all checks
        $this->checkVersionConsistency();
        $this->checkDatabaseConnection();
        $this->scanCodeForIssues();
        $this->checkConfigurationFiles();
        $this->checkDatabaseSchema();
        $this->checkMigrations();
        $this->checkSecurityIssues();
        $this->checkTODOsAndFIXMEs();
        $this->checkDeprecatedCode();
        $this->checkRoutingIntegrity();
        $this->checkTranslations();
        $this->checkFileStructure();
        $this->checkPermissionsSetup();
        $this->checkBackupFiles();
        $this->checkDebugFiles();
        
        // Generate report
        $this->generateReport();
    }
    
    private function addIssue($category, $severity, $file, $line, $message, $suggestion = '') {
        $this->issues[] = [
            'category' => $category,
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
            'message' => $message,
            'suggestion' => $suggestion
        ];
        $this->stats['total_issues']++;
        $this->stats[strtolower($severity)]++;
    }
    
    private function checkVersionConsistency() {
        echo "🔍 Checking version consistency...\n";
        
        $versionFiles = [
            'VERSION' => $this->currentVersion,
            'config/config.php' => null,
            'config/config.example.php' => null
        ];
        
        // Check VERSION file
        if (file_exists($this->baseDir . '/VERSION')) {
            $version = trim(file_get_contents($this->baseDir . '/VERSION'));
            if ($version !== $this->currentVersion) {
                $this->addIssue('Version', 'CRITICAL', 'VERSION', 1, 
                    "VERSION file shows $version but should be $this->currentVersion",
                    "Update VERSION file to $this->currentVersion");
            }
        }
        
        // Check config files for APP_VERSION
        foreach (['config/config.php', 'config/config.example.php'] as $configFile) {
            $path = $this->baseDir . '/' . $configFile;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                if (preg_match("/define\('APP_VERSION',\s*'([^']+)'/", $content, $matches)) {
                    if ($matches[1] !== $this->currentVersion) {
                        $this->addIssue('Version', 'CRITICAL', $configFile, 0,
                            "APP_VERSION is {$matches[1]} but should be $this->currentVersion",
                            "Update APP_VERSION to '$this->currentVersion'");
                    }
                }
            }
        }
        
        // Check for hardcoded old versions in code
        $this->scanForOldVersions();
    }
    
    private function scanForOldVersions() {
        $oldVersions = ['1.0.0', '1.1.0', '1.2.0', '1.2.5', '1.3.0', '1.3.5', '1.3.6', '1.3.8', '1.4.0', '1.5.0'];
        
        foreach ($this->scanDirs as $dir) {
            $path = $this->baseDir . '/' . $dir;
            if (!is_dir($path)) continue;
            
            $files = $this->getPhpFiles($path);
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $lines = explode("\n", $content);
                
                foreach ($lines as $lineNum => $line) {
                    // Skip version comments in docblocks (those are for reference)
                    if (preg_match('/@version|CHANGELOG|migrate_|\.md|\.sql/', $line)) {
                        continue;
                    }
                    
                    foreach ($oldVersions as $oldVer) {
                        if (stripos($line, "v$oldVer") !== false || stripos($line, "'$oldVer'") !== false) {
                            $this->addIssue('Version', 'WARNING', str_replace($this->baseDir . '/', '', $file), $lineNum + 1,
                                "Hardcoded old version reference: $oldVer",
                                "Review if this needs updating to $this->currentVersion");
                        }
                    }
                }
            }
        }
    }
    
    private function checkDatabaseConnection() {
        echo "🔍 Checking database connectivity...\n";
        
        $configPath = $this->baseDir . '/config/config.php';
        if (!file_exists($configPath)) {
            $this->addIssue('Database', 'CRITICAL', 'config/config.php', 0,
                "Config file not found",
                "Copy config.example.php to config.php and configure database");
            return;
        }
        
        require_once $configPath;
        
        if (!defined('DB_HOST') || !defined('DB_NAME')) {
            $this->addIssue('Database', 'CRITICAL', 'config/config.php', 0,
                "Database constants not defined",
                "Define DB_HOST, DB_NAME, DB_USER, DB_PASS in config.php");
            return;
        }
        
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            echo "  ✓ Database connection successful\n";
        } catch (PDOException $e) {
            $this->addIssue('Database', 'CRITICAL', 'config/config.php', 0,
                "Cannot connect to database: " . $e->getMessage(),
                "Check database credentials and ensure MySQL is running");
        }
    }
    
    private function scanCodeForIssues() {
        echo "🔍 Scanning code for common issues...\n";
        
        foreach ($this->scanDirs as $dir) {
            $path = $this->baseDir . '/' . $dir;
            if (!is_dir($path)) continue;
            
            $files = $this->getPhpFiles($path);
            foreach ($files as $file) {
                $this->stats['files_scanned']++;
                $this->analyzePhpFile($file);
            }
        }
    }
    
    private function analyzePhpFile($filePath) {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $fileName = str_replace($this->baseDir . '/', '', $filePath);
        
        foreach ($lines as $lineNum => $line) {
            $lineNumber = $lineNum + 1;
            
            // Check for var_dump, print_r, die, exit in production code
            if (preg_match('/\b(var_dump|print_r)\s*\(/', $line) && !preg_match('/\/\/.*var_dump/', $line)) {
                $this->addIssue('Code Quality', 'WARNING', $fileName, $lineNumber,
                    "Debug function found: " . trim($line),
                    "Remove debug output or comment it out");
            }
            
            // Check for die() or exit() without proper error handling
            if (preg_match('/\b(die|exit)\s*\(/', $line) && !preg_match('/\/\//', $line)) {
                if (!preg_match('/error|exception|fail/i', $line)) {
                    $this->addIssue('Code Quality', 'NOTICE', $fileName, $lineNumber,
                        "Abrupt termination without error context",
                        "Consider proper error handling instead of die/exit");
                }
            }
            
            // Check for SQL injection vulnerabilities
            if (preg_match('/\$_(GET|POST|REQUEST)\[.*\]/', $line) && preg_match('/SELECT|INSERT|UPDATE|DELETE/', $line)) {
                if (!preg_match('/prepare|bindParam|bindValue/', $content)) {
                    $this->addIssue('Security', 'CRITICAL', $fileName, $lineNumber,
                        "Potential SQL injection vulnerability",
                        "Use prepared statements with PDO");
                }
            }
            
            // Check for XSS vulnerabilities
            if (preg_match('/echo\s+\$_(GET|POST|REQUEST|COOKIE)/', $line)) {
                if (!preg_match('/htmlspecialchars|htmlentities/', $line)) {
                    $this->addIssue('Security', 'CRITICAL', $fileName, $lineNumber,
                        "Potential XSS vulnerability - unescaped output",
                        "Use htmlspecialchars() for user input output");
                }
            }
            
            // Check for deprecated PHP functions
            $deprecated = ['mysql_query', 'mysql_connect', 'ereg', 'split', 'session_register'];
            foreach ($deprecated as $func) {
                if (preg_match('/\b' . preg_quote($func) . '\s*\(/', $line)) {
                    $this->addIssue('Code Quality', 'CRITICAL', $fileName, $lineNumber,
                        "Deprecated function: $func",
                        "Use modern alternatives (PDO, preg_*, explode)");
                }
            }
            
            // Check for missing error handling in try-catch
            if (preg_match('/catch\s*\(/', $line)) {
                $catchBlock = '';
                for ($i = $lineNum; $i < min($lineNum + 10, count($lines)); $i++) {
                    $catchBlock .= $lines[$i];
                }
                if (!preg_match('/error_log|log|Logger|throw/', $catchBlock)) {
                    $this->addIssue('Code Quality', 'WARNING', $fileName, $lineNumber,
                        "Empty or silent catch block",
                        "Log errors or handle exceptions properly");
                }
            }
            
            // Check for hardcoded credentials
            if (preg_match('/(password|passwd|pwd)\s*=\s*["\'](?!.*(\$|{))/', $line, $matches)) {
                if (!preg_match('/example|sample|test|dummy|placeholder/i', $line)) {
                    $this->addIssue('Security', 'CRITICAL', $fileName, $lineNumber,
                        "Potential hardcoded password",
                        "Use environment variables or config files");
                }
            }
            
            // Check for TODO/FIXME/HACK
            if (preg_match('/(TODO|FIXME|HACK|XXX|BUG)[\s:]/i', $line, $matches)) {
                $this->addIssue('Code Quality', 'NOTICE', $fileName, $lineNumber,
                    "Code marker: " . $matches[1] . " - " . trim($line),
                    "Address this marker or remove if completed");
            }
        }
    }
    
    private function checkConfigurationFiles() {
        echo "🔍 Checking configuration files...\n";
        
        // Check if config.php exists
        if (!file_exists($this->baseDir . '/config/config.php')) {
            $this->addIssue('Configuration', 'CRITICAL', 'config/config.php', 0,
                "Main configuration file missing",
                "Copy config.example.php to config.php");
        }
        
        // Check for .env or sensitive files in version control
        if (file_exists($this->baseDir . '/.env')) {
            if (file_exists($this->baseDir . '/.gitignore')) {
                $gitignore = file_get_contents($this->baseDir . '/.gitignore');
                if (stripos($gitignore, '.env') === false) {
                    $this->addIssue('Security', 'CRITICAL', '.gitignore', 0,
                        ".env file not in .gitignore",
                        "Add .env to .gitignore to prevent credential leaks");
                }
            }
        }
        
        // Check for backup config files
        $backupFiles = glob($this->baseDir . '/config/*.backup');
        foreach ($backupFiles as $backup) {
            $this->addIssue('Configuration', 'NOTICE', str_replace($this->baseDir . '/', '', $backup), 0,
                "Backup config file found",
                "Consider removing old backup files from production");
        }
    }
    
    private function checkDatabaseSchema() {
        echo "🔍 Checking database schema...\n";
        
        if (!defined('DB_HOST')) return;
        
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Get all tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tables)) {
                $this->addIssue('Database', 'CRITICAL', 'Database', 0,
                    "Database has no tables",
                    "Run installation migrations");
                return;
            }
            
            // Check for required core tables
            $requiredTables = [
                'users', 'customers', 'projects', 'tasks', 'appointments',
                'roles', 'permissions', 'user_roles', 'role_permissions',
                'settings', 'materials_catalog'
            ];
            
            foreach ($requiredTables as $table) {
                if (!in_array($table, $tables)) {
                    $this->addIssue('Database', 'CRITICAL', 'Database Schema', 0,
                        "Required table '$table' is missing",
                        "Run migration scripts to create missing tables");
                }
            }
            
            // Check for orphaned references
            $this->checkForeignKeyIntegrity($pdo);
            
            echo "  ✓ Found " . count($tables) . " tables\n";
            
        } catch (PDOException $e) {
            // Already reported in checkDatabaseConnection
        }
    }
    
    private function checkForeignKeyIntegrity($pdo) {
        try {
            // Check for orphaned project tasks
            $stmt = $pdo->query("
                SELECT COUNT(*) as count 
                FROM tasks t 
                LEFT JOIN projects p ON t.project_id = p.id 
                WHERE t.project_id IS NOT NULL AND p.id IS NULL
            ");
            $orphaned = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($orphaned['count'] > 0) {
                $this->addIssue('Database', 'WARNING', 'tasks table', 0,
                    "Found {$orphaned['count']} orphaned tasks (project_id references non-existent projects)",
                    "Clean up orphaned records or restore missing projects");
            }
            
            // Check for orphaned user_roles
            $stmt = $pdo->query("
                SELECT COUNT(*) as count 
                FROM user_roles ur 
                LEFT JOIN users u ON ur.user_id = u.id 
                WHERE u.id IS NULL
            ");
            $orphaned = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($orphaned['count'] > 0) {
                $this->addIssue('Database', 'WARNING', 'user_roles table', 0,
                    "Found {$orphaned['count']} orphaned user roles",
                    "Clean up user_roles for deleted users");
            }
            
        } catch (PDOException $e) {
            // Some tables might not exist yet
        }
    }
    
    private function checkMigrations() {
        echo "🔍 Checking migration files...\n";
        
        $migrationsDir = $this->baseDir . '/migrations';
        if (!is_dir($migrationsDir)) {
            $this->addIssue('Migrations', 'WARNING', 'migrations/', 0,
                "Migrations directory not found",
                "Create migrations directory for database versioning");
            return;
        }
        
        $migrations = glob($migrationsDir . '/*.sql');
        echo "  ✓ Found " . count($migrations) . " migration files\n";
        
        // Check for migration naming consistency
        foreach ($migrations as $migration) {
            $filename = basename($migration);
            if (!preg_match('/^(migrate_|v\d+\.\d+\.\d+)/', $filename)) {
                $this->addIssue('Migrations', 'NOTICE', 'migrations/' . $filename, 0,
                    "Migration file doesn't follow naming convention",
                    "Use format: migrate_x.x.x_to_y.y.y.sql or vX.X.X_*.sql");
            }
        }
    }
    
    private function checkSecurityIssues() {
        echo "🔍 Checking security configurations...\n";
        
        // Check .htaccess for uploads directory
        if (is_dir($this->baseDir . '/uploads')) {
            if (!file_exists($this->baseDir . '/uploads/.htaccess')) {
                $this->addIssue('Security', 'CRITICAL', 'uploads/', 0,
                    "No .htaccess protection in uploads directory",
                    "Add .htaccess to prevent direct PHP execution in uploads");
            }
        }
        
        // Check for exposed sensitive files
        $sensitiveFiles = [
            '.git/config' => 'Git configuration exposed',
            'composer.json' => 'Composer file accessible',
            'config/config.php' => 'Config file may be accessible'
        ];
        
        foreach ($sensitiveFiles as $file => $message) {
            if (file_exists($this->baseDir . '/' . $file)) {
                $this->addIssue('Security', 'WARNING', $file, 0,
                    $message,
                    "Ensure .htaccess or web server config blocks access");
            }
        }
        
        // Check for session security in config
        if (file_exists($this->baseDir . '/config/config.php')) {
            $config = file_get_contents($this->baseDir . '/config/config.php');
            
            if (stripos($config, 'session.cookie_httponly') === false) {
                $this->addIssue('Security', 'WARNING', 'config/config.php', 0,
                    "HttpOnly cookie flag not set",
                    "Set session.cookie_httponly = 1 for XSS protection");
            }
            
            if (stripos($config, 'session.cookie_secure') === false) {
                $this->addIssue('Security', 'NOTICE', 'config/config.php', 0,
                    "Secure cookie flag not configured",
                    "Set session.cookie_secure = 1 for HTTPS-only cookies");
            }
        }
    }
    
    private function checkTODOsAndFIXMEs() {
        echo "🔍 Collecting TODO and FIXME markers...\n";
        // Already handled in analyzePhpFile
    }
    
    private function checkDeprecatedCode() {
        echo "🔍 Checking for deprecated code patterns...\n";
        
        // Check for old _OLD or _BACKUP files
        foreach ($this->scanDirs as $dir) {
            $path = $this->baseDir . '/' . $dir;
            if (!is_dir($path)) continue;
            
            $oldFiles = glob($path . '/*_OLD.php');
            $backupFiles = glob($path . '/*.backup');
            
            foreach (array_merge($oldFiles, $backupFiles) as $file) {
                $this->addIssue('Code Quality', 'NOTICE', str_replace($this->baseDir . '/', '', $file), 0,
                    "Old/backup file found in codebase",
                    "Remove deprecated files or move to separate backup directory");
            }
        }
    }
    
    private function checkRoutingIntegrity() {
        echo "🔍 Checking routing integrity...\n";
        
        $routerFile = $this->baseDir . '/router.php';
        if (!file_exists($routerFile)) {
            $this->addIssue('Routing', 'CRITICAL', 'router.php', 0,
                "Main router file not found",
                "Router is required for application to function");
            return;
        }
        
        // Check for router class
        if (file_exists($this->baseDir . '/classes/Router.php')) {
            echo "  ✓ Router class found\n";
        } else {
            $this->addIssue('Routing', 'CRITICAL', 'classes/Router.php', 0,
                "Router class file not found",
                "Router class is required");
        }
    }
    
    private function checkTranslations() {
        echo "🔍 Checking translation files...\n";
        
        $langDir = $this->baseDir . '/languages';
        if (!is_dir($langDir)) {
            $this->addIssue('Translations', 'WARNING', 'languages/', 0,
                "Languages directory not found",
                "Create language files for internationalization");
            return;
        }
        
        $languages = glob($langDir . '/*.php');
        if (empty($languages)) {
            $this->addIssue('Translations', 'WARNING', 'languages/', 0,
                "No language files found",
                "Add at least one language file (e.g., el.php, en.php)");
        } else {
            echo "  ✓ Found " . count($languages) . " language files\n";
        }
    }
    
    private function checkFileStructure() {
        echo "🔍 Checking file structure...\n";
        
        $requiredDirs = [
            'classes' => 'Core classes directory',
            'controllers' => 'Controllers directory',
            'models' => 'Models directory',
            'views' => 'Views directory',
            'config' => 'Configuration directory',
            'uploads' => 'User uploads directory'
        ];
        
        foreach ($requiredDirs as $dir => $description) {
            if (!is_dir($this->baseDir . '/' . $dir)) {
                $this->addIssue('Structure', 'CRITICAL', $dir . '/', 0,
                    "$description is missing",
                    "Create required directory: $dir");
            }
        }
        
        // Check for proper permissions on uploads
        if (is_dir($this->baseDir . '/uploads')) {
            if (!is_writable($this->baseDir . '/uploads')) {
                $this->addIssue('Structure', 'CRITICAL', 'uploads/', 0,
                    "Uploads directory is not writable",
                    "Set proper permissions: chmod 755 or 775");
            }
        }
    }
    
    private function checkPermissionsSetup() {
        echo "🔍 Checking RBAC permissions setup...\n";
        
        if (!defined('DB_HOST')) return;
        
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Check if roles table has data
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
            $rolesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($rolesCount == 0) {
                $this->addIssue('RBAC', 'CRITICAL', 'Database: roles', 0,
                    "No roles defined in system",
                    "Run RBAC setup SQL to create default roles");
            }
            
            // Check if permissions table has data
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM permissions");
            $permsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($permsCount == 0) {
                $this->addIssue('RBAC', 'CRITICAL', 'Database: permissions', 0,
                    "No permissions defined in system",
                    "Run RBAC setup SQL to create default permissions");
            }
            
            // Check for users without roles
            $stmt = $pdo->query("
                SELECT COUNT(*) as count 
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id 
                WHERE ur.user_id IS NULL AND u.status = 'active'
            ");
            $noRoleUsers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($noRoleUsers > 0) {
                $this->addIssue('RBAC', 'WARNING', 'Database: user_roles', 0,
                    "$noRoleUsers active users have no assigned roles",
                    "Assign appropriate roles to all users");
            }
            
        } catch (PDOException $e) {
            // Tables might not exist
        }
    }
    
    private function checkBackupFiles() {
        echo "🔍 Checking for backup/temporary files...\n";
        
        $patterns = ['*.backup', '*.bak', '*.tmp', '*~', '*.old', '*_OLD.php'];
        $found = [];
        
        foreach ($patterns as $pattern) {
            $files = glob($this->baseDir . '/' . $pattern);
            $found = array_merge($found, $files);
            
            // Check subdirectories
            foreach ($this->scanDirs as $dir) {
                if (is_dir($this->baseDir . '/' . $dir)) {
                    $subFiles = glob($this->baseDir . '/' . $dir . '/' . $pattern);
                    $found = array_merge($found, $subFiles);
                }
            }
        }
        
        foreach ($found as $file) {
            $this->addIssue('Cleanup', 'NOTICE', str_replace($this->baseDir . '/', '', $file), 0,
                "Backup or temporary file found",
                "Clean up backup files from production environment");
        }
    }
    
    private function checkDebugFiles() {
        echo "🔍 Checking for debug files...\n";
        
        $debugFiles = glob($this->baseDir . '/debug*.php');
        $testFiles = glob($this->baseDir . '/test*.php');
        
        foreach (array_merge($debugFiles, $testFiles) as $file) {
            $filename = basename($file);
            if ($filename !== 'test_index.php') { // Might be intentional
                $this->addIssue('Cleanup', 'WARNING', $filename, 0,
                    "Debug/test file found in root directory",
                    "Remove debug files from production or move to /dev directory");
            }
        }
    }
    
    private function generateReport() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                    ANALYSIS COMPLETE                           ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";
        
        // Summary
        echo "📊 SUMMARY\n";
        echo "─────────────────────────────────────────────────────────────────\n";
        echo "Files Scanned:    {$this->stats['files_scanned']}\n";
        echo "Total Issues:     {$this->stats['total_issues']}\n";
        echo "  🔴 Critical:    {$this->stats['critical']}\n";
        echo "  🟡 Warning:     {$this->stats['warning']}\n";
        echo "  🔵 Notice:      {$this->stats['notice']}\n";
        echo "  ℹ️  Info:        {$this->stats['info']}\n\n";
        
        // Group issues by category
        $byCategory = [];
        foreach ($this->issues as $issue) {
            $byCategory[$issue['category']][] = $issue;
        }
        
        // Sort by severity
        $severityOrder = ['CRITICAL' => 0, 'WARNING' => 1, 'NOTICE' => 2, 'INFO' => 3];
        
        foreach ($byCategory as $category => $issues) {
            usort($issues, function($a, $b) use ($severityOrder) {
                return $severityOrder[$a['severity']] - $severityOrder[$b['severity']];
            });
            
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "📁 $category (" . count($issues) . " issues)\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            foreach ($issues as $idx => $issue) {
                $icon = $this->getSeverityIcon($issue['severity']);
                $num = $idx + 1;
                
                echo "$icon [{$issue['severity']}] Issue #$num\n";
                echo "   File: {$issue['file']}" . ($issue['line'] ? ":{$issue['line']}" : "") . "\n";
                echo "   Problem: {$issue['message']}\n";
                if ($issue['suggestion']) {
                    echo "   💡 Suggestion: {$issue['suggestion']}\n";
                }
                echo "\n";
            }
        }
        
        // Save to file
        $this->saveReportToFile();
        
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║  Report saved to: analysis-report-" . date('Y-m-d-His') . ".txt  ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n";
    }
    
    private function getSeverityIcon($severity) {
        $icons = [
            'CRITICAL' => '🔴',
            'WARNING' => '🟡',
            'NOTICE' => '🔵',
            'INFO' => 'ℹ️'
        ];
        return $icons[$severity] ?? '⚪';
    }
    
    private function saveReportToFile() {
        $filename = $this->baseDir . '/analysis-report-' . date('Y-m-d-His') . '.txt';
        
        $report = "HandyCRM System Analysis Report\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $report .= "Version: $this->currentVersion\n";
        $report .= str_repeat("=", 80) . "\n\n";
        
        $report .= "SUMMARY\n";
        $report .= str_repeat("-", 80) . "\n";
        $report .= "Files Scanned: {$this->stats['files_scanned']}\n";
        $report .= "Total Issues: {$this->stats['total_issues']}\n";
        $report .= "Critical: {$this->stats['critical']}\n";
        $report .= "Warning: {$this->stats['warning']}\n";
        $report .= "Notice: {$this->stats['notice']}\n";
        $report .= "Info: {$this->stats['info']}\n\n";
        
        // Group by category
        $byCategory = [];
        foreach ($this->issues as $issue) {
            $byCategory[$issue['category']][] = $issue;
        }
        
        foreach ($byCategory as $category => $issues) {
            $report .= str_repeat("=", 80) . "\n";
            $report .= "CATEGORY: $category (" . count($issues) . " issues)\n";
            $report .= str_repeat("=", 80) . "\n\n";
            
            foreach ($issues as $idx => $issue) {
                $num = $idx + 1;
                $report .= "[$issue[severity]] Issue #$num\n";
                $report .= "File: $issue[file]" . ($issue['line'] ? ":$issue[line]" : "") . "\n";
                $report .= "Problem: $issue[message]\n";
                if ($issue['suggestion']) {
                    $report .= "Suggestion: $issue[suggestion]\n";
                }
                $report .= "\n";
            }
        }
        
        // Add action plan
        $report .= str_repeat("=", 80) . "\n";
        $report .= "RECOMMENDED ACTION PLAN\n";
        $report .= str_repeat("=", 80) . "\n\n";
        $report .= "1. Address all CRITICAL issues first (security and functionality)\n";
        $report .= "2. Fix WARNING issues (potential bugs and code quality)\n";
        $report .= "3. Review NOTICE items (cleanup and optimization)\n";
        $report .= "4. Consider INFO suggestions for future improvements\n\n";
        
        file_put_contents($filename, $report);
    }
    
    private function getPhpFiles($dir) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
}

// Run the analyzer
$analyzer = new SystemAnalyzer();
$analyzer->runFullAnalysis();
