<?php
/**
 * Project Statistics View
 * Shows detailed analytics and insights for a project
 */

// Get statistics
$stats = $statistics ?? [];
?>

<div class="container-fluid">
    <?php if (empty($stats) || $stats['total_tasks'] == 0): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Δεν υπάρχουν αρκετά δεδομένα για στατιστικά. Προσθέστε εργασίες για να δείτε αναλυτικά στατιστικά.
        </div>
    <?php else: ?>
        
        <!-- Overview Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-tasks fa-2x text-primary mb-2"></i>
                        <h3 class="mb-0"><?= $stats['total_tasks'] ?></h3>
                        <small class="text-muted">Εργασίες</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-euro-sign fa-2x text-success mb-2"></i>
                        <h3 class="mb-0"><?= number_format($stats['total_cost'], 2) ?> €</h3>
                        <small class="text-muted">Συνολικό Κόστος</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                        <h3 class="mb-0"><?= number_format($stats['total_hours'], 1) ?></h3>
                        <small class="text-muted">Σύνολο Ωρών</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-2x text-warning mb-2"></i>
                        <h3 class="mb-0"><?= $stats['total_days'] ?></h3>
                        <small class="text-muted">Ημέρες Εργασίας</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 1: Cost Breakdown & Technicians -->
        <div class="row mb-4">
            <!-- Cost Breakdown Chart -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Κατανομή Κόστους</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <canvas id="costPieChart" style="max-height: 200px;"></canvas>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-boxes text-warning me-2"></i>Υλικά</span>
                                        <strong><?= number_format($stats['materials_total'], 2) ?> €</strong>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-warning" style="width: <?= $stats['materials_percentage'] ?>%">
                                            <?= number_format($stats['materials_percentage'], 1) ?>%
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span><i class="fas fa-user-hard-hat text-info me-2"></i>Εργατικά</span>
                                        <strong><?= number_format($stats['labor_total'], 2) ?> €</strong>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-info" style="width: <?= $stats['labor_percentage'] ?>%">
                                            <?= number_format($stats['labor_percentage'], 1) ?>%
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Μέσο Κόστος/Εργασία:</strong>
                                    <strong class="text-success"><?= number_format($stats['avg_cost_per_task'], 2) ?> €</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Μέσο Κόστος/Ημέρα:</strong>
                                    <strong class="text-primary"><?= number_format($stats['avg_cost_per_day'], 2) ?> €</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Technicians -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Κατάταξη Τεχνικών</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($stats['technicians'])): ?>
                            <p class="text-muted text-center py-3">Δεν υπάρχουν δεδομένα εργατικών</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Τεχνικός</th>
                                            <th class="text-center">Ώρες</th>
                                            <th class="text-end">Κόστος</th>
                                            <th class="text-center">Εργασίες</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank = 1;
                                        foreach ($stats['technicians'] as $tech): 
                                            $roleIcons = [
                                                'technician' => 'fa-wrench text-primary',
                                                'assistant' => 'fa-user-cog text-info',
                                                'admin' => 'fa-user-shield text-warning'
                                            ];
                                            $icon = $roleIcons[$tech['role']] ?? 'fa-user text-secondary';
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php if ($rank <= 3): ?>
                                                        <span class="badge bg-<?= $rank == 1 ? 'warning' : ($rank == 2 ? 'secondary' : 'danger') ?> me-1">
                                                            #<?= $rank ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <i class="fas <?= $icon ?> me-1"></i>
                                                    <?= htmlspecialchars($tech['name']) ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary"><?= number_format($tech['total_hours'], 1) ?>h</span>
                                                </td>
                                                <td class="text-end">
                                                    <strong><?= number_format($tech['total_cost'], 2) ?> €</strong>
                                                </td>
                                                <td class="text-center">
                                                    <?= $tech['tasks_count'] ?>
                                                </td>
                                            </tr>
                                        <?php 
                                            $rank++;
                                        endforeach; 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Task Types & Weekday Distribution -->
        <div class="row mb-4">
            <!-- Task Types -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Τύποι Εργασιών</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-calendar-day fa-3x text-primary mb-2"></i>
                                    <h3><?= $stats['single_day_tasks'] ?></h3>
                                    <small class="text-muted">Μονοήμερες</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-calendar-week fa-3x text-info mb-2"></i>
                                    <h3><?= $stats['date_range_tasks'] ?></h3>
                                    <small class="text-muted">Πολυήμερες</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekday Distribution -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Κατανομή ανά Ημέρα</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="weekdayChart" style="max-height: 200px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Top Expensive Tasks -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Top 5 Πιο Ακριβές Εργασίες</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Περιγραφή</th>
                                        <th width="15%">Ημερομηνία</th>
                                        <th width="15%" class="text-end">Κόστος</th>
                                        <th width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    foreach ($stats['top_expensive_tasks'] as $task): 
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?= $rank == 1 ? 'danger' : 'secondary' ?>">
                                                    <?= $rank ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($task['description']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($task['date'])) ?></td>
                                            <td class="text-end">
                                                <strong class="text-danger"><?= number_format($task['cost'], 2) ?> €</strong>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/view/<?= $task['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                        $rank++;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
<?php if (!empty($stats) && $stats['total_tasks'] > 0): ?>
// Cost Pie Chart
const ctxPie = document.getElementById('costPieChart').getContext('2d');
new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: ['Υλικά', 'Εργατικά'],
        datasets: [{
            data: [<?= $stats['materials_total'] ?>, <?= $stats['labor_total'] ?>],
            backgroundColor: ['#ffc107', '#17a2b8'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Weekday Bar Chart
const ctxWeekday = document.getElementById('weekdayChart').getContext('2d');
const greekDays = {
    'Monday': 'Δευτέρα',
    'Tuesday': 'Τρίτη',
    'Wednesday': 'Τετάρτη',
    'Thursday': 'Πέμπτη',
    'Friday': 'Παρασκευή',
    'Saturday': 'Σάββατο',
    'Sunday': 'Κυριακή'
};

new Chart(ctxWeekday, {
    type: 'bar',
    data: {
        labels: Object.keys(greekDays).map(key => greekDays[key]),
        datasets: [{
            label: 'Εργασίες',
            data: [
                <?= $stats['tasks_by_weekday']['Monday'] ?>,
                <?= $stats['tasks_by_weekday']['Tuesday'] ?>,
                <?= $stats['tasks_by_weekday']['Wednesday'] ?>,
                <?= $stats['tasks_by_weekday']['Thursday'] ?>,
                <?= $stats['tasks_by_weekday']['Friday'] ?>,
                <?= $stats['tasks_by_weekday']['Saturday'] ?>,
                <?= $stats['tasks_by_weekday']['Sunday'] ?>
            ],
            backgroundColor: [
                '#007bff', '#007bff', '#007bff', '#007bff', '#007bff', '#ffc107', '#ffc107'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
<?php endif; ?>
</script>
