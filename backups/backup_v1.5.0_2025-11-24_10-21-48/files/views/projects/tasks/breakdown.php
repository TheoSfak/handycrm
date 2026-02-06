<?php
/**
 * Task Daily Breakdown View
 * Shows daily split of costs for date range tasks
 */

$pageTitle = $title ?? 'Ανάλυση ανά Ημέρα';
require_once __DIR__ . '/../../includes/header.php';
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
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/view/<?= $task['id'] ?>">
                    <?= htmlspecialchars($task['description']) ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Ανάλυση</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3>
                <i class="fas fa-chart-bar text-info me-2"></i>
                Ανάλυση ανά Ημέρα
            </h3>
            <p class="text-muted mb-0">
                <?= htmlspecialchars($task['description']) ?>
                <br>
                <?php
                // Calculate total days
                if (!empty($task['date_from']) && !empty($task['date_to'])) {
                    $dateFrom = new DateTime($task['date_from']);
                    $dateTo = new DateTime($task['date_to']);
                    $totalDays = $dateTo->diff($dateFrom)->days + 1;
                } else {
                    $totalDays = 1;
                }
                ?>
                <small>
                    <?= date('d/m/Y', strtotime($task['date_from'])) ?> - 
                    <?= date('d/m/Y', strtotime($task['date_to'])) ?>
                    (<?= $totalDays ?> ημέρες)
                </small>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/view/<?= $task['id'] ?>" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Πίσω
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0"><?= $totalDays ?></h3>
                    <small class="text-muted">Ημέρες</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0"><?= number_format($task['materials_total'] ?? 0, 2) ?> €</h3>
                    <small class="text-muted">Σύνολο Υλικών</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-hard-hat fa-2x text-info mb-2"></i>
                    <h3 class="mb-0"><?= number_format($task['labor_total'] ?? 0, 2) ?> €</h3>
                    <small class="text-muted">Σύνολο Εργατικών</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-2x text-success mb-2"></i>
                    <?php
                    $totalCost = ($task['materials_total'] ?? 0) + ($task['labor_total'] ?? 0);
                    $dailyAverage = $totalDays > 0 ? $totalCost / $totalDays : 0;
                    ?>
                    <h3 class="mb-0"><?= number_format($dailyAverage, 2) ?> €</h3>
                    <small class="text-muted">Μέσος Όρος/Ημέρα</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown Table -->
    <div class="card shadow">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>
                Κόστος ανά Ημέρα
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($breakdown)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th width="20%">Ημερομηνία</th>
                                <th width="15%">Ημέρα</th>
                                <th width="20%" class="text-end">Υλικά</th>
                                <th width="20%" class="text-end">Εργατικά</th>
                                <th width="20%" class="text-end">Σύνολο</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $dayNumber = 1;
                            $runningTotal = 0;
                            foreach ($breakdown as $day): 
                                $dayTotal = $day['materials'] + $day['labor'];
                                $runningTotal += $dayTotal;
                            ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= $dayNumber++ ?></span>
                                    </td>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($day['date'])) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $greekDays = [
                                            'Monday' => 'Δευτέρα',
                                            'Tuesday' => 'Τρίτη',
                                            'Wednesday' => 'Τετάρτη',
                                            'Thursday' => 'Πέμπτη',
                                            'Friday' => 'Παρασκευή',
                                            'Saturday' => 'Σάββατο',
                                            'Sunday' => 'Κυριακή'
                                        ];
                                        $dayName = date('l', strtotime($day['date']));
                                        echo $greekDays[$dayName] ?? $dayName;
                                        ?>
                                        <?php if (date('N', strtotime($day['date'])) >= 6): ?>
                                            <span class="badge bg-warning text-dark ms-1">Σ/Κ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-warning">
                                            <?= number_format($day['materials'], 2) ?> €
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-info">
                                            <?= number_format($day['labor'], 2) ?> €
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            <?= number_format($dayTotal, 2) ?> €
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="3" class="text-end">Σύνολο (<?= count($breakdown) ?> ημέρες)</th>
                                <th class="text-end text-warning">
                                    <?= number_format($task['materials_total'] ?? 0, 2) ?> €
                                </th>
                                <th class="text-end text-info">
                                    <?= number_format($task['labor_total'] ?? 0, 2) ?> €
                                </th>
                                <th class="text-end text-success">
                                    <strong><?= number_format(($task['materials_total'] ?? 0) + ($task['labor_total'] ?? 0), 2) ?> €</strong>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Chart Section -->
                <hr>
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h5 class="text-center mb-3">Γράφημα Κόστους ανά Ημέρα</h5>
                        <canvas id="breakdownChart" height="100"></canvas>
                    </div>
                </div>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Δεν υπάρχουν δεδομένα για ανάλυση</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($breakdown)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare chart data
const chartData = {
    labels: [
        <?php foreach ($breakdown as $day): ?>
            '<?= date('d/m', strtotime($day['date'])) ?>',
        <?php endforeach; ?>
    ],
    datasets: [
        {
            label: 'Υλικά',
            data: [<?= implode(',', array_column($breakdown, 'materials')) ?>],
            backgroundColor: 'rgba(255, 193, 7, 0.5)',
            borderColor: 'rgba(255, 193, 7, 1)',
            borderWidth: 2
        },
        {
            label: 'Εργατικά',
            data: [<?= implode(',', array_column($breakdown, 'labor')) ?>],
            backgroundColor: 'rgba(13, 202, 240, 0.5)',
            borderColor: 'rgba(13, 202, 240, 1)',
            borderWidth: 2
        }
    ]
};

// Create chart
const ctx = document.getElementById('breakdownChart').getContext('2d');
const breakdownChart = new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
        responsive: true,
        scales: {
            x: {
                stacked: true
            },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toFixed(2) + ' €';
                    }
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' €';
                    }
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
