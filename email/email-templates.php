<?php
// Email Template Management System
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/config.php';
require_once '../classes/Database.php';

// Language is already loaded via config.php which includes functions.php with __() helper

// Database connection using proper Database class
try {
    $database = new Database();
    $pdo = $database->connect();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['save_template'])) {
        $template_name = $_POST['template_name'];
        $subject = $_POST['subject'];
        $body = $_POST['body'];
        $template_type = $_POST['template_type'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (isset($_POST['template_id']) && !empty($_POST['template_id'])) {
            // Update existing template
            $stmt = $pdo->prepare("UPDATE email_templates SET name = ?, subject = ?, body_html = ?, type = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$template_name, $subject, $body, $template_type, $is_active, $_POST['template_id']]);
            $success_message = "✅ " . __('email_templates.template_updated', 'Template updated successfully!');
        } else {
            // Create new template
            $stmt = $pdo->prepare("INSERT INTO email_templates (name, subject, body_html, type, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            $stmt->execute([$template_name, $subject, $body, $template_type, $is_active]);
            $success_message = "✅ " . __('email_templates.template_created', 'Template created successfully!');
        }
    } elseif (isset($_POST['delete_template'])) {
        $stmt = $pdo->prepare("DELETE FROM email_templates WHERE id = ?");
        $stmt->execute([$_POST['template_id']]);
        $success_message = "✅ " . __('email_templates.template_deleted', 'Template deleted successfully!');
    } elseif (isset($_POST['test_template'])) {
        $template_id = $_POST['template_id'];
        
        // Get template
        $stmt = $pdo->prepare("SELECT * FROM email_templates WHERE id = ?");
        $stmt->execute([$template_id]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($template) {
            // Mock data for template testing
            $mock_data = [
                '{customer_name}' => 'Δοκιμαστικός Πελάτης',
                '{maintenance_date}' => '2024-02-15',
                '{maintenance_time}' => '10:00',
                '{equipment_type}' => 'Μετασχηματιστής',
                '{location}' => 'Αθήνα, Κέντρο',
                '{contact_phone}' => '210-1234567',
                '{company_name}' => 'HandyCRM'
            ];
            
            $test_subject = str_replace(array_keys($mock_data), array_values($mock_data), $template['subject']);
            $test_body = str_replace(array_keys($mock_data), array_values($mock_data), $template['body']);
            
            // Show preview instead of sending
            $preview_subject = $test_subject;
            $preview_body = $test_body;
        }
    }
}

// Get edit template if requested
$edit_template = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM email_templates WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_template = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all templates
$templates = $pdo->query("SELECT * FROM email_templates ORDER BY type, name")->fetchAll(PDO::FETCH_ASSOC);

// Template types
$template_types = [
    'maintenance_reminder' => __('email_templates.maintenance_reminder', 'Maintenance Reminder'),
    'maintenance_confirmation' => __('email_templates.maintenance_confirmation', 'Maintenance Confirmation'),
    'maintenance_completion' => __('email_templates.maintenance_completion', 'Maintenance Completion'),
    'appointment_reminder' => __('email_templates.appointment_reminder', 'Appointment Reminder'),
    'welcome' => __('email_templates.welcome', 'Welcome Email'),
    'general' => __('email_templates.general', 'General Purpose')
];
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Templates - HandyCRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-envelope-open-text"></i> <?php echo __('email_templates.title', 'Email Templates'); ?></h2>
                <div class="btn-group">
                    <a href="email-settings-phpmailer.php" class="btn btn-outline-secondary">
                        <i class="fas fa-cog"></i> <?php echo __('email_templates.email_settings', 'Email Settings'); ?>
                    </a>
                    <a href="customer-email-management.php" class="btn btn-outline-info">
                        <i class="fas fa-users"></i> <?php echo __('email_templates.email_management', 'Email Management'); ?>
                    </a>
                    <a href="customer-email-import-export.php" class="btn btn-outline-success">
                        <i class="fas fa-file-csv"></i> <?php echo __('email_templates.import_export', 'Import/Export'); ?>
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal">
                        <i class="fas fa-plus"></i> <?php echo __('email_templates.new_template', 'New Template'); ?>
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Preview Modal -->
            <?php if (isset($preview_subject)): ?>
            <div class="modal fade show" id="previewModal" tabindex="-1" style="display: block;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-eye"></i> <?php echo __('email_templates.template_preview', 'Template Preview'); ?></h5>
                            <button type="button" class="btn-close" onclick="document.getElementById('previewModal').style.display='none'"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label"><strong><?php echo __('email_templates.subject', 'Subject'); ?>:</strong></label>
                                <div class="alert alert-info"><?php echo htmlspecialchars($preview_subject); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong><?php echo __('email_templates.body', 'Body'); ?>:</strong></label>
                                <div class="border p-3" style="background: #f8f9fa; white-space: pre-wrap;"><?php echo htmlspecialchars($preview_body); ?></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('previewModal').style.display='none'"><?php echo __('email_templates.close', 'Close'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
            <?php endif; ?>

            <!-- Templates List -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> <?php echo __('email_templates.available_templates', 'Available Templates'); ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo __('email_templates.template_name', 'Template Name'); ?></th>
                                    <th><?php echo __('email_templates.type', 'Type'); ?></th>
                                    <th><?php echo __('email_templates.subject', 'Subject'); ?></th>
                                    <th><?php echo __('email_templates.status', 'Status'); ?></th>
                                    <th><?php echo __('email_templates.last_updated', 'Last Updated'); ?></th>
                                    <th><?php echo __('email_templates.actions', 'Actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($template['name'] ?? ''); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $template_types[$template['type']] ?? $template['type']; ?></span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(substr($template['subject'], 0, 50)); ?><?php echo strlen($template['subject']) > 50 ? '...' : ''; ?></small>
                                    </td>
                                    <td>
                                        <?php if ($template['is_active']): ?>
                                            <span class="badge bg-success"><?php echo __('email_templates.active', 'Active'); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo __('email_templates.inactive', 'Inactive'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo date('d/m/Y H:i', strtotime($template['updated_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                <button type="submit" name="test_template" class="btn btn-outline-info" title="Test Template">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </form>
                                            <a href="?edit=<?php echo $template['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('<?php echo __('email_templates.delete_confirm', 'Are you sure you want to delete this template?'); ?>')">
                                                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                <button type="submit" name="delete_template" class="btn btn-outline-danger" title="<?php echo __('email_templates.delete', 'Delete'); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Variable Reference -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6><i class="fas fa-code"></i> <?php echo __('email_templates.available_variables', 'Available Template Variables'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6><?php echo __('email_templates.customer_variables', 'Customer Variables'); ?>:</h6>
                            <ul class="list-unstyled">
                                <li><code>{customer_name}</code></li>
                                <li><code>{customer_email}</code></li>
                                <li><code>{customer_phone}</code></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6><?php echo __('email_templates.maintenance_variables', 'Maintenance Variables'); ?>:</h6>
                            <ul class="list-unstyled">
                                <li><code>{maintenance_date}</code></li>
                                <li><code>{maintenance_time}</code></li>
                                <li><code>{equipment_type}</code></li>
                                <li><code>{location}</code></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6><?php echo __('common.company', 'Company'); ?> Variables:</h6>
                            <ul class="list-unstyled">
                                <li><code>{company_name}</code></li>
                                <li><code>{contact_phone}</code></li>
                                <li><code>{website}</code></li>
                            </ul>
                        </div>
                        <div class="col-md-3">
                            <h6><?php echo __('email_templates.system_variables', 'System Variables'); ?>:</h6>
                            <ul class="list-unstyled">
                                <li><code>{current_date}</code></li>
                                <li><code>{current_time}</code></li>
                                <li><code>{year}</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope-open-text"></i> 
                        <?php echo $edit_template ? 'Edit Template' : 'New Template'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($edit_template): ?>
                        <input type="hidden" name="template_id" value="<?php echo $edit_template['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="template_name" class="form-label">Template Name *</label>
                                <input type="text" class="form-control" id="template_name" name="template_name" 
                                       value="<?php echo htmlspecialchars($edit_template['template_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="template_type" class="form-label">Type *</label>
                                <select class="form-select" id="template_type" name="template_type" required>
                                    <?php foreach ($template_types as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" 
                                                <?php echo ($edit_template['type'] ?? '') === $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Email Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               value="<?php echo htmlspecialchars($edit_template['subject'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="body" class="form-label">Email Body *</label>
                        <textarea class="form-control" id="body" name="body" rows="10" required><?php echo htmlspecialchars($edit_template['body'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               <?php echo ($edit_template['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">
                            Active Template
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_template" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>

<script>
// Show edit modal if editing
<?php if ($edit_template): ?>
document.addEventListener('DOMContentLoaded', function() {
    var templateModal = new bootstrap.Modal(document.getElementById('templateModal'));
    templateModal.show();
});
<?php endif; ?>

// Auto-hide alerts
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        alert.style.display = 'none';
    });
}, 5000);
</script>
</body>
</html>