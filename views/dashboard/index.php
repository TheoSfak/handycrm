<?php
// Check for updates
require_once 'classes/UpdateChecker.php';
$updateChecker = new UpdateChecker();
echo $updateChecker->getUpdateNotification();
?>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0">Πελάτες</h6>
                        <h3 class="mb-0"><?= $stats['total_customers'] ?? 0 ?></h3>
                        <small>+<?= $stats['new_customers_month'] ?? 0 ?> αυτόν τον μήνα</small>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0">Ενεργά Έργα</h6>
                        <h3 class="mb-0"><?= $stats['active_projects'] ?? 0 ?></h3>
                        <small>Σε εξέλιξη</small>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-project-diagram fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0">Ραντεβού Σήμερα</h6>
                        <h3 class="mb-0"><?= $stats['appointments_today'] ?? 0 ?></h3>
                        <small>Προγραμματισμένα</small>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-title mb-0">Έσοδα Μήνα</h6>
                        <h3 class="mb-0"><?= number_format($stats['revenue_month'] ?? 0, 2) ?>€</h3>
                        <small>Ολοκληρωμένα έργα</small>
                    </div>
                    <div class="text-end">
                        <i class="fas fa-euro-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Activities -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Πρόσφατη Δραστηριότητα</h5>
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
                    <p>Δεν υπάρχει πρόσφατη δραστηριότητα</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Appointments -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Επόμενα Ραντεβού</h5>
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
                    <a href="/appointments" class="btn btn-outline-primary btn-sm">
                        Όλα τα Ραντεβού <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-50"></i>
                    <p>Δεν υπάρχουν προγραμματισμένα ραντεβού</p>
                    <a href="/appointments/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Νέο Ραντεβού
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Γρήγορες Ενέργειες</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/customers/create" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span>Νέος Πελάτης</span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/projects/create" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-project-diagram fa-2x mb-2"></i>
                            <span>Νέο Έργο</span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/appointments/create" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <span>Νέο Ραντεβού</span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/quotes/create" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-file-invoice fa-2x mb-2"></i>
                            <span>Νέα Προσφορά</span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/invoices/create" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-receipt fa-2x mb-2"></i>
                            <span>Νέο Τιμολόγιο</span>
                        </a>
                    </div>
                    
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <a href="?route=/appointments/calendar" class="btn btn-outline-dark w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                            <i class="fas fa-calendar fa-2x mb-2"></i>
                            <span>Ημερολόγιο</span>
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
                <h5 class="mb-0"><i class="fas fa-bell"></i> Ειδοποιήσεις</h5>
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
$(document).ready(function() {
    <?php if (isset($_SESSION['first_login']) && $_SESSION['first_login']): ?>
    // Show welcome modal or tour for first-time users
    // You can implement a welcome tour here
    <?php unset($_SESSION['first_login']); ?>
    <?php endif; ?>
});
</script>