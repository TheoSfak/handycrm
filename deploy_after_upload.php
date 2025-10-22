<?php
/**
 * Post-Upload Deployment Script
 * Run this AFTER uploading files via FTP
 * Access: https://1stop.gr/deploy_after_upload.php?run=deploy_2025
 */

$SECURITY_KEY = 'deploy_2025';

if (!isset($_GET['run']) || $_GET['run'] !== $SECURITY_KEY) {
    die('Access denied. Use: ?run=' . $SECURITY_KEY);
}

// Initialize error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'config/config.php';
    require_once 'classes/Database.php';
    $db = new Database();
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

echo "<!DOCTYPE html>
<html lang='el'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Deployment Script</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .step { 
            padding: 20px; 
            margin: 15px 0; 
            border-left: 5px solid #667eea; 
            background: #f8f9fa; 
            border-radius: 6px;
            transition: all 0.3s;
        }
        .step:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .step h3 {
            margin-top: 0;
            color: #333;
        }
        .success { 
            border-left-color: #28a745; 
            background: #d4edda; 
        }
        .error { 
            border-left-color: #dc3545; 
            background: #f8d7da; 
        }
        .warning { 
            border-left-color: #ffc107; 
            background: #fff3cd; 
        }
        .info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
        }
        code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .progress {
            margin: 10px 0;
            padding: 10px;
            background: #e9ecef;
            border-radius: 4px;
        }
        ul {
            margin: 10px 0;
            padding-left: 25px;
        }
        li {
            margin: 5px 0;
        }
        .cleanup-list {
            background: #fff3cd;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        hr {
            border: none;
            border-top: 2px solid #e9ecef;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>
            <span style='font-size: 40px;'>🚀</span>
            Post-Upload Deployment Script
        </h1>
        <p style='color: #666; margin-top: -10px;'>Automated deployment for HandyCRM</p>
        <hr>";

$errors = [];
$warnings = [];
$completed = [];

// ==========================================
// STEP 1: Check uploaded files
// ==========================================
echo "<div class='step info'>
        <h3>📋 Step 1: Checking Uploaded Files</h3>";

$requiredFiles = [
    'index.php',
    'controllers/ProjectController.php',
    'controllers/ReportsController.php',
    'views/includes/header.php',
    'views/dashboard/index.php',
    'migrate_remove_invoices.php'
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<div class='progress'>✅ Found: <code>$file</code></div>";
    } else {
        echo "<div class='progress' style='background: #f8d7da;'>❌ Missing: <code>$file</code></div>";
        $missingFiles[] = $file;
    }
}

if (empty($missingFiles)) {
    echo "<p><strong>✅ All required files are present!</strong></p>";
    $completed[] = "File check";
} else {
    echo "<p style='color: #dc3545;'><strong>❌ Missing files detected. Please upload them first.</strong></p>";
    $errors[] = "Missing files: " . implode(', ', $missingFiles);
}

echo "</div>";

// ==========================================
// STEP 2: Check files to delete
// ==========================================
echo "<div class='step info'>
        <h3>🗑️ Step 2: Checking Files to Delete</h3>";

$filesToDelete = [
    'controllers/InvoiceController.php',
    'models/Invoice.php',
    'views/invoices/index.php',
    'views/invoices/create.php',
    'views/invoices/edit.php',
    'views/invoices/view.php'
];

$foundToDelete = [];
foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        echo "<div class='progress' style='background: #fff3cd;'>⚠️ Still exists: <code>$file</code> (should be deleted)</div>";
        $foundToDelete[] = $file;
        $warnings[] = "File still exists: $file";
    } else {
        echo "<div class='progress'>✅ Already deleted: <code>$file</code></div>";
    }
}

if (empty($foundToDelete)) {
    echo "<p><strong>✅ All invoice files have been deleted!</strong></p>";
    $completed[] = "File cleanup";
} else {
    echo "<p style='color: #ffc107;'><strong>⚠️ Some invoice files still exist. Please delete them manually via FTP.</strong></p>";
}

echo "</div>";

// ==========================================
// STEP 3: Database Migration
// ==========================================
echo "<div class='step info'>
        <h3>🗄️ Step 3: Database Migration</h3>";

try {
    // Check if invoiced_at column exists
    $checkColumn = $db->fetchOne("SHOW COLUMNS FROM projects LIKE 'invoiced_at'");
    
    if (!$checkColumn) {
        echo "<div class='progress'>➕ Adding column <code>invoiced_at</code> to projects table...</div>";
        $db->execute("ALTER TABLE projects ADD COLUMN invoiced_at DATETIME NULL AFTER completion_date");
        echo "<div class='progress' style='background: #d4edda;'>✅ Column added successfully!</div>";
        $completed[] = "Add invoiced_at column";
    } else {
        echo "<div class='progress'>ℹ️ Column <code>invoiced_at</code> already exists</div>";
        $warnings[] = "Column already exists (migration may have run before)";
    }
    
    // Check if invoices table exists
    $checkInvoices = $db->fetchOne("SHOW TABLES LIKE 'invoices'");
    
    if ($checkInvoices) {
        echo "<div class='progress'>🔄 Migrating data from invoices to projects...</div>";
        
        // Migrate data
        $migrateSQL = "UPDATE projects p
                      INNER JOIN invoices i ON p.id = i.project_id
                      SET p.invoiced_at = i.paid_date
                      WHERE i.paid_date IS NOT NULL 
                      AND p.invoiced_at IS NULL";
        $db->execute($migrateSQL);
        
        echo "<div class='progress' style='background: #d4edda;'>✅ Data migrated from invoices to projects</div>";
        $completed[] = "Data migration";
        
        // Drop invoice_items table
        echo "<div class='progress'>🗑️ Dropping <code>invoice_items</code> table...</div>";
        $db->execute("DROP TABLE IF EXISTS invoice_items");
        echo "<div class='progress' style='background: #d4edda;'>✅ Table dropped!</div>";
        $completed[] = "Drop invoice_items table";
        
        // Drop invoices table
        echo "<div class='progress'>🗑️ Dropping <code>invoices</code> table...</div>";
        $db->execute("DROP TABLE IF EXISTS invoices");
        echo "<div class='progress' style='background: #d4edda;'>✅ Table dropped!</div>";
        $completed[] = "Drop invoices table";
        
        echo "<p><strong>✅ Database migration completed successfully!</strong></p>";
    } else {
        echo "<div class='progress'>ℹ️ Invoices table doesn't exist (already migrated)</div>";
        $warnings[] = "Migration already completed";
    }
    
} catch (Exception $e) {
    echo "<div class='progress' style='background: #f8d7da;'>❌ Error: " . $e->getMessage() . "</div>";
    $errors[] = "Migration error: " . $e->getMessage();
}

echo "</div>";

// ==========================================
// STEP 4: Verify Database
// ==========================================
echo "<div class='step info'>
        <h3>🔍 Step 4: Verifying Database</h3>";

try {
    // Check projects table structure
    $columns = $db->fetchAll("SHOW COLUMNS FROM projects");
    $hasInvoicedAt = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'invoiced_at') {
            $hasInvoicedAt = true;
            echo "<div class='progress' style='background: #d4edda;'>✅ Column <code>invoiced_at</code> exists in projects</div>";
            break;
        }
    }
    
    if (!$hasInvoicedAt) {
        echo "<div class='progress' style='background: #f8d7da;'>❌ Column <code>invoiced_at</code> NOT found!</div>";
        $errors[] = "invoiced_at column missing";
    }
    
    // Check if invoice tables are gone
    $tables = $db->fetchAll("SHOW TABLES");
    $hasInvoices = false;
    $hasInvoiceItems = false;
    
    foreach ($tables as $table) {
        $tableName = current($table);
        if ($tableName === 'invoices') $hasInvoices = true;
        if ($tableName === 'invoice_items') $hasInvoiceItems = true;
    }
    
    if (!$hasInvoices) {
        echo "<div class='progress' style='background: #d4edda;'>✅ Table <code>invoices</code> removed</div>";
    } else {
        echo "<div class='progress' style='background: #f8d7da;'>❌ Table <code>invoices</code> still exists!</div>";
        $errors[] = "invoices table not removed";
    }
    
    if (!$hasInvoiceItems) {
        echo "<div class='progress' style='background: #d4edda;'>✅ Table <code>invoice_items</code> removed</div>";
    } else {
        echo "<div class='progress' style='background: #f8d7da;'>❌ Table <code>invoice_items</code> still exists!</div>";
        $errors[] = "invoice_items table not removed";
    }
    
    // Count invoiced projects
    $invoicedCount = $db->fetchOne("SELECT COUNT(*) as cnt FROM projects WHERE invoiced_at IS NOT NULL")['cnt'];
    echo "<div class='progress'>ℹ️ Found <strong>$invoicedCount</strong> invoiced projects</div>";
    
    if (empty($errors)) {
        echo "<p><strong>✅ Database structure verified successfully!</strong></p>";
        $completed[] = "Database verification";
    }
    
} catch (Exception $e) {
    echo "<div class='progress' style='background: #f8d7da;'>❌ Verification error: " . $e->getMessage() . "</div>";
    $errors[] = "Verification error: " . $e->getMessage();
}

echo "</div>";

// ==========================================
// SUMMARY
// ==========================================
echo "<hr>
      <div class='step " . (empty($errors) ? 'success' : 'warning') . "'>
        <h2>📊 Deployment Summary</h2>";

if (!empty($completed)) {
    echo "<h4 style='color: #28a745;'>✅ Completed Steps:</h4><ul>";
    foreach ($completed as $step) {
        echo "<li>$step</li>";
    }
    echo "</ul>";
}

if (!empty($warnings)) {
    echo "<h4 style='color: #ffc107;'>⚠️ Warnings:</h4><ul>";
    foreach ($warnings as $warning) {
        echo "<li>$warning</li>";
    }
    echo "</ul>";
}

if (!empty($errors)) {
    echo "<h4 style='color: #dc3545;'>❌ Errors:</h4><ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

if (empty($errors)) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <h3 style='color: #28a745; margin-top: 0;'>🎉 Deployment Successful!</h3>
            <p>The invoice module has been completely removed from HandyCRM.</p>
          </div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <h3 style='color: #dc3545; margin-top: 0;'>⚠️ Deployment Completed with Errors</h3>
            <p>Please review the errors above and fix them manually.</p>
          </div>";
}

echo "</div>";

// ==========================================
// CLEANUP INSTRUCTIONS
// ==========================================
echo "<div class='step warning'>
        <h3>🧹 Post-Deployment Cleanup</h3>
        <p><strong>Delete these files from your server via FTP:</strong></p>
        <div class='cleanup-list'>
            <ul>
                <li><code>deploy_after_upload.php</code> (this file)</li>
                <li><code>migrate_remove_invoices.php</code></li>
                <li><code>test_invoice_creation.php</code> (if exists)</li>
                <li><code>add_project_costs.php</code> (if exists)</li>
                <li><code>list_projects.php</code> (if exists)</li>
                <li><code>check_config.php</code> (if exists)</li>
            </ul>
        </div>
        <p style='color: #856404;'><strong>⚠️ These scripts expose sensitive information and should not remain on production!</strong></p>
      </div>";

// ==========================================
// NEXT STEPS
// ==========================================
echo "<div class='step info'>
        <h3>📋 Next Steps</h3>
        <ol>
            <li>Test the main site: <a href='/' target='_blank'>Go to Dashboard</a></li>
            <li>Verify 'Τιμολόγια' menu is removed from sidebar</li>
            <li>Go to a project and change status to 'Τιμολογημένο'</li>
            <li>Check <a href='?route=/reports' target='_blank'>Reports</a> page - should work with projects data</li>
            <li>Check customer pages - should show invoiced projects instead of invoices</li>
            <li><strong>Delete the cleanup files listed above!</strong></li>
        </ol>
      </div>";

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;'>
        <p style='color: #666; margin: 0;'>Deployment completed on " . date('d/m/Y H:i:s') . "</p>
      </div>

    </div>
</body>
</html>";
?>
