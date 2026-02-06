<?php
/**
 * Permission Model Class
 * Manages permissions and checks user access rights
 * 
 * @package HandyCRM
 * @version 1.4.0
 */

require_once __DIR__ . '/Database.php';

class Permission {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    /**
     * Get all permissions grouped by module
     * 
     * @return array Permissions grouped by module
     */
    public function getAll() {
        $sql = "SELECT * FROM permissions ORDER BY module, action";
        $stmt = $this->db->query($sql);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by module
        $grouped = [];
        foreach ($permissions as $permission) {
            $module = $permission['module'];
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
    
    /**
     * Get all permissions as flat list
     * 
     * @return array List of all permissions
     */
    public function getAllFlat() {
        $sql = "SELECT * FROM permissions ORDER BY module, action";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get permissions for a specific module
     * 
     * @param string $module Module name
     * @return array List of permissions for the module
     */
    public function getByModule($module) {
        $sql = "SELECT * FROM permissions WHERE module = ? ORDER BY action";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$module]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get permission by module and action
     * 
     * @param string $module Module name
     * @param string $action Action name
     * @return array|false Permission data or false
     */
    public function getByModuleAction($module, $action) {
        $sql = "SELECT * FROM permissions WHERE module = ? AND action = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$module, $action]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new permission
     * 
     * @param string $module Module name
     * @param string $action Action name
     * @param string $display_name Human-readable permission name
     * @param string $description Permission description
     * @return int|false New permission ID or false on failure
     */
    public function create($module, $action, $display_name, $description = null) {
        // Check if permission already exists
        if ($this->getByModuleAction($module, $action)) {
            return false;
        }
        
        $sql = "INSERT INTO permissions (module, action, display_name, description) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$module, $action, $display_name, $description]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Check if user has a specific permission
     * 
     * @param int $user_id User ID
     * @param string $module Module name
     * @param string $action Action name
     * @return bool True if user has permission
     */
    public function checkPermission($user_id, $module, $action) {
        $sql = "SELECT COUNT(*) 
                FROM users u
                INNER JOIN role_permissions rp ON u.role_id = rp.role_id
                INNER JOIN permissions p ON rp.permission_id = p.id
                WHERE u.id = ? AND p.module = ? AND p.action = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id, $module, $action]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get all permissions for a user
     * 
     * @param int $user_id User ID
     * @return array List of permissions the user has
     */
    public function getUserPermissions($user_id) {
        $sql = "SELECT DISTINCT p.* 
                FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                INNER JOIN users u ON rp.role_id = u.role_id
                WHERE u.id = ?
                ORDER BY p.module, p.action";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user permissions as associative array for quick lookup
     * Format: ['module.action' => true, ...]
     * 
     * @param int $user_id User ID
     * @return array Permissions map
     */
    public function getUserPermissionsMap($user_id) {
        $permissions = $this->getUserPermissions($user_id);
        $map = [];
        
        foreach ($permissions as $permission) {
            $key = $permission['module'] . '.' . $permission['action'];
            $map[$key] = true;
        }
        
        return $map;
    }
    
    /**
     * Check if user has role
     * 
     * @param int $user_id User ID
     * @param string $role_name Role name
     * @return bool True if user has the role
     */
    public function userHasRole($user_id, $role_name) {
        $sql = "SELECT COUNT(*) 
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE u.id = ? AND r.name = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id, $role_name]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get available modules
     * 
     * @return array List of unique module names
     */
    public function getModules() {
        $sql = "SELECT DISTINCT module FROM permissions ORDER BY module";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get available actions
     * 
     * @return array List of unique action names
     */
    public function getActions() {
        $sql = "SELECT DISTINCT action FROM permissions ORDER BY action";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get module display name (translated)
     * 
     * @param string $module Module name
     * @return string Display name
     */
    public function getModuleDisplayName($module) {
        $translations = [
            'users' => 'Χρήστες',
            'customers' => 'Πελάτες',
            'projects' => 'Έργα',
            'maintenances' => 'Συντηρήσεις',
            'tasks' => 'Εργασίες',
            'materials' => 'Υλικά',
            'invoices' => 'Τιμολόγια',
            'reports' => 'Αναφορές',
            'email' => 'Email',
            'settings' => 'Ρυθμίσεις'
        ];
        
        return $translations[$module] ?? ucfirst($module);
    }
    
    /**
     * Get action display name (translated)
     * 
     * @param string $action Action name
     * @return string Display name
     */
    public function getActionDisplayName($action) {
        $translations = [
            'view' => 'Προβολή',
            'create' => 'Δημιουργία',
            'edit' => 'Επεξεργασία',
            'delete' => 'Διαγραφή',
            'export' => 'Εξαγωγή',
            'send' => 'Αποστολή',
            'manage' => 'Διαχείριση',
            'financial' => 'Οικονομικές',
            'assign_tasks' => 'Ανάθεση Εργασιών'
        ];
        
        return $translations[$action] ?? ucfirst($action);
    }
}
