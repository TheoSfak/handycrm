<?php
/**
 * TaskMaterial Model
 * 
 * Manages materials for project tasks
 * 
 * @package HandyCRM
 * @version 1.1.0
 */

require_once 'classes/BaseModel.php';

class TaskMaterial extends BaseModel {
    protected $table = 'task_materials';
    protected $primaryKey = 'id';
    
    /**
     * Get all materials for a task
     * 
     * @param int $taskId Task ID
     * @return array
     */
    public function getByTask($taskId) {
        $sql = "SELECT * FROM {$this->table} WHERE task_id = ? ORDER BY id ASC";
        return $this->query($sql, [$taskId]);
    }
    
    /**
     * Create material
     * 
     * @param array $data Material data
     * @return int|false Material ID or false
     */
    public function create($data) {
        $subtotal = $this->calculateSubtotal($data['unit_price'], $data['quantity']);
        
        $sql = "INSERT INTO {$this->table} 
                (task_id, description, unit_price, quantity, unit_type, subtotal) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->execute($sql, [
            $data['task_id'],
            $data['description'],
            $data['unit_price'],
            $data['quantity'],
            $data['unit_type'],
            $subtotal
        ]);
        
        return $stmt ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update material
     * 
     * @param int $id Material ID
     * @param array $data Material data
     * @return bool
     */
    public function update($id, $data) {
        $subtotal = $this->calculateSubtotal($data['unit_price'], $data['quantity']);
        
        $sql = "UPDATE {$this->table} 
                SET description = ?, unit_price = ?, quantity = ?, unit_type = ?, subtotal = ?
                WHERE id = ?";
        
        $stmt = $this->db->execute($sql, [
            $data['description'],
            $data['unit_price'],
            $data['quantity'],
            $data['unit_type'],
            $subtotal,
            $id
        ]);
        
        return $stmt && $this->db->rowCount($stmt) > 0;
    }
    
    /**
     * Delete material
     * 
     * @param int $id Material ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->execute($sql, [$id]);
        return $stmt && $this->db->rowCount($stmt) > 0;
    }
    
    /**
     * Calculate subtotal
     * 
     * @param float $unitPrice Unit price
     * @param float $quantity Quantity
     * @return float
     */
    public function calculateSubtotal($unitPrice, $quantity) {
        return (float)$unitPrice * (float)$quantity;
    }
    
    /**
     * Get total materials cost for task
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
     * Validate material data
     * 
     * @param array $data Material data
     * @return array Errors array
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['description'])) {
            $errors[] = 'Η περιγραφή υλικού είναι υποχρεωτική';
        }
        
        if (!isset($data['unit_price']) || !is_numeric($data['unit_price']) || $data['unit_price'] < 0) {
            $errors[] = 'Η τιμή μονάδος πρέπει να είναι θετικός αριθμός';
        }
        
        if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            $errors[] = 'Η ποσότητα πρέπει να είναι μεγαλύτερη από μηδέν';
        }
        
        if (empty($data['unit_type'])) {
            $errors[] = 'Η μονάδα μέτρησης είναι υποχρεωτική';
        }
        
        return $errors;
    }
}
