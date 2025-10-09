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
            'title' => 'Υλικά - ' . APP_NAME,
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
            'title' => 'Νέο Υλικό - ' . APP_NAME,
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
            'title' => 'Επεξεργασία Υλικού - ' . APP_NAME,
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
    public function delete() {
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
        
        $id = $_POST['id'] ?? 0;
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό υλικού';
            $this->redirect('/materials');
        }
        
        $materialModel = new Material();
        $success = $materialModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Το υλικό διαγράφηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή του υλικού';
        }
        
        $this->redirect('/materials');
    }
}
