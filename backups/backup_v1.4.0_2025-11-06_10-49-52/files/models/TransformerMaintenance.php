<?php
/**
 * TransformerMaintenance Model
 * Handles transformer maintenance records
 */

require_once 'classes/BaseModel.php';

class TransformerMaintenance extends BaseModel {
    protected $table = 'transformer_maintenances';
    
    /**
     * Get all maintenances with pagination and filters
     */
    public function getAll($page = 1, $perPage = 20, $search = null, $dateFrom = null, $dateTo = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT tm.*, 
                CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM {$this->table} tm
                LEFT JOIN users u ON tm.created_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        // Search filter
        if ($search) {
            $sql .= " AND (tm.customer_name LIKE ? OR tm.phone LIKE ? OR tm.address LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Date range filter
        if ($dateFrom) {
            $sql .= " AND tm.maintenance_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND tm.maintenance_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY tm.maintenance_date DESC, tm.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total count with filters
     */
    public function getTotalCount($search = null, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($search) {
            $sql .= " AND (customer_name LIKE ? OR phone LIKE ? OR address LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($dateFrom) {
            $sql .= " AND maintenance_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND maintenance_date <= ?";
            $params[] = $dateTo;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'];
    }
    
    /**
     * Create new maintenance record
     */
    public function create($data) {
        // Auto-calculate next maintenance date (+1 year)
        if (!empty($data['maintenance_date'])) {
            $maintenanceDate = new DateTime($data['maintenance_date']);
            $maintenanceDate->modify('+1 year');
            $data['next_maintenance_date'] = $maintenanceDate->format('Y-m-d');
        }
        
        $sql = "INSERT INTO {$this->table} (
            customer_name, address, phone, other_details,
            maintenance_date, next_maintenance_date, transformer_power, transformer_type,
            insulation_measurements, coil_resistance_measurements, grounding_measurement,
            oil_breakdown_v1, oil_breakdown_v2, oil_breakdown_v3, oil_breakdown_v4, oil_breakdown_v5,
            observations, photo_path, transformers_data, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['customer_name'],
            $data['address'] ?? null,
            $data['phone'] ?? null,
            $data['other_details'] ?? null,
            $data['maintenance_date'],
            $data['next_maintenance_date'],
            $data['transformer_power'],
            $data['transformer_type'] ?? 'oil',
            $data['insulation_measurements'],
            $data['coil_resistance_measurements'],
            $data['grounding_measurement'],
            $data['oil_breakdown_v1'] ?? null,
            $data['oil_breakdown_v2'] ?? null,
            $data['oil_breakdown_v3'] ?? null,
            $data['oil_breakdown_v4'] ?? null,
            $data['oil_breakdown_v5'] ?? null,
            $data['observations'] ?? null,
            $data['photo_path'] ?? null,
            $data['transformers_data'] ?? null,
            $data['created_by']
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Update maintenance record
     */
    public function update($id, $data) {
        // Auto-calculate next maintenance date if maintenance_date changed
        if (!empty($data['maintenance_date'])) {
            $maintenanceDate = new DateTime($data['maintenance_date']);
            $maintenanceDate->modify('+1 year');
            $data['next_maintenance_date'] = $maintenanceDate->format('Y-m-d');
        }
        
        $sql = "UPDATE {$this->table} SET
            customer_name = ?,
            address = ?,
            phone = ?,
            other_details = ?,
            maintenance_date = ?,
            next_maintenance_date = ?,
            transformer_power = ?,
            transformer_type = ?,
            insulation_measurements = ?,
            coil_resistance_measurements = ?,
            grounding_measurement = ?,
            oil_breakdown_v1 = ?,
            oil_breakdown_v2 = ?,
            oil_breakdown_v3 = ?,
            oil_breakdown_v4 = ?,
            oil_breakdown_v5 = ?,
            observations = ?,
            photo_path = ?,
            transformers_data = ?
            WHERE id = ?";
        
        $params = [
            $data['customer_name'],
            $data['address'] ?? null,
            $data['phone'] ?? null,
            $data['other_details'] ?? null,
            $data['maintenance_date'],
            $data['next_maintenance_date'],
            $data['transformer_power'],
            $data['transformer_type'] ?? 'oil',
            $data['insulation_measurements'],
            $data['coil_resistance_measurements'],
            $data['grounding_measurement'],
            $data['oil_breakdown_v1'] ?? null,
            $data['oil_breakdown_v2'] ?? null,
            $data['oil_breakdown_v3'] ?? null,
            $data['oil_breakdown_v4'] ?? null,
            $data['oil_breakdown_v5'] ?? null,
            $data['observations'] ?? null,
            $data['photo_path'] ?? null,
            $data['transformers_data'] ?? null,
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Get maintenance by ID
     */
    public function find($id) {
        $sql = "SELECT tm.*, 
                CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM {$this->table} tm
                LEFT JOIN users u ON tm.created_by = u.id
                WHERE tm.id = ?";
        
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Delete maintenance record
     */
    public function delete($id) {
        // Get photo path before deletion
        $maintenance = $this->find($id);
        
        // Delete photo file if exists
        if ($maintenance && !empty($maintenance['photo_path'])) {
            $photoPath = __DIR__ . '/../' . $maintenance['photo_path'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Get upcoming maintenances (within next 30 days)
     */
    public function getUpcoming($days = 30) {
        $sql = "SELECT tm.*, 
                CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM {$this->table} tm
                LEFT JOIN users u ON tm.created_by = u.id
                WHERE tm.next_maintenance_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY tm.next_maintenance_date ASC";
        
        return $this->db->fetchAll($sql, [$days]);
    }
}
