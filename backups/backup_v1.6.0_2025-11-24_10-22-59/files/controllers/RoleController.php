<?php
/**
 * RoleController
 * Manages user roles and permissions
 * 
 * @package HandyCRM
 * @version 1.4.0
 */

require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../classes/Role.php';
require_once __DIR__ . '/../classes/Permission.php';
require_once __DIR__ . '/../classes/AuthMiddleware.php';

class RoleController extends BaseController {
    private $roleModel;
    private $permissionModel;
    private $auth;
    
    public function __construct() {
        parent::__construct();
        
        // Only admins can manage roles
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Μόνο οι διαχειριστές έχουν πρόσβαση σε αυτή τη λειτουργία';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
        $this->auth = AuthMiddleware::getInstance();
    }
    
    /**
     * Display all roles
     */
    public function index() {
        $roles = $this->roleModel->getAll();
        
        $data = [
            'title' => 'Διαχείριση Ρόλων',
            'roles' => $roles
        ];
        
        $this->view('roles/index', $data);
    }
    
    /**
     * Show create role form
     */
    public function create() {
        $permissions = $this->permissionModel->getAll();
        
        $data = [
            'title' => 'Νέος Ρόλος',
            'permissions' => $permissions
        ];
        
        $this->view('roles/create', $data);
    }
    
    /**
     * Store new role
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Validate input
        $name = trim($_POST['name'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($name) || empty($display_name)) {
            $_SESSION['error'] = 'Το όνομα και η ετικέτα είναι υποχρεωτικά';
            header('Location: ' . BASE_URL . '/roles/create');
            exit;
        }
        
        // Create role
        $roleId = $this->roleModel->create(
            $name,
            $display_name,
            $description,
            false // is_system = false for custom roles
        );
        
        if ($roleId) {
            // Assign permissions
            if (!empty($permissions)) {
                foreach ($permissions as $permissionId) {
                    $this->roleModel->assignPermission($roleId, $permissionId);
                }
            }
            
            $_SESSION['success'] = 'Ο ρόλος δημιουργήθηκε επιτυχώς';
            header('Location: ' . BASE_URL . '/roles');
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη δημιουργία του ρόλου';
            header('Location: ' . BASE_URL . '/roles/create');
        }
        exit;
    }
    
    /**
     * Show edit role form
     */
    public function edit($id) {
        $role = $this->roleModel->getById($id);
        
        if (!$role) {
            $_SESSION['error'] = 'Ο ρόλος δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Prevent editing system roles
        if (in_array($role['name'], ['admin', 'supervisor', 'technician', 'assistant'])) {
            $_SESSION['error'] = 'Οι προεπιλεγμένοι ρόλοι δεν μπορούν να τροποποιηθούν';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $permissions = $this->permissionModel->getAll();
        $rolePermissions = $this->roleModel->getRolePermissions($id);
        
        $data = [
            'title' => 'Επεξεργασία Ρόλου',
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ];
        
        $this->view('roles/edit', $data);
    }
    
    /**
     * Update role
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $role = $this->roleModel->getById($id);
        
        if (!$role) {
            $_SESSION['error'] = 'Ο ρόλος δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Prevent editing system roles
        if (in_array($role['name'], ['admin', 'supervisor', 'technician', 'assistant'])) {
            $_SESSION['error'] = 'Οι προεπιλεγμένοι ρόλοι δεν μπορούν να τροποποιηθούν';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Validate input
        $display_name = trim($_POST['display_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($display_name)) {
            $_SESSION['error'] = 'Η ετικέτα είναι υποχρεωτική';
            header('Location: ' . BASE_URL . '/roles/edit/' . $id);
            exit;
        }
        
        // Update role
        $success = $this->roleModel->update($id, [
            'display_name' => $display_name,
            'description' => $description
        ]);
        
        if ($success) {
            // Update permissions
            $this->roleModel->clearPermissions($id);
            if (!empty($permissions)) {
                foreach ($permissions as $permissionId) {
                    $this->roleModel->assignPermission($id, $permissionId);
                }
            }
            
            $_SESSION['success'] = 'Ο ρόλος ενημερώθηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση του ρόλου';
        }
        
        header('Location: ' . BASE_URL . '/roles');
        exit;
    }
    
    /**
     * Delete role
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $role = $this->roleModel->getById($id);
        
        if (!$role) {
            $_SESSION['error'] = 'Ο ρόλος δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Prevent deleting system roles
        if (in_array($role['name'], ['admin', 'supervisor', 'technician', 'assistant'])) {
            $_SESSION['error'] = 'Οι προεπιλεγμένοι ρόλοι δεν μπορούν να διαγραφούν';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Check if role is in use
        $usersCount = $this->roleModel->getUsersCount($id);
        if ($usersCount > 0) {
            $_SESSION['error'] = 'Ο ρόλος δεν μπορεί να διαγραφεί γιατί χρησιμοποιείται από ' . $usersCount . ' χρήστες';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Delete role
        if ($this->roleModel->delete($id)) {
            $_SESSION['success'] = 'Ο ρόλος διαγράφηκε επιτυχώς';
        } else {
            $_SESSION['error'] = 'Σφάλμα κατά τη διαγραφή του ρόλου';
        }
        
        header('Location: ' . BASE_URL . '/roles');
        exit;
    }
    
    /**
     * Manage role permissions
     */
    public function permissions($id) {
        $role = $this->roleModel->getById($id);
        
        if (!$role) {
            $_SESSION['error'] = 'Ο ρόλος δεν βρέθηκε';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $permissions = $_POST['permissions'] ?? [];
            
            // Update permissions
            $this->roleModel->clearPermissions($id);
            if (!empty($permissions)) {
                foreach ($permissions as $permissionId) {
                    $this->roleModel->assignPermission($id, $permissionId);
                }
            }
            
            $_SESSION['success'] = 'Τα δικαιώματα ενημερώθηκαν επιτυχώς';
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $permissions = $this->permissionModel->getAll();
        $rolePermissions = $this->roleModel->getRolePermissions($id);
        
        $data = [
            'title' => 'Δικαιώματα Ρόλου: ' . $role['display_name'],
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ];
        
        $this->view('roles/permissions', $data);
    }
}
