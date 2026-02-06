<?php
/**
 * User Controller
 * Handles user management (Admin only)
 */

class UserController extends BaseController {
    
    /**
     * Show users list
     */
    public function index() {
        // Only admin can view users list
        $this->requireAdmin();
        
        $user = $this->getCurrentUser();
        
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->query("SELECT * FROM users ORDER BY first_name");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => __('users.title') . ' - ' . APP_NAME,
            'user' => $user,
            'users' => $users
        ];
        
        $this->view('users/index', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        // Only admin can create users
        $this->requireAdmin();
        
        $user = $this->getCurrentUser();
        
        $data = [
            'title' => __('users.new_user') . ' - ' . APP_NAME,
            'user' => $user
        ];
        
        $this->view('users/create', $data);
    }
    
    /**
     * Store new user
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
        }
        
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/users/create');
            }
        }
        
        $errors = [];
        if (empty($_POST['username'])) $errors[] = 'Το username είναι υποχρεωτικό';
        if (empty($_POST['email'])) $errors[] = 'Το email είναι υποχρεωτικό';
        if (empty($_POST['password'])) $errors[] = 'Ο κωδικός είναι υποχρεωτικός';
        if (empty($_POST['first_name'])) $errors[] = 'Το όνομα είναι υποχρεωτικό';
        if (empty($_POST['last_name'])) $errors[] = 'Το επώνυμο είναι υποχρεωτικό';
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/users/create');
        }
        
        $database = new Database();
        $db = $database->connect();
        
        // Check if username or email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$_POST['username'], $_POST['email']]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Το username ή το email υπάρχει ήδη';
            $this->redirect('/users/create');
        }
        
        $userData = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => $_POST['role'] ?? 'technician',
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'is_active' => 1
        ];
        
        $userModel = new User();
        $id = $userModel->create($userData);
        
        if ($id) {
            $_SESSION['success'] = 'Ο χρήστης δημιουργήθηκε με επιτυχία';
            $this->redirect('/users');
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία του χρήστη';
            $this->redirect('/users/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit() {
        $user = $this->getCurrentUser();
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό χρήστη';
            $this->redirect('/users');
        }
        
        $userModel = new User();
        $editUser = $userModel->find($id);
        
        if (!$editUser) {
            $_SESSION['error'] = 'Ο χρήστης δεν βρέθηκε';
            $this->redirect('/users');
        }
        
        $data = [
            'title' => __('users.edit') . ' ' . __('users.title') . ' - ' . APP_NAME,
            'user' => $user,
            'editUser' => $editUser
        ];
        
        $this->view('users/edit', $data);
    }
    
    /**
     * Update user
     */
    public function update() {
        // Only admin can update users
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
        }
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό χρήστη';
            $this->redirect('/users');
        }
        
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/users/edit?id=' . $id);
            }
        }
        
        // Validate role
        $allowedRoles = ['admin', 'supervisor', 'technician', 'assistant'];
        $role = $_POST['role'] ?? 'technician';
        if (!in_array($role, $allowedRoles)) {
            $_SESSION['error'] = 'Μη έγκυρος ρόλος χρήστη';
            $this->redirect('/users/edit?id=' . $id);
            return;
        }
        
        $userData = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => $role,
            'hourly_rate' => floatval($_POST['hourly_rate'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Update password only if provided
        if (!empty($_POST['password'])) {
            $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        try {
            $userModel = new User();
            $success = $userModel->update($id, $userData);
            
            if ($success) {
                $_SESSION['success'] = 'Ο χρήστης ενημερώθηκε με επιτυχία';
            } else {
                $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του χρήστη';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Σφάλμα: ' . $e->getMessage();
        }
        
        $this->redirect('/users');
    }
    
    /**
     * Delete user
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
        }
        
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/users');
            }
        }
        
        $id = $_POST['id'] ?? 0;
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό χρήστη';
            $this->redirect('/users');
        }
        
        // Cannot delete yourself
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Δεν μπορείτε να διαγράψετε τον εαυτό σας';
            $this->redirect('/users');
        }
        
        $userModel = new User();
        $success = $userModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Ο χρήστης διαγράφηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή του χρήστη';
        }
        
        $this->redirect('/users');
    }
    
    /**
     * Show user profile with payment history
     */
    public function show($id) {
        // Check if user can view this profile
        // Admin can view all, others can only view their own
        if (!$this->canViewUser($id)) {
            $_SESSION['error'] = 'Δεν έχετε δικαίωμα πρόσβασης σε αυτή την καρτέλα';
            $this->redirect('/dashboard');
            return;
        }
        
        $user = $this->getCurrentUser();
        
        $database = new Database();
        $db = $database->connect();
        
        // Get user details
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $viewUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$viewUser) {
            $_SESSION['error'] = 'Ο χρήστης δεν βρέθηκε';
            $this->redirect('/users');
            return;
        }
        
        // Get payment history from task_labor
        $paymentHistory = $this->getPaymentHistory($id, $db);
        
        // Calculate totals
        $totalEarned = 0;
        $totalPaid = 0;
        $totalUnpaid = 0;
        
        foreach ($paymentHistory as $entry) {
            $totalEarned += $entry['subtotal'];
            if (!empty($entry['paid_at'])) {
                $totalPaid += $entry['subtotal'];
            } else {
                $totalUnpaid += $entry['subtotal'];
            }
        }
        
        $data = [
            'title' => $viewUser['first_name'] . ' ' . $viewUser['last_name'] . ' - ' . APP_NAME,
            'user' => $user,
            'viewUser' => $viewUser,
            'paymentHistory' => $paymentHistory,
            'totalEarned' => $totalEarned,
            'totalPaid' => $totalPaid,
            'totalUnpaid' => $totalUnpaid
        ];
        
        $this->view('users/show', $data);
    }
    
    /**
     * Get payment history for a user
     */
    private function getPaymentHistory($userId, $db) {
        $sql = "SELECT 
                    tl.*,
                    pt.description as task_description,
                    pt.task_date,
                    pt.date_from,
                    pt.date_to,
                    pt.task_type,
                    p.title as project_title,
                    c.first_name as customer_first_name,
                    c.last_name as customer_last_name,
                    c.company_name as customer_company_name,
                    c.customer_type,
                    CONCAT(paid_user.first_name, ' ', paid_user.last_name) as paid_by_name
                FROM task_labor tl
                INNER JOIN project_tasks pt ON tl.task_id = pt.id
                INNER JOIN projects p ON pt.project_id = p.id
                INNER JOIN customers c ON p.customer_id = c.id
                LEFT JOIN users paid_user ON tl.paid_by = paid_user.id
                WHERE tl.user_id = ?
                ORDER BY COALESCE(pt.task_date, pt.date_from) DESC
                LIMIT 100";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
