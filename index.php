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
        // Database not properly set up
        die('
            <div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px; font-family: Arial;">
                <h3>Database Error</h3>
                <p>The database tables are not properly set up. Please run the installation again.</p>
                <a href="install.php" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Go to Installation</a>
            </div>
        ');
    }
} catch (Exception $e) {
    // Database connection failed - show detailed error
    die('
        <div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px; font-family: Arial;">
            <h3>Database Connection Error</h3>
            <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p>Please check your database configuration in <code>config/config.php</code></p>
            <p><strong>Common issues:</strong></p>
            <ul>
                <li>Wrong database host, username, or password</li>
                <li>Database server is not running</li>
                <li>Database does not exist</li>
            </ul>
            <a href="install.php" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Reinstall</a>
        </div>
    ');
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