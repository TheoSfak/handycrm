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
    public function getAll($page = 1, $perPage = 20, $search = null, $dateFrom = null, $dateTo = null, $isInvoiced = null, $reportSent = null, $upcoming = null) {
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

        // Upcoming / overdue filter (exclude already renewed records)
        if ($upcoming !== null && $upcoming !== '') {
            if ($upcoming === 'overdue') {
                $sql .= " AND tm.next_maintenance_date < CURDATE() AND (tm.is_renewed = 0 OR tm.is_renewed IS NULL)";
            } else {
                $days = (int)$upcoming * 30;
                $sql .= " AND tm.next_maintenance_date >= CURDATE() AND tm.next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) AND (tm.is_renewed = 0 OR tm.is_renewed IS NULL)";
                $params[] = $days;
            }
            $sql .= " ORDER BY tm.next_maintenance_date ASC LIMIT ? OFFSET ?";
        } else {
            $sql .= " ORDER BY tm.maintenance_date DESC, tm.created_at DESC LIMIT ? OFFSET ?";
        }
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total count with filters
     */
    public function getTotalCount($search = null, $dateFrom = null, $dateTo = null, $isInvoiced = null, $reportSent = null, $upcoming = null) {
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

        // Upcoming / overdue filter (exclude already renewed records)
        if ($upcoming !== null && $upcoming !== '') {
            if ($upcoming === 'overdue') {
                $sql .= " AND next_maintenance_date < CURDATE() AND (is_renewed = 0 OR is_renewed IS NULL)";
            } else {
                $days = (int)$upcoming * 30;
                $sql .= " AND next_maintenance_date >= CURDATE() AND next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) AND (is_renewed = 0 OR is_renewed IS NULL)";
                $params[] = $days;
            }
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'];
    }

    /**
     * Get count of overdue maintenances (next_maintenance_date already passed, not renewed)
     */
    public function getOverdueCount() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL AND next_maintenance_date < CURDATE() AND (is_renewed = 0 OR is_renewed IS NULL)";
        $result = $this->db->fetchOne($sql, []);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get count of maintenances due within the next N days (not renewed)
     */
    public function getUpcomingCount($days = 30) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL AND next_maintenance_date >= CURDATE() AND next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) AND (is_renewed = 0 OR is_renewed IS NULL)";
        $result = $this->db->fetchOne($sql, [$days]);
        return (int)($result['total'] ?? 0);
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
            observations, photo_path, photos, transformers_data, created_by, additional_technicians,
            previous_id, renewed_by_id, is_renewed
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Encode photos as JSON
        $photosJson = !empty($data['photos']) ? json_encode($data['photos']) : null;
        
        // Encode additional technicians as JSON
        $additionalTechsJson = !empty($data['additional_technicians']) ? json_encode($data['additional_technicians']) : null;
        
        $previousId = !empty($data['previous_id']) ? (int)$data['previous_id'] : null;

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
            $previousId,
            null,
            0
        ];
        
        $inserted = $this->db->execute($sql, $params);
        if (!$inserted) {
            return false;
        }

        $newId = (int)$this->db->getPdo()->lastInsertId();

        // Auto-detect previous unrenewed maintenance for this customer if previous_id was not explicitly passed
        if (empty($previousId) && !empty($data['customer_name'])) {
            try {
                $existing = $this->db->fetchOne(
                    "SELECT id FROM {$this->table} 
                     WHERE customer_name = ? AND deleted_at IS NULL AND id != ? 
                       AND maintenance_date <= ? AND (is_renewed = 0 OR is_renewed IS NULL)
                     ORDER BY maintenance_date DESC, id DESC LIMIT 1",
                    [trim($data['customer_name']), $newId, $data['maintenance_date']]
                );
                if (!empty($existing['id'])) {
                    $previousId = (int)$existing['id'];
                    $this->db->execute("UPDATE {$this->table} SET previous_id = ? WHERE id = ?", [$previousId, $newId]);
                }
            } catch (Exception $e) {
                error_log("TransformerMaintenance::create auto-detect previous maintenance failed: " . $e->getMessage());
            }
        }

        // If a previous maintenance exists, mark it as renewed by this new maintenance
        if (!empty($previousId)) {
            try {
                $this->db->execute(
                    "UPDATE {$this->table} SET renewed_by_id = ?, is_renewed = 1, renewed_at = NOW() WHERE id = ?",
                    [$newId, $previousId]
                );
            } catch (Exception $e) {
                error_log("TransformerMaintenance::create mark previous as renewed failed: " . $e->getMessage());
            }
        }

        return $newId;
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
                WHERE tm.deleted_at IS NULL 
                  AND (tm.is_renewed = 0 OR tm.is_renewed IS NULL)
                  AND tm.next_maintenance_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
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

    /**
     * Mark a maintenance as renewed
     */
    public function markAsRenewed($id, $renewedById = null) {
        $sql = "UPDATE {$this->table} SET is_renewed = 1, renewed_by_id = ?, renewed_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$renewedById, $id]);
    }

    /**
     * Get distinct maintenance customers with their last known information
     * Excludes soft-deleted records and orders alphabetically
     */
    public function getMaintenanceCustomers() {
        $sql = "SELECT tm.customer_name, tm.address, tm.phone, tm.other_details, 
                       tm.transformers_data, tm.transformer_power, tm.transformer_type,
                       tm.id as last_maintenance_id, tm.maintenance_date as last_maintenance_date,
                       tm.next_maintenance_date as last_next_maintenance_date,
                       tm.is_renewed as last_is_renewed
                FROM {$this->table} tm
                INNER JOIN (
                    SELECT customer_name, MAX(maintenance_date) as max_date, MAX(id) as max_id
                    FROM {$this->table}
                    WHERE deleted_at IS NULL AND customer_name IS NOT NULL AND TRIM(customer_name) != ''
                    GROUP BY customer_name
                ) latest ON tm.customer_name = latest.customer_name AND tm.id = latest.max_id
                WHERE tm.deleted_at IS NULL
                ORDER BY tm.customer_name ASC";
        
        try {
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Failed to fetch maintenance customers: " . $e->getMessage());
            return $this->db->fetchAll("SELECT DISTINCT customer_name, address, phone FROM {$this->table} WHERE deleted_at IS NULL AND customer_name IS NOT NULL AND TRIM(customer_name) != '' ORDER BY customer_name ASC");
        }
    }

    /**
     * Get maintenance history chain for a customer / maintenance
     */
    public function getMaintenanceHistory($id) {
        $current = $this->find($id);
        if (!$current) {
            return [];
        }
        
        $customerName = trim($current['customer_name']);
        if (empty($customerName)) {
            $current['is_current'] = true;
            return [$current];
        }
        
        $sql = "SELECT tm.id, tm.maintenance_date, tm.next_maintenance_date,
                       tm.customer_name, tm.transformer_power, tm.transformer_type,
                       tm.is_invoiced, tm.report_sent, tm.is_renewed, tm.renewed_by_id, tm.previous_id,
                       CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM {$this->table} tm
                LEFT JOIN users u ON tm.created_by = u.id
                WHERE tm.deleted_at IS NULL AND tm.customer_name = ?
                ORDER BY tm.maintenance_date DESC, tm.id DESC";
        
        try {
            $rows = $this->db->fetchAll($sql, [$customerName]);
            foreach ($rows as &$row) {
                $row['is_current'] = ((int)$row['id'] === (int)$id);
            }
            unset($row);
            return $rows;
        } catch (Exception $e) {
            error_log("Failed to get maintenance history: " . $e->getMessage());
            $current['is_current'] = true;
            return [$current];
        }
    }

    /**
     * Merge all maintenances from a source customer name into a target customer name
     * Re-chains all maintenances chronologically
     *
     * @param string $sourceCustomerName
     * @param string $targetCustomerName
     * @return int|false Number of merged records or false on failure
     */
    public function mergeCustomers($sourceCustomerName, $targetCustomerName) {
        $source = trim($sourceCustomerName);
        $target = trim($targetCustomerName);
        
        if (empty($source) || empty($target) || mb_strtolower($source) === mb_strtolower($target)) {
            return 0;
        }
        
        $pdo = $this->db->getPdo();
        $pdo->beginTransaction();
        
        try {
            // Count records to be moved
            $countResult = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM {$this->table} WHERE customer_name = ? AND deleted_at IS NULL",
                [$source]
            );
            $movedCount = (int)($countResult['cnt'] ?? 0);
            
            if ($movedCount === 0) {
                $pdo->rollBack();
                return 0;
            }
            
            // Get canonical target details (address, phone) if available
            $targetInfo = $this->db->fetchOne("
                SELECT address, phone, other_details, transformers_data 
                FROM {$this->table} 
                WHERE customer_name = ? AND deleted_at IS NULL 
                ORDER BY maintenance_date DESC, id DESC LIMIT 1
            ", [$target]);
            
            // Update customer_name on all source records to target
            $this->db->execute(
                "UPDATE {$this->table} SET customer_name = ? WHERE customer_name = ?",
                [$target, $source]
            );
            
            // Backfill empty address/phone from target if target had them
            if (!empty($targetInfo['address'])) {
                $this->db->execute(
                    "UPDATE {$this->table} SET address = ? WHERE customer_name = ? AND (address IS NULL OR address = '')",
                    [$targetInfo['address'], $target]
                );
            }
            if (!empty($targetInfo['phone'])) {
                $this->db->execute(
                    "UPDATE {$this->table} SET phone = ? WHERE customer_name = ? AND (phone IS NULL OR phone = '')",
                    [$targetInfo['phone'], $target]
                );
            }
            
            // Re-chain chronological history for the unified customer
            $allRecords = $this->db->fetchAll("
                SELECT id, maintenance_date 
                FROM {$this->table} 
                WHERE customer_name = ? AND deleted_at IS NULL 
                ORDER BY maintenance_date ASC, id ASC
            ", [$target]);
            
            $totalRecords = count($allRecords);
            for ($i = 0; $i < $totalRecords; $i++) {
                $currId = $allRecords[$i]['id'];
                $prevId = ($i > 0) ? $allRecords[$i - 1]['id'] : null;
                $isLatest = ($i === $totalRecords - 1);
                $nextId = (!$isLatest) ? $allRecords[$i + 1]['id'] : null;
                $isRenewed = $isLatest ? 0 : 1;
                $renewedAt = $isLatest ? null : date('Y-m-d H:i:s');
                
                $this->db->execute("
                    UPDATE {$this->table} 
                    SET previous_id = ?, renewed_by_id = ?, is_renewed = ?, renewed_at = ?
                    WHERE id = ?
                ", [$prevId, $nextId, $isRenewed, $renewedAt, $currId]);
            }
            
            $pdo->commit();
            return $movedCount;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Failed to merge maintenance customers: " . $e->getMessage());
            return false;
        }
    }
}

