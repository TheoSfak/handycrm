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
        $user = $this->getCurrentUser();
        
        $database = new Database();
        $db = $database->connect();
        
        $stmt = $db->query("SELECT * FROM users ORDER BY first_name");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'title' => 'Χρήστες - ' . APP_NAME,
            'user' => $user,
            'users' => $users
        ];
        
        $this->view('users/index', $data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $user = $this->getCurrentUser();
        
        $data = [
            'title' => 'Νέος Χρήστης - ' . APP_NAME,
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
            'title' => 'Επεξεργασία Χρήστη - ' . APP_NAME,
            'user' => $user,
            'editUser' => $editUser
        ];
        
        $this->view('users/edit', $data);
    }
    
    /**
     * Update user
     */
    public function update() {
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
        
        $userData = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => $_POST['role'] ?? 'technician',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // Update password only if provided
        if (!empty($_POST['password'])) {
            $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        $userModel = new User();
        $success = $userModel->update($id, $userData);
        
        if ($success) {
            $_SESSION['success'] = 'Ο χρήστης ενημερώθηκε με επιτυχία';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του χρήστη';
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
}
