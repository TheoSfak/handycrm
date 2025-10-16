<?php
/**
 * Project Tasks Index View
 * Displays all tasks for a project with filters and summary
 */

// Check if we're in a tab context (included from show.php)
if (!isset($isTabView)) {
    $isTabView = false;
    $pageTitle = $title ?? 'Εργασίες Έργου';
    require_once __DIR__ . '/../../includes/header.php';
}
?>

<?php if (!$isTabView): ?>
<div class="container-fluid py-4">
    <!-- Project Context Header -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects">
                    <i class="fas fa-briefcase me-1"></i>Έργα
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects/<?= $project['slug'] ?>">
                    <?= htmlspecialchars($project['title'] ?? $project['name'] ?? 'Project') ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Εργασίες</li>
        </ol>
    </nav>
<?php endif; ?>

    <!-- Summary Cards -->
    <?php if ($summary): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0"><?= $summary['total_tasks'] ?></h3>
                    <small class="text-muted">Σύνολο Εργασιών</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0"><?= number_format($summary['materials_total'] ?? 0, 2) ?> €</h3>
                    <small class="text-muted">Υλικά</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                    <h3 class="mb-0"><?= number_format($summary['labor_total'] ?? 0, 2) ?> €</h3>
                    <small class="text-muted">Εργατικά</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-2x text-success mb-2"></i>
                    <h3 class="mb-0"><?= number_format($summary['grand_total'] ?? 0, 2) ?> €</h3>
                    <small class="text-muted">Συνολικό Κόστος</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Header with Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>
            <i class="fas fa-list me-2"></i>Λίστα Εργασιών
        </h4>
        <div>
            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/export-csv" class="btn btn-success me-2">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Νέα Εργασία
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Τύπος Εργασίας</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Όλες</option>
                        <option value="single_day" <?= (isset($_GET['type']) && $_GET['type'] === 'single_day') ? 'selected' : '' ?>>
                            Μονοήμερες
                        </option>
                        <option value="date_range" <?= (isset($_GET['type']) && $_GET['type'] === 'date_range') ? 'selected' : '' ?>>
                            Εύρος Ημερομηνιών
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Από Ημερομηνία</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" 
                           value="<?= $_GET['date_from'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Έως Ημερομηνία</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" 
                           value="<?= $_GET['date_to'] ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Ταξινόμηση</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="date_desc" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'date_desc') ? 'selected' : '' ?>>
                            Νεότερες Πρώτα
                        </option>
                        <option value="date_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'date_asc') ? 'selected' : '' ?>>
                            Παλαιότερες Πρώτα
                        </option>
                        <option value="cost_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'cost_desc') ? 'selected' : '' ?>>
                            Κόστος (Φθίνουσα)
                        </option>
                        <option value="cost_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'cost_asc') ? 'selected' : '' ?>>
                            Κόστος (Αύξουσα)
                        </option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-filter me-1"></i>Εφαρμογή Φίλτρων
                    </button>
                    <?php if (!empty($_GET)): ?>
                        <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times me-1"></i>Καθαρισμός
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card shadow">
        <div class="card-body">
            <?php if (!empty($tasks)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ημερομηνία</th>
                                <th>Τύπος</th>
                                <th>Περιγραφή</th>
                                <th class="text-end"><i class="fas fa-boxes text-warning me-1"></i>Υλικά</th>
                                <th class="text-end"><i class="fas fa-users text-info me-1"></i>Εργατικά</th>
                                <th class="text-end">Σύνολο</th>
                                <th class="text-center">Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <?php
                                $totalCost = $task['materials_total'] + $task['labor_total'];
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($task['task_type'] === 'single_day'): ?>
                                            <i class="fas fa-calendar-day text-primary me-1"></i>
                                            <?= date('d/m/Y', strtotime($task['task_date'])) ?>
                                        <?php else: ?>
                                            <i class="fas fa-calendar-week text-info me-1"></i>
                                            <?= date('d/m/Y', strtotime($task['date_from'])) ?>
                                            <br>
                                            <small class="text-muted">έως <?= date('d/m/Y', strtotime($task['date_to'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($task['task_type'] === 'single_day'): ?>
                                            <span class="badge bg-primary">Μονοήμερη</span>
                                        <?php else: ?>
                                            <?php 
                                                // Calculate total days if not provided
                                                if (!isset($task['total_days']) && !empty($task['date_from']) && !empty($task['date_to'])) {
                                                    $dateFrom = new DateTime($task['date_from']);
                                                    $dateTo = new DateTime($task['date_to']);
                                                    $totalDays = $dateTo->diff($dateFrom)->days + 1;
                                                } else {
                                                    $totalDays = $task['total_days'] ?? 0;
                                                }
                                            ?>
                                            <span class="badge bg-info">
                                                <?= $totalDays ?> ημέρες
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($task['description']) ?></strong>
                                        <?php if ($task['task_type'] === 'date_range'): ?>
                                            <?php 
                                                // Calculate daily average if not provided
                                                if (!isset($task['daily_average'])) {
                                                    $totalCost = ($task['materials_total'] ?? 0) + ($task['labor_total'] ?? 0);
                                                    $dailyAverage = $totalDays > 0 ? $totalCost / $totalDays : 0;
                                                } else {
                                                    $dailyAverage = $task['daily_average'];
                                                }
                                            ?>
                                            <br>
                                            <small class="text-muted">
                                                Κόστος/ημέρα: <?= number_format($dailyAverage, 2) ?> €
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-warning">
                                            <?= number_format($task['materials_total'], 2) ?> €
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-info">
                                            <?= number_format($task['labor_total'], 2) ?> €
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            <?= number_format($totalCost, 2) ?> €
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/view/<?= $task['id'] ?>" 
                                               class="btn btn-outline-primary" 
                                               title="Προβολή">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($task['task_type'] === 'date_range'): ?>
                                                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/<?= $task['id'] ?>/breakdown" 
                                                   class="btn btn-outline-info" 
                                                   title="Ανάλυση">
                                                    <i class="fas fa-chart-bar"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/edit/<?= $task['id'] ?>" 
                                               class="btn btn-outline-warning" 
                                               title="Επεξεργασία">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-secondary" 
                                                    title="Αντιγραφή"
                                                    onclick="copyTask(<?= $task['id'] ?>)">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    title="Διαγραφή"
                                                    onclick="deleteTask(<?= $task['id'] ?>, '<?= htmlspecialchars(addslashes($task['description'])) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3">Σύνολο (<?= count($tasks) ?> εργασίες)</th>
                                <th class="text-end text-warning">
                                    <?= number_format(array_sum(array_column($tasks, 'materials_total')), 2) ?> €
                                </th>
                                <th class="text-end text-info">
                                    <?= number_format(array_sum(array_column($tasks, 'labor_total')), 2) ?> €
                                </th>
                                <th class="text-end text-success">
                                    <strong>
                                        <?= number_format(
                                            array_sum(array_column($tasks, 'materials_total')) + 
                                            array_sum(array_column($tasks, 'labor_total')), 
                                            2
                                        ) ?> €
                                    </strong>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Δεν υπάρχουν εργασίες για αυτό το έργο</h5>
                    <p class="text-muted">Δημιουργήστε την πρώτη εργασία για να ξεκινήσετε την παρακολούθηση</p>
                    <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/add" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Προσθήκη Εργασίας
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php if (!$isTabView): ?>
</div>
<?php endif; ?>

<!-- Copy Task Form (Hidden) -->
<form id="copyTaskForm" method="POST" action="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/copy" style="display:none;">
    <input type="hidden" name="task_id" id="copyTaskId">
</form>

<!-- Delete Task Form (Hidden) -->
<form id="deleteTaskForm" method="POST" action="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/delete" style="display:none;">
    <input type="hidden" name="task_id" id="deleteTaskId">
</form>

<script>
function copyTask(taskId) {
    if (confirm('Θέλετε να αντιγράψετε αυτή την εργασία;\n\nΘα δημιουργηθεί μια νέα εργασία με τα ίδια υλικά και εργατικά.')) {
        document.getElementById('copyTaskId').value = taskId;
        document.getElementById('copyTaskForm').submit();
    }
}

function deleteTask(taskId, description) {
    if (confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε την εργασία:\n\n"' + description + '"\n\nΑυτή η ενέργεια δεν μπορεί να αναιρεθεί!')) {
        document.getElementById('deleteTaskId').value = taskId;
        document.getElementById('deleteTaskForm').submit();
    }
}
</script>

<?php if (!$isTabView): ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php endif; ?>
