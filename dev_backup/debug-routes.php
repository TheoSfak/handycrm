<?php
/**
 * Debug Routes Script
 */

// Simulate the environment
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['REQUEST_URI'] = '/handycrm/maintenances/sendEmail/4';
$_SERVER['REQUEST_METHOD'] = 'POST';

define('APP_ROOT', dirname(__FILE__));
define('BASE_URL', '/handycrm');

require_once 'classes/Router.php';

$router = new Router();

// Add all maintenance routes
echo "<h2>Adding Routes:</h2><pre>";
$router->add('/maintenances', 'TransformerMaintenanceController', 'index');
echo "✓ GET /maintenances -> index\n";

$router->add('/maintenances/create', 'TransformerMaintenanceController', 'create');
echo "✓ GET /maintenances/create -> create\n";

$router->add('/maintenances/store', 'TransformerMaintenanceController', 'store', 'POST');
echo "✓ POST /maintenances/store -> store\n";

$router->add('/maintenances/view/{id}', 'TransformerMaintenanceController', 'show');
echo "✓ GET /maintenances/view/{id} -> show\n";

$router->add('/maintenances/sendEmail/{id}', 'TransformerMaintenanceController', 'sendEmail', 'POST');
echo "✓ POST /maintenances/sendEmail/{id} -> sendEmail\n";

$router->add('/maintenances/edit/{id}', 'TransformerMaintenanceController', 'edit');
echo "✓ GET /maintenances/edit/{id} -> edit\n";

$router->add('/maintenances/update/{id}', 'TransformerMaintenanceController', 'update', 'POST');
echo "✓ POST /maintenances/update/{id} -> update\n";

$router->add('/maintenances/exportPDF/{id}', 'TransformerMaintenanceController', 'exportPDF');
echo "✓ GET /maintenances/exportPDF/{id} -> exportPDF\n";

$router->add('/maintenances/exportExcel/{id}', 'TransformerMaintenanceController', 'exportExcel');
echo "✓ GET /maintenances/exportExcel/{id} -> exportExcel\n";

echo "</pre>";

// Test the route matching
echo "<h2>Testing Route Match:</h2><pre>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Base Path: " . BASE_URL . "\n";

// Get clean URI
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = strtok($requestUri, '?');
$basePath = BASE_URL;
if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = rtrim($requestUri, '/');

echo "Clean URI: " . $requestUri . "\n";

// Check if sendEmail route exists
$reflection = new ReflectionClass('Router');
$property = $reflection->getProperty('routes');
$property->setAccessible(true);
$routes = $property->getValue($router);

echo "\nAll Routes Registered:\n";
foreach ($routes as $i => $route) {
    echo ($i + 1) . ". " . $route['method'] . " " . $route['route'] . " -> " . $route['controller'] . "::" . $route['action'] . "\n";
}

echo "\nMatching /maintenances/sendEmail/4 with POST:\n";
foreach ($routes as $route) {
    $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['route']);
    $pattern = '#^' . $pattern . '$#';
    $matches = preg_match($pattern, $requestUri);
    $methodMatch = $route['method'] === 'POST';
    
    if ($route['route'] === '/maintenances/sendEmail/{id}') {
        echo "  Route: " . $route['route'] . "\n";
        echo "  Pattern: " . $pattern . "\n";
        echo "  URI Match: " . ($matches ? "✓ YES" : "✗ NO") . "\n";
        echo "  Method Match: " . ($methodMatch ? "✓ YES" : "✗ NO") . "\n";
        echo "  Overall: " . ($matches && $methodMatch ? "✓ MATCH!" : "✗ NO MATCH") . "\n";
    }
}

echo "</pre>";

// Check controller exists
echo "<h2>Checking Controller:</h2><pre>";
$controllerFile = __DIR__ . '/controllers/TransformerMaintenanceController.php';
echo "Controller file: " . $controllerFile . "\n";
echo "File exists: " . (file_exists($controllerFile) ? "✓ YES" : "✗ NO") . "\n";

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    echo "Class exists: " . (class_exists('TransformerMaintenanceController') ? "✓ YES" : "✗ NO") . "\n";
    
    if (class_exists('TransformerMaintenanceController')) {
        echo "Method sendEmail exists: " . (method_exists('TransformerMaintenanceController', 'sendEmail') ? "✓ YES" : "✗ NO") . "\n";
    }
}

echo "</pre>";

// Check if email was sent
echo "<h2>Check Recent Emails:</h2><pre>";
require_once 'config/config.php';
require_once 'classes/Database.php';

try {
    $database = new Database();
    $pdo = $database->connect();
    
    $stmt = $pdo->query("SELECT id, type, recipient_email, subject, status, created_at FROM email_notifications ORDER BY created_at DESC LIMIT 5");
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($emails)) {
        echo "No emails found in database.\n";
    } else {
        echo "Recent emails:\n";
        foreach ($emails as $email) {
            echo sprintf(
                "  [%s] %s to %s - %s (%s)\n",
                $email['id'],
                $email['type'],
                $email['recipient_email'],
                $email['status'],
                $email['created_at']
            );
        }
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
