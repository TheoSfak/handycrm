<?php $pageTitle = 'Εργασίες Ημέρας'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end align-items-center">
                <a href="<?= BASE_URL ?>/daily-tasks/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Νέα Εργασία
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/daily-tasks" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Αναζήτηση</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Πελάτης, περιγραφή...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Από Ημερομηνία</label>
                            <input type="date" class="form-control" name="date_from" 
                                   value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Έως Ημερομηνία</label>
                            <input type="date" class="form-control" name="date_to" 
                                   value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Τύπος Εργασίας</label>
                            <select class="form-select" name="task_type">
                                <option value="">Όλες</option>
                                <option value="electrical" <?= ($_GET['task_type'] ?? '') === 'electrical' ? 'selected' : '' ?>>
                                    Ηλεκτρολογικές
                                </option>
                                <option value="inspection" <?= ($_GET['task_type'] ?? '') === 'inspection' ? 'selected' : '' ?>>
                                    Επίσκεψη/Έλεγχος
                                </option>
                                <option value="fault_repair" <?= ($_GET['task_type'] ?? '') === 'fault_repair' ? 'selected' : '' ?>>
                                    Έλεγχος Βλάβες
                                </option>
                                <option value="other" <?= ($_GET['task_type'] ?? '') === 'other' ? 'selected' : '' ?>>
                                    Διάφορα
                                </option>
                            </select>
                        </div>
                                <div class="col-md-2">
                                    <label class="form-label">Τεχνικός</label>
                                    <select class="form-select" name="technician_id">
                                        <option value="">Όλοι</option>
                                        <?php foreach ($technicians as $tech): ?>
                                            <option value="<?= $tech['id'] ?>" 
                                                <?= ($_GET['technician_id'] ?? '') == $tech['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tech['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Τιμολ.</label>
                                <select class="form-select" name="is_invoiced">
                                    <option value="">Όλες</option>
                                    <option value="1" <?= ($_GET['is_invoiced'] ?? '') === '1' ? 'selected' : '' ?>>Ναι</option>
                                    <option value="0" <?= ($_GET['is_invoiced'] ?? '') === '0' ? 'selected' : '' ?>>Όχι</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Κατάσταση</label>
                                <select class="form-select" name="status">
                                    <option value="">Όλες</option>
                                    <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>
                                        Ολοκληρώθηκε
                                    </option>
                                    <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>
                                        Σε Εξέλιξη
                                    </option>
                                    <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>
                                        Ακυρώθηκε
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i> Φίλτρα
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="<?= BASE_URL ?>/daily-tasks" class="btn btn-secondary w-100">
                                    <i class="fas fa-redo"></i> Καθαρισμός
                                </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <!-- Tasks Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($tasks)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Δεν βρέθηκαν εργασίες.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Αρ. Εργασίας</th>
                                <th>Ημερομηνία</th>
                                <th>Πελάτης</th>
                                <th>Τύπος</th>
                                <th>Περιγραφή</th>
                                <th>Ώρες</th>
                                <th>Τιμολ.</th>
                                <th>Τεχνικός</th>
                                <th>Κατάσταση</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($task['task_number']) ?></strong>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($task['date'])) ?></td>
                                    <td>
                                        <?= htmlspecialchars($task['customer_name']) ?>
                                        <?php if ($task['phone']): ?>
                                            <br><small class="text-muted">
                                                <i class="fas fa-phone"></i> <?= htmlspecialchars($task['phone']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $taskTypeLabels = [
                                            'electrical' => '<span class="badge bg-warning">Ηλεκτρολογικές</span>',
                                            'inspection' => '<span class="badge bg-info">Επίσκεψη/Έλεγχος</span>',
                                            'fault_repair' => '<span class="badge bg-danger">Έλεγχος Βλάβες</span>',
                                            'other' => '<span class="badge bg-secondary">Διάφορα</span>'
                                        ];
                                        echo $taskTypeLabels[$task['task_type']] ?? '';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $desc = htmlspecialchars($task['description']);
                                        echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                                        ?>
                        </td>
                    <td>
                        <?php if ($task['hours_worked']): ?>
                            <i class="fas fa-clock"></i> <?= number_format($task['hours_worked'], 2) ?>h
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-invoiced" 
                                   type="checkbox" 
                                   data-task-id="<?= $task['id'] ?>"
                                   <?= $task['is_invoiced'] ? 'checked' : '' ?>>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($task['technician_name']) ?></td>
                    <td>
                        <?php
                        $statusBadges = [
                            'completed' => '<span class="badge bg-success">Ολοκληρώθηκε</span>',
                            'in_progress' => '<span class="badge bg-primary">Σε Εξέλιξη</span>',
                            'cancelled' => '<span class="badge bg-danger">Ακυρώθηκε</span>'
                        ];
                        echo $statusBadges[$task['status']] ?? '';
                        ?>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?= BASE_URL ?>/daily-tasks/view/<?= $task['id'] ?>" 
                               class="btn btn-info" title="Προβολή">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/daily-tasks/edit/<?= $task['id'] ?>" 
                               class="btn btn-warning" title="Επεξεργασία">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" 
                                  action="<?= BASE_URL ?>/daily-tasks/delete/<?= $task['id'] ?>" 
                                  style="display:inline;"
                                  onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή την εργασία;');">
                                <button type="submit" class="btn btn-danger btn-sm" title="Διαγραφή">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                // Build query string for pagination
                $queryParams = $_GET;
                unset($queryParams['page']);
                $queryString = http_build_query($queryParams);
            ?>
                
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $queryString ?>&page=<?= $currentPage - 1 ?>">Προηγούμενη</a>
                </li>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= $queryString ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $queryString ?>&page=<?= $currentPage + 1 ?>">Επόμενη</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
        </div>
    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// AJAX Toggle for is_invoiced
document.querySelectorAll('.toggle-invoiced').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const taskId = this.getAttribute('data-task-id');
        const isInvoiced = this.checked ? 1 : 0;
        
        fetch(`<?= BASE_URL ?>/daily-tasks/toggle-invoiced/${taskId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: isInvoiced })
        })
        .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert checkbox on error
            this.checked = !this.checked;
            alert('Σφάλμα κατά την ενημέρωση');
        }
    })
    .catch(error => {
        // Revert checkbox on error
        this.checked = !this.checked;
        alert('Σφάλμα κατά την ενημέρωση');
    });
});
});
</script>
</div>
