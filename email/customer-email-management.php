<?php
// Customer Email Management Interface
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

// Handle bulk email updates
if ($_POST && isset($_POST['bulk_update_emails'])) {
    $updates = $_POST['email_updates'] ?? [];
    $phone_updates = $_POST['phone_updates'] ?? [];
    
    $updated_count = 0;
    $errors = [];
    
    foreach ($updates as $customer_id => $email) {
        $email = trim($email);
        $phone = trim($phone_updates[$customer_id] ?? '');
        
        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format for customer ID {$customer_id}: {$email}";
            continue;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE customers SET email = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([
                empty($email) ? null : $email,
                empty($phone) ? null : $phone,
                $customer_id
            ]);
            
            if ($stmt->rowCount() > 0) {
                $updated_count++;
            }
        } catch (Exception $e) {
            $errors[] = "Failed to update customer ID {$customer_id}: " . $e->getMessage();
        }
    }
    
    if ($updated_count > 0) {
        $success_message = "✅ Successfully updated {$updated_count} customers!";
    }
    
    if (!empty($errors)) {
        $error_message = "❌ Some updates failed:\n" . implode("\n", $errors);
    }
}

// Get statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_customers,
        SUM(CASE WHEN email IS NOT NULL AND email != '' THEN 1 ELSE 0 END) as has_email,
        SUM(CASE WHEN email IS NULL OR email = '' THEN 1 ELSE 0 END) as missing_email
    FROM customers
")->fetch(PDO::FETCH_ASSOC);

// Get maintenance customers without emails (priority)
$maintenance_no_email = $pdo->query("
    SELECT COUNT(DISTINCT c.id) as count
    FROM customers c
    INNER JOIN transformer_maintenances tm ON (
        c.company_name = tm.customer_name OR 
        CONCAT(c.first_name, ' ', c.last_name) = tm.customer_name
    )
    WHERE (c.email IS NULL OR c.email = '') 
    AND tm.next_maintenance_date >= CURDATE()
")->fetch(PDO::FETCH_ASSOC)['count'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filter options
$filter = $_GET['filter'] ?? 'missing_email';
$search = $_GET['search'] ?? '';

// Build query based on filter
$where_conditions = [];
$params = [];

if ($filter === 'missing_email') {
    $where_conditions[] = "(c.email IS NULL OR c.email = '')";
} elseif ($filter === 'has_email') {
    $where_conditions[] = "(c.email IS NOT NULL AND c.email != '')";
} elseif ($filter === 'maintenance_priority') {
    $where_conditions[] = "(c.email IS NULL OR c.email = '')";
    $where_conditions[] = "tm.next_maintenance_date >= CURDATE()";
}

if (!empty($search)) {
    $where_conditions[] = "(c.company_name LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $search_param = "%{$search}%";
    $params = array_fill(0, 5, $search_param);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get customers with maintenance info
$sql = "
    SELECT 
        c.*,
        CASE 
            WHEN c.company_name IS NOT NULL AND c.company_name != '' THEN c.company_name
            WHEN c.first_name IS NOT NULL AND c.last_name IS NOT NULL THEN CONCAT(c.first_name, ' ', c.last_name)
            WHEN c.first_name IS NOT NULL THEN c.first_name
            WHEN c.last_name IS NOT NULL THEN c.last_name
            ELSE CONCAT('Customer #', c.id)
        END as display_name,
        COUNT(tm.id) as maintenance_count,
        MAX(tm.next_maintenance_date) as next_maintenance_date
    FROM customers c
    LEFT JOIN transformer_maintenances tm ON (
        c.company_name = tm.customer_name OR 
        CONCAT(c.first_name, ' ', c.last_name) = tm.customer_name
    )
    {$where_clause}
    GROUP BY c.id
    ORDER BY 
        CASE WHEN c.email IS NULL OR c.email = '' THEN 0 ELSE 1 END,
        COUNT(tm.id) DESC,
        c.company_name,
        c.last_name
    LIMIT {$per_page} OFFSET {$offset}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$count_sql = "
    SELECT COUNT(DISTINCT c.id) as total
    FROM customers c
    LEFT JOIN transformer_maintenances tm ON (
        c.company_name = tm.customer_name OR 
        CONCAT(c.first_name, ' ', c.last_name) = tm.customer_name
    )
    {$where_clause}
";

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_customers = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_customers / $per_page);
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Email Management - HandyCRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .priority-high { border-left: 4px solid #dc3545; }
        .priority-medium { border-left: 4px solid #ffc107; }
        .priority-low { border-left: 4px solid #6c757d; }
        .email-input { width: 250px; }
        .phone-input { width: 150px; }
    </style>
</head>
<body>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-envelope"></i> Customer Email Management</h2>
                <div class="btn-group">
                    <a href="email-settings-phpmailer.php" class="btn btn-outline-secondary">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="email-templates.php" class="btn btn-outline-info">
                        <i class="fas fa-envelope-open-text"></i> Templates
                    </a>
                    <a href="customer-email-import-export.php" class="btn btn-outline-warning">
                        <i class="fas fa-file-csv"></i> Import/Export
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $stats['total_customers']; ?></h4>
                                    <span>Total Customers</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $stats['has_email']; ?></h4>
                                    <span>Have Email</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $stats['missing_email']; ?></h4>
                                    <span>Missing Email</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $maintenance_no_email; ?></h4>
                                    <span>Maintenance Priority</span>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-tools fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
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

            <!-- Filters and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="filter" class="form-label">Filter</label>
                            <select class="form-select" id="filter" name="filter">
                                <option value="missing_email" <?php echo $filter === 'missing_email' ? 'selected' : ''; ?>>Missing Email</option>
                                <option value="has_email" <?php echo $filter === 'has_email' ? 'selected' : ''; ?>>Has Email</option>
                                <option value="maintenance_priority" <?php echo $filter === 'maintenance_priority' ? 'selected' : ''; ?>>Maintenance Priority</option>
                                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Customers</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, email, or phone...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Customer List -->
            <form method="POST">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Customer List 
                            <small class="text-muted">(<?php echo $total_customers; ?> total)</small>
                        </h5>
                        <button type="submit" name="bulk_update_emails" class="btn btn-success">
                            <i class="fas fa-save"></i> Save All Changes
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Customer</th>
                                        <th>Current Email</th>
                                        <th>Phone</th>
                                        <th>Maintenances</th>
                                        <th>Priority</th>
                                        <th>Update Email</th>
                                        <th>Update Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $customer): ?>
                                    <?php
                                    $priority_class = '';
                                    $priority_text = '';
                                    if ($customer['maintenance_count'] > 0 && empty($customer['email'])) {
                                        $priority_class = 'priority-high';
                                        $priority_text = 'High - Has Maintenance';
                                    } elseif ($customer['maintenance_count'] > 0) {
                                        $priority_class = 'priority-medium';
                                        $priority_text = 'Medium - Has Maintenance';
                                    } else {
                                        $priority_class = 'priority-low';
                                        $priority_text = 'Low';
                                    }
                                    ?>
                                    <tr class="<?php echo $priority_class; ?>">
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($customer['display_name'] ?? ''); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo $customer['customer_type']; ?>
                                                    <?php if ($customer['id']): ?>
                                                        | ID: <?php echo $customer['id']; ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($customer['email'])): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> <?php echo htmlspecialchars($customer['email']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times"></i> No Email
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($customer['phone'])): ?>
                                                <small><?php echo htmlspecialchars($customer['phone']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">No Phone</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['maintenance_count'] > 0): ?>
                                                <span class="badge bg-primary"><?php echo $customer['maintenance_count']; ?></span>
                                                <?php if ($customer['next_maintenance_date']): ?>
                                                    <br><small class="text-muted">Next: <?php echo date('d/m/Y', strtotime($customer['next_maintenance_date'])); ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($customer['maintenance_count'] > 0 && empty($customer['email'])): ?>
                                                <span class="badge bg-danger">High Priority</span>
                                            <?php elseif ($customer['maintenance_count'] > 0): ?>
                                                <span class="badge bg-warning">Medium</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Low</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <input type="email" 
                                                   class="form-control form-control-sm email-input" 
                                                   name="email_updates[<?php echo $customer['id']; ?>]"
                                                   value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>"
                                                   placeholder="Enter email address">
                                        </td>
                                        <td>
                                            <input type="tel" 
                                                   class="form-control form-control-sm phone-input" 
                                                   name="phone_updates[<?php echo $customer['id']; ?>]"
                                                   value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>"
                                                   placeholder="Phone number">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Customer pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <!-- Instructions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-info-circle"></i> Usage Instructions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>📧 Email Collection Strategy:</h6>
                            <ul class="small">
                                <li>Focus on <strong>High Priority</strong> customers first</li>
                                <li>Use phone/SMS to request emails</li>
                                <li>Update emails directly in the form</li>
                                <li>Click "Save All Changes" to apply updates</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>🔍 Filtering & Search:</h6>
                            <ul class="small">
                                <li><strong>Missing Email:</strong> Customers without emails</li>
                                <li><strong>Maintenance Priority:</strong> Urgent customers</li>
                                <li><strong>Search:</strong> Find specific customers</li>
                                <li><strong>Has Email:</strong> View completed customers</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>⚡ Bulk Operations:</h6>
                            <ul class="small">
                                <li>Use <strong>Import/Export Tools</strong> for CSV operations</li>
                                <li>Update multiple customers at once</li>
                                <li>Validate email formats automatically</li>
                                <li>Track progress with statistics</li>
                            </ul>
                        </div>
                    </div>
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

// Real-time email validation
document.querySelectorAll('input[type="email"]').forEach(function(input) {
    input.addEventListener('blur', function() {
        if (this.value && !this.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
});
</script>
</body>
</html>