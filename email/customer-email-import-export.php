<?php
// Customer Email Import/Export Tool
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/Database.php';

// Database connection using proper Database class
try {
    $database = new Database();
    $pdo = $database->connect();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $sql = "
        SELECT 
            c.id,
            COALESCE(c.company_name, CONCAT(c.first_name, ' ', c.last_name)) as customer_name,
            c.first_name,
            c.last_name,
            c.company_name,
            c.email,
            c.phone,
            c.mobile,
            c.customer_type,
            COUNT(tm.id) as maintenance_count,
            MAX(tm.next_maintenance_date) as next_maintenance
        FROM customers c
        LEFT JOIN transformer_maintenances tm ON (
            c.company_name = tm.customer_name OR 
            CONCAT(c.first_name, ' ', c.last_name) = tm.customer_name
        )
        GROUP BY c.id
        ORDER BY 
            CASE WHEN c.email IS NULL OR c.email = '' THEN 0 ELSE 1 END,
            COUNT(tm.id) DESC
    ";
    
    $customers = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    $filename = 'handycrm_customers_' . date('Y-m-d_H-i') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    fputcsv($output, [
        'ID',
        'Customer Name', 
        'First Name',
        'Last Name',
        'Company Name',
        'Email',
        'Phone',
        'Mobile',
        'Type',
        'Maintenances',
        'Next Maintenance',
        'Email Status'
    ]);
    
    // CSV Data
    foreach ($customers as $customer) {
        $emailStatus = !empty($customer['email']) ? 'Has Email' : 'Missing Email';
        if ($customer['maintenance_count'] > 0 && empty($customer['email'])) {
            $emailStatus = 'URGENT - Maintenance Missing Email';
        }
        
        fputcsv($output, [
            $customer['id'],
            $customer['customer_name'],
            $customer['first_name'],
            $customer['last_name'],
            $customer['company_name'],
            $customer['email'],
            $customer['phone'],
            $customer['mobile'],
            $customer['customer_type'],
            $customer['maintenance_count'],
            $customer['next_maintenance'],
            $emailStatus
        ]);
    }
    
    fclose($output);
    exit;
}

// Handle CSV Import
if ($_POST && isset($_POST['import_csv']) && isset($_FILES['csv_file'])) {
    try {
        $uploaded_file = $_FILES['csv_file']['tmp_name'];
        
        if (!is_uploaded_file($uploaded_file)) {
            throw new Exception('No file uploaded');
        }
        
        $handle = fopen($uploaded_file, 'r');
        if (!$handle) {
            throw new Exception('Cannot read uploaded file');
        }
        
        // Skip header row
        fgetcsv($handle);
        
        $imported = 0;
        $errors = [];
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) < 6) continue; // Skip incomplete rows
            
            $id = (int)$data[0];
            $email = trim($data[5]);
            $phone = trim($data[6]);
            
            // Validate email
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$imported}: Invalid email format: {$email}";
                continue;
            }
            
            // Update customer
            $stmt = $pdo->prepare("UPDATE customers SET email = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            if ($stmt->execute([empty($email) ? null : $email, empty($phone) ? null : $phone, $id])) {
                if ($stmt->rowCount() > 0) {
                    $imported++;
                }
            }
        }
        
        fclose($handle);
        
        $success_message = "✅ Successfully imported {$imported} customer email updates!";
        
        if (!empty($errors)) {
            $error_message = "⚠️ Some imports failed:\n" . implode("\n", array_slice($errors, 0, 10));
        }
        
    } catch (Exception $e) {
        $error_message = "❌ Import failed: " . $e->getMessage();
    }
}

// Handle bulk email request
if ($_POST && isset($_POST['send_email_requests'])) {
    $selected_customers = $_POST['selected_customers'] ?? [];
    
    if (empty($selected_customers)) {
        $error_message = "Please select customers to send email requests to.";
    } else {
        // This would integrate with the email system
        $success_message = "Email requests scheduled for " . count($selected_customers) . " customers.";
        // TODO: Implement actual email sending
    }
}

// Get customers for bulk operations
$customers_no_email = $pdo->query("
    SELECT 
        c.*,
        COALESCE(c.company_name, CONCAT(c.first_name, ' ', c.last_name)) as display_name,
        COUNT(tm.id) as maintenance_count
    FROM customers c
    LEFT JOIN transformer_maintenances tm ON (
        c.company_name = tm.customer_name OR 
        CONCAT(c.first_name, ' ', c.last_name) = tm.customer_name
    )
    WHERE c.email IS NULL OR c.email = ''
    GROUP BY c.id
    HAVING c.phone IS NOT NULL AND c.phone != ''
    ORDER BY COUNT(tm.id) DESC, c.company_name, c.last_name
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Email Import/Export - HandyCRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-exchange-alt"></i> Customer Email Import/Export</h2>
                <div class="btn-group">
                    <a href="email-settings-phpmailer.php" class="btn btn-outline-secondary">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="email-templates.php" class="btn btn-outline-info">
                        <i class="fas fa-envelope-open-text"></i> Templates
                    </a>
                    <a href="customer-email-management.php" class="btn btn-outline-success">
                        <i class="fas fa-users"></i> Email Management
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo nl2br(htmlspecialchars($error_message)); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Export Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-download"></i> Export Customer Data</h5>
                        </div>
                        <div class="card-body">
                            <p>Export customer list with email status for external processing:</p>
                            <ul class="list-unstyled">
                                <li>✅ All customer information</li>
                                <li>✅ Email status (missing/present)</li>
                                <li>✅ Maintenance count</li>
                                <li>✅ Priority marking for urgent customers</li>
                            </ul>
                            <a href="?export=csv" class="btn btn-success">
                                <i class="fas fa-file-csv"></i> Download CSV File
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Import Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-upload"></i> Import Customer Emails</h5>
                        </div>
                        <div class="card-body">
                            <p>Upload updated CSV file with customer emails:</p>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="csv_file" class="form-label">CSV File</label>
                                    <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                    <div class="form-text">Use the same format as the exported CSV file</div>
                                </div>
                                <button type="submit" name="import_csv" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Import CSV File
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Email Request -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-envelope-open-text"></i> Request Emails from Customers</h5>
                </div>
                <div class="card-body">
                    <p>Send SMS or call customers to request their email addresses. Focus on customers with scheduled maintenances.</p>
                    
                    <form method="POST">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="select_all" class="form-check-input">
                                        </th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Maintenances</th>
                                        <th>Priority</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers_no_email as $customer): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_customers[]" value="<?php echo $customer['id']; ?>" class="form-check-input customer-checkbox">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($customer['display_name'] ?? ''); ?></strong>
                                            <br><small class="text-muted"><?php echo $customer['customer_type']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($customer['phone'] ?? ''); ?>
                                            <?php if ($customer['mobile']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($customer['mobile'] ?? ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['maintenance_count'] > 0): ?>
                                                <span class="badge bg-primary"><?php echo $customer['maintenance_count']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['maintenance_count'] > 0): ?>
                                                <span class="badge bg-danger">High</span>
                                            <?php elseif ($customer['customer_type'] === 'company'): ?>
                                                <span class="badge bg-warning">Medium</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Low</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Showing customers with phone numbers but missing emails
                            </small>
                            <button type="submit" name="send_email_requests" class="btn btn-outline-primary">
                                <i class="fas fa-paper-plane"></i> Schedule Email Requests
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Instructions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-info-circle"></i> Email Collection Strategies</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>📱 SMS Message Template:</h6>
                            <div class="bg-light p-3 rounded">
                                <em>"Γεια σας! Για καλύτερη εξυπηρέτηση θα θέλαμε το email σας για ειδοποιήσεις συντήρησης. Στείλτε απάντηση με το email σας. Ευχαριστούμε! - HandyCRM"</em>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>📞 Phone Call Script:</h6>
                            <div class="bg-light p-3 rounded">
                                <em>"Καλησπέρα! Σας καλώ από την HandyCRM. Για να σας στέλνουμε υπενθυμίσεις συντήρησης, μπορείτε να μου δώσετε το email σας;"</em>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>📧 Follow-up Email:</h6>
                            <div class="bg-light p-3 rounded">
                                <em>"Σας ευχαριστούμε για το email σας! Από τώρα θα λαμβάνετε υπενθυμίσεις για τις προγραμματισμένες συντηρήσεις."</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Select all checkbox functionality
document.getElementById('select_all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.customer-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Auto-hide alerts
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        alert.style.display = 'none';
    });
}, 5000);
</script>
</body>
</html>