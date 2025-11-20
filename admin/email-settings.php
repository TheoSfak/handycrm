<?php
require_once '../config.php';
require_once '../classes/Database.php';
require_once '../classes/EmailService.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$emailService = new EmailService();

// Handle SMTP settings form
if ($_POST && isset($_POST['save_smtp'])) {
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_port = $_POST['smtp_port'] ?? 587;
    $smtp_user = $_POST['smtp_user'] ?? '';
    $smtp_pass = $_POST['smtp_pass'] ?? '';
    $smtp_from_email = $_POST['smtp_from_email'] ?? '';
    $smtp_from_name = $_POST['smtp_from_name'] ?? 'HandyCRM';
    
    try {
        // Save to database settings table
        $settings = [
            'smtp_host' => $smtp_host,
            'smtp_port' => $smtp_port,
            'smtp_user' => $smtp_user,
            'smtp_pass' => $smtp_pass,
            'smtp_from_email' => $smtp_from_email,
            'smtp_from_name' => $smtp_from_name
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                 ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        
        $success_message = "Οι ρυθμίσεις SMTP αποθηκεύτηκαν επιτυχώς!";
        
    } catch (Exception $e) {
        $error_message = "Σφάλμα κατά την αποθήκευση: " . $e->getMessage();
    }
}

// Handle test email
if ($_POST && isset($_POST['send_test'])) {
    $test_email = $_POST['test_email'] ?? '';
    $test_name = $_POST['test_name'] ?? 'Test User';
    
    try {
        if (empty($test_email)) {
            throw new Exception('Παρακαλώ εισάγετε email για το test');
        }
        
        $result = $emailService->sendTestEmail($test_email, $test_name);
        
        if ($result) {
            $success_message = "Test email στάλθηκε επιτυχώς στο {$test_email}!";
        } else {
            $error_message = "Αποτυχία αποστολής test email. Ελέγξτε τις ρυθμίσεις SMTP.";
        }
        
    } catch (Exception $e) {
        $error_message = "Σφάλμα: " . $e->getMessage();
    }
}

// Get current SMTP settings
$smtp_settings = [];
try {
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'");
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($settings as $setting) {
        $smtp_settings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (Exception $e) {
    // Use defaults if no settings found
}

// Get recent notifications
$notifications = $emailService->getNotificationHistory(1, 10);

// Page title
$page_title = "Ρυθμίσεις Email";
require_once '../views/includes/header.php';
?>

<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-envelope"></i> Ρυθμίσεις Email System</h2>
                <div class="btn-group">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Πίσω στο Dashboard
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- SMTP Configuration Tab -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-server"></i> Ρυθμίσεις SMTP Server</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_host" class="form-label">SMTP Host *</label>
                                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                                   value="<?php echo htmlspecialchars($smtp_settings['smtp_host'] ?? ''); ?>" 
                                                   placeholder="smtp.gmail.com" required>
                                            <div class="form-text">π.χ. smtp.gmail.com, mail.yourcompany.com</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_port" class="form-label">SMTP Port *</label>
                                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                   value="<?php echo htmlspecialchars($smtp_settings['smtp_port'] ?? '587'); ?>" 
                                                   placeholder="587" required>
                                            <div class="form-text">Συνήθως: 587 (TLS), 465 (SSL), 25 (unsecured)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_user" class="form-label">SMTP Username *</label>
                                            <input type="email" class="form-control" id="smtp_user" name="smtp_user" 
                                                   value="<?php echo htmlspecialchars($smtp_settings['smtp_user'] ?? ''); ?>" 
                                                   placeholder="your-email@gmail.com" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_pass" class="form-label">SMTP Password *</label>
                                            <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" 
                                                   value="<?php echo htmlspecialchars($smtp_settings['smtp_pass'] ?? ''); ?>" 
                                                   placeholder="App Password ή κωδικός" required>
                                            <div class="form-text">Για Gmail χρησιμοποιήστε App Password</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_from_email" class="form-label">From Email *</label>
                                            <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" 
                                                   value="<?php echo htmlspecialchars($smtp_settings['smtp_from_email'] ?? ''); ?>" 
                                                   placeholder="noreply@yourcompany.com" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_from_name" class="form-label">From Name</label>
                                            <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" 
                                                   value="<?php echo htmlspecialchars($smtp_settings['smtp_from_name'] ?? 'HandyCRM'); ?>" 
                                                   placeholder="HandyCRM">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" name="save_smtp" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Αποθήκευση Ρυθμίσεων
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Test Email Panel -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-paper-plane"></i> Test Email</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="test_email" class="form-label">Προς Email</label>
                                    <input type="email" class="form-control" id="test_email" name="test_email" 
                                           placeholder="test@example.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="test_name" class="form-label">Όνομα</label>
                                    <input type="text" class="form-control" id="test_name" name="test_name" 
                                           value="Test User" placeholder="Test User">
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="send_test" class="btn btn-success" 
                                            <?php echo !$emailService->isConfigured() ? 'disabled' : ''; ?>>
                                        <i class="fas fa-paper-plane"></i> Αποστολή Test
                                    </button>
                                </div>
                                <?php if (!$emailService->isConfigured()): ?>
                                    <div class="text-muted mt-2">
                                        <small>Αποθηκεύστε πρώτα τις ρυθμίσεις SMTP</small>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Configuration Status -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6><i class="fas fa-info-circle"></i> Κατάσταση</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <?php if ($emailService->isConfigured()): ?>
                                    <span class="badge bg-success me-2">
                                        <i class="fas fa-check"></i> Διαμορφωμένο
                                    </span>
                                    <small class="text-success">Email system έτοιμο!</small>
                                <?php else: ?>
                                    <span class="badge bg-warning me-2">
                                        <i class="fas fa-exclamation"></i> Μη διαμορφωμένο
                                    </span>
                                    <small class="text-muted">Χρειάζονται ρυθμίσεις SMTP</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <?php if (!empty($notifications)): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-history"></i> Πρόσφατες Αποστολές Email</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ημερομηνία</th>
                                    <th>Τύπος</th>
                                    <th>Προς</th>
                                    <th>Θέμα</th>
                                    <th>Κατάσταση</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notifications as $notification): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $notification['type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($notification['recipient_email']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($notification['subject'], 0, 50)) . (strlen($notification['subject']) > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $notification['status_class']; ?>">
                                            <?php echo ucfirst($notification['status']); ?>
                                        </span>
                                        <?php if ($notification['status'] === 'failed' && $notification['error_message']): ?>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($notification['error_message']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Help Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-question-circle"></i> Οδηγίες Εγκατάστασης</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📧 Gmail Setup:</h6>
                            <ul class="list-unstyled">
                                <li>• Host: <code>smtp.gmail.com</code></li>
                                <li>• Port: <code>587</code></li>
                                <li>• Ενεργοποιήστε 2-Factor Authentication</li>
                                <li>• Δημιουργήστε App Password</li>
                                <li>• Χρησιμοποιήστε το App Password στο SMTP Password</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>🔧 Custom SMTP:</h6>
                            <ul class="list-unstyled">
                                <li>• Συμβουλευτείτε τον hosting provider σας</li>
                                <li>• Συνήθως: <code>mail.yourdomain.com</code></li>
                                <li>• Port 587 για TLS ή 465 για SSL</li>
                                <li>• Username συνήθως ίδιο με email</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-hide alert messages after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);
</script>

<?php require_once '../views/includes/footer.php'; ?>