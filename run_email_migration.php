<?php
// Execute email notification system migration
require_once 'config.php';
require_once 'classes/Database.php';

try {
    $db = new Database();
    
    // Read migration file
    $sql = file_get_contents('migrations/add_email_notification_system.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "<h2>🚀 Executing Email Notification System Migration</h2>\n";
    echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; margin: 20px 0;'>\n";
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !str_starts_with($statement, '--')) {
            $shortStatement = substr(str_replace(["\n", "\r"], ' ', $statement), 0, 80) . '...';
            echo "✅ Executing: " . htmlspecialchars($shortStatement) . "<br>\n";
            
            $db->exec($statement);
        }
    }
    
    echo "</div>\n";
    echo "<h3 style='color: green;'>✅ Email notification system migration completed successfully!</h3>\n";
    echo "<p><a href='admin/email-settings.php'>👉 Configure Email Settings Now</a></p>\n";
    
    // Verify tables were created
    $tables = ['email_notifications', 'notification_settings', 'email_templates'];
    echo "<h4>📊 Verification:</h4>\n";
    echo "<ul>\n";
    
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($result) {
            $count = $db->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            echo "<li style='color: green;'>✅ Table '$table' created with $count records</li>\n";
        } else {
            echo "<li style='color: red;'>❌ Table '$table' not found</li>\n";
        }
    }
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Migration failed: " . htmlspecialchars($e->getMessage()) . "</h3>\n";
    echo "<p>Error details: " . htmlspecialchars($e->getTraceAsString()) . "</p>\n";
}
?>