<?php
// Email System Navigation Menu - Include this in your main navigation
?>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-envelope"></i> Email Management System
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- SMTP Configuration -->
            <div class="col-md-3">
                <div class="card h-100 border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-cog fa-2x text-info mb-3"></i>
                        <h6>SMTP Settings</h6>
                        <p class="text-muted small">Configure email server settings</p>
                        <a href="email-settings-phpmailer.php" class="btn btn-info btn-sm">
                            <i class="fas fa-tools"></i> Configure
                        </a>
                    </div>
                </div>
            </div>

            <!-- Customer Email Management -->
            <div class="col-md-3">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-3"></i>
                        <h6>Customer Emails</h6>
                        <p class="text-muted small">Manage customer email addresses</p>
                        <a href="customer-email-management.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Manage
                        </a>
                    </div>
                </div>
            </div>

            <!-- Import/Export Tools -->
            <div class="col-md-3">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-exchange-alt fa-2x text-success mb-3"></i>
                        <h6>Import/Export</h6>
                        <p class="text-muted small">Bulk email operations</p>
                        <a href="customer-email-import-export.php" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Tools
                        </a>
                    </div>
                </div>
            </div>

            <!-- Email Templates -->
            <div class="col-md-3">
                <div class="card h-100 border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-envelope-open-text fa-2x text-warning mb-3"></i>
                        <h6>Email Templates</h6>
                        <p class="text-muted small">Manage email templates</p>
                        <a href="email-templates.php" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Templates
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Status Row -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-light border">
                    <div class="row text-center">
                        <?php
                        // Quick system status (you can include this where needed)
                        try {
                            $pdo = new PDO('mysql:host=localhost;dbname=handycrm;charset=utf8mb4', 'handycrm_user', 'handycrm123');
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            
                            // Get SMTP status
                            $smtp_config = $pdo->query("SELECT COUNT(*) as configured FROM notification_settings WHERE setting_key = 'smtp_host' AND setting_value IS NOT NULL AND setting_value != ''")->fetch()['configured'];
                            
                            // Get customer email stats
                            $email_stats = $pdo->query("SELECT 
                                COUNT(*) as total_customers,
                                SUM(CASE WHEN email IS NOT NULL AND email != '' THEN 1 ELSE 0 END) as has_email,
                                SUM(CASE WHEN email IS NULL OR email = '' THEN 1 ELSE 0 END) as missing_email
                                FROM customers")->fetch();
                            
                            // Get template count
                            $template_count = $pdo->query("SELECT COUNT(*) as count FROM email_templates WHERE is_active = 1")->fetch()['count'];
                        } catch (Exception $e) {
                            $smtp_config = 0;
                            $email_stats = ['total_customers' => 0, 'has_email' => 0, 'missing_email' => 0];
                            $template_count = 0;
                        }
                        ?>
                        
                        <div class="col-md-3">
                            <strong>SMTP Status:</strong><br>
                            <?php if ($smtp_config > 0): ?>
                                <span class="badge bg-success">✅ Configured</span>
                            <?php else: ?>
                                <span class="badge bg-danger">❌ Not Configured</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-3">
                            <strong>Customer Emails:</strong><br>
                            <span class="badge bg-info"><?php echo $email_stats['has_email']; ?>/<?php echo $email_stats['total_customers']; ?> Complete</span>
                        </div>
                        
                        <div class="col-md-3">
                            <strong>Missing Emails:</strong><br>
                            <?php if ($email_stats['missing_email'] > 0): ?>
                                <span class="badge bg-warning"><?php echo $email_stats['missing_email']; ?> Missing</span>
                            <?php else: ?>
                                <span class="badge bg-success">All Complete</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-3">
                            <strong>Active Templates:</strong><br>
                            <span class="badge bg-primary"><?php echo $template_count; ?> Templates</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>