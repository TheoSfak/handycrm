<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';

// Custom PDF class with footer
class CustomPDF extends TCPDF {
    private $companySettings = [];
    
    public function setCompanySettings($settings) {
        $this->companySettings = $settings;
    }
    
    public function Footer() {
        $this->SetY(-30);
        $this->SetFont('dejavusans', '', 8);
        
        // Footer with border
        $html = '<div style="border-top: 2px solid #3498db; padding-top: 8px; margin-top: 10px;">';
        $html .= '<table style="width: 100%; border: none;">';
        $html .= '<tr>';
        
        // Left: Company info
        $html .= '<td style="width: 70%; border: none; text-align: left; vertical-align: top;">';
        $html .= '<strong style="font-size: 10px; color: #2c3e50;">' . htmlspecialchars($this->companySettings['company_name'] ?? '') . '</strong><br>';
        
        // Address
        if (!empty($this->companySettings['company_address'])) {
            $html .= '<span style="font-size: 8px; color: #7f8c8d;">' . htmlspecialchars($this->companySettings['company_address']) . '</span><br>';
        }
        
        // Contact info line
        $contactInfo = [];
        if (!empty($this->companySettings['company_phone'])) {
            $contactInfo[] = 'Τ: ' . htmlspecialchars($this->companySettings['company_phone']);
        }
        if (!empty($this->companySettings['company_email'])) {
            $contactInfo[] = htmlspecialchars($this->companySettings['company_email']);
        }
        if (!empty($this->companySettings['company_tax_id'])) {
            $contactInfo[] = 'ΑΦΜ: ' . htmlspecialchars($this->companySettings['company_tax_id']);
        }
        
        if (!empty($contactInfo)) {
            $html .= '<span style="font-size: 7px; color: #95a5a6;">' . implode(' | ', $contactInfo) . '</span>';
        }
        
        $html .= '</td>';
        
        // Right: Page number
        $html .= '<td style="width: 30%; border: none; text-align: right; vertical-align: top;">';
        $html .= '<span style="font-size: 9px; color: #34495e; font-weight: bold;">Σελίδα ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . '</span><br>';
        $html .= '<span style="font-size: 7px; color: #95a5a6;">' . date('d/m/Y H:i') . '</span>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</div>';
        
        $this->writeHTML($html, true, false, true, false, '');
    }
}

class ProjectReportController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function generate($projectId) {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
        
        // Get date filters
        $fromDate = isset($_POST['from_date']) && !empty($_POST['from_date']) ? $_POST['from_date'] : null;
        $toDate = isset($_POST['to_date']) && !empty($_POST['to_date']) ? $_POST['to_date'] : null;
        
        // Get hide prices option
        $hidePrices = isset($_POST['hide_prices']) && $_POST['hide_prices'] === '1';
        
        // Get report content filter (materials, labor, or both)
        $reportContent = isset($_POST['report_content']) ? $_POST['report_content'] : 'both';
        
        // Get report notes
        $reportNotes = isset($_POST['report_notes']) && !empty($_POST['report_notes']) ? $_POST['report_notes'] : null;
        
        // Get project data
        $project = $this->getProject($projectId);
        if (!$project) {
            die('Project not found');
        }
        
        // Get customer data
        $customer = $this->getCustomer($project['customer_id']);
        
        // Get company settings
        $settings = $this->getCompanySettings();
        
        // Get tasks with date filter
        $tasks = $this->getTasks($projectId, $fromDate, $toDate);
        
        // Get aggregated materials (only if needed)
        $materials = [];
        if ($reportContent === 'both' || $reportContent === 'materials') {
            $materials = $this->getAggregatedMaterials($projectId, $fromDate, $toDate);
        }
        
        // Get aggregated labor (only if needed)
        $labor = [];
        if ($reportContent === 'both' || $reportContent === 'labor') {
            $labor = $this->getAggregatedLabor($projectId, $fromDate, $toDate);
        }
        
        // Calculate totals
        $totals = $this->calculateTotals($materials, $labor);
        
        // Generate PDF
        $this->generatePDF($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hidePrices, $reportNotes);
    }
    
    private function getProject($projectId) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("
            SELECT * FROM projects WHERE id = ?
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getCustomer($customerId) {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("
            SELECT * FROM customers WHERE id = ?
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getCompanySettings() {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    private function getTasks($projectId, $fromDate = null, $toDate = null) {
        $pdo = $this->db->getPdo();
        $sql = "SELECT * FROM project_tasks WHERE project_id = ?";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND task_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }
        
        $sql .= " ORDER BY task_date ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getAggregatedMaterials($projectId, $fromDate = null, $toDate = null) {
        $pdo = $this->db->getPdo();
        $sql = "
            SELECT 
                tm.description as material_name,
                tm.unit_type as unit,
                SUM(tm.quantity) as total_quantity,
                AVG(tm.unit_price) as unit_cost,
                SUM(tm.subtotal) as total_cost
            FROM task_materials tm
            LEFT JOIN project_tasks pt ON tm.task_id = pt.id
            WHERE pt.project_id = ?
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND pt.task_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }
        
        $sql .= " GROUP BY tm.description, tm.unit_type ORDER BY tm.description";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log the materials
        error_log("=== MATERIALS QUERY DEBUG ===");
        error_log("Total materials found: " . count($results));
        foreach ($results as $mat) {
            error_log("Material: " . $mat['material_name'] . " | Unit: " . $mat['unit'] . " | Qty: " . $mat['total_quantity'] . " | Unit Price: " . $mat['unit_cost'] . " | Total: " . $mat['total_cost']);
        }
        
        return $results;
    }
    
    private function getAggregatedLabor($projectId, $fromDate = null, $toDate = null) {
        $pdo = $this->db->getPdo();
        
        // Check if technician_name column exists, otherwise use user_id with JOIN
        $checkColumn = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'technician_name'");
        $hasTechnicianName = $checkColumn->rowCount() > 0;
        
        if ($hasTechnicianName) {
            // Use technician_name if column exists
            $workerNameField = "COALESCE(tl.technician_name, CONCAT(u.first_name, ' ', u.last_name))";
            $groupByField = "tl.technician_name";
        } else {
            // Fall back to user_id with JOIN to users table
            $workerNameField = "CONCAT(u.first_name, ' ', u.last_name)";
            $groupByField = "tl.user_id";
        }
        
        // Check if hours_worked column exists, otherwise use hours
        $checkHours = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'hours_worked'");
        $hasHoursWorked = $checkHours->rowCount() > 0;
        $hoursField = $hasHoursWorked ? "tl.hours_worked" : "tl.hours";
        
        // Check which ID column to use for JOIN
        $checkTechId = $pdo->query("SHOW COLUMNS FROM task_labor LIKE 'technician_id'");
        $hasTechnicianId = $checkTechId->rowCount() > 0;
        $userIdField = $hasTechnicianId ? "tl.technician_id" : "tl.user_id";
        
        $sql = "
            SELECT 
                $workerNameField as worker_name,
                SUM($hoursField) as total_hours,
                COUNT(DISTINCT pt.id) as days_worked,
                AVG(tl.hourly_rate) as hourly_rate,
                SUM(tl.subtotal) as total_cost
            FROM task_labor tl
            LEFT JOIN project_tasks pt ON tl.task_id = pt.id
            LEFT JOIN users u ON $userIdField = u.id
            WHERE pt.project_id = ?
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND pt.task_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }
        
        $sql .= " GROUP BY $groupByField ORDER BY worker_name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function calculateTotals($materials, $labor) {
        $materialsCost = 0;
        foreach ($materials as $material) {
            $materialsCost += $material['total_cost'];
        }
        
        $laborCost = 0;
        $totalDays = 0;
        $totalHours = 0;
        foreach ($labor as $worker) {
            $laborCost += $worker['total_cost'];
            $totalDays += $worker['days_worked'];
            $totalHours += $worker['total_hours'];
        }
        
        return [
            'materials_cost' => $materialsCost,
            'labor_cost' => $laborCost,
            'total_cost' => $materialsCost + $laborCost,
            'total_workers' => count($labor),
            'total_days' => $totalDays,
            'total_hours' => $totalHours,
            'total_materials' => count($materials)
        ];
    }
    
    private function generatePDF($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hidePrices = false, $reportNotes = null) {
        // Create new PDF document with custom footer
        $pdf = new CustomPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Pass settings to PDF for footer
        $pdf->setCompanySettings($settings);
        
        // Set document information
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor($settings['company_name'] ?? 'HandyCRM');
        $pdf->SetTitle(__('projects.project_report') . ' - ' . $project['title']);
        
        // Remove default header
        $pdf->setPrintHeader(false);
        
        // Set margins with larger bottom margin to avoid splitting
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 35); // Larger bottom margin
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('dejavusans', '', 10);
        
        // Build HTML content
        $html = $this->buildHTMLContent($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hidePrices, $reportNotes);
        
        // Output HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $filename = 'project_report_' . $project['id'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'I');
    }
    
    private function buildFooterContent($settings, $pageNum = 1, $totalPages = 1) {
        $html = '<div style="border-top: 2px solid #3498db; padding-top: 8px; text-align: center;">';
        $html .= '<table style="width: 100%; border: none;">';
        $html .= '<tr>';
        
        // Left: Company info
        $html .= '<td style="width: 70%; border: none; text-align: left; padding: 0;">';
        $html .= '<strong style="font-size: 9px; color: #2c3e50;">' . htmlspecialchars($settings['company_name'] ?? '') . '</strong><br>';
        
        $contactInfo = [];
        if (!empty($settings['company_phone'])) {
            $contactInfo[] = 'Τ: ' . htmlspecialchars($settings['company_phone']);
        }
        if (!empty($settings['company_email'])) {
            $contactInfo[] = htmlspecialchars($settings['company_email']);
        }
        if (!empty($settings['company_tax_id'])) {
            $contactInfo[] = 'ΑΦΜ: ' . htmlspecialchars($settings['company_tax_id']);
        }
        
        if (!empty($contactInfo)) {
            $html .= '<span style="font-size: 7px; color: #7f8c8d;">' . implode(' | ', $contactInfo) . '</span>';
        }
        
        $html .= '</td>';
        
        // Right: Page number
        $html .= '<td style="width: 30%; border: none; text-align: right; padding: 0;">';
        $html .= '<span style="font-size: 8px; color: #95a5a6;">Σελίδα ' . $pageNum . ' από ' . $totalPages . '</span><br>';
        $html .= '<span style="font-size: 7px; color: #bdc3c7;">' . date('d/m/Y H:i') . '</span>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }
    
    private function buildHTMLContent($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate, $hidePrices = false, $reportNotes = null) {
        // Always use HTML entity for euro to avoid server encoding issues
        // The database might have corrupted € character depending on server charset
        $currencySymbol = '&euro;';
        
        $html = '
        <style>
            body { font-family: "DejaVu Sans", sans-serif; }
            h1 { 
                color: #2c3e50; 
                font-size: 26px; 
                font-weight: bold;
                margin-bottom: 10px;
                letter-spacing: 1px;
            }
            h2 { 
                color: #34495e; 
                font-size: 16px; 
                font-weight: bold;
                margin-top: 25px; 
                margin-bottom: 20px; 
                padding-bottom: 8px;
                page-break-after: avoid;
                letter-spacing: 0.5px;
            }
            h3 { 
                color: #7f8c8d; 
                font-size: 11px; 
                font-weight: bold;
                margin-top: 0; 
                margin-bottom: 8px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            table { 
                border-collapse: collapse; 
                width: 100%; 
                margin-top: 10px;
                margin-bottom: 20px; 
                page-break-inside: auto;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            thead { 
                display: table-header-group;
            }
            tbody {
                display: table-row-group;
            }
            th { 
                background: linear-gradient(180deg, #3498db 0%, #2980b9 100%);
                color: #000000; 
                padding: 10px 8px; 
                text-align: left; 
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border: 1px solid #2980b9;
            }
            td { 
                border: 1px solid #ecf0f1; 
                padding: 8px; 
                font-size: 10px;
                line-height: 1.4;
            }
            tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .company-logo { 
                max-height: 45px; 
                width: auto; 
                display: block; 
                margin: 0 auto;
            }
        </style>
        ';
        
        // Logo at top center
        if (!empty($settings['company_logo'])) {
            $logoPath = __DIR__ . '/../' . $settings['company_logo'];
            if (file_exists($logoPath)) {
                $html .= '<div style="text-align: center; margin-bottom: 20px;">';
                $html .= '<img src="' . $logoPath . '" class="company-logo" />';
                $html .= '</div>';
            }
        }
        
        // Header with Company and Customer Info - Simple & Clean
        $html .= '<table style="margin-bottom: 25px; border: none;">';
        $html .= '<tr>';
        
        // Customer Info (Left)
        $html .= '<td style="width: 50%; border: none; vertical-align: top; padding-right: 15px;">';
        $html .= '<h3 style="margin-top: 0; color: #3498db; margin-bottom: 10px;">ΠΕΛΑΤΗΣ</h3>';
        
        // Customer Name/Company
        if (!empty($customer['company_name'])) {
            $html .= '<strong style="font-size: 13px; color: #2c3e50;">' . htmlspecialchars($customer['company_name']) . '</strong><br>';
            $customerName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
            if (!empty($customerName)) {
                $html .= '<span style="font-size: 10px; color: #7f8c8d;">' . htmlspecialchars($customerName) . '</span><br>';
            }
        } else {
            $customerName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
            $html .= '<strong style="font-size: 13px; color: #2c3e50;">' . htmlspecialchars($customerName) . '</strong><br>';
        }
        
        // Address
        if (!empty($customer['address'])) {
            $html .= '<span style="font-size: 10px;">' . htmlspecialchars($customer['address']) . '</span><br>';
        }
        
        // City & Postal Code
        $cityPostal = [];
        if (!empty($customer['postal_code'])) {
            $cityPostal[] = htmlspecialchars($customer['postal_code']);
        }
        if (!empty($customer['city'])) {
            $cityPostal[] = htmlspecialchars($customer['city']);
        }
        if (!empty($cityPostal)) {
            $html .= '<span style="font-size: 10px;">' . implode(', ', $cityPostal) . '</span><br>';
        }
        
        // Tax ID (ΑΦΜ)
        if (!empty($customer['tax_id'])) {
            $html .= '<span style="font-size: 10px;">ΑΦΜ: ' . htmlspecialchars($customer['tax_id']) . '</span><br>';
        }
        
        // Phone
        if (!empty($customer['phone'])) {
            $html .= '<span style="font-size: 9px; color: #7f8c8d;">Τηλ: ' . htmlspecialchars($customer['phone']) . '</span><br>';
        }
        
        // Email
        if (!empty($customer['email'])) {
            $html .= '<span style="font-size: 9px; color: #7f8c8d;">Email: ' . htmlspecialchars($customer['email']) . '</span>';
        }
        
        $html .= '</td>';
        
        // Company Info (Right)
        $html .= '<td style="width: 50%; border: none; vertical-align: top; padding-left: 15px;">';
        $html .= '<h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 10px;">' . htmlspecialchars($settings['company_name'] ?? '') . '</h3>';
        
        if (!empty($settings['company_address'])) {
            $html .= '<span style="font-size: 10px; color: #2c3e50;">' . htmlspecialchars($settings['company_address']) . '</span><br>';
        }
        if (!empty($settings['company_phone'])) {
            $html .= '<span style="font-size: 9px; color: #7f8c8d;">Τηλ: ' . htmlspecialchars($settings['company_phone']) . '</span><br>';
        }
        if (!empty($settings['company_email'])) {
            $html .= '<span style="font-size: 9px; color: #7f8c8d;">Email: ' . htmlspecialchars($settings['company_email']) . '</span><br>';
        }
        if (!empty($settings['company_tax_id'])) {
            $html .= '<span style="font-size: 9px; color: #7f8c8d;">ΑΦΜ: ' . htmlspecialchars($settings['company_tax_id']) . '</span>';
        }
        
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';
        
        // Report Title and Date
        $html .= '<h1 class="text-center" style="margin-top: 30px; margin-bottom: 15px;">ΑΝΑΦΟΡΑ ΕΡΓΟΥ</h1>';
        $html .= '<h2 class="text-center" style="border: none; margin-bottom: 15px; font-size: 20px;">' . htmlspecialchars($project['title']) . '</h2>';
        
        // Date info
        $html .= '<p class="text-center" style="color: #7f8c8d; font-size: 11px; margin-top: 15px; margin-bottom: 30px;">';
        $html .= 'Ημερομηνία Αναφοράς: ' . date('d/m/Y');
        if ($fromDate && $toDate) {
            $html .= ' | Περίοδος: ' . date('d/m/Y', strtotime($fromDate)) . ' - ' . date('d/m/Y', strtotime($toDate));
        } else {
            $html .= ' | Όλη η Περίοδος Έργου';
        }
        $html .= '</p>';
        
        // Tasks Section
        if (!empty($tasks)) {
            $html .= '<h2><i class="fas fa-tasks"></i> ΕΡΓΑΣΙΕΣ</h2>';
            $html .= '<table>';
            $html .= '<thead nobr="true">';
            $html .= '<tr nobr="true"><th style="width: 25%; text-align: left;">ΗΜΕΡΟΜΗΝΙΑ</th><th style="width: 75%; text-align: left; padding-left: 8px;">ΠΕΡΙΓΡΑΦΗ ΕΡΓΑΣΙΑΣ</th></tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            foreach ($tasks as $task) {
                $html .= '<tr>';
                $html .= '<td style="width: 25%;">' . date('d/m/Y', strtotime($task['task_date'])) . '</td>';
                $html .= '<td style="width: 75%;"><strong>' . htmlspecialchars($task['description'] ?? '') . '</strong><br>';
                if (!empty($task['notes'])) {
                    $html .= '<span style="color: #7f8c8d; font-size: 9px;">' . htmlspecialchars($task['notes']) . '</span>';
                }
                $html .= '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '</table>';
        }
        
        // Materials Section
        if (!empty($materials)) {
            $html .= '<h2><i class="fas fa-box"></i> ΥΛΙΚΑ (ΣΥΓΚΕΝΤΡΩΤΙΚΑ)</h2>';
            $html .= '<table>';
            $html .= '<thead nobr="true">';
            
            // Table headers
            if ($hidePrices) {
                $html .= '<tr nobr="true"><th style="width: 50%;">ΥΛΙΚΟ</th><th class="text-center" style="width: 50%;">ΠΟΣΟΤΗΤΑ</th></tr>';
            } else {
                $html .= '<tr nobr="true"><th style="width: 40%;">ΥΛΙΚΟ</th><th class="text-center" style="width: 20%;">ΠΟΣΟΤΗΤΑ</th><th class="text-right" style="width: 20%;">ΤΙΜΗ ΜΟΝ.</th><th class="text-right" style="width: 20%;">ΣΥΝΟΛΟ</th></tr>';
            }
            
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($materials as $material) {
                $html .= '<tr>';
                
                if ($hidePrices) {
                    $html .= '<td style="width: 50%;">' . htmlspecialchars($material['material_name']) . '</td>';
                    $html .= '<td class="text-center" style="width: 50%;">' . number_format($material['total_quantity'], 2, ',', '.') . ' ' . htmlspecialchars($material['unit']) . '</td>';
                } else {
                    $html .= '<td style="width: 40%;">' . htmlspecialchars($material['material_name']) . '</td>';
                    $html .= '<td class="text-center" style="width: 20%;">' . number_format($material['total_quantity'], 2, ',', '.') . ' ' . htmlspecialchars($material['unit']) . '</td>';
                    $html .= '<td class="text-right" style="width: 20%;">' . formatCurrencyWithVAT($material['unit_cost']) . '</td>';
                    $html .= '<td class="text-right" style="width: 20%;"><strong>' . formatCurrencyWithVAT($material['total_cost']) . '</strong></td>';
                }
                
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
        }
        
        // Labor Section
        if (!empty($labor)) {
            $html .= '<h2><i class="fas fa-hard-hat"></i> ΗΜΕΡΟΜΙΣΘΙΑ (ΣΥΓΚΕΝΤΡΩΤΙΚΑ)</h2>';
            $html .= '<table>';
            $html .= '<thead nobr="true">';
            
            // Table headers
            if ($hidePrices) {
                $html .= '<tr nobr="true"><th style="width: 50%;">ΤΕΧΝΙΚΟΣ</th><th class="text-center" style="width: 25%;">ΩΡΕΣ</th><th class="text-center" style="width: 25%;">ΗΜΕΡΕΣ</th></tr>';
            } else {
                $html .= '<tr nobr="true"><th style="width: 30%;">ΤΕΧΝΙΚΟΣ</th><th class="text-center" style="width: 15%;">ΩΡΕΣ</th><th class="text-center" style="width: 15%;">ΗΜΕΡΕΣ</th><th class="text-right" style="width: 20%;">ΩΡΟΜΙΣΘΙΟ</th><th class="text-right" style="width: 20%;">ΣΥΝΟΛΟ</th></tr>';
            }
            
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($labor as $worker) {
                $html .= '<tr>';
                
                if ($hidePrices) {
                    $html .= '<td style="width: 50%;">' . htmlspecialchars($worker['worker_name']) . '</td>';
                    $html .= '<td class="text-center" style="width: 25%;">' . number_format($worker['total_hours'], 2, ',', '.') . 'h</td>';
                    $html .= '<td class="text-center" style="width: 25%;">' . $worker['days_worked'] . '</td>';
                } else {
                    $html .= '<td style="width: 30%;">' . htmlspecialchars($worker['worker_name']) . '</td>';
                    $html .= '<td class="text-center" style="width: 15%;">' . number_format($worker['total_hours'], 2, ',', '.') . 'h</td>';
                    $html .= '<td class="text-center" style="width: 15%;">' . $worker['days_worked'] . '</td>';
                    $html .= '<td class="text-right" style="width: 20%;">' . formatCurrencyWithVAT($worker['hourly_rate']) . '/h</td>';
                    $html .= '<td class="text-right" style="width: 20%;"><strong>' . formatCurrencyWithVAT($worker['total_cost']) . '</strong></td>';
                }
                
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
        }
        
        // Summary Cards
        $html .= '<h2 style="margin-top: 100px; margin-bottom: 15px;">ΣΥΓΚΕΝΤΡΩΤΙΚΑ ΣΤΟΙΧΕΙΑ</h2>';
        $html .= '<div style="border-top: 2px solid #3498db; margin-bottom: 20px;"></div>';
        
        $html .= '<table style="margin-bottom: 20px; border: none;">';
        $html .= '<tr>';
        
        if ($hidePrices) {
            // Show only counts when hiding prices
            
            // Materials Card
            $html .= '<td style="width: 50%; border: none; padding: 5px;">';
            $html .= '<table style="width: 100%; background-color: #3498db; margin: 0; height: 80px;">';
            $html .= '<tr><td style="border: none; padding: 12px; text-align: center; vertical-align: middle;">';
            $html .= '<div style="font-size: 10px; color: white; opacity: 0.9;">ΣΥΝΟΛΟ ΥΛΙΚΩΝ</div>';
            $html .= '<div style="font-size: 24px; font-weight: bold; color: white; margin-top: 5px;">' . $totals['total_materials'] . '</div>';
            $html .= '<div style="font-size: 9px; color: white; opacity: 0.8; margin-top: 3px;">είδη</div>';
            $html .= '</td></tr>';
            $html .= '</table>';
            $html .= '</td>';
            
            // Labor Card
            $html .= '<td style="width: 50%; border: none; padding: 5px;">';
            $html .= '<table style="width: 100%; background-color: #9b59b6; margin: 0; height: 80px;">';
            $html .= '<tr><td style="border: none; padding: 12px; text-align: center; vertical-align: middle;">';
            $html .= '<div style="font-size: 10px; color: white; opacity: 0.9;">ΣΥΝΟΛΟ ΕΡΓΑΣΙΑΣ</div>';
            $html .= '<div style="font-size: 20px; font-weight: bold; color: white; margin-top: 5px;">' . number_format($totals['total_hours'], 2, ',', '.') . ' ώρες</div>';
            $html .= '<div style="font-size: 9px; color: white; opacity: 0.8; margin-top: 3px;">' . $totals['total_workers'] . ' τεχνικοί × ' . $totals['total_days'] . ' ημέρες</div>';
            $html .= '</td></tr>';
            $html .= '</table>';
            $html .= '</td>';
            
        } else {
            // Show prices (original version)
            
            // Materials Card
            $html .= '<td style="width: 33.33%; border: none; padding: 5px;">';
            $html .= '<table style="width: 100%; background-color: #3498db; margin: 0; height: 80px;">';
            $html .= '<tr><td style="border: none; padding: 12px; text-align: center; vertical-align: middle;">';
            $html .= '<div style="font-size: 10px; color: white; opacity: 0.9;">ΣΥΝΟΛΟ ΥΛΙΚΩΝ</div>';
            $html .= '<div style="font-size: 18px; font-weight: bold; color: white; margin-top: 5px;">' . number_format($totals['materials_cost'], 2, ',', '.') . ' ' . $currencySymbol . '</div>';
            $html .= '<div style="font-size: 8px; color: white; opacity: 0.8; margin-top: 3px;">(χωρίς ΦΠΑ)</div>';
            $html .= '<div style="font-size: 8px; color: white; opacity: 0.7; margin-top: 2px;">' . $totals['total_materials'] . ' είδη</div>';
            $html .= '</td></tr>';
            $html .= '</table>';
            $html .= '</td>';
            
            // Labor Card
            $html .= '<td style="width: 33.33%; border: none; padding: 5px;">';
            $html .= '<table style="width: 100%; background-color: #9b59b6; margin: 0; height: 80px;">';
            $html .= '<tr><td style="border: none; padding: 12px; text-align: center; vertical-align: middle;">';
            $html .= '<div style="font-size: 10px; color: white; opacity: 0.9;">ΣΥΝΟΛΟ ΕΡΓΑΣΙΑΣ</div>';
            $html .= '<div style="font-size: 18px; font-weight: bold; color: white; margin-top: 5px;">' . number_format($totals['labor_cost'], 2, ',', '.') . ' ' . $currencySymbol . '</div>';
            $html .= '<div style="font-size: 8px; color: white; opacity: 0.8; margin-top: 3px;">(χωρίς ΦΠΑ)</div>';
            $html .= '<div style="font-size: 8px; color: white; opacity: 0.7; margin-top: 2px;">' . $totals['total_workers'] . ' τεχνικοί × ' . number_format($totals['total_hours'], 2, ',', '.') . ' ώρες</div>';
            $html .= '</td></tr>';
            $html .= '</table>';
            $html .= '</td>';
            
            // Total Card
            $html .= '<td style="width: 33.33%; border: none; padding: 5px;">';
            $html .= '<table style="width: 100%; background-color: #e74c3c; margin: 0; height: 80px;">';
            $html .= '<tr><td style="border: none; padding: 12px; text-align: center; vertical-align: middle;">';
            $html .= '<div style="font-size: 10px; color: white; opacity: 0.9;">ΓΕΝΙΚΟ ΣΥΝΟΛΟ</div>';
            $html .= '<div style="font-size: 18px; font-weight: bold; color: white; margin-top: 5px;">' . number_format($totals['total_cost'], 2, ',', '.') . ' ' . $currencySymbol . '</div>';
            $html .= '<div style="font-size: 8px; color: white; opacity: 0.8; margin-top: 3px;">(χωρίς ΦΠΑ)</div>';
            $html .= '</td></tr>';
            $html .= '</table>';
            $html .= '</td>';
        }
        
        $html .= '</tr>';
        $html .= '</table>';
        
        // Report Notes Section
        if (!empty($reportNotes)) {
            $html .= '<h2 style="margin-top: 40px; margin-bottom: 15px;">ΠΑΡΑΤΗΡΗΣΕΙΣ</h2>';
            $html .= '<div style="border-top: 2px solid #3498db; margin-bottom: 20px;"></div>';
            $html .= '<div style="background-color: #f8f9fa; padding: 15px; font-size: 11px; line-height: 1.6;">';
            $html .= nl2br(htmlspecialchars($reportNotes));
            $html .= '</div>';
        }
        
        return $html;
    }
}
