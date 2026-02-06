<?php
/**
 * TaskLabor Model
 * 
 * Manages labor/technician work hours for project tasks
 * 
 * @package HandyCRM
 * @version 1.1.0
 */

require_once 'classes/BaseModel.php';

class TaskLabor extends BaseModel {
    protected $table = 'task_labor';
    protected $primaryKey = 'id';
    
    /**
     * Get all labor entries for a task
     * 
     * @param int $taskId Task ID
     * @return array
     */
    public function getByTask($taskId) {
        $sql = "SELECT * FROM {$this->table} WHERE task_id = ? ORDER BY id ASC";
        return $this->query($sql, [$taskId]);
    }
    
    /**
     * Create labor entry
     * 
     * @param array $data Labor data
     * @return int|false Labor ID or false
     */
    public function create($data) {
        // Calculate hours from time if provided
        if (!empty($data['time_from']) && !empty($data['time_to']) && empty($data['hours_worked'])) {
            $data['hours_worked'] = $this->calculateHoursFromTime($data['time_from'], $data['time_to']);
        }
        
        $subtotal = $this->calculateSubtotal($data['hours_worked'], $data['hourly_rate']);
        
        $sql = "INSERT INTO {$this->table} 
                (task_id, technician_id, technician_name, role_id, is_temporary, 
                 hours_worked, time_from, time_to, hourly_rate, subtotal, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->execute($sql, [
            $data['task_id'],
            $data['technician_id'] ?? null,
            $data['technician_name'],
            $data['role_id'] ?? null,
            $data['is_temporary'] ?? 0,
            $data['hours_worked'],
            $data['time_from'] ?? null,
            $data['time_to'] ?? null,
            $data['hourly_rate'],
            $subtotal,
            $data['notes'] ?? null
        ]);
        
        return $stmt ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update labor entry
     * 
     * @param int $id Labor ID
     * @param array $data Labor data
     * @return bool
     */
    public function update($id, $data) {
        // Calculate hours from time if provided
        if (!empty($data['time_from']) && !empty($data['time_to']) && empty($data['hours_worked'])) {
            $data['hours_worked'] = $this->calculateHoursFromTime($data['time_from'], $data['time_to']);
        }
        
        $subtotal = $this->calculateSubtotal($data['hours_worked'], $data['hourly_rate']);
        
        $sql = "UPDATE {$this->table} 
                SET technician_id = ?, technician_name = ?, role_id = ?, 
                    is_temporary = ?, hours_worked = ?, time_from = ?, time_to = ?, 
                    hourly_rate = ?, subtotal = ?, notes = ?
                WHERE id = ?";
        
        $stmt = $this->db->execute($sql, [
            $data['technician_id'] ?? null,
            $data['technician_name'],
            $data['role_id'] ?? null,
            $data['is_temporary'] ?? 0,
            $data['hours_worked'],
            $data['time_from'] ?? null,
            $data['time_to'] ?? null,
            $data['hourly_rate'],
            $subtotal,
            $data['notes'] ?? null,
            $id
        ]);
        
        return $stmt && $this->db->rowCount($stmt) > 0;
    }
    
    /**
     * Delete labor entry
     * 
     * @param int $id Labor ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->execute($sql, [$id]);
        return $stmt && $this->db->rowCount($stmt) > 0;
    }
    
    /**
     * Calculate hours from time range
     * 
     * @param string $timeFrom Start time (HH:MM:SS or HH:MM)
     * @param string $timeTo End time (HH:MM:SS or HH:MM)
     * @return float Hours as decimal
     */
    public function calculateHoursFromTime($timeFrom, $timeTo) {
        $from = new DateTime($timeFrom);
        $to = new DateTime($timeTo);
        
        // Handle overnight work (e.g., 22:00 to 06:00)
        if ($to < $from) {
            $to->modify('+1 day');
        }
        
        $interval = $from->diff($to);
        
        // Convert to decimal hours
        $hours = $interval->h + ($interval->i / 60) + ($interval->s / 3600);
        
        return round($hours, 2);
    }
    
    /**
     * Calculate subtotal
     * 
     * @param float $hours Hours worked
     * @param float $hourlyRate Hourly rate
     * @return float
     */
    public function calculateSubtotal($hours, $hourlyRate) {
        return (float)$hours * (float)$hourlyRate;
    }
    
    /**
     * Get total labor cost for task
     * 
     * @param int $taskId Task ID
     * @return float
     */
    public function getTotalForTask($taskId) {
        $sql = "SELECT SUM(subtotal) as total FROM {$this->table} WHERE task_id = ?";
        $result = $this->queryOne($sql, [$taskId]);
        return $result ? (float)($result['total'] ?? 0) : 0;
    }
    
    /**
     * Get total hours for task
     * 
     * @param int $taskId Task ID
     * @return float
     */
    public function getTotalHoursForTask($taskId) {
        $sql = "SELECT SUM(hours_worked) as total FROM {$this->table} WHERE task_id = ?";
        $result = $this->queryOne($sql, [$taskId]);
        return $result ? (float)($result['total'] ?? 0) : 0;
    }
    
    /**
     * Get labor by technician for task
     * 
     * @param int $taskId Task ID
     * @param int $technicianId Technician ID
     * @return array
     */
    public function getByTechnician($taskId, $technicianId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE task_id = ? AND technician_id = ?
                ORDER BY id ASC";
        
        return $this->query($sql, [$taskId, $technicianId]);
    }
    
    /**
     * Validate labor data
     * 
     * @param array $data Labor data
     * @return array Errors array
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['technician_name'])) {
            $errors[] = 'Το όνομα τεχνικού είναι υποχρεωτικό';
        }
        
        // Check if hours are provided or can be calculated from time
        $hasHours = !empty($data['hours_worked']) && is_numeric($data['hours_worked']);
        $hasTime = !empty($data['time_from']) && !empty($data['time_to']);
        
        if (!$hasHours && !$hasTime) {
            $errors[] = 'Πρέπει να δώσετε είτε ώρες εργασίας είτε χρόνο από-έως';
        }
        
        if ($hasHours && $data['hours_worked'] <= 0) {
            $errors[] = 'Οι ώρες εργασίας πρέπει να είναι μεγαλύτερες από μηδέν';
        }
        
        if (!isset($data['hourly_rate']) || !is_numeric($data['hourly_rate']) || $data['hourly_rate'] < 0) {
            $errors[] = 'Η τιμή ώρας πρέπει να είναι θετικός αριθμός';
        }
        
        // Validate time format if provided
        if ($hasTime) {
            if (!$this->isValidTime($data['time_from'])) {
                $errors[] = 'Μη έγκυρη ώρα έναρξης';
            }
            if (!$this->isValidTime($data['time_to'])) {
                $errors[] = 'Μη έγκυρη ώρα λήξης';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate time format
     * 
     * @param string $time Time string
     * @return bool
     */
    private function isValidTime($time) {
        return (bool)preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time);
    }
}
