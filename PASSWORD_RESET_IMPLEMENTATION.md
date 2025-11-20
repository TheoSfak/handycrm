# Password Reset Implementation - Summary

## ✅ Completed Files

1. **views/auth/login.php** - Updated με ωραίο footer
2. **views/auth/forgot-password.php** - Νέα σελίδα (CREATED)
3. **views/auth/reset-password.php** - Νέα σελίδα (CREATED)
4. **migrations/add_password_reset_fields.sql** - Νέο migration (CREATED)

## 📝 Επόμενα βήματα:

### 1. Τρέξε το SQL Migration στην βάση (local & production):

```sql
ALTER TABLE users 
ADD COLUMN reset_token VARCHAR(64) NULL AFTER password,
ADD COLUMN reset_token_expiry DATETIME NULL AFTER reset_token,
ADD INDEX idx_reset_token (reset_token);
```

### 2. Πρόσθεσε routes στο index.php:

Βρες τη γραμμή με `/logout` και πρόσθεσε **ΚΑΤΩ ΑΠΟ ΑΥΤΗ**:

```php
// Password Reset Routes
$router->add('/forgot-password', 'AuthController', 'forgotPassword');
$router->add('/reset-password', 'AuthController', 'resetPassword');
```

Και στο POST requests section πρόσθεσε:

```php
$router->add('/forgot-password', 'AuthController', 'processForgotPassword', 'POST');
$router->add('/reset-password', 'AuthController', 'processResetPassword', 'POST');
```

### 3. Πρόσθεσε μεθόδους στο AuthController.php:

Πρόσθεσε στο **ΤΕΛΟΣ του AuthController** (πριν το τελευταίο `}`):

```php
    /**
     * Process forgot password request
     */
    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/forgot-password');
        }
        
        try {
            $email = $this->sanitize($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('error', 'Παρακαλώ εισάγετε έγκυρο email');
                $this->redirect('/forgot-password');
            }
            
            // Check if email exists
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, first_name, email FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save reset token
                $updateStmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $updateStmt->execute([$resetToken, $resetExpiry, $user['id']]);
                
                // Send email with reset link
                $resetLink = BASE_URL . '/reset-password?token=' . $resetToken;
                $subject = 'Ανάκτηση Κωδικού - HandyCRM';
                $message = "Γεια σας " . $user['first_name'] . ",\n\n";
                $message .= "Λάβαμε αίτημα επαναφοράς του κωδικού σας.\n\n";
                $message .= "Πατήστε τον παρακάτω σύνδεσμο για να δημιουργήσετε νέο κωδικό:\n";
                $message .= $resetLink . "\n\n";
                $message .= "Ο σύνδεσμος λήγει σε 1 ώρα.\n\n";
                $message .= "Αν δεν ζητήσατε εσείς την επαναφορά, αγνοήστε αυτό το email.\n\n";
                $message .= "Με εκτίμηση,\nECOWATT Team";
                
                $headers = "From: noreply@ecowatt.gr\r\n";
                $headers .= "Reply-To: info@ecowatt.gr\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();
                
                mail($user['email'], $subject, $message, $headers);
            }
            
            // Always show success message (don't reveal if email exists)
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
        
        // Verify token
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        
        if (!$stmt->fetch()) {
            $this->flash('error', 'Ο σύνδεσμος επαναφοράς έχει λήξει ή δεν είναι έγκυρος');
            $this->redirect('/login');
        }
        
        $data = [
            'title' => 'Επαναφορά Κωδικού - ' . APP_NAME
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
            
            // Verify token
            $db = Database::getInstance()->getConnection();
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
```

## 📋 Checklist Deployment:

- [ ] Ανέβασε `views/auth/login.php` (updated footer)
- [ ] Ανέβασε `views/auth/forgot-password.php` (NEW)
- [ ] Ανέβασε `views/auth/reset-password.php` (NEW)
- [ ] Τρέξε το SQL migration στο local
- [ ] Τρέξε το SQL migration στο production
- [ ] Πρόσθεσε routes στο `index.php`
- [ ] Πρόσθεσε μεθόδους στο `controllers/AuthController.php`
- [ ] Ανέβασε updated `controllers/AuthController.php`
- [ ] Ανέβασε updated `index.php`
- [ ] Test: Forgot Password → Email → Reset Password

## 🎨 Features που πρόσθεσα:

✅ Ωραίο footer στη login με ECOWATT branding
✅ Error messages για λάθος username/password
✅ "Ξέχασα τον κωδικό μου" λειτουργικότητα
✅ Email με reset link (λήγει σε 1 ώρα)
✅ Reset password σελίδα με validation
✅ Security: Δεν αποκαλύπτει αν email υπάρχει ή όχι
✅ Password strength requirements
✅ Token expiry (1 hour)
