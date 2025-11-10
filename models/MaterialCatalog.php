<?php
/**
 * Material Catalog Model
 * Handles materials catalog
 */

require_once 'classes/BaseModel.php';

class MaterialCatalog extends BaseModel {
    protected $table = 'materials_catalog';
    protected $primaryKey = 'id';
    
    /**
     * Get all materials with optional filters and pagination
     */
    public function getAll($filters = []) {
        $sql = "SELECT mc.*, mcat.name as category_name 
                FROM {$this->table} mc
                LEFT JOIN material_categories mcat ON mc.category_id = mcat.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND mc.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (mc.name LIKE ? OR mc.description LIKE ? OR mc.supplier LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND mc.is_active = ?";
            $params[] = $filters['is_active'];
        } else {
            $sql .= " AND mc.is_active = 1"; // Default: only active materials
        }
        
        $sql .= " ORDER BY mcat.name, mc.name";
        
        // Add pagination if specified
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT " . (int)$filters['limit'] . " OFFSET " . (int)$filters['offset'];
        }
        
        $stmt = $this->execute($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    /**
     * Get total count of materials with filters (for pagination)
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} mc
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND mc.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (mc.name LIKE ? OR mc.description LIKE ? OR mc.supplier LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND mc.is_active = ?";
            $params[] = $filters['is_active'];
        } else {
            $sql .= " AND mc.is_active = 1";
        }
        
        $stmt = $this->execute($sql, $params);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return (int)$result['total'];
    }
    
    /**
     * Search materials for autocomplete (case-insensitive, searches in name + aliases)
     */
    public function search($query, $limit = 10) {
        $sql = "SELECT mc.id, mc.name, mc.unit, mc.default_price, mcat.name as category_name
                FROM {$this->table} mc
                LEFT JOIN material_categories mcat ON mc.category_id = mcat.id
                WHERE mc.is_active = 1 
                AND (
                    LOWER(mc.name) LIKE LOWER(?) 
                    OR LOWER(mc.aliases) LIKE LOWER(?)
                )
                ORDER BY 
                    CASE 
                        WHEN LOWER(mc.name) LIKE LOWER(?) THEN 1  -- Exact match in name first
                        WHEN LOWER(mc.name) LIKE LOWER(?) THEN 2  -- Starts with query in name
                        WHEN LOWER(mc.aliases) LIKE LOWER(?) THEN 3  -- Match in aliases
                        ELSE 4                                      -- Contains in name
                    END,
                    mc.name
                LIMIT " . (int)$limit;
        
        $searchPattern = '%' . $query . '%';
        $startsWithPattern = $query . '%';
        
        $stmt = $this->execute($sql, [
            $searchPattern,      // WHERE name condition
            $searchPattern,      // WHERE aliases condition
            $query,              // ORDER BY exact match
            $startsWithPattern,  // ORDER BY starts with
            $searchPattern       // ORDER BY aliases match
        ]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    /**
     * Get material by ID
     */
    public function getById($id) {
        $sql = "SELECT mc.*, mcat.name as category_name 
                FROM {$this->table} mc
                LEFT JOIN material_categories mcat ON mc.category_id = mcat.id
                WHERE mc.id = ?";
        
        $stmt = $this->execute($sql, [$id]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }
    
    /**
     * Create new material
     */
    public function create($data) {
        // Auto-generate aliases if not provided
        require_once 'classes/MaterialAliasGenerator.php';
        
        if (empty($data['aliases'])) {
            $data['aliases'] = MaterialAliasGenerator::generate($data['name']);
        }
        
        $sql = "INSERT INTO {$this->table} 
                (category_id, name, description, unit, default_price, supplier, notes, aliases, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->execute($sql, [
            $data['category_id'] ?? null,
            $data['name'],
            $data['description'] ?? null,
            $data['unit'] ?? null,
            $data['default_price'] ?? null,
            $data['supplier'] ?? null,
            $data['notes'] ?? null,
            $data['aliases'] ?? null,
            isset($data['is_active']) ? $data['is_active'] : 1
        ]);
        
        return $stmt ? $this->lastInsertId() : false;
    }
    
    /**
     * Update material
     */
    public function update($id, $data) {
        // Auto-regenerate aliases if name changed and aliases not manually set
        require_once 'classes/MaterialAliasGenerator.php';
        
        if (empty($data['aliases']) && !empty($data['name'])) {
            $data['aliases'] = MaterialAliasGenerator::generate($data['name']);
        }
        
        $sql = "UPDATE {$this->table} 
                SET category_id = ?, name = ?, description = ?, unit = ?, 
                    default_price = ?, supplier = ?, notes = ?, aliases = ?, is_active = ?
                WHERE id = ?";
        
        $stmt = $this->execute($sql, [
            $data['category_id'] ?? null,
            $data['name'],
            $data['description'] ?? null,
            $data['unit'] ?? null,
            $data['default_price'] ?? null,
            $data['supplier'] ?? null,
            $data['notes'] ?? null,
            $data['aliases'] ?? null,
            isset($data['is_active']) ? $data['is_active'] : 1,
            $id
        ]);
        
        return $stmt && $this->rowCount($stmt) > 0;
    }
    
    /**
     * Delete material
     */
    public function delete($id) {
        // Check if material is used in any tasks
        $checkSql = "SELECT COUNT(*) as count FROM task_materials WHERE catalog_material_id = ?";
        $stmt = $this->execute($checkSql, [$id]);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
        
        if ($result['count'] > 0) {
            // Don't delete, just deactivate
            $material = $this->getById($id);
            if ($material) {
                $material['is_active'] = 0;
                return $this->update($id, $material);
            }
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->execute($sql, [$id]);
        return $stmt && $this->rowCount($stmt) > 0;
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_materials,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_materials,
                    COUNT(DISTINCT category_id) as total_categories
                FROM {$this->table}";
        
        $stmt = $this->execute($sql, []);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total_materials' => 0, 'active_materials' => 0, 'total_categories' => 0];
    }
    
    /**
     * Get most used materials
     */
    public function getMostUsed($limit = 10) {
        $sql = "SELECT mc.*, COUNT(tm.id) as usage_count
                FROM {$this->table} mc
                INNER JOIN task_materials tm ON mc.id = tm.catalog_material_id
                WHERE mc.is_active = 1
                GROUP BY mc.id
                ORDER BY usage_count DESC
                LIMIT " . (int)$limit;
        
        $stmt = $this->execute($sql, []);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    /**
     * Update material status (active/inactive)
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET is_active = ? WHERE id = ?";
        $stmt = $this->execute($sql, [$status, $id]);
        return $stmt !== false;
    }
    
    /**
     * Find duplicate materials based on name
     * Returns groups of materials with similar names
     */
    public function findDuplicates() {
        try {
            // Find materials with exact same name (case-insensitive)
            $sql = "SELECT LOWER(TRIM(name)) as normalized_name, 
                           GROUP_CONCAT(id ORDER BY id) as ids, 
                           COUNT(*) as count
                    FROM {$this->table}
                    GROUP BY normalized_name
                    HAVING count > 1
                    ORDER BY count DESC, normalized_name";
            
            $stmt = $this->execute($sql, []);
            if (!$stmt) {
                return [];
            }
            
            $duplicateGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $duplicates = [];
            
            foreach ($duplicateGroups as $group) {
                $ids = explode(',', $group['ids']);
                
                // Get full details for each material in this duplicate group
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $detailSql = "SELECT mc.*, cat.name as category_name 
                             FROM {$this->table} mc
                             LEFT JOIN material_categories cat ON mc.category_id = cat.id
                             WHERE mc.id IN ($placeholders)
                             ORDER BY mc.id";
                
                $detailStmt = $this->execute($detailSql, $ids);
                if (!$detailStmt) {
                    continue;
                }
                
                $materials = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($materials) > 1) {
                    $simplifiedMaterials = [];
                    foreach ($materials as $material) {
                        $simplifiedMaterials[] = [
                            'id' => $material['id'],
                            'name' => $material['name'],
                            'sku' => $material['sku'] ?? '',
                            'category' => $material['category_name'] ?? 'N/A',
                            'price' => $material['price'] ?? null
                        ];
                    }
                    
                    $duplicates[] = [
                        'name' => $materials[0]['name'], // Use actual name, not normalized
                        'count' => count($materials),
                        'materials' => $simplifiedMaterials
                    ];
                }
            }
            
            return $duplicates;
            
        } catch (Exception $e) {
            error_log("MaterialCatalog findDuplicates error: " . $e->getMessage());
            return [];
        }
    }
}
