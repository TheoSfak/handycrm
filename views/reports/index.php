<?php require_once 'views/includes/header.php'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-chart-line"></i> Αναφορές & Στατιστικά</h2>
        <p class="text-muted mb-0">Ανάλυση δεδομένων και insights</p>
    </div>
    <div>
        <!-- Date Range Filter -->
        <form method="GET" action="?route=/reports" class="d-flex gap-2">
            <input type="hidden" name="route" value="/reports">
            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" required>
            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" required>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Φίλτρο
            </button>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-euro-sign fa-2x text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small fw-bold">ΣΥΝΟΛΙΚΑ ΕΣΟΔΑ</div>
                        <div class="h4 mb-0 text-success"><?= formatCurrency($summary['total_revenue']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-project-diagram fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small fw-bold">ΣΥΝΟΛΟ ΕΡΓΩΝ</div>
                        <div class="h4 mb-0"><?= number_format($summary['total_projects']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small fw-bold">ΕΝΕΡΓΟΙ ΠΕΛΑΤΕΣ</div>
                        <div class="h4 mb-0"><?= number_format($summary['active_customers']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-percentage fa-2x text-warning"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small fw-bold">CONVERSION RATE</div>
                        <div class="h4 mb-0"><?= number_format($summary['quote_conversion_rate'], 1) ?>%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row mb-4">
    <!-- Revenue Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-area"></i> Έσοδα ανά Μήνα</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Project Status Chart -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Κατάσταση Έργων</h5>
            </div>
            <div class="card-body">
                <canvas id="projectStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row mb-4">
    <!-- Category Breakdown -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Έσοδα ανά Κατηγορία</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Customer Growth -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Νέοι Πελάτες</h5>
            </div>
            <div class="card-body">
                <canvas id="customerGrowthChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Customers Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 10 Πελάτες (ανά Έσοδα)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($customer_data['top_customers'])): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>Δεν υπάρχουν δεδομένα πελατών για την επιλεγμένη περίοδο</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Πελάτης</th>
                                    <th>Τύπος</th>
                                    <th class="text-center">Τιμολόγια</th>
                                    <th class="text-end">Συνολικά Έσοδα</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customer_data['top_customers'] as $index => $customer): ?>
                                <tr>
                                    <td>
                                        <?php if ($index < 3): ?>
                                            <span class="badge bg-warning">
                                                <?php if ($index == 0): ?><i class="fas fa-trophy"></i><?php endif; ?>
                                                <?php if ($index == 1): ?><i class="fas fa-medal"></i><?php endif; ?>
                                                <?php if ($index == 2): ?><i class="fas fa-award"></i><?php endif; ?>
                                                <?= $index + 1 ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted"><?= $index + 1 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?route=/customers/show&id=<?= $customer['id'] ?>" class="text-decoration-none">
                                            <i class="fas fa-<?= $customer['customer_type'] === 'company' ? 'building' : 'user' ?>"></i>
                                            <?= htmlspecialchars($customer['customer_name']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $customer['customer_type'] === 'company' ? 'info' : 'primary' ?>">
                                            <?= $customer['customer_type'] === 'company' ? 'Εταιρεία' : 'Ιδιώτης' ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= $customer['total_invoices'] ?></strong>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success"><?= formatCurrency($customer['total_revenue']) ?></strong>
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

<!-- Technician Performance -->
<?php if (!empty($technician_data)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-user-cog"></i> Απόδοση Τεχνικών</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Τεχνικός</th>
                                <th class="text-center">Συνολικά Έργα</th>
                                <th class="text-center">Ολοκληρωμένα</th>
                                <th class="text-center">Ποσοστό Ολοκλήρωσης</th>
                                <th class="text-center">Μέσος Χρόνος</th>
                                <th class="text-end">Συνολικά Έσοδα</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($technician_data as $tech): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-user"></i>
                                    <?= htmlspecialchars($tech['technician_name']) ?>
                                </td>
                                <td class="text-center"><?= $tech['total_projects'] ?></td>
                                <td class="text-center"><?= $tech['completed_projects'] ?></td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?= $tech['completion_rate'] ?>%">
                                            <?= number_format($tech['completion_rate'], 1) ?>%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?= $tech['avg_completion_days'] ? round($tech['avg_completion_days']) . ' ημέρες' : 'N/A' ?>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success"><?= formatCurrency($tech['total_revenue']) ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Project Statistics -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                <h3 class="text-primary"><?= round($project_data['avg_duration']) ?> ημέρες</h3>
                <p class="text-muted mb-0">Μέσος Χρόνος Ολοκλήρωσης</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h3 class="text-success"><?= $project_data['completion_rate']['rate'] ?>%</h3>
                <p class="text-muted mb-0">Ποσοστό Ολοκλήρωσης Έργων</p>
                <small class="text-muted">
                    (<?= $project_data['completion_rate']['completed'] ?> από <?= $project_data['completion_rate']['total'] ?>)
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 text-center">
            <div class="card-body">
                <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                <h3 class="text-info"><?= number_format($summary['total_appointments']) ?></h3>
                <p class="text-muted mb-0">Συνολικά Ραντεβού</p>
            </div>
        </div>
    </div>
</div>

<!-- Add spacing before footer -->
<div class="mb-5"></div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($revenue_data, 'month')) ?>,
        datasets: [{
            label: 'Συνολικά Έσοδα (με ΦΠΑ)',
            data: <?= json_encode(array_column($revenue_data, 'total_revenue')) ?>,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Καθαρά Έσοδα (χωρίς ΦΠΑ)',
            data: <?= json_encode(array_column($revenue_data, 'subtotal')) ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'ΦΠΑ',
            data: <?= json_encode(array_column($revenue_data, 'vat_amount')) ?>,
            borderColor: '#ffc107',
            backgroundColor: 'rgba(255, 193, 7, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': €' + context.parsed.y.toLocaleString('el-GR', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '€' + value.toLocaleString('el-GR');
                    }
                }
            }
        }
    }
});

// Project Status Chart
const statusCtx = document.getElementById('projectStatusChart').getContext('2d');
const statusData = <?= json_encode($project_data['status_breakdown']) ?>;
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusData.map(s => {
            const labels = {
                'new': 'Νέο',
                'in_progress': 'Σε Εξέλιξη',
                'completed': 'Ολοκληρωμένο',
                'invoiced': 'Τιμολογημένο',
                'cancelled': 'Ακυρωμένο'
            };
            return labels[s.status] || s.status;
        }),
        datasets: [{
            data: statusData.map(s => s.count),
            backgroundColor: [
                '#17a2b8',
                '#ffc107',
                '#28a745',
                '#6f42c1',
                '#dc3545'
            ]
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

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryData = <?= json_encode($project_data['category_breakdown']) ?>;
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: categoryData.map(c => {
            const labels = {
                'electrical': 'Ηλεκτρολογικά',
                'plumbing': 'Υδραυλικά',
                'maintenance': 'Συντήρηση',
                'emergency': 'Επείγον'
            };
            return labels[c.category] || c.category;
        }),
        datasets: [{
            label: 'Έσοδα (€)',
            data: categoryData.map(c => c.total_revenue),
            backgroundColor: [
                '#ff6b35',
                '#4dabf7',
                '#69db7c',
                '#f03e3e'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Έσοδα: €' + context.parsed.y.toLocaleString('el-GR', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '€' + value.toLocaleString('el-GR');
                    }
                }
            }
        }
    }
});

// Customer Growth Chart
const customerGrowthCtx = document.getElementById('customerGrowthChart').getContext('2d');
const customerGrowth = <?= json_encode($customer_data['growth']) ?>;
new Chart(customerGrowthCtx, {
    type: 'bar',
    data: {
        labels: customerGrowth.map(g => g.month),
        datasets: [{
            label: 'Νέοι Πελάτες',
            data: customerGrowth.map(g => g.new_customers),
            backgroundColor: '#667eea'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #3498db 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}
</style>

<?php require_once 'views/includes/footer.php'; ?>
