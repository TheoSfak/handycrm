<?php
/**
 * Base Controller Class
 * Contains common functionality for all controllers
 */

class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->checkAuth();
    }
    
    /**
     * Check if user is authenticated
     */
    protected function checkAuth() {
        if (!$this->isLoggedIn() && !$this->isLoginPage()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Check if user is logged in
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Check if current page is login page
     */
    protected function isLoginPage() {
        $currentPath = $_SERVER['REQUEST_URI'];
        return strpos($currentPath, '/login') !== false || strpos($currentPath, '/auth') !== false;
    }
    
    /**
     * Get current logged-in user
     */
    protected function getCurrentUser() {
        if ($this->isLoggedIn()) {
            $userModel = new User();
            return $userModel->find($_SESSION['user_id']);
        }
        return null;
    }
    
    /**
     * Check if user has specific role
     */
    protected function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Check if user is admin
     */
    protected function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Check if user is workshop supervisor
     */
    protected function isSupervisor() {
        return $this->hasRole('supervisor');
    }
    
    /**
     * Check if user is technician
     */
    protected function isTechnician() {
        return $this->hasRole('technician');
    }
    
    /**
     * Check if user is assistant
     */
    protected function isAssistant() {
        return $this->hasRole('assistant');
    }
    
    /**
     * Check if user can manage all data (admin only)
     */
    protected function canManageAll() {
        return $this->isAdmin();
    }
    
    /**
     * Check if user can manage projects (admin or supervisor)
     */
    protected function canManageProjects() {
        return $this->isAdmin() || $this->isSupervisor();
    }
    
    /**
     * Check if user can manage materials (admin or supervisor)
     */
    protected function canManageMaterials() {
        return $this->isAdmin() || $this->isSupervisor();
    }
    
    /**
     * Check if user can view own profile only (technician or assistant)
     */
    protected function canViewOwnProfileOnly() {
        return $this->isTechnician() || $this->isAssistant();
    }
    
    /**
     * Check if user can view user data
     * @param int|null $userId If provided, checks if user can view that specific user
     */
    protected function canViewUser($userId = null) {
        // Admin can view all users
        if ($this->isAdmin()) {
            return true;
        }
        
        // Supervisor can view their own profile
        if ($this->isSupervisor()) {
            return $userId === null || $userId == $_SESSION['user_id'];
        }
        
        // Technician/Assistant can only view their own profile
        if ($userId === null) {
            return false; // Cannot view user list
        }
        
        return $userId == $_SESSION['user_id'];
    }
    
    /**
     * Require admin access or redirect
     */
    protected function requireAdmin() {
        if (!$this->isAdmin()) {
            $this->redirect('/dashboard?error=unauthorized');
        }
    }
    
    /**
     * Require admin or supervisor access or redirect
     */
    protected function requireSupervisorOrAdmin() {
        if (!$this->isAdmin() && !$this->isSupervisor()) {
            $this->redirect('/dashboard?error=unauthorized');
        }
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        // Make sure session data is written before redirect
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            session_start(); // Restart to keep session available
        }
        
        if (strpos($url, 'http') !== 0) {
            // For PHP built-in server, use query parameter routing
            // Replace ? with & for additional query parameters
            $url = str_replace('?', '&', $url);
            $url = '?route=' . $url;
        }
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Load view with data
     */
    protected function view($viewName, $data = []) {
        // Set UTF-8 encoding
        header('Content-Type: text/html; charset=UTF-8');
        
        // Extract data to variables
        extract($data);
        
        // Include header
        include APP_ROOT . '/views/includes/header.php';
        
        // Include the specific view
        $viewFile = APP_ROOT . '/views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View not found: {$viewName}");
        }
        
        // Include footer
        include APP_ROOT . '/views/includes/footer.php';
    }
    
    /**
     * Load partial view (without header/footer)
     */
    protected function partial($viewName, $data = []) {
        extract($data);
        
        $viewFile = APP_ROOT . '/views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View not found: {$viewName}");
        }
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrfToken() {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_GET[CSRF_TOKEN_NAME] ?? '';
        
        if (empty($token) || !isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new Exception("Invalid CSRF token");
        }
    }
    
    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                $sanitized[$key] = $this->sanitize($value);
            }
            return $sanitized;
        }
        
        return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($data, $required) {
        $errors = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Το πεδίο είναι υποχρεωτικό";
            }
        }
        
        return $errors;
    }
    
    /**
     * Set flash message
     */
    protected function flash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get and clear flash message
     */
    protected function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
    
    /**
     * Handle file upload
     */
    protected function uploadFile($file, $allowedTypes = null, $maxSize = null) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("No file uploaded or upload error");
        }
        
        $allowedTypes = $allowedTypes ?: explode(',', ALLOWED_FILE_TYPES);
        $maxSize = $maxSize ?: MAX_FILE_SIZE;
        
        // Check file size
        if ($file['size'] > $maxSize) {
            throw new Exception("File too large. Maximum size: " . ($maxSize / 1024 / 1024) . "MB");
        }
        
        // Check file type
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedTypes)) {
            throw new Exception("File type not allowed. Allowed types: " . implode(', ', $allowedTypes));
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $fileExt;
        $uploadPath = UPLOAD_PATH . $filename;
        
        // Create upload directory if it doesn't exist
        if (!is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'filename' => $filename,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'path' => $uploadPath,
                'url' => APP_URL . '/uploads/' . $filename
            ];
        } else {
            throw new Exception("Failed to upload file");
        }
    }
}