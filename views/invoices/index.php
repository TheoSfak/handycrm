<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice-dollar"></i> <?= __('menu.invoices') ?></h2>
    <a href="?route=/invoices/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> <?= __('invoices.new_invoice') ?>
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="?" class="row g-3">
            <input type="hidden" name="route" value="/invoices">
            
            <div class="col-md-2">
                <label class="form-label"><?= __('invoices.status') ?></label>
                <select name="status" class="form-select">
                    <option value=""><?= __('invoices.all') ?></option>
                    <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['status'] === $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= __('invoices.payment') ?></label>
                <select name="payment_status" class="form-select">
                    <option value=""><?= __('invoices.all') ?></option>
                    <?php foreach ($paymentStatuses as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['payment_status'] === $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= __('invoices.customer') ?></label>
                <select name="customer" class="form-select">
                    <option value=""><?= __('invoices.all') ?></option>
                    <?php foreach ($customers as $customer): ?>
                    <?php
                        $customerName = $customer['customer_type'] === 'company' && !empty($customer['company_name']) 
                            ? $customer['company_name'] 
                            : $customer['first_name'] . ' ' . $customer['last_name'];
                    ?>
                    <option value="<?= $customer['id'] ?>" <?= ($filters['customer'] == $customer['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($customerName) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= __('invoices.from') ?></label>
                <input type="date" name="date_from" class="form-control" 
                       value="<?= htmlspecialchars($filters['date_from']) ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= __('invoices.to') ?></label>
                <input type="date" name="date_to" class="form-control" 
                       value="<?= htmlspecialchars($filters['date_to']) ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= __('invoices.search') ?></label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="<?= __('invoices.search_placeholder') ?>" 
                           value="<?= htmlspecialchars($filters['search']) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($invoices)): ?>
        <div class="text-center py-5">
            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
            <p class="text-muted"><?= __('invoices.no_invoices_found') ?></p>
            <a href="?route=/invoices/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= __('invoices.create_new_invoice') ?>
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('invoices.number') ?></th>
                        <th><?= __('invoices.date') ?></th>
                        <th><?= __('invoices.customer') ?></th>
                        <th><?= __('invoices.project') ?></th>
                        <th><?= __('invoices.amount') ?></th>
                        <th><?= __('invoices.due_date') ?></th>
                        <th><?= __('invoices.status') ?></th>
                        <th><?= __('invoices.payment') ?></th>
                        <th><?= __('invoices.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                    <?php
                        $customerName = $invoice['customer_type'] === 'company' && !empty($invoice['customer_company_name']) 
                            ? $invoice['customer_company_name'] 
                            : $invoice['customer_first_name'] . ' ' . $invoice['customer_last_name'];
                        
                        // Status badge colors
                        $statusColors = [
                            'draft' => 'secondary',
                            'sent' => 'info',
                            'viewed' => 'primary',
                            'paid' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $statusColor = $statusColors[$invoice['status']] ?? 'secondary';
                        
                        // Payment status badge colors
                        $paymentColors = [
                            'unpaid' => 'warning',
                            'partial' => 'info',
                            'paid' => 'success',
                            'overdue' => 'danger'
                        ];
                        $paymentColor = $paymentColors[$invoice['payment_status']] ?? 'secondary';
                        
                        // Check if overdue
                        $isOverdue = strtotime($invoice['due_date']) < time() && $invoice['payment_status'] !== 'paid';
                    ?>
                    <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                        <td>
                            <a href="<?= BASE_URL ?>/invoices/<?= $invoice['slug'] ?>" 
                               class="text-decoration-none fw-bold">
                                <?= htmlspecialchars($invoice['invoice_number']) ?>
                            </a>
                            <?php if ($invoice['title']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($invoice['title']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= formatDate($invoice['issue_date']) ?></td>
                        <td><?= htmlspecialchars($customerName) ?></td>
                        <td>
                            <?php if ($invoice['project_title']): ?>
                                <?= htmlspecialchars($invoice['project_title']) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= formatCurrency($invoice['total_amount']) ?></strong></td>
                        <td>
                            <?= formatDate($invoice['due_date']) ?>
                            <?php if ($isOverdue): ?>
                            <br><span class="badge bg-danger"><?= __('invoices.overdue') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= $statuses[$invoice['status']] ?? $invoice['status'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $paymentColor ?>">
                                <?= $paymentStatuses[$invoice['payment_status']] ?? $invoice['payment_status'] ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?= BASE_URL ?>/invoices/<?= $invoice['slug'] ?>" 
                                   class="btn btn-sm btn-info" title="<?= __('invoices.view') ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($invoice['payment_status'] !== 'paid'): ?>
                                <a href="<?= BASE_URL ?>/invoices/edit/<?= $invoice['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="<?= __('invoices.edit') ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $invoice['id'] ?>)" title="<?= __('invoices.delete') ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?route=/invoices&page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= ($i === $pagination['current_page']) ? 'active' : '' ?>">
                    <a class="page-link" href="?route=/invoices&page=<?= $i ?>&<?= http_build_query($filters) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?route=/invoices&page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<form id="deleteForm" method="POST" action="index.php?route=/invoices/delete">
    <input type="hidden" name="id" id="deleteInvoiceId">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<script>
function confirmDelete(invoiceId) {
    if (confirm('<?= __('invoices.confirm_delete') ?>')) {
        document.getElementById('deleteInvoiceId').value = invoiceId;
        document.getElementById('deleteForm').submit();
    }
}
</script>
