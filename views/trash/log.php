<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-history"></i> Ιστορικό Διαγραφών</h1>
                <a href="?route=/trash" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Επιστροφή στον Κάδο
                </a>
            </div>
            
            <!-- Φίλτρα -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="/trash/log" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Τύπος Στοιχείου</label>
                            <select name="type" class="form-select">
                                <option value="">Όλοι οι Τύποι</option>
                                <option value="project" <?= $type === 'project' ? 'selected' : '' ?>>Έργα</option>
                                <option value="project_task" <?= $type === 'project_task' ? 'selected' : '' ?>>Εργασίες Έργων</option>
                                <option value="task_labor" <?= $type === 'task_labor' ? 'selected' : '' ?>>Ημερομίσθια</option>
                                <option value="daily_task" <?= $type === 'daily_task' ? 'selected' : '' ?>>Ημερήσιες Εργασίες</option>
                                <option value="maintenance" <?= $type === 'maintenance' ? 'selected' : '' ?>>Συντηρήσεις Μ/Σ</option>
                                <option value="material" <?= $type === 'material' ? 'selected' : '' ?>>Υλικά</option>
                            </select>
                        </div>
                        
                        <div class="col-md-5">
                            <label class="form-label">Ενέργεια</label>
                            <select name="action" class="form-select">
                                <option value="">Όλες οι Ενέργειες</option>
                                <option value="deleted" <?= $action === 'deleted' ? 'selected' : '' ?>>Διαγραφή</option>
                                <option value="restored" <?= $action === 'restored' ? 'selected' : '' ?>>Επαναφορά</option>
                                <option value="permanent" <?= $action === 'permanent' ? 'selected' : '' ?>>Οριστική Διαγραφή</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Φιλτράρισμα
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Πίνακας Ιστορικού -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                            <p class="lead">Δεν υπάρχουν καταχωρήσεις στο ιστορικό</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ημερομηνία</th>
                                        <th>Τύπος</th>
                                        <th>Όνομα Στοιχείου</th>
                                        <th>Ενέργεια</th>
                                        <th>Χρήστης</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars(Trash::getTypeLabel($log['item_type'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($log['item_name']) ?></strong>
                                                <?php if (!empty($log['item_details'])): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        ID: <?= $log['item_id'] ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $badgeClass = 'bg-secondary';
                                                switch ($log['action']) {
                                                    case 'deleted':
                                                        $badgeClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'restored':
                                                        $badgeClass = 'bg-success';
                                                        break;
                                                    case 'permanent':
                                                        $badgeClass = 'bg-danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= htmlspecialchars(Trash::getActionLabel($log['action'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= htmlspecialchars($log['user_name']) ?>
                                                </small>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
