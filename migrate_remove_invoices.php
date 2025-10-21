<?php
/**
 * Migration: Remove Invoices Module
 * This script:
 * 1. Adds invoiced_at column to projects table
 * 2. Migrates data from invoices to projects
 * 3. Drops invoices and invoice_items tables
 * 
 * Access: https://yourdomain.com/migrate_remove_invoices.php?run=remove_invoices_2025
 */

$SECURITY_KEY = 'remove_invoices_2025';

if (!isset($_GET['run']) || $_GET['run'] !== $SECURITY_KEY) {
    die('Access denied. Use: ?run=' . $SECURITY_KEY);
}

require_once 'config/config.php';
require_once 'classes/Database.php';

// Initialize database connection
$db = new Database();

echo "<!DOCTYPE html>
<html lang='el'>
<head>
    <meta charset='UTF-8'>
    <title>Migration: Remove Invoices</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .step { padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; background: #f8f9fa; border-radius: 4px; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Migration: Remove Invoices Module</h1>
        <hr>";

try {
    // STEP 1: Add invoiced_at column to projects
    echo "<div class='step'><h3>Step 1: Add invoiced_at column to projects</h3>";
    
    $checkColumn = $db->fetchOne("SHOW COLUMNS FROM projects LIKE 'invoiced_at'");
    
    if (!$checkColumn) {
        $sql = "ALTER TABLE projects ADD COLUMN invoiced_at DATETIME NULL AFTER completion_date";
        $db->execute($sql);
        echo "<p>✅ Column 'invoiced_at' added successfully</p>";
    } else {
        echo "<p>⚠️ Column 'invoiced_at' already exists</p>";
    }
    echo "</div>";
    
    // STEP 2: Migrate invoice data to projects
    echo "<div class='step'><h3>Step 2: Migrate invoice data to projects</h3>";
    
    $invoices = $db->fetchAll("SELECT project_id, paid_date FROM invoices WHERE project_id IS NOT NULL AND paid_date IS NOT NULL");
    
    if (!empty($invoices)) {
        $count = 0;
        foreach ($invoices as $inv) {
            // Update project with invoiced_at from invoice paid_date
            $sql = "UPDATE projects SET invoiced_at = ? WHERE id = ? AND invoiced_at IS NULL";
            $db->execute($sql, [$inv['paid_date'], $inv['project_id']]);
            $count++;
        }
        echo "<p>✅ Migrated {$count} invoice dates to projects</p>";
    } else {
        echo "<p>⚠️ No invoices with paid dates found</p>";
    }
    echo "</div>";
    
    // STEP 3: Drop invoice_items table
    echo "<div class='step'><h3>Step 3: Drop invoice_items table</h3>";
    
    $checkTable = $db->fetchOne("SHOW TABLES LIKE 'invoice_items'");
    
    if ($checkTable) {
        $sql = "DROP TABLE IF EXISTS invoice_items";
        $db->execute($sql);
        echo "<p>✅ Table 'invoice_items' dropped successfully</p>";
    } else {
        echo "<p>⚠️ Table 'invoice_items' does not exist</p>";
    }
    echo "</div>";
    
    // STEP 4: Drop invoices table
    echo "<div class='step'><h3>Step 4: Drop invoices table</h3>";
    
    $checkTable = $db->fetchOne("SHOW TABLES LIKE 'invoices'");
    
    if ($checkTable) {
        $sql = "DROP TABLE IF EXISTS invoices";
        $db->execute($sql);
        echo "<p>✅ Table 'invoices' dropped successfully</p>";
    } else {
        echo "<p>⚠️ Table 'invoices' does not exist</p>";
    }
    echo "</div>";
    
    // STEP 5: Verify
    echo "<div class='step success'><h3>✅ Migration Completed!</h3>";
    echo "<p><strong>Summary:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Projects table now has 'invoiced_at' column</li>";
    echo "<li>✅ Invoice data migrated to projects</li>";
    echo "<li>✅ Invoices module tables removed</li>";
    echo "</ul>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Test the application thoroughly</li>";
    echo "<li>Check Reports → Revenue (should show invoiced projects)</li>";
    echo "<li>Verify customer statistics</li>";
    echo "<li>DELETE THIS FILE for security!</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='step error'>";
    echo "<h3>❌ Migration Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<hr>
      <div class='step warning'>
        <strong>⚠️ DELETE THIS FILE IMMEDIATELY AFTER MIGRATION!</strong><br>
        <small>This file has unrestricted database access.</small>
      </div>
    </div>
</body>
</html>";
?>
