<?php
/**
 * Debug Role System
 * Upload this to ecowatt.gr/crm/debug_roles.php
 */

session_start();
require_once 'config/config.php';
require_once 'classes/Database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== ROLE SYSTEM DEBUG ===\n\n";

// 1. Check database structure
echo "1. DATABASE STRUCTURE:\n";
echo "----------------------\n";
$db = new Database();
$conn = $db->connect();

// Check if role column exists
try {
    $stmt = $conn->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Users table columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
} catch (Exception $e) {
    echo "ERROR checking users table: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Check roles table
echo "2. ROLES TABLE:\n";
echo "---------------\n";
try {
    $stmt = $conn->query("SELECT * FROM roles ORDER BY id");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($roles as $role) {
        echo "ID: {$role['id']}, Name: {$role['name']}, Display: {$role['display_name']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Check a test user
echo "3. TEST USER (username='agis' or 'xaris'):\n";
echo "------------------------------------------\n";
try {
    $stmt = $conn->prepare("
        SELECT u.*, r.name as role_name, r.display_name as role_display_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.username IN ('agis', 'xaris')
        LIMIT 1
    ");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Username: {$user['username']}\n";
        echo "Email: {$user['email']}\n";
        echo "role_id: {$user['role_id']}\n";
        echo "role_name: {$user['role_name']}\n";
        echo "role_display_name: {$user['role_display_name']}\n";
        
        // Check if old 'role' column exists
        if (isset($user['role'])) {
            echo "OLD COLUMN 'role' EXISTS: {$user['role']} ⚠️\n";
        } else {
            echo "OLD COLUMN 'role' REMOVED: ✓\n";
        }
    } else {
        echo "User not found!\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Check permissions for test role
echo "4. PERMISSIONS FOR ROLE 'test':\n";
echo "--------------------------------\n";
try {
    $stmt = $conn->prepare("
        SELECT p.module, p.action, p.display_name 
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        JOIN roles r ON rp.role_id = r.id
        WHERE r.name = 'test'
        ORDER BY p.module, p.action
    ");
    $stmt->execute();
    $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($perms) > 0) {
        foreach ($perms as $perm) {
            echo "  - {$perm['module']}.{$perm['action']}\n";
        }
        echo "\nTotal: " . count($perms) . " permissions\n";
    } else {
        echo "No permissions found for 'test' role!\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. Check AuthMiddleware file
echo "5. AUTHMIDDLEWARE CHECK:\n";
echo "------------------------\n";
if (file_exists('classes/AuthMiddleware.php')) {
    echo "✓ AuthMiddleware.php exists\n";
    $content = file_get_contents('classes/AuthMiddleware.php');
    if (strpos($content, 'function can($permission)') !== false) {
        echo "✓ Global can() function exists\n";
    } else {
        echo "✗ Global can() function NOT FOUND!\n";
    }
} else {
    echo "✗ AuthMiddleware.php NOT FOUND!\n";
}

echo "\n";

// 6. Check UserController
echo "6. USERCONTROLLER CHECK:\n";
echo "------------------------\n";
if (file_exists('controllers/UserController.php')) {
    echo "✓ UserController.php exists\n";
    $content = file_get_contents('controllers/UserController.php');
    if (strpos($content, 'require_once __DIR__ . \'/../classes/AuthMiddleware.php\'') !== false) {
        echo "✓ UserController requires AuthMiddleware\n";
    } else {
        echo "✗ UserController DOES NOT require AuthMiddleware!\n";
    }
    
    if (strpos($content, 'LEFT JOIN roles r ON u.role_id = r.id') !== false) {
        echo "✓ UserController uses role_id JOIN\n";
    } else {
        echo "✗ UserController DOES NOT use role_id JOIN!\n";
    }
} else {
    echo "✗ UserController.php NOT FOUND!\n";
}

echo "\n";

// 7. Check Database.php for UTF8
echo "7. DATABASE UTF8 CHECK:\n";
echo "-----------------------\n";
if (file_exists('classes/Database.php')) {
    $content = file_get_contents('classes/Database.php');
    if (strpos($content, 'SET NAMES utf8mb4') !== false) {
        echo "✓ Database.php sets UTF8MB4\n";
    } else {
        echo "✗ Database.php DOES NOT set UTF8MB4!\n";
    }
} else {
    echo "✗ Database.php NOT FOUND!\n";
}

echo "\n";

// 8. Check index.php for hardcoded admin check
echo "8. INDEX.PHP HARDCODED CHECKS:\n";
echo "------------------------------\n";
if (file_exists('index.php')) {
    $content = file_get_contents('index.php');
    
    // Check for users route
    if (preg_match('/strpos\(\$currentRoute.*\/users.*\{.*?\}/s', $content, $matches)) {
        $usersBlock = $matches[0];
        if (strpos($usersBlock, "\$_SESSION['role'] !== 'admin'") !== false) {
            echo "✗ index.php HAS hardcoded admin check for /users route!\n";
        } else {
            echo "✓ index.php NO hardcoded admin check for /users\n";
        }
    }
} else {
    echo "✗ index.php NOT FOUND!\n";
}

echo "\n";
echo "=== END DEBUG ===\n";
