<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice"></i> <?= __('menu.quotes') ?></h2>
    <a href="?route=/quotes/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> <?= __('quotes.new_quote') ?>
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
            <input type="hidden" name="route" value="/quotes">
            
            <div class="col-md-4">
                <label class="form-label"><?= __('quotes.status') ?></label>
                <select name="status" class="form-select">
                    <option value=""><?= __('quotes.all') ?></option>
                    <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['status'] === $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-6">
                <label class="form-label"><?= __('quotes.search') ?></label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="<?= __('quotes.search_placeholder') ?>" 
                           value="<?= htmlspecialchars($filters['search']) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <a href="?route=/quotes" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?= __('quotes.clear') ?>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Quotes Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($quotes)): ?>
        <div class="text-center py-5">
            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
            <p class="text-muted"><?= __('quotes.no_quotes_found') ?></p>
            <a href="?route=/quotes/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= __('quotes.create_new_quote') ?>
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('quotes.quote_number') ?></th>
                        <th><?= __('quotes.quote_title') ?></th>
                        <th><?= __('quotes.customer') ?></th>
                        <th><?= __('quotes.issue_date') ?></th>
                        <th><?= __('quotes.valid_until') ?></th>
                        <th><?= __('quotes.amount') ?></th>
                        <th><?= __('quotes.status') ?></th>
                        <th><?= __('quotes.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotes as $quote): ?>
                    <?php
                        $customerName = $quote['customer_type'] === 'company' && !empty($quote['customer_company_name']) 
                            ? $quote['customer_company_name'] 
                            : $quote['customer_first_name'] . ' ' . $quote['customer_last_name'];
                        
                        // Status badge colors
                        $statusColors = [
                            'draft' => 'secondary',
                            'sent' => 'info',
                            'accepted' => 'success',
                            'rejected' => 'danger',
                            'expired' => 'warning'
                        ];
                        $statusColor = $statusColors[$quote['status']] ?? 'secondary';
                        
                        // Check if expired
                        $isExpired = strtotime($quote['valid_until']) < time() && $quote['status'] !== 'accepted';
                    ?>
                    <tr class="<?= $isExpired ? 'table-warning' : '' ?>">
                        <td>
                            <strong><?= htmlspecialchars($quote['quote_number']) ?></strong>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/quotes/<?= $quote['slug'] ?>" 
                               class="text-decoration-none fw-bold">
                                <?= htmlspecialchars($quote['title']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($customerName) ?></td>
                        <td><?= date('d/m/Y', strtotime($quote['created_at'])) ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($quote['valid_until'])) ?>
                            <?php if ($isExpired): ?>
                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> <?= __('quotes.expired') ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= number_format($quote['total_amount'], 2) ?>€</strong>
                        </td>
                        <td>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= $statuses[$quote['status']] ?? $quote['status'] ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?= BASE_URL ?>/quotes/<?= $quote['slug'] ?>" 
                                   class="btn btn-sm btn-info" title="<?= __('quotes.view') ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/quotes/edit/<?= $quote['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="<?= __('quotes.edit') ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/quotes/delete/<?= $quote['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('<?= __('quotes.confirm_delete') ?>')" 
                                   title="<?= __('quotes.delete') ?>">
                                    <i class="fas fa-trash"></i>
                                </a>
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
                    <a class="page-link" href="?route=/quotes&page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= ($i === $pagination['current_page']) ? 'active' : '' ?>">
                    <a class="page-link" href="?route=/quotes&page=<?= $i ?>&<?= http_build_query($filters) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?route=/quotes&page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query($filters) ?>">
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


