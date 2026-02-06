<?php
/**
 * Materials Controller
 * Handles materials catalog management
 */

require_once 'classes/BaseController.php';
require_once 'models/MaterialCatalog.php';
require_once 'models/MaterialCategory.php';

class MaterialsController extends BaseController {
    private $materialModel;
    private $categoryModel;
    
    public function __construct() {
        parent::__construct();
        $this->materialModel = new MaterialCatalog();
        $this->categoryModel = new MaterialCategory();
    }
    
    /**
     * GET /materials
     * Display materials list with pagination
     */
    public function index() {
        $this->checkAuth();
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(10, min(100, (int)$_GET['per_page'])) : 25;
        $offset = ($page - 1) * $perPage;
        
        // Get filters
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'search' => $_GET['search'] ?? null,
            'is_active' => isset($_GET['show_inactive']) ? null : 1,
            'limit' => $perPage,
            'offset' => $offset
        ];
        
        // Get total count for pagination
        $totalMaterials = $this->materialModel->getCount($filters);
        $totalPages = ceil($totalMaterials / $perPage);
        
        // Get materials for current page
        $materials = $this->materialModel->getAll($filters);
        
        // Get categories for filter dropdown
        $categories = $this->categoryModel->getWithMaterialCount();
        
        // Get statistics
        $statistics = $this->materialModel->getStatistics();
        
        $this->view('materials/index', [
            'materials' => $materials,
            'categories' => $categories,
            'statistics' => $statistics,
            'filters' => $filters,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'totalMaterials' => $totalMaterials,
            'pageTitle' => 'Κατάλογος Υλικών'
        ]);
    }
    
    /**
     * GET /materials/add
     * Show add material form
     */
    public function add() {
        $this->checkAuth();
        
        $categories = $this->categoryModel->getAll();
        
        $this->view('materials/form', [
            'categories' => $categories,
            'material' => null,
            'pageTitle' => 'Προσθήκη Υλικού'
        ]);
    }
    
    /**
     * POST /materials/add
     * Create new material
     */
    public function store() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials/add');
            return;
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['name'])) {
            $errors[] = 'Το όνομα του υλικού είναι υποχρεωτικό';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/materials/add');
            return;
        }
        
        // Prepare data
        $data = [
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'unit' => trim($_POST['unit'] ?? ''),
            'default_price' => !empty($_POST['default_price']) ? floatval($_POST['default_price']) : null,
            'supplier' => trim($_POST['supplier'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Create material
        $id = $this->materialModel->create($data);
        
        if ($id) {
            $_SESSION['success'] = 'Το υλικό προστέθηκε επιτυχώς';
            $this->redirect('/materials');
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την προσθήκη του υλικού';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/materials/add');
        }
    }
    
    /**
     * GET /materials/{id}/edit
     * Show edit material form
     */
    public function edit($id) {
        $this->checkAuth();
        
        $material = $this->materialModel->getById($id);
        
        if (!$material) {
            $_SESSION['error'] = 'Το υλικό δεν βρέθηκε';
            $this->redirect('/materials');
            return;
        }
        
        $categories = $this->categoryModel->getAll();
        
        $this->view('materials/form', [
            'material' => $material,
            'categories' => $categories,
            'pageTitle' => 'Επεξεργασία Υλικού'
        ]);
    }
    
    /**
     * POST /materials/{id}/update
     * Update material
     */
    public function update($id) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials/' . $id . '/edit');
            return;
        }
        
        // Validation
        $errors = [];
        
        if (empty($_POST['name'])) {
            $errors[] = 'Το όνομα του υλικού είναι υποχρεωτικό';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/materials/' . $id . '/edit');
            return;
        }
        
        // Prepare data
        $data = [
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? ''),
            'unit' => trim($_POST['unit'] ?? ''),
            'default_price' => !empty($_POST['default_price']) ? floatval($_POST['default_price']) : null,
            'supplier' => trim($_POST['supplier'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Update material
        if ($this->materialModel->update($id, $data)) {
            $_SESSION['success'] = 'Το υλικό ενημερώθηκε επιτυχώς';
            $this->redirect('/materials');
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του υλικού';
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/materials/' . $id . '/edit');
        }
    }
    
    /**
     * POST /materials/{id}/delete
     * Delete material
     */
    public function delete($id) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials');
            return;
        }
        
        if ($this->materialModel->delete($id)) {
            $_SESSION['success'] = 'Το υλικό διαγράφηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή. Το υλικό χρησιμοποιείται σε εργασίες';
        }
        
        $this->redirect('/materials');
    }
    
    /**
     * GET /api/materials/search
     * Search materials for autocomplete (returns JSON)
     */
    public function search() {
        $this->checkAuth();
        
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }
        
        $results = $this->materialModel->search($query, $limit);
        
        // Format for autocomplete
        $formatted = array_map(function($material) {
            return [
                'id' => $material['id'],
                'name' => $material['name'],
                'unit' => $material['unit'] ?? '',
                'price' => $material['default_price'] ?? 0,
                'category' => $material['category_name'] ?? ''
            ];
        }, $results);
        
        echo json_encode($formatted);
    }
    
    /**
     * GET /materials/categories
     * Manage categories
     */
    public function categories() {
        $this->checkAuth();
        
        $categories = $this->categoryModel->getWithMaterialCount();
        
        $this->view('materials/categories', [
            'categories' => $categories,
            'pageTitle' => 'Κατηγορίες Υλικών'
        ]);
    }
    
    /**
     * POST /materials/categories/add
     * Create category
     */
    public function addCategory() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials/categories');
            return;
        }
        
        if (empty($_POST['name'])) {
            $_SESSION['error'] = 'Το όνομα της κατηγορίας είναι υποχρεωτικό';
            $this->redirect('/materials/categories');
            return;
        }
        
        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? '')
        ];
        
        if ($this->categoryModel->create($data)) {
            $_SESSION['success'] = 'Η κατηγορία προστέθηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την προσθήκη της κατηγορίας';
        }
        
        $this->redirect('/materials/categories');
    }
    
    /**
     * POST /materials/categories/{id}/update
     * Update category
     */
    public function updateCategory($id) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials/categories');
            return;
        }
        
        if (empty($_POST['name'])) {
            $_SESSION['error'] = 'Το όνομα της κατηγορίας είναι υποχρεωτικό';
            $this->redirect('/materials/categories');
            return;
        }
        
        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'] ?? '')
        ];
        
        if ($this->categoryModel->update($id, $data)) {
            $_SESSION['success'] = 'Η κατηγορία ενημερώθηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση της κατηγορίας';
        }
        
        $this->redirect('/materials/categories');
    }
    
    /**
     * POST /materials/categories/{id}/delete
     * Delete category
     */
    public function deleteCategory($id) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/materials/categories');
            return;
        }
        
        if ($this->categoryModel->delete($id)) {
            $_SESSION['success'] = 'Η κατηγορία διαγράφηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Δεν μπορεί να διαγραφεί κατηγορία που περιέχει υλικά';
        }
        
        $this->redirect('/materials/categories');
    }
    
    /**
     * Regenerate aliases for all materials (Admin tool)
     */
    public function regenerateAliases() {
        $this->checkAuth();
        
        require_once 'classes/MaterialAliasGenerator.php';
        
        // Get all materials
        $db = new Database();
        $sql = "SELECT id, name FROM materials_catalog";
        $stmt = $db->execute($sql);
        $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updated = 0;
        $results = [];
        
        foreach ($materials as $material) {
            $aliases = MaterialAliasGenerator::generate($material['name']);
            
            $updateSql = "UPDATE materials_catalog SET aliases = ? WHERE id = ?";
            $updateStmt = $db->execute($updateSql, [$aliases, $material['id']]);
            
            if ($updateStmt) {
                $updated++;
                $results[] = [
                    'name' => $material['name'],
                    'aliases' => $aliases
                ];
            }
        }
        
        // Display results
        $pageTitle = 'Aliases Regenerated';
        require_once 'views/materials/regenerate_aliases_result.php';
    }
    
    /**
     * GET /materials/export
     * Export materials to CSV
     */
    public function exportCSV() {
        $this->checkAuth();
        
        // Get all materials without pagination
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'search' => $_GET['search'] ?? null,
            'is_active' => isset($_GET['show_inactive']) ? null : 1
        ];
        
        $materials = $this->materialModel->getAll($filters);
        
        // Prepare CSV
        $filename = 'materials_export_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, [
            'ID',
            'Όνομα',
            'Περιγραφή',
            'Κατηγορία',
            'Μονάδα',
            'Τιμή (€)',
            'Απόθεμα',
            'Ελάχιστο Απόθεμα',
            'Προμηθευτής',
            'Κωδικός Προμηθευτή',
            'Κατάσταση'
        ]);
        
        // Data rows
        foreach ($materials as $material) {
            fputcsv($output, [
                $material['id'],
                $material['name'],
                $material['description'] ?? '',
                $material['category_name'] ?? '',
                $material['unit'] ?? '',
                $material['default_price'] ?? '',
                $material['current_stock'] ?? '',
                $material['min_stock'] ?? '',
                $material['supplier'] ?? '',
                $material['supplier_code'] ?? '',
                $material['is_active'] ? 'Ενεργό' : 'Ανενεργό'
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * POST /materials/import
     * Import materials from CSV
     */
    public function importCSV() {
        $this->checkAuth();
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        // Get JSON data
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!isset($data['materials']) || !is_array($data['materials'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid data format']);
            exit;
        }
        
        $imported = 0;
        $errors = [];
        
        // Map Greek headers to database fields
        $headerMap = [
            'Όνομα' => 'name',
            'Περιγραφή' => 'description',
            'Κατηγορία' => 'category',
            'Μονάδα' => 'unit',
            'Τιμή' => 'default_price',
            'Προμηθευτής' => 'supplier',
            'Κωδικός Προμηθευτή' => 'supplier_code'
        ];
        
        foreach ($data['materials'] as $index => $material) {
            try {
                // Map headers
                $mappedData = [];
                foreach ($material as $key => $value) {
                    $mappedKey = $headerMap[$key] ?? $key;
                    $mappedData[$mappedKey] = $value;
                }
                
                // Validate required fields
                if (empty($mappedData['name'])) {
                    $errors[] = "Γραμμή " . ($index + 2) . ": Λείπει το όνομα";
                    continue;
                }
                
                // Find or create category
                $categoryId = null;
                if (!empty($mappedData['category'])) {
                    $categories = $this->categoryModel->getAll();
                    foreach ($categories as $cat) {
                        if (mb_strtolower($cat['name']) === mb_strtolower($mappedData['category'])) {
                            $categoryId = $cat['id'];
                            break;
                        }
                    }
                }
                
                // Prepare data for insertion
                $insertData = [
                    'name' => $mappedData['name'],
                    'description' => $mappedData['description'] ?? null,
                    'category_id' => $categoryId,
                    'unit' => $mappedData['unit'] ?? null,
                    'default_price' => !empty($mappedData['default_price']) ? floatval($mappedData['default_price']) : null,
                    'supplier' => $mappedData['supplier'] ?? null,
                    'supplier_code' => $mappedData['supplier_code'] ?? null,
                    'is_active' => 1
                ];
                
                // Insert material
                if ($this->materialModel->create($insertData)) {
                    $imported++;
                }
                
            } catch (Exception $e) {
                $errors[] = "Γραμμή " . ($index + 2) . ": " . $e->getMessage();
            }
        }
        
        echo json_encode([
            'success' => true,
            'imported' => $imported,
            'errors' => $errors
        ]);
        exit;
    }
}
