<?php
/**
 * Material Model
 * Manages material/inventory data
 */

class Material extends BaseModel {
    protected $table = 'materials';
    protected $primaryKey = 'id';
    
    /**
     * Get paginated materials with filters
     */
    public function getPaginated($page = 1, $perPage = ITEMS_PER_PAGE, $filters = []) {
        $database = new Database();
        $db = $database->connect();
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        // Build WHERE clause
        $whereConditions = ['1=1'];
        
        if (!empty($filters['category'])) {
            $whereConditions[] = "category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['low_stock'])) {
            $whereConditions[] = "current_stock <= min_stock";
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(name LIKE ? OR description LIKE ? OR supplier LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        try {
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get data
            $dataSql = "SELECT * FROM {$this->table} 
                        WHERE {$whereClause}
                        ORDER BY name 
                        LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $db->prepare($dataSql);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'data' => $records,
                'pagination' => [
                    'total_records' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $perPage),
                    'current_page' => $page,
                    'per_page' => $perPage
                ]
            ];
            
        } catch (PDOException $e) {
            error_log("Material getPaginated error: " . $e->getMessage());
            return [
                'data' => [],
                'pagination' => [
                    'total_records' => 0,
                    'total_pages' => 0,
                    'current_page' => 1,
                    'per_page' => $perPage
                ]
            ];
        }
    }
    
    /**
     * Get low stock materials
     */
    public function getLowStock() {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE current_stock <= min_stock 
                    ORDER BY current_stock ASC";
            
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Material getLowStock error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get material statistics
     */
    public function getStatistics() {
        $database = new Database();
        $db = $database->connect();
        
        try {
            $sql = "SELECT 
                        COUNT(*) as total_materials,
                        COALESCE(SUM(COALESCE(current_stock, 0) * COALESCE(unit_price, 0)), 0) as total_value,
                        COUNT(CASE WHEN current_stock <= min_stock THEN 1 END) as low_stock_count,
                        COUNT(CASE WHEN current_stock = 0 THEN 1 END) as out_of_stock_count
                    FROM {$this->table}
                    WHERE is_active = 1";
            
            $stmt = $db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Material getStatistics error: " . $e->getMessage());
            return [
                'total_materials' => 0,
                'total_value' => 0,
                'low_stock_count' => 0,
                'out_of_stock_count' => 0
            ];
        }
    }
    
    /**
     * Get all materials for export (no pagination)
     */
    public function getAllForExport($filters = []) {
        $database = new Database();
        $db = $database->connect();
        $params = [];
        
        // Build WHERE clause
        $whereConditions = ['1=1'];
        
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "m.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(m.name LIKE ? OR m.description LIKE ? OR m.supplier LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        try {
            $sql = "SELECT m.*, mc.name as category_name 
                    FROM {$this->table} m
                    LEFT JOIN material_categories mc ON m.category_id = mc.id
                    WHERE {$whereClause}
                    ORDER BY m.name";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Material getAllForExport error: " . $e->getMessage());
            return [];
        }
    }
}

