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
        // Only admin and supervisor can view projects
        $this->requireSupervisorOrAdmin();
        
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
            'title' => __('projects.title') . ' - ' . APP_NAME,
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
     * Show project details (alias for show)
     */
    public function show() {
        $this->details();
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
        
        // Load tasks data for the Tasks tab
        $tasksCount = 0;
        $tasks = [];
        $summary = [];
        $filters = [
            'task_type' => $_GET['task_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        try {
            $taskModel = new ProjectTask();
            $tasksCount = $taskModel->countByProject($project['id']);
            $tasks = $taskModel->getByProject($project['id'], $filters);
            $summary = $taskModel->getSummary($project['id'], $filters);
        } catch (Exception $e) {
            // Tasks feature not available yet
        }
        
        // Get all photos from all tasks in this project
        $projectPhotos = [
            'before' => [],
            'after' => [],
            'during' => [],
            'issue' => [],
            'other' => []
        ];
        $totalPhotos = 0;
        
        try {
            require_once 'models/TaskPhoto.php';
            $photoModel = new TaskPhoto();
            
            // Get all photos for this project's tasks, grouped by type
            $allPhotos = $photoModel->query(
                "SELECT tp.*, pt.description as task_description, u.username, u.first_name, u.last_name
                 FROM task_photos tp
                 INNER JOIN project_tasks pt ON tp.task_id = pt.id
                 LEFT JOIN users u ON tp.uploaded_by = u.id
                 WHERE pt.project_id = ?
                 ORDER BY tp.photo_type, tp.created_at DESC",
                [$project['id']]
            );
            
            // Group by type
            foreach ($allPhotos as $photo) {
                $projectPhotos[$photo['photo_type']][] = $photo;
                $totalPhotos++;
            }
        } catch (Exception $e) {
            // Photos feature not available yet
            error_log("Photo loading error: " . $e->getMessage());
        }
        
        // Get all labor entries from all tasks in this project
        $laborEntries = [];
        try {
            require_once 'models/ProjectTask.php';
            $taskModel = new ProjectTask();
            
            // Get all labor entries with task and technician info
            $laborEntries = $taskModel->query(
                "SELECT 
                    pt.task_type,
                    pt.task_date,
                    pt.date_from,
                    pt.date_to,
                    pt.description as task_description,
                    tl.technician_id,
                    tl.hours_worked as hours,
                    u.first_name,
                    u.last_name
                 FROM project_tasks pt
                 INNER JOIN task_labor tl ON pt.id = tl.task_id
                 LEFT JOIN users u ON tl.technician_id = u.id
                 WHERE pt.project_id = ?
                 ORDER BY 
                    CASE 
                        WHEN pt.task_type = 'single_day' THEN pt.task_date
                        ELSE pt.date_from
                    END DESC,
                    u.last_name, u.first_name",
                [$project['id']]
            );
        } catch (Exception $e) {
            // Labor entries not available
            error_log("Labor loading error: " . $e->getMessage());
        }
        
        // Get materials summary and detail
        $materialsSummary = [];
        $materialsDetail = [];
        $materialsDateFrom = $_GET['materials_date_from'] ?? '';
        $materialsDateTo = $_GET['materials_date_to'] ?? '';
        
        try {
            // Build date filter for materials query
            $dateFilter = '';
            $params = [$project['id']];
            
            if (!empty($materialsDateFrom) && !empty($materialsDateTo)) {
                $dateFilter = " AND (
                    (pt.task_type = 'single_day' AND pt.task_date BETWEEN ? AND ?)
                    OR
                    (pt.task_type = 'date_range' AND (
                        (pt.date_from BETWEEN ? AND ?) OR 
                        (pt.date_to BETWEEN ? AND ?) OR
                        (pt.date_from <= ? AND pt.date_to >= ?)
                    ))
                )";
                $params[] = $materialsDateFrom;
                $params[] = $materialsDateTo;
                $params[] = $materialsDateFrom;
                $params[] = $materialsDateTo;
                $params[] = $materialsDateFrom;
                $params[] = $materialsDateTo;
                $params[] = $materialsDateFrom;
                $params[] = $materialsDateTo;
            } elseif (!empty($materialsDateFrom)) {
                $dateFilter = " AND (
                    (pt.task_type = 'single_day' AND pt.task_date >= ?)
                    OR
                    (pt.task_type = 'date_range' AND pt.date_to >= ?)
                )";
                $params[] = $materialsDateFrom;
                $params[] = $materialsDateFrom;
            } elseif (!empty($materialsDateTo)) {
                $dateFilter = " AND (
                    (pt.task_type = 'single_day' AND pt.task_date <= ?)
                    OR
                    (pt.task_type = 'date_range' AND pt.date_from <= ?)
                )";
                $params[] = $materialsDateTo;
                $params[] = $materialsDateTo;
            }
            
            // Get materials summary (grouped by material name)
            $materialsSummary = $taskModel->query(
                "SELECT 
                    tm.name,
                    tm.unit,
                    SUM(tm.quantity) as total_quantity,
                    AVG(tm.unit_price) as avg_price,
                    mc.name as category
                 FROM task_materials tm
                 INNER JOIN project_tasks pt ON tm.task_id = pt.id
                 LEFT JOIN materials_catalog mcat ON tm.catalog_material_id = mcat.id
                 LEFT JOIN material_categories mc ON mcat.category_id = mc.id
                 WHERE pt.project_id = ?{$dateFilter}
                 GROUP BY tm.name, tm.unit, mc.name
                 ORDER BY tm.name",
                $params
            );
            
            // Get materials detail (by task)
            $materialsDetail = [];
            $tasksWithMaterials = $taskModel->query(
                "SELECT DISTINCT
                    pt.id as task_id,
                    pt.task_type,
                    CASE 
                        WHEN pt.task_type = 'single_day' THEN pt.task_date
                        ELSE pt.date_from
                    END as task_date,
                    pt.description
                 FROM project_tasks pt
                 INNER JOIN task_materials tm ON pt.id = tm.task_id
                 WHERE pt.project_id = ?{$dateFilter}
                 ORDER BY task_date DESC",
                $params
            );
            
            // For each task, get its materials
            foreach ($tasksWithMaterials as $task) {
                $materials = $taskModel->query(
                    "SELECT name, unit, quantity, unit_price, subtotal
                     FROM task_materials
                     WHERE task_id = ?
                     ORDER BY name",
                    [$task['task_id']]
                );
                
                $materialsDetail[] = [
                    'task_id' => $task['task_id'],
                    'task_date' => $task['task_date'],
                    'description' => $task['description'],
                    'materials' => $materials
                ];
            }
        } catch (Exception $e) {
            // Materials not available
            error_log("Materials loading error: " . $e->getMessage());
        }
        
        $data = [
            'title' => 'Έργο: ' . $project['title'] . ' - ' . APP_NAME,
            'user' => $user,
            'project' => $project,
            'tasksCount' => $tasksCount,
            'tasks' => $tasks,
            'summary' => $summary,
            'filters' => $filters,
            'projectPhotos' => $projectPhotos,
            'totalPhotos' => $totalPhotos,
            'laborEntries' => $laborEntries,
            'materialsSummary' => $materialsSummary,
            'materialsDetail' => $materialsDetail
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
            $this->redirect('/projects/show?id=' . $projectId);
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
            $this->redirect('/projects/show?id=' . $id);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του έργου';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/projects/edit?id=' . $id);
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
            
            // If status is invoiced, create invoice automatically
            if ($newStatus === 'invoiced') {
                $this->createInvoiceForProject($project);
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
    
    /**
     * Create invoice automatically when project is marked as invoiced
     */
    private function createInvoiceForProject($project) {
        try {
            error_log("=== CREATE INVOICE DEBUG ===");
            error_log("Project ID: " . $project['id']);
            error_log("Project data: " . print_r($project, true));
            
            // Check if invoice already exists for this project
            $checkSql = "SELECT id FROM invoices WHERE project_id = ? AND status IN ('paid', 'sent', 'draft')";
            $existing = $this->db->fetchOne($checkSql, [$project['id']]);
            
            if ($existing) {
                error_log("Invoice already exists for project " . $project['id']);
                // Invoice already exists, don't create duplicate
                $_SESSION['success'] .= ' (Υπάρχει ήδη τιμολόγιο)';
                return;
            }
            
            error_log("No existing invoice found, proceeding...");
            
            // Calculate costs from project materials and labor
            $materialsSql = "SELECT COALESCE(SUM(tm.quantity * tm.unit_price), 0) as total 
                            FROM task_materials tm 
                            JOIN tasks t ON tm.task_id = t.id 
                            WHERE t.project_id = ?";
            $materialsResult = $this->db->fetchOne($materialsSql, [$project['id']]);
            $materialsTotal = $materialsResult['total'];
            error_log("Materials total: $materialsTotal");
            
            $laborSql = "SELECT COALESCE(SUM(tl.hours * tl.hourly_rate), 0) as total 
                        FROM task_labor tl 
                        JOIN tasks t ON tl.task_id = t.id 
                        WHERE t.project_id = ?";
            $laborResult = $this->db->fetchOne($laborSql, [$project['id']]);
            $laborTotal = $laborResult['total'];
            error_log("Labor total: $laborTotal");
            
            $subtotal = $materialsTotal + $laborTotal;
            $vatRate = $project['vat_rate'] ?? DEFAULT_VAT_RATE;
            $vatAmount = $subtotal * ($vatRate / 100);
            $totalAmount = $subtotal + $vatAmount;
            
            error_log("Subtotal: $subtotal, VAT: $vatAmount, Total: $totalAmount");
            
            // If total is 0, don't create invoice
            if ($totalAmount == 0) {
                error_log("Total is 0, not creating invoice");
                $_SESSION['success'] .= ' (Δεν δημιουργήθηκε τιμολόγιο - το έργο δεν έχει κόστη)';
                return;
            }
            
            // Generate unique invoice number
            $year = date('Y');
            $lastInvoiceSql = "SELECT invoice_number FROM invoices 
                              WHERE invoice_number LIKE ? 
                              ORDER BY id DESC LIMIT 1";
            $lastInvoice = $this->db->fetchOne($lastInvoiceSql, ["INV-{$year}-%"]);
            
            if ($lastInvoice) {
                $lastNumber = (int) substr($lastInvoice['invoice_number'], -4);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
            
            $invoiceNumber = "INV-{$year}-{$newNumber}";
            
            // Create invoice
            $invoiceSql = "INSERT INTO invoices (
                invoice_number, customer_id, project_id, title, description,
                subtotal, vat_rate, vat_amount, total_amount, paid_amount,
                status, issue_date, due_date, paid_date, payment_method,
                created_by, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $this->db->execute($invoiceSql, [
                $invoiceNumber,
                $project['customer_id'],
                $project['id'],
                'Τιμολόγιο για: ' . $project['title'],
                $project['description'],
                $subtotal,
                $vatRate,
                $vatAmount,
                $totalAmount,
                $totalAmount, // paid_amount = total (marked as fully paid)
                'paid', // status = paid
                date('Y-m-d'), // issue_date = today
                date('Y-m-d'), // due_date = today (already paid)
                date('Y-m-d'), // paid_date = today
                'cash', // default payment method
                $_SESSION['user_id']
            ]);
            
            $invoiceId = $this->db->lastInsertId();
            
            // Add invoice items from project materials
            $materials = $this->db->fetchAll(
                "SELECT tm.*, t.title as task_title 
                 FROM task_materials tm 
                 JOIN tasks t ON tm.task_id = t.id 
                 WHERE t.project_id = ?", 
                [$project['id']]
            );
            foreach ($materials as $material) {
                $itemSql = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price, created_at)
                           VALUES (?, ?, ?, ?, ?, NOW())";
                $this->db->execute($itemSql, [
                    $invoiceId,
                    $material['description'] . ' (' . $material['task_title'] . ')',
                    $material['quantity'],
                    $material['unit_price'],
                    $material['quantity'] * $material['unit_price']
                ]);
            }
            
            // Add invoice items from project labor
            $labor = $this->db->fetchAll(
                "SELECT tl.*, t.title as task_title, u.first_name, u.last_name 
                 FROM task_labor tl 
                 JOIN tasks t ON tl.task_id = t.id 
                 LEFT JOIN users u ON tl.user_id = u.id 
                 WHERE t.project_id = ?", 
                [$project['id']]
            );
            foreach ($labor as $laborItem) {
                $techName = ($laborItem['first_name'] ?? '') . ' ' . ($laborItem['last_name'] ?? '');
                $itemSql = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price, created_at)
                           VALUES (?, ?, ?, ?, ?, NOW())";
                $this->db->execute($itemSql, [
                    $invoiceId,
                    'Εργασία: ' . $laborItem['task_title'] . ' - ' . trim($techName),
                    $laborItem['hours'],
                    $laborItem['hourly_rate'],
                    $laborItem['hours'] * $laborItem['hourly_rate']
                ]);
            }
            
            error_log("Invoice created successfully: $invoiceNumber with total: $totalAmount");
            $_SESSION['success'] .= " - Δημιουργήθηκε τιμολόγιο $invoiceNumber (" . number_format($totalAmount, 2) . "€)";
            
        } catch (Exception $e) {
            // Log error but don't fail the status update
            error_log("Failed to create invoice for project {$project['id']}: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Σφάλμα δημιουργίας τιμολογίου: ' . $e->getMessage();
        }
    }
    
    /**
     * Export all projects to CSV
     */
    public function exportCsv() {
        $projectModel = new Project();
        $projects = $projectModel->getAll();
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="projects_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add CSV headers
        fputcsv($output, [
            'ID',
            'Title',
            'Description',
            'Customer',
            'Technician',
            'Category',
            'Status',
            'Priority',
            'Start Date',
            'Completion Date',
            'Estimated Hours',
            'Material Cost',
            'Labor Cost',
            'Total Cost',
            'Location',
            'Notes',
            'Created At'
        ]);
        
        // Add project data
        foreach ($projects as $project) {
            $customerName = $project['customer_type'] == 'company' 
                ? $project['customer_company_name']
                : $project['customer_first_name'] . ' ' . $project['customer_last_name'];
            
            $technicianName = ($project['tech_first_name'] ?? '') . ' ' . ($project['tech_last_name'] ?? '');
            
            fputcsv($output, [
                $project['id'] ?? '',
                $project['title'] ?? '',
                $project['description'] ?? '',
                $customerName,
                trim($technicianName),
                $project['category'] ?? '',
                $project['status'] ?? '',
                $project['priority'] ?? '',
                $project['start_date'] ?? '',
                $project['completion_date'] ?? '',
                $project['estimated_hours'] ?? '',
                $project['material_cost'] ?? '',
                $project['labor_cost'] ?? '',
                $project['total_cost'] ?? '',
                $project['location'] ?? '',
                $project['notes'] ?? '',
                $project['created_at'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Download demo CSV file with sample project data
     */
    public function downloadDemoCsv() {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="projects_demo.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add CSV headers (without ID - will be auto-generated)
        fputcsv($output, [
            'Title',
            'Description',
            'Customer ID',
            'Technician ID',
            'Category',
            'Status',
            'Priority',
            'Start Date',
            'Completion Date',
            'Estimated Hours',
            'Material Cost',
            'Labor Cost',
            'Location',
            'Notes'
        ]);
        
        // Add sample data
        $samples = [
            [
                'Εγκατάσταση Ηλεκτρικού Πίνακα',
                'Εγκατάσταση νέου ηλεκτρικού πίνακα σε διαμέρισμα',
                '1',
                '1',
                'electrical',
                'in_progress',
                'high',
                date('Y-m-d'),
                date('Y-m-d', strtotime('+7 days')),
                '8',
                '150.00',
                '200.00',
                'Αθήνα, Κολωνάκι',
                'Απαιτείται ειδική άδεια'
            ],
            [
                'Επισκευή Υδραυλικών',
                'Αντικατάσταση σωληνώσεων νερού',
                '1',
                '1',
                'plumbing',
                'pending',
                'medium',
                date('Y-m-d', strtotime('+3 days')),
                date('Y-m-d', strtotime('+5 days')),
                '4',
                '80.00',
                '120.00',
                'Θεσσαλονίκη',
                ''
            ],
            [
                'Συντήρηση Κλιματιστικών',
                'Ετήσιο service κλιματιστικών μονάδων',
                '2',
                '1',
                'maintenance',
                'completed',
                'low',
                date('Y-m-d', strtotime('-10 days')),
                date('Y-m-d', strtotime('-8 days')),
                '3',
                '50.00',
                '90.00',
                'Πειραιάς',
                'Ολοκληρώθηκε επιτυχώς'
            ]
        ];
        
        foreach ($samples as $sample) {
            fputcsv($output, $sample);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Import projects from CSV file
     */
    public function importCsv() {
        try {
            // Check if file was uploaded
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = __('projects.csv_file_required');
                header('Location: ?route=/projects');
                exit;
            }
            
            $file = $_FILES['csv_file']['tmp_name'];
            
            // Open CSV file
            $handle = fopen($file, 'r');
            if ($handle === false) {
                throw new Exception(__('projects.csv_invalid_format'));
            }
            
            // Read header row
            $headers = fgetcsv($handle);
            
            $projectModel = new Project();
            $importCount = 0;
            $errors = [];
            
            // Process each row
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                try {
                    // Map CSV columns to data array
                    $data = [
                        'title' => $this->getCsvValue($row, $headers, 'Title'),
                        'description' => $this->getCsvValue($row, $headers, 'Description'),
                        'customer_id' => $this->getCsvValue($row, $headers, 'Customer ID'),
                        'assigned_technician' => $this->getCsvValue($row, $headers, 'Technician ID'),
                        'category' => $this->getCsvValue($row, $headers, 'Category', 'maintenance'),
                        'status' => $this->getCsvValue($row, $headers, 'Status', 'pending'),
                        'priority' => $this->getCsvValue($row, $headers, 'Priority', 'normal'),
                        'start_date' => $this->getCsvValue($row, $headers, 'Start Date'),
                        'completion_date' => $this->getCsvValue($row, $headers, 'Completion Date'),
                        'estimated_hours' => $this->getCsvValue($row, $headers, 'Estimated Hours', '0'),
                        'material_cost' => $this->getCsvValue($row, $headers, 'Material Cost', '0.00'),
                        'labor_cost' => $this->getCsvValue($row, $headers, 'Labor Cost', '0.00'),
                        'location' => $this->getCsvValue($row, $headers, 'Location'),
                        'notes' => $this->getCsvValue($row, $headers, 'Notes'),
                        'created_by' => $_SESSION['user_id']
                    ];
                    
                    // Basic validation
                    if (empty($data['title']) || empty($data['customer_id'])) {
                        continue;
                    }
                    
                    // Calculate total cost
                    $data['total_cost'] = floatval($data['material_cost']) + floatval($data['labor_cost']);
                    
                    // Create project
                    if ($projectModel->create($data)) {
                        $importCount++;
                    }
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
            
            fclose($handle);
            
            // Set success message
            $_SESSION['success'] = str_replace('{count}', $importCount, __('projects.csv_import_success'));
            
            if (!empty($errors)) {
                $_SESSION['warning'] = implode(', ', array_slice($errors, 0, 5));
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = __('projects.csv_import_error') . ': ' . $e->getMessage();
        }
        
        header('Location: ?route=/projects');
        exit;
    }
    
    /**
     * Helper function to get CSV value by column name
     */
    private function getCsvValue($row, $headers, $columnName, $default = '') {
        $index = array_search($columnName, $headers);
        if ($index === false) {
            return $default;
        }
        return isset($row[$index]) && $row[$index] !== '' ? trim($row[$index]) : $default;
    }
}
