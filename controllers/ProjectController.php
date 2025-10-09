<?php
/**
 * Project Controller
 * Handles project management operations
 */

class ProjectController extends BaseController {
    
    /**
     * Show projects list
     */
    public function index() {
        $user = $this->getCurrentUser();
        
        // Get filters from request
        $filters = [
            'status' => $_GET['status'] ?? '',
            'category' => $_GET['category'] ?? '',
            'technician' => $_GET['technician'] ?? '',
            'customer' => $_GET['customer'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Get current page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get projects
        $projectModel = new Project();
        $result = $projectModel->getPaginated($page, ITEMS_PER_PAGE, $filters);
        
        // Get filter options
        $database = new Database();
        $db = $database->connect();
        
        // Get all technicians
        $stmt = $db->query("SELECT id, first_name, last_name FROM users WHERE role IN ('admin', 'technician') AND is_active = 1 ORDER BY first_name");
        $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get project categories
        $categories = [
            'electrical' => 'Ηλεκτρολογικά',
            'plumbing' => 'Υδραυλικά',
            'installation' => 'Εγκατάσταση',
            'repair' => 'Επισκευή',
            'maintenance' => 'Συντήρηση',
            'other' => 'Άλλο'
        ];
        
        // Get project statuses
        $statuses = [
            'pending' => 'Σε αναμονή',
            'in_progress' => 'Σε εξέλιξη',
            'on_hold' => 'Σε αναστολή',
            'completed' => 'Ολοκληρωμένο',
            'cancelled' => 'Ακυρωμένο'
        ];
        
        $data = [
            'title' => 'Έργα - ' . APP_NAME,
            'user' => $user,
            'projects' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => $filters,
            'technicians' => $technicians,
            'categories' => $categories,
            'statuses' => $statuses
        ];
        
        parent::view('projects/index', $data);
    }
    
    /**
     * Show project details
     */
    public function details() {
        $user = $this->getCurrentUser();
        
        // Δέχεται είτε slug είτε id για backwards compatibility
        $slug = $_GET['slug'] ?? '';
        $id = $_GET['id'] ?? 0;
        
        if (!$slug && !$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό έργου';
            $this->redirect('/projects');
        }
        
        $projectModel = new Project();
        
        // Προτεραιότητα στο slug
        if ($slug) {
            $project = $projectModel->getBySlug($slug);
        } else {
            $project = $projectModel->getWithDetails($id);
        }
        
        if (!$project) {
            $_SESSION['error'] = 'Το έργο δεν βρέθηκε';
            $this->redirect('/projects');
        }
        
        $data = [
            'title' => 'Έργο: ' . $project['title'] . ' - ' . APP_NAME,
            'user' => $user,
            'project' => $project
        ];
        
        parent::view('projects/show', $data);
    }
    
    /**
     * Show create project form
     */
    public function create() {
        $user = $this->getCurrentUser();
        
        // Get pre-selected customer ID from query string (if coming from customer page)
        $preselectedCustomerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        
        // Get customers for dropdown
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->query("SELECT * FROM customers WHERE is_active = 1 ORDER BY first_name, last_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get technicians
        $stmt = $db->query("SELECT id, first_name, last_name FROM users WHERE role IN ('admin', 'technician') AND is_active = 1 ORDER BY first_name");
        $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Categories
        $categories = [
            'electrical' => 'Ηλεκτρολογικά',
            'plumbing' => 'Υδραυλικά',
            'installation' => 'Εγκατάσταση',
            'repair' => 'Επισκευή',
            'maintenance' => 'Συντήρηση',
            'other' => 'Άλλο'
        ];
        
        $data = [
            'title' => 'Νέο Έργο - ' . APP_NAME,
            'user' => $user,
            'customers' => $customers,
            'technicians' => $technicians,
            'categories' => $categories,
            'preselected_customer_id' => $preselectedCustomerId,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        parent::view('projects/create', $data);
    }
    
    /**
     * Store new project
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects/create');
        }
        
        $user = $this->getCurrentUser();
        
        // Validate CSRF token
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/projects/create');
            }
        }
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['title'])) {
            $errors[] = 'Το πεδίο τίτλος είναι υποχρεωτικό';
        }
        
        if (empty($_POST['customer_id'])) {
            $errors[] = 'Το πεδίο πελάτης είναι υποχρεωτικό';
        }
        
        if (empty($_POST['category'])) {
            $errors[] = 'Το πεδίο κατηγορία είναι υποχρεωτικό';
        }
        
        if (empty($_POST['assigned_technician'])) {
            $errors[] = 'Το πεδίο τεχνικός είναι υποχρεωτικό';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/projects/create');
        }
        
        // Create project
        $projectData = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description'] ?? ''),
            'customer_id' => (int)$_POST['customer_id'],
            'category' => $_POST['category'],
            'assigned_technician' => (int)$_POST['assigned_technician'],
            'status' => $_POST['status'] ?? 'new',
            'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'completion_date' => !empty($_POST['completion_date']) ? $_POST['completion_date'] : null,
            'estimated_hours' => !empty($_POST['estimated_hours']) ? (float)$_POST['estimated_hours'] : null,
            'material_cost' => !empty($_POST['material_cost']) ? (float)$_POST['material_cost'] : 0.00,
            'labor_cost' => !empty($_POST['labor_cost']) ? (float)$_POST['labor_cost'] : 0.00,
            'priority' => $_POST['priority'] ?? 'medium',
            'created_by' => $user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $projectModel = new Project();
        $projectId = $projectModel->create($projectData);
        
        if ($projectId) {
            // Generate slug for the new project
            $slug = $projectModel->generateSlug($projectId, $projectData['title']);
            
            $_SESSION['success'] = 'Το έργο δημιουργήθηκε με επιτυχία';
            $this->redirect('/projects/' . $slug);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία του έργου';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/projects/create');
        }
    }
    
    /**
     * Show edit project form
     */
    public function edit() {
        $user = $this->getCurrentUser();
        
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id || $id <= 0) {
            error_log("ProjectController edit() - Invalid ID: " . var_export($_GET, true));
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό έργου';
            $this->redirect('/projects');
        }
        
        $projectModel = new Project();
        $project = $projectModel->find($id);
        
        if (!$project) {
            $_SESSION['error'] = 'Το έργο δεν βρέθηκε';
            $this->redirect('/projects');
        }
        
        // Get customers
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->query("SELECT * FROM customers WHERE is_active = 1 ORDER BY first_name, last_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get technicians
        $stmt = $db->query("SELECT id, first_name, last_name FROM users WHERE role IN ('admin', 'technician') AND is_active = 1 ORDER BY first_name");
        $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Categories
        $categories = [
            'electrical' => 'Ηλεκτρολογικά',
            'plumbing' => 'Υδραυλικά',
            'installation' => 'Εγκατάσταση',
            'repair' => 'Επισκευή',
            'maintenance' => 'Συντήρηση',
            'other' => 'Άλλο'
        ];
        
        $data = [
            'title' => 'Επεξεργασία Έργου - ' . APP_NAME,
            'user' => $user,
            'project' => $project,
            'customers' => $customers,
            'technicians' => $technicians,
            'categories' => $categories,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        parent::view('projects/edit', $data);
    }
    
    /**
     * Update project
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects');
        }
        
        $user = $this->getCurrentUser();
        
        // Get id from GET parameter (comes from form action URL)
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id || $id <= 0) {
            error_log("ProjectController update() - Invalid ID: " . var_export($_GET, true));
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό έργου';
            $this->redirect('/projects');
        }
        
        // Validate CSRF token
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/projects/edit?id=' . $id);
            }
        }
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['title'])) {
            $errors[] = 'Το πεδίο τίτλος είναι υποχρεωτικό';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/projects/edit?id=' . $id);
        }
        
        // Update project
        $projectData = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description'] ?? ''),
            'customer_id' => (int)$_POST['customer_id'],
            'category' => $_POST['category'],
            'assigned_technician' => (int)$_POST['assigned_technician'],
            'status' => $_POST['status'],
            'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'completion_date' => !empty($_POST['completion_date']) ? $_POST['completion_date'] : null,
            'estimated_hours' => !empty($_POST['estimated_hours']) ? (float)$_POST['estimated_hours'] : null,
            'material_cost' => !empty($_POST['material_cost']) ? (float)$_POST['material_cost'] : 0.00,
            'labor_cost' => !empty($_POST['labor_cost']) ? (float)$_POST['labor_cost'] : 0.00,
            'notes' => trim($_POST['notes'] ?? ''),
            'priority' => $_POST['priority'] ?? 'medium',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $projectModel = new Project();
        $success = $projectModel->update($id, $projectData);
        
        if ($success) {
            // Regenerate slug if title changed
            $slug = $projectModel->generateSlug($id, $projectData['title']);
            
            $_SESSION['success'] = 'Το έργο ενημερώθηκε με επιτυχία';
            $this->redirect('/projects/' . $slug);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του έργου';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/projects/edit/' . $id);
        }
    }
    
    /**
     * Delete project
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects');
        }
        
        $user = $this->getCurrentUser();
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό έργου';
            $this->redirect('/projects');
        }
        
        // Only admins can delete projects
        if ($user['role'] !== 'admin') {
            $_SESSION['error'] = 'Δεν έχετε δικαίωμα διαγραφής έργων';
            $this->redirect('/projects');
        }
        
        $projectModel = new Project();
        $success = $projectModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Το έργο διαγράφηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή του έργου';
        }
        
        $this->redirect('/projects');
    }
    
    /**
     * Quick update project status
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects');
        }
        
        try {
            $this->validateCsrfToken();
            
            $projectId = $_POST['project_id'] ?? null;
            $newStatus = $_POST['status'] ?? null;
            
            if (!$projectId || !$newStatus) {
                throw new Exception('Λείπουν απαραίτητα πεδία');
            }
            
            // Validate status
            $validStatuses = ['new', 'in_progress', 'completed', 'invoiced', 'cancelled'];
            if (!in_array($newStatus, $validStatuses)) {
                throw new Exception('Μη έγκυρο status');
            }
            
            // Check if project exists
            $projectModel = new Project();
            $project = $projectModel->find($projectId);
            
            if (!$project) {
                throw new Exception('Το έργο δεν βρέθηκε');
            }
            
            // Update status
            $sql = "UPDATE projects SET status = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [$newStatus, $projectId]);
            
            // If status is completed and no completion_date, set it to today
            if ($newStatus === 'completed' && empty($project['completion_date'])) {
                $sql = "UPDATE projects SET completion_date = CURDATE() WHERE id = ?";
                $this->db->execute($sql, [$projectId]);
            }
            
            $statusLabels = [
                'new' => 'Νέο',
                'in_progress' => 'Σε Εξέλιξη',
                'completed' => 'Ολοκληρωμένο',
                'invoiced' => 'Τιμολογημένο',
                'cancelled' => 'Ακυρωμένο'
            ];
            
            $_SESSION['success'] = 'Το status του έργου άλλαξε σε "' . $statusLabels[$newStatus] . '"';
            $this->redirect('/projects/show?id=' . $projectId);
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            if (isset($_POST['project_id'])) {
                $this->redirect('/projects/show?id=' . $_POST['project_id']);
            } else {
                $this->redirect('/projects');
            }
        }
    }
}
