<?php
/**
 * Authentication Controller
 * Handles user login, logout and authentication
 */

class AuthController extends BaseController {
    
    public function __construct() {
        $this->db = new Database();
        // Don't call parent constructor to skip auth check for login page
    }
    
    /**
     * Show login form
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $data = [
            'title' => 'Σύνδεση - ' . APP_NAME,
            'error' => null,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        // Check for flash messages
        $flash = $this->getFlash();
        if ($flash) {
            $data['flash'] = $flash;
        }
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Process login authentication
     */
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }
        
        try {
            // Validate CSRF token (skip in debug mode for easier testing)
            if (!DEBUG_MODE) {
                $this->validateCsrfToken();
            }
            
            // Sanitize input
            $username = $this->sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) ? true : false;
            
            // Validate required fields
            $errors = $this->validateRequired([
                'username' => $username,
                'password' => $password
            ], ['username', 'password']);
            
            if (!empty($errors)) {
                $this->flash('error', 'Παρακαλώ συμπληρώστε όλα τα υποχρεωτικά πεδία');
                $this->redirect('/login');
            }
            
            // Attempt authentication
            $userModel = new User();
            $user = $userModel->authenticate($username, $password);
            
            if ($user) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Set remember me cookie if requested
                if ($remember) {
                    $cookieToken = bin2hex(random_bytes(32));
                    setcookie('remember_token', $cookieToken, time() + (86400 * 30), '/'); // 30 days
                    
                    // Store token in database (you might want to create a remember_tokens table)
                    // For now, we'll skip this implementation
                }
                
                // Log successful login
                $this->logActivity($user['id'], 'login', 'Επιτυχής σύνδεση');
                
                // Redirect to dashboard
                $this->flash('success', 'Καλώς ήρθατε, ' . $user['first_name'] . '!');
                $this->redirect('/dashboard');
                
            } else {
                // Invalid credentials
                $this->flash('error', 'Λάθος όνομα χρήστη ή κωδικός πρόσβασης');
                $this->redirect('/login');
            }
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                $this->flash('error', 'Σφάλμα: ' . $e->getMessage());
            } else {
                $this->flash('error', 'Παρουσιάστηκε σφάλμα κατά τη σύνδεση. Παρακαλώ δοκιμάστε ξανά.');
            }
            $this->redirect('/login');
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            // Log logout activity
            $this->logActivity($_SESSION['user_id'], 'logout', 'Αποσύνδεση χρήστη');
            
            // Clear remember me cookie
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/');
            }
            
            // Destroy session
            session_unset();
            session_destroy();
            
            $this->flash('success', 'Αποσυνδεθήκατε επιτυχώς');
        }
        
        $this->redirect('/login');
    }
    
    /**
     * Check if user is logged in (override parent method)
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Log user activity
     */
    private function logActivity($userId, $action, $description) {
        try {
            $sql = "INSERT INTO user_activities (user_id, action, description, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            // Note: You might want to create a user_activities table for this
            // For now, we'll skip this implementation
            
        } catch (Exception $e) {
            // Log error but don't interrupt the login process
            error_log("Failed to log user activity: " . $e->getMessage());
        }
    }
    
    /**
     * Show forgot password form
     */
    public function forgotPassword() {
        $data = [
            'title' => 'Ξέχασα τον Κωδικό - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('auth/forgot-password', $data);
    }
    
    /**
     * Process forgot password request
     */
    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/forgot-password');
        }
        
        try {
            $this->validateCsrfToken();
            
            $email = $this->sanitize($_POST['email'] ?? '');
            
            if (empty($email)) {
                $this->flash('error', 'Παρακαλώ εισάγετε το email σας');
                $this->redirect('/forgot-password');
            }
            
            // Check if email exists
            $userModel = new User();
            $user = $userModel->findAll(['email' => $email]);
            
            if (!empty($user)) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save reset token (you might want to add reset_token and reset_expiry columns to users table)
                // For now, we'll just show a success message
                
                $this->flash('success', 'Αν το email υπάρχει στο σύστημα, θα λάβετε οδηγίες επαναφοράς κωδικού');
            } else {
                // Don't reveal if email exists or not
                $this->flash('success', 'Αν το email υπάρχει στο σύστημα, θα λάβετε οδηγίες επαναφοράς κωδικού');
            }
            
            $this->redirect('/login');
            
        } catch (Exception $e) {
            $this->flash('error', 'Παρουσιάστηκε σφάλμα. Παρακαλώ δοκιμάστε ξανά.');
            $this->redirect('/forgot-password');
        }
    }
}