<?php
require_once 'views/includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-money-bill-wave me-2"></i><?= __('payments.page_title') ?></h2>
            <p class="text-muted"><?= __('payments.page_description') ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/payments" class="row g-3">
                <div class="col-md-4">
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

                <div class="col-md-3">
                    <label for="week_start" class="form-label">
                        <i class="fas fa-calendar me-1"></i><?= __('payments.week_start') ?>
                    </label>
                    <input type="date" class="form-control" id="week_start" name="week_start" 
                           value="<?= $weekStart ?>" required>
                </div>

                <div class="col-md-3">
                    <label for="week_end" class="form-label">
                        <i class="fas fa-calendar me-1"></i><?= __('payments.week_end') ?>
                    </label>
                    <input type="date" class="form-control" id="week_end" name="week_end" 
                           value="<?= $weekEnd ?>" required>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i><?= __('payments.filter') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Weekly Summary -->
    <?php if (!empty($weeklyData)): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong><?= __('payments.showing_week') ?>:</strong> 
                    <?= formatDate($weekStart) ?> - <?= formatDate($weekEnd) ?>
                    (<?= count($weeklyData) ?> <?= __('payments.technicians_found') ?>)
                </div>
            </div>
        </div>

        <!-- Technicians List -->
        <?php foreach ($weeklyData as $tech): ?>
            <div class="card shadow-sm mb-4" id="tech-card-<?= $tech['technician_id'] ?>">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>
                                <?= htmlspecialchars($tech['technician_name']) ?>
                            </h5>
                        </div>
                        <div class="col-md-3 text-center">
                            <strong><?= __('payments.hourly_rate') ?>:</strong> 
                            <?= formatCurrency($tech['hourly_rate']) ?>/h
                        </div>
                        <div class="col-md-3 text-center">
                            <strong><?= __('payments.total_hours') ?>:</strong> 
                            <span class="badge bg-light text-dark fs-6"><?= number_format($tech['total_hours'], 2) ?>h</span>
                        </div>
                        <div class="col-md-2 text-end">
                            <strong><?= __('payments.total_amount') ?>:</strong> 
                            <span class="badge bg-warning text-dark fs-6"><?= formatCurrency($tech['total_amount']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <?php if (!empty($tech['entries'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><?= __('payments.date') ?></th>
                                        <th><?= __('payments.project') ?></th>
                                        <th><?= __('payments.task') ?></th>
                                        <th class="text-center"><?= __('payments.hours') ?></th>
                                        <th class="text-end"><?= __('payments.rate') ?></th>
                                        <th class="text-end"><?= __('payments.amount') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tech['entries'] as $entry): 
                                        // Get the work date from either task_date (single_day) or date_from (date_range)
                                        $workDate = $entry['task_date'] ?: $entry['date_from'];
                                    ?>
                                        <tr>
                                            <td><?= formatDate($workDate) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($entry['project_title']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($entry['task_description']) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?= number_format($entry['hours_worked'], 2) ?>h</span>
                                            </td>
                                            <td class="text-end"><?= formatCurrency($entry['hourly_rate']) ?></td>
                                            <td class="text-end">
                                                <strong><?= formatCurrency($entry['subtotal']) ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="3" class="text-end"><?= __('payments.total') ?>:</th>
                                        <th class="text-center">
                                            <strong><?= number_format($tech['total_hours'], 2) ?>h</strong>
                                        </th>
                                        <th></th>
                                        <th class="text-end">
                                            <strong class="text-success fs-5"><?= formatCurrency($tech['total_amount']) ?></strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Payment Status -->
                        <div class="mt-3 pt-3 border-top">
                            <?php if ($tech['paid_at']): ?>
                                <div class="alert alert-success mb-0">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <strong><?= __('payments.paid') ?>:</strong> 
                                            <?= formatDate($tech['paid_at'], true) ?>
                                            <?php if ($tech['paid_by_user']): ?>
                                                <?= __('payments.by') ?> <?= htmlspecialchars($tech['paid_by_user']) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-outline-danger btn-sm mark-unpaid-btn" 
                                                    data-payment-id="<?= $tech['payment_id'] ?>"
                                                    data-technician-id="<?= $tech['technician_id'] ?>">
                                                <i class="fas fa-undo me-1"></i><?= __('payments.mark_unpaid') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong><?= __('payments.not_paid_yet') ?></strong>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-success mark-paid-btn" 
                                                    data-technician-id="<?= $tech['technician_id'] ?>"
                                                    data-technician-name="<?= htmlspecialchars($tech['technician_name']) ?>"
                                                    data-week-start="<?= $weekStart ?>"
                                                    data-week-end="<?= $weekEnd ?>"
                                                    data-total-hours="<?= $tech['total_hours'] ?>"
                                                    data-total-amount="<?= $tech['total_amount'] ?>">
                                                <i class="fas fa-check me-1"></i><?= __('payments.mark_as_paid') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <?= __('payments.no_entries_found') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted"><?= __('payments.no_data_found') ?></h4>
                <p><?= __('payments.try_different_filters') ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Payment Confirmation Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i><?= __('payments.confirm_payment') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="payment-details"></div>
                <div class="mb-3 mt-3">
                    <label for="payment-notes" class="form-label"><?= __('payments.notes') ?> (<?= __('payments.optional') ?>)</label>
                    <textarea class="form-control" id="payment-notes" rows="3" 
                              placeholder="<?= __('payments.notes_placeholder') ?>"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i><?= __('payments.cancel') ?>
                </button>
                <button type="button" class="btn btn-success" id="confirm-payment-btn">
                    <i class="fas fa-check me-1"></i><?= __('payments.confirm_payment') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    let currentPaymentData = {};

    // Mark as Paid button click
    document.querySelectorAll('.mark-paid-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentPaymentData = {
                technician_id: this.dataset.technicianId,
                technician_name: this.dataset.technicianName,
                week_start: this.dataset.weekStart,
                week_end: this.dataset.weekEnd,
                total_hours: this.dataset.totalHours,
                total_amount: this.dataset.totalAmount
            };

            // Display payment details in modal
            document.getElementById('payment-details').innerHTML = `
                <div class="alert alert-info">
                    <p><strong><?= __('payments.technician') ?>:</strong> ${currentPaymentData.technician_name}</p>
                    <p><strong><?= __('payments.week') ?>:</strong> ${formatDateJS(currentPaymentData.week_start)} - ${formatDateJS(currentPaymentData.week_end)}</p>
                    <p><strong><?= __('payments.total_hours') ?>:</strong> ${parseFloat(currentPaymentData.total_hours).toFixed(2)}h</p>
                    <p class="mb-0"><strong><?= __('payments.total_amount') ?>:</strong> <span class="text-success fs-5">${formatCurrencyJS(currentPaymentData.total_amount)}</span></p>
                </div>
            `;

            paymentModal.show();
        });
    });

    // Confirm payment
    document.getElementById('confirm-payment-btn').addEventListener('click', function() {
        const notes = document.getElementById('payment-notes').value;
        const btn = this;
        const originalText = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i><?= __('payments.processing') ?>';

        fetch('<?= BASE_URL ?>/payments/mark-paid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                ...currentPaymentData,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show updated status
                location.reload();
            } else {
                alert('<?= __('payments.error') ?>: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            alert('<?= __('payments.error') ?>: ' + error);
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    // Mark as Unpaid button click
    document.querySelectorAll('.mark-unpaid-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('<?= __('payments.confirm_mark_unpaid') ?>')) {
                return;
            }

            const paymentId = this.dataset.paymentId;
            const btn = this;
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i><?= __('payments.processing') ?>';

            fetch('<?= BASE_URL ?>/payments/mark-unpaid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    payment_id: paymentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('<?= __('payments.error') ?>: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                alert('<?= __('payments.error') ?>: ' + error);
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    });

    // Helper function to format date
    function formatDateJS(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('el-GR');
    }

    // Helper function to format currency
    function formatCurrencyJS(amount) {
        return parseFloat(amount).toFixed(2).replace('.', ',') + ' €';
    }
});
</script>

<?php require_once 'views/includes/footer.php'; ?>
