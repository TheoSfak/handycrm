<?php
/**
 * View Project Task Details (Readonly)
 */

$pageTitle = $title ?? 'Προβολή Εργασίας';
require_once __DIR__ . '/../../includes/header.php';

$totalCost = $task['materials_total'] + $task['labor_total'];
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects"><i class="fas fa-briefcase me-1"></i>Έργα</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects/<?= $project['slug'] ?>">
                    <?= htmlspecialchars($project['title'] ?? $project['name'] ?? 'Project') ?>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks">Εργασίες</a>
            </li>
            <li class="breadcrumb-item active">Προβολή</li>
        </ol>
    </nav>

    <!-- Header with Actions -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3>
                <?php if ($task['task_type'] === 'single_day'): ?>
                    <i class="fas fa-calendar-day text-primary me-2"></i>
                <?php else: ?>
                    <i class="fas fa-calendar-week text-info me-2"></i>
                <?php endif; ?>
                <?= htmlspecialchars($task['description']) ?>
            </h3>
            <p class="text-muted mb-0">
                <?php if ($task['task_type'] === 'single_day'): ?>
                    <?= date('d/m/Y', strtotime($task['task_date'])) ?>
                <?php else: ?>
                    <?= date('d/m/Y', strtotime($task['date_from'])) ?> - <?= date('d/m/Y', strtotime($task['date_to'])) ?>
                    (<?= $task['total_days'] ?> ημέρες)
                <?php endif; ?>
            </p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/<?= $task['id'] ?>/photos" 
               class="btn btn-primary">
                <i class="fas fa-camera me-2"></i>Φωτογραφίες
            </a>
            <?php if ($task['task_type'] === 'date_range'): ?>
                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/<?= $task['id'] ?>/breakdown" 
                   class="btn btn-info">
                    <i class="fas fa-chart-bar me-2"></i>Ανάλυση
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/edit/<?= $task['id'] ?>" 
               class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Επεξεργασία
            </a>
            <button class="btn btn-secondary" onclick="copyTask(<?= $task['id'] ?>)">
                <i class="fas fa-copy me-2"></i>Αντιγραφή
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Materials & Labor -->
        <div class="col-lg-8">
            <!-- Materials Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Υλικά</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($materials)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Περιγραφή</th>
                                        <th class="text-end">Τιμή Μονάδας</th>
                                        <th class="text-center">Ποσότητα</th>
                                        <th class="text-center">Μονάδα</th>
                                        <th class="text-end">Σύνολο</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materials as $material): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($material['description']) ?></td>
                                            <td class="text-end"><?= number_format($material['unit_price'], 2) ?> €</td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?= number_format($material['quantity'], 2) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">
                                                    <?php
                                                    $units = [
                                                        'meters' => 'μέτρα',
                                                        'pieces' => 'τεμάχια',
                                                        'kg' => 'κιλά',
                                                        'liters' => 'λίτρα',
                                                        'boxes' => 'κουτιά',
                                                        'other' => 'άλλο'
                                                    ];
                                                    echo $units[$material['unit_type']] ?? $material['unit_type'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-warning"><?= number_format($material['subtotal'], 2) ?> €</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-warning">
                                    <tr>
                                        <th colspan="4" class="text-end">Σύνολο Υλικών:</th>
                                        <th class="text-end"><?= number_format($task['materials_total'], 2) ?> €</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">Δεν υπάρχουν υλικά σε αυτή την εργασία</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Labor Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user-hard-hat me-2"></i>Εργατικά</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($labor)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Τεχνικός</th>
                                        <th class="text-center">Ώρες</th>
                                        <th class="text-center">Χρόνος</th>
                                        <th class="text-end">Τιμή/Ώρα</th>
                                        <th class="text-end">Σύνολο</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($labor as $entry): ?>
                                        <tr>
                                            <td>
                                                <?php if ($entry['technician_name']): ?>
                                                    <i class="fas fa-user me-2"></i>
                                                    <?= htmlspecialchars($entry['technician_name']) ?>
                                                    <?php if (!empty($entry['technician_role_display'])): ?>
                                                        <span class="badge bg-primary ms-2">
                                                            <?= htmlspecialchars($entry['technician_role_display']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Άλλο Εργατικό</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-dark"><?= number_format($entry['hours_worked'], 1) ?>h</span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($entry['time_from'] && $entry['time_to']): ?>
                                                    <small class="text-muted">
                                                        <?= substr($entry['time_from'], 0, 5) ?> - <?= substr($entry['time_to'], 0, 5) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end"><?= number_format($entry['hourly_rate'], 2) ?> €</td>
                                            <td class="text-end">
                                                <strong class="text-info"><?= number_format($entry['subtotal'], 2) ?> €</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-info">
                                    <tr>
                                        <th colspan="4" class="text-end">Σύνολο Εργατικών:</th>
                                        <th class="text-end"><?= number_format($task['labor_total'], 2) ?> €</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">Δεν υπάρχουν εργατικά σε αυτή την εργασία</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Photos Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Φωτογραφίες</h5>
                        <span class="badge bg-light text-primary"><?= $totalPhotos ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentPhotos)): ?>
                        <div class="row g-2 mb-3">
                            <?php foreach ($recentPhotos as $photo): ?>
                                <div class="col-2">
                                    <div class="photo-thumbnail-wrapper" style="position: relative;">
                                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($photo['file_path']) ?>" 
                                           target="_blank" 
                                           style="display: block; aspect-ratio: 1; overflow: hidden; border-radius: 6px; border: 1px solid #e0e0e0;">
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($photo['file_path']) ?>" 
                                                 alt="<?= htmlspecialchars($photo['caption']) ?>"
                                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.2s;"
                                                 onmouseover="this.style.transform='scale(1.1)'" 
                                                 onmouseout="this.style.transform='scale(1)'">
                                        </a>
                                        <form action="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/<?= $task['id'] ?>/photos/<?= $photo['id'] ?>/delete" 
                                              method="POST" 
                                              class="photo-delete-form"
                                              onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή τη φωτογραφία;');">
                                            <button type="submit" 
                                                    class="btn btn-danger btn-sm"
                                                    title="Διαγραφή">
                                                <i class="fas fa-trash" style="font-size: 10px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center">
                            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/<?= $task['id'] ?>/photos" 
                               class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-images me-2"></i>Προβολή όλων (<?= $totalPhotos ?>)
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-camera text-muted mb-2" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mb-3">Δεν υπάρχουν φωτογραφίες</p>
                            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/<?= $task['id'] ?>/photos" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-2"></i>Προσθήκη Φωτογραφιών
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary & Info -->
        <div class="col-lg-4">
            <!-- Cost Summary Card -->
            <div class="card shadow border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Συνολικό Κόστος</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Υλικά:</span>
                            <h4 class="text-warning mb-0"><?= number_format($task['materials_total'], 2) ?> €</h4>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: <?= $totalCost > 0 ? ($task['materials_total'] / $totalCost * 100) : 0 ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Εργατικά:</span>
                            <h4 class="text-info mb-0"><?= number_format($task['labor_total'], 2) ?> €</h4>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: <?= $totalCost > 0 ? ($task['labor_total'] / $totalCost * 100) : 0 ?>%"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <small class="text-muted d-block mb-2">ΓΕΝΙΚΟ ΣΥΝΟΛΟ</small>
                        <h2 class="text-success mb-0"><?= number_format($totalCost, 2) ?> €</h2>
                    </div>

                    <?php if ($task['task_type'] === 'date_range'): ?>
                        <hr>
                        <div class="text-center">
                            <small class="text-muted d-block">Μέσος Όρος ανά Ημέρα</small>
                            <h4 class="mb-0"><?= number_format($task['daily_average'], 2) ?> €</h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Additional Info Card -->
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Πληροφορίες</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tbody>
                            <tr>
                                <th width="40%">Τύπος:</th>
                                <td>
                                    <?php if ($task['task_type'] === 'single_day'): ?>
                                        <span class="badge bg-primary">Μονοήμερη</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Εύρος Ημερομηνιών</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($task['task_type'] === 'date_range'): ?>
                                <tr>
                                    <th>Διάρκεια:</th>
                                    <td><?= $task['total_days'] ?> ημέρες</td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Υλικά:</th>
                                <td><?= count($materials) ?> στοιχεία</td>
                            </tr>
                            <tr>
                                <th>Εργατικά:</th>
                                <td><?= count($labor) ?> εγγραφές</td>
                            </tr>
                            <tr>
                                <th>Δημιουργήθηκε:</th>
                                <td><?= date('d/m/Y H:i', strtotime($task['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <th>Τελευταία Ενημέρωση:</th>
                                <td><?= date('d/m/Y H:i', strtotime($task['updated_at'])) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Copy Task Form (Hidden) -->
<form id="copyTaskForm" method="POST" action="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/copy" style="display:none;">
    <input type="hidden" name="task_id" id="copyTaskId">
</form>

<style>
.photo-delete-form {
    position: absolute;
    top: 5px;
    right: 5px;
    opacity: 0;
    transition: opacity 0.2s;
    z-index: 10;
}

.photo-thumbnail-wrapper:hover .photo-delete-form {
    opacity: 1;
}

.photo-delete-form button {
    padding: 4px 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
</style>

<script>
function copyTask(taskId) {
    if (confirm('Θέλετε να αντιγράψετε αυτή την εργασία;\n\nΘα δημιουργηθεί μια νέα εργασία με τα ίδια υλικά και εργατικά.')) {
        document.getElementById('copyTaskId').value = taskId;
        document.getElementById('copyTaskForm').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

