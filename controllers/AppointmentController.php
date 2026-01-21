<?php
/**
 * Appointment Controller
 * Handles appointment management operations
 */

require_once __DIR__ . '/../classes/AuthMiddleware.php';

class AppointmentController extends BaseController {
    
    /**
     * Show appointments list
     */
    public function index() {
        // Check permission for viewing appointments
        if (!$this->isAdmin() && !$this->isSupervisor() && !can('appointments.view')) {
            $this->redirect('/dashboard?error=unauthorized');
        }
        
        $user = $this->getCurrentUser();
        
        // Get filters from request
        $filters = [
            'status' => $_GET['status'] ?? '',
            'technician' => $_GET['technician'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        // Get current page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        // Get appointments
        $appointmentModel = new Appointment();
        $result = $appointmentModel->getPaginated($page, ITEMS_PER_PAGE, $filters);
        
        // Get filter options
        $database = new Database();
        $db = $database->connect();
        
        // Get all technicians
        $stmt = $db->query("
            SELECT u.id, u.first_name, u.last_name 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE r.name IN ('admin', 'technician') AND u.is_active = 1 
            ORDER BY u.first_name
        ");
        $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get appointment statuses
        $statuses = [
            'scheduled' => 'Προγραμματισμένο',
            'confirmed' => 'Επιβεβαιωμένο',
            'in_progress' => 'Σε εξέλιξη',
            'completed' => 'Ολοκληρωμένο',
            'cancelled' => 'Ακυρωμένο',
            'no_show' => 'Δεν εμφανίστηκε'
        ];
        
        $data = [
            'title' => __('appointments.title') . ' - ' . APP_NAME,
            'user' => $user,
            'appointments' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => $filters,
            'technicians' => $technicians,
            'statuses' => $statuses
        ];
        
        parent::view('appointments/index', $data);
    }
    
    /**
     * Show appointment details
     */
    public function details() {
        $user = $this->getCurrentUser();
        
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό ραντεβού';
            $this->redirect('/appointments');
        }
        
        $appointmentModel = new Appointment();
        $appointment = $appointmentModel->getWithDetails($id);
        
        if (!$appointment) {
            $_SESSION['error'] = 'Το ραντεβού δεν βρέθηκε';
            $this->redirect('/appointments');
        }
        
        $data = [
            'title' => __('appointments.title') . ': ' . $appointment['title'] . ' - ' . APP_NAME,
            'user' => $user,
            'appointment' => $appointment
        ];
        
        parent::view('appointments/view', $data);
    }
    
    /**
     * Show create appointment form
     */
    public function create() {
        $user = $this->getCurrentUser();
        
        // Get pre-selected customer ID from query string (if coming from customer page)
        $preselectedCustomerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
        
        // Get customers for dropdown
        $customerModel = new Customer();
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->query("SELECT * FROM customers WHERE is_active = 1 ORDER BY first_name, last_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get technicians
        $stmt = $db->query("
            SELECT u.id, u.first_name, u.last_name 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE r.name IN ('admin', 'technician') AND u.is_active = 1 
            ORDER BY u.first_name
        ");
        $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get projects (optional) - if customer is preselected, filter by that customer
        if ($preselectedCustomerId) {
            $stmt = $db->prepare("SELECT id, title, customer_id FROM projects WHERE customer_id = ? AND status IN ('new', 'in_progress') ORDER BY title");
            $stmt->execute([$preselectedCustomerId]);
        } else {
            $stmt = $db->query("SELECT id, title, customer_id FROM projects WHERE status IN ('new', 'in_progress') ORDER BY title");
        }
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => __('appointments.new_appointment') . ' - ' . APP_NAME,
            'user' => $user,
            'customers' => $customers,
            'technicians' => $technicians,
            'projects' => $projects,
            'preselected_customer_id' => $preselectedCustomerId,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        parent::view('appointments/create', $data);
    }
    
    /**
     * Store new appointment
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/appointments/create');
        }
        
        $user = $this->getCurrentUser();
        
        // SECURITY FIX: CSRF protection must ALWAYS be enforced
        try {
            $this->validateCsrfToken();
        } catch (Exception $e) {
            error_log("AppointmentController::create - CSRF validation failed: " . $e->getMessage());
            $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
            $this->redirect('/appointments/create');
        }
        
        // Validate input
        $errors = [];
        
        if (empty($_POST['title'])) {
            $errors[] = 'Το πεδίο τίτλος είναι υποχρεωτικό';
        }
        
        if (empty($_POST['customer_id'])) {
            $errors[] = 'Το πεδίο πελάτης είναι υποχρεωτικό';
        }
        
        if (empty($_POST['technician_id'])) {
            $errors[] = 'Το πεδίο τεχνικός είναι υποχρεωτικό';
        }
        
        if (empty($_POST['appointment_date'])) {
            $errors[] = 'Το πεδίο ημερομηνία και ώρα είναι υποχρεωτικό';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/appointments/create');
        }
        
        // Create appointment
        $appointmentData = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description'] ?? ''),
            'customer_id' => (int)$_POST['customer_id'],
            'technician_id' => (int)$_POST['technician_id'],
            'project_id' => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'appointment_date' => $_POST['appointment_date'],
            'duration_minutes' => (int)($_POST['duration_minutes'] ?? 60),
            'status' => $_POST['status'] ?? 'scheduled',
            'address' => trim($_POST['address'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'created_by' => $user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $appointmentModel = new Appointment();
        $appointmentId = $appointmentModel->create($appointmentData);
        
        if ($appointmentId) {
            $_SESSION['success'] = 'Το ραντεβού δημιουργήθηκε με επιτυχία';
            $this->redirect('/appointments/details?id=' . $appointmentId);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία του ραντεβού';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/appointments/create');
        }
    }
    
    /**
     * Show edit appointment form
     */
    public function edit() {
        $user = $this->getCurrentUser();
        
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό ραντεβού';
            $this->redirect('/appointments');
        }
        
        $appointmentModel = new Appointment();
        $appointment = $appointmentModel->find($id);
        
        if (!$appointment) {
            $_SESSION['error'] = 'Το ραντεβού δεν βρέθηκε';
            $this->redirect('/appointments');
        }
        
        // Get customers
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->query("SELECT * FROM customers WHERE is_active = 1 ORDER BY first_name, last_name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get technicians
        $stmt = $db->query("
            SELECT u.id, u.first_name, u.last_name 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE r.name IN ('admin', 'technician') AND u.is_active = 1 
            ORDER BY u.first_name
        ");
        $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get projects
        $stmt = $db->query("SELECT id, title, customer_id FROM projects WHERE status IN ('pending', 'in_progress') ORDER BY title");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => __('appointments.edit') . ' ' . __('appointments.title') . ' - ' . APP_NAME,
            'user' => $user,
            'appointment' => $appointment,
            'customers' => $customers,
            'technicians' => $technicians,
            'projects' => $projects,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        parent::view('appointments/edit', $data);
    }
    
    /**
     * Update appointment
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/appointments');
        }
        
        $user = $this->getCurrentUser();
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό ραντεβού';
            $this->redirect('/appointments');
        }
        
        // SECURITY FIX: CSRF protection must ALWAYS be enforced
        try {
            $this->validateCsrfToken();
        } catch (Exception $e) {
            error_log("AppointmentController::update - CSRF validation failed: " . $e->getMessage());
            $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
            $this->redirect('/appointments/edit?id=' . $id);
        }
        
        // Update appointment
        $appointmentData = [
            'title' => trim($_POST['title']),
            'description' => trim($_POST['description'] ?? ''),
            'technician_id' => (int)$_POST['technician_id'],
            'appointment_date' => $_POST['appointment_date'],
            'duration_minutes' => (int)($_POST['duration_minutes'] ?? 60),
            'status' => $_POST['status'],
            'address' => trim($_POST['address'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $appointmentModel = new Appointment();
        $success = $appointmentModel->update($id, $appointmentData);
        
        if ($success) {
            $_SESSION['success'] = 'Το ραντεβού ενημερώθηκε με επιτυχία';
            $this->redirect('/appointments/details?id=' . $id);
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του ραντεβού';
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/appointments/edit?id=' . $id);
        }
    }
    
    /**
     * Delete appointment
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/appointments');
        }
        
        // SECURITY FIX: CSRF protection must ALWAYS be enforced
        try {
            $this->validateCsrfToken();
        } catch (Exception $e) {
            error_log("AppointmentController::delete - CSRF validation failed: " . $e->getMessage());
            $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
            $this->redirect('/appointments');
        }
        
        $user = $this->getCurrentUser();
        
        $id = $_POST['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό ραντεβού';
            $this->redirect('/appointments');
        }
        
        $appointmentModel = new Appointment();
        $success = $appointmentModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Το ραντεβού διαγράφηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή του ραντεβού';
        }
        
        $this->redirect('/appointments');
    }
    
    /**
     * Show calendar view
     */
    public function calendar() {
        $user = $this->getCurrentUser();
        
        $data = [
            'title' => __('appointments.calendar') . ' - ' . APP_NAME,
            'user' => $user
        ];
        
        parent::view('appointments/calendar', $data);
    }
    
    /**
     * API endpoint to get appointments for calendar
     */
    public function apiList() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        // Get date range from request
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        // Get appointments for date range
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->prepare("
            SELECT 
                a.id,
                a.title,
                a.description,
                a.appointment_date,
                a.duration_minutes,
                a.status,
                CASE 
                    WHEN c.customer_type = 'company' AND c.company_name IS NOT NULL THEN c.company_name
                    ELSE CONCAT(c.first_name, ' ', c.last_name)
                END as customer_name,
                CONCAT(u.first_name, ' ', u.last_name) as technician_name
            FROM appointments a
            LEFT JOIN customers c ON a.customer_id = c.id
            LEFT JOIN users u ON a.technician_id = u.id
            WHERE DATE(a.appointment_date) BETWEEN ? AND ?
            ORDER BY a.appointment_date ASC
        ");
        
        $stmt->execute([$startDate, $endDate]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return JSON
        header('Content-Type: application/json');
        echo json_encode($appointments);
        exit;
    }
}
