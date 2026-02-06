<?php
/**
 * Role Model Class
 * Manages user roles and their permissions in the RBAC system
 * 
 * @package HandyCRM
 * @version 1.4.0
 */

require_once __DIR__ . '/Database.php';

class Role {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    /**
     * Get all roles
     * 
     * @return array List of all roles with permission counts
     */
    public function getAll() {
        $sql = "SELECT r.*, 
                COUNT(rp.permission_id) as permission_count
                FROM roles r
                LEFT JOIN role_permissions rp ON r.id = rp.role_id
                GROUP BY r.id
                ORDER BY r.is_system DESC, r.name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get role by ID with permissions
     * 
     * @param int $id Role ID
     * @return array|false Role data with permissions array
     */
    public function getById($id) {
        // Get role details
        $sql = "SELECT * FROM roles WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$role) {
            return false;
        }
        
        // Get role permissions
        $role['permissions'] = $this->getPermissions($id);
        
        return $role;
    }
    
    /**
     * Get role by name
     * 
     * @param string $name Role name
     * @return array|false Role data
     */
    public function getByName($name) {
        $sql = "SELECT * FROM roles WHERE name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new role
     * 
     * @param string $name Internal role name (lowercase, underscores)
     * @param string $display_name Human-readable role name
     * @param string $description Role description
     * @param bool $is_system Is this a system role? (cannot be deleted)
     * @return int|false New role ID or false on failure
     */
    public function create($name, $display_name, $description = null, $is_system = false) {
        // Check if role name already exists
        if ($this->getByName($name)) {
            return false;
        }
        
        $sql = "INSERT INTO roles (name, display_name, description, is_system) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $name,
            $display_name,
            $description,
            $is_system ? 1 : 0
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update role
     * 
     * @param int $id Role ID
     * @param array $data Associative array with fields to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $role = $this->getById($id);
        if (!$role) {
            return false;
        }
        
        $allowedFields = ['name', 'display_name', 'description'];
        $updates = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE roles SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete role
     * System roles cannot be deleted
     * 
     * @param int $id Role ID
     * @return bool Success status
     */
    public function delete($id) {
        $role = $this->getById($id);
        
        if (!$role) {
            return false;
        }
        
        // Cannot delete system roles
        if ($role['is_system']) {
            return false;
        }
        
        // Check if any users have this role
        $sql = "SELECT COUNT(*) FROM users WHERE role_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $userCount = $stmt->fetchColumn();
        
        if ($userCount > 0) {
            // Cannot delete role with assigned users
            return false;
        }
        
        // Delete role (permissions will be cascade deleted)
        $sql = "DELETE FROM roles WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Get number of users with a specific role
     * 
     * @param int $role_id Role ID
     * @return int Number of users with this role
     */
    public function getUsersCount($role_id) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE role_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role_id]);
        $result = $stmt->fetch();
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get permissions for a role
     * 
     * @param int $role_id Role ID
     * @return array List of permissions
     */
    public function getPermissions($role_id) {
        $sql = "SELECT p.* 
                FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
                ORDER BY p.module, p.action";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get permission IDs for a role
     * 
     * @param int $role_id Role ID
     * @return array List of permission IDs
     */
    public function getPermissionIds($role_id) {
        $sql = "SELECT permission_id FROM role_permissions WHERE role_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Assign permissions to a role
     * This replaces all existing permissions
     * 
     * @param int $role_id Role ID
     * @param array $permission_ids Array of permission IDs
     * @param int|null $user_id User making the change (for audit log)
     * @return bool Success status
     */
    public function assignPermissions($role_id, $permission_ids, $user_id = null) {
        // Get current permissions for audit log
        $oldPermissions = $this->getPermissionIds($role_id);
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Delete existing permissions
            $sql = "DELETE FROM role_permissions WHERE role_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$role_id]);
            
            // Insert new permissions
            if (!empty($permission_ids)) {
                $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                
                foreach ($permission_ids as $permission_id) {
                    $stmt->execute([$role_id, $permission_id]);
                }
            }
            
            // Log changes to audit log
            if ($user_id) {
                $this->logPermissionChanges($role_id, $oldPermissions, $permission_ids, $user_id);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Role::assignPermissions() error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log permission changes to audit log
     * 
     * @param int $role_id Role ID
     * @param array $old_permissions Old permission IDs
     * @param array $new_permissions New permission IDs
     * @param int $user_id User making the change
     */
    private function logPermissionChanges($role_id, $old_permissions, $new_permissions, $user_id) {
        $granted = array_diff($new_permissions, $old_permissions);
        $revoked = array_diff($old_permissions, $new_permissions);
        
        $sql = "INSERT INTO permission_audit_log (user_id, role_id, permission_id, action) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($granted as $permission_id) {
            $stmt->execute([$user_id, $role_id, $permission_id, 'granted']);
        }
        
        foreach ($revoked as $permission_id) {
            $stmt->execute([$user_id, $role_id, $permission_id, 'revoked']);
        }
    }
    
    /**
     * Get audit log for a role
     * 
     * @param int $role_id Role ID
     * @param int $limit Number of entries to return
     * @return array Audit log entries
     */
    public function getAuditLog($role_id, $limit = 50) {
        $sql = "SELECT 
                    pal.*,
                    u.username,
                    CONCAT(u.first_name, ' ', u.last_name) as user_full_name,
                    p.display_name as permission_name,
                    p.module,
                    p.action
                FROM permission_audit_log pal
                LEFT JOIN users u ON pal.user_id = u.id
                LEFT JOIN permissions p ON pal.permission_id = p.id
                WHERE pal.role_id = ?
                ORDER BY pal.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Assign a single permission to a role
     * 
     * @param int $role_id Role ID
     * @param int $permission_id Permission ID
     * @return bool Success status
     */
    public function assignPermission($role_id, $permission_id) {
        $sql = "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$role_id, $permission_id]);
    }
    
    /**
     * Clear all permissions for a role
     * 
     * @param int $role_id Role ID
     * @return bool Success status
     */
    public function clearPermissions($role_id) {
        $sql = "DELETE FROM role_permissions WHERE role_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$role_id]);
    }
    
    /**
     * Get role permissions (alias for getPermissions for compatibility)
     * 
     * @param int $role_id Role ID
     * @return array List of permissions
     */
    public function getRolePermissions($role_id) {
        return $this->getPermissions($role_id);
    }
}
