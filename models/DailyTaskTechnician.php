<?php
/**
 * DailyTaskTechnician Model
 * Manages technicians assigned to daily tasks with individual hours tracking
 */

require_once 'classes/BaseModel.php';

class DailyTaskTechnician extends BaseModel {
    protected $table = 'daily_task_technicians';
    
    /**
     * Get all technicians for a specific daily task
     * 
     * @param int $dailyTaskId
     * @return array
     */
    public function getByDailyTask($dailyTaskId) {
        try {
            $sql = "SELECT dtt.*, 
                           u.username, 
                           CONCAT(u.first_name, ' ', u.last_name) as full_name,
                           u.email,
                           u.hourly_rate
                    FROM {$this->table} dtt
                    LEFT JOIN users u ON dtt.user_id = u.id
                    WHERE dtt.daily_task_id = ?
                    ORDER BY dtt.is_primary DESC, u.last_name ASC";
            
            $stmt = $this->db->execute($sql, [$dailyTaskId]);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            
        } catch (Exception $e) {
            error_log("Error fetching technicians for daily task {$dailyTaskId}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get primary technician for a daily task
     * 
     * @param int $dailyTaskId
     * @return array|null
     */
    public function getPrimaryTechnician($dailyTaskId) {
        try {
            $sql = "SELECT dtt.*, 
                           u.username, 
                           CONCAT(u.first_name, ' ', u.last_name) as full_name,
                           u.email
                    FROM {$this->table} dtt
                    LEFT JOIN users u ON dtt.user_id = u.id
                    WHERE dtt.daily_task_id = ? 
                      AND dtt.is_primary = 1
                    LIMIT 1";
            
            $stmt = $this->db->execute($sql, [$dailyTaskId]);
            return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            
        } catch (Exception $e) {
            error_log("Error fetching primary technician: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add a technician to a daily task
     * 
     * @param array $data
     * @return bool
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} 
                    (daily_task_id, user_id, hours_worked, is_primary, created_at) 
                    VALUES 
                    (?, ?, ?, ?, NOW())";
            
            $params = [
                (int)$data['daily_task_id'],
                (int)$data['user_id'],
                (float)($data['hours_worked'] ?? 0),
                (int)($data['is_primary'] ?? 0)
            ];
            
            $stmt = $this->db->execute($sql, $params);
            return $stmt !== false;
            
        } catch (Exception $e) {
            error_log("Error creating technician assignment: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Update technician hours
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET hours_worked = ?,
                        is_primary = ?
                    WHERE id = ?";
            
            $params = [
                $data['hours_worked'] ?? 0,
                $data['is_primary'] ?? 0,
                $id
            ];
            
            $stmt = $this->db->execute($sql, $params);
            return $stmt !== false;
            
        } catch (Exception $e) {
            error_log("Error updating technician: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a technician assignment
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->execute($sql, [$id]);
            return $stmt !== false;
            
        } catch (Exception $e) {
            error_log("Error deleting technician: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete all technicians for a daily task
     * 
     * @param int $dailyTaskId
     * @return bool
     */
    public function deleteByDailyTask($dailyTaskId) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE daily_task_id = ?";
            $stmt = $this->db->execute($sql, [$dailyTaskId]);
            return $stmt !== false;
            
        } catch (Exception $e) {
            error_log("Error deleting technicians for task: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get total hours worked on a daily task
     * 
     * @param int $dailyTaskId
     * @return float
     */
    public function getTotalHours($dailyTaskId) {
        try {
            $sql = "SELECT SUM(hours_worked) as total_hours 
                    FROM {$this->table} 
                    WHERE daily_task_id = ?";
            
            $stmt = $this->db->execute($sql, [$dailyTaskId]);
            $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            
            return $result ? (float)($result['total_hours'] ?? 0) : 0;
            
        } catch (Exception $e) {
            error_log("Error calculating total hours: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get statistics for a daily task
     * 
     * @param int $dailyTaskId
     * @return array
     */
    public function getStatistics($dailyTaskId) {
        try {
            $sql = "SELECT 
                        COUNT(*) as technician_count,
                        SUM(hours_worked) as total_hours,
                        AVG(hours_worked) as avg_hours,
                        MIN(hours_worked) as min_hours,
                        MAX(hours_worked) as max_hours
                    FROM {$this->table} 
                    WHERE daily_task_id = ?";
            
            $stmt = $this->db->execute($sql, [$dailyTaskId]);
            return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [];
            
        } catch (Exception $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [];
        }
    }
}
