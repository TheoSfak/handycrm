<?php
/**
 * User Model
 * Handles user authentication and user management
 */

class User extends BaseModel {
    protected $table = 'users';
    
    /**
     * Override find to include role information
     */
    public function find($id) {
        $sql = "SELECT u.*, r.name as role, r.display_name as role_display_name
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Authenticate user login
     */
    public function authenticate($username, $password) {
        $sql = "SELECT u.*, r.name as role 
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1";
        $user = $this->db->fetchOne($sql, [$username, $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->update($user['id'], ['updated_at' => date('Y-m-d H:i:s')]);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create new user with hashed password
     */
    public function createUser($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default values
        $data['is_active'] = $data['is_active'] ?? 1;
        // No longer set default role here - must be provided via role_id
        
        return $this->create($data);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Get all technicians (including assistants)
     */
    public function getTechnicians() {
        $sql = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, u.email, u.phone, r.name as role
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE r.name IN ('technician', 'assistant', 'admin') AND u.is_active = 1 
                ORDER BY u.first_name, u.last_name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get users by role(s)
     */
    public function getByRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $placeholders = str_repeat('?,', count($roles) - 1) . '?';
        $sql = "SELECT u.id, u.first_name, u.last_name, CONCAT(u.first_name, ' ', u.last_name) as name, 
                r.name as role, u.hourly_rate, u.email, u.phone 
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE r.name IN ($placeholders) AND u.is_active = 1 
                ORDER BY u.first_name, u.last_name";
        return $this->db->fetchAll($sql, $roles);
    }
    
    /**
     * Get all active users regardless of role
     */
    public function getAllActive() {
        $sql = "SELECT u.id, u.first_name, u.last_name, CONCAT(u.first_name, ' ', u.last_name) as name, 
                r.name as role, u.hourly_rate, u.email, u.phone 
                FROM {$this->table} u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.is_active = 1 
                ORDER BY u.first_name, u.last_name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get user statistics
     */
    public function getUserStats($userId) {
        $stats = [];
        
        // Active projects
        $sql = "SELECT COUNT(*) as count FROM projects WHERE assigned_technician = ? AND status IN ('new', 'in_progress')";
        $result = $this->db->fetchOne($sql, [$userId]);
        $stats['active_projects'] = $result['count'];
        
        // Completed projects this month
        $sql = "SELECT COUNT(*) as count FROM projects 
                WHERE assigned_technician = ? 
                AND status = 'completed' 
                AND MONTH(completion_date) = MONTH(CURRENT_DATE()) 
                AND YEAR(completion_date) = YEAR(CURRENT_DATE())";
        $result = $this->db->fetchOne($sql, [$userId]);
        $stats['completed_projects_month'] = $result['count'];
        
        // Upcoming appointments today
        $sql = "SELECT COUNT(*) as count FROM appointments 
                WHERE technician_id = ? 
                AND DATE(appointment_date) = CURDATE() 
                AND status IN ('scheduled', 'confirmed')";
        $result = $this->db->fetchOne($sql, [$userId]);
        $stats['appointments_today'] = $result['count'];
        
        // Total revenue this month
        $sql = "SELECT SUM(total_cost) as total FROM projects 
                WHERE assigned_technician = ? 
                AND status = 'completed' 
                AND MONTH(completion_date) = MONTH(CURRENT_DATE()) 
                AND YEAR(completion_date) = YEAR(CURRENT_DATE())";
        $result = $this->db->fetchOne($sql, [$userId]);
        $stats['revenue_month'] = $result['total'] ?? 0;
        
        return $stats;
    }
}