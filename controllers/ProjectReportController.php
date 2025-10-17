<?php
require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';

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
        
        // Get aggregated materials
        $materials = $this->getAggregatedMaterials($projectId, $fromDate, $toDate);
        
        // Get aggregated labor
        $labor = $this->getAggregatedLabor($projectId, $fromDate, $toDate);
        
        // Calculate totals
        $totals = $this->calculateTotals($materials, $labor);
        
        // Generate PDF
        $this->generatePDF($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate);
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
                m.name as material_name,
                m.unit,
                SUM(pm.quantity) as total_quantity,
                m.cost as unit_cost,
                SUM(pm.quantity * m.cost) as total_cost
            FROM project_materials pm
            LEFT JOIN materials m ON pm.material_id = m.id
            WHERE pm.project_id = ?
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND pm.date_added BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }
        
        $sql .= " GROUP BY m.id, m.name, m.unit, m.cost ORDER BY m.name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getAggregatedLabor($projectId, $fromDate = null, $toDate = null) {
        $pdo = $this->db->getPdo();
        $sql = "
            SELECT 
                worker_name,
                COUNT(*) as days_worked,
                daily_rate,
                SUM(daily_rate) as total_cost
            FROM project_labor
            WHERE project_id = ?
        ";
        $params = [$projectId];
        
        if ($fromDate && $toDate) {
            $sql .= " AND work_date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
        }
        
        $sql .= " GROUP BY worker_name, daily_rate ORDER BY worker_name";
        
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
        foreach ($labor as $worker) {
            $laborCost += $worker['total_cost'];
            $totalDays += $worker['days_worked'];
        }
        
        return [
            'materials_cost' => $materialsCost,
            'labor_cost' => $laborCost,
            'total_cost' => $materialsCost + $laborCost,
            'total_workers' => count($labor),
            'total_days' => $totalDays,
            'total_materials' => count($materials)
        ];
    }
    
    private function generatePDF($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate) {
        // Create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor($settings['company_name'] ?? 'HandyCRM');
        $pdf->SetTitle(__('projects.project_report') . ' - ' . $project['title']);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('dejavusans', '', 10);
        
        // Build HTML content
        $html = $this->buildHTMLContent($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate);
        
        // Output HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $filename = 'project_report_' . $project['id'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'I');
    }
    
    private function buildHTMLContent($project, $customer, $settings, $tasks, $materials, $labor, $totals, $fromDate, $toDate) {
        $currencySymbol = $settings['currency_symbol'] ?? '€';
        
        $html = '
        <style>
            h1 { color: #2c3e50; font-size: 24px; margin-bottom: 10px; }
            h2 { color: #34495e; font-size: 18px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
            h3 { color: #7f8c8d; font-size: 14px; margin-top: 15px; margin-bottom: 8px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
            th { background-color: #3498db; color: white; padding: 8px; text-align: left; font-size: 11px; }
            td { border: 1px solid #ddd; padding: 6px; font-size: 10px; }
            .header-box { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
            .summary-card { background-color: #ecf0f1; border-left: 4px solid #3498db; padding: 10px; margin: 5px 0; }
            .total-card { background-color: #2ecc71; color: white; border-left: 4px solid #27ae60; padding: 10px; margin: 5px 0; font-weight: bold; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .company-logo { max-height: 60px; width: auto; }
        </style>
        ';
        
        // Header with Company and Customer Info
        $html .= '<table style="margin-bottom: 20px;">';
        $html .= '<tr>';
        
        // Customer Info (Left)
        $html .= '<td style="width: 50%; border: none; vertical-align: top;">';
        $html .= '<div class="header-box">';
        $html .= '<h3 style="margin-top: 0;">ΠΕΛΑΤΗΣ</h3>';
        $html .= '<strong>' . htmlspecialchars($customer['name']) . '</strong><br>';
        if (!empty($customer['address'])) {
            $html .= htmlspecialchars($customer['address']) . '<br>';
        }
        if (!empty($customer['phone'])) {
            $html .= 'Τηλ: ' . htmlspecialchars($customer['phone']) . '<br>';
        }
        if (!empty($customer['email'])) {
            $html .= 'Email: ' . htmlspecialchars($customer['email']);
        }
        $html .= '</div>';
        $html .= '</td>';
        
        // Company Info (Right)
        $html .= '<td style="width: 50%; border: none; vertical-align: top; text-align: right;">';
        $html .= '<div class="header-box">';
        
        // Company Logo
        if (!empty($settings['company_logo'])) {
            $logoPath = __DIR__ . '/../' . $settings['company_logo'];
            if (file_exists($logoPath)) {
                $html .= '<img src="' . $logoPath . '" class="company-logo" /><br>';
            }
        }
        
        $html .= '<h3 style="margin-top: 10px;">' . htmlspecialchars($settings['company_name'] ?? '') . '</h3>';
        if (!empty($settings['company_address'])) {
            $html .= htmlspecialchars($settings['company_address']) . '<br>';
        }
        if (!empty($settings['company_phone'])) {
            $html .= 'Τηλ: ' . htmlspecialchars($settings['company_phone']) . '<br>';
        }
        if (!empty($settings['company_email'])) {
            $html .= 'Email: ' . htmlspecialchars($settings['company_email']) . '<br>';
        }
        if (!empty($settings['company_tax_id'])) {
            $html .= 'ΑΦΜ: ' . htmlspecialchars($settings['company_tax_id']);
        }
        $html .= '</div>';
        $html .= '</td>';
        
        $html .= '</tr>';
        $html .= '</table>';
        
        // Report Title and Date
        $html .= '<h1 class="text-center">ΑΝΑΦΟΡΑ ΕΡΓΟΥ</h1>';
        $html .= '<h2 class="text-center" style="border: none; margin-bottom: 5px;">' . htmlspecialchars($project['title']) . '</h2>';
        $html .= '<p class="text-center" style="color: #7f8c8d; font-size: 11px; margin-bottom: 20px;">';
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
            $html .= '<tr><th style="width: 20%;">ΗΜΕΡΟΜΗΝΙΑ</th><th>ΤΙΤΛΟΣ ΕΡΓΑΣΙΑΣ</th></tr>';
            foreach ($tasks as $task) {
                $html .= '<tr>';
                $html .= '<td>' . date('d/m/Y', strtotime($task['task_date'])) . '</td>';
                $html .= '<td><strong>' . htmlspecialchars($task['title']) . '</strong><br>';
                if (!empty($task['description'])) {
                    $html .= '<span style="color: #7f8c8d; font-size: 9px;">' . htmlspecialchars($task['description']) . '</span>';
                }
                $html .= '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }
        
        // Materials Section
        if (!empty($materials)) {
            $html .= '<h2><i class="fas fa-box"></i> ΥΛΙΚΑ (ΣΥΓΚΕΝΤΡΩΤΙΚΑ)</h2>';
            $html .= '<table>';
            $html .= '<tr><th>ΥΛΙΚΟ</th><th class="text-center">ΠΟΣΟΤΗΤΑ</th><th class="text-right">ΤΙΜΗ ΜΟΝ.</th><th class="text-right">ΣΥΝΟΛΟ</th></tr>';
            foreach ($materials as $material) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($material['material_name']) . '</td>';
                $html .= '<td class="text-center">' . number_format($material['total_quantity'], 2) . ' ' . htmlspecialchars($material['unit']) . '</td>';
                $html .= '<td class="text-right">' . number_format($material['unit_cost'], 2) . ' ' . $currencySymbol . '</td>';
                $html .= '<td class="text-right"><strong>' . number_format($material['total_cost'], 2) . ' ' . $currencySymbol . '</strong></td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }
        
        // Labor Section
        if (!empty($labor)) {
            $html .= '<h2><i class="fas fa-hard-hat"></i> ΗΜΕΡΟΜΙΣΘΙΑ (ΣΥΓΚΕΝΤΡΩΤΙΚΑ)</h2>';
            $html .= '<table>';
            $html .= '<tr><th>ΕΡΓΑΖΟΜΕΝΟΣ</th><th class="text-center">ΗΜΕΡΕΣ</th><th class="text-right">ΗΜΕΡΟΜΙΣΘΙΟ</th><th class="text-right">ΣΥΝΟΛΟ</th></tr>';
            foreach ($labor as $worker) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($worker['worker_name']) . '</td>';
                $html .= '<td class="text-center">' . $worker['days_worked'] . '</td>';
                $html .= '<td class="text-right">' . number_format($worker['daily_rate'], 2) . ' ' . $currencySymbol . '</td>';
                $html .= '<td class="text-right"><strong>' . number_format($worker['total_cost'], 2) . ' ' . $currencySymbol . '</strong></td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }
        
        // Summary Cards
        $html .= '<h2>ΣΥΓΚΕΝΤΡΩΤΙΚΑ ΣΤΟΙΧΕΙΑ</h2>';
        
        $html .= '<table style="margin-bottom: 10px;">';
        $html .= '<tr>';
        $html .= '<td style="width: 33%; border: none; padding: 5px;">';
        $html .= '<div class="summary-card">';
        $html .= '<div style="font-size: 10px; color: #7f8c8d;">ΣΥΝΟΛΟ ΥΛΙΚΩΝ</div>';
        $html .= '<div style="font-size: 18px; font-weight: bold; color: #2c3e50;">' . number_format($totals['materials_cost'], 2) . ' ' . $currencySymbol . '</div>';
        $html .= '<div style="font-size: 9px; color: #95a5a6;">' . $totals['total_materials'] . ' είδη</div>';
        $html .= '</div>';
        $html .= '</td>';
        
        $html .= '<td style="width: 33%; border: none; padding: 5px;">';
        $html .= '<div class="summary-card">';
        $html .= '<div style="font-size: 10px; color: #7f8c8d;">ΣΥΝΟΛΟ ΗΜΕΡΟΜΙΣΘΙΩΝ</div>';
        $html .= '<div style="font-size: 18px; font-weight: bold; color: #2c3e50;">' . number_format($totals['labor_cost'], 2) . ' ' . $currencySymbol . '</div>';
        $html .= '<div style="font-size: 9px; color: #95a5a6;">' . $totals['total_workers'] . ' εργαζόμενοι × ' . $totals['total_days'] . ' ημέρες</div>';
        $html .= '</div>';
        $html .= '</td>';
        
        $html .= '<td style="width: 34%; border: none; padding: 5px;">';
        $html .= '<div class="total-card">';
        $html .= '<div style="font-size: 10px;">ΓΕΝΙΚΟ ΣΥΝΟΛΟ</div>';
        $html .= '<div style="font-size: 20px; font-weight: bold;">' . number_format($totals['total_cost'], 2) . ' ' . $currencySymbol . '</div>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        // Footer
        $html .= '<div style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; text-align: center; color: #95a5a6; font-size: 9px;">';
        $html .= 'Αυτή η αναφορά δημιουργήθηκε αυτόματα από το HandyCRM στις ' . date('d/m/Y H:i') . '<br>';
        $html .= 'Για περισσότερες πληροφορίες επικοινωνήστε με ' . htmlspecialchars($settings['company_name'] ?? '');
        $html .= '</div>';
        
        return $html;
    }
}
