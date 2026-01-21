<?php
/**
 * Debug Maintenances Redirect
 * Upload to ecowatt.gr/crm/debug_maintenances_redirect.php
 */

session_start();
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/AuthMiddleware.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== MAINTENANCES REDIRECT DEBUG ===\n\n";

// 1. Session info
echo "1. SESSION:\n";
echo "-----------\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n\n";

// 2. Get BaseController methods
echo "2. CHECKING PERMISSION METHODS:\n";
echo "--------------------------------\n";

require_once 'classes/BaseController.php';
$testController = new class extends BaseController {
    public function testMethods() {
        echo "isAdmin(): " . ($this->isAdmin() ? 'TRUE' : 'FALSE') . "\n";
        echo "isSupervisor(): " . (method_exists($this, 'isSupervisor') && $this->isSupervisor() ? 'TRUE' : 'FALSE') . "\n";
    }
};
$testController->testMethods();

// 3. Test can() function
echo "\n3. PERMISSION CHECKS:\n";
echo "---------------------\n";
echo "can('transformer_maintenance.view'): " . (can('transformer_maintenance.view') ? 'TRUE' : 'FALSE') . "\n";
echo "can('maintenances.view'): " . (can('maintenances.view') ? 'TRUE' : 'FALSE') . "\n";
echo "can('dashboard.view'): " . (can('dashboard.view') ? 'TRUE' : 'FALSE') . "\n";

// 4. Test the exact check from controller
echo "\n4. CONTROLLER CHECK SIMULATION:\n";
echo "--------------------------------\n";

$db = new Database();
$conn = $db->connect();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $isAdmin = ($user['role_id'] == 1 || $_SESSION['role'] === 'admin');
    $canView = can('transformer_maintenance.view');
    
    echo "User role_id: {$user['role_id']}\n";
    echo "isAdmin check: " . ($isAdmin ? 'TRUE' : 'FALSE') . "\n";
    echo "can('transformer_maintenance.view'): " . ($canView ? 'TRUE' : 'FALSE') . "\n";
    
    $shouldRedirect = (!$isAdmin && !$canView);
    echo "\nShould redirect (NOT admin AND NOT can): " . ($shouldRedirect ? 'YES - WILL REDIRECT!' : 'NO - WILL ALLOW') . "\n";
    
    if ($shouldRedirect) {
        echo "❌ USER WILL BE REDIRECTED TO DASHBOARD!\n";
    } else {
        echo "✓ USER WILL BE ALLOWED TO VIEW MAINTENANCES!\n";
    }
}

echo "\n=== END DEBUG ===\n";
