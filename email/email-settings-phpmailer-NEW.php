<?php
// SMTP Email Settings - Works with smtp_settings table (host, port, username columns)
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';

$database = new Database();
$pdo = $database->connect();

$success_message = '';
$error_message = '';

// Handle SAVE SETTINGS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    try {
        $check = $pdo->query("SELECT COUNT(*) FROM smtp_settings")->fetchColumn();
        
        if ($check > 0) {
            $stmt = $pdo->prepare("UPDATE smtp_settings SET host=?, port=?, username=?, password=?, encryption=?, from_email=?, from_name=?, updated_at=CURRENT_TIMESTAMP LIMIT 1");
        } else {
            $stmt = $pdo->prepare("INSERT INTO smtp_settings (host, port, username, password, encryption, from_email, from_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
        }
        
        $stmt->execute([
            $_POST['smtp_host'],
            (int)$_POST['smtp_port'],
            $_POST['smtp_username'],
            $_POST['smtp_password'],
            $_POST['smtp_encryption'],
            $_POST['from_email'],
            $_POST['from_name']
        ]);
        
        $success_message = "✅ SMTP settings saved!";
    } catch (Exception $e) {
        $error_message = "❌ Error: " . $e->getMessage();
    }
}

// Handle TEST EMAIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test_email'])) {
    try {
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        
        $settings = $pdo->query("SELECT * FROM smtp_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $settings['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['username'];
        $mail->Password = $settings['password'];
        $mail->SMTPSecure = $settings['encryption'];
        $mail->Port = $settings['port'];
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($settings['from_email'], $settings['from_name']);
        $mail->addAddress($_POST['test_email']);
        $mail->Subject = 'Test from HandyCRM';
        $mail->Body = 'SMTP is working!';
        $mail->send();
        
        $success_message = "✅ Test email sent to " . $_POST['test_email'];
    } catch (Exception $e) {
        $error_message = "❌ Failed: " . $e->getMessage();
    }
}

// Get current settings
$settings = $pdo->query("SELECT * FROM smtp_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$page_title = "Email Settings";
require_once __DIR__ . '/../views/includes/header.php';
?>

<div class="container-fluid mt-4">
    <h2><i class="fas fa-envelope"></i> SMTP Email Settings</h2>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- SMTP Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5><i class="fas fa-server"></i> SMTP Configuration</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>SMTP Host *</label>
                        <input type="text" class="form-control" name="smtp_host" value="<?= htmlspecialchars($settings['host'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Port *</label>
                        <input type="number" class="form-control" name="smtp_port" value="<?= $settings['port'] ?? 465 ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Encryption *</label>
                        <select class="form-control" name="smtp_encryption" required>
                            <option value="ssl" <?= ($settings['encryption'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="tls" <?= ($settings['encryption'] ?? '') == 'tls' ? 'selected' : '' ?>>TLS</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Username *</label>
                        <input type="text" class="form-control" name="smtp_username" value="<?= htmlspecialchars($settings['username'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Password *</label>
                        <input type="password" class="form-control" name="smtp_password" value="<?= htmlspecialchars($settings['password'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>From Email *</label>
                        <input type="email" class="form-control" name="from_email" value="<?= htmlspecialchars($settings['from_email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>From Name *</label>
                        <input type="text" class="form-control" name="from_name" value="<?= htmlspecialchars($settings['from_name'] ?? 'HandyCRM') ?>" required>
                    </div>
                </div>
                <button type="submit" name="save_settings" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </form>
        </div>
    </div>

    <!-- Test Email -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5><i class="fas fa-paper-plane"></i> Send Test Email</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <input type="email" class="form-control" name="test_email" placeholder="Enter test email" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="send_test_email" class="btn btn-success w-100">
                            <i class="fas fa-paper-plane"></i> Send Test
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../views/includes/footer.php'; ?>
