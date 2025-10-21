<?php
/**
 * HandyCRM v1.3.5 - Auto-Fix Migration Script
 * 
 * This script automatically fixes all migration issues for v1.3.5 upgrade
 * Run this file once via browser: http://yourdomain.com/auto_fix_1.3.5.php
 * 
 * Fixes:
 * 1. Adds missing 'paid_by' column to task_labor table
 * 2. Updates users.role ENUM to include 'supervisor'
 * 3. Adds 'is_active' column to users table
 * 4. Creates necessary indexes for performance
 * 
 * SAFE TO RUN MULTIPLE TIMES - All operations are idempotent
 * 
 * @version 1.3.5
 * @date 2025-10-21
 */

// Security: Delete this file after running!
$SECURITY_KEY = 'handycrm_fix_135'; // Change this to run the script

// Configuration
define('AUTO_DELETE_AFTER_RUN', false); // Set to true to auto-delete this file after successful run

?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HandyCRM v1.3.5 - Auto-Fix Migration</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 800px;
            width: 100%;
            padding: 30px;
        }
        h1 {
            color: #667eea;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .version {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            font-size: 14px;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .step {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        .step h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
        }
        .step-result {
            font-family: monospace;
            font-size: 13px;
            color: #666;
        }
        .icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 50%;
            margin-right: 8px;
            font-weight: bold;
        }
        .icon.success { background: #28a745; color: white; }
        .icon.error { background: #dc3545; color: white; }
        .icon.warning { background: #ffc107; color: #333; }
        .icon.info { background: #17a2b8; color: white; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #5568d3;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
        .security-notice {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .security-notice strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 HandyCRM Auto-Fix Migration</h1>
        <div class="version">Version 1.3.5 | October 21, 2025</div>

        <?php
        // Check if we should run the fix
        if (!isset($_GET['run']) || $_GET['run'] !== $SECURITY_KEY) {
            ?>
            <div class="security-notice">
                <strong>⚠️ Security Notice:</strong> This script will modify your database. 
                Make sure you have a backup before proceeding!
            </div>

            <div class="status info">
                <span class="icon info">i</span>
                <strong>Ready to fix migration issues</strong><br>
                This script will automatically:<br>
                <ul style="margin: 10px 0 0 30px;">
                    <li>Add missing 'paid_by' column to task_labor</li>
                    <li>Update users.role ENUM to include 'supervisor'</li>
                    <li>Add 'is_active' column to users table</li>
                    <li>Create performance indexes</li>
                </ul>
            </div>

            <a href="?run=<?php echo $SECURITY_KEY; ?>" class="btn">▶️ Run Auto-Fix</a>

            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px; font-size: 13px;">
                <strong>Before running:</strong>
                <ol style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li>Make sure you have a database backup</li>
                    <li>Verify that config/config.php has correct database credentials</li>
                    <li>This script is safe to run multiple times (idempotent)</li>
                </ol>
            </div>
            <?php
            exit;
        }

        // Include config
        $configFile = __DIR__ . '/config/config.php';
        if (!file_exists($configFile)) {
            echo '<div class="status error">❌ Error: config/config.php not found!</div>';
            exit;
        }

        require_once $configFile;
        require_once __DIR__ . '/classes/Database.php';

        echo '<div class="status info"><span class="icon info">▶</span> Starting migration fix...</div>';

        try {
            $db = new Database();
            $pdo = $db->connect();
            
            $allSuccess = true;
            $results = [];

            // Step 1: Check and add paid_by column
            echo '<div class="step">';
            echo '<h3>Step 1: Add paid_by column to task_labor</h3>';
            
            $checkColumn = $pdo->query("
                SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'task_labor' 
                AND COLUMN_NAME = 'paid_by'
            ")->fetch(PDO::FETCH_ASSOC);

            if ($checkColumn['count'] == 0) {
                $pdo->exec("
                    ALTER TABLE task_labor 
                    ADD COLUMN paid_by INT UNSIGNED NULL 
                    COMMENT 'User ID who marked this as paid'
                ");
                echo '<div class="status success"><span class="icon success">✓</span> Column <code>paid_by</code> added successfully!</div>';
                $results[] = 'paid_by column ADDED';
            } else {
                echo '<div class="status warning"><span class="icon warning">!</span> Column <code>paid_by</code> already exists (OK)</div>';
                $results[] = 'paid_by column already exists';
            }
            echo '</div>';

            // Step 2: Add foreign key
            echo '<div class="step">';
            echo '<h3>Step 2: Add foreign key constraint</h3>';
            
            $checkFK = $pdo->query("
                SELECT COUNT(*) as count 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE CONSTRAINT_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'task_labor' 
                AND CONSTRAINT_NAME = 'fk_task_labor_paid_by'
            ")->fetch(PDO::FETCH_ASSOC);

            if ($checkFK['count'] == 0 && $checkColumn['count'] > 0) {
                try {
                    $pdo->exec("
                        ALTER TABLE task_labor 
                        ADD CONSTRAINT fk_task_labor_paid_by 
                        FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL
                    ");
                    echo '<div class="status success"><span class="icon success">✓</span> Foreign key <code>fk_task_labor_paid_by</code> added successfully!</div>';
                    $results[] = 'Foreign key ADDED';
                } catch (Exception $e) {
                    echo '<div class="status warning"><span class="icon warning">!</span> Foreign key creation skipped: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    $results[] = 'Foreign key SKIPPED (may already exist)';
                }
            } else {
                echo '<div class="status warning"><span class="icon warning">!</span> Foreign key already exists or column missing (OK)</div>';
                $results[] = 'Foreign key already exists';
            }
            echo '</div>';

            // Step 3: Update users role ENUM
            echo '<div class="step">';
            echo '<h3>Step 3: Update users.role ENUM (add supervisor)</h3>';
            
            try {
                $pdo->exec("
                    ALTER TABLE users 
                    MODIFY COLUMN role ENUM('admin', 'supervisor', 'technician', 'assistant') 
                    DEFAULT 'technician' 
                    COMMENT 'User role: admin (full access), supervisor (projects & materials), technician (own profile), assistant (own profile)'
                ");
                echo '<div class="status success"><span class="icon success">✓</span> Users role ENUM updated successfully!</div>';
                $results[] = 'Role ENUM updated with supervisor';
            } catch (Exception $e) {
                echo '<div class="status warning"><span class="icon warning">!</span> Role ENUM update: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $results[] = 'Role ENUM update attempted';
            }
            echo '</div>';

            // Step 4: Add is_active column to users
            echo '<div class="step">';
            echo '<h3>Step 4: Add is_active column to users</h3>';
            
            $checkActive = $pdo->query("
                SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'users' 
                AND COLUMN_NAME = 'is_active'
            ")->fetch(PDO::FETCH_ASSOC);

            if ($checkActive['count'] == 0) {
                $pdo->exec("
                    ALTER TABLE users 
                    ADD COLUMN is_active TINYINT(1) DEFAULT 1 
                    COMMENT 'Whether user account is active'
                ");
                echo '<div class="status success"><span class="icon success">✓</span> Column <code>is_active</code> added successfully!</div>';
                
                // Set all existing users as active
                $pdo->exec("UPDATE users SET is_active = 1 WHERE is_active IS NULL");
                $results[] = 'is_active column ADDED';
            } else {
                echo '<div class="status warning"><span class="icon warning">!</span> Column <code>is_active</code> already exists (OK)</div>';
                $results[] = 'is_active column already exists';
            }
            echo '</div>';

            // Step 5: Create indexes
            echo '<div class="step">';
            echo '<h3>Step 5: Create performance indexes</h3>';
            
            $indexes = [
                'idx_task_labor_paid_at' => "CREATE INDEX idx_task_labor_paid_at ON task_labor (paid_at)",
                'idx_task_labor_tech_paid' => "CREATE INDEX idx_task_labor_tech_paid ON task_labor (technician_id, paid_at)"
            ];

            foreach ($indexes as $indexName => $sql) {
                $checkIndex = $pdo->query("
                    SELECT COUNT(*) as count 
                    FROM INFORMATION_SCHEMA.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'task_labor' 
                    AND INDEX_NAME = '$indexName'
                ")->fetch(PDO::FETCH_ASSOC);

                if ($checkIndex['count'] == 0) {
                    try {
                        $pdo->exec($sql);
                        echo '<div class="status success"><span class="icon success">✓</span> Index <code>' . $indexName . '</code> created!</div>';
                        $results[] = $indexName . ' CREATED';
                    } catch (Exception $e) {
                        echo '<div class="status warning"><span class="icon warning">!</span> Index <code>' . $indexName . '</code>: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        $results[] = $indexName . ' SKIPPED';
                    }
                } else {
                    echo '<div class="status warning"><span class="icon warning">!</span> Index <code>' . $indexName . '</code> already exists (OK)</div>';
                    $results[] = $indexName . ' already exists';
                }
            }
            echo '</div>';

            // Step 6: Record migration
            echo '<div class="step">';
            echo '<h3>Step 6: Record migration in database</h3>';
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO migrations (filename, executed_at) 
                    VALUES ('auto_fix_1.3.5.php', NOW())
                    ON DUPLICATE KEY UPDATE executed_at = NOW()
                ");
                $stmt->execute();
                echo '<div class="status success"><span class="icon success">✓</span> Migration recorded in database!</div>';
            } catch (Exception $e) {
                echo '<div class="status warning"><span class="icon warning">!</span> Migration recording: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            echo '</div>';

            // Final summary
            echo '<div class="status success" style="margin-top: 30px; padding: 20px;">';
            echo '<h3 style="margin: 0 0 15px 0; font-size: 20px;">🎉 Migration Complete!</h3>';
            echo '<strong>Summary of changes:</strong>';
            echo '<ul style="margin: 10px 0 0 20px; line-height: 1.8;">';
            foreach ($results as $result) {
                echo '<li>' . htmlspecialchars($result) . '</li>';
            }
            echo '</ul>';
            echo '</div>';

            echo '<div class="status info" style="margin-top: 20px;">';
            echo '<span class="icon info">i</span> <strong>Next Steps:</strong><br>';
            echo '<ol style="margin: 10px 0 0 30px; line-height: 1.8;">';
            echo '<li>Clear your browser cache (Ctrl+F5)</li>';
            echo '<li>Go to your payments page and verify it works</li>';
            echo '<li><strong>DELETE THIS FILE</strong> for security: <code>auto_fix_1.3.5.php</code></li>';
            echo '</ol>';
            echo '</div>';

            // Auto-delete option
            if (AUTO_DELETE_AFTER_RUN) {
                if (unlink(__FILE__)) {
                    echo '<div class="status success"><span class="icon success">✓</span> This file has been automatically deleted for security.</div>';
                } else {
                    echo '<div class="status warning"><span class="icon warning">!</span> Could not auto-delete this file. Please delete it manually.</div>';
                }
            } else {
                echo '<div class="status warning" style="margin-top: 20px;">';
                echo '<span class="icon warning">⚠</span> <strong>SECURITY WARNING:</strong> Please delete this file manually: <code>auto_fix_1.3.5.php</code>';
                echo '</div>';
            }

        } catch (Exception $e) {
            echo '<div class="status error">';
            echo '<span class="icon error">✗</span> <strong>Fatal Error:</strong> ' . htmlspecialchars($e->getMessage());
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>

    </div>
</body>
</html>
