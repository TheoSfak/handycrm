<?php
/**
 * Payment Model
 * Handles weekly technician payments
 */

class Payment extends Model {
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
                    t.id as technician_id,
                    t.name as technician_name,
                    t.hourly_rate,
                    COUNT(DISTINCT le.id) as entry_count,
                    SUM(le.hours_worked) as total_hours,
                    SUM(le.subtotal) as total_amount,
                    p.id as payment_id,
                    p.paid_at,
                    p.paid_by,
                    u.username as paid_by_user
                FROM technicians t
                INNER JOIN labor_entries le ON t.id = le.technician_id
                LEFT JOIN payments p ON (
                    p.technician_id = t.id 
                    AND p.week_start = :week_start 
                    AND p.week_end = :week_end
                )
                LEFT JOIN users u ON p.paid_by = u.id
                WHERE le.work_date BETWEEN :week_start2 AND :week_end2
                GROUP BY t.id, t.name, t.hourly_rate, p.id, p.paid_at, p.paid_by, u.username
                ORDER BY t.name";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'week_start2' => $weekStart,
            'week_end2' => $weekEnd
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    le.*,
                    pt.description as task_description,
                    p.title as project_title,
                    t.name as technician_name
                FROM labor_entries le
                INNER JOIN project_tasks pt ON le.task_id = pt.id
                INNER JOIN projects p ON pt.project_id = p.id
                INNER JOIN technicians t ON le.technician_id = t.id
                WHERE le.technician_id = :technician_id
                AND le.work_date BETWEEN :week_start AND :week_end
                ORDER BY le.work_date DESC, p.title";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'technician_id' => $technicianId,
            'week_start' => $weekStart,
            'week_end' => $weekEnd
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    SET total_hours = :total_hours,
                        total_amount = :total_amount,
                        paid_at = NOW(),
                        paid_by = :paid_by,
                        notes = :notes
                    WHERE id = :id";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'total_hours' => $totalHours,
                'total_amount' => $totalAmount,
                'paid_by' => $userId,
                'notes' => $notes,
                'id' => $existing['id']
            ]);
            
            return $existing['id'];
        } else {
            // Create new payment record
            $sql = "INSERT INTO payments 
                    (technician_id, week_start, week_end, total_hours, total_amount, paid_at, paid_by, notes)
                    VALUES 
                    (:technician_id, :week_start, :week_end, :total_hours, :total_amount, NOW(), :paid_by, :notes)";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'technician_id' => $technicianId,
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'total_hours' => $totalHours,
                'total_amount' => $totalAmount,
                'paid_by' => $userId,
                'notes' => $notes
            ]);
            
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
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $paymentId]);
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
                WHERE technician_id = :technician_id
                AND week_start = :week_start
                AND week_end = :week_end
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'technician_id' => $technicianId,
            'week_start' => $weekStart,
            'week_end' => $weekEnd
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
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
                WHERE p.technician_id = :technician_id
                AND p.paid_at IS NOT NULL
                ORDER BY p.paid_at DESC
                LIMIT :limit";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('technician_id', $technicianId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
