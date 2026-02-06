<?php
/**
 * TransformerMaintenanceController
 * Handles transformer maintenance operations
 */

require_once 'classes/BaseController.php';
require_once 'models/TransformerMaintenance.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TransformerMaintenanceController extends BaseController {
    private $maintenanceModel;
    
    public function __construct() {
        parent::__construct();
        
        // Check if user has access to maintenance functions
        if (!$this->hasMaintenanceAccess()) {
            $_SESSION['error'] = 'Δεν έχετε πρόσβαση σε αυτή τη λειτουργία.';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $this->maintenanceModel = new TransformerMaintenance();
    }
    
    /**
     * Check if user has access to maintenance functions
     * Only admin and maintenance_technician roles can access
     */
    private function hasMaintenanceAccess() {
        $allowedRoles = ['admin', 'maintenance_technician'];
        return in_array($_SESSION['role'], $allowedRoles);
    }
    
    /**
     * List all maintenances
     */
    public function index() {
        // Get filters from GET
        $search = $_GET['search'] ?? null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        
        // Get maintenances
        $maintenances = $this->maintenanceModel->getAll($page, $perPage, $search, $dateFrom, $dateTo);
        $totalCount = $this->maintenanceModel->getTotalCount($search, $dateFrom, $dateTo);
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('maintenances/index', [
            'maintenances' => $maintenances,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ]);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $this->view('maintenances/create');
    }
    
    /**
     * Store new maintenance
     */
    public function store() {
        
        // Validate required fields
        $required = [
            'customer_name' => 'Όνομα Πελάτη',
            'maintenance_date' => 'Ημερομηνία Συντήρησης'
        ];
        
        $errors = [];
        foreach ($required as $field => $label) {
            if (empty($_POST[$field])) {
                $errors[] = "Το πεδίο '{$label}' είναι υποχρεωτικό";
            }
        }
        
        // Validate transformers data
        if (empty($_POST['transformers']) || !is_array($_POST['transformers'])) {
            $errors[] = "Πρέπει να προσθέσετε τουλάχιστον έναν μετασχηματιστή";
        } else {
            foreach ($_POST['transformers'] as $index => $transformer) {
                if (empty($transformer['power'])) {
                    $errors[] = "Μετασχηματιστής #{$index}: Η ισχύς είναι υποχρεωτική";
                }
                if (empty($transformer['insulation'])) {
                    $errors[] = "Μετασχηματιστής #{$index}: Οι μετρήσεις μόνωσης είναι υποχρεωτικές";
                }
                if (empty($transformer['coil_resistance'])) {
                    $errors[] = "Μετασχηματιστής #{$index}: Οι μετρήσεις αντίστασης πηνίων είναι υποχρεωτικές";
                }
                if (empty($transformer['grounding'])) {
                    $errors[] = "Μετασχηματιστής #{$index}: Η μέτρηση γείωσης είναι υποχρεωτική";
                }
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . BASE_URL . '/maintenances/create');
            exit;
        }
        
        // Handle photo upload
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadPhoto($_FILES['photo']);
            if ($uploadResult['success']) {
                $photoPath = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['error'];
                header('Location: ' . BASE_URL . '/maintenances/create');
                exit;
            }
        }
        
        // Prepare transformers data as JSON
        $transformersData = [];
        foreach ($_POST['transformers'] as $transformer) {
            $transformersData[] = [
                'power' => $transformer['power'],
                'type' => $transformer['type'] ?? 'oil', // Default to oil type for backward compatibility
                'insulation' => $transformer['insulation'],
                'coil_resistance' => $transformer['coil_resistance'],
                'grounding' => $transformer['grounding'],
                'oil_v1' => $transformer['oil_v1'] ?? null,
                'oil_v2' => $transformer['oil_v2'] ?? null,
                'oil_v3' => $transformer['oil_v3'] ?? null,
                'oil_v4' => $transformer['oil_v4'] ?? null,
                'oil_v5' => $transformer['oil_v5'] ?? null
            ];
        }
        
        $firstTransformer = $transformersData[0];
        $firstTransformer = $transformersData[0];
        
        // Prepare data
        $data = [
            'customer_name' => $_POST['customer_name'],
            'address' => $_POST['address'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'other_details' => $_POST['other_details'] ?? null,
            'maintenance_date' => $_POST['maintenance_date'],
            // Legacy fields (first transformer)
            'transformer_power' => $firstTransformer['power'],
            'transformer_type' => $firstTransformer['type'], // New field for transformer type
            'insulation_measurements' => $firstTransformer['insulation'],
            'coil_resistance_measurements' => $firstTransformer['coil_resistance'],
            'grounding_measurement' => $firstTransformer['grounding'],
            'oil_breakdown_v1' => $firstTransformer['oil_v1'],
            'oil_breakdown_v2' => $firstTransformer['oil_v2'],
            'oil_breakdown_v3' => $firstTransformer['oil_v3'],
            'oil_breakdown_v4' => $firstTransformer['oil_v4'],
            'oil_breakdown_v5' => $firstTransformer['oil_v5'],
            'observations' => $_POST['observations'] ? trim(str_replace(["\r\n", "\r"], "\n", $_POST['observations'])) : null,
            'photo_path' => $photoPath,
            // New JSON field for all transformers
            'transformers_data' => json_encode($transformersData),
            'created_by' => $_SESSION['user_id']
        ];
        
        if ($this->maintenanceModel->create($data)) {
            $_SESSION['success'] = 'Η συντήρηση δημιουργήθηκε επιτυχώς';
            header('Location: ' . BASE_URL . '/maintenances');
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία της συντήρησης';
            header('Location: ' . BASE_URL . '/maintenances/create');
        }
        exit;
    }
    
    /**
     * Show maintenance details
     */
    public function show($id) {

        
        $maintenance = $this->maintenanceModel->find($id);
        
        if (!$maintenance) {
            $_SESSION['error'] = 'Η συντήρηση δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }
        
        $this->view('maintenances/view', [
            'maintenance' => $maintenance
        ]);
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {

        
        $maintenance = $this->maintenanceModel->find($id);
        
        if (!$maintenance) {
            $_SESSION['error'] = 'Η συντήρηση δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }
        
        $this->view('maintenances/edit', [
            'maintenance' => $maintenance
        ]);
    }
    
    /**
     * Update maintenance
     */
    public function update($id) {

        
        $maintenance = $this->maintenanceModel->find($id);
        
        if (!$maintenance) {
            $_SESSION['error'] = 'Η συντήρηση δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }
        
        // Validate required fields
        $required = [
            'customer_name' => 'Όνομα Πελάτη',
            'maintenance_date' => 'Ημερομηνία Συντήρησης'
        ];
        
        $errors = [];
        foreach ($required as $field => $label) {
            if (empty($_POST[$field])) {
                $errors[] = "Το πεδίο '{$label}' είναι υποχρεωτικό";
            }
        }
        
        // Validate transformers data
        if (empty($_POST['transformers']) || !is_array($_POST['transformers'])) {
            $errors[] = "Πρέπει να προσθέσετε τουλάχιστον έναν μετασχηματιστή";
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header("Location: " . BASE_URL . "/maintenances/edit/{$id}");
            exit;
        }
        
        // Handle photo upload
        $photoPath = $maintenance['photo_path'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Delete old photo if exists
            if (!empty($photoPath)) {
                $oldPhotoPath = __DIR__ . '/../' . $photoPath;
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }
            
            $uploadResult = $this->uploadPhoto($_FILES['photo']);
            if ($uploadResult['success']) {
                $photoPath = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['error'];
                header("Location: " . BASE_URL . "/maintenances/edit/{$id}");
                exit;
            }
        }
        
        // Prepare transformers data as JSON
        $transformersData = [];
        foreach ($_POST['transformers'] as $transformer) {
            $transformersData[] = [
                'power' => $transformer['power'],
                'type' => $transformer['type'] ?? 'oil', // Default to oil type for backward compatibility
                'insulation' => $transformer['insulation'],
                'coil_resistance' => $transformer['coil_resistance'],
                'grounding' => $transformer['grounding'],
                'oil_v1' => $transformer['oil_v1'] ?? null,
                'oil_v2' => $transformer['oil_v2'] ?? null,
                'oil_v3' => $transformer['oil_v3'] ?? null,
                'oil_v4' => $transformer['oil_v4'] ?? null,
                'oil_v5' => $transformer['oil_v5'] ?? null
            ];
        }
        $firstTransformer = $transformersData[0];
        
        // Prepare data
        $data = [
            'customer_name' => $_POST['customer_name'],
            'address' => $_POST['address'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'other_details' => $_POST['other_details'] ?? null,
            'maintenance_date' => $_POST['maintenance_date'],
            'transformer_power' => $firstTransformer['power'],
            'transformer_type' => $firstTransformer['type'], // New field for transformer type
            'insulation_measurements' => $firstTransformer['insulation'],
            'coil_resistance_measurements' => $firstTransformer['coil_resistance'],
            'grounding_measurement' => $firstTransformer['grounding'],
            'oil_breakdown_v1' => $firstTransformer['oil_v1'],
            'oil_breakdown_v2' => $firstTransformer['oil_v2'],
            'oil_breakdown_v3' => $firstTransformer['oil_v3'],
            'oil_breakdown_v4' => $firstTransformer['oil_v4'],
            'oil_breakdown_v5' => $firstTransformer['oil_v5'],
            'observations' => $_POST['observations'] ? trim(str_replace(["\r\n", "\r"], "\n", $_POST['observations'])) : null,
            'photo_path' => $photoPath,
            'transformers_data' => json_encode($transformersData)
        ];
        
        if ($this->maintenanceModel->update($id, $data)) {
            $_SESSION['success'] = 'Η συντήρηση ενημερώθηκε επιτυχώς';
            header("Location: " . BASE_URL . "/maintenances/view/{$id}");
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση της συντήρησης';
            header("Location: " . BASE_URL . "/maintenances/edit/{$id}");
        }
        exit;
    }
    
    /**
     * Delete maintenance
     */
    public function delete($id) {

        
        if ($this->maintenanceModel->delete($id)) {
            $_SESSION['success'] = 'Η συντήρηση διαγράφηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή της συντήρησης';
        }
        
        header('Location: ' . BASE_URL . '/maintenances');
        exit;
    }
    
    /**
     * Upload photo
     */
    private function uploadPhoto($file) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Μη έγκυρος τύπος αρχείου. Επιτρέπονται μόνο εικόνες (JPG, PNG, GIF)'];
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Το αρχείο είναι πολύ μεγάλο. Μέγιστο μέγεθος: 5MB'];
        }
        
        // Create uploads directory if not exists
        $uploadDir = __DIR__ . '/../uploads/maintenances/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('maintenance_') . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'path' => 'uploads/maintenances/' . $filename];
        } else {
            return ['success' => false, 'error' => 'Σφάλμα κατά τη μεταφορά του αρχείου'];
        }
    }
    
    /**
     * Delete photo
     */
    public function deletePhoto($id) {

        
        $maintenance = $this->maintenanceModel->find($id);
        
        if (!$maintenance) {
            echo json_encode(['success' => false, 'message' => 'Η συντήρηση δεν βρέθηκε']);
            exit;
        }
        
        if (!empty($maintenance['photo_path'])) {
            $photoPath = __DIR__ . '/../' . $maintenance['photo_path'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
            
            // Update database
            $this->maintenanceModel->update($id, array_merge($maintenance, ['photo_path' => null]));
            
            echo json_encode(['success' => true, 'message' => 'Η φωτογραφία διαγράφηκε επιτυχώς']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Δεν υπάρχει φωτογραφία']);
        }
        exit;
    }
    
    /**
     * Export to Excel using template
     */
    public function exportExcel($id) {
        $maintenance = $this->maintenanceModel->find($id);
        
        if (!$maintenance) {
            $_SESSION['error'] = 'Η συντήρηση δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }
        
        require_once 'vendor/autoload.php';
        
        // Parse transformers data
        $transformers = [];
        if (!empty($maintenance['transformers_data'])) {
            $transformers = json_decode($maintenance['transformers_data'], true);
        }
        
        // Fallback to legacy single transformer fields
        if (empty($transformers)) {
            $transformers = [[
                'power' => $maintenance['transformer_power'],
                'type' => $maintenance['transformer_type'] ?? 'oil', // Include type for legacy records
                'insulation' => $maintenance['insulation_measurements'],
                'coil_resistance' => $maintenance['coil_resistance_measurements'],
                'grounding' => $maintenance['grounding_measurement'],
                'oil_v1' => $maintenance['oil_breakdown_v1'],
                'oil_v2' => $maintenance['oil_breakdown_v2'],
                'oil_v3' => $maintenance['oil_breakdown_v3'],
                'oil_v4' => $maintenance['oil_breakdown_v4'],
                'oil_v5' => $maintenance['oil_breakdown_v5']
            ]];
        }
        
        $totalTransformers = count($transformers);
        
        // Determine transformer type for template selection
        // Check if any transformer is dry type
        $hasDryType = false;
        foreach ($transformers as $transformer) {
            if (isset($transformer['type']) && $transformer['type'] === 'dry') {
                $hasDryType = true;
                break;
            }
        }
        
        // Select appropriate template based on number of transformers and type
        if ($hasDryType) {
            // Use dry type templates
            if ($totalTransformers == 1) {
                $templatePath = __DIR__ . '/../templates/maintenance_dry_template_1.xlsx';
            } elseif ($totalTransformers == 2) {
                $templatePath = __DIR__ . '/../templates/maintenance_dry_template_2.xlsx';
            } else {
                // For 3 or more transformers, use the 3-transformer template
                $templatePath = __DIR__ . '/../templates/maintenance_dry_template_3.xlsx';
            }
        } else {
            // Use oil type templates (original templates)
            if ($totalTransformers == 1) {
                $templatePath = __DIR__ . '/../templates/maintenance_template_1.xlsx';
            } elseif ($totalTransformers == 2) {
                $templatePath = __DIR__ . '/../templates/maintenance_template _2.xlsx';
            } else {
                // For 3 or more transformers, use the 3-transformer template
                $templatePath = __DIR__ . '/../templates/maintenance_template_3.xlsx';
            }
        }
        
        // Load the selected template
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        
        // Remove all sheets except the first one
        while ($spreadsheet->getSheetCount() > 1) {
            $spreadsheet->removeSheetByIndex(1);
        }
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Συντηρηση MS');
        
        // Fill in the common data (customer info, dates) - same for all transformers
        // Search and replace placeholders in the first 10 rows
        for ($row = 1; $row <= 10; $row++) {
            foreach (range('A', 'K') as $col) {
                $cellValue = $sheet->getCell($col . $row)->getValue();
                if ($cellValue && is_string($cellValue)) {
                    // Replace placeholders with actual data
                    $cellValue = str_replace('"Πεδίο 1"', $maintenance['customer_name'], $cellValue);
                    $cellValue = str_replace('"Πεδίο 2"', $maintenance['address'] ?? '', $cellValue);
                    $cellValue = str_replace('"Πεδίο 3"', $maintenance['phone'] ?? '', $cellValue);
                    $cellValue = str_replace('"Πεδίο 4"', $maintenance['other_details'] ?? '', $cellValue);
                    $cellValue = str_replace('"Πεδίο 5"', date('d/m/Y', strtotime($maintenance['maintenance_date'])), $cellValue);
                    $cellValue = str_replace('"Πεδίο 6"', date('d/m/Y', strtotime($maintenance['next_maintenance_date'])), $cellValue);
                    $sheet->setCellValue($col . $row, $cellValue);
                }
            }
        }
        
        // Also set fixed positions as fallback (if templates don't have placeholders)
        $sheet->setCellValue('D1', $maintenance['customer_name']);
        $sheet->setCellValue('D2', $maintenance['address'] ?? '');
        $sheet->setCellValue('D3', $maintenance['phone'] ?? '');
        $sheet->setCellValue('D4', $maintenance['other_details'] ?? '');
        $sheet->setCellValue('C6', date('d/m/Y', strtotime($maintenance['maintenance_date'])));
        $sheet->setCellValue('H6', date('d/m/Y', strtotime($maintenance['next_maintenance_date'])));
        
        // Define the base rows for each transformer in the templates
        // These are the starting rows where each transformer section begins
        $transformerBaseRows = [];
        
        // Different positioning for dry type vs oil type templates
        if ($hasDryType) {
            // Dry type templates - specific positioning as per template requirements
            if ($totalTransformers == 1) {
                $transformerBaseRows = [39]; // Single transformer starts at row 39
            } elseif ($totalTransformers == 2) {
                $transformerBaseRows = [39, 69]; // First at 39, second at 69
            } elseif ($totalTransformers == 3) {
                $transformerBaseRows = [39, 69, 100]; // First at 39, second at 69, third at 100
            } else {
                // For more than 3 transformers, extend the pattern
                $transformerBaseRows = [39, 69, 100]; // Base pattern
                for ($i = 3; $i < $totalTransformers; $i++) {
                    // Continue pattern with approximately 31 row spacing
                    $transformerBaseRows[] = 100 + (31 * ($i - 2));
                }
            }
        } else {
            // Oil type templates (original positioning)
            if ($totalTransformers == 1) {
                $transformerBaseRows = [39]; // Single transformer starts at row 39
            } elseif ($totalTransformers == 2) {
                $transformerBaseRows = [39, 81]; // First at 39, second at 81
            } elseif ($totalTransformers == 3) {
                $transformerBaseRows = [39, 81, 122]; // First at 39, second at 81, third at 122
            } else {
                // For more than 3 transformers, calculate positioning dynamically
                $transformerBaseRows = [39]; // First always at 39
                for ($i = 1; $i < $totalTransformers; $i++) {
                    // Pattern: 39, 81, 122, 164, 206... (39 + 42*i - i for i>=1)
                    $transformerBaseRows[] = 39 + (41 * $i) + $i;
                }
            }
        }
        
        // Fill data for each transformer
        foreach ($transformers as $index => $transformer) {
            // Get the base row for this transformer
            $baseRow = $transformerBaseRows[$index];
            
            // Πεδίο 7: Ισχύς Μ/Σ - Replace "Πεδίο 7" with actual power
            $currentValue = $sheet->getCell('A' . ($baseRow + 2))->getValue();
            if ($currentValue) {
                $sheet->setCellValue('A' . ($baseRow + 2), str_replace('"Πεδίο 7"', $transformer['power'], $currentValue));
            }
            
            // Find and replace "XXX" with actual transformer power
            // Template has: ΜΕΤΡΗΣΕΙΣ Μ/Σ "XXX" ΚVA
            for ($r = $baseRow; $r <= $baseRow + 40; $r++) {
                foreach (range('A', 'K') as $col) {
                    $cellValue = $sheet->getCell($col . $r)->getValue();
                    if ($cellValue && is_string($cellValue) && strpos($cellValue, 'XXX') !== false) {
                        $newValue = str_replace('XXX', $transformer['power'], $cellValue);
                        $sheet->setCellValue($col . $r, $newValue);
                    }
                }
            }
            
            // Πεδίο 8: Μετρήσεις Μόνωσης
            $insulation = $transformer['insulation'];
            $sheet->setCellValue('D' . ($baseRow + 6), $insulation);
            $sheet->setCellValue('D' . ($baseRow + 7), $insulation);
            $sheet->setCellValue('D' . ($baseRow + 8), $insulation);
            $sheet->setCellValue('B' . ($baseRow + 20), $insulation);
            $sheet->setCellValue('E' . ($baseRow + 20), $insulation);
            $sheet->setCellValue('H' . ($baseRow + 20), $insulation);
            $sheet->setCellValue('B' . ($baseRow + 23), $insulation);
            $sheet->setCellValue('E' . ($baseRow + 23), $insulation);
            $sheet->setCellValue('H' . ($baseRow + 23), $insulation);
            
            // Πεδίο 9: Μετρήσεις Αντίστασης Πηνίων
            $coilResistance = $transformer['coil_resistance'];
            $sheet->setCellValue('B' . ($baseRow + 11), $coilResistance); // VU
            $sheet->setCellValue('E' . ($baseRow + 11), $coilResistance); // UW
            $sheet->setCellValue('H' . ($baseRow + 11), $coilResistance); // WV
            
            // Πεδίο 10: Μέτρηση Γείωσης
            $sheet->setCellValue('B' . ($baseRow + 17), $transformer['grounding']);
            
            // Πεδίο 11-15: Διηλεκτρική Αντοχή Λαδιού
            $sheet->setCellValue('A' . ($baseRow + 27), $transformer['oil_v1'] ?? '');
            $sheet->setCellValue('B' . ($baseRow + 27), $transformer['oil_v2'] ?? '');
            $sheet->setCellValue('D' . ($baseRow + 27), $transformer['oil_v3'] ?? '');
            $sheet->setCellValue('F' . ($baseRow + 27), $transformer['oil_v4'] ?? '');
            $sheet->setCellValue('H' . ($baseRow + 27), $transformer['oil_v5'] ?? '');
        }
        
        // Fill observations - search for "Πεδίο 16" placeholder and replace it
        $foundPedio = false;
        
        // Calculate search area based on template type and number of transformers
        if ($hasDryType) {
            // Dry type templates positioning
            if ($totalTransformers == 1) {
                $searchStartRow = 75; // After single transformer section
            } elseif ($totalTransformers == 2) {
                $searchStartRow = 105; // After 2nd transformer at row 69
            } else {
                $searchStartRow = 135; // After 3rd transformer at row 100
            }
        } else {
            // Oil type templates positioning (original)
            $searchStartRow = ($totalTransformers == 1) ? 85 : 
                             (($totalTransformers == 2) ? 120 : 160);
        }
        
        $searchEndRow = $searchStartRow + 20;
        
        for ($r = $searchStartRow; $r < $searchEndRow; $r++) {
            foreach (range('A', 'K') as $col) {
                $cellValue = $sheet->getCell($col . $r)->getValue();
                if ($cellValue && is_string($cellValue) && 
                    (stripos($cellValue, 'Πεδίο') !== false || stripos($cellValue, 'Πεδιο') !== false)) {
                    // Set observations with proper line breaks for Excel
                    $observations = $maintenance['observations'] ?? '';
                    // Convert HTML line breaks and normalize for Excel
                    $observations = str_replace(['<br>', '<br/>', '<br />'], "\n", $observations);
                    $sheet->setCellValue($col . $r, $observations);
                    
                    // Enable text wrapping for the cell
                    $sheet->getStyle($col . $r)->getAlignment()->setWrapText(true);
                    
                    // Calculate and set appropriate row height based on content
                    $lineCount = substr_count($observations, "\n") + 1; // Count lines
                    $minHeight = 20; // Minimum height
                    $lineHeight = 15; // Height per line
                    $calculatedHeight = max($minHeight, $lineCount * $lineHeight);
                    $sheet->getRowDimension($r)->setRowHeight($calculatedHeight);
                    
                    $foundPedio = true;
                    break 2;
                }
            }
        }
        
        if (!$foundPedio) {
            error_log("WARNING: Could not find 'Πεδίο' placeholder for observations");
        }
        
        // Note: Photo is only for viewing in the app, not exported to Excel
        
        // Set the first sheet as active
        $spreadsheet->setActiveSheetIndex(0);
        
        // Output - Format: Συντήρηση υποσταθμού_ονομαπελατη_dd_mm_yyyy.xlsx
        // Use customer name as is (browsers handle UTF-8 filenames)
        $customerName = $maintenance['customer_name'];
        $dateFormatted = date('d_m_Y', strtotime($maintenance['maintenance_date']));
        
        // Debug: Log the customer name to see what we're getting
        error_log("DEBUG - Customer name from DB: '" . $maintenance['customer_name'] . "'");
        error_log("DEBUG - Customer name used: '" . $customerName . "'");
        
        $filename = 'Συντήρηση υποσταθμού_' . $customerName . '_' . $dateFormatted . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Export to PDF using Word template
     */
    public function exportPDF($id) {
        $maintenance = $this->maintenanceModel->find($id);
        
        if (!$maintenance) {
            $_SESSION['error'] = 'Η συντήρηση δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/maintenances');
            exit;
        }
        
        require_once 'vendor/autoload.php';
        
        // Parse transformers data
        $transformers = [];
        if (!empty($maintenance['transformers_data'])) {
            $transformers = json_decode($maintenance['transformers_data'], true);
        }
        
        // Fallback to legacy single transformer
        if (empty($transformers)) {
            $transformers = [[
                'power' => $maintenance['transformer_power'],
                'insulation' => $maintenance['insulation_measurements'],
                'coil_resistance' => $maintenance['coil_resistance_measurements'],
                'grounding' => $maintenance['grounding_measurement'],
                'oil_v1' => $maintenance['oil_breakdown_v1'],
                'oil_v2' => $maintenance['oil_breakdown_v2'],
                'oil_v3' => $maintenance['oil_breakdown_v3'],
                'oil_v4' => $maintenance['oil_breakdown_v4'],
                'oil_v5' => $maintenance['oil_breakdown_v5']
            ]];
        }
        
        $templatePath = __DIR__ . '/../templates/maintenance certificate template.docx';
        
        // Load the Word template
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
        
        // Replace placeholders with actual data using PhpWord setValue method
        $templateProcessor->setValue('DATENOW', date('d/m/Y'));
        $templateProcessor->setValue('Όνομα_Πελάτη', $maintenance['customer_name']);
        $templateProcessor->setValue('Διεύθυνση', $maintenance['address'] ?? '');
        $templateProcessor->setValue('Ημερομηνία_Συντήρησης', date('d/m/Y', strtotime($maintenance['maintenance_date'])));
        
        // Generate filename
        $customerName = $maintenance['customer_name'];
        $dateFormatted = date('d_m_Y', strtotime($maintenance['maintenance_date']));
        $filename = 'Πιστοποιητικό συντήρησης_' . $customerName . '_' . $dateFormatted . '.docx';
        
        // Send Word file directly (since PDF conversion requires LibreOffice)
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $templateProcessor->saveAs('php://output');
        exit;
    }
}
