<?php
require_once 'views/includes/header.php';
?>

<div class="container-fluid mt-4">
    <!-- Page Header with gradient background -->
    <div class="card shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
        <div class="card-body text-white py-4">
            <div class="text-center mb-3">
                <h2 class="mb-2">
                    <i class="fas fa-money-bill-wave me-3"></i>
                    Πληρωμές Εργαζομένων
                </h2>
                <p class="mb-0 opacity-75">Διαχείριση εβδομαδιαίων πληρωμών εργαζομένων</p>
            </div>
        </div>
    </div>

    <!-- Filters Card with colored header -->
    <div class="card shadow-sm mb-4">
        <div class="card-header border-bottom" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h5 class="mb-0 text-white">
                <i class="fas fa-filter me-2"></i>Κατάσταση Πληρωμής
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/payments" class="row g-3">
                <div class="col-md-3">
                    <label for="technician_id" class="form-label">
                        <i class="fas fa-user me-1"></i><?= __('payments.select_technician') ?>
                    </label>
                    <select class="form-select" id="technician_id" name="technician_id">
                        <option value=""><?= __('payments.all_technicians') ?></option>
                        <?php foreach ($technicians as $tech): ?>
                            <option value="<?= $tech['id'] ?>" <?= $selectedTechnician == $tech['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tech['name']) ?> - <?= formatCurrency($tech['hourly_rate']) ?>/h
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="week_start" class="form-label">
                        <i class="fas fa-calendar me-1"></i><?= __('payments.week_start') ?>
                    </label>
                    <input type="date" class="form-control" id="week_start" name="week_start" 
                           value="<?= $weekStart ?>" required>
                </div>

                <div class="col-md-2">
                    <label for="week_end" class="form-label">
                        <i class="fas fa-calendar me-1"></i><?= __('payments.week_end') ?>
                    </label>
                    <input type="date" class="form-control" id="week_end" name="week_end" 
                           value="<?= $weekEnd ?>" required>
                </div>
                
                <!-- Quick Date Presets -->
                <div class="col-12 mb-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('current-week')">
                            <i class="fas fa-calendar-week me-1"></i>Τρέχουσα Εβδομάδα
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('last-week')">
                            <i class="fas fa-calendar-minus me-1"></i>Προηγούμενη Εβδομάδα
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('current-month')">
                            <i class="fas fa-calendar-alt me-1"></i>Τρέχων Μήνας
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="setDateRange('last-month')">
                            <i class="fas fa-calendar-times me-1"></i>Προηγούμενος Μήνας
                        </button>
                    </div>
                </div>

                <div class="col-md-2">
                    <label for="paid_status" class="form-label">
                        <i class="fas fa-filter me-1"></i><?= __('payments.paid_status') ?>
                    </label>
                    <select class="form-select" id="paid_status" name="paid_status">
                        <option value="all" <?= $paidStatus === 'all' ? 'selected' : '' ?>>
                            <?= __('payments.all_entries') ?>
                        </option>
                        <option value="unpaid" <?= $paidStatus === 'unpaid' ? 'selected' : '' ?>>
                            <?= __('payments.unpaid_only') ?>
                        </option>
                        <option value="paid" <?= $paidStatus === 'paid' ? 'selected' : '' ?>>
                            <?= __('payments.paid_only') ?>
                        </option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search me-1"></i><?= __('payments.filter') ?>
                    </button>
                    <a href="<?= BASE_URL ?>/payments/export?date_from=<?= $weekStart ?>&date_to=<?= $weekEnd ?>&paid_status=<?= $paidStatus ?><?= $selectedTechnician ? '&technician_id=' . $selectedTechnician : '' ?>" 
                       class="btn btn-success flex-fill" title="Export Excel">
                        <i class="fas fa-file-excel"></i>
                    </a>
                    <a href="<?= BASE_URL ?>/payments/report?date_from=<?= $weekStart ?>&date_to=<?= $weekEnd ?>&paid_status=<?= $paidStatus ?><?= $selectedTechnician ? '&technician_id=' . $selectedTechnician : '' ?>" 
                       class="btn btn-danger flex-fill" target="_blank" title="<?= __('payments.generate_pdf') ?>">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <?php if (empty($weeklyData)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <?= __('payments.no_data_found') ?>. <?= __('payments.try_different_filters') ?>.
        </div>
    <?php else: ?>
        <!-- Summary Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Συνολική Μισθοδοσία</h6>
                                <h3 class="mb-0 text-primary"><?= formatCurrency($grandTotalAmount) ?></h3>
                                <small class="text-muted"><?= number_format($grandTotalHours, 2) ?>h</small>
                            </div>
                            <div class="fs-1 text-primary opacity-50">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Πληρωμένα</h6>
                                <h3 class="mb-0 text-success"><?= formatCurrency($grandTotalPaid) ?></h3>
                                <small class="text-muted">
                                    <?= number_format($grandTotalAmount > 0 ? ($grandTotalPaid / $grandTotalAmount) * 100 : 0, 1) ?>%
                                </small>
                            </div>
                            <div class="fs-1 text-success opacity-50">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Απλήρωτα</h6>
                                <h3 class="mb-0 text-warning"><?= formatCurrency($grandTotalUnpaid) ?></h3>
                                <small class="text-muted">
                                    <?= number_format($grandTotalAmount > 0 ? ($grandTotalUnpaid / $grandTotalAmount) * 100 : 0, 1) ?>%
                                </small>
                            </div>
                            <div class="fs-1 text-warning opacity-50">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-info">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Πρόοδος Πληρωμής</h6>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $paymentPercentage ?>%;" 
                                 aria-valuenow="<?= $paymentPercentage ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                                <strong><?= number_format($paymentPercentage, 1) ?>%</strong>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i><?= $totalTechnicians ?> εργαζόμενοι
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bulk Actions Toolbar -->
        <div class="row mb-3">
            <div class="col-md-6">
                <h5><?= __('payments.showing_week') ?>: <?= formatDate($weekStart) ?> - <?= formatDate($weekEnd) ?></h5>
            </div>
            <div class="col-md-6 text-end">
                <?php if ($grandTotalUnpaid > 0): ?>
                    <button type="button" class="btn btn-warning" id="markAllPaidBtn" 
                            data-week-start="<?= $weekStart ?>" 
                            data-week-end="<?= $weekEnd ?>">
                        <i class="fas fa-check-double me-2"></i>Επισήμανση Όλων ως Πληρωμένα
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php foreach ($weeklyData as $tech): 
            // Calculate payment percentage
            $totalAmount = $tech['filtered_total_amount'] ?? $tech['total_amount'];
            $paidAmount = 0;
            $unpaidAmount = 0;
            
            foreach ($tech['entries'] as $entry) {
                if (!empty($entry['paid_at'])) {
                    $paidAmount += $entry['subtotal'];
                } else {
                    $unpaidAmount += $entry['subtotal'];
                }
            }
            
            $paymentPercentage = $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0;
            
            // Role labels in Greek
            $roleLabels = [
                'technician' => 'ΤΕΧΝΙΚΟΣ',
                'assistant' => 'ΒΟΗΘΟΣ ΤΕΧΝΙΚΟΥ',
                'supervisor' => 'ΥΠΕΥΘΥΝΟΣ ΣΥΝΕΡΓΕΙΟΥ',
                'admin' => 'ΔΙΑΧΕΙΡΙΣΤΗΣ'
            ];
            $roleLabel = $roleLabels[$tech['role']] ?? strtoupper($tech['role']);
        ?>
            <div class="card shadow-sm mb-4 technician-card" data-technician-id="<?= $tech['technician_id'] ?>">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <h5 class="mb-0">
                                <i class="fas fa-user-hard-hat me-2"></i>
                                <?= htmlspecialchars($tech['technician_name']) ?>
                                <span class="badge bg-light text-dark ms-2" style="font-size: 0.7rem;">
                                    <?= $roleLabel ?>
                                </span>
                            </h5>
                            <small><?= formatCurrency($tech['hourly_rate']) ?> / <?= __('payments.hours') ?></small>
                        </div>
                        <div class="col-md-9">
                            <div class="row text-end align-items-center">
                                <div class="col-3">
                                    <small class="d-block"><?= __('payments.total_hours') ?></small>
                                    <strong><?= number_format($tech['filtered_total_hours'] ?? $tech['total_hours'], 2) ?>h</strong>
                                </div>
                                <div class="col-3">
                                    <small class="d-block"><?= __('payments.total_amount') ?></small>
                                    <strong><?= formatCurrency($totalAmount) ?></strong>
                                </div>
                                <div class="col-3">
                                    <small class="d-block">Πληρωμένα / Απλήρωτα</small>
                                    <strong class="text-success"><?= formatCurrency($paidAmount) ?></strong>
                                    <span class="mx-1">/</span>
                                    <strong class="text-danger" data-unpaid-amount="<?= $unpaidAmount ?>"><?= formatCurrency($unpaidAmount) ?></strong>
                                </div>
                                <div class="col-3">
                                    <small class="d-block">Ποσοστό Πληρωμής</small>
                                    <div class="progress mt-1" style="height: 20px;" data-bs-toggle="tooltip" 
                                         title="<?= number_format($paymentPercentage, 1) ?>% πληρωμένα">
                                        <div class="progress-bar <?= $paymentPercentage == 100 ? 'bg-success' : ($paymentPercentage > 50 ? 'bg-info' : 'bg-warning') ?>" 
                                             role="progressbar" 
                                             style="width: <?= $paymentPercentage ?>%"
                                             aria-valuenow="<?= $paymentPercentage ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <strong><?= number_format($paymentPercentage, 0) ?>%</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <?php if (empty($tech['entries'])): ?>
                        <div class="alert alert-info mb-0">
                            <?= __('payments.no_entries_found') ?>
                        </div>
                    <?php else: ?>
                        <!-- Selection Controls -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary select-all-btn">
                                    <i class="fas fa-check-square me-1"></i><?= __('payments.select_all') ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary deselect-all-btn">
                                    <i class="fas fa-square me-1"></i><?= __('payments.deselect_all') ?>
                                </button>
                                <span class="ms-3 text-muted">
                                    <span class="selected-count">0</span> <?= __('payments.selected') ?>
                                </span>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-success mark-selected-paid-btn">
                                    <i class="fas fa-check me-1"></i><?= __('payments.mark_selected_paid') ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning mark-selected-unpaid-btn">
                                    <i class="fas fa-undo me-1"></i><?= __('payments.mark_selected_unpaid') ?>
                                </button>
                            </div>
                        </div>

                        <!-- Labor Entries Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input select-all-checkbox">
                                        </th>
                                        <th><?= __('payments.date') ?></th>
                                        <th><?= __('payments.project') ?></th>
                                        <th><?= __('payments.task') ?></th>
                                        <th class="text-center"><?= __('payments.hours') ?></th>
                                        <th class="text-end"><?= __('payments.rate') ?></th>
                                        <th class="text-end"><?= __('payments.amount') ?></th>
                                        <th class="text-center"><?= __('payments.paid') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tech['entries'] as $entry): 
                                        $workDate = $entry['task_date'] ?: $entry['date_from'];
                                        $isPaid = !empty($entry['paid_at']);
                                        $rowClass = $isPaid ? 'table-success' : 'table-warning';
                                        $amountClass = $isPaid ? 'text-success' : 'text-danger';
                                    ?>
                                        <tr class="labor-entry-row <?= $rowClass ?>" 
                                            data-entry-id="<?= $entry['id'] ?>"
                                            data-is-paid="<?= $isPaid ? '1' : '0' ?>">
                                            <td>
                                                <input type="checkbox" class="form-check-input entry-checkbox" 
                                                       value="<?= $entry['id'] ?>">
                                            </td>
                                            <td><?= formatDate($workDate) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($entry['project_title']) ?></strong>
                                            </td>
                                            <td>
                                                <span data-bs-toggle="tooltip" 
                                                      data-bs-html="true"
                                                      title="<strong>Έργο:</strong> <?= htmlspecialchars($entry['project_title']) ?><br><strong>Εργασία:</strong> <?= htmlspecialchars($entry['task_description']) ?><br><strong>Ημερομηνία:</strong> <?= formatDate($workDate) ?>">
                                                    <?= htmlspecialchars($entry['task_description']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info" data-bs-toggle="tooltip" 
                                                      title="<?= number_format($entry['hours_worked'], 2) ?> ώρες × <?= formatCurrency($entry['hourly_rate']) ?>/ώρα">
                                                    <?= number_format($entry['hours_worked'], 2) ?>h
                                                </span>
                                            </td>
                                            <td class="text-end"><?= formatCurrency($entry['hourly_rate']) ?></td>
                                            <td class="text-end">
                                                <strong class="<?= $amountClass ?>"><?= formatCurrency($entry['subtotal']) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($isPaid): ?>
                                                    <span class="badge bg-success" data-bs-toggle="tooltip"
                                                          data-bs-html="true"
                                                          title="<strong>Πληρώθηκε:</strong> <?= formatDate($entry['paid_at'], true) ?><?= !empty($entry['paid_by_name']) ? '<br><strong>Από:</strong> ' . htmlspecialchars($entry['paid_by_name']) : '' ?>">
                                                        <i class="fas fa-check-circle"></i>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted" style="font-size: 0.7rem;">
                                                        <?= formatDate($entry['paid_at'], true) ?>
                                                        <?php if (!empty($entry['paid_by_name'])): ?>
                                                            <br><?= htmlspecialchars($entry['paid_by_name']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-minus-circle"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong><?= __('payments.total') ?>:</strong></td>
                                        <td class="text-center">
                                            <strong><?= number_format($tech['filtered_total_hours'] ?? $tech['total_hours'], 2) ?>h</strong>
                                        </td>
                                        <td></td>
                                        <td class="text-end">
                                            <strong><?= formatCurrency($tech['filtered_total_amount'] ?? $tech['total_amount']) ?></strong>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Week Actions -->
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-lg btn-success mark-week-paid-btn"
                                    data-technician-id="<?= $tech['technician_id'] ?>"
                                    data-week-start="<?= $weekStart ?>"
                                    data-week-end="<?= $weekEnd ?>">
                                <i class="fas fa-check-double me-2"></i><?= __('payments.mark_week_paid') ?>
                            </button>
                            <button type="button" class="btn btn-lg btn-outline-warning mark-week-unpaid-btn"
                                    data-technician-id="<?= $tech['technician_id'] ?>"
                                    data-week-start="<?= $weekStart ?>"
                                    data-week-end="<?= $weekEnd ?>">
                                <i class="fas fa-undo me-2"></i><?= __('payments.mark_week_unpaid') ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Pagination -->
    <?php if (isset($totalPages) && $totalPages > 1): ?>
        <nav aria-label="Pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Previous button -->
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?technician_id=<?= $selectedTechnician ?? '' ?>&week_start=<?= $weekStart ?>&week_end=<?= $weekEnd ?>&paid_status=<?= $paidStatus ?>&page=<?= $currentPage - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <?php
                // Calculate page range to show
                $range = 2; // Show 2 pages before and after current
                $startPage = max(1, $currentPage - $range);
                $endPage = min($totalPages, $currentPage + $range);
                
                // Show first page if not in range
                if ($startPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?technician_id=<?= $selectedTechnician ?? '' ?>&week_start=<?= $weekStart ?>&week_end=<?= $weekEnd ?>&paid_status=<?= $paidStatus ?>&page=1">1</a>
                    </li>
                    <?php if ($startPage > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Page numbers -->
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?technician_id=<?= $selectedTechnician ?? '' ?>&week_start=<?= $weekStart ?>&week_end=<?= $weekEnd ?>&paid_status=<?= $paidStatus ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <!-- Show last page if not in range -->
                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?technician_id=<?= $selectedTechnician ?? '' ?>&week_start=<?= $weekStart ?>&week_end=<?= $weekEnd ?>&paid_status=<?= $paidStatus ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>
                
                <!-- Next button -->
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?technician_id=<?= $selectedTechnician ?? '' ?>&week_start=<?= $weekStart ?>&week_end=<?= $weekEnd ?>&paid_status=<?= $paidStatus ?>&page=<?= $currentPage + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Results info -->
        <div class="text-center text-muted mb-4">
            <?php 
            $startItem = ($currentPage - 1) * $itemsPerPage + 1;
            $endItem = min($currentPage * $itemsPerPage, $totalTechnicians);
            ?>
            Εμφάνιση <?= $startItem ?> - <?= $endItem ?> από <?= $totalTechnicians ?> εργαζόμενους
        </div>
    <?php endif; ?>
</div>

<!-- Bulk Payment Confirmation Modal -->
<div class="modal fade" id="bulkPaymentModal" tabindex="-1" aria-labelledby="bulkPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="bulkPaymentModalLabel">
                    <i class="fas fa-check-double me-2"></i>Επιβεβαίωση Μαζικής Πληρωμής
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Θα επισημανθούν ως πληρωμένες όλες οι <strong>απλήρωτες</strong> εγγραφές για την επιλεγμένη περίοδο.
                </div>
                
                <h6 class="mb-3">Τεχνικοί προς πληρωμή:</h6>
                <div id="bulkPaymentList" class="mb-3">
                    <!-- Will be populated via JavaScript -->
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Συνολικό Ποσό Πληρωμής:</strong>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4 class="text-warning mb-0" id="bulkTotalAmount">0.00€</h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Ακύρωση
                </button>
                <button type="button" class="btn btn-warning" id="confirmBulkPayment">
                    <i class="fas fa-check-double me-2"></i>Επιβεβαίωση Πληρωμής
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Quick Date Range Presets
function setDateRange(preset) {
    const weekStartInput = document.getElementById('week_start');
    const weekEndInput = document.getElementById('week_end');
    const today = new Date();
    let startDate, endDate;
    
    switch(preset) {
        case 'current-week':
            // Get Monday of current week
            const currentDay = today.getDay();
            const diff = currentDay === 0 ? -6 : 1 - currentDay; // Sunday = 0, so go back 6 days
            startDate = new Date(today);
            startDate.setDate(today.getDate() + diff);
            // Get Sunday of current week
            endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);
            break;
            
        case 'last-week':
            // Get Monday of last week
            const lastWeekDay = today.getDay();
            const lastWeekDiff = lastWeekDay === 0 ? -13 : -6 - lastWeekDay;
            startDate = new Date(today);
            startDate.setDate(today.getDate() + lastWeekDiff);
            // Get Sunday of last week
            endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);
            break;
            
        case 'current-month':
            // First day of current month
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            // Last day of current month
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
            
        case 'last-month':
            // First day of last month
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            // Last day of last month
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
    }
    
    // Format dates as dd/mm/yyyy for Greek format input fields
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${day}/${month}/${year}`;
    };
    
    weekStartInput.value = formatDate(startDate);
    weekEndInput.value = formatDate(endDate);
}

document.addEventListener('DOMContentLoaded', function() {
    // Update selected count for a card
    function updateSelectedCount(card) {
        const checkedCount = card.querySelectorAll('.entry-checkbox:checked').length;
        const countSpan = card.querySelector('.selected-count');
        if (countSpan) {
            countSpan.textContent = checkedCount;
        }
    }

    // Get all technician cards
    const cards = document.querySelectorAll('.technician-card');
    
    cards.forEach(card => {
        // Select all checkbox in header
        const selectAllCheckbox = card.querySelector('.select-all-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const entryCheckboxes = card.querySelectorAll('.entry-checkbox');
                entryCheckboxes.forEach(cb => cb.checked = this.checked);
                updateSelectedCount(card);
            });
        }

        // Select all button
        const selectAllBtn = card.querySelector('.select-all-btn');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                const entryCheckboxes = card.querySelectorAll('.entry-checkbox');
                entryCheckboxes.forEach(cb => cb.checked = true);
                if (selectAllCheckbox) selectAllCheckbox.checked = true;
                updateSelectedCount(card);
            });
        }

        // Deselect all button
        const deselectAllBtn = card.querySelector('.deselect-all-btn');
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', function() {
                const entryCheckboxes = card.querySelectorAll('.entry-checkbox');
                entryCheckboxes.forEach(cb => cb.checked = false);
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                updateSelectedCount(card);
            });
        }

        // Individual checkbox change
        const entryCheckboxes = card.querySelectorAll('.entry-checkbox');
        entryCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount(card);
                
                // Update select-all checkbox state
                const total = card.querySelectorAll('.entry-checkbox').length;
                const checked = card.querySelectorAll('.entry-checkbox:checked').length;
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = (total === checked);
                }
            });
        });

        // Mark selected entries as paid
        const markSelectedPaidBtn = card.querySelector('.mark-selected-paid-btn');
        if (markSelectedPaidBtn) {
            markSelectedPaidBtn.addEventListener('click', function() {
                const selectedCheckboxes = card.querySelectorAll('.entry-checkbox:checked');
                const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

                if (selectedIds.length === 0) {
                    alert('<?= __('payments.no_entries_selected') ?>');
                    return;
                }

                if (!confirm(`<?= __('payments.mark_selected_paid') ?>? (${selectedIds.length} <?= __('payments.selected') ?>)`)) {
                    return;
                }

                // Send AJAX request
                const formData = new FormData();
                selectedIds.forEach(id => formData.append('labor_ids[]', id));

                fetch('<?= BASE_URL ?>/payments/mark-entries-paid', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '<?= __('payments.error') ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?= __('payments.error_updating') ?>');
                });
            });
        }

        // Mark selected entries as unpaid
        const markSelectedUnpaidBtn = card.querySelector('.mark-selected-unpaid-btn');
        if (markSelectedUnpaidBtn) {
            markSelectedUnpaidBtn.addEventListener('click', function() {
                const selectedCheckboxes = card.querySelectorAll('.entry-checkbox:checked');
                const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

                if (selectedIds.length === 0) {
                    alert('<?= __('payments.no_entries_selected') ?>');
                    return;
                }

                if (!confirm(`<?= __('payments.mark_selected_unpaid') ?>? (${selectedIds.length} <?= __('payments.selected') ?>)`)) {
                    return;
                }

                const formData = new FormData();
                selectedIds.forEach(id => formData.append('labor_ids[]', id));

                fetch('<?= BASE_URL ?>/payments/mark-entries-unpaid', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '<?= __('payments.error') ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?= __('payments.error_updating') ?>');
                });
            });
        }

        // Mark entire week as paid
        const markWeekPaidBtn = card.querySelector('.mark-week-paid-btn');
        if (markWeekPaidBtn) {
            markWeekPaidBtn.addEventListener('click', function() {
                const technicianId = this.dataset.technicianId;
                const weekStart = this.dataset.weekStart;
                const weekEnd = this.dataset.weekEnd;

                if (!confirm('<?= __('payments.mark_week_paid') ?>?')) {
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('payments.processing') ?>';

                const formData = new FormData();
                formData.append('technician_id', technicianId);
                formData.append('week_start', weekStart);
                formData.append('week_end', weekEnd);

                fetch('<?= BASE_URL ?>/payments/mark-week-paid', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '<?= __('payments.error') ?>');
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-check-double me-2"></i><?= __('payments.mark_week_paid') ?>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?= __('payments.error_updating') ?>');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-check-double me-2"></i><?= __('payments.mark_week_paid') ?>';
                });
            });
        }

        // Mark entire week as unpaid
        const markWeekUnpaidBtn = card.querySelector('.mark-week-unpaid-btn');
        if (markWeekUnpaidBtn) {
            markWeekUnpaidBtn.addEventListener('click', function() {
                const technicianId = this.dataset.technicianId;
                const weekStart = this.dataset.weekStart;
                const weekEnd = this.dataset.weekEnd;

                if (!confirm('<?= __('payments.mark_week_unpaid') ?>?')) {
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('payments.processing') ?>';

                const formData = new FormData();
                formData.append('technician_id', technicianId);
                formData.append('week_start', weekStart);
                formData.append('week_end', weekEnd);

                fetch('<?= BASE_URL ?>/payments/mark-week-unpaid', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || '<?= __('payments.error') ?>');
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-undo me-2"></i><?= __('payments.mark_week_unpaid') ?>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?= __('payments.error_updating') ?>');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-undo me-2"></i><?= __('payments.mark_week_unpaid') ?>';
                });
            });
        }
    });
});

// Bulk Payment Functionality
document.addEventListener('DOMContentLoaded', function() {
    const markAllBtn = document.getElementById('markAllPaidBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            // Collect all unpaid technicians from the page
            const technicianCards = document.querySelectorAll('.technician-card');
            const unpaidTechs = [];
            let totalUnpaid = 0;
            
            technicianCards.forEach(card => {
                // Find the unpaid amount using the data attribute
                const unpaidElement = card.querySelector('[data-unpaid-amount]');
                
                if (unpaidElement) {
                    const unpaidAmount = parseFloat(unpaidElement.dataset.unpaidAmount);
                    
                    // Only include techs with unpaid amounts > 0
                    if (unpaidAmount > 0) {
                        const techName = card.querySelector('h5').textContent.trim();
                        
                        unpaidTechs.push({
                            id: card.dataset.technicianId,
                            name: techName,
                            amount: unpaidAmount
                        });
                        totalUnpaid += unpaidAmount;
                    }
                }
            });
            
            if (unpaidTechs.length === 0) {
                alert('Δεν υπάρχουν απλήρωτες εγγραφές για την επιλεγμένη περίοδο.');
                return;
            }
            
            // Populate modal
            const listContainer = document.getElementById('bulkPaymentList');
            listContainer.innerHTML = unpaidTechs.map(tech => `
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <i class="fas fa-user me-2 text-muted"></i>
                        <strong>${tech.name}</strong>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-warning text-dark">${tech.amount.toFixed(2)}€</span>
                    </div>
                </div>
            `).join('');
            
            document.getElementById('bulkTotalAmount').textContent = totalUnpaid.toFixed(2) + '€';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('bulkPaymentModal'));
            modal.show();
        });
    }
    
    // Confirm bulk payment
    const confirmBtn = document.getElementById('confirmBulkPayment');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const weekStart = document.getElementById('markAllPaidBtn').dataset.weekStart;
            const weekEnd = document.getElementById('markAllPaidBtn').dataset.weekEnd;
            
            // Disable button
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Επεξεργασία...';
            
            const formData = new FormData();
            formData.append('week_start', weekStart);
            formData.append('week_end', weekEnd);
            
            fetch('<?= BASE_URL ?>/payments/mark-all-paid', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Σφάλμα κατά την ενημέρωση');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-check-double me-2"></i>Επιβεβαίωση Πληρωμής';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Σφάλμα κατά την επικοινωνία με τον server');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-check-double me-2"></i>Επιβεβαίωση Πληρωμής';
            });
        });
    }
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once 'views/includes/footer.php'; ?>

