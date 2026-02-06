<?php $pageTitle = 'Συντηρήσεις Μ/Σ'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end align-items-center">
                <a href="<?= BASE_URL ?>/maintenances/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Νέα Συντήρηση
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/maintenances" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Αναζήτηση</label>
                    <input type="text" class="form-control" name="search" 
                           placeholder="Όνομα, τηλέφωνο, διεύθυνση..." 
                           value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Από Ημερομηνία</label>
                    <input type="date" class="form-control" name="date_from" 
                           value="<?= htmlspecialchars($dateFrom ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Έως Ημερομηνία</label>
                    <input type="date" class="form-control" name="date_to" 
                           value="<?= htmlspecialchars($dateTo ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Αναζήτηση
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                Συνολικά: <?= $totalCount ?> συντηρήσεις
                <?php if ($search || $dateFrom || $dateTo): ?>
                    <a href="<?= BASE_URL ?>/maintenances" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="fas fa-times"></i> Καθαρισμός Φίλτρων
                    </a>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($maintenances)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <?= $search || $dateFrom || $dateTo ? 'Δεν βρέθηκαν συντηρήσεις με τα κριτήρια αναζήτησης.' : 'Δεν υπάρχουν καταχωρημένες συντηρήσεις.' ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Ημ/νία Συντήρησης</th>
                                <th style="width: 20%;">Πελάτης</th>
                                <th style="width: 10%;">Διεύθυνση</th>
                                <th style="width: 10%;">Τηλέφωνο</th>
                                <th style="width: 15%;">Ισχύς Μ/Σ</th>
                                <th style="width: 10%;">Επόμενη Συντήρηση</th>
                                <th style="width: 20%;">Τεχνικός</th>
                                <th style="width: 5%;" class="text-end">Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($maintenances as $maintenance): ?>
                                <?php
                                // Check if next maintenance is soon (within 30 days)
                                $nextDate = new DateTime($maintenance['next_maintenance_date']);
                                $today = new DateTime();
                                $interval = $today->diff($nextDate);
                                $daysUntil = $interval->days;
                                $isUpcoming = !$interval->invert && $daysUntil <= 30;
                                $isPast = $interval->invert;
                                ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($maintenance['maintenance_date'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($maintenance['customer_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($maintenance['address'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($maintenance['phone'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        // Show all transformers information
                                        $transformerCount = 1;
                                        $transformers = [];
                                        
                                        if (!empty($maintenance['transformers_data'])) {
                                            $transformersArray = json_decode($maintenance['transformers_data'], true);
                                            if (is_array($transformersArray)) {
                                                $transformers = $transformersArray;
                                                $transformerCount = count($transformersArray);
                                            }
                                        } else {
                                            // Fallback to legacy single transformer
                                            $transformers = [[
                                                'power' => $maintenance['transformer_power'],
                                                'type' => $maintenance['transformer_type'] ?? 'oil'
                                            ]];
                                        }
                                        
                                        if ($transformerCount > 1) {
                                            echo '<span class="badge bg-primary mb-1">' . $transformerCount . ' Μετασχηματιστές</span><br>';
                                            foreach ($transformers as $index => $transformer) {
                                                $typeLabel = ($transformer['type'] ?? 'oil') === 'dry' ? 'Ξ' : 'Ε';
                                                $typeBadgeClass = ($transformer['type'] ?? 'oil') === 'dry' ? 'bg-warning' : 'bg-info';
                                                echo '<small class="d-block">';
                                                echo ($index + 1) . '. ' . htmlspecialchars($transformer['power']) . ' kVA ';
                                                echo '<span class="badge ' . $typeBadgeClass . ' text-white" style="font-size: 10px;">' . $typeLabel . '</span>';
                                                echo '</small>';
                                            }
                                        } else {
                                            $transformer = $transformers[0];
                                            $typeLabel = ($transformer['type'] ?? 'oil') === 'dry' ? 'Ξηρού' : 'Ελαίου';
                                            $typeBadgeClass = ($transformer['type'] ?? 'oil') === 'dry' ? 'bg-warning' : 'bg-info';
                                            echo htmlspecialchars($transformer['power']) . ' kVA<br>';
                                            echo '<span class="badge ' . $typeBadgeClass . '">' . $typeLabel . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $isPast ? 'danger' : ($isUpcoming ? 'warning' : 'success') ?>">
                                            <?= date('d/m/Y', strtotime($maintenance['next_maintenance_date'])) ?>
                                        </span>
                                        <?php if ($isPast): ?>
                                            <small class="text-danger d-block">Καθυστερημένη</small>
                                        <?php elseif ($isUpcoming): ?>
                                            <small class="text-warning d-block">Σε <?= $daysUntil ?> ημέρες</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($maintenance['technician_name'] ?? 'N/A') ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/maintenances/view/<?= $maintenance['id'] ?>" 
                                               class="btn btn-info" title="Προβολή">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/maintenances/edit/<?= $maintenance['id'] ?>" 
                                               class="btn btn-warning" title="Επεξεργασία">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="confirmDelete(<?= $maintenance['id'] ?>)" 
                                                    title="Διαγραφή">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php
                            $queryParams = [];
                            if ($search) $queryParams['search'] = $search;
                            if ($dateFrom) $queryParams['date_from'] = $dateFrom;
                            if ($dateTo) $queryParams['date_to'] = $dateTo;
                            $queryString = http_build_query($queryParams);
                            $queryPrefix = $queryString ? '&' : '';
                            ?>

                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= BASE_URL ?>/maintenances?page=<?= $currentPage - 1 ?><?= $queryPrefix . $queryString ?>">
                                        Προηγούμενη
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/maintenances?page=<?= $i ?><?= $queryPrefix . $queryString ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= BASE_URL ?>/maintenances?page=<?= $currentPage + 1 ?><?= $queryPrefix . $queryString ?>">
                                        Επόμενη
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
const BASE_URL_JS = <?= json_encode(isset($BASE_URL) ? $BASE_URL : (defined('BASE_URL') ? BASE_URL : '')) ?>;

function confirmDelete(id) {
    if (confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή τη συντήρηση;')) {
        const form = document.getElementById('deleteForm');
        form.action = BASE_URL_JS + '/maintenances/delete/' + id;
        form.submit();
    }
}
</script>
