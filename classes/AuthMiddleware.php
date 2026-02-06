<?php
/**
 * Authorization Middleware
 * Handles permission checks and access control
 * 
 * @package HandyCRM
 * @version 1.4.0
 */

require_once __DIR__ . '/Permission.php';

class AuthMiddleware {
    private static $instance = null;
    private $permissionModel;
    private $userPermissions = null;
    
    private function __construct() {
        $this->permissionModel = new Permission();
    }
    
    /**
     * Get singleton instance
     * @return AuthMiddleware
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new AuthMiddleware();
        }
        return self::$instance;
    }
    
    /**
     * Check if current user has permission
     * @param string $module Module name
     * @param string $action Action name
     * @return bool True if user has permission
     */
    public function checkPermission($module, $action) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->permissionModel->checkPermission($_SESSION['user_id'], $module, $action);
    }
    
    /**
     * Require permission or throw 403
     * @param string $module Module name
     * @param string $action Action name
     * @throws Exception If user doesn't have permission
     */
    public function requirePermission($module, $action) {
        if (!$this->checkPermission($module, $action)) {
            $this->accessDenied();
        }
    }
    
    /**
     * Check if current user has a specific role
     * @param string $role_name Role name (admin, supervisor, technician, etc.)
     * @return bool True if user has the role
     */
    public function hasRole($role_name) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        return $this->permissionModel->userHasRole($_SESSION['user_id'], $role_name);
    }
    
    /**
     * Check if current user is admin
     * @return bool True if user is admin
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Check if current user is supervisor or admin
     * @return bool True if user is supervisor or admin
     */
    public function isSupervisor() {
        return $this->hasRole('supervisor') || $this->isAdmin();
    }
    
    /**
     * Get all permissions for current user
     * @return array User permissions map
     */
    public function getUserPermissions() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        if ($this->userPermissions === null) {
            $this->userPermissions = $this->permissionModel->getUserPermissionsMap($_SESSION['user_id']);
        }
        
        return $this->userPermissions;
    }
    
    /**
     * Quick permission check helper
     * @param string $permission Permission in format "module.action"
     * @return bool True if user has permission
     */
    public function can($permission) {
        error_log("=== AuthMiddleware::can() called ===");
        error_log("Permission: $permission");
        error_log("User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
        error_log("Is Admin: " . ($this->isAdmin() ? 'YES' : 'NO'));
        
        if ($this->isAdmin()) {
            error_log("Result: TRUE (admin)");
            return true;
        }
        
        $parts = explode('.', $permission);
        if (count($parts) !== 2) {
            error_log("Result: FALSE (invalid format)");
            return false;
        }
        
        $result = $this->checkPermission($parts[0], $parts[1]);
        error_log("Result: " . ($result ? 'TRUE' : 'FALSE'));
        
        return $result;
    }
    
    /**
     * Check multiple permissions (OR logic)
     * @param array $permissions Array of permissions in format ["module.action", ...]
     * @return bool True if user has ANY of the permissions
     */
    public function canAny(array $permissions) {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check multiple permissions (AND logic)
     * @param array $permissions Array of permissions in format ["module.action", ...]
     * @return bool True if user has ALL permissions
     */
    public function canAll(array $permissions) {
        foreach ($permissions as $permission) {
            if (!$this->can($permission)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Handle access denied
     */
    private function accessDenied() {
        http_response_code(403);
        $_SESSION['error'] = 'Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη λειτουργία';
        
        // Redirect to dashboard or show 403 page
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            // AJAX request
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied', 'message' => 'Δεν έχετε δικαίωμα πρόσβασης']);
            exit;
        } else {
            // Regular request - redirect to dashboard
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }
    
    /**
     * Check if user owns a resource
     * @param int $resource_user_id User ID who owns the resource
     * @param bool $allow_supervisor Allow supervisors to access
     * @return bool True if user owns resource or is supervisor/admin
     */
    public function ownsResource($resource_user_id, $allow_supervisor = true) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Admin always has access
        if ($this->isAdmin()) {
            return true;
        }
        
        // Supervisor has access if allowed
        if ($allow_supervisor && $this->isSupervisor()) {
            return true;
        }
        
        // Check ownership
        return $_SESSION['user_id'] == $resource_user_id;
    }
}

/**
 * Global helper function to check permissions in views
 * @param string $permission Permission in format "module.action"
 * @return bool True if user has permission
 */
function can($permission) {
    return AuthMiddleware::getInstance()->can($permission);
}

/**
 * Global helper to check if user has role
 * @param string $role_name Role name
 * @return bool True if user has role
 */
function hasRole($role_name) {
    return AuthMiddleware::getInstance()->hasRole($role_name);
}

/**
 * Global helper to check if user is admin
 * @return bool True if user is admin
 */
function isAdmin() {
    return AuthMiddleware::getInstance()->isAdmin();
}

/**
 * Global helper to check if user is supervisor or admin
 * @return bool True if user is supervisor or admin
 */
function isSupervisor() {
    return AuthMiddleware::getInstance()->isSupervisor();
}
