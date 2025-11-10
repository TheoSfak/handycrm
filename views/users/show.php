<?php
require_once 'views/includes/header.php';
?>

<div class="container-fluid mt-4">
    <!-- User Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-user-circle me-2"></i>
                        <?= htmlspecialchars($viewUser['first_name'] . ' ' . $viewUser['last_name']) ?>
                    </h2>
                    <p class="text-muted mb-0">
                        <?php
                        $roleName = $viewUser['role_name'] ?? 'technician';
                        $roleDisplay = $viewUser['role_display_name'] ?? ucfirst($roleName);
                        $roleColors = [
                            'admin' => 'danger',
                            'supervisor' => 'warning',
                            'technician' => 'primary',
                            'assistant' => 'info',
                            'maintenance_technician' => 'success',
                            'maintenance_tech' => 'success'
                        ];
                        $roleColor = $roleColors[$roleName] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $roleColor ?>">
                            <?= htmlspecialchars($roleDisplay) ?>
                        </span>
                        <span class="ms-2"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($viewUser['email']) ?></span>
                        <?php if (!empty($viewUser['phone'])): ?>
                            <span class="ms-2"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($viewUser['phone']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>/users/edit/<?= $viewUser['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i><?= __('users.edit_user') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i><?= __('common.back') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="min-width: 180px;">
                            <h6 class="text-white-50 mb-1">Συνολικά Κέρδη</h6>
                            <h3 class="mb-0" style="white-space: nowrap; min-width: 150px;"><?= formatCurrency($totalEarned) ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="min-width: 180px;">
                            <h6 class="text-white-50 mb-1">Πληρωμένα</h6>
                            <h3 class="mb-0" style="white-space: nowrap; min-width: 150px;"><?= formatCurrency($totalPaid) ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="min-width: 180px;">
                            <h6 class="text-white-50 mb-1">Απλήρωτα</h6>
                            <h3 class="mb-0" style="white-space: nowrap; min-width: 150px;"><?= formatCurrency($totalUnpaid) ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Στοιχεία Χρήστη</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 140px;"><i class="fas fa-user me-2"></i>Όνομα:</td>
                                <td><strong><?= htmlspecialchars($viewUser['first_name']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="width: 140px;"><i class="fas fa-user me-2"></i>Επώνυμο:</td>
                                <td><strong><?= htmlspecialchars($viewUser['last_name']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="width: 140px;"><i class="fas fa-envelope me-2"></i>Email:</td>
                                <td><strong><?= htmlspecialchars($viewUser['email']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="width: 140px;"><i class="fas fa-phone me-2"></i>Τηλέφωνο:</td>
                                <td><strong><?= htmlspecialchars($viewUser['phone'] ?? '-') ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="width: 140px;"><i class="fas fa-user-tag me-2"></i>Ρόλος:</td>
                                <td>
                                    <span class="badge bg-<?= $roleColor ?>">
                                        <?= htmlspecialchars($roleDisplay) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="width: 140px;"><i class="fas fa-euro-sign me-2"></i>Ωρομίσθιο:</td>
                                <td><strong style="white-space: nowrap;"><?= formatCurrency($viewUser['hourly_rate'] ?? 0) ?>/h</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="width: 140px; white-space: nowrap;"><i class="fas fa-calendar me-2"></i>Δημιουργήθηκε:</td>
                                <td><strong style="white-space: nowrap;"><?= formatDate($viewUser['created_at'], true) ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Ιστορικό Ημερομισθίων</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($paymentHistory)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>Δεν υπάρχει ιστορικό πληρωμών
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ημερομηνία</th>
                                        <th>Πελάτης</th>
                                        <th>Έργο</th>
                                        <th>Εργασία</th>
                                        <th class="text-center">Ώρες</th>
                                        <th class="text-end">Τιμή</th>
                                        <th class="text-end">Ποσό</th>
                                        <th class="text-center">Κατάσταση</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentHistory as $entry): 
                                        $workDate = $entry['task_date'] ?: $entry['date_from'];
                                        $isPaid = !empty($entry['paid_at']);
                                        $rowClass = $isPaid ? 'table-success' : '';
                                        
                                        // Customer name
                                        $customerName = $entry['customer_type'] === 'company' && !empty($entry['customer_company_name']) 
                                            ? $entry['customer_company_name'] 
                                            : $entry['customer_first_name'] . ' ' . $entry['customer_last_name'];
                                    ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td>
                                                <small><?= formatDate($workDate) ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($customerName) ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($entry['project_title']) ?></small>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars($entry['task_description']) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?= number_format($entry['hours_worked'], 2) ?>h</span>
                                            </td>
                                            <td class="text-end">
                                                <small><?= formatCurrency($entry['hourly_rate']) ?></small>
                                            </td>
                                            <td class="text-end">
                                                <strong><?= formatCurrency($entry['subtotal']) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($isPaid): ?>
                                                    <span class="badge bg-success" title="Πληρώθηκε">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted" style="font-size: 0.65rem;">
                                                        <?= formatDate($entry['paid_at'], true) ?>
                                                        <?php if (!empty($entry['paid_by_name'])): ?>
                                                            <br><?= htmlspecialchars($entry['paid_by_name']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Απλήρωτο
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Σύνολο:</strong></td>
                                        <td class="text-end" style="white-space: nowrap; min-width: 100px;"><strong><?= formatCurrency($totalEarned) ?></strong></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end text-success"><strong>Πληρωμένα:</strong></td>
                                        <td class="text-end text-success" style="white-space: nowrap; min-width: 100px;"><strong><?= formatCurrency($totalPaid) ?></strong></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end text-warning"><strong>Απλήρωτα:</strong></td>
                                        <td class="text-end text-warning" style="white-space: nowrap; min-width: 100px;"><strong><?= formatCurrency($totalUnpaid) ?></strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <?php if (count($paymentHistory) >= 100): ?>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>Εμφανίζονται οι 100 πιο πρόσφατες εγγραφές
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/includes/footer.php'; ?>
