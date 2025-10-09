<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-project-diagram"></i> Έργα</h2>
    <a href="?route=/projects/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Νέο Έργο
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="?" class="row g-3">
            <input type="hidden" name="route" value="/projects">
            
            <div class="col-md-3">
                <label class="form-label">Κατάσταση</label>
                <select name="status" class="form-select">
                    <option value="">Όλα</option>
                    <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['status'] === $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Κατηγορία</label>
                <select name="category" class="form-select">
                    <option value="">Όλες</option>
                    <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['category'] === $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Τεχνικός</label>
                <select name="technician" class="form-select">
                    <option value="">Όλοι</option>
                    <?php foreach ($technicians as $tech): ?>
                    <option value="<?= $tech['id'] ?>" <?= ($filters['technician'] == $tech['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Αναζήτηση</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Τίτλος, περιγραφή, πελάτης..." 
                           value="<?= htmlspecialchars($filters['search']) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Projects Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($projects)): ?>
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <p class="text-muted">Δεν βρέθηκαν έργα</p>
            <a href="?route=/projects/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Δημιουργία Νέου Έργου
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Τίτλος</th>
                        <th>Πελάτης</th>
                        <th>Κατηγορία</th>
                        <th>Τεχνικός</th>
                        <th>Κατάσταση</th>
                        <th>Έναρξη</th>
                        <th>Κόστος</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <?php
                        $customerName = $project['customer_type'] === 'company' && !empty($project['customer_company_name']) 
                            ? $project['customer_company_name'] 
                            : $project['customer_first_name'] . ' ' . $project['customer_last_name'];
                        
                        // Status badge colors
                        $statusColors = [
                            'pending' => 'warning',
                            'in_progress' => 'primary',
                            'on_hold' => 'secondary',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $statusColor = $statusColors[$project['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/projects/<?= $project['slug'] ?>" 
                               class="text-decoration-none fw-bold">
                                <?= htmlspecialchars($project['title']) ?>
                            </a>
                            <?php if ($project['priority'] === 'urgent'): ?>
                            <span class="badge bg-danger ms-2">Επείγον</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($customerName) ?></td>
                        <td><?= $categories[$project['category']] ?? $project['category'] ?></td>
                        <td><?= htmlspecialchars($project['tech_first_name'] . ' ' . $project['tech_last_name']) ?></td>
                        <td>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= $statuses[$project['status']] ?? $project['status'] ?>
                            </span>
                        </td>
                        <td>
                            <?= $project['start_date'] ? formatDate($project['start_date']) : '-' ?>
                        </td>
                        <td>
                            <?php 
                            $totalCost = ($project['material_cost'] ?? 0) + ($project['labor_cost'] ?? 0);
                            if ($totalCost > 0): 
                            ?>
                                <?= number_format($totalCost, 2) ?>€
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?= BASE_URL ?>/projects/<?= $project['slug'] ?>" 
                                   class="btn btn-sm btn-info" title="Προβολή">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/projects/edit/<?= $project['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Επεξεργασία">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($user['role'] === 'admin'): ?>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $project['id'] ?>)" title="Διαγραφή">
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
                    <a class="page-link" href="?route=/projects&page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= ($i === $pagination['current_page']) ? 'active' : '' ?>">
                    <a class="page-link" href="?route=/projects&page=<?= $i ?>&<?= http_build_query($filters) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?route=/projects&page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query($filters) ?>">
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
<form id="deleteForm" method="POST" action="?route=/projects/delete">
    <input type="hidden" name="id" id="deleteProjectId">
</form>

<script>
function confirmDelete(projectId) {
    if (confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το έργο;')) {
        document.getElementById('deleteProjectId').value = projectId;
        document.getElementById('deleteForm').submit();
    }
}
</script>
