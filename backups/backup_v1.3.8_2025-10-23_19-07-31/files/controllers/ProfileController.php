<?php
/**
 * User Profile Controller
 * Handles user profile view and updates
 */

class ProfileController extends BaseController {
    
    /**
     * Show user profile
     */
    public function index() {
        $user = $this->getCurrentUser();
        
        $data = [
            'title' => 'Προφίλ - ' . APP_NAME,
            'user' => $user
        ];
        
        $this->view('profile/index', $data);
    }
    
    /**
     * Update user profile
     */
    public function update() {
        $user = $this->getCurrentUser();
        
        // CSRF validation
        if (!isset($_POST[CSRF_TOKEN_NAME]) || $_POST[CSRF_TOKEN_NAME] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Μη έγκυρο αίτημα';
            $this->redirect('/profile');
        }
        
        $database = new Database();
        $db = $database->connect();
        
        try {
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Μη έγκυρη διεύθυνση email');
            }
            
            // Check if email exists for another user
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                throw new Exception('Το email χρησιμοποιείται ήδη από άλλο χρήστη');
            }
            
            // Update user
            $stmt = $db->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$firstName, $lastName, $email, $phone, $user['id']]);
            
            // Update session
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            
            $_SESSION['success'] = 'Το προφίλ ενημερώθηκε με επιτυχία';
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        $this->redirect('/profile');
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $user = $this->getCurrentUser();
        
        // CSRF validation
        if (!isset($_POST[CSRF_TOKEN_NAME]) || $_POST[CSRF_TOKEN_NAME] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = 'Μη έγκυρο αίτημα';
            $this->redirect('/profile');
        }
        
        $database = new Database();
        $db = $database->connect();
        
        try {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Get current password hash
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify current password
            if (!password_verify($currentPassword, $userData['password'])) {
                throw new Exception('Το τρέχον password είναι λάθος');
            }
            
            // Validate new password
            if (strlen($newPassword) < 6) {
                throw new Exception('Το νέο password πρέπει να έχει τουλάχιστον 6 χαρακτήρες');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('Τα passwords δεν ταιριάζουν');
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            $_SESSION['success'] = 'Το password άλλαξε με επιτυχία';
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        
        $this->redirect('/profile');
    }
}
