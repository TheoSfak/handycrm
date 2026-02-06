<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice-dollar"></i> <?= __('invoices.invoice') ?> #<?= $invoice['invoice_number'] ?></h2>
    <div>
        <!-- Quick Status Change Dropdown -->
        <div class="btn-group me-2" role="group">
            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-sync-alt"></i> <?= __('invoices.change_status') ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="changeStatus('draft', <?= $invoice['id'] ?>); return false;">
                    <i class="fas fa-file text-secondary"></i> <?= __('invoices.draft') ?>
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="changeStatus('sent', <?= $invoice['id'] ?>); return false;">
                    <i class="fas fa-paper-plane text-info"></i> <?= __('invoices.sent') ?>
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="changeStatus('paid', <?= $invoice['id'] ?>); return false;">
                    <i class="fas fa-check-circle text-success"></i> <?= __('invoices.paid') ?>
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="changeStatus('overdue', <?= $invoice['id'] ?>); return false;">
                    <i class="fas fa-exclamation-triangle text-warning"></i> <?= __('invoices.overdue') ?>
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="changeStatus('cancelled', <?= $invoice['id'] ?>); return false;">
                    <i class="fas fa-ban text-danger"></i> <?= __('invoices.cancelled') ?>
                </a></li>
            </ul>
        </div>
        
        <?php if ($invoice['status'] !== 'paid'): ?>
        <a href="<?= BASE_URL ?>/invoices/edit/<?= $invoice['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> <?= __('common.edit') ?>
        </a>
        <?php endif; ?>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> <?= __('common.print') ?>
        </button>
        <a href="?route=/invoices" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?= __('common.back') ?>
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Status Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2"><?= __('invoices.invoice_status') ?></h6>
                <?php
                    $statusColors = ['draft' => 'secondary', 'sent' => 'info', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'dark'];
                    $statusLabels = ['draft' => __('invoices.draft'), 'sent' => __('invoices.sent'), 'paid' => __('invoices.paid'), 'overdue' => __('invoices.overdue'), 'cancelled' => __('invoices.cancelled')];
                ?>
                <span class="badge bg-<?= $statusColors[$invoice['status']] ?? 'secondary' ?> fs-6">
                    <?= $statusLabels[$invoice['status']] ?? $invoice['status'] ?>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2"><?= __('invoices.total_amount') ?></h6>
                <h4 class="mb-0 text-primary"><?= formatCurrency($invoice['total_amount']) ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2"><?= __('invoices.paid_amount') ?></h6>
                <h4 class="mb-0 <?= $invoice['paid_amount'] >= $invoice['total_amount'] ? 'text-success' : 'text-warning' ?>">
                    <?= formatCurrency($invoice['paid_amount']) ?>
                </h4>
                <?php if ($invoice['paid_date']): ?>
                    <small class="text-muted"><?= __('invoices.paid_on') ?>: <?= formatDate($invoice['paid_date']) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Invoice Details Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('invoices.invoice_details') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($invoice['title'])): ?>
                    <h4><?= htmlspecialchars($invoice['title']) ?></h4>
                <?php endif; ?>
                <?php if (!empty($invoice['description'])): ?>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($invoice['description'])) ?></p>
                <?php endif; ?>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p><strong><?= __('invoices.issue_date_short') ?>:</strong> <?= formatDate($invoice['issue_date']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><?= __('invoices.due_date_short') ?>:</strong> <?= formatDate($invoice['due_date']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('invoices.customer_details') ?></h5>
            </div>
            <div class="card-body">
                <h6>
                    <?php
                        $customerName = $invoice['customer_type'] === 'company' && !empty($invoice['customer_company_name']) 
                            ? $invoice['customer_company_name'] 
                            : $invoice['customer_first_name'] . ' ' . $invoice['customer_last_name'];
                        echo htmlspecialchars($customerName);
                    ?>
                </h6>
                <?php if (!empty($invoice['customer_phone'])): ?>
                    <p class="mb-1"><i class="fas fa-phone"></i> <?= htmlspecialchars($invoice['customer_phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($invoice['customer_email'])): ?>
                    <p class="mb-1"><i class="fas fa-envelope"></i> <?= htmlspecialchars($invoice['customer_email']) ?></p>
                <?php endif; ?>
                <?php if (!empty($invoice['customer_address'])): ?>
                    <p class="mb-1"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($invoice['customer_address']) ?>
                    <?php if (!empty($invoice['customer_postal_code']) || !empty($invoice['customer_city'])): ?>
                        , <?= htmlspecialchars($invoice['customer_postal_code']) ?> <?= htmlspecialchars($invoice['customer_city']) ?>
                    <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($invoice['customer_tax_id'])): ?>
                    <p class="mb-0"><i class="fas fa-id-card"></i> <?= __('customers.tax_id') ?>: <?= htmlspecialchars($invoice['customer_tax_id']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('invoices.invoice_items') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= __('invoices.description') ?></th>
                                <th width="15%"><?= __('invoices.quantity') ?></th>
                                <th width="18%"><?= __('invoices.price') ?></th>
                                <th width="18%"><?= __('invoices.total') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoice['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td><?= number_format($item['quantity'], 2, ',', '.') ?></td>
                                <td><?= formatCurrency($item['unit_price']) ?></td>
                                <td><?= formatCurrency($item['total_price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong><?= __('invoices.subtotal') ?>:</strong></td>
                                <td><strong><?= formatCurrency($invoice['subtotal']) ?></strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><?= __('invoices.vat') ?> <?= number_format($invoice['vat_rate'], 0) ?>%:</td>
                                <td><?= formatCurrency($invoice['vat_amount']) ?></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><h5 class="mb-0"><?= __('invoices.total') ?>:</h5></td>
                                <td><h5 class="mb-0"><?= formatCurrency($invoice['total_amount']) ?></h5></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($invoice['notes'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('invoices.notes') ?></h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('common.actions') ?></h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if ($invoice['status'] !== 'paid'): ?>
                    <a href="<?= BASE_URL ?>/invoices/edit/<?= $invoice['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> <?= __('common.edit') ?>
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> <?= __('common.delete') ?>
                    </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> <?= __('common.print') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Project Info -->
        <?php if ($invoice['project_title']): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?= __('invoices.project') ?></h5>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    <i class="fas fa-project-diagram"></i> 
                    <?= htmlspecialchars($invoice['project_title']) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="?route=/invoices/delete">
    <input type="hidden" name="id" value="<?= $invoice['id'] ?>">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<script>
function confirmDelete() {
    if (confirm('<?= __('invoices.confirm_delete') ?>')) {
        document.getElementById('deleteForm').submit();
    }
}

function changeStatus(newStatus, invoiceId) {
    const statusLabels = {
        'draft': '<?= __('invoices.draft') ?>',
        'sent': '<?= __('invoices.sent') ?>',
        'paid': '<?= __('invoices.paid') ?>',
        'overdue': '<?= __('invoices.overdue') ?>',
        'cancelled': '<?= __('invoices.cancelled') ?>'
    };
    
    let message = '<?= __('invoices.confirm_change_status') ?> "' + statusLabels[newStatus] + '"?';
    
    if (newStatus === 'paid') {
        message += '\n\n<?= __('invoices.paid_auto_update') ?>';
    }
    
    if (confirm(message)) {
        // Create form dynamically
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?route=/invoices/update-status';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= CSRF_TOKEN_NAME ?>';
        csrfInput.value = '<?= $_SESSION['csrf_token'] ?>';
        form.appendChild(csrfInput);
        
        // Add invoice ID
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'invoice_id';
        idInput.value = invoiceId;
        form.appendChild(idInput);
        
        // Add status
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
