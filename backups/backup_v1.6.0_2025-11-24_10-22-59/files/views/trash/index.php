<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-trash"></i> Κάδος Απορριμμάτων</h1>
                <a href="?route=/trash/log" class="btn btn-outline-secondary">
                    <i class="fas fa-history"></i> Ιστορικό Διαγραφών
                </a>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Tabs για τύπους στοιχείων -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'project' ? 'active' : '' ?>" href="?type=project">
                        Έργα <?php if ($counts['project'] > 0): ?>
                            <span class="badge bg-danger"><?= $counts['project'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'daily_task' ? 'active' : '' ?>" href="?type=daily_task">
                        Ημερήσιες Εργασίες <?php if ($counts['daily_task'] > 0): ?>
                            <span class="badge bg-danger"><?= $counts['daily_task'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'maintenance' ? 'active' : '' ?>" href="?type=maintenance">
                        Συντηρήσεις Μ/Σ <?php if ($counts['maintenance'] > 0): ?>
                            <span class="badge bg-danger"><?= $counts['maintenance'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            
            <!-- Φίλτρα -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="?" class="row g-3">
                        <input type="hidden" name="route" value="/trash">
                        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                        
                        <div class="col-md-4">
                            <label class="form-label">Αναζήτηση</label>
                            <input type="text" name="search" class="form-control" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Αναζήτηση στοιχείων...">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Από Ημερομηνία</label>
                            <input type="date" name="date_from" class="form-control" 
                                   value="<?= htmlspecialchars($dateFrom) ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Έως Ημερομηνία</label>
                            <input type="date" name="date_to" class="form-control" 
                                   value="<?= htmlspecialchars($dateTo) ?>">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Αναζήτηση
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Bulk Actions -->
            <?php if (!empty($items)): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAll">
                                    <i class="fas fa-check-double"></i> Επιλογή Όλων
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                    <i class="fas fa-times"></i> Αποεπιλογή Όλων
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-success" id="bulkRestore" disabled>
                                    <i class="fas fa-undo"></i> Επαναφορά Επιλεγμένων
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" id="bulkDelete" disabled>
                                    <i class="fas fa-trash-alt"></i> Οριστική Διαγραφή Επιλεγμένων
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="emptyTrash">
                                    <i class="fas fa-dumpster"></i> Άδειασμα Κάδου
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Πίνακας Διαγραμμένων Στοιχείων -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($items)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="lead">Δεν υπάρχουν διαγραμμένα στοιχεία</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllCheckbox">
                                        </th>
                                        <th>Όνομα</th>
                                        <th>Διαγράφηκε</th>
                                        <th>Διαγράφηκε Από</th>
                                        <th width="200">Ενέργειες</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="item-checkbox" 
                                                       value="<?= $item['id'] ?>">
                                            </td>
                                            <td>
                                                <strong>
                                                    <?php
                                                    switch ($type) {
                                                        case 'project':
                                                            echo htmlspecialchars($item['title'] ?? 'N/A');
                                                            break;
                                                        case 'daily_task':
                                                            echo htmlspecialchars($item['customer_name'] ?? 'N/A');
                                                            break;
                                                        case 'maintenance':
                                                            echo htmlspecialchars($item['customer_name'] ?? 'N/A');
                                                            break;
                                                    }
                                                    ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($item['deleted_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= htmlspecialchars($item['deleted_by_name'] ?? $item['deleted_by_username'] ?? 'N/A') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <form method="POST" action="?route=/trash/restore" class="d-inline">
                                                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            title="Επαναφορά">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" action="?route=/trash/permanent-delete" 
                                                      class="d-inline" 
                                                      onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε οριστικά αυτό το στοιχείο; Η ενέργεια αυτή δεν μπορεί να αναιρεθεί!');">
                                                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Οριστική Διαγραφή">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkRestoreBtn = document.getElementById('bulkRestore');
    const bulkDeleteBtn = document.getElementById('bulkDelete');
    const emptyTrashBtn = document.getElementById('emptyTrash');
    
    // Έλεγχος για ενεργοποίηση bulk buttons
    function updateBulkButtons() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (bulkRestoreBtn) bulkRestoreBtn.disabled = checkedCount === 0;
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = checkedCount === 0;
    }
    
    // Select All Checkbox
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButtons();
        });
    }
    
    // Select All Button
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            itemCheckboxes.forEach(cb => cb.checked = true);
            if (selectAllCheckbox) selectAllCheckbox.checked = true;
            updateBulkButtons();
        });
    }
    
    // Deselect All Button
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            itemCheckboxes.forEach(cb => cb.checked = false);
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            updateBulkButtons();
        });
    }
    
    // Item checkboxes
    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkButtons);
    });
    
    // Bulk Restore
    if (bulkRestoreBtn) {
        bulkRestoreBtn.addEventListener('click', function() {
            const checkedIds = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                .map(cb => cb.value);
            
            if (checkedIds.length === 0) return;
            
            if (confirm(`Θέλετε να επαναφέρετε ${checkedIds.length} στοιχεία;`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '?route=/trash/bulk-restore';
                
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'type';
                typeInput.value = '<?= htmlspecialchars($type) ?>';
                form.appendChild(typeInput);
                
                checkedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    // Bulk Delete
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedIds = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                .map(cb => cb.value);
            
            if (checkedIds.length === 0) return;
            
            if (confirm(`ΠΡΟΣΟΧΗ! Θέλετε να διαγράψετε οριστικά ${checkedIds.length} στοιχεία; Η ενέργεια αυτή δεν μπορεί να αναιρεθεί!`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '?route=/trash/bulk-delete';
                
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'type';
                typeInput.value = '<?= htmlspecialchars($type) ?>';
                form.appendChild(typeInput);
                
                checkedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    // Empty Trash
    if (emptyTrashBtn) {
        emptyTrashBtn.addEventListener('click', function() {
            if (confirm('ΠΡΟΣΟΧΗ! Θέλετε να αδειάσετε ολόκληρο τον κάδο για αυτήν την κατηγορία; Όλα τα στοιχεία θα διαγραφούν οριστικά και η ενέργεια δεν μπορεί να αναιρεθεί!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '?route=/trash/empty';
                
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'type';
                typeInput.value = '<?= htmlspecialchars($type) ?>';
                form.appendChild(typeInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
