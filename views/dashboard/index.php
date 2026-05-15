<?php
// Check for updates
require_once 'classes/UpdateChecker.php';
$updateChecker = new UpdateChecker();
echo $updateChecker->getUpdateNotification();
?>

<div class="row g-3 mb-4">
    <!-- Customers stat card -->
    <div class="col-lg-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-accent" style="background:var(--accent);"></div>
            <div class="stat-label"><?= __('dashboard.customers') ?></div>
            <div class="stat-value" style="color:var(--accent);"><?= $stats['total_customers'] ?? 0 ?></div>
            <div class="stat-sub">+<?= $stats['new_customers_month'] ?? 0 ?> <?= __('dashboard.new_this_month') ?></div>
            <i class="fas fa-users stat-icon" style="color:var(--accent);"></i>
        </div>
    </div>

    <!-- Active Projects stat card -->
    <div class="col-lg-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-accent" style="background:var(--success);"></div>
            <div class="stat-label"><?= __('dashboard.active_projects') ?></div>
            <div class="stat-value" style="color:var(--success);"><?= $stats['active_projects'] ?? 0 ?></div>
            <div class="stat-sub"><?= __('dashboard.in_progress') ?></div>
            <i class="fas fa-layer-group stat-icon" style="color:var(--success);"></i>
        </div>
    </div>

    <!-- Appointments Today stat card -->
    <div class="col-lg-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-accent" style="background:var(--warning);"></div>
            <div class="stat-label"><?= __('dashboard.appointments_today') ?></div>
            <div class="stat-value" style="color:var(--warning);"><?= $stats['appointments_today'] ?? 0 ?></div>
            <div class="stat-sub"><?= __('dashboard.scheduled') ?></div>
            <i class="fas fa-calendar-alt stat-icon" style="color:var(--warning);"></i>
        </div>
    </div>

    <!-- Revenue stat card -->
    <div class="col-lg-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-card-accent" style="background:var(--info);"></div>
            <div class="stat-label"><?= __('dashboard.revenue_month') ?></div>
            <div class="stat-value" style="color:var(--info);font-size:1.5rem;"><?= formatCurrencyWithVAT($stats['revenue_month'] ?? 0) ?></div>
            <div class="stat-sub"><?= ($stats['completed_projects_count'] ?? 0) ?> <?= __('dashboard.completed_projects') ?></div>
            <i class="fas fa-coins stat-icon" style="color:var(--info);"></i>
        </div>
    </div>
</div>

<?php if (($stats['overdue_maintenances'] ?? 0) > 0 || ($stats['upcoming_maintenances'] ?? 0) > 0): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <span class="fw-semibold text-muted me-2">
                        <i class="fas fa-tools me-1"></i> Συντηρήσεις Μ/Σ:
                    </span>
                    <?php if (($stats['overdue_maintenances'] ?? 0) > 0): ?>
                        <a href="<?= BASE_URL ?>/maintenances?upcoming=overdue" class="text-decoration-none">
                            <span class="badge bg-danger fs-6 px-3 py-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <?= $stats['overdue_maintenances'] ?> Ληξιπρόθεσμ<?= $stats['overdue_maintenances'] === 1 ? 'η' : 'ες' ?>
                            </span>
                        </a>
                    <?php endif; ?>
                    <?php if (($stats['upcoming_maintenances'] ?? 0) > 0): ?>
                        <a href="<?= BASE_URL ?>/maintenances?upcoming=1" class="text-decoration-none">
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                <i class="fas fa-clock me-1"></i>
                                <?= $stats['upcoming_maintenances'] ?> επόμεν<?= $stats['upcoming_maintenances'] === 1 ? 'η' : 'ες' ?> 30 ημέρες
                            </span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/maintenances" class="btn btn-sm btn-outline-secondary ms-auto">
                        <i class="fas fa-list me-1"></i>Όλες οι Συντηρήσεις
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Recent Activities -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock"></i> <?= __('dashboard.recent_activity') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_activities)): ?>
                <div class="timeline">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <i class="<?= $activity['icon'] ?> text-primary"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">
                                <a href="<?= $activity['link'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($activity['title']) ?>
                                </a>
                            </h6>
                            <p class="text-muted mb-1"><?= htmlspecialchars($activity['description']) ?></p>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> 
                                <?= formatDate($activity['date'], true) ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                    <p><?= __('dashboard.no_recent_activity') ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Appointments -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> <?= __('dashboard.upcoming_appointments') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_appointments)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($upcoming_appointments as $appointment): ?>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <?php
                                    $customerName = $appointment['customer_type'] === 'company' && !empty($appointment['company_name']) 
                                        ? $appointment['company_name'] 
                                        : $appointment['first_name'] . ' ' . $appointment['last_name'];
                                    echo htmlspecialchars($customerName);
                                    ?>
                                </h6>
                                <p class="mb-1 small text-muted"><?= htmlspecialchars($appointment['title']) ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> 
                                    <?= date('d/m H:i', strtotime($appointment['appointment_date'])) ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge badge-<?= $appointment['status'] ?>">
                                    <?= ucfirst($appointment['status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="?route=/appointments" class="btn btn-outline-primary btn-sm">
                        <?= __('dashboard.all_appointments') ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                    <p><?= __('dashboard.no_upcoming_appointments') ?></p>
                    <a href="?route=/appointments/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> <?= __('dashboard.new_appointment') ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Daily Tasks & Revenue Card -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Έσοδα Μήνα</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted mb-2"><i class="fas fa-tasks text-success"></i> Ημερήσιες Εργασίες</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Ολοκληρωμένες:</span>
                        <span class="badge bg-success"><?= $stats['revenue_breakdown']['daily_tasks_count'] ?? 0 ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><strong>Έσοδα:</strong></span>
                        <span class="text-success fw-bold"><?= formatCurrencyWithVAT($stats['revenue_breakdown']['daily_tasks'] ?? 0) ?></span>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6 class="text-muted mb-2"><i class="fas fa-tools text-info"></i> Συντηρήσεις</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Ολοκληρωμένες:</span>
                        <span class="badge bg-info"><?= $stats['revenue_breakdown']['maintenance_count'] ?? 0 ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><strong>Έσοδα:</strong></span>
                        <span class="text-info fw-bold"><?= formatCurrencyWithVAT($stats['revenue_breakdown']['maintenance'] ?? 0) ?></span>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <h6 class="text-muted mb-2"><i class="fas fa-project-diagram text-primary"></i> Έργα</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Τιμολογημένα:</span>
                        <span class="badge bg-primary"><?= $stats['revenue_breakdown']['projects_count'] ?? 0 ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><strong>Έσοδα:</strong></span>
                        <span class="text-primary fw-bold"><?= formatCurrencyWithVAT($stats['revenue_breakdown']['projects'] ?? 0) ?></span>
                    </div>
                </div>
                <hr class="my-3">
                <div class="text-center bg-light p-3 rounded">
                    <small class="text-muted d-block mb-1">ΣΥΝΟΛΟ ΕΣΟΔΩΝ</small>
                    <h4 class="mb-0 text-success"><?= formatCurrencyWithVAT($stats['revenue_month'] ?? 0) ?></h4>
                    <small class="text-muted"><?= date('m/Y') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> <?= __('dashboard.quick_actions') ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/customers/create" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span><?= __('dashboard.new_customer') ?></span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/projects/create" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-project-diagram fa-2x mb-2"></i>
                            <span><?= __('dashboard.new_project') ?></span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/appointments/create" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <span><?= __('dashboard.new_appointment') ?></span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/quotes/create" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-file-invoice fa-2x mb-2"></i>
                            <span><?= __('dashboard.new_quote') ?></span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/appointments/calendar" class="btn btn-outline-dark w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-calendar fa-2x mb-2"></i>
                            <span><?= __('dashboard.calendar') ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notifications Panel -->
<?php if (!empty($notifications)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-bell"></i> <?= __('dashboard.notifications') ?></h5>
            </div>
            <div class="card-body">
                <?php foreach ($notifications as $notification): ?>
                <div class="alert alert-light border-start border-4 border-warning" role="alert">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                            <p class="mb-0"><?= htmlspecialchars($notification['message']) ?></p>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> 
                                <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                            </small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                onclick="markNotificationRead(<?= $notification['id'] ?>)">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Custom CSS for Timeline -->
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -22px;
    top: 25px;
    height: calc(100% - 10px);
    width: 2px;
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    background-color: white;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.timeline-content {
    padding-left: 15px;
}
</style>

<script>
// Mark notification as read
function markNotificationRead(notificationId) {
    $.post('/dashboard/mark-notification-read', {
        notification_id: notificationId,
        csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
    }, function(data) {
        if (data.success) {
            location.reload();
        }
    });
}

// Auto-refresh dashboard data every 5 minutes
setInterval(function() {
    // You can implement AJAX refresh here if needed
}, 300000);

// Welcome message for new users
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['first_login']) && $_SESSION['first_login']): ?>
    // Show welcome modal or tour for first-time users
    // You can implement a welcome tour here
    <?php unset($_SESSION['first_login']); ?>
    <?php endif; ?>
});
</script>