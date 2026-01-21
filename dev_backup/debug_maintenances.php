<?php
/**
 * Debug Maintenances Permission
 * Upload to ecowatt.gr/crm/debug_maintenances.php
 */

session_start();
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/AuthMiddleware.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== MAINTENANCES PERMISSION DEBUG ===\n\n";

// 1. Check current session
echo "1. SESSION INFO:\n";
echo "----------------\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";

echo "\n";

// 2. Get user's role from database
echo "2. USER ROLE FROM DB:\n";
echo "---------------------\n";
if (isset($_SESSION['user_id'])) {
    $db = new Database();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("
        SELECT u.*, r.name as role_name, r.display_name as role_display_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "role_id: {$user['role_id']}\n";
        echo "role_name: {$user['role_name']}\n";
        echo "role_display_name: {$user['role_display_name']}\n";
        
        // 3. Check permissions for this role
        echo "\n3. PERMISSIONS FOR THIS ROLE:\n";
        echo "-----------------------------\n";
        
        $stmt = $conn->prepare("
            SELECT p.module, p.action, p.display_name
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = ?
            ORDER BY p.module, p.action
        ");
        $stmt->execute([$user['role_id']]);
        $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($perms as $perm) {
            echo "  - {$perm['module']}.{$perm['action']}\n";
        }
        
        echo "\nTotal: " . count($perms) . " permissions\n";
        
        // 4. Check specifically for maintenances.view
        echo "\n4. CHECK maintenances.view:\n";
        echo "----------------------------\n";
        
        $hasMaintenance = false;
        foreach ($perms as $perm) {
            if ($perm['module'] === 'maintenances' && $perm['action'] === 'view') {
                $hasMaintenance = true;
                break;
            }
        }
        
        if ($hasMaintenance) {
            echo "✓ User HAS maintenances.view permission\n";
        } else {
            echo "✗ User DOES NOT have maintenances.view permission\n";
        }
        
        // 5. Test can() function
        echo "\n5. TEST can() FUNCTION:\n";
        echo "-----------------------\n";
        $canResult = can('maintenances.view');
        echo "can('maintenances.view') = " . ($canResult ? 'TRUE' : 'FALSE') . "\n";
        
        // 6. Check if permission exists in DB
        echo "\n6. CHECK IF PERMISSION EXISTS:\n";
        echo "-------------------------------\n";
        $stmt = $conn->prepare("
            SELECT * FROM permissions 
            WHERE module = 'maintenances' AND action = 'view'
        ");
        $stmt->execute();
        $permExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($permExists) {
            echo "✓ Permission 'maintenances.view' EXISTS in database\n";
            echo "  ID: {$permExists['id']}\n";
            echo "  Display: {$permExists['display_name']}\n";
        } else {
            echo "✗ Permission 'maintenances.view' DOES NOT EXIST in database!\n";
        }
    }
} else {
    echo "Not logged in!\n";
}

echo "\n=== END DEBUG ===\n";
