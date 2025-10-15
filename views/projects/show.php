<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">

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

<!-- Tab Navigation -->
<ul class="nav nav-tabs mb-4" id="projectTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
            <i class="fas fa-info-circle me-2"></i>Γενικά Στοιχεία
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">
            <i class="fas fa-tasks me-2"></i>Εργασίες
            <?php if (!empty($tasksCount)): ?>
                <span class="badge bg-primary rounded-pill ms-1"><?= $tasksCount ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="statistics-tab" data-bs-toggle="tab" data-bs-target="#statistics" type="button" role="tab">
            <i class="fas fa-chart-line me-2"></i>Στατιστικά
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="photos-tab" data-bs-toggle="tab" data-bs-target="#photos" type="button" role="tab">
            <i class="fas fa-camera me-2"></i>Φωτογραφίες
            <?php if (!empty($totalPhotos)): ?>
                <span class="badge bg-primary rounded-pill ms-1"><?= $totalPhotos ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="projectTabContent">
    <!-- Overview Tab -->
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
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
        <!-- Tasks History Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Ιστορικό Εργασιών</h5>
                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/add" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>Νέα Εργασία
                </a>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <i class="fas fa-tasks fa-2x text-primary mb-2"></i>
                            <h3 class="mb-0"><?= $tasksCount ?? 0 ?></h3>
                            <small class="text-muted">Σύνολο Εργασιών</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <i class="fas fa-boxes fa-2x text-warning mb-2"></i>
                            <h3 class="mb-0"><?= formatCurrency($summary['materials_total'] ?? 0) ?></h3>
                            <small class="text-muted">Κόστος Υλικών</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <i class="fas fa-users fa-2x text-info mb-2"></i>
                            <h3 class="mb-0"><?= formatCurrency($summary['labor_total'] ?? 0) ?></h3>
                            <small class="text-muted">Κόστος Εργατικών</small>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($tasksCount)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Δεν υπάρχουν εργασίες ακόμα</p>
                        <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/add" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Δημιουργία Εργασίας
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-lg" onclick="document.getElementById('tasks-tab').click()">
                            <i class="fas fa-list me-2"></i>Προβολή Όλων των Εργασιών (<?= $tasksCount ?>)
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
    </div>
    <!-- End Overview Tab -->

    <!-- Tasks Tab -->
    <div class="tab-pane fade" id="tasks" role="tabpanel">
        <?php
        // Include the tasks index view
        if (file_exists(__DIR__ . '/tasks/index.php')) {
            include __DIR__ . '/tasks/index.php';
        } else {
            ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Το σύστημα εργασιών δεν είναι ακόμα διαθέσιμο.
            </div>
            <?php
        }
        ?>
    </div>
    <!-- End Tasks Tab -->

    <!-- Statistics Tab -->
    <div class="tab-pane fade" id="statistics" role="tabpanel">
        <?php
        // Get statistics data
        require_once 'models/ProjectTask.php';
        $taskModel = new ProjectTask();
        $statistics = $taskModel->getStatistics($project['id']);
        
        // Include the statistics view
        if (file_exists(__DIR__ . '/statistics.php')) {
            include __DIR__ . '/statistics.php';
        } else {
            ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Τα στατιστικά δεν είναι διαθέσιμα.
            </div>
            <?php
        }
        ?>
    </div>
    <!-- End Statistics Tab -->

    <!-- Photos Tab -->
    <div class="tab-pane fade" id="photos" role="tabpanel">
        <?php if ($totalPhotos > 0): ?>
            <?php
            $photoTypes = [
                'before' => ['label' => 'Πριν', 'icon' => 'fa-clock', 'color' => 'primary'],
                'after' => ['label' => 'Μετά', 'icon' => 'fa-check-circle', 'color' => 'success'],
                'during' => ['label' => 'Κατά τη διάρκεια', 'icon' => 'fa-cog', 'color' => 'info'],
                'issue' => ['label' => 'Πρόβλημα/Ζημιά', 'icon' => 'fa-exclamation-triangle', 'color' => 'danger'],
                'other' => ['label' => 'Άλλο', 'icon' => 'fa-image', 'color' => 'secondary']
            ];
            
            foreach ($photoTypes as $type => $info):
                if (!empty($projectPhotos[$type])):
            ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-<?= $info['color'] ?> text-white">
                    <h5 class="mb-0">
                        <i class="fas <?= $info['icon'] ?> me-2"></i><?= $info['label'] ?>
                        <span class="badge bg-light text-<?= $info['color'] ?> ms-2"><?= count($projectPhotos[$type]) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($projectPhotos[$type] as $photo): ?>
                        <div class="col-md-3 col-sm-4 col-6">
                            <div class="position-relative" style="aspect-ratio: 1; overflow: hidden; border-radius: 8px; border: 2px solid #e0e0e0;">
                                <a href="<?= BASE_URL ?>/<?= htmlspecialchars($photo['file_path']) ?>" 
                                   data-lightbox="project-<?= $project['id'] ?>-<?= $type ?>" 
                                   data-title="<?= htmlspecialchars($photo['caption'] ?: $info['label']) ?> - <?= htmlspecialchars($photo['task_description']) ?>">
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($photo['file_path']) ?>" 
                                         alt="<?= htmlspecialchars($photo['caption']) ?>"
                                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.2s;"
                                         onmouseover="this.style.transform='scale(1.1)'" 
                                         onmouseout="this.style.transform='scale(1)'">
                                </a>
                                <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-2" style="font-size: 0.75rem;">
                                    <div class="text-truncate">
                                        <i class="fas fa-tasks me-1"></i><?= htmlspecialchars($photo['task_description']) ?>
                                    </div>
                                    <?php if ($photo['caption']): ?>
                                    <div class="text-truncate opacity-75">
                                        <?= htmlspecialchars($photo['caption']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
                endif;
            endforeach;
            ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-camera text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                <h4 class="text-muted">Δεν υπάρχουν φωτογραφίες</h4>
                <p class="text-muted">Προσθέστε φωτογραφίες στις εργασίες του έργου για να τις δείτε εδώ.</p>
            </div>
        <?php endif; ?>
    </div>
    <!-- End Photos Tab -->

</div>
<!-- End Tab Content -->



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

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>
lightbox.option({
    'resizeDuration': 200,
    'wrapAround': true,
    'albumLabel': 'Φωτογραφία %1 από %2'
});
</script>