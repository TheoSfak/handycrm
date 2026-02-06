<?php
/**
 * View Technician Details
 */

$pageTitle = $title ?? $technician['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/technicians">
                    <i class="fas fa-user-hard-hat me-1"></i>Τεχνικοί
                </a>
            </li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($technician['name']) ?></li>
        </ol>
    </nav>

    <!-- Header Row with Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <?php if ($technician['role'] === 'technician'): ?>
                <i class="fas fa-user-tie text-primary me-2"></i>
            <?php else: ?>
                <i class="fas fa-user text-info me-2"></i>
            <?php endif; ?>
            <?= htmlspecialchars($technician['name']) ?>
            <span class="badge bg-<?= $technician['is_active'] ? 'success' : 'secondary' ?> ms-2">
                <?= $technician['is_active'] ? 'Ενεργός' : 'Ανενεργός' ?>
            </span>
        </h2>
        <div>
            <a href="<?= BASE_URL ?>/technicians/edit/<?= $technician['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Επεξεργασία
            </a>
            <?php if ($technician['is_active']): ?>
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                    <i class="fas fa-ban me-2"></i>Απενεργοποίηση
                </button>
            <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>/technicians/activate" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $technician['id'] ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Ενεργοποίηση
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Info & Statistics -->
        <div class="col-lg-5">
            <!-- Basic Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Πληροφορίες</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th width="40%">Ρόλος:</th>
                                <td>
                                    <span class="badge bg-<?= $technician['role'] === 'technician' ? 'primary' : 'info' ?>">
                                        <?= $technician['role'] === 'technician' ? 'Τεχνικός' : 'Βοηθός' ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Μεροκάματο:</th>
                                <td><strong class="text-success"><?= number_format($technician['hourly_rate'], 2) ?> €/ώρα</strong></td>
                            </tr>
                            <?php if ($technician['phone']): ?>
                                <tr>
                                    <th>Τηλέφωνο:</th>
                                    <td>
                                        <a href="tel:<?= htmlspecialchars($technician['phone']) ?>">
                                            <i class="fas fa-phone me-2"></i><?= htmlspecialchars($technician['phone']) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($technician['email']): ?>
                                <tr>
                                    <th>Email:</th>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($technician['email']) ?>">
                                            <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($technician['email']) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Εγγραφή:</th>
                                <td><?= date('d/m/Y H:i', strtotime($technician['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <th>Τελευταία Ενημέρωση:</th>
                                <td><?= date('d/m/Y H:i', strtotime($technician['updated_at'])) ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php if ($technician['notes']): ?>
                        <hr>
                        <div class="alert alert-info mb-0">
                            <small class="d-block mb-1"><strong><i class="fas fa-sticky-note me-1"></i>Σημειώσεις:</strong></small>
                            <?= nl2br(htmlspecialchars($technician['notes'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Στατιστικά</h5>
                </div>
                <div class="card-body">
                    <?php if ($statistics): ?>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h3 class="text-primary mb-0"><?= number_format($statistics['total_hours'], 1) ?></h3>
                                    <small class="text-muted">Ώρες</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h3 class="text-success mb-0"><?= number_format($statistics['total_earnings'], 2) ?>€</h3>
                                    <small class="text-muted">Κέρδη</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h3 class="text-info mb-0"><?= $statistics['project_count'] ?></h3>
                                    <small class="text-muted">Έργα</small>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Statistics by Date Range -->
                        <hr>
                        <form method="GET" action="<?= BASE_URL ?>/technicians/view/<?= $technician['id'] ?>" class="row g-2">
                            <div class="col-md-5">
                                <label class="form-label small">Από:</label>
                                <input type="date" class="form-control form-control-sm" name="from" value="<?= $_GET['from'] ?? '' ?>">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small">Έως:</label>
                                <input type="date" class="form-control form-control-sm" name="to" value="<?= $_GET['to'] ?? '' ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">&nbsp;</label>
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </form>

                        <?php if (isset($_GET['from']) || isset($_GET['to'])): ?>
                            <div class="text-center mt-2">
                                <a href="<?= BASE_URL ?>/technicians/view/<?= $technician['id'] ?>" class="btn btn-sm btn-link">
                                    <i class="fas fa-times me-1"></i>Καθαρισμός Φίλτρων
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">Δεν υπάρχουν στατιστικά ακόμα</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Work History -->
        <div class="col-lg-7">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Ιστορικό Εργασιών</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($workHistory)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Έργο</th>
                                        <th>Ημερομηνία</th>
                                        <th class="text-center">Ώρες</th>
                                        <th class="text-end">Κόστος</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($workHistory as $work): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= BASE_URL ?>/projects/view/<?= $work['project_id'] ?>">
                                                    <?= htmlspecialchars($work['project_name']) ?>
                                                </a>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($work['task_description']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($work['task_type'] === 'single_day'): ?>
                                                    <?= date('d/m/Y', strtotime($work['task_date'])) ?>
                                                <?php else: ?>
                                                    <?= date('d/m/Y', strtotime($work['date_from'])) ?> - 
                                                    <?= date('d/m/Y', strtotime($work['date_to'])) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?= number_format($work['hours_worked'], 1) ?>h</span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success"><?= number_format($work['subtotal'], 2) ?> €</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <th colspan="2">Σύνολο</th>
                                        <th class="text-center">
                                            <?= number_format(array_sum(array_column($workHistory, 'hours_worked')), 1) ?>h
                                        </th>
                                        <th class="text-end">
                                            <?= number_format(array_sum(array_column($workHistory, 'subtotal')), 2) ?> €
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Δεν υπάρχει ιστορικό εργασιών</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Confirmation Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Απενεργοποίηση Τεχνικού</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Είστε σίγουροι ότι θέλετε να απενεργοποιήσετε τον τεχνικό <strong><?= htmlspecialchars($technician['name']) ?></strong>;</p>
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Ο τεχνικός δεν θα διαγραφεί, αλλά δεν θα εμφανίζεται στις νέες εργασίες.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                <form method="POST" action="<?= BASE_URL ?>/technicians/delete" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $technician['id'] ?>">
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit" class="btn btn-warning">Απενεργοποίηση</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
