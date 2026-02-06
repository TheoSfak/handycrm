<?php
/**
 * Technician Model
 * 
 * Manages technicians and assistants with their hourly rates
 * Used for project task labor tracking
 * 
 * @package HandyCRM
 * @version 1.1.0
 */

require_once 'classes/BaseModel.php';

class Technician extends BaseModel {
    protected $table = 'technicians';
    protected $primaryKey = 'id';
    
    /**
     * Get all technicians
     * 
     * @param bool $includeInactive Include inactive technicians
     * @return array
     */
    public function getAll($includeInactive = false) {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!$includeInactive) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY name ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Get technician by ID
     * 
     * @param int $id Technician ID
     * @return array|null
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->query($sql, [$id]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get technicians by role
     * 
     * @param string $role 'technician' or 'assistant'
     * @param bool $activeOnly Only active technicians
     * @return array
     */
    public function getByRole($role, $activeOnly = true) {
        $sql = "SELECT * FROM {$this->table} WHERE role = ?";
        $params = [$role];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY name ASC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get only active technicians
     * 
     * @return array
     */
    public function getActive() {
        return $this->getAll(false);
    }
    
    /**
     * Create new technician
     * 
     * @param array $data Technician data
     * @return int|false Last insert ID or false on failure
     */
    public function create($data) {
        $fields = ['name', 'role', 'hourly_rate', 'phone', 'email', 'is_active', 'notes'];
        $values = [];
        $placeholders = [];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $values[] = $data[$field];
                $placeholders[] = '?';
            }
        }
        
        if (empty($values)) {
            return false;
        }
        
        $fieldsList = implode(', ', $fields);
        $placeholdersList = implode(', ', $placeholders);
        
        $sql = "INSERT INTO {$this->table} 
                (name, role, hourly_rate, phone, email, is_active, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->execute($sql, [
            $data['name'] ?? '',
            $data['role'] ?? 'technician',
            $data['hourly_rate'] ?? 0.00,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['is_active'] ?? 1,
            $data['notes'] ?? null
        ]);
        
        return $stmt ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update technician
     * 
     * @param int $id Technician ID
     * @param array $data Updated data
     * @return bool
     */
    public function update($id, $data) {
        $allowedFields = ['name', 'role', 'hourly_rate', 'phone', 'email', 'is_active', 'notes'];
        $setClause = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $setClause[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($setClause)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db->execute($sql, $values);
        return $stmt && $this->db->rowCount($stmt) > 0;
    }
    
    /**
     * Deactivate technician (soft delete)
     * 
     * @param int $id Technician ID
     * @return bool
     */
    public function deactivate($id) {
        return $this->update($id, ['is_active' => 0]);
    }
    
    /**
     * Activate technician
     * 
     * @param int $id Technician ID
     * @return bool
     */
    public function activate($id) {
        return $this->update($id, ['is_active' => 1]);
    }
    
    /**
     * Delete technician permanently
     * 
     * @param int $id Technician ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->execute($sql, [$id]);
        return $stmt && $this->db->rowCount($stmt) > 0;
    }
    
    /**
     * Get total hours worked by technician
     * 
     * @param int $technicianId Technician ID
     * @param string|null $dateFrom Start date (Y-m-d)
     * @param string|null $dateTo End date (Y-m-d)
     * @return float
     */
    public function getTotalHours($technicianId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT SUM(tl.hours_worked) as total_hours
                FROM task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                WHERE tl.technician_id = ?";
        
        $params = [$technicianId];
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR
                (pt.task_type = 'date_range' AND (
                    (pt.date_from BETWEEN ? AND ?) OR 
                    (pt.date_to BETWEEN ? AND ?) OR
                    (pt.date_from <= ? AND pt.date_to >= ?)
                ))
            )";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        
        $result = $this->query($sql, $params);
        return $result ? (float)($result[0]['total_hours'] ?? 0) : 0;
    }
    
    /**
     * Get total earnings for technician
     * 
     * @param int $technicianId Technician ID
     * @param string|null $dateFrom Start date (Y-m-d)
     * @param string|null $dateTo End date (Y-m-d)
     * @return float
     */
    public function getTotalEarnings($technicianId, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT SUM(tl.subtotal) as total_earnings
                FROM task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                WHERE tl.technician_id = ?";
        
        $params = [$technicianId];
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR
                (pt.task_type = 'date_range' AND (
                    (pt.date_from BETWEEN ? AND ?) OR 
                    (pt.date_to BETWEEN ? AND ?) OR
                    (pt.date_from <= ? AND pt.date_to >= ?)
                ))
            )";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        
        $result = $this->query($sql, $params);
        return $result ? (float)($result[0]['total_earnings'] ?? 0) : 0;
    }
    
    /**
     * Get number of projects technician worked on
     * 
     * @param int $technicianId Technician ID
     * @return int
     */
    public function getProjectCount($technicianId) {
        $sql = "SELECT COUNT(DISTINCT pt.project_id) as project_count
                FROM task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                WHERE tl.technician_id = ?";
        
        $result = $this->query($sql, [$technicianId]);
        return $result ? (int)($result[0]['project_count'] ?? 0) : 0;
    }
    
    /**
     * Get work history for technician
     * 
     * @param int $technicianId Technician ID
     * @param int $limit Number of records to return
     * @return array
     */
    public function getWorkHistory($technicianId, $limit = 50) {
        $sql = "SELECT 
                    tl.*,
                    pt.description as task_description,
                    pt.task_type,
                    pt.task_date,
                    pt.date_from,
                    pt.date_to,
                    p.name as project_name,
                    p.id as project_id
                FROM task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                INNER JOIN projects p ON pt.project_id = p.id
                WHERE tl.technician_id = ?
                ORDER BY pt.created_at DESC
                LIMIT ?";
        
        return $this->query($sql, [$technicianId, $limit]);
    }
    
    /**
     * Get statistics for technician
     * 
     * @param int $technicianId Technician ID
     * @param string|null $dateFrom Start date
     * @param string|null $dateTo End date
     * @return array|null
     */
    public function getStatistics($technicianId, $dateFrom = null, $dateTo = null) {
        $technician = $this->getById($technicianId);
        
        if (!$technician) {
            return null;
        }
        
        $totalHours = $this->getTotalHours($technicianId, $dateFrom, $dateTo);
        $totalEarnings = $this->getTotalEarnings($technicianId, $dateFrom, $dateTo);
        $projectCount = $this->getProjectCount($technicianId);
        
        return [
            'technician' => $technician,
            'total_hours' => $totalHours,
            'total_earnings' => $totalEarnings,
            'project_count' => $projectCount,
            'average_hours_per_project' => $projectCount > 0 ? ($totalHours / $projectCount) : 0,
            'average_hourly_rate' => $totalHours > 0 ? ($totalEarnings / $totalHours) : 0
        ];
    }
    
    /**
     * Check if technician has any work records
     * 
     * @param int $technicianId Technician ID
     * @return bool
     */
    public function hasWorkRecords($technicianId) {
        $sql = "SELECT COUNT(*) as count FROM task_labor WHERE technician_id = ?";
        $result = $this->query($sql, [$technicianId]);
        return $result && $result[0]['count'] > 0;
    }
    
    /**
     * Validate technician data
     * 
     * @param array $data Technician data
     * @param bool $isUpdate Is this an update operation
     * @return array Array of errors (empty if valid)
     */
    public function validate($data, $isUpdate = false) {
        $errors = [];
        
        // Name is required
        if (empty($data['name'])) {
            $errors[] = 'Το όνομα είναι υποχρεωτικό';
        }
        
        // Role validation
        if (!empty($data['role']) && !in_array($data['role'], ['technician', 'assistant'])) {
            $errors[] = 'Μη έγκυρος ρόλος';
        }
        
        // Hourly rate validation
        if (isset($data['hourly_rate']) && (!is_numeric($data['hourly_rate']) || $data['hourly_rate'] < 0)) {
            $errors[] = 'Η τιμή ώρας πρέπει να είναι θετικός αριθμός';
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Μη έγκυρο email';
        }
        
        return $errors;
    }
}
