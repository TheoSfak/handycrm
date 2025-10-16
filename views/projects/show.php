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
        <button class="nav-link" id="labor-tab" data-bs-toggle="tab" data-bs-target="#labor" type="button" role="tab">
            <i class="fas fa-hard-hat me-2"></i>Ημερομίσθια
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" type="button" role="tab">
            <i class="fas fa-boxes me-2"></i>Υλικά
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="statistics-tab" data-bs-toggle="tab" data-bs-target="#statistics" type="button" role="tab">
            <i class="fas fa-chart-line me-2"></i>Στατιστικά
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

    <!-- Labor Tab -->
    <div class="tab-pane fade" id="labor" role="tabpanel">
        <?php if (!empty($laborEntries)): ?>
            <!-- Export Button -->
            <div class="mb-3 text-end">
                <button onclick="exportLaborToCSV()" class="btn btn-success">
                    <i class="fas fa-file-export me-2"></i>Export CSV
                </button>
            </div>
            <?php
            // Group labor entries by date/date range
            $groupedLabor = [];
            foreach ($laborEntries as $entry) {
                if ($entry['task_type'] === 'single_day') {
                    $dateKey = $entry['task_date'];
                    $dateLabel = date('d/m/Y', strtotime($entry['task_date']));
                } else {
                    $dateKey = $entry['date_from'] . '_to_' . $entry['date_to'];
                    $dateLabel = 'Από ' . date('d/m/Y', strtotime($entry['date_from'])) . ' έως ' . date('d/m/Y', strtotime($entry['date_to']));
                }
                
                if (!isset($groupedLabor[$dateKey])) {
                    $groupedLabor[$dateKey] = [
                        'label' => $dateLabel,
                        'type' => $entry['task_type'],
                        'entries' => []
                    ];
                }
                
                $groupedLabor[$dateKey]['entries'][] = $entry;
            }
            ?>
            
            <!-- Labor Cards in 3 Columns -->
            <div class="row g-3">
                <?php foreach ($groupedLabor as $dateKey => $group): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card shadow-sm h-100 border-0">
                            <div class="card-header <?= $group['type'] === 'single_day' ? 'bg-primary' : 'bg-info' ?> text-white">
                                <h6 class="mb-0">
                                    <?php if ($group['type'] === 'single_day'): ?>
                                        <i class="fas fa-calendar-day me-2"></i>
                                    <?php else: ?>
                                        <i class="fas fa-calendar-week me-2"></i>
                                    <?php endif; ?>
                                    <?= $group['label'] ?>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-hover mb-0">
                                    <tbody>
                                        <?php 
                                        $totalHours = 0;
                                        foreach ($group['entries'] as $entry): 
                                            $totalHours += $entry['hours'];
                                        ?>
                                            <tr>
                                                <td class="py-2">
                                                    <i class="fas fa-user-hard-hat text-muted me-2"></i>
                                                    <small><?= htmlspecialchars($entry['last_name'] . ' ' . $entry['first_name']) ?></small>
                                                </td>
                                                <td class="text-end py-2">
                                                    <span class="badge bg-info">
                                                        <?= number_format($entry['hours'], 1) ?>h
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong><i class="fas fa-clock me-1"></i>Σύνολο:</strong>
                                    <span class="badge bg-success fs-6">
                                        <?= number_format($totalHours, 1) ?> ώρες
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Grand Total -->
            <div class="card shadow-sm mt-4 mb-0 border-success" style="background-color: #d1e7dd;">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 text-success">
                                <i class="fas fa-calculator me-2"></i>Συνολικές Ώρες Έργου
                            </h5>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h3 class="mb-0">
                                <span class="badge bg-success fs-4">
                                    <?php 
                                    $grandTotal = array_sum(array_column($laborEntries, 'hours'));
                                    echo number_format($grandTotal, 1); 
                                    ?> ώρες
                                </span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-hard-hat fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                <h5 class="text-muted">Δεν υπάρχουν ημερομίσθια</h5>
                <p class="text-muted">Προσθέστε εργατικά στις εργασίες του έργου για να τα δείτε εδώ.</p>
                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Δημιουργία Εργασίας
                </a>
            </div>
        <?php endif; ?>
    </div>
    <!-- End Labor Tab -->

    <!-- Materials Tab -->
    <div class="tab-pane fade" id="materials" role="tabpanel">
        <!-- Filters Card -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Φίλτρα Ημερομηνιών</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" id="materialsFilterForm">
                    <input type="hidden" name="slug" value="<?= htmlspecialchars($project['slug']) ?>">
                    <input type="hidden" name="tab" value="materials">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="materials_date_from" class="form-label">Από Ημερομηνία</label>
                            <input type="date" class="form-control" id="materials_date_from" name="materials_date_from" 
                                   value="<?= $_GET['materials_date_from'] ?? '' ?>">
                        </div>
                        <div class="col-md-5">
                            <label for="materials_date_to" class="form-label">Έως Ημερομηνία</label>
                            <input type="date" class="form-control" id="materials_date_to" name="materials_date_to" 
                                   value="<?= $_GET['materials_date_to'] ?? '' ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Αναζήτηση
                            </button>
                        </div>
                    </div>
                    <?php if (!empty($_GET['materials_date_from']) || !empty($_GET['materials_date_to'])): ?>
                    <div class="mt-3">
                        <a href="?slug=<?= htmlspecialchars($project['slug']) ?>&tab=materials" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-2"></i>Καθαρισμός Φίλτρων
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (!empty($materialsSummary)): ?>
            <!-- Export Button -->
            <div class="mb-3 text-end">
                <button onclick="exportMaterialsToCSV()" class="btn btn-success">
                    <i class="fas fa-file-export me-2"></i>Export CSV
                </button>
            </div>

            <!-- Date Range Info -->
            <?php if (!empty($_GET['materials_date_from']) || !empty($_GET['materials_date_to'])): ?>
            <div class="alert alert-info">
                <i class="fas fa-calendar-alt me-2"></i>
                <strong>Περίοδος:</strong> 
                <?php if (!empty($_GET['materials_date_from'])): ?>
                    Από <?= date('d/m/Y', strtotime($_GET['materials_date_from'])) ?>
                <?php endif; ?>
                <?php if (!empty($_GET['materials_date_to'])): ?>
                    έως <?= date('d/m/Y', strtotime($_GET['materials_date_to'])) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Materials Summary Table -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Συγκεντρωτικά Υλικά</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="materialsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Υλικό</th>
                                    <th class="text-center">Μονάδα Μέτρησης</th>
                                    <th class="text-end">Συνολική Ποσότητα</th>
                                    <th class="text-end">Μέση Τιμή</th>
                                    <th class="text-end">Συνολική Αξία</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grandTotalValue = 0;
                                foreach ($materialsSummary as $material): 
                                    $totalValue = $material['total_quantity'] * $material['avg_price'];
                                    $grandTotalValue += $totalValue;
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($material['name']) ?></strong>
                                            <?php if (!empty($material['category'])): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-tag fa-xs"></i> <?= htmlspecialchars($material['category']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?= htmlspecialchars($material['unit']) ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong><?= number_format($material['total_quantity'], 2) ?></strong>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format($material['avg_price'], 2) ?> €
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success"><?= number_format($totalValue, 2) ?> €</strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="table-success">
                                    <td colspan="4" class="text-end"><strong><i class="fas fa-calculator me-2"></i>ΣΥΝΟΛΟ:</strong></td>
                                    <td class="text-end"><strong class="fs-5 text-success"><?= number_format($grandTotalValue, 2) ?> €</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Materials Detail (by task) -->
            <?php if (!empty($materialsDetail)): ?>
            <div class="card shadow-sm mt-4 border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Λεπτομέρειες ανά Εργασία</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="materialsAccordion">
                        <?php foreach ($materialsDetail as $index => $task): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                    <i class="fas fa-calendar-day me-2 text-primary"></i>
                                    <strong><?= date('d/m/Y', strtotime($task['task_date'])) ?></strong>
                                    <span class="ms-3 text-muted"><?= htmlspecialchars($task['description']) ?></span>
                                    <span class="badge bg-primary ms-auto me-3"><?= count($task['materials']) ?> υλικά</span>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" 
                                 data-bs-parent="#materialsAccordion">
                                <div class="accordion-body p-0">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Υλικό</th>
                                                <th class="text-center">Μονάδα</th>
                                                <th class="text-end">Ποσότητα</th>
                                                <th class="text-end">Τιμή</th>
                                                <th class="text-end">Σύνολο</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($task['materials'] as $mat): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($mat['name']) ?></td>
                                                <td class="text-center"><small><?= htmlspecialchars($mat['unit']) ?></small></td>
                                                <td class="text-end"><?= number_format($mat['quantity'], 2) ?></td>
                                                <td class="text-end"><?= number_format($mat['unit_price'], 2) ?> €</td>
                                                <td class="text-end"><strong><?= number_format($mat['subtotal'], 2) ?> €</strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-boxes fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                <h5 class="text-muted">Δεν υπάρχουν υλικά</h5>
                <p class="text-muted">Προσθέστε υλικά στις εργασίες του έργου για να τα δείτε εδώ.</p>
                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Δημιουργία Εργασίας
                </a>
            </div>
        <?php endif; ?>
    </div>
    <!-- End Materials Tab -->

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

<!-- Chart.js for Statistics Tab -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let statisticsChartsInitialized = false;

// Initialize charts when Statistics tab is shown
document.getElementById('statistics-tab')?.addEventListener('shown.bs.tab', function (e) {
    if (statisticsChartsInitialized) return; // Already initialized
    
    statisticsChartsInitialized = true;
    
    <?php if (!empty($statistics) && $statistics['total_tasks'] > 0): ?>
    // Cost Pie Chart
    const ctxPie = document.getElementById('costPieChart');
    if (ctxPie) {
        new Chart(ctxPie.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Υλικά', 'Εργατικά'],
                datasets: [{
                    data: [<?= $statistics['materials_total'] ?? 0 ?>, <?= $statistics['labor_total'] ?? 0 ?>],
                    backgroundColor: ['#ffc107', '#17a2b8'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Weekday Bar Chart
    const ctxWeekday = document.getElementById('weekdayChart');
    if (ctxWeekday) {
        const greekDays = {
            'Monday': 'Δευτέρα',
            'Tuesday': 'Τρίτη',
            'Wednesday': 'Τετάρτη',
            'Thursday': 'Πέμπτη',
            'Friday': 'Παρασκευή',
            'Saturday': 'Σάββατο',
            'Sunday': 'Κυριακή'
        };

        new Chart(ctxWeekday.getContext('2d'), {
            type: 'bar',
            data: {
                labels: Object.keys(greekDays).map(key => greekDays[key]),
                datasets: [{
                    label: 'Εργασίες',
                    data: [
                        <?= $statistics['tasks_by_weekday']['Monday'] ?? 0 ?>,
                        <?= $statistics['tasks_by_weekday']['Tuesday'] ?? 0 ?>,
                        <?= $statistics['tasks_by_weekday']['Wednesday'] ?? 0 ?>,
                        <?= $statistics['tasks_by_weekday']['Thursday'] ?? 0 ?>,
                        <?= $statistics['tasks_by_weekday']['Friday'] ?? 0 ?>,
                        <?= $statistics['tasks_by_weekday']['Saturday'] ?? 0 ?>,
                        <?= $statistics['tasks_by_weekday']['Sunday'] ?? 0 ?>
                    ],
                    backgroundColor: [
                        '#007bff', '#007bff', '#007bff', '#007bff', '#007bff', '#ffc107', '#ffc107'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>

<script>
// Export Labor Data to CSV
function exportLaborToCSV() {
    try {
        const projectName = <?= json_encode($project['title'] ?? 'Project') ?>;
        const laborData = <?= json_encode($laborEntries ?? []) ?>;
        
        if (!laborData || laborData.length === 0) {
            alert('Δεν υπάρχουν δεδομένα για export');
            return;
        }
    
    // CSV Header
    let csv = '\uFEFF'; // BOM for proper Greek character encoding
    csv += 'Ημερομηνία,Τεχνικός,Ώρες\n';
    
    // Add data rows
    laborData.forEach(entry => {
        let dateStr = '';
        if (entry.task_type === 'single_day') {
            const date = new Date(entry.task_date);
            dateStr = date.toLocaleDateString('el-GR');
        } else {
            const dateFrom = new Date(entry.date_from);
            const dateTo = new Date(entry.date_to);
            dateStr = 'Από ' + dateFrom.toLocaleDateString('el-GR') + ' έως ' + dateTo.toLocaleDateString('el-GR');
        }
        
        const technicianName = (entry.last_name + ' ' + entry.first_name).trim();
        const hours = parseFloat(entry.hours).toFixed(1);
        
        csv += `"${dateStr}","${technicianName}",${hours}\n`;
    });
    
    // Add total
    const totalHours = laborData.reduce((sum, entry) => sum + parseFloat(entry.hours), 0);
    csv += `\n"ΣΥΝΟΛΟ","",${totalHours.toFixed(1)}\n`;
    
    // Create download link
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    const filename = projectName + ' - Ημερομίσθια.csv';
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    } catch (error) {
        console.error('Export error:', error);
        alert('Σφάλμα κατά το export: ' + error.message);
    }
}

// Export Materials Data to CSV
function exportMaterialsToCSV() {
    try {
        const projectName = <?= json_encode($project['title'] ?? 'Project') ?>;
        const materialsData = <?= json_encode($materialsSummary ?? []) ?>;
        
        if (!materialsData || materialsData.length === 0) {
            alert('Δεν υπάρχουν δεδομένα για export');
            return;
        }
        
        // CSV Header
        let csv = '\uFEFF'; // BOM for proper Greek character encoding
        csv += 'Υλικό,Μονάδα,Ποσότητα,Μέση Τιμή,Συνολική Αξία,Κατηγορία\n';
        
        let grandTotal = 0;
        
        // Add data rows
        materialsData.forEach(material => {
            const name = material.name || '';
            const unit = material.unit || '';
            const quantity = parseFloat(material.total_quantity).toFixed(2);
            const avgPrice = parseFloat(material.avg_price).toFixed(2);
            const totalValue = (parseFloat(material.total_quantity) * parseFloat(material.avg_price)).toFixed(2);
            const category = material.category || '-';
            
            grandTotal += parseFloat(totalValue);
            
            csv += `"${name}","${unit}",${quantity},${avgPrice},${totalValue},"${category}"\n`;
        });
        
        // Add grand total
        csv += `\n"ΓΕΝΙΚΟ ΣΥΝΟΛΟ","","","",${grandTotal.toFixed(2)},""\n`;
        
        // Create download link
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        const filename = projectName + ' - Υλικά.csv';
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Export error:', error);
        alert('Σφάλμα κατά το export: ' + error.message);
    }
}

// Auto-activate materials tab if filter is applied
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    
    if (tab === 'materials') {
        const materialsTab = document.getElementById('materials-tab');
        if (materialsTab) {
            const bsTab = new bootstrap.Tab(materialsTab);
            bsTab.show();
        }
    }
});
</script>