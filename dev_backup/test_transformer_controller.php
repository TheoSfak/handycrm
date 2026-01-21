<?php
/**
 * Test TransformerMaintenanceController Permission Check
 * Upload to ecowatt.gr/crm/test_transformer_controller.php
 */

$controllerFile = 'controllers/TransformerMaintenanceController.php';

echo "=== CONTROLLER CHECK ===\n\n";

if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    // Check which permission it's checking
    if (strpos($content, "can('transformer_maintenance.view')") !== false) {
        echo "✓ Controller checks for 'transformer_maintenance.view'\n";
    } elseif (strpos($content, "can('maintenances.view')") !== false) {
        echo "✗ Controller STILL checks for 'maintenances.view' (WRONG!)\n";
    } else {
        echo "? Cannot find permission check in controller\n";
    }
    
    // Check if AuthMiddleware is required
    if (strpos($content, "require_once __DIR__ . '/../classes/AuthMiddleware.php'") !== false ||
        strpos($content, "require_once 'classes/AuthMiddleware.php'") !== false) {
        echo "✓ Controller requires AuthMiddleware\n";
    } else {
        echo "✗ Controller DOES NOT require AuthMiddleware\n";
    }
    
    // Show last modified time
    echo "\nFile last modified: " . date('Y-m-d H:i:s', filemtime($controllerFile)) . "\n";
} else {
    echo "✗ Controller file NOT FOUND!\n";
}

echo "\n=== END CHECK ===\n";
