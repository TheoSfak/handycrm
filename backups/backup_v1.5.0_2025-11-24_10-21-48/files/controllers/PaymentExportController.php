<?php
/**
 * Payment Export Controller
 * Handles Excel/CSV export for payments
 */

require_once 'classes/BaseController.php';

class PaymentExportController extends BaseController {
    private $paymentModel;
    
    public function __construct() {
        parent::__construct();
        $this->paymentModel = new Payment();
    }
    
    /**
     * Export payments to CSV
     */
    public function exportCSV() {
        // Get filters from request
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $paidStatus = $_GET['paid_status'] ?? '';
        $technicianId = $_GET['technician_id'] ?? '';
        
        // Get all entries for the period (no pagination)
        $entries = $this->getEntriesForExport($dateFrom, $dateTo, $paidStatus, $technicianId);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=payments_' . date('Y-m-d_His') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 (helps Excel recognize Greek characters)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write header row
        fputcsv($output, [
            'Ημερομηνία',
            'Τεχνικός',
            'Πελάτης',
            'Έργο',
            'Εργασία',
            'Ώρες',
            'Ωρομίσθιο',
            'Ποσό',
            'Κατάσταση',
            'Ημ/νία Πληρωμής',
            'Πληρώθηκε από'
        ], ';'); // Use semicolon for better Excel compatibility
        
        // Write data rows
        foreach ($entries as $entry) {
            // Format status
            $status = $entry['paid_at'] ? 'Πληρωμένο' : 'Απλήρωτο';
            
            // Format paid date
            $paidDate = $entry['paid_at'] ? date('d/m/Y H:i', strtotime($entry['paid_at'])) : '-';
            
            // Format paid by
            $paidBy = $entry['paid_by_name'] ?? '-';
            
            // Get task date (either task_date or date_from depending on task_type)
            $taskDate = $entry['task_type'] === 'single_day' 
                ? $entry['task_date'] 
                : $entry['date_from'];
            
            fputcsv($output, [
                date('d/m/Y', strtotime($taskDate)),
                $entry['technician_name'],
                $entry['customer_name'] ?? '-',
                $entry['project_title'],
                $entry['task_description'] ?? '-',
                number_format($entry['hours_worked'], 2, ',', ''),
                number_format($entry['hourly_rate'], 2, ',', '') . '€',
                number_format($entry['subtotal'], 2, ',', '') . '€',
                $status,
                $paidDate,
                $paidBy
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get all entries for export (no pagination)
     */
    private function getEntriesForExport($dateFrom, $dateTo, $paidStatus, $technicianId) {
        $db = $this->db->connect();
        
        // Build query - matching the structure from Payment model
        $query = "SELECT 
                    tl.*,
                    pt.description as task_description,
                    pt.task_date,
                    pt.date_from,
                    pt.date_to,
                    pt.task_type,
                    p.title as project_title,
                    CASE 
                        WHEN c.customer_type = 'company' AND c.company_name IS NOT NULL 
                        THEN c.company_name
                        ELSE CONCAT(c.first_name, ' ', c.last_name)
                    END as customer_name,
                    CONCAT(u.first_name, ' ', u.last_name) as technician_name,
                    CONCAT(paid_user.first_name, ' ', paid_user.last_name) as paid_by_name
                FROM task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                INNER JOIN projects p ON pt.project_id = p.id
                LEFT JOIN customers c ON p.customer_id = c.id
                INNER JOIN users u ON tl.technician_id = u.id
                LEFT JOIN users paid_user ON tl.paid_by = paid_user.id
                WHERE 1=1";
        
        $params = [];
        
        // Add date filters (check task type)
        if (!empty($dateFrom) && !empty($dateTo)) {
            $query .= " AND (
                (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                OR (pt.task_type = 'date_range' AND pt.date_from <= ? AND pt.date_to >= ?)
            )";
            $params[] = $dateFrom;
            $params[] = $dateTo;
            $params[] = $dateTo;
            $params[] = $dateFrom;
        }
        
        // Add paid status filter
        if ($paidStatus === 'paid') {
            $query .= " AND tl.paid_at IS NOT NULL";
        } elseif ($paidStatus === 'unpaid') {
            $query .= " AND tl.paid_at IS NULL";
        }
        
        // Add technician filter
        if (!empty($technicianId)) {
            $query .= " AND tl.technician_id = ?";
            $params[] = $technicianId;
        }
        
        $query .= " ORDER BY COALESCE(pt.task_date, pt.date_from) DESC, p.title";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
