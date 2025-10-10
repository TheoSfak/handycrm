<!-- Project Header -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-primary rounded-circle p-3 me-3">
                        <i class="fas fa-project-diagram text-white fa-2x"></i>
                    </div>
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($project['title']) ?></h2>
                        <div>
                            <?php
                            $statusLabels = [
                                'new' => __('projects.new'),
                                'in_progress' => __('projects.in_progress'),
                                'completed' => __('projects.completed'),
                                'cancelled' => __('projects.cancelled')
                            ];
                            $statusColors = [
                                'new' => 'info',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $priorityLabels = [
                                'low' => __('projects.low'),
                                'medium' => __('projects.medium'),
                                'high' => __('projects.high'),
                                'urgent' => __('projects.urgent')
                            ];
                            $priorityColors = [
                                'low' => 'secondary',
                                'medium' => 'info',
                                'high' => 'warning',
                                'urgent' => 'danger'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusColors[$project['status']] ?? 'secondary' ?> me-2">
                                <i class="fas fa-circle fa-xs"></i> <?= $statusLabels[$project['status']] ?? $project['status'] ?>
                            </span>
                            <span class="badge bg-<?= $priorityColors[$project['priority']] ?? 'secondary' ?>">
                                <i class="fas fa-flag"></i> <?= $priorityLabels[$project['priority']] ?? $project['priority'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end">
                <!-- Quick Status Change -->
                <div class="btn-group mb-1 me-1">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-sync-alt"></i> <?= __('projects.change_status') ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#" onclick="changeStatus('new', <?= $project['id'] ?>)">
                                <i class="fas fa-circle text-info"></i> <?= __('projects.new') ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="changeStatus('in_progress', <?= $project['id'] ?>)">
                                <i class="fas fa-circle text-warning"></i> <?= __('projects.in_progress') ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="changeStatus('completed', <?= $project['id'] ?>)">
                                <i class="fas fa-circle text-success"></i> <?= __('projects.completed') ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="changeStatus('invoiced', <?= $project['id'] ?>)">
                                <i class="fas fa-circle text-primary"></i> <?= __('projects.invoiced') ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" onclick="changeStatus('cancelled', <?= $project['id'] ?>)">
                                <i class="fas fa-circle text-danger"></i> <?= __('projects.cancelled') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <a href="<?= BASE_URL ?>/projects/edit/<?= $project['id'] ?>" class="btn btn-primary mb-1">
                    <i class="fas fa-edit"></i> <?= __('projects.edit') ?>
                </a>
                <a href="?route=/projects" class="btn btn-outline-secondary mb-1">
                    <i class="fas fa-arrow-left"></i> <?= __('projects.back_to_list') ?>
                </a>
            </div>
        </div>

        <?php if (!empty($project['description'])): ?>
        <div class="alert alert-light border-start border-primary border-4 mb-0">
            <h6 class="text-muted mb-2"><i class="fas fa-file-alt"></i> <?= __('projects.description') ?></h6>
            <p class="mb-0"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-4">
        <!-- Customer Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-tie"></i> <?= __('projects.customer') ?></h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light rounded-circle p-2 me-3">
                        <i class="fas fa-<?= $project['customer_type'] === 'company' ? 'building' : 'user' ?> fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">
                            <a href="<?= BASE_URL ?>/customers/<?= $project['customer_slug'] ?>" class="text-decoration-none">
                                <?php
                                if ($project['customer_type'] === 'company' && !empty($project['customer_company_name'])) {
                                    echo htmlspecialchars($project['customer_company_name']);
                                } else {
                                    echo htmlspecialchars($project['customer_first_name'] . ' ' . $project['customer_last_name']);
                                }
                                ?>
                            </a>
                        </h6>
                        <small class="text-muted">
                            <?= $project['customer_type'] === 'company' ? __('customers.company') : __('customers.individual') ?>
                        </small>
                    </div>
                </div>

                <?php if (!empty($project['customer_phone']) || !empty($project['customer_email'])): ?>
                <div class="border-top pt-3">
                    <?php if (!empty($project['customer_phone'])): ?>
                    <div class="mb-2">
                        <a href="tel:<?= htmlspecialchars($project['customer_phone']) ?>" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($project['customer_phone']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($project['customer_email'])): ?>
                    <div class="mb-2">
                        <a href="mailto:<?= htmlspecialchars($project['customer_email']) ?>" class="btn btn-outline-info btn-sm w-100">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($project['customer_email']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($project['customer_address'])): ?>
                <div class="border-top pt-3 mt-2">
                    <small class="text-muted">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= nl2br(htmlspecialchars($project['customer_address'])) ?>
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Details Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> <?= __('projects.details') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($project['project_address'])): ?>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <small class="text-muted fw-bold"><?= strtoupper(__('projects.project_location')) ?></small>
                    </div>
                    <div class="ps-4"><?= nl2br(htmlspecialchars($project['project_address'])) ?></div>
                </div>
                <?php endif; ?>
                
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-calendar-check text-success me-2"></i>
                        <small class="text-muted fw-bold"><?= strtoupper(__('projects.dates')) ?></small>
                    </div>
                    <div class="ps-4">
                        <div class="mb-1">
                            <small class="text-muted"><?= __('projects.start') ?>:</small>
                            <strong><?= formatDate($project['start_date']) ?></strong>
                        </div>
                        <?php if (!empty($project['completion_date'])): ?>
                        <div>
                            <small class="text-muted"><?= __('projects.completion') ?>:</small>
                            <strong><?= formatDate($project['completion_date']) ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-euro-sign text-success me-2"></i>
                        <small class="text-muted fw-bold"><?= strtoupper(__('projects.cost')) ?></small>
                    </div>
                    <div class="ps-4">
                        <h4 class="mb-0 text-success"><?= formatCurrency($project['total_cost']) ?></h4>
                    </div>
                </div>
                
                <div class="mb-0">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-user-cog text-primary me-2"></i>
                        <small class="text-muted fw-bold"><?= strtoupper(__('projects.technician')) ?></small>
                    </div>
                    <div class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 me-2">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($project['tech_first_name'] . ' ' . $project['tech_last_name']) ?></strong>
                                <?php if (!empty($project['tech_phone'])): ?>
                                <br><a href="tel:<?= htmlspecialchars($project['tech_phone']) ?>" class="small text-decoration-none">
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($project['tech_phone']) ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="fas fa-user"></i> <?= __('projects.created_by') ?> 
                    <strong><?= htmlspecialchars($project['creator_first_name'] . ' ' . $project['creator_last_name']) ?></strong>
                    <br>
                    <i class="fas fa-clock"></i> <?= formatDate($project['created_at']) ?>
                </small>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> <?= __('projects.quick_actions') ?></h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="?route=/appointments/create&project_id=<?= $project['id'] ?>&customer_id=<?= $project['customer_id'] ?>" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-calendar-plus"></i> <?= __('projects.new_appointment') ?>
                    </a>
                    <a href="?route=/quotes/create&project_id=<?= $project['id'] ?>&customer_id=<?= $project['customer_id'] ?>" 
                       class="btn btn-outline-info">
                        <i class="fas fa-file-invoice"></i> <?= __('projects.new_quote') ?>
                    </a>
                    <a href="?route=/invoices/create&project_id=<?= $project['id'] ?>&customer_id=<?= $project['customer_id'] ?>" 
                       class="btn btn-outline-success">
                        <i class="fas fa-file-invoice-dollar"></i> <?= __('projects.new_invoice') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-8">
        <!-- Appointments -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> <?= __('menu.appointments') ?></h5>
                <a href="?route=/appointments/create&project_id=<?= $project['id'] ?>&customer_id=<?= $project['customer_id'] ?>" 
                   class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> <?= __('projects.new_appointment') ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($project['appointments'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted"><?= __('projects.no_appointments') ?></p>
                        <a href="?route=/appointments/create&project_id=<?= $project['id'] ?>&customer_id=<?= $project['customer_id'] ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?= __('projects.create_appointment') ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($project['appointments'] as $appointment): ?>
                        <div class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="bg-light rounded p-3 text-center">
                                        <div class="text-primary fw-bold"><?= date('d', strtotime($appointment['appointment_date'])) ?></div>
                                        <div class="small text-muted"><?= date('M', strtotime($appointment['appointment_date'])) ?></div>
                                        <div class="small text-muted"><?= date('Y', strtotime($appointment['appointment_date'])) ?></div>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="mb-1"><?= htmlspecialchars($appointment['title'] ?? 'Ραντεβού') ?></h6>
                                    <div class="small text-muted">
                                        <i class="fas fa-clock"></i> <?= date('H:i', strtotime($appointment['appointment_date'])) ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-user-cog"></i> <?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <?php
                                    $appointmentStatusColors = [
                                        'scheduled' => 'secondary',
                                        'confirmed' => 'info',
                                        'in_progress' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        'no_show' => 'warning'
                                    ];
                                    $appointmentStatusLabels = [
                                        'scheduled' => 'Προγραμματισμένο',
                                        'confirmed' => 'Επιβεβαιωμένο',
                                        'in_progress' => 'Σε Εξέλιξη',
                                        'completed' => 'Ολοκληρωμένο',
                                        'cancelled' => 'Ακυρωμένο',
                                        'no_show' => 'Δεν Εμφανίστηκε'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $appointmentStatusColors[$appointment['status']] ?? 'secondary' ?>">
                                        <?= $appointmentStatusLabels[$appointment['status']] ?? $appointment['status'] ?>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Materials / Notes Section -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-sticky-note"></i> <?= __('projects.notes_and_materials') ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($project['notes'])): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0"><?= __('projects.no_notes') ?></p>
                    </div>
                <?php else: ?>
                    <div class="border-start border-warning border-4 ps-3">
                        <?= nl2br(htmlspecialchars($project['notes'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row mt-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar-check fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small"><?= __('menu.appointments') ?></div>
                        <div class="h4 mb-0"><?= count($project['appointments'] ?? []) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-euro-sign fa-2x text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small"><?= __('projects.material_cost') ?></div>
                        <div class="h4 mb-0"><?= formatCurrency($project['material_cost'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tools fa-2x text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Κόστος Εργασίας</div>
                        <div class="h4 mb-0"><?= formatCurrency($project['labor_cost'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Διάρκεια</div>
                        <div class="h5 mb-0">
                            <?php
                            if (!empty($project['start_date']) && !empty($project['completion_date'])) {
                                $start = new DateTime($project['start_date']);
                                $end = new DateTime($project['completion_date']);
                                $diff = $start->diff($end);
                                echo $diff->days . ' ημέρες';
                            } else {
                                echo 'Σε εξέλιξη';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.list-group-item {
    transition: background-color 0.2s;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
function changeStatus(newStatus, projectId) {
    event.preventDefault();
    
    const statusLabels = {
        'new': 'Νέο',
        'in_progress': 'Σε Εξέλιξη',
        'completed': 'Ολοκληρωμένο',
        'invoiced': 'Τιμολογημένο',
        'cancelled': 'Ακυρωμένο'
    };
    
    if (confirm(`Θέλετε να αλλάξετε το status του έργου σε "${statusLabels[newStatus]}";`)) {
        // Create form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?route=/projects/update-status';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '<?= CSRF_TOKEN_NAME ?>';
        csrfToken.value = '<?= $_SESSION[CSRF_TOKEN_NAME] ?>';
        form.appendChild(csrfToken);
        
        // Add project ID
        const projectIdInput = document.createElement('input');
        projectIdInput.type = 'hidden';
        projectIdInput.name = 'project_id';
        projectIdInput.value = projectId;
        form.appendChild(projectIdInput);
        
        // Add new status
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        form.appendChild(statusInput);
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}
</script>