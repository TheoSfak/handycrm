<?php
session_start();

// Simple error handling
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>DEBUG CURRENT STATE</h2>";
echo "<pre>";

// Check session
echo "=== SESSION INFO ===\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "Role ID: " . ($_SESSION['role_id'] ?? 'NOT SET') . "\n";
echo "\n";

if (!isset($_SESSION['user_id'])) {
    echo "❌ NOT LOGGED IN\n";
    echo "Please login first, then visit this page again.\n";
    exit;
}

// Direct database connection
try {
    $db = new PDO(
        "mysql:host=localhost;dbname=u858321845_handycrm;charset=utf8mb4",
        "u858321845_handycrm",
        "Spyros1998!"
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connected\n\n";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Get user permissions from database
$stmt = $db->prepare("
    SELECT p.module, p.action, p.display_name
    FROM permissions p
    JOIN role_permissions rp ON p.id = rp.permission_id
    WHERE rp.role_id = ?
    ORDER BY p.module, p.action
");
$stmt->execute([$_SESSION['role_id']]);
$permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== USER PERMISSIONS (role_id=" . $_SESSION['role_id'] . ") ===\n";
if (empty($permissions)) {
    echo "❌ NO PERMISSIONS FOUND!\n";
} else {
    foreach ($permissions as $perm) {
        echo "✓ {$perm['module']}.{$perm['action']} - {$perm['display_name']}\n";
    }
}
echo "\n";

// Manual permission checks (without AuthMiddleware helpers)
$userPerms = array_map(function($p) {
    return $p['module'] . '.' . $p['action'];
}, $permissions);

echo "=== PERMISSION CHECKS ===\n";
echo "has 'maintenances.view': " . (in_array('maintenances.view', $userPerms) ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "has 'transformer_maintenance.view': " . (in_array('transformer_maintenance.view', $userPerms) ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "has 'transformer_maintenance.create': " . (in_array('transformer_maintenance.create', $userPerms) ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "has 'dashboard.view': " . (in_array('dashboard.view', $userPerms) ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "has 'payments.view': " . (in_array('payments.view', $userPerms) ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "Role is admin: " . (($_SESSION['role'] ?? '') === 'admin' ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "Role is supervisor: " . (($_SESSION['role'] ?? '') === 'supervisor' ? '✓ TRUE' : '✗ FALSE') . "\n";
echo "\n";

// Check if maintenances.* permissions still exist in database
$stmt = $db->query("SELECT * FROM permissions WHERE module = 'maintenances' ORDER BY action");
$oldPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== OLD 'maintenances' PERMISSIONS IN DATABASE ===\n";
if (empty($oldPerms)) {
    echo "✓ CORRECTLY DELETED - No 'maintenances' module found\n";
} else {
    echo "❌ STILL EXIST!\n";
    foreach ($oldPerms as $p) {
        echo "  - maintenances.{$p['action']} (id: {$p['id']})\n";
    }
}
echo "\n";

// Check transformer_maintenance permissions
$stmt = $db->query("SELECT * FROM permissions WHERE module = 'transformer_maintenance' ORDER BY action");
$newPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== 'transformer_maintenance' PERMISSIONS IN DATABASE ===\n";
if (empty($newPerms)) {
    echo "❌ NOT FOUND - These should exist!\n";
} else {
    echo "✓ Found " . count($newPerms) . " permissions:\n";
    foreach ($newPerms as $p) {
        echo "  - transformer_maintenance.{$p['action']} (id: {$p['id']})\n";
    }
}
echo "\n";

// Check what AuthController would do
echo "=== REDIRECT LOGIC TEST ===\n";
$role = $_SESSION['role'] ?? '';
if ($role === 'admin' || $role === 'supervisor') {
    echo "→ Would redirect to: /dashboard (admin/supervisor)\n";
} elseif (in_array('dashboard.view', $userPerms)) {
    echo "→ Would redirect to: /dashboard (has dashboard.view)\n";
} elseif (in_array('payments.view', $userPerms)) {
    echo "→ Would redirect to: /payments (has payments.view)\n";
} elseif (in_array('users.view', $userPerms)) {
    echo "→ Would redirect to: /users (has users.view)\n";
} elseif (in_array('projects.view', $userPerms)) {
    echo "→ Would redirect to: /projects (has projects.view)\n";
} elseif (in_array('transformer_maintenance.view', $userPerms)) {
    echo "→ Would redirect to: /maintenances (has transformer_maintenance.view) ✓\n";
} else {
    echo "→ Would redirect to: /users/show/" . $_SESSION['user_id'] . " (no permissions) ❌\n";
}
echo "\n";

// Check TransformerMaintenanceController logic
echo "=== TRANSFORMER MAINTENANCE CONTROLLER CHECK ===\n";
$canAccess = ($role === 'admin' || $role === 'supervisor' || in_array('transformer_maintenance.view', $userPerms));
if ($canAccess) {
    echo "✓ Can access /maintenances (would show maintenance list)\n";
} else {
    echo "❌ Cannot access /maintenances (would redirect to /dashboard?error=unauthorized)\n";
    echo "   Reason: Not admin/supervisor AND doesn't have transformer_maintenance.view\n";
}

echo "</pre>";
