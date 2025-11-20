<?php
/**
 * DailyTaskMaterial Model
 * 
 * Manages materials for daily tasks
 * 
 * @package HandyCRM
 * @version 1.5.0
 */

require_once 'classes/BaseModel.php';

class DailyTaskMaterial extends BaseModel {
    protected $table = 'daily_task_materials';
    protected $primaryKey = 'id';
    
    /**
     * Get all materials for a daily task
     * 
     * @param int $dailyTaskId Daily Task ID
     * @return array
     */
    public function getByDailyTask($dailyTaskId) {
        $sql = "SELECT dtm.*, mc.name as catalog_name, mc.unit as catalog_unit 
                FROM {$this->table} dtm
                LEFT JOIN materials_catalog mc ON dtm.catalog_material_id = mc.id
                WHERE dtm.daily_task_id = ? 
                ORDER BY dtm.id ASC";
        
        try {
            $stmt = $this->db->execute($sql, [$dailyTaskId]);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("DailyTaskMaterial getByDailyTask error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create material
     * 
     * @param array $data Material data
     * @return int|false Material ID or false
     */
    public function create($data) {
        $subtotal = $this->calculateSubtotal($data['unit_price'], $data['quantity']);
        
        // Support both 'name' and 'description' field names
        $materialName = $data['name'] ?? $data['description'] ?? '';
        
        $sql = "INSERT INTO {$this->table} 
                (daily_task_id, name, description, unit, unit_price, quantity, subtotal, catalog_material_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['daily_task_id'],
            $materialName,
            $materialName, // Keep description for backward compatibility
            $data['unit'] ?? '',
            $data['unit_price'],
            $data['quantity'],
            $subtotal,
            !empty($data['catalog_material_id']) ? $data['catalog_material_id'] : null
        ];
        
        try {
            $stmt = $this->db->execute($sql, $params);
            return $stmt ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("DailyTaskMaterial create error: " . $e->getMessage());
            return false;
        }
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
        $materialName = $data['name'] ?? $data['description'] ?? '';
        
        $sql = "UPDATE {$this->table} 
                SET name = ?, 
                    description = ?,
                    unit = ?, 
                    unit_price = ?, 
                    quantity = ?, 
                    subtotal = ?,
                    catalog_material_id = ?
                WHERE id = ?";
        
        $params = [
            $materialName,
            $materialName,
            $data['unit'] ?? '',
            $data['unit_price'],
            $data['quantity'],
            $subtotal,
            !empty($data['catalog_material_id']) ? $data['catalog_material_id'] : null,
            $id
        ];
        
        try {
            $stmt = $this->db->execute($sql, $params);
            return $stmt !== false;
        } catch (Exception $e) {
            error_log("DailyTaskMaterial update error: " . $e->getMessage());
            return false;
        }
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
        return $stmt !== false;
    }
    
    /**
     * Delete all materials for a daily task
     * 
     * @param int $dailyTaskId Daily Task ID
     * @return bool
     */
    public function deleteByDailyTask($dailyTaskId) {
        $sql = "DELETE FROM {$this->table} WHERE daily_task_id = ?";
        $stmt = $this->db->execute($sql, [$dailyTaskId]);
        return $stmt !== false;
    }
    
    /**
     * Get total cost of materials for a daily task
     * 
     * @param int $dailyTaskId Daily Task ID
     * @return float
     */
    public function getTotalCost($dailyTaskId) {
        $sql = "SELECT COALESCE(SUM(subtotal), 0) as total 
                FROM {$this->table} 
                WHERE daily_task_id = ?";
        
        try {
            $stmt = $this->db->execute($sql, [$dailyTaskId]);
            $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("DailyTaskMaterial getTotalCost error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculate subtotal
     * 
     * @param float $unitPrice Unit price
     * @param float $quantity Quantity
     * @return float
     */
    private function calculateSubtotal($unitPrice, $quantity) {
        return round($unitPrice * $quantity, 2);
    }
    
    /**
     * Get material statistics for a daily task
     * 
     * @param int $dailyTaskId Daily Task ID
     * @return array
     */
    public function getStatistics($dailyTaskId) {
        $sql = "SELECT 
                    COUNT(*) as total_items,
                    COALESCE(SUM(quantity), 0) as total_quantity,
                    COALESCE(SUM(subtotal), 0) as total_cost,
                    COALESCE(AVG(unit_price), 0) as avg_unit_price
                FROM {$this->table} 
                WHERE daily_task_id = ?";
        
        try {
            $stmt = $this->db->execute($sql, [$dailyTaskId]);
            $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            return $result ?? [
                'total_items' => 0,
                'total_quantity' => 0,
                'total_cost' => 0,
                'avg_unit_price' => 0
            ];
        } catch (Exception $e) {
            error_log("DailyTaskMaterial getStatistics error: " . $e->getMessage());
            return [
                'total_items' => 0,
                'total_quantity' => 0,
                'total_cost' => 0,
                'avg_unit_price' => 0
            ];
        }
    }
}
