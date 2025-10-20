<?php
/**
 * Payment Report Controller
 * Generates PDF reports for payments
 */

require_once 'classes/BaseController.php';
require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';

class PaymentReportController extends BaseController {
    private $paymentModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->paymentModel = new Payment();
        $this->userModel = new User();
    }
    
    /**
     * Get a setting value from the database
     */
    private function getSetting($key, $default = '') {
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['setting_value'] : $default;
    }
    
    /**
     * Generate PDF report for payments
     */
    public function generate() {
        // Get filter parameters
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        $paidStatus = $_GET['paid_status'] ?? 'all'; // 'all', 'paid', 'unpaid'
        $technicianId = $_GET['technician_id'] ?? null;
        
        if (!$dateFrom || !$dateTo) {
            // Default to current month
            $dateFrom = date('Y-m-01');
            $dateTo = date('Y-m-t');
        }
        
        // Get all technicians with labor for this period
        $technicians = $this->userModel->getByRole(['technician', 'assistant']);
        $allData = [];
        $grandTotalHours = 0;
        $grandTotalAmount = 0;
        
        foreach ($technicians as $tech) {
            // Skip if specific technician selected and this is not it
            if ($technicianId && $tech['id'] != $technicianId) {
                continue;
            }
            
            // Get labor entries for this technician
            $entries = $this->getEntriesForPeriod($tech['id'], $dateFrom, $dateTo, $paidStatus);
            
            if (empty($entries)) {
                continue;
            }
            
            $totalHours = array_sum(array_column($entries, 'hours_worked'));
            $totalAmount = array_sum(array_column($entries, 'subtotal'));
            
            $allData[] = [
                'technician' => $tech,
                'entries' => $entries,
                'total_hours' => $totalHours,
                'total_amount' => $totalAmount
            ];
            
            $grandTotalHours += $totalHours;
            $grandTotalAmount += $totalAmount;
        }
        
        // Generate PDF
        $this->generatePDF($allData, $dateFrom, $dateTo, $paidStatus, $grandTotalHours, $grandTotalAmount);
    }
    
    /**
     * Get labor entries for a technician in a period
     */
    private function getEntriesForPeriod($technicianId, $dateFrom, $dateTo, $paidStatus) {
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
                    OR (pt.task_type = 'date_range' AND (
                        (pt.date_from BETWEEN ? AND ?) OR 
                        (pt.date_to BETWEEN ? AND ?) OR
                        (pt.date_from <= ? AND pt.date_to >= ?)
                    ))
                )";
        
        $params = [$technicianId, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo, $dateFrom, $dateTo];
        
        // Add paid status filter
        if ($paidStatus === 'paid') {
            $sql .= " AND tl.paid_at IS NOT NULL";
        } elseif ($paidStatus === 'unpaid') {
            $sql .= " AND tl.paid_at IS NULL";
        }
        
        $sql .= " ORDER BY COALESCE(pt.task_date, pt.date_from) ASC, p.title";
        
        return $this->paymentModel->query($sql, $params);
    }
    
    /**
     * Generate PDF document
     */
    private function generatePDF($data, $dateFrom, $dateTo, $paidStatus, $grandTotalHours, $grandTotalAmount) {
        // Get company name from settings
        $companyName = $this->getSetting('company_name', 'HandyCRM');
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($companyName);
        $pdf->SetTitle(__('payments.payment_report'));
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Set font
        $pdf->SetFont('dejavusans', '', 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Header
        $html = '<h1 style="text-align: center; color: #0d6efd;">' . __('payments.payment_report') . '</h1>';
        $html .= '<p style="text-align: center; font-size: 11px; margin-bottom: 20px;">';
        $html .= __('payments.period') . ': ' . formatDate($dateFrom) . ' - ' . formatDate($dateTo) . '<br>';
        $html .= __('payments.status') . ': ';
        if ($paidStatus === 'paid') {
            $html .= __('payments.paid_only');
        } elseif ($paidStatus === 'unpaid') {
            $html .= __('payments.unpaid_only');
        } else {
            $html .= __('payments.all_entries');
        }
        $html .= '</p>';
        
        // Summary
        $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">';
        $html .= '<tr style="background-color: #e9ecef; font-weight: bold;">';
        $html .= '<td style="width: 50%;">' . __('payments.total_hours') . '</td>';
        $html .= '<td style="width: 50%; text-align: right;">' . number_format($grandTotalHours, 2) . ' h</td>';
        $html .= '</tr>';
        $html .= '<tr style="background-color: #e9ecef; font-weight: bold;">';
        $html .= '<td>' . __('payments.total_amount') . '</td>';
        $html .= '<td style="text-align: right;">' . formatCurrency($grandTotalAmount) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        // Each technician's data
        foreach ($data as $techData) {
            $tech = $techData['technician'];
            $entries = $techData['entries'];
            
            // Technician header
            $html .= '<h3 style="margin-top: 20px; color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 5px;">';
            $html .= htmlspecialchars($tech['name']) . ' - ' . formatCurrency($tech['hourly_rate']) . '/h';
            $html .= '</h3>';
            
            // Entries table
            $html .= '<table border="1" cellpadding="4" style="border-collapse: collapse; width: 100%; font-size: 9px; margin-bottom: 10px;">';
            $html .= '<thead>';
            $html .= '<tr style="background-color: #f8f9fa; font-weight: bold;">';
            $html .= '<td style="width: 10%;">' . __('payments.date') . '</td>';
            $html .= '<td style="width: 30%;">' . __('payments.project') . '</td>';
            $html .= '<td style="width: 30%;">' . __('payments.task') . '</td>';
            $html .= '<td style="width: 10%; text-align: center;">' . __('payments.hours') . '</td>';
            $html .= '<td style="width: 10%; text-align: right;">' . __('payments.rate') . '</td>';
            $html .= '<td style="width: 10%; text-align: right;">' . __('payments.amount') . '</td>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($entries as $entry) {
                $workDate = $entry['task_date'] ?: $entry['date_from'];
                $isPaid = !empty($entry['paid_at']);
                $rowStyle = $isPaid ? 'background-color: #d1e7dd;' : '';
                
                $html .= '<tr style="' . $rowStyle . '">';
                $html .= '<td>' . formatDate($workDate) . '</td>';
                $html .= '<td>' . htmlspecialchars($entry['project_title']) . '</td>';
                $html .= '<td>' . htmlspecialchars($entry['task_description']) . '</td>';
                $html .= '<td style="text-align: center;">' . number_format($entry['hours_worked'], 2) . '</td>';
                $html .= '<td style="text-align: right;">' . formatCurrency($entry['hourly_rate']) . '</td>';
                $html .= '<td style="text-align: right;"><strong>' . formatCurrency($entry['subtotal']) . '</strong></td>';
                $html .= '</tr>';
            }
            
            // Subtotal
            $html .= '<tr style="background-color: #e9ecef; font-weight: bold;">';
            $html .= '<td colspan="3" style="text-align: right;">' . __('payments.total') . ':</td>';
            $html .= '<td style="text-align: center;">' . number_format($techData['total_hours'], 2) . ' h</td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align: right;">' . formatCurrency($techData['total_amount']) . '</td>';
            $html .= '</tr>';
            
            $html .= '</tbody>';
            $html .= '</table>';
        }
        
        // Footer
        $html .= '<p style="text-align: center; font-size: 8px; color: #6c757d; margin-top: 30px;">';
        $html .= __('payments.report_generated_on') . ' ' . date('d/m/Y H:i');
        $html .= '<br>' . $companyName;
        $html .= '</p>';
        
        // Print text using writeHTMLCell()
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $filename = 'payment_report_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'I');
    }
}
