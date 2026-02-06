<?php
/**
 * Material Category Model
 * Handles material categories
 */

require_once 'classes/BaseModel.php';

class MaterialCategory extends BaseModel {
    protected $table = 'material_categories';
    protected $primaryKey = 'id';
    
    /**
     * Get all categories
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY name";
        $stmt = $this->execute($sql, []);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    /**
     * Get category by ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->execute($sql, [$id]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
    
    /**
     * Create new category
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, description) VALUES (?, ?)";
        $stmt = $this->execute($sql, [
            $data['name'],
            $data['description'] ?? null
        ]);
        
        return $stmt ? $this->lastInsertId() : false;
    }
    
    /**
     * Update category
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->execute($sql, [
            $data['name'],
            $data['description'] ?? null,
            $id
        ]);
        
        return $stmt && $this->rowCount($stmt) > 0;
    }
    
    /**
     * Delete category
     */
    public function delete($id) {
        // Check if category has materials
        $checkSql = "SELECT COUNT(*) as count FROM materials_catalog WHERE category_id = ?";
        $stmt = $this->execute($checkSql, [$id]);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
        
        if ($result['count'] > 0) {
            return false; // Cannot delete category with materials
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->execute($sql, [$id]);
        return $stmt && $this->rowCount($stmt) > 0;
    }
    
    /**
     * Get category with material count
     */
    public function getWithMaterialCount() {
        $sql = "SELECT mc.*, 
                       COUNT(m.id) as material_count
                FROM {$this->table} mc
                LEFT JOIN materials_catalog m ON mc.id = m.category_id
                GROUP BY mc.id
                ORDER BY mc.name";
        
        $stmt = $this->execute($sql, []);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
