<!DOCTYPE html>
<html>
<head>
    <title>HandyCRM v1.4.0 - Diagnostic Check</title>
    <style>
        body { font-family: Arial; max-width: 1200px; margin: 20px auto; padding: 20px; }
        h1 { color: #333; }
        .check { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .icon { font-weight: bold; margin-right: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔍 HandyCRM v1.4.0 - Diagnostic Check</h1>
    <p>Current Time: <?= date('Y-m-d H:i:s') ?></p>
    <p>Server: <?= $_SERVER['SERVER_NAME'] ?></p>

    <?php
    // Database connection
    require_once __DIR__ . '/config/database.php';
    
    $checks = [];
    
    // 1. Check Controllers
    echo "<h2>📁 Controllers Check</h2>";
    $requiredControllers = [
        'EmailTemplateController.php',
        'SmtpSettingsController.php',
        'RoleController.php',
        'TransformerMaintenanceController.php',
        'ProjectReportController.php',
        'UserController.php'
    ];
    
    foreach ($requiredControllers as $controller) {
        $path = __DIR__ . '/controllers/' . $controller;
        if (file_exists($path)) {
            echo "<div class='check success'><span class='icon'>✅</span> $controller exists</div>";
        } else {
            echo "<div class='check error'><span class='icon'>❌</span> $controller MISSING!</div>";
        }
    }
    
    // 2. Check Classes
    echo "<h2>📚 Classes Check</h2>";
    $requiredClasses = [
        'EmailService.php',
        'AuthMiddleware.php',
        'CustomMaintenancePDF.php'
    ];
    
    foreach ($requiredClasses as $class) {
        $path = __DIR__ . '/classes/' . $class;
        if (file_exists($path)) {
            echo "<div class='check success'><span class='icon'>✅</span> $class exists</div>";
        } else {
            echo "<div class='check error'><span class='icon'>❌</span> $class MISSING!</div>";
        }
    }
    
    // 3. Check Views
    echo "<h2>🎨 Views Check</h2>";
    $requiredViews = [
        'views/roles',
        'views/email_templates',
        'views/smtp_settings'
    ];
    
    foreach ($requiredViews as $view) {
        $path = __DIR__ . '/' . $view;
        if (is_dir($path)) {
            $files = scandir($path);
            $phpFiles = array_filter($files, function($f) { return pathinfo($f, PATHINFO_EXTENSION) === 'php'; });
            echo "<div class='check success'><span class='icon'>✅</span> $view exists (" . count($phpFiles) . " PHP files)</div>";
        } else {
            echo "<div class='check error'><span class='icon'>❌</span> $view folder MISSING!</div>";
        }
    }
    
    // 4. Check Database Tables
    echo "<h2>🗄️ Database Tables Check</h2>";
    $requiredTables = [
        'email_notifications',
        'smtp_settings',
        'email_templates',
        'roles',
        'permissions',
        'role_permissions',
        'user_role'
    ];
    
    try {
        foreach ($requiredTables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                // Count rows
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "<div class='check success'><span class='icon'>✅</span> Table '$table' exists ($count rows)</div>";
            } else {
                echo "<div class='check error'><span class='icon'>❌</span> Table '$table' MISSING!</div>";
            }
        }
    } catch (PDOException $e) {
        echo "<div class='check error'><span class='icon'>❌</span> Database Error: " . $e->getMessage() . "</div>";
    }
    
    // 5. Check Permissions Data
    echo "<h2>🔐 Permissions Data Check</h2>";
    try {
        $stmt = $pdo->query("SELECT module, COUNT(*) as count FROM permissions GROUP BY module");
        $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($modules) > 0) {
            echo "<table>";
            echo "<tr><th>Module</th><th>Permissions Count</th></tr>";
            foreach ($modules as $module) {
                echo "<tr><td>{$module['module']}</td><td>{$module['count']}</td></tr>";
            }
            echo "</table>";
            
            // Check for invoices (should NOT exist)
            $invoiceCheck = $pdo->query("SELECT COUNT(*) FROM permissions WHERE module = 'invoices'")->fetchColumn();
            if ($invoiceCheck > 0) {
                echo "<div class='check warning'><span class='icon'>⚠️</span> Found $invoiceCheck invoice permissions (should be deleted!)</div>";
            } else {
                echo "<div class='check success'><span class='icon'>✅</span> No invoice permissions (correct)</div>";
            }
        } else {
            echo "<div class='check error'><span class='icon'>❌</span> No permissions found in database!</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='check error'><span class='icon'>❌</span> Error checking permissions: " . $e->getMessage() . "</div>";
    }
    
    // 6. Check Routes in index.php
    echo "<h2>🛣️ Routing Check</h2>";
    $indexContent = file_get_contents(__DIR__ . '/index.php');
    
    $routesToCheck = [
        '/roles' => 'RoleController',
        '/email-templates' => 'EmailTemplateController',
        '/smtp-settings' => 'SmtpSettingsController'
    ];
    
    foreach ($routesToCheck as $route => $controller) {
        if (strpos($indexContent, $route) !== false && strpos($indexContent, $controller) !== false) {
            echo "<div class='check success'><span class='icon'>✅</span> Route '$route' → $controller configured</div>";
        } else {
            echo "<div class='check error'><span class='icon'>❌</span> Route '$route' → $controller NOT FOUND in index.php!</div>";
        }
    }
    
    // 7. Check Composer
    echo "<h2>📦 Composer Check</h2>";
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        echo "<div class='check success'><span class='icon'>✅</span> Composer vendor folder exists</div>";
        
        // Check PHPMailer
        if (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer')) {
            echo "<div class='check success'><span class='icon'>✅</span> PHPMailer installed</div>";
        } else {
            echo "<div class='check error'><span class='icon'>❌</span> PHPMailer NOT installed! Run: composer require phpmailer/phpmailer</div>";
        }
    } else {
        echo "<div class='check error'><span class='icon'>❌</span> Composer not installed! Run: composer install</div>";
    }
    
    // 8. PHP Version
    echo "<h2>🐘 PHP Environment</h2>";
    echo "<div class='check info'>";
    echo "PHP Version: " . PHP_VERSION . "<br>";
    echo "Extensions: " . implode(', ', get_loaded_extensions());
    echo "</div>";
    
    // 9. File Permissions
    echo "<h2>🔒 File Permissions Check</h2>";
    $dirsToCheck = ['controllers', 'classes', 'views'];
    foreach ($dirsToCheck as $dir) {
        $perms = substr(sprintf('%o', fileperms(__DIR__ . '/' . $dir)), -4);
        $readable = is_readable(__DIR__ . '/' . $dir);
        if ($readable) {
            echo "<div class='check success'><span class='icon'>✅</span> $dir: $perms (readable)</div>";
        } else {
            echo "<div class='check error'><span class='icon'>❌</span> $dir: $perms (NOT readable!)</div>";
        }
    }
    
    // Summary
    echo "<h2>📊 Summary</h2>";
    echo "<div class='check info'>";
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Fix any ❌ errors above<br>";
    echo "2. If routes are missing, replace index.php<br>";
    echo "3. If tables are missing, run migration_v1.4.0.sql<br>";
    echo "4. If files are missing, re-upload them<br>";
    echo "5. If composer is missing, run: composer install --no-dev<br>";
    echo "</div>";
    ?>
</body>
</html>
