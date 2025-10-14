<?php
/**
 * TechniciansController
 * 
 * Handles technicians and assistants management
 * 
 * @package HandyCRM
 * @version 1.1.0
 */

require_once 'classes/BaseController.php';
require_once 'models/Technician.php';

class TechniciansController extends BaseController {
    private $technicianModel;
    
    public function __construct() {
        parent::__construct();
        $this->technicianModel = new Technician();
    }
    
    /**
     * List all technicians
     * GET /technicians
     */
    public function index() {
        $this->checkAuth();
        
        // Get filter from query params
        $filter = $_GET['filter'] ?? 'active'; // active, all, technician, assistant
        
        switch ($filter) {
            case 'all':
                $technicians = $this->technicianModel->getAll(true);
                break;
            case 'technician':
                $technicians = $this->technicianModel->getByRole('technician');
                break;
            case 'assistant':
                $technicians = $this->technicianModel->getByRole('assistant');
                break;
            default:
                $technicians = $this->technicianModel->getActive();
        }
        
        $this->view('technicians/index', [
            'title' => 'Τεχνικοί & Βοηθοί',
            'technicians' => $technicians,
            'current_filter' => $filter
        ]);
    }
    
    /**
     * Show add technician form
     * GET /technicians/add
     */
    public function add() {
        $this->checkAuth();
        $this->checkPermission('technicians.manage');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->store();
        }
        
        $this->view('technicians/add', [
            'title' => 'Προσθήκη Τεχνικού'
        ]);
    }
    
    /**
     * Store new technician
     * POST /technicians/add
     */
    private function store() {
        $this->checkCSRF();
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'role' => $_POST['role'] ?? 'technician',
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Validate
        $errors = $this->technicianModel->validate($data);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->view('technicians/add', [
                'title' => 'Προσθήκη Τεχνικού',
                'data' => $data
            ]);
            return;
        }
        
        // Create technician
        $technicianId = $this->technicianModel->create($data);
        
        if ($technicianId) {
            $_SESSION['success'] = 'Ο τεχνικός δημιουργήθηκε επιτυχώς!';
            $this->redirect('/technicians/view/' . $technicianId);
        } else {
            $_SESSION['error'] = 'Αποτυχία δημιουργίας τεχνικού';
            $this->view('technicians/add', [
                'title' => 'Προσθήκη Τεχνικού',
                'data' => $data
            ]);
        }
    }
    
    /**
     * Show edit technician form
     * GET /technicians/edit/{id}
     */
    public function edit($id) {
        $this->checkAuth();
        $this->checkPermission('technicians.manage');
        
        $technician = $this->technicianModel->getById($id);
        
        if (!$technician) {
            $_SESSION['error'] = 'Ο τεχνικός δεν βρέθηκε';
            $this->redirect('/technicians');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }
        
        $this->view('technicians/edit', [
            'title' => 'Επεξεργασία Τεχνικού',
            'technician' => $technician
        ]);
    }
    
    /**
     * Update technician
     * POST /technicians/edit/{id}
     */
    private function update($id) {
        $this->checkCSRF();
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'role' => $_POST['role'] ?? 'technician',
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'notes' => trim($_POST['notes'] ?? '')
        ];
        
        // Validate
        $errors = $this->technicianModel->validate($data, true);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $technician = array_merge(['id' => $id], $data);
            $this->view('technicians/edit', [
                'title' => 'Επεξεργασία Τεχνικού',
                'technician' => $technician
            ]);
            return;
        }
        
        // Update technician
        if ($this->technicianModel->update($id, $data)) {
            $_SESSION['success'] = 'Ο τεχνικός ενημερώθηκε επιτυχώς!';
            $this->redirect('/technicians/view/' . $id);
        } else {
            $_SESSION['error'] = 'Αποτυχία ενημέρωσης τεχνικού';
            $technician = array_merge(['id' => $id], $data);
            $this->view('technicians/edit', [
                'title' => 'Επεξεργασία Τεχνικού',
                'technician' => $technician
            ]);
        }
    }
    
    /**
     * View technician details with work history
     * GET /technicians/view/{id}
     */
    public function view($id) {
        $this->checkAuth();
        
        $technician = $this->technicianModel->getById($id);
        
        if (!$technician) {
            $_SESSION['error'] = 'Ο τεχνικός δεν βρέθηκε';
            $this->redirect('/technicians');
            return;
        }
        
        // Get date range from query params (default: last 3 months)
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-3 months'));
        
        // Get statistics
        $stats = $this->technicianModel->getStatistics($id, $dateFrom, $dateTo);
        
        // Get work history
        $workHistory = $this->technicianModel->getWorkHistory($id, 50);
        
        $this->view('technicians/view', [
            'title' => 'Προβολή Τεχνικού: ' . $technician['name'],
            'technician' => $technician,
            'statistics' => $stats,
            'work_history' => $workHistory,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
    }
    
    /**
     * Delete or deactivate technician
     * POST /technicians/delete
     */
    public function delete() {
        $this->checkAuth();
        $this->checkPermission('technicians.manage');
        $this->checkCSRF();
        
        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? 'deactivate'; // deactivate or delete
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο ID τεχνικού';
            $this->redirect('/technicians');
            return;
        }
        
        if ($action === 'delete') {
            // Check if has work records
            if ($this->technicianModel->hasWorkRecords($id)) {
                $_SESSION['error'] = 'Δεν μπορείτε να διαγράψετε τεχνικό που έχει καταγραφές εργασίας. Απενεργοποιήστε τον αντ\' αυτού.';
                $this->redirect('/technicians/view/' . $id);
                return;
            }
            
            // Permanent delete
            if ($this->technicianModel->delete($id)) {
                $_SESSION['success'] = 'Ο τεχνικός διαγράφηκε οριστικά';
            } else {
                $_SESSION['error'] = 'Αποτυχία διαγραφής τεχνικού';
            }
        } else {
            // Deactivate
            if ($this->technicianModel->deactivate($id)) {
                $_SESSION['success'] = 'Ο τεχνικός απενεργοποιήθηκε επιτυχώς';
            } else {
                $_SESSION['error'] = 'Αποτυχία απενεργοποίησης τεχνικού';
            }
        }
        
        $this->redirect('/technicians');
    }
    
    /**
     * Activate technician
     * POST /technicians/activate
     */
    public function activate() {
        $this->checkAuth();
        $this->checkPermission('technicians.manage');
        $this->checkCSRF();
        
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο ID τεχνικού';
            $this->redirect('/technicians');
            return;
        }
        
        if ($this->technicianModel->activate($id)) {
            $_SESSION['success'] = 'Ο τεχνικός ενεργοποιήθηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Αποτυχία ενεργοποίησης τεχνικού';
        }
        
        $this->redirect('/technicians/view/' . $id);
    }
    
    /**
     * API endpoint - Get technician data (for AJAX)
     * GET /api/technicians/{id}
     */
    public function apiGet($id) {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $technician = $this->technicianModel->getById($id);
        
        if (!$technician) {
            http_response_code(404);
            echo json_encode(['error' => 'Technician not found']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $technician['id'],
                'name' => $technician['name'],
                'role' => $technician['role'],
                'hourly_rate' => $technician['hourly_rate'],
                'phone' => $technician['phone'],
                'email' => $technician['email']
            ]
        ]);
    }
    
    /**
     * API endpoint - Get all active technicians (for dropdowns)
     * GET /api/technicians
     */
    public function apiList() {
        $this->checkAuth();
        header('Content-Type: application/json');
        
        $technicians = $this->technicianModel->getActive();
        
        $data = array_map(function($tech) {
            return [
                'id' => $tech['id'],
                'name' => $tech['name'],
                'role' => $tech['role'],
                'hourly_rate' => $tech['hourly_rate']
            ];
        }, $technicians);
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }
}
