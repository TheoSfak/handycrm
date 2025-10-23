<?php
/**
 * Project Model
 * Handles project data and related operations
 */

class Project extends BaseModel {
    protected $table = 'projects';
    
    /**
     * Get project with customer and technician details
     */
    public function getWithDetails($id) {
        $sql = "SELECT p.*, 
                       c.first_name as customer_first_name, 
                       c.last_name as customer_last_name, 
                       c.company_name as customer_company_name,
                       c.customer_type,
                       c.slug as customer_slug,
                       c.phone as customer_phone,
                       c.email as customer_email,
                       c.address as customer_address,
                       t.first_name as tech_first_name, 
                       t.last_name as tech_last_name,
                       t.phone as tech_phone,
                       creator.first_name as creator_first_name,
                       creator.last_name as creator_last_name
                FROM {$this->table} p 
                JOIN customers c ON p.customer_id = c.id 
                JOIN users t ON p.assigned_technician = t.id 
                JOIN users creator ON p.created_by = creator.id 
                WHERE p.id = ?";
        
        $project = $this->db->fetchOne($sql, [$id]);
        
        if ($project) {
            // Get project files
            $sql = "SELECT pf.*, u.first_name, u.last_name 
                    FROM project_files pf 
                    JOIN users u ON pf.uploaded_by = u.id 
                    WHERE pf.project_id = ? 
                    ORDER BY pf.uploaded_at DESC";
            $project['files'] = $this->db->fetchAll($sql, [$id]);
            
            // Get related appointments
            $sql = "SELECT a.*, u.first_name, u.last_name 
                    FROM appointments a 
                    JOIN users u ON a.technician_id = u.id 
                    WHERE a.project_id = ? 
                    ORDER BY a.appointment_date ASC";
            $project['appointments'] = $this->db->fetchAll($sql, [$id]);
        }
        
        return $project;
    }
    
    /**
     * Get projects with pagination and filters
     */
    public function getPaginated($page = 1, $perPage = ITEMS_PER_PAGE, $filters = []) {
        $database = new Database();
        $db = $database->connect();
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        // Build WHERE clause
        $whereConditions = ['1=1'];
        
        if (!empty($filters['status'])) {
            $whereConditions[] = "p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['category'])) {
            $whereConditions[] = "p.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['technician'])) {
            $whereConditions[] = "p.assigned_technician = ?";
            $params[] = $filters['technician'];
        }
        
        if (!empty($filters['customer'])) {
            $whereConditions[] = "p.customer_id = ?";
            $params[] = $filters['customer'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(p.title LIKE ? OR p.description LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        try {
            // Get total count
            $countSql = "SELECT COUNT(*) as total 
                         FROM {$this->table} p 
                         JOIN customers c ON p.customer_id = c.id 
                         WHERE {$whereClause}";
            $stmt = $db->prepare($countSql);
            $stmt->execute($params);
            $totalResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalRecords = $totalResult['total'];
            $totalPages = ceil($totalRecords / $perPage);
            
            // Get records with calculated costs from materials and labor
            $sql = "SELECT p.*, 
                           c.first_name as customer_first_name, 
                           c.last_name as customer_last_name, 
                           c.company_name as customer_company_name,
                           c.customer_type,
                           t.first_name as tech_first_name, 
                           t.last_name as tech_last_name,
                           COALESCE((SELECT SUM(tm.subtotal) 
                                     FROM task_materials tm 
                                     JOIN project_tasks pt ON tm.task_id = pt.id 
                                     WHERE pt.project_id = p.id), 0) +
                           COALESCE((SELECT SUM(tl.subtotal) 
                                     FROM task_labor tl 
                                     JOIN project_tasks pt ON tl.task_id = pt.id 
                                     WHERE pt.project_id = p.id), 0) as calculated_total_cost
                    FROM {$this->table} p 
                    JOIN customers c ON p.customer_id = c.id 
                    JOIN users t ON p.assigned_technician = t.id 
                    WHERE {$whereClause} 
                    ORDER BY p.created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'data' => $records,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_records' => $totalRecords,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ];
            
        } catch (PDOException $e) {
            error_log("Project getPaginated error: " . $e->getMessage());
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'total_records' => 0,
                    'total_pages' => 0,
                    'has_next' => false,
                    'has_prev' => false
                ]
            ];
        }
    }
    
    /**
     * Get all projects without pagination
     */
    public function getAll() {
        $sql = "SELECT p.*, 
                       c.first_name as customer_first_name, 
                       c.last_name as customer_last_name, 
                       c.company_name as customer_company_name,
                       c.customer_type,
                       t.first_name as tech_first_name, 
                       t.last_name as tech_last_name,
                       COALESCE(costs.labor_cost, 0) as calculated_labor_cost,
                       COALESCE(costs.materials_cost, 0) as calculated_materials_cost,
                       COALESCE(costs.total_cost, 0) as calculated_total_cost
                FROM {$this->table} p 
                LEFT JOIN customers c ON p.customer_id = c.id 
                LEFT JOIN users t ON p.assigned_technician = t.id
                LEFT JOIN (
                    SELECT 
                        pt.project_id,
                        SUM(tl.subtotal) as labor_cost,
                        SUM(tm.subtotal) as materials_cost,
                        SUM(COALESCE(tl.subtotal, 0) + COALESCE(tm.subtotal, 0)) as total_cost
                    FROM project_tasks pt
                    LEFT JOIN task_labor tl ON pt.id = tl.task_id
                    LEFT JOIN task_materials tm ON pt.id = tm.task_id
                    GROUP BY pt.project_id
                ) costs ON p.id = costs.project_id
                ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Update project costs and calculate total
     */
    public function updateCosts($id, $materialCost, $laborCost, $vatRate = null) {
        $vatRate = $vatRate ?: DEFAULT_VAT_RATE;
        
        $subtotal = $materialCost + $laborCost;
        $vatAmount = $subtotal * ($vatRate / 100);
        $totalCost = $subtotal + $vatAmount;
        
        return $this->update($id, [
            'material_cost' => $materialCost,
            'labor_cost' => $laborCost,
            'vat_rate' => $vatRate,
            'total_cost' => $totalCost
        ]);
    }
    
    /**
     * Get projects by customer
     */
    public function getByCustomer($customerId) {
        $sql = "SELECT p.*, u.first_name, u.last_name 
                FROM {$this->table} p 
                JOIN users u ON p.assigned_technician = u.id 
                WHERE p.customer_id = ? 
                ORDER BY p.created_at DESC";
        return $this->db->fetchAll($sql, [$customerId]);
    }
    
    /**
     * Get project statistics
     */
    public function getStats() {
        $stats = [];
        
        // Project status breakdown
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $statusBreakdown = $this->db->fetchAll($sql);
        $stats['status_breakdown'] = [];
        foreach ($statusBreakdown as $item) {
            $stats['status_breakdown'][$item['status']] = $item['count'];
        }
        
        // Category breakdown
        $sql = "SELECT category, COUNT(*) as count FROM {$this->table} GROUP BY category";
        $categoryBreakdown = $this->db->fetchAll($sql);
        $stats['category_breakdown'] = [];
        foreach ($categoryBreakdown as $item) {
            $stats['category_breakdown'][$item['category']] = $item['count'];
        }
        
        // This month's revenue
        $sql = "SELECT SUM(total_cost) as total FROM {$this->table} 
                WHERE status = 'completed' 
                AND MONTH(completion_date) = MONTH(CURRENT_DATE()) 
                AND YEAR(completion_date) = YEAR(CURRENT_DATE())";
        $result = $this->db->fetchOne($sql);
        $stats['revenue_month'] = $result['total'] ?? 0;
        
        // Active projects
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status IN ('new', 'in_progress')";
        $result = $this->db->fetchOne($sql);
        $stats['active_projects'] = $result['count'];
        
        return $stats;
    }
    
    /**
     * Get recent projects
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT p.id, p.title, p.category, p.status, p.created_at,
                       c.first_name as customer_first_name, 
                       c.last_name as customer_last_name, 
                       c.company_name as customer_company_name,
                       c.customer_type
                FROM {$this->table} p 
                JOIN customers c ON p.customer_id = c.id 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Add project file
     */
    public function addFile($projectId, $fileData, $uploadedBy, $description = '') {
        $data = [
            'project_id' => $projectId,
            'filename' => $fileData['filename'],
            'original_filename' => $fileData['original_name'],
            'file_type' => $this->getFileType($fileData['filename']),
            'file_size' => $fileData['size'],
            'file_path' => $fileData['path'],
            'description' => $description,
            'uploaded_by' => $uploadedBy
        ];
        
        $sql = "INSERT INTO project_files (project_id, filename, original_filename, file_type, file_size, file_path, description, uploaded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, array_values($data));
    }
    
    /**
     * Determine file type based on extension
     */
    private function getFileType($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return 'image';
        } elseif ($ext === 'pdf') {
            return 'pdf';
        } elseif (in_array($ext, ['doc', 'docx', 'txt', 'xls', 'xlsx'])) {
            return 'document';
        } else {
            return 'other';
        }
    }
    
    /**
     * Get project by slug
     */
    public function getBySlug($slug) {
        $sql = "SELECT p.*, 
                       c.first_name as customer_first_name, 
                       c.last_name as customer_last_name, 
                       c.company_name as customer_company_name,
                       c.customer_type,
                       c.slug as customer_slug,
                       c.phone as customer_phone,
                       c.email as customer_email,
                       c.address as customer_address,
                       t.first_name as tech_first_name, 
                       t.last_name as tech_last_name,
                       t.phone as tech_phone,
                       creator.first_name as creator_first_name,
                       creator.last_name as creator_last_name
                FROM {$this->table} p 
                JOIN customers c ON p.customer_id = c.id 
                JOIN users t ON p.assigned_technician = t.id 
                JOIN users creator ON p.created_by = creator.id 
                WHERE p.slug = ?";
        
        $project = $this->db->fetchOne($sql, [$slug]);
        
        if ($project) {
            // Get project files
            $sql = "SELECT pf.*, u.first_name, u.last_name 
                    FROM project_files pf 
                    JOIN users u ON pf.uploaded_by = u.id 
                    WHERE pf.project_id = ? 
                    ORDER BY pf.uploaded_at DESC";
            $project['files'] = $this->db->fetchAll($sql, [$project['id']]);
            
            // Get related appointments
            $sql = "SELECT a.*, u.first_name, u.last_name 
                    FROM appointments a 
                    JOIN users u ON a.technician_id = u.id 
                    WHERE a.project_id = ? 
                    ORDER BY a.appointment_date ASC";
            $project['appointments'] = $this->db->fetchAll($sql, [$project['id']]);
        }
        
        return $project;
    }
    
    /**
     * Generate and save slug for project
     */
    public function generateSlug($id, $title) {
        require_once __DIR__ . '/../helpers/SlugHelper.php';
        
        $slug = SlugHelper::createUniqueSlug($title, $this->table, $id);
        
        $sql = "UPDATE {$this->table} SET slug = ? WHERE id = ?";
        $this->db->execute($sql, [$slug, $id]);
        
        return $slug;
    }
}