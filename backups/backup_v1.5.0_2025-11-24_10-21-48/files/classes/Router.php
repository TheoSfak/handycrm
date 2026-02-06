<?php
/**
 * Router Class
 * Handles URL routing and dispatching to appropriate controllers
 */

class Router {
    private $routes = [];
    private $currentRoute = '';
    
    /**
     * Add route
     */
    public function add($route, $controller, $action = 'index', $method = 'GET') {
        $this->routes[] = [
            'route' => $route,
            'controller' => $controller,
            'action' => $action,
            'method' => strtoupper($method)
        ];
    }
    
    /**
     * Add GET route
     */
    public function get($route, $controller, $action = 'index') {
        $this->add($route, $controller, $action, 'GET');
    }
    
    /**
     * Add POST route
     */
    public function post($route, $controller, $action = 'index') {
        $this->add($route, $controller, $action, 'POST');
    }
    
    /**
     * Dispatch request to appropriate controller
     */
    public function dispatch() {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Remove query string from URI
        $requestUri = strtok($requestUri, '?');
        
        // Remove base path if running in subdirectory
        $basePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', APP_ROOT);
        if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        // Remove trailing slash
        $requestUri = rtrim($requestUri, '/');
        if (empty($requestUri)) {
            $requestUri = '/';
        }
        
        $this->currentRoute = $requestUri;
        
        // Find matching route
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route['route'], $requestUri) && $route['method'] === $requestMethod) {
                $this->executeRoute($route, $requestUri);
                return;
            }
        }
        
        // No route found - 404
        $this->handleNotFound();
    }
    
    /**
     * Check if route matches current URI
     */
    private function matchRoute($routePattern, $requestUri) {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePattern);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestUri);
    }
    
    /**
     * Execute matched route
     */
    private function executeRoute($route, $requestUri) {
        // Extract parameters from URL
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['route']);
        $pattern = '#^' . $pattern . '$#';
        
        preg_match($pattern, $requestUri, $matches);
        array_shift($matches); // Remove full match
        
        // Include controller file
        $controllerFile = APP_ROOT . '/controllers/' . $route['controller'] . '.php';
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller not found: {$route['controller']}");
        }
        
        require_once $controllerFile;
        
        // Instantiate controller
        $controllerClass = $route['controller'];
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller class not found: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
        // Check if action exists
        $action = $route['action'];
        if (!method_exists($controller, $action)) {
            throw new Exception("Action not found: {$controllerClass}::{$action}");
        }
        
        // Call action with parameters
        call_user_func_array([$controller, $action], $matches);
    }
    
    /**
     * Handle 404 - Not Found
     */
    private function handleNotFound() {
        http_response_code(404);
        
        // Try to load 404 view
        $errorFile = APP_ROOT . '/views/errors/404.php';
        if (file_exists($errorFile)) {
            include APP_ROOT . '/views/includes/header.php';
            include $errorFile;
            include APP_ROOT . '/views/includes/footer.php';
        } else {
            echo "<h1>404 - Page Not Found</h1>";
            echo "<p>The requested page could not be found.</p>";
        }
    }
    
    /**
     * Get current route
     */
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    /**
     * Generate URL for route
     */
    public function url($route, $params = []) {
        $url = $route;
        
        // Replace parameters in route
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return APP_URL . $url;
    }
}