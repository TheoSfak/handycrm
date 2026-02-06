<?php
/**
 * DailyTask Model
 * Handles daily task records
 */

require_once 'classes/BaseModel.php';

class DailyTask extends BaseModel {
    protected $table = 'daily_tasks';
    
    /**
     * Get all daily tasks with pagination and filters
     */
    public function getAll($page = 1, $perPage = 20, $search = null, $dateFrom = null, $dateTo = null, $taskType = null, $technicianId = null, $isInvoiced = null, $status = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT dt.*, 
                CONCAT(u.first_name, ' ', u.last_name) as technician_name,
                CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name
                FROM {$this->table} dt
                LEFT JOIN users u ON dt.technician_id = u.id
                LEFT JOIN users creator ON dt.created_by = creator.id
                WHERE dt.deleted_at IS NULL";
        
        $params = [];
        
        // Search filter
        if ($search) {
            $sql .= " AND (dt.customer_name LIKE ? OR dt.phone LIKE ? OR dt.address LIKE ? OR dt.description LIKE ? OR dt.task_number LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Date range filter
        if ($dateFrom) {
            $sql .= " AND dt.date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND dt.date <= ?";
            $params[] = $dateTo;
        }
        
        // Task type filter
        if ($taskType !== null && $taskType !== '') {
            $sql .= " AND dt.task_type = ?";
            $params[] = $taskType;
        }
        
        // Technician filter
        if ($technicianId !== null && $technicianId !== '') {
            $sql .= " AND dt.technician_id = ?";
            $params[] = $technicianId;
        }
        
        // Invoiced filter
        if ($isInvoiced !== null && $isInvoiced !== '') {
            $sql .= " AND dt.is_invoiced = ?";
            $params[] = (int)$isInvoiced;
        }
        
        // Status filter
        if ($status !== null && $status !== '') {
            $sql .= " AND dt.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY dt.date DESC, dt.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total count with filters
     */
    public function getTotalCount($search = null, $dateFrom = null, $dateTo = null, $taskType = null, $technicianId = null, $isInvoiced = null, $status = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];
        
        if ($search) {
            $sql .= " AND (customer_name LIKE ? OR phone LIKE ? OR address LIKE ? OR description LIKE ? OR task_number LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($dateFrom) {
            $sql .= " AND date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND date <= ?";
            $params[] = $dateTo;
        }
        
        if ($taskType !== null && $taskType !== '') {
            $sql .= " AND task_type = ?";
            $params[] = $taskType;
        }
        
        if ($technicianId !== null && $technicianId !== '') {
            $sql .= " AND technician_id = ?";
            $params[] = $technicianId;
        }
        
        if ($isInvoiced !== null && $isInvoiced !== '') {
            $sql .= " AND is_invoiced = ?";
            $params[] = (int)$isInvoiced;
        }
        
        if ($status !== null && $status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'];
    }
    
    /**
     * Generate unique task number
     */
    private function generateTaskNumber() {
        $year = date('Y');
        $prefix = "DT-{$year}-";
        
        // Get last task number for this year
        $sql = "SELECT task_number FROM {$this->table} 
                WHERE task_number LIKE ? 
                ORDER BY task_number DESC 
                LIMIT 1";
        
        $lastTask = $this->db->fetchOne($sql, ["{$prefix}%"]);
        
        if ($lastTask) {
            // Extract number and increment
            $lastNumber = (int)str_replace($prefix, '', $lastTask['task_number']);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Create new daily task
     */
    public function create($data) {
        // Generate task number
        $taskNumber = $this->generateTaskNumber();
        
        // Calculate hours from time range if provided
        if (!empty($data['time_from']) && !empty($data['time_to'])) {
            $timeFrom = new DateTime($data['time_from']);
            $timeTo = new DateTime($data['time_to']);
            $interval = $timeFrom->diff($timeTo);
            $data['hours_worked'] = $interval->h + ($interval->i / 60);
        }
        
        // Encode JSON fields
        $additionalTechnicians = !empty($data['additional_technicians']) ? json_encode($data['additional_technicians']) : null;
        $photos = !empty($data['photos']) ? json_encode($data['photos']) : null;
        
        $sql = "INSERT INTO {$this->table} (
            task_number, date, customer_name, address, phone, task_type,
            description, hours_worked, time_from, time_to, materials,
            is_invoiced, technician_id, additional_technicians, notes, photos, status, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $taskNumber,
            $data['date'],
            $data['customer_name'],
            $data['address'] ?? null,
            $data['phone'] ?? null,
            $data['task_type'],
            $data['description'],
            $data['hours_worked'] ?? null,
            $data['time_from'] ?? null,
            $data['time_to'] ?? null,
            $data['materials'] ?? null,
            $data['is_invoiced'] ?? 0,
            $data['technician_id'],
            $additionalTechnicians,
            $data['notes'] ?? null,
            $photos,
            $data['status'] ?? 'completed',
            $data['created_by']
        ];
        
        $stmt = $this->db->execute($sql, $params);
        return $stmt ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update daily task
     */
    public function update($id, $data) {
        // Calculate hours from time range if provided
        if (!empty($data['time_from']) && !empty($data['time_to'])) {
            $timeFrom = new DateTime($data['time_from']);
            $timeTo = new DateTime($data['time_to']);
            $interval = $timeFrom->diff($timeTo);
            $data['hours_worked'] = $interval->h + ($interval->i / 60);
        }
        
        // Encode JSON fields
        $additionalTechnicians = !empty($data['additional_technicians']) ? json_encode($data['additional_technicians']) : null;
        $photos = !empty($data['photos']) ? json_encode($data['photos']) : null;
        
        $sql = "UPDATE {$this->table} SET
            date = ?,
            customer_name = ?,
            address = ?,
            phone = ?,
            task_type = ?,
            description = ?,
            hours_worked = ?,
            time_from = ?,
            time_to = ?,
            materials = ?,
            technician_id = ?,
            additional_technicians = ?,
            notes = ?,
            photos = ?,
            status = ?
            WHERE id = ?";
        
        $params = [
            $data['date'],
            $data['customer_name'],
            $data['address'] ?? null,
            $data['phone'] ?? null,
            $data['task_type'],
            $data['description'],
            $data['hours_worked'] ?? null,
            $data['time_from'] ?? null,
            $data['time_to'] ?? null,
            $data['materials'] ?? null,
            $data['technician_id'],
            $additionalTechnicians,
            $data['notes'] ?? null,
            $photos,
            $data['status'] ?? 'completed',
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Get task by ID
     */
    public function find($id) {
        $sql = "SELECT dt.*, 
                CONCAT(u.first_name, ' ', u.last_name) as technician_name,
                CONCAT(creator.first_name, ' ', creator.last_name) as created_by_name
                FROM {$this->table} dt
                LEFT JOIN users u ON dt.technician_id = u.id
                LEFT JOIN users creator ON dt.created_by = creator.id
                WHERE dt.id = ?";
        
        $task = $this->db->fetchOne($sql, [$id]);
        
        if ($task) {
            // Decode JSON fields
            $task['additional_technicians'] = $task['additional_technicians'] ? json_decode($task['additional_technicians'], true) : [];
            $task['photos'] = $task['photos'] ? json_decode($task['photos'], true) : [];
        }
        
        return $task;
    }
    
    /**
     * Delete task
     */
    public function delete($id) {
        // Get photos before deletion
        $task = $this->find($id);
        
        // Delete photo files
        if ($task && !empty($task['photos'])) {
            foreach ($task['photos'] as $photo) {
                $photoPath = __DIR__ . '/../' . $photo;
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Get tasks by technician
     */
    public function getByTechnician($technicianId, $limit = 10) {
        $sql = "SELECT dt.*, 
                CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM {$this->table} dt
                LEFT JOIN users u ON dt.technician_id = u.id
                WHERE dt.technician_id = ?
                ORDER BY dt.date DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$technicianId, $limit]);
    }
    
    /**
     * Get today's tasks
     */
    public function getToday() {
        $sql = "SELECT dt.*, 
                CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM {$this->table} dt
                LEFT JOIN users u ON dt.technician_id = u.id
                WHERE dt.date = CURDATE()
                ORDER BY dt.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Update invoiced status
     */
    public function updateInvoicedStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET is_invoiced = ? WHERE id = ?";
        return $this->db->execute($sql, [$status ? 1 : 0, $id]);
    }
    
    /**
     * Get statistics
     */
    public function getStats($userId = null) {
        $where = $userId ? "WHERE technician_id = ?" : "";
        $params = $userId ? [$userId] : [];
        
        $sql = "SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN is_invoiced = 0 THEN 1 ELSE 0 END) as uninvoiced_tasks,
                SUM(CASE WHEN date = CURDATE() THEN 1 ELSE 0 END) as today_tasks,
                SUM(CASE WHEN MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as month_tasks,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as pending_tasks
                FROM {$this->table}
                {$where}";
        
        return $this->db->fetchOne($sql, $params);
    }
}
