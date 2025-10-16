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
     * Display materials list
     */
    public function index() {
        $this->checkAuth();
        
        // Get filters
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'search' => $_GET['search'] ?? null,
            'is_active' => isset($_GET['show_inactive']) ? null : 1
        ];
        
        // Get materials
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
}
