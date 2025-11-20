<?php
/**
 * Material Controller
 * Handles material/inventory management
 */

class MaterialController extends BaseController {
    
    /**
     * Show materials list
     */
    public function index() {
        $user = $this->getCurrentUser();
        
        // Get filters
        $filters = [
            'category' => $_GET['category'] ?? '',
            'low_stock' => $_GET['low_stock'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        $materialModel = new Material();
        $result = $materialModel->getPaginated($page, ITEMS_PER_PAGE, $filters);
        $stats = $materialModel->getStatistics();
        
        // Categories
        $categories = ['electrical' => 'Ηλεκτρολογικά', 'plumbing' => 'Υδραυλικά', 'tools' => 'Εργαλεία', 'other' => 'Άλλα'];
        
        $data = [
            'title' => __('materials.title') . ' - ' . APP_NAME,
            'user' => $user,
            'materials' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => $filters,
            'categories' => $categories,
            'stats' => $stats
        ];
        
        $this->view('materials/index', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $user = $this->getCurrentUser();
        
        $categories = ['electrical' => 'Ηλεκτρολογικά', 'plumbing' => 'Υδραυλικά', 'tools' => 'Εργαλεία', 'other' => 'Άλλα'];
        
        $data = [
            'title' => __('materials.new_material') . ' - ' . APP_NAME,
            'user' => $user,
            'categories' => $categories
        ];
        
        $this->view('materials/create', $data);
    }
    
    /**
     * Store new material
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials');
        }
        
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/materials/create');
            }
        }
        
        $user = $this->getCurrentUser();
        
        $errors = [];
        if (empty($_POST['name'])) $errors[] = 'Το όνομα είναι υποχρεωτικό';
        if (empty($_POST['category'])) $errors[] = 'Η κατηγορία είναι υποχρεωτική';
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/materials/create');
        }
        
        $materialData = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'category' => $_POST['category'],
            'unit' => trim($_POST['unit'] ?? 'τεμ'),
            'unit_price' => (float)($_POST['unit_price'] ?? 0),
            'current_stock' => (float)($_POST['current_stock'] ?? 0),
            'min_stock' => (float)($_POST['min_stock'] ?? 0),
            'supplier' => trim($_POST['supplier'] ?? ''),
            'supplier_code' => trim($_POST['supplier_code'] ?? ''),
            'created_by' => $_SESSION['user_id']
        ];
        
        $materialModel = new Material();
        $id = $materialModel->create($materialData);
        
        if ($id) {
            $_SESSION['success'] = 'Το υλικό δημιουργήθηκε με επιτυχία';
            $this->redirect('/materials');
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία του υλικού';
            $this->redirect('/materials/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit() {
        $user = $this->getCurrentUser();
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό υλικού';
            $this->redirect('/materials');
        }
        
        $materialModel = new Material();
        $material = $materialModel->find($id);
        
        if (!$material) {
            $_SESSION['error'] = 'Το υλικό δεν βρέθηκε';
            $this->redirect('/materials');
        }
        
        $categories = ['electrical' => 'Ηλεκτρολογικά', 'plumbing' => 'Υδραυλικά', 'tools' => 'Εργαλεία', 'other' => 'Άλλα'];
        
        $data = [
            'title' => __('materials.edit') . ' ' . __('materials.title') . ' - ' . APP_NAME,
            'user' => $user,
            'material' => $material,
            'categories' => $categories
        ];
        
        $this->view('materials/edit', $data);
    }
    
    /**
     * Update material
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials');
        }
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό υλικού';
            $this->redirect('/materials');
        }
        
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/materials/edit?id=' . $id);
            }
        }
        
        // Validation
        $errors = [];
        if (empty($_POST['name'])) {
            $errors[] = 'Το όνομα είναι υποχρεωτικό';
        }
        if (empty($_POST['category'])) {
            $errors[] = 'Η κατηγορία είναι υποχρεωτική';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/materials/edit?id=' . $id);
        }
        
        $materialData = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'category' => $_POST['category'],
            'unit' => trim($_POST['unit'] ?? 'τεμ'),
            'unit_price' => (float)($_POST['unit_price'] ?? 0),
            'current_stock' => (float)($_POST['current_stock'] ?? 0),
            'min_stock' => (float)($_POST['min_stock'] ?? 0),
            'supplier' => trim($_POST['supplier'] ?? ''),
            'supplier_code' => trim($_POST['supplier_code'] ?? '')
        ];
        
        $materialModel = new Material();
        $success = $materialModel->update($id, $materialData);
        
        if ($success) {
            $_SESSION['success'] = 'Το υλικό ενημερώθηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του υλικού';
        }
        
        $this->redirect('/materials');
    }
    
    /**
     * Delete material
     */
    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials');
        }
        
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/materials');
            }
        }
        
        // Get ID from parameter or POST
        if (!$id) {
            $id = $_POST['id'] ?? 0;
        }
        
        // Cast to integer
        $id = (int)$id;
        
        if (!$id || $id <= 0) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό υλικού';
            $this->redirect('/materials');
        }
        
        $materialModel = new Material();
        
        // Delete the material permanently
        if ($materialModel->delete($id)) {
            $_SESSION['success'] = 'Το υλικό διαγράφηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή του υλικού';
        }
        
        $this->redirect('/materials');
    }
    
    /**
     * Export materials to CSV
     */
    public function export() {
        $materialModel = new Material();
        
        // Get all materials (no pagination)
        $filters = [
            'category' => $_GET['category'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $materials = $materialModel->getAllForExport($filters);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="materials_export_' . date('Y-m-d') . '.csv"');
        
        // Output UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Write header row
        fputcsv($output, [
            'Όνομα',
            'Περιγραφή',
            'Κατηγορία',
            'Μονάδα',
            'Τιμή',
            'Απόθεμα',
            'Ελάχιστο Απόθεμα',
            'Προμηθευτής',
            'Κωδικός Προμηθευτή'
        ]);
        
        // Write data rows
        foreach ($materials as $material) {
            fputcsv($output, [
                $material['name'],
                $material['description'] ?? '',
                $material['category_name'] ?? '',
                $material['unit'] ?? '',
                $material['unit_price'] ?? '0',
                $material['current_stock'] ?? '0',
                $material['min_stock'] ?? '0',
                $material['supplier'] ?? '',
                $material['supplier_code'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Import materials from CSV
     */
    public function import() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        // Read JSON input
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!isset($data['materials']) || !is_array($data['materials'])) {
            echo json_encode(['success' => false, 'error' => 'Μη έγκυρα δεδομένα']);
            exit;
        }
        
        $materialModel = new Material();
        $categoryModel = new MaterialCategory();
        $categories = $categoryModel->getAll();
        
        // Create category mapping (name to id)
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[strtolower(trim($cat['name']))] = $cat['id'];
        }
        
        $imported = 0;
        $errors = [];
        
        foreach ($data['materials'] as $index => $materialData) {
            try {
                // Map CSV columns to database fields
                $name = trim($materialData['Όνομα'] ?? $materialData['name'] ?? '');
                $description = trim($materialData['Περιγραφή'] ?? $materialData['description'] ?? '');
                $categoryName = trim($materialData['Κατηγορία'] ?? $materialData['category'] ?? '');
                $unit = trim($materialData['Μονάδα'] ?? $materialData['unit'] ?? 'τεμ');
                $price = (float)($materialData['Τιμή'] ?? $materialData['price'] ?? 0);
                $stock = (float)($materialData['Απόθεμα'] ?? $materialData['stock'] ?? 0);
                $minStock = (float)($materialData['Ελάχιστο Απόθεμα'] ?? $materialData['min_stock'] ?? 0);
                $supplier = trim($materialData['Προμηθευτής'] ?? $materialData['supplier'] ?? '');
                $supplierCode = trim($materialData['Κωδικός Προμηθευτή'] ?? $materialData['supplier_code'] ?? '');
                
                if (empty($name)) {
                    $errors[] = "Σειρά " . ($index + 2) . ": Το όνομα είναι υποχρεωτικό";
                    continue;
                }
                
                // Find or create category
                $categoryId = null;
                $categoryNameLower = strtolower($categoryName);
                
                if (isset($categoryMap[$categoryNameLower])) {
                    $categoryId = $categoryMap[$categoryNameLower];
                } else if (!empty($categoryName)) {
                    // Create new category
                    $newCategoryId = $categoryModel->create([
                        'name' => $categoryName,
                        'description' => 'Αυτόματη δημιουργία από εισαγωγή',
                        'created_by' => $_SESSION['user_id']
                    ]);
                    
                    if ($newCategoryId) {
                        $categoryId = $newCategoryId;
                        $categoryMap[$categoryNameLower] = $newCategoryId;
                    }
                }
                
                // Create material
                $materialId = $materialModel->create([
                    'name' => $name,
                    'description' => $description,
                    'category_id' => $categoryId,
                    'unit' => $unit,
                    'unit_price' => $price,
                    'current_stock' => $stock,
                    'min_stock' => $minStock,
                    'supplier' => $supplier,
                    'supplier_code' => $supplierCode,
                    'created_by' => $_SESSION['user_id']
                ]);
                
                if ($materialId) {
                    $imported++;
                }
                
            } catch (Exception $e) {
                $errors[] = "Σειρά " . ($index + 2) . ": " . $e->getMessage();
            }
        }
        
        if ($imported > 0) {
            echo json_encode([
                'success' => true,
                'imported' => $imported,
                'errors' => $errors
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Δεν εισήχθησαν υλικά',
                'errors' => $errors
            ]);
        }
        exit;
    }
    
    /**
     * Bulk delete materials
     */
    public function bulkDelete() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];
        
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'error' => 'Δεν δόθηκαν υλικά για διαγραφή']);
            exit;
        }
        
        $materialModel = new Material();
        $deleted = 0;
        $errors = [];
        
        foreach ($ids as $id) {
            try {
                if ($materialModel->delete($id)) {
                    $deleted++;
                } else {
                    $errors[] = "Αποτυχία διαγραφής υλικού #$id";
                }
            } catch (Exception $e) {
                $errors[] = "Σφάλμα στο υλικό #$id: " . $e->getMessage();
            }
        }
        
        echo json_encode([
            'success' => $deleted > 0,
            'deleted' => $deleted,
            'errors' => $errors
        ]);
        exit;
    }
    
    /**
     * Bulk activate materials
     */
    public function bulkActivate() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];
        
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'error' => 'Δεν δόθηκαν υλικά για ενεργοποίηση']);
            exit;
        }
        
        $materialModel = new Material();
        $activated = 0;
        
        foreach ($ids as $id) {
            if ($materialModel->updateStatus($id, 1)) {
                $activated++;
            }
        }
        
        echo json_encode([
            'success' => $activated > 0,
            'activated' => $activated
        ]);
        exit;
    }
    
    /**
     * Bulk deactivate materials
     */
    public function bulkDeactivate() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];
        
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'error' => 'Δεν δόθηκαν υλικά για απενεργοποίηση']);
            exit;
        }
        
        $materialModel = new Material();
        $deactivated = 0;
        
        foreach ($ids as $id) {
            if ($materialModel->updateStatus($id, 0)) {
                $deactivated++;
            }
        }
        
        echo json_encode([
            'success' => $deactivated > 0,
            'deactivated' => $deactivated
        ]);
        exit;
    }
    
    /**
     * Check for duplicate materials based on name
     */
    public function checkDuplicates() {
        header('Content-Type: application/json');
        
        $materialModel = new Material();
        $duplicates = $materialModel->findDuplicates();
        
        echo json_encode([
            'success' => true,
            'duplicates' => $duplicates
        ]);
        exit;
    }
}
