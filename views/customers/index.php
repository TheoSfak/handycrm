<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users"></i> <?= __('customers.title') ?></h2>
    <div class="btn-group" role="group">
        <a href="index.php?route=/customers/export-csv" class="btn btn-success">
            <i class="fas fa-download"></i> <?= __('customers.export_csv') ?>
        </a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importCsvModal">
            <i class="fas fa-upload"></i> <?= __('customers.import_csv') ?>
        </button>
        <a href="index.php?route=/customers/demo-csv" class="btn btn-secondary">
            <i class="fas fa-file-csv"></i> <?= __('customers.demo_csv') ?>
        </a>
        <a href="?route=/customers/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?= __('customers.new_customer') ?>
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label"><?= __('customers.search') ?></label>
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       placeholder="<?= __('customers.search_placeholder') ?>" 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            
            <div class="col-md-4">
                <label for="type" class="form-label"><?= __('customers.customer_type') ?></label>
                <select class="form-select" id="type" name="type">
                    <option value=""><?= __('customers.all') ?></option>
                    <option value="individual" <?= $type === 'individual' ? 'selected' : '' ?>><?= __('customers.individual') ?></option>
                    <option value="company" <?= $type === 'company' ? 'selected' : '' ?>><?= __('customers.company') ?></option>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">
                    <i class="fas fa-search"></i> <?= __('customers.search') ?>
                </button>
                <a href="?route=/customers" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Results Summary -->
<div class="row mb-3">
    <div class="col-md-6">
        <p class="text-muted mb-0">
            <?= str_replace(['{count}', '{total}'], [count($customers['data']), $customers['total_records']], __('customers.showing_results')) ?>
            <?php if (!empty($search)): ?>
                (<?= __('customers.search_for') ?>: "<?= htmlspecialchars($search) ?>")
            <?php endif; ?>
        </p>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group btn-group-sm" role="group">
            <input type="radio" class="btn-check" name="view" id="card-view" checked>
            <label class="btn btn-outline-secondary" for="card-view">
                <i class="fas fa-th"></i> <?= __('customers.cards_view') ?>
            </label>
            
            <input type="radio" class="btn-check" name="view" id="table-view">
            <label class="btn btn-outline-secondary" for="table-view">
                <i class="fas fa-list"></i> <?= __('customers.list_view') ?>
            </label>
        </div>
    </div>
</div>

<!-- Customers Grid View -->
<div id="customers-grid" class="row">
    <?php if (!empty($customers['data'])): ?>
        <?php foreach ($customers['data'] as $customer): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 customer-card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <!-- Customer Type Badge -->
                            <div class="mb-2">
                                <span class="badge <?= $customer['customer_type'] === 'company' ? 'bg-info' : 'bg-primary' ?>">
                                    <i class="fas fa-<?= $customer['customer_type'] === 'company' ? 'building' : 'user' ?>"></i>
                                    <?= $customer['customer_type'] === 'company' ? __('customers.company') : __('customers.individual') ?>
                                </span>
                            </div>
                            
                            <!-- Customer Name -->
                            <h5 class="card-title mb-1">
                                <a href="<?= BASE_URL ?>/customers/<?= $customer['slug'] ?>" class="text-decoration-none">
                                    <?php 
                                    if ($customer['customer_type'] === 'company' && !empty($customer['company_name'])) {
                                        echo htmlspecialchars($customer['company_name']);
                                    } else {
                                        echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']);
                                    }
                                    ?>
                                </a>
                            </h5>
                            
                            <!-- Contact Info -->
                            <div class="mb-2">
                                <small class="text-muted d-block">
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone']) ?>
                                </small>
                                <?php if (!empty($customer['email'])): ?>
                                <small class="text-muted d-block">
                                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($customer['email']) ?>
                                </small>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Created Date -->
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> 
                                <?= __('customers.created') ?>: <?= date('d/m/Y', strtotime($customer['created_at'])) ?>
                            </small>
                        </div>
                        
                        <!-- Actions Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/customers/<?= $customer['slug'] ?>">
                                        <i class="fas fa-eye"></i> <?= __('customers.view') ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>/customers/edit/<?= $customer['id'] ?>">
                                        <i class="fas fa-edit"></i> <?= __('customers.edit') ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="?route=/projects/create&customer_id=<?= $customer['id'] ?>">
                                        <i class="fas fa-project-diagram"></i> <?= __('customers.new_project') ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="?route=/appointments/create&customer_id=<?= $customer['id'] ?>">
                                        <i class="fas fa-calendar-plus"></i> <?= __('customers.new_appointment') ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="index.php?route=/customers/delete&id=<?= $customer['id'] ?>" 
                                          onsubmit="return confirm('<?= __('customers.confirm_delete') ?>')" 
                                          class="d-inline">
                                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash"></i> <?= __('customers.delete') ?>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted"><?= __('customers.no_customers') ?></h4>
                    <?php if (!empty($search) || !empty($type)): ?>
                        <p class="text-muted"><?= __('customers.try_different_search') ?></p>
                        <a href="?route=/customers" class="btn btn-outline-primary">
                            <i class="fas fa-times"></i> <?= __('customers.clear_filters') ?>
                        </a>
                    <?php else: ?>
                        <p class="text-muted"><?= __('customers.no_customers_message') ?></p>
                        <a href="?route=/customers/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> <?= __('customers.new_customer') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Customers Table View (Hidden by default) -->
<div id="customers-table" style="display: none;">
    <?php if (!empty($customers['data'])): ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th><?= __('customers.name') ?></th>
                        <th><?= __('customers.customer_type') ?></th>
                        <th><?= __('customers.phone') ?></th>
                        <th><?= __('customers.email') ?></th>
                        <th><?= __('customers.city') ?></th>
                        <th><?= __('customers.created_at') ?></th>
                        <th><?= __('customers.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers['data'] as $customer): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="avatar bg-<?= $customer['customer_type'] === 'company' ? 'info' : 'primary' ?> text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-<?= $customer['customer_type'] === 'company' ? 'building' : 'user' ?>"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        <a href="<?= BASE_URL ?>/customers/<?= $customer['slug'] ?>" class="text-decoration-none">
                                            <?php 
                                            if ($customer['customer_type'] === 'company' && !empty($customer['company_name'])) {
                                                echo htmlspecialchars($customer['company_name']);
                                            } else {
                                                echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']);
                                            }
                                            ?>
                                        </a>
                                    </h6>
                                    <?php if ($customer['customer_type'] === 'company' && !empty($customer['company_name'])): ?>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $customer['customer_type'] === 'company' ? 'bg-info' : 'bg-primary' ?>">
                                <?= $customer['customer_type'] === 'company' ? __('customers.company') : __('customers.individual') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($customer['phone']) ?></td>
                        <td><?= htmlspecialchars($customer['email'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($customer['city'] ?? '-') ?></td>
                        <td><?= date('d/m/Y', strtotime($customer['created_at'])) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?= BASE_URL ?>/customers/<?= $customer['slug'] ?>" class="btn btn-outline-primary" title="<?= __('customers.view') ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/customers/edit/<?= $customer['id'] ?>" class="btn btn-outline-secondary" title="<?= __('customers.edit') ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" title="<?= __('common.more') ?>">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="?route=/projects/create&customer_id=<?= $customer['id'] ?>">
                                                <i class="fas fa-project-diagram"></i> <?= __('customers.new_project') ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="?route=/appointments/create&customer_id=<?= $customer['id'] ?>">
                                                <i class="fas fa-calendar-plus"></i> <?= __('customers.new_appointment') ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="?route=/quotes/create&customer_id=<?= $customer['id'] ?>">
                                                <i class="fas fa-file-invoice"></i> <?= __('customers.new_quote') ?>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="index.php?route=/customers/delete&id=<?= $customer['id'] ?>" 
                                                  onsubmit="return confirm('<?= __('customers.confirm_delete') ?>')" 
                                                  class="d-inline">
                                                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash"></i> <?= __('customers.delete') ?>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if (!empty($pagination)): ?>
<div class="d-flex justify-content-center mt-4">
    <?= $pagination ?>
</div>
<?php endif; ?>

<script>
// Toggle between card and table view
document.addEventListener('DOMContentLoaded', function() {
    const cardViewBtn = document.getElementById('card-view');
    const tableViewBtn = document.getElementById('table-view');
    const customersGrid = document.getElementById('customers-grid');
    const customersTable = document.getElementById('customers-table');
    
    cardViewBtn.addEventListener('change', function() {
        if (this.checked) {
            customersGrid.style.display = 'block';
            customersTable.style.display = 'none';
        }
    });
    
    tableViewBtn.addEventListener('change', function() {
        if (this.checked) {
            customersGrid.style.display = 'none';
            customersTable.style.display = 'block';
        }
    });
    
    // Save view preference
    const savedView = localStorage.getItem('customers_view');
    if (savedView === 'table') {
        tableViewBtn.checked = true;
        customersGrid.style.display = 'none';
        customersTable.style.display = 'block';
    }
    
    cardViewBtn.addEventListener('change', function() {
        if (this.checked) {
            localStorage.setItem('customers_view', 'card');
        }
    });
    
    tableViewBtn.addEventListener('change', function() {
        if (this.checked) {
            localStorage.setItem('customers_view', 'table');
        }
    });
});

// Auto-submit search form on type change
document.getElementById('type').addEventListener('change', function() {
    this.form.submit();
});

// Fix dropdown clipped by cards: initialize with fixed strategy via Bootstrap API
document.querySelectorAll('.customer-card .dropdown [data-bs-toggle="dropdown"]').forEach(function(el) {
    new bootstrap.Dropdown(el, {
        popperConfig: {
            strategy: 'fixed',
            modifiers: [{
                name: 'preventOverflow',
                options: { boundary: 'viewport' }
            }]
        }
    });
});
</script>

<style>
.customer-card {
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
    border: 1px solid #dee2e6;
}

.customer-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.avatar {
    font-size: 16px;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<!-- Import CSV Modal -->
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-labelledby="importCsvModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importCsvModalLabel">
                    <i class="fas fa-upload"></i> <?= __('customers.import_csv') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=/customers/import-csv" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <?= __('customers.download_demo_csv') ?>: 
                        <a href="index.php?route=/customers/demo-csv" target="_blank"><?= __('customers.demo_csv') ?></a>
                    </div>
                    <div class="mb-3">
                        <label for="csv_file" class="form-label"><?= __('customers.csv_file_required') ?></label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= __('common.cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> <?= __('customers.import_csv') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>