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
    public function getAll($page = 1, $perPage = 20, $search = null, $dateFrom = null, $dateTo = null, $isInvoiced = null, $reportSent = null) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT tm.*, 
                CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM {$this->table} tm
                LEFT JOIN users u ON tm.created_by = u.id
                WHERE tm.deleted_at IS NULL";
        
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
        
        // Invoiced filter
        if ($isInvoiced !== null && $isInvoiced !== '') {
            $sql .= " AND tm.is_invoiced = ?";
            $params[] = (int)$isInvoiced;
        }
        
        // Report sent filter
        if ($reportSent !== null && $reportSent !== '') {
            $sql .= " AND tm.report_sent = ?";
            $params[] = (int)$reportSent;
        }
        
        $sql .= " ORDER BY tm.maintenance_date DESC, tm.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total count with filters
     */
    public function getTotalCount($search = null, $dateFrom = null, $dateTo = null, $isInvoiced = null, $reportSent = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
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
        
        // Invoiced filter
        if ($isInvoiced !== null && $isInvoiced !== '') {
            $sql .= " AND is_invoiced = ?";
            $params[] = (int)$isInvoiced;
        }
        
        // Report sent filter
        if ($reportSent !== null && $reportSent !== '') {
            $sql .= " AND report_sent = ?";
            $params[] = (int)$reportSent;
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
            observations, photo_path, photos, transformers_data, created_by, additional_technicians
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Encode photos as JSON
        $photosJson = !empty($data['photos']) ? json_encode($data['photos']) : null;
        
        // Encode additional technicians as JSON
        $additionalTechsJson = !empty($data['additional_technicians']) ? json_encode($data['additional_technicians']) : null;
        
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
            $photosJson,
            $data['transformers_data'] ?? null,
            $data['created_by'],
            $additionalTechsJson
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
            photos = ?,
            transformers_data = ?,
            created_by = ?,
            additional_technicians = ?
            WHERE id = ?";
        
        // Encode photos as JSON
        $photosJson = !empty($data['photos']) ? json_encode($data['photos']) : null;
        
        // Encode additional technicians as JSON
        $additionalTechsJson = !empty($data['additional_technicians']) ? json_encode($data['additional_technicians']) : null;
        
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
            $photosJson,
            $data['transformers_data'] ?? null,
            $data['created_by'],
            $additionalTechsJson,
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Get maintenance by ID
     */
    public function find($id) {
        $sql = "SELECT tm.*, 
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
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
    
    /**
     * Update invoiced status and calculate total amount
     */
    public function updateInvoicedStatus($id, $status) {
        if ($status) {
            // Calculate total amount based on number of transformers
            $totalAmount = $this->calculateMaintenanceAmount($id);
            $sql = "UPDATE {$this->table} SET is_invoiced = ?, total_amount = ?, invoiced_at = NOW() WHERE id = ?";
            return $this->db->execute($sql, [1, $totalAmount, $id]);
        } else {
            $sql = "UPDATE {$this->table} SET is_invoiced = ?, total_amount = NULL, invoiced_at = NULL WHERE id = ?";
            return $this->db->execute($sql, [0, $id]);
        }
    }
    
    /**
     * Calculate maintenance amount based on number of transformers
     */
    public function calculateMaintenanceAmount($id) {
        // Get maintenance record
        $maintenance = $this->find($id);
        if (!$maintenance) {
            return 0;
        }
        
        // Count transformers from transformers_data JSON
        $transformersCount = 0;
        if (!empty($maintenance['transformers_data'])) {
            $transformersData = json_decode($maintenance['transformers_data'], true);
            if (is_array($transformersData)) {
                $transformersCount = count($transformersData);
            }
        }
        
        // Get pricing from settings
        $pricing = $this->getMaintenancePricing();
        
        // Calculate amount based on transformer count
        if ($transformersCount >= 3) {
            return $pricing['3_transformers'];
        } elseif ($transformersCount == 2) {
            return $pricing['2_transformers'];
        } elseif ($transformersCount == 1) {
            return $pricing['1_transformer'];
        }
        
        return 0;
    }
    
    /**
     * Get maintenance pricing from settings
     */
    private function getMaintenancePricing() {
        $defaults = [
            '1_transformer' => 400.00,
            '2_transformers' => 600.00,
            '3_transformers' => 900.00
        ];
        
        try {
            $stmt = $this->db->getPdo()->query("
                SELECT setting_key, setting_value 
                FROM settings 
                WHERE setting_key LIKE 'maintenance_price_%'
            ");
            
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($settings as $setting) {
                $key = str_replace('maintenance_price_', '', $setting['setting_key']);
                $defaults[$key] = floatval($setting['setting_value']);
            }
        } catch (Exception $e) {
            error_log("Failed to load maintenance pricing: " . $e->getMessage());
        }
        
        return $defaults;
    }
    
    /**
     * Update report sent status
     */
    public function updateReportSentStatus($id, $status) {
        if ($status) {
            $sql = "UPDATE {$this->table} SET report_sent = 1, report_sent_at = NOW() WHERE id = ?";
            return $this->db->execute($sql, [$id]);
        } else {
            $sql = "UPDATE {$this->table} SET report_sent = 0, report_sent_at = NULL WHERE id = ?";
            return $this->db->execute($sql, [$id]);
        }
    }
}
