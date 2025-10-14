<?php
/**
 * HandyCRM - Main Entry Point
 * 
 * @author Theodore Sfakianakis
 * @email theodore.sfakianakis@gmail.com
 * @copyright 2025 Theodore Sfakianakis. All rights reserved.
 * 
 * Checks if system is installed and routes accordingly
 */

// Check if system is installed
$configFile = __DIR__ . '/config/config.php';

if (!file_exists($configFile)) {
    // System not installed, redirect to installation
    header('Location: install.php');
    exit;
}

// Load configuration
require_once $configFile;
require_once 'classes/Database.php';

// Define BASE_URL for clean URLs
if (!defined('BASE_URL')) {
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $scriptPath === '/' ? '' : $scriptPath);
}

// Test database connection
try {
    $db = new Database();
    $connection = $db->connect();
    
    // Check if tables exist
    $stmt = $connection->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        // Database not properly set up - redirect to installation
        header('Location: install.php');
        exit;
    }
} catch (Exception $e) {
    // Database connection failed - redirect to installation
    header('Location: install.php');
    exit;
}

// System is properly installed, load the application
// Session is already started in config.php

// Auto-load classes
spl_autoload_register(function ($class) {
    $paths = [
        'classes/' . $class . '.php',
        'models/' . $class . '.php',
        'controllers/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Initialize router
$router = new Router();

// Define routes
$router->add('/', 'DashboardController', 'index');
$router->add('/login', 'AuthController', 'login');
$router->add('/logout', 'AuthController', 'logout');
$router->add('/dashboard', 'DashboardController', 'index');

// Customer routes
$router->add('/customers', 'CustomerController', 'index');
$router->add('/customers/create', 'CustomerController', 'create');
$router->add('/customers/export-csv', 'CustomerController', 'exportCsv');
$router->add('/customers/import-csv', 'CustomerController', 'importCsv');
$router->add('/customers/demo-csv', 'CustomerController', 'downloadDemoCsv');
$router->add('/customers/show/{id}', 'CustomerController', 'show');
$router->add('/customers/edit/{id}', 'CustomerController', 'edit');
$router->add('/customers/delete/{id}', 'CustomerController', 'delete');

// Project routes (future implementation)
$router->add('/projects', 'ProjectController', 'index');
$router->add('/projects/create', 'ProjectController', 'create');
$router->add('/projects/show/{id}', 'ProjectController', 'show');
$router->add('/projects/details/{id}', 'ProjectController', 'details');

// Appointment routes (future implementation)
$router->add('/appointments', 'AppointmentController', 'index');
$router->add('/appointments/create', 'AppointmentController', 'create');
$router->add('/appointments/calendar', 'AppointmentController', 'calendar');

// Get current route from URL
$currentRoute = $_GET['route'] ?? '/';

// Simple routing for PHP built-in server
if ($currentRoute === '/' || $currentRoute === '/dashboard') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    // Load dashboard
    require_once 'controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
    
} elseif ($currentRoute === '/login') {
    require_once 'controllers/AuthController.php';
    $controller = new AuthController();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->authenticate();
    } else {
        $controller->login();
    }
    
} elseif ($currentRoute === '/logout') {
    require_once 'controllers/AuthController.php';
    $controller = new AuthController();
    $controller->logout();
    
} elseif (strpos($currentRoute, '/profile') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/ProfileController.php';
    $controller = new ProfileController();
    
    if ($currentRoute === '/profile') {
        $controller->index();
    } elseif ($currentRoute === '/profile/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->update();
    } elseif ($currentRoute === '/profile/change-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->changePassword();
    } else {
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/customers') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/CustomerController.php';
    $controller = new CustomerController();
    
    if ($currentRoute === '/customers') {
        $controller->index();
    } elseif ($currentRoute === '/customers/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    } elseif ($currentRoute === '/customers/export-csv') {
        $controller->exportCsv();
    } elseif ($currentRoute === '/customers/import-csv') {
        $controller->importCsv();
    } elseif ($currentRoute === '/customers/demo-csv') {
        $controller->downloadDemoCsv();
    } elseif ($currentRoute === '/customers/show') {
        $id = $_GET['id'] ?? 0;
        $controller->show($id);
    } elseif ($currentRoute === '/customers/edit') {
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update($id);
        } else {
            $controller->edit($id);
        }
    } elseif ($currentRoute === '/customers/delete') {
        $id = $_GET['id'] ?? 0;
        $controller->delete($id);
    } elseif (preg_match('/\/customers\/view\/(\d+)/', $currentRoute, $matches)) {
        $controller->show($matches[1]);
    } elseif (preg_match('/\/customers\/edit\/(\d+)/', $currentRoute, $matches)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update($matches[1]);
        } else {
            $controller->edit($matches[1]);
        }
    } elseif ($currentRoute === '/customers/delete') {
        $id = $_GET['id'] ?? $_POST['id'] ?? 0;
        $controller->delete($id);
    } elseif (preg_match('/\/customers\/delete\/(\d+)/', $currentRoute, $matches)) {
        $controller->delete($matches[1]);
    } else {
        // 404 for customers
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Customer page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/projects') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/ProjectController.php';
    $controller = new ProjectController();
    
    if ($currentRoute === '/projects') {
        $controller->index();
    } elseif ($currentRoute === '/projects/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    } elseif ($currentRoute === '/projects/export-csv') {
        $controller->exportCsv();
    } elseif ($currentRoute === '/projects/import-csv') {
        $controller->importCsv();
    } elseif ($currentRoute === '/projects/demo-csv') {
        $controller->downloadDemoCsv();
    } elseif (preg_match('/\/projects\/show\/(\d+)/', $currentRoute, $matches)) {
        $_GET['id'] = $matches[1];
        $controller->show();
    } elseif ($currentRoute === '/projects/show') {
        $controller->details();
    } elseif ($currentRoute === '/projects/details') {
        $controller->details();
    } elseif ($currentRoute === '/projects/edit') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->edit();
        }
    } elseif ($currentRoute === '/projects/update-status') {
        $controller->updateStatus();
    } elseif ($currentRoute === '/projects/delete') {
        $controller->delete();
    } elseif (preg_match('/\/projects\/(\d+)\/tasks/', $currentRoute, $projectMatches)) {
        // Project Tasks routes - moved inside projects block
        require_once 'controllers/ProjectTasksController.php';
        $tasksController = new ProjectTasksController();
        $projectId = $projectMatches[1];
        
        if (preg_match('/\/projects\/(\d+)\/tasks$/', $currentRoute)) {
            $tasksController->index($projectId);
        } elseif (preg_match('/\/projects\/(\d+)\/tasks\/export-csv/', $currentRoute)) {
            $tasksController->exportCsv($projectId);
        } elseif (preg_match('/\/projects\/(\d+)\/tasks\/add/', $currentRoute)) {
            $tasksController->add($projectId);
        } elseif (preg_match('/\/projects\/(\d+)\/tasks\/edit\/(\d+)/', $currentRoute, $matches)) {
            $taskId = $matches[2];
            $tasksController->edit($projectId, $taskId);
        } elseif (preg_match('/\/projects\/(\d+)\/tasks\/view\/(\d+)/', $currentRoute, $matches)) {
            $taskId = $matches[2];
            $tasksController->viewTask($projectId, $taskId);
        } elseif (preg_match('/\/projects\/(\d+)\/tasks\/(\d+)\/breakdown/', $currentRoute, $matches)) {
            $taskId = $matches[2];
            $tasksController->breakdown($projectId, $taskId);
        } elseif (preg_match('/\/projects\/(\d+)\/tasks\/copy/', $currentRoute) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? 0;
            $tasksController->copy($projectId, $taskId);
        } elseif (preg_match('/\/projects\/(\d+)\/tasks\/delete/', $currentRoute) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['task_id'] ?? 0;
            $tasksController->delete($projectId);
        } else {
            header('HTTP/1.0 404 Not Found');
            echo "<h1>404 - Project task page not found</h1>";
        }
    } else {
        // 404 for projects
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Project page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/appointments') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/AppointmentController.php';
    $controller = new AppointmentController();
    
    if ($currentRoute === '/appointments') {
        $controller->index();
    } elseif ($currentRoute === '/appointments/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    } elseif ($currentRoute === '/appointments/details') {
        $controller->details();
    } elseif ($currentRoute === '/appointments/edit') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->edit();
        }
    } elseif ($currentRoute === '/appointments/delete') {
        $controller->delete();
    } elseif ($currentRoute === '/appointments/calendar') {
        $controller->calendar();
    } elseif ($currentRoute === '/appointments/api/list') {
        $controller->apiList();
    } else {
        // 404 for appointments
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Appointment page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/quotes') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/QuoteController.php';
    $controller = new QuoteController();
    
    if ($currentRoute === '/quotes') {
        $controller->index();
    } elseif ($currentRoute === '/quotes/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    } elseif ($currentRoute === '/quotes/details') {
        $controller->details();
    } elseif ($currentRoute === '/quotes/edit') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->edit();
        }
    } elseif ($currentRoute === '/quotes/delete') {
        $controller->delete();
    } else {
        // 404 for quotes
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Quote page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/invoices') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/InvoiceController.php';
    $controller = new InvoiceController();
    
    if ($currentRoute === '/invoices') {
        $controller->index();
    } elseif ($currentRoute === '/invoices/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    } elseif ($currentRoute === '/invoices/view') {
        $controller->details();
    } elseif ($currentRoute === '/invoices/details') {
        $controller->details();
    } elseif ($currentRoute === '/invoices/edit') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->edit();
        }
    } elseif ($currentRoute === '/invoices/delete') {
        $controller->delete();
    } elseif ($currentRoute === '/invoices/update-status') {
        $controller->updateStatus();
    } elseif ($currentRoute === '/invoices/updatePaymentStatus') {
        $controller->updatePaymentStatus();
    } else {
        // 404 for invoices
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Invoice page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/materials') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/MaterialController.php';
    $controller = new MaterialController();
    
    if ($currentRoute === '/materials') {
        $controller->index();
    } elseif ($currentRoute === '/materials/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    } elseif ($currentRoute === '/materials/edit') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->edit();
        }
    } elseif ($currentRoute === '/materials/delete') {
        $controller->delete();
    } else {
        // 404 for materials
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Material page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/technicians') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/TechniciansController.php';
    $controller = new TechniciansController();
    
    if ($currentRoute === '/technicians') {
        $controller->index();
    } elseif ($currentRoute === '/technicians/add') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->add();
        } else {
            $controller->add();
        }
    } elseif (preg_match('/\/technicians\/edit\/(\d+)/', $currentRoute, $matches)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->edit($matches[1]);
        } else {
            $controller->edit($matches[1]);
        }
    } elseif (preg_match('/\/technicians\/view\/(\d+)/', $currentRoute, $matches)) {
        $controller->view($matches[1]);
    } elseif ($currentRoute === '/technicians/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->delete();
    } elseif ($currentRoute === '/technicians/activate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->activate();
    } elseif (preg_match('/\/api\/technicians\/(\d+)/', $currentRoute, $matches)) {
        $controller->apiGet($matches[1]);
    } elseif ($currentRoute === '/api/technicians') {
        $controller->apiList();
    } else {
        // 404 for technicians
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Technician page not found</h1>";
    }
    
} elseif ($currentRoute === '/api/tasks/check-overlap' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // API endpoint for overlap checking
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    require_once 'controllers/ProjectTasksController.php';
    $controller = new ProjectTasksController();
    $controller->apiCheckOverlap();
    
} elseif ($currentRoute === '/api/tasks/check-technician-overlap') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    require_once 'controllers/ProjectTasksController.php';
    $controller = new ProjectTasksController();
    $controller->apiCheckTechnicianOverlap();
    
} elseif (strpos($currentRoute, '/users') === 0) {
    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = 'Δεν έχετε δικαίωμα πρόσβασης';
        header('Location: ?route=/dashboard');
        exit;
    }
    
    require_once 'controllers/UserController.php';
    $controller = new UserController();
    
    if ($currentRoute === '/users') {
        $controller->index();
    } elseif ($currentRoute === '/users/create') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
    } elseif ($currentRoute === '/users/edit') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->edit();
        }
    } elseif ($currentRoute === '/users/delete') {
        $controller->delete();
    } else {
        // 404 for users
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - User page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/reports') === 0) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    require_once 'controllers/ReportsController.php';
    $controller = new ReportsController();
    
    if ($currentRoute === '/reports') {
        $controller->index();
    } else {
        // 404 for reports
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Reports page not found</h1>";
    }
    
} elseif (strpos($currentRoute, '/settings') === 0) {
    // Check if user is logged in and is admin
    if (!isset($_SESSION['user_id'])) {
        header('Location: ?route=/login');
        exit;
    }
    
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = 'Δεν έχετε δικαίωμα πρόσβασης';
        header('Location: ?route=/dashboard');
        exit;
    }
    
    require_once 'controllers/SettingsController.php';
    $controller = new SettingsController();
    
    if ($currentRoute === '/settings') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->update();
        } else {
            $controller->index();
        }
    } elseif ($currentRoute === '/settings/reset-data') {
        $controller->resetData();
    } elseif ($currentRoute === '/settings/update') {
        // Update checker page - already handled separately
        require_once 'views/settings/update.php';
    } elseif ($currentRoute === '/settings/translations') {
        $controller->translations();
    } elseif ($currentRoute === '/settings/change-language') {
        $controller->changeLanguage();
    } else {
        // 404 for settings
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 - Settings page not found</h1>";
    }
    
} else {
    // 404 - Not found
    header('HTTP/1.0 404 Not Found');
    include 'views/errors/404.php';
}
?>