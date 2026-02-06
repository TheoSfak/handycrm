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
                $_SESSION['role_id'] = $user['role_id']; // Add role_id to session
                $_SESSION['language'] = $user['language'] ?? 'el'; // Load user's language preference
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
                
                // Redirect based on role and permissions
                $this->flash('success', 'Καλώς ήρθατε, ' . $user['first_name'] . '!');
                
                // Load AuthMiddleware for permission check
                require_once __DIR__ . '/../classes/AuthMiddleware.php';
                
                // Determine where to redirect based on permissions
                if ($user['role'] === 'admin' || $user['role'] === 'supervisor') {
                    // Admin and Supervisor go to dashboard
                    $this->redirect('/dashboard');
                } elseif (can('dashboard.view')) {
                    // User has dashboard permission
                    $this->redirect('/dashboard');
                } elseif (can('payments.view')) {
                    // Redirect to first available menu item
                    $this->redirect('/payments');
                } elseif (can('users.view')) {
                    $this->redirect('/users');
                } elseif (can('projects.view')) {
                    $this->redirect('/projects');
                } elseif (can('transformer_maintenance.view')) {
                    $this->redirect('/maintenances');
                } else {
                    // Fallback to profile page
                    $this->redirect('/users/show/' . $user['id']);
                }
                
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
            $email = $this->sanitize($_POST['email'] ?? '');
            
            // Debug
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                error_log("DEBUG: Forgot password request for email: " . $email);
            }
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('error', 'Παρακαλώ εισάγετε έγκυρο email');
                $this->redirect('/forgot-password');
            }
            
            // Check if email exists
            $database = new Database();
            $db = $database->connect();
            
            // Trim and lowercase for comparison
            $emailSearch = trim(strtolower($email));
            
            $stmt = $db->prepare("SELECT id, first_name, email, is_active FROM users WHERE LOWER(TRIM(email)) = ?");
            $stmt->execute([$emailSearch]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                if ($user) {
                    error_log("DEBUG: User found - ID: " . $user['id'] . ", Name: " . $user['first_name'] . ", Active: " . $user['is_active']);
                    if ($user['is_active'] != 1) {
                        error_log("DEBUG: WARNING - User is not active!");
                    }
                } else {
                    error_log("DEBUG: No user found with email: " . $emailSearch);
                }
            }
            
            // Check if user exists AND is active
            if ($user && $user['is_active'] == 1) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Debug
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    error_log("DEBUG: Generated token, updating database for user ID: " . $user['id']);
                }
                
                // Save reset token to database
                $updateStmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $updateStmt->execute([$resetToken, $resetExpiry, $user['id']]);
                
                // Debug
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    error_log("DEBUG: Token saved to database, proceeding to send email");
                }
                
                // Send email with reset link using EmailService
                try {
                    require_once __DIR__ . '/../classes/EmailService.php';
                    
                    // Debug: Log attempt
                    if (defined('DEBUG_MODE') && DEBUG_MODE) {
                        error_log("DEBUG: Attempting to create EmailService");
                    }
                    
                    $emailService = new EmailService($db);
                    
                    if (defined('DEBUG_MODE') && DEBUG_MODE) {
                        error_log("DEBUG: EmailService created, checking if configured");
                    }
                    
                    // Check if SMTP is configured
                    if (!$emailService->isConfigured()) {
                        error_log("SMTP not configured for password reset");
                        
                        // In debug mode, show warning
                        if (defined('DEBUG_MODE') && DEBUG_MODE) {
                            $this->flash('warning', 'SMTP δεν είναι ρυθμισμένο! Το email δεν στάλθηκε. Configure SMTP στο /email/email-settings-phpmailer.php');
                            $this->redirect('/forgot-password');
                        }
                    } else {
                        if (defined('DEBUG_MODE') && DEBUG_MODE) {
                            error_log("DEBUG: SMTP is configured, creating mailer");
                        }
                        
                        $mail = $emailService->createMailer();
                        $mail->addAddress($user['email'], $user['first_name']);
                        
                        if (defined('DEBUG_MODE') && DEBUG_MODE) {
                            error_log("DEBUG: Mailer created, preparing email content");
                        }
                        
                        // Use proper base URL for email links
                        $baseUrl = 'https://ecowatt.gr/crm';
                        $resetLink = $baseUrl . '/reset-password?token=' . $resetToken;
                        
                        require_once __DIR__ . '/../helpers/app_display_name.php';
                        $appName = getAppDisplayName();
                        
                        // HTML Email
                        $mail->isHTML(true);
                        $mail->Subject = 'Ανάκτηση Κωδικού - ' . $appName;
                        $mail->Body = '
                        <html>
                        <head>
                            <meta charset="UTF-8">
                        </head>
                        <body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4;">
                            <div style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                <div style="text-align: center; margin-bottom: 30px;">
                                    <h1 style="color: #007bff; margin: 0;">' . htmlspecialchars($appName) . '</h1>
                                    <p style="color: #666; margin: 5px 0;">ECOWATT Ενεργειακές Λύσεις</p>
                                </div>
                                
                                <h2 style="color: #333;">Ανάκτηση Κωδικού</h2>
                                
                                <p>Γεια σας <strong>' . htmlspecialchars($user['first_name']) . '</strong>,</p>
                                
                                <p>Λάβαμε αίτημα επαναφοράς του κωδικού πρόσβασής σας.</p>
                                
                                <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                                    <p style="margin: 0 0 15px 0;">Πατήστε το παρακάτω κουμπί για να δημιουργήσετε νέο κωδικό:</p>
                                    <div style="text-align: center;">
                                        <a href="' . htmlspecialchars($resetLink) . '" 
                                           style="display: inline-block; padding: 12px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                                            Επαναφορά Κωδικού
                                        </a>
                                    </div>
                                </div>
                                
                                <p style="font-size: 14px; color: #666;">Αν δεν μπορείτε να πατήσετε το κουμπί, αντιγράψτε και επικολλήστε αυτό το link στο πρόγραμμα περιήγησής σας:</p>
                                <p style="font-size: 12px; word-break: break-all; background: #f8f9fa; padding: 10px; border-radius: 3px;">
                                    ' . htmlspecialchars($resetLink) . '
                                </p>
                                
                                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
                                    <p style="margin: 0; color: #856404;"><strong>⏰ Σημαντικό:</strong> Ο σύνδεσμος λήγει σε <strong>1 ώρα</strong>.</p>
                                </div>
                                
                                <p style="color: #666; font-size: 14px;">Αν δεν ζητήσατε εσείς την επαναφορά κωδικού, αγνοήστε αυτό το email. Ο κωδικός σας παραμένει ασφαλής.</p>
                                
                                <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
                                
                                <p style="color: #999; font-size: 12px; text-align: center; margin: 0;">
                                    © 2024 ECOWATT Ενεργειακές Λύσεις | ' . htmlspecialchars($appName) . ' v' . APP_VERSION . '<br>
                                    Αυτό είναι ένα αυτοματοποιημένο email. Παρακαλώ μην απαντήσετε.
                                </p>
                            </div>
                        </body>
                        </html>';
                        
                        // Plain text alternative
                        $mail->AltBody = "Γεια σας " . $user['first_name'] . ",\n\n"
                            . "Λάβαμε αίτημα επαναφοράς του κωδικού σας.\n\n"
                            . "Πατήστε τον παρακάτω σύνδεσμο για να δημιουργήσετε νέο κωδικό:\n"
                            . $resetLink . "\n\n"
                            . "Ο σύνδεσμος λήγει σε 1 ώρα.\n\n"
                            . "Αν δεν ζητήσατε εσείς την επαναφορά, αγνοήστε αυτό το email.\n\n"
                            . "Με εκτίμηση,\nECOWATT Team";
                        
                        if (defined('DEBUG_MODE') && DEBUG_MODE) {
                            error_log("DEBUG: Email content prepared, attempting to send to: " . $user['email']);
                        }
                        
                        $mail->send();
                        
                        if (defined('DEBUG_MODE') && DEBUG_MODE) {
                            error_log("DEBUG: Email sent successfully!");
                        }
                        
                        // Log the email
                        $emailService->logEmail($user['email'], $mail->Subject, 'Password Reset', 'sent');
                        
                        // Debug success message
                        if (defined('DEBUG_MODE') && DEBUG_MODE) {
                            error_log("Password reset email sent successfully to: " . $user['email']);
                        }
                    }
                } catch (Exception $emailError) {
                    // Log error
                    error_log("Password reset email error: " . $emailError->getMessage());
                    
                    // In debug mode, show the actual error
                    if (defined('DEBUG_MODE') && DEBUG_MODE) {
                        $this->flash('error', 'Email Error: ' . $emailError->getMessage());
                        $this->redirect('/forgot-password');
                    }
                }
            }
            
            // Always show success message (don't reveal if email exists in production)
            $this->flash('success', 'Αν το email υπάρχει στο σύστημα, θα λάβετε οδηγίες επαναφοράς κωδικού σε λίγα λεπτά.');
            $this->redirect('/login');
            
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $this->flash('error', 'Παρουσιάστηκε σφάλμα. Παρακαλώ δοκιμάστε ξανά.');
            $this->redirect('/forgot-password');
        }
    }
    
    /**
     * Show reset password form
     */
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->flash('error', 'Μη έγκυρος σύνδεσμος επαναφοράς');
            $this->redirect('/login');
        }
        
        // Verify token exists and not expired
        $database = new Database();
        $db = $database->connect();
        $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        
        if (!$stmt->fetch()) {
            $this->flash('error', 'Ο σύνδεσμος επαναφοράς έχει λήξει ή δεν είναι έγκυρος');
            $this->redirect('/login');
        }
        
        $data = [
            'title' => 'Επαναφορά Κωδικού - ' . APP_NAME,
            'token' => $token,
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('auth/reset-password', $data);
    }
    
    /**
     * Process reset password
     */
    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }
        
        try {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            
            // Validate inputs
            if (empty($token) || empty($password) || empty($passwordConfirm)) {
                $this->flash('error', 'Παρακαλώ συμπληρώστε όλα τα πεδία');
                $this->redirect('/reset-password?token=' . urlencode($token));
            }
            
            if ($password !== $passwordConfirm) {
                $this->flash('error', 'Οι κωδικοί δεν ταιριάζουν');
                $this->redirect('/reset-password?token=' . urlencode($token));
            }
            
            if (strlen($password) < 6) {
                $this->flash('error', 'Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες');
                $this->redirect('/reset-password?token=' . urlencode($token));
            }
            
            // Verify token still valid
            $database = new Database();
            $db = $database->connect();
            $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->flash('error', 'Ο σύνδεσμος επαναφοράς έχει λήξει ή δεν είναι έγκυρος');
                $this->redirect('/login');
            }
            
            // Update password and clear reset token
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $user['id']]);
            
            $this->flash('success', 'Ο κωδικός σας ενημερώθηκε επιτυχώς! Μπορείτε τώρα να συνδεθείτε.');
            $this->redirect('/login');
            
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $this->flash('error', 'Παρουσιάστηκε σφάλμα. Παρακαλώ δοκιμάστε ξανά.');
            $this->redirect('/login');
        }
    }
}
