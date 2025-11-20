<?php
// SMTP Email Settings with PHPMailer - Enhanced Version
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/EmailService.php';

// Database connection using proper Database class
try {
    $database = new Database();
    $pdo = $database->connect();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['save_settings'])) {
        $settings = [
            'smtp_host' => $_POST['smtp_host'],
            'smtp_port' => $_POST['smtp_port'],
            'smtp_username' => $_POST['smtp_username'],
            'smtp_password' => $_POST['smtp_password'],
            'smtp_encryption' => $_POST['smtp_encryption'],
            'from_email' => $_POST['from_email'],
            'from_name' => $_POST['from_name'],
            'reply_to' => $_POST['reply_to']
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO smtp_settings (setting_key, setting_value, updated_at) 
                VALUES (?, ?, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$key, $value, $value]);
        }
        
        $success_message = "✅ SMTP settings saved successfully!";
    } elseif (isset($_POST['send_test_email'])) {
        $test_email = $_POST['test_email'];
        
        if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "❌ Invalid email address format.";
        } else {
            try {
                $emailService = new EmailService($pdo);
                $result = $emailService->sendTestEmail($test_email);
                
                if ($result['success']) {
                    $success_message = "✅ Test email sent successfully to {$test_email}!";
                } else {
                    $error_message = "❌ Failed to send test email: " . $result['error'];
                }
            } catch (Exception $e) {
                $error_message = "❌ Email sending failed: " . $e->getMessage();
            }
        }
    }
}

// Get current settings from smtp_settings table
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM smtp_settings");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get email history
$email_history = $pdo->query("
    SELECT * FROM email_notifications 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Email Settings - HandyCRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-3">
    <!-- Header with Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-envelope-square"></i> SMTP Email Settings</h2>
        <div class="btn-group">
            <a href="../index.php?route=/settings" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Ρυθμίσεις
            </a>
            <a href="email-templates.php" class="btn btn-outline-info">
                <i class="fas fa-envelope-open-text"></i> Templates
            </a>
            <a href="customer-email-management.php" class="btn btn-outline-success">
                <i class="fas fa-users"></i> Email Management
            </a>
            <a href="customer-email-import-export.php" class="btn btn-outline-warning">
                <i class="fas fa-file-csv"></i> Import/Export
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Main Settings Form -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-envelope-square"></i> SMTP Email Configuration</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        
                        <!-- Show Next Steps if test email was sent successfully -->
                        <?php if (strpos($success_message, 'Test email sent successfully') !== false): ?>
                            <div class="card mb-4" style="border: 2px solid #28a745;">
                                <div class="card-header bg-success text-white">
                                    <h5><i class="fas fa-rocket"></i> 🎉 Email System Ready! - Επόμενα Βήματα</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-users"></i> Customer Email Management</h6>
                                            <p class="small">Διαχείριση email πελατών και στατιστικά</p>
                                            <a href="customer-email-management.php" class="btn btn-primary btn-sm mb-2">
                                                <i class="fas fa-envelope-open-text"></i> Customer Email Interface
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-file-import"></i> Import/Export Tools</h6>
                                            <p class="small">Εισαγωγή και εξαγωγή email από CSV</p>
                                            <a href="customer-email-import-export.php" class="btn btn-info btn-sm mb-2">
                                                <i class="fas fa-download"></i> Import/Export CSV
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-template"></i> Email Templates</h6>
                                            <p class="small">Διαχείριση templates για emails</p>
                                            <a href="email-templates.php" class="btn btn-warning btn-sm mb-2">
                                                <i class="fas fa-edit"></i> Manage Templates
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-cogs"></i> Automation Setup</h6>
                                            <p class="small">Αυτοματισμός maintenance reminders</p>
                                            <span class="badge bg-secondary">Coming Next</span>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="alert alert-info mb-0">
                                        <strong><i class="fas fa-lightbulb"></i> Tip:</strong> 
                                        Ξεκινήστε με το <strong>Customer Email Management</strong> για να συλλέξετε email διευθύνσεις πελατών!
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="smtp_host" class="form-label">SMTP Host *</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                           value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" 
                                           placeholder="smtp.gmail.com" required>
                                    <div class="form-text">Gmail: smtp.gmail.com | Outlook: smtp-mail.outlook.com</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="smtp_port" class="form-label">SMTP Port *</label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                           value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>" 
                                           placeholder="587" required>
                                    <div class="form-text">TLS: 587 | SSL: 465</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="smtp_username" class="form-label">SMTP Username *</label>
                                    <input type="email" class="form-control" id="smtp_username" name="smtp_username" 
                                           value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>" 
                                           placeholder="your-email@gmail.com" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="smtp_password" class="form-label">SMTP Password *</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                           value="<?php echo htmlspecialchars($settings['smtp_password'] ?? ''); ?>" 
                                           placeholder="App Password for Gmail" required>
                                    <div class="form-text">
                                        <small>Gmail: Use App Password | <a href="https://support.google.com/accounts/answer/185833" target="_blank">How to create App Password</a></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="smtp_encryption" class="form-label">Encryption *</label>
                                    <select class="form-select" id="smtp_encryption" name="smtp_encryption" required>
                                        <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS (Recommended)</option>
                                        <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="from_email" class="form-label">From Email *</label>
                                    <input type="email" class="form-control" id="from_email" name="from_email" 
                                           value="<?php echo htmlspecialchars($settings['from_email'] ?? ''); ?>" 
                                           placeholder="noreply@yourcompany.com" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="from_name" class="form-label">From Name *</label>
                                    <input type="text" class="form-control" id="from_name" name="from_name" 
                                           value="<?php echo htmlspecialchars($settings['from_name'] ?? 'HandyCRM'); ?>" 
                                           placeholder="HandyCRM System" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reply_to" class="form-label">Reply-To Email</label>
                                    <input type="email" class="form-control" id="reply_to" name="reply_to" 
                                           value="<?php echo htmlspecialchars($settings['reply_to'] ?? ''); ?>" 
                                           placeholder="support@yourcompany.com">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="save_settings" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Save SMTP Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Test Email Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-paper-plane"></i> Test Email Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="test_email" class="form-label">Test Email Address</label>
                                    <input type="email" class="form-control" id="test_email" name="test_email" 
                                           placeholder="Enter email to test SMTP configuration" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" name="send_test_email" class="btn btn-success">
                                            <i class="fas fa-envelope"></i> Send Test Email
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Test Email Content:</strong><br>
                        The test email will contain current date/time and SMTP configuration status to verify delivery.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Configuration Status -->
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-chart-line"></i> Configuration Status</h6>
                </div>
                <div class="card-body">
                    <?php
                    $required_settings = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_email'];
                    $configured_count = 0;
                    foreach ($required_settings as $setting) {
                        if (!empty($settings[$setting])) {
                            $configured_count++;
                        }
                    }
                    $completion_percentage = ($configured_count / count($required_settings)) * 100;
                    ?>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Configuration Progress</span>
                            <span><?php echo round($completion_percentage); ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?php echo $completion_percentage === 100 ? 'bg-success' : 'bg-warning'; ?>" 
                                 style="width: <?php echo $completion_percentage; ?>%"></div>
                        </div>
                    </div>

                    <ul class="list-group list-group-flush">
                        <?php foreach ($required_settings as $setting): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <?php echo ucwords(str_replace('_', ' ', $setting)); ?>
                            <?php if (!empty($settings[$setting])): ?>
                                <span class="badge bg-success rounded-pill">✓</span>
                            <?php else: ?>
                                <span class="badge bg-danger rounded-pill">✗</span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Quick Setup Guide -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6><i class="fas fa-rocket"></i> Quick Setup Guide</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">Gmail Setup:</h6>
                    <ul class="small">
                        <li>Host: <code>smtp.gmail.com</code></li>
                        <li>Port: <code>587</code></li>
                        <li>Encryption: <code>TLS</code></li>
                        <li>Enable 2FA & create App Password</li>
                    </ul>

                    <h6 class="text-success mt-3">Outlook/Hotmail:</h6>
                    <ul class="small">
                        <li>Host: <code>smtp-mail.outlook.com</code></li>
                        <li>Port: <code>587</code></li>
                        <li>Encryption: <code>TLS</code></li>
                        <li>Use account password</li>
                    </ul>

                    <div class="alert alert-warning small mt-3">
                        <strong>⚠️ Security Note:</strong><br>
                        Always use App Passwords for Gmail instead of your regular password.
                    </div>
                </div>
            </div>

            <!-- Recent Email Activity -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6><i class="fas fa-history"></i> Recent Email Activity</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($email_history)): ?>
                        <p class="text-muted small">No recent email activity.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($email_history, 0, 5) as $email): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between">
                                    <small class="text-truncate">
                                                            <small class="text-truncate">
                        <?php echo htmlspecialchars($email['recipient_email'] ?? 'Unknown'); ?>
                    </small>
                                    </small>
                                    <?php if ($email['status'] === 'sent'): ?>
                                        <span class="badge bg-success">✓</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">✗</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($email['subject'] ?? ''); ?><br>
                                    <?php echo date('d/m/Y H:i', strtotime($email['created_at'])); ?>
                                </small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide alerts after 5 seconds
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        alert.style.display = 'none';
    });
}, 5000);

// Toggle password visibility
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('smtp_password');
    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'btn btn-outline-secondary';
    toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
    
    // Add toggle functionality (optional enhancement)
    toggleButton.addEventListener('click', function() {
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
        }
    });
});
</script>
</body>
</html>

<?php require_once '../views/includes/footer.php'; ?>