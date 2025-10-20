<?php
/**
 * Payment Model
 * Handles weekly technician payments
 */

require_once 'classes/BaseModel.php';

class Payment extends BaseModel {
    protected $table = 'payments';
    
    /**
     * Get all technicians with labor entries for a specific week
     * 
     * @param string $weekStart Start date of the week (Y-m-d)
     * @param string $weekEnd End date of the week (Y-m-d)
     * @return array
     */
    public function getTechniciansForWeek($weekStart, $weekEnd) {
        $sql = "SELECT 
                    u.id as technician_id,
                    CONCAT(u.first_name, ' ', u.last_name) as technician_name,
                    u.hourly_rate,
                    COUNT(DISTINCT tl.id) as entry_count,
                    SUM(tl.hours_worked) as total_hours,
                    SUM(tl.subtotal) as total_amount,
                    p.id as payment_id,
                    p.paid_at,
                    p.paid_by,
                    paid_by_user.username as paid_by_user
                FROM users u
                INNER JOIN task_labor tl ON u.id = tl.technician_id
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                LEFT JOIN payments p ON (
                    p.technician_id = u.id 
                    AND p.week_start = ? 
                    AND p.week_end = ?
                )
                LEFT JOIN users paid_by_user ON p.paid_by = paid_by_user.id
                WHERE u.is_active = 1
                AND u.role IN ('technician', 'assistant')
                AND (
                    (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                    OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
                )
                GROUP BY u.id, u.first_name, u.last_name, u.hourly_rate, p.id, p.paid_at, p.paid_by, paid_by_user.username
                ORDER BY u.first_name, u.last_name";
                
        return $this->query($sql, [$weekStart, $weekEnd, $weekStart, $weekEnd, $weekEnd, $weekStart]);
    }
    
    /**
     * Get labor entries for a specific technician and week
     * 
     * @param int $technicianId
     * @param string $weekStart
     * @param string $weekEnd
     * @return array
     */
    public function getLaborEntriesForWeek($technicianId, $weekStart, $weekEnd) {
        $sql = "SELECT 
                    tl.*,
                    pt.description as task_description,
                    pt.task_date,
                    pt.date_from,
                    pt.date_to,
                    pt.task_type,
                    p.title as project_title,
                    CONCAT(u.first_name, ' ', u.last_name) as technician_name
                FROM task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                INNER JOIN projects p ON pt.project_id = p.id
                INNER JOIN users u ON tl.technician_id = u.id
                WHERE tl.technician_id = ?
                AND (
                    (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                    OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
                )
                ORDER BY COALESCE(pt.task_date, pt.date_from) DESC, p.title";
                
        return $this->query($sql, [$technicianId, $weekStart, $weekEnd, $weekEnd, $weekStart]);
    }
    
    /**
     * Mark a week as paid for a technician
     * 
     * @param int $technicianId
     * @param string $weekStart
     * @param string $weekEnd
     * @param float $totalHours
     * @param float $totalAmount
     * @param int $userId User who marked it as paid
     * @param string|null $notes
     * @return int Payment ID
     */
    public function markAsPaid($technicianId, $weekStart, $weekEnd, $totalHours, $totalAmount, $userId, $notes = null) {
        // Check if payment record already exists
        $existing = $this->getPaymentRecord($technicianId, $weekStart, $weekEnd);
        
        if ($existing) {
            // Update existing record
            $sql = "UPDATE payments 
                    SET total_hours = ?,
                        total_amount = ?,
                        paid_at = NOW(),
                        paid_by = ?,
                        notes = ?
                    WHERE id = ?";
                    
            $this->db->execute($sql, [$totalHours, $totalAmount, $userId, $notes, $existing['id']]);
            
            return $existing['id'];
        } else {
            // Create new payment record
            $sql = "INSERT INTO payments 
                    (technician_id, week_start, week_end, total_hours, total_amount, paid_at, paid_by, notes)
                    VALUES 
                    (?, ?, ?, ?, ?, NOW(), ?, ?)";
                    
            $this->db->execute($sql, [$technicianId, $weekStart, $weekEnd, $totalHours, $totalAmount, $userId, $notes]);
            
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * Mark a payment as unpaid (revert)
     * 
     * @param int $paymentId
     * @return bool
     */
    public function markAsUnpaid($paymentId) {
        $sql = "UPDATE payments 
                SET paid_at = NULL,
                    paid_by = NULL
                WHERE id = ?";
                
        return $this->db->execute($sql, [$paymentId]);
    }
    
    /**
     * Get payment record for a specific week
     * 
     * @param int $technicianId
     * @param string $weekStart
     * @param string $weekEnd
     * @return array|null
     */
    public function getPaymentRecord($technicianId, $weekStart, $weekEnd) {
        $sql = "SELECT * FROM payments 
                WHERE technician_id = ?
                AND week_start = ?
                AND week_end = ?
                LIMIT 1";
                
        $result = $this->queryOne($sql, [$technicianId, $weekStart, $weekEnd]);
        return $result ?: null;
    }
    
    /**
     * Get the date range for the last completed week
     * 
     * @return array ['start' => 'Y-m-d', 'end' => 'Y-m-d']
     */
    public static function getLastCompletedWeek() {
        // Week starts on Monday and ends on Sunday
        $today = new DateTime();
        $dayOfWeek = $today->format('N'); // 1 (Monday) to 7 (Sunday)
        
        // Calculate last Monday
        $daysToSubtract = $dayOfWeek + 6; // Go back to last Monday
        $lastMonday = clone $today;
        $lastMonday->modify("-{$daysToSubtract} days");
        
        // Calculate last Sunday
        $lastSunday = clone $lastMonday;
        $lastSunday->modify('+6 days');
        
        return [
            'start' => $lastMonday->format('Y-m-d'),
            'end' => $lastSunday->format('Y-m-d')
        ];
    }
    
    /**
     * Get payment history for a technician
     * 
     * @param int $technicianId
     * @param int $limit
     * @return array
     */
    public function getPaymentHistory($technicianId, $limit = 10) {
        $sql = "SELECT 
                    p.*,
                    u.username as paid_by_user
                FROM payments p
                LEFT JOIN users u ON p.paid_by = u.id
                WHERE p.technician_id = ?
                AND p.paid_at IS NOT NULL
                ORDER BY p.paid_at DESC
                LIMIT ?";
                
        return $this->query($sql, [$technicianId, $limit]);
    }
    
    /**
     * Mark individual task labor entries as paid
     * 
     * @param array $laborIds Array of task_labor IDs
     * @param int $userId User who marked as paid
     * @return bool
     */
    public function markLaborEntriesAsPaid($laborIds, $userId) {
        if (empty($laborIds)) {
            return false;
        }
        
        $placeholders = str_repeat('?,', count($laborIds) - 1) . '?';
        $sql = "UPDATE task_labor 
                SET paid_at = NOW(), paid_by = ? 
                WHERE id IN ($placeholders)";
        
        $params = array_merge([$userId], $laborIds);
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Mark individual task labor entries as unpaid (revert)
     * 
     * @param array $laborIds Array of task_labor IDs
     * @return bool
     */
    public function markLaborEntriesAsUnpaid($laborIds) {
        if (empty($laborIds)) {
            return false;
        }
        
        $placeholders = str_repeat('?,', count($laborIds) - 1) . '?';
        $sql = "UPDATE task_labor 
                SET paid_at = NULL, paid_by = NULL 
                WHERE id IN ($placeholders)";
        
        return $this->db->execute($sql, $laborIds);
    }
    
    /**
     * Mark all labor entries for a technician's week as paid
     * 
     * @param int $technicianId
     * @param string $weekStart
     * @param string $weekEnd
     * @param int $userId
     * @return int Payment ID
     */
    public function markWeekAsPaid($technicianId, $weekStart, $weekEnd, $userId) {
        $sql = "UPDATE task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                SET tl.paid_at = NOW(), tl.paid_by = ?
                WHERE tl.technician_id = ?
                AND tl.paid_at IS NULL
                AND (
                    (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                    OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
                )";
        
        $this->db->execute($sql, [$userId, $technicianId, $weekStart, $weekEnd, $weekEnd, $weekStart]);
        
        // Also create/update payment record
        $entries = $this->getLaborEntriesForWeek($technicianId, $weekStart, $weekEnd);
        $totalHours = array_sum(array_column($entries, 'hours_worked'));
        $totalAmount = array_sum(array_column($entries, 'subtotal'));
        
        return $this->markAsPaid($technicianId, $weekStart, $weekEnd, $totalHours, $totalAmount, $userId);
    }
    
    /**
     * Mark all labor entries for a technician's week as unpaid
     * 
     * @param int $technicianId
     * @param string $weekStart
     * @param string $weekEnd
     * @return bool
     */
    public function markWeekAsUnpaid($technicianId, $weekStart, $weekEnd) {
        $sql = "UPDATE task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                SET tl.paid_at = NULL, tl.paid_by = NULL
                WHERE tl.technician_id = ?
                AND (
                    (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                    OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
                )";
        
        $result = $this->db->execute($sql, [$technicianId, $weekStart, $weekEnd, $weekEnd, $weekStart]);
        
        // Also mark payment record as unpaid
        $payment = $this->getPaymentRecord($technicianId, $weekStart, $weekEnd);
        if ($payment) {
            $this->markAsUnpaid($payment['id']);
        }
        
        return $result;
    }
}

