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
                <div class="col-md-3">
                    <label class="form-label">Αναζήτηση</label>
                    <input type="text" class="form-control" name="search" 
                           placeholder="Όνομα, τηλέφωνο, διεύθυνση..." 
                           value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Από Ημερομηνία</label>
                    <input type="date" class="form-control" name="date_from" 
                           value="<?= htmlspecialchars($dateFrom ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Έως Ημερομηνία</label>
                    <input type="date" class="form-control" name="date_to" 
                           value="<?= htmlspecialchars($dateTo ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Τιμολογήθηκε</label>
                    <select class="form-select" name="is_invoiced">
                        <option value="">Όλες</option>
                        <option value="1" <?= (isset($isInvoiced) && $isInvoiced === '1') ? 'selected' : '' ?>>Ναι</option>
                        <option value="0" <?= (isset($isInvoiced) && $isInvoiced === '0') ? 'selected' : '' ?>>Όχι</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Δελτίο Στάλθηκε</label>
                    <select class="form-select" name="report_sent">
                        <option value="">Όλες</option>
                        <option value="1" <?= (isset($reportSent) && $reportSent === '1') ? 'selected' : '' ?>>Ναι</option>
                        <option value="0" <?= (isset($reportSent) && $reportSent === '0') ? 'selected' : '' ?>>Όχι</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
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
                <?php if ($search || $dateFrom || $dateTo || isset($isInvoiced) || isset($reportSent)): ?>
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
                    <?= ($search || $dateFrom || $dateTo || isset($isInvoiced) || isset($reportSent)) ? 'Δεν βρέθηκαν συντηρήσεις με τα κριτήρια αναζήτησης.' : 'Δεν υπάρχουν καταχωρημένες συντηρήσεις.' ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 8%;">Ημ/νία Συντήρησης</th>
                                <th style="width: 18%;">Πελάτης</th>
                                <th style="width: 10%;">Διεύθυνση</th>
                                <th style="width: 10%;">Τηλέφωνο</th>
                                <th style="width: 12%;">Ισχύς Μ/Σ</th>
                                <th style="width: 10%;">Επόμενη Συντήρηση</th>
                                <th style="width: 6%;" class="text-center">Τιμολογήθηκε</th>
                                <th style="width: 6%;" class="text-center">Δελτίο Συντ.</th>
                                <th style="width: 15%;">Τεχνικός</th>
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
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="invoiced_<?= $maintenance['id'] ?>"
                                                   <?= $maintenance['is_invoiced'] ? 'checked' : '' ?>
                                                   onchange="toggleStatus(<?= $maintenance['id'] ?>, 'invoiced', this.checked)">
                                        </div>
                                        <?php if (!empty($maintenance['invoiced_at'])): ?>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                                <?= date('d/m/Y H:i', strtotime($maintenance['invoiced_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="report_<?= $maintenance['id'] ?>"
                                                   <?= $maintenance['report_sent'] ? 'checked' : '' ?>
                                                   onchange="toggleStatus(<?= $maintenance['id'] ?>, 'report', this.checked)">
                                        </div>
                                        <?php if (!empty($maintenance['report_sent_at'])): ?>
                                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                                <?= date('d/m/Y H:i', strtotime($maintenance['report_sent_at'])) ?>
                                            </div>
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
                            if (isset($isInvoiced) && $isInvoiced !== '') $queryParams['is_invoiced'] = $isInvoiced;
                            if (isset($reportSent) && $reportSent !== '') $queryParams['report_sent'] = $reportSent;
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

function toggleStatus(id, type, checked) {
    const status = checked ? 1 : 0;
    const checkboxId = type === 'invoiced' ? 'invoiced_' + id : 'report_' + id;
    const checkbox = document.getElementById(checkboxId);
    
    // Disable checkbox during request
    checkbox.disabled = true;
    
    const url = BASE_URL_JS + '/maintenances/toggle-status/' + id;
    console.log('Calling URL:', url); // Debug
    console.log('Payload:', { type: type, status: status }); // Debug
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            type: type,
            status: status
        })
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // Debug
        if (data.success) {
            // Update timestamp display
            const cell = checkbox.closest('td');
            const existingTimestamp = cell.querySelector('.text-muted.small');
            
            if (checked) {
                // Add or update timestamp
                const now = new Date();
                const timestamp = now.toLocaleDateString('el-GR', { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                if (existingTimestamp) {
                    existingTimestamp.textContent = timestamp;
                } else {
                    const timestampDiv = document.createElement('div');
                    timestampDiv.className = 'text-muted small mt-1';
                    timestampDiv.style.fontSize = '0.75rem';
                    timestampDiv.textContent = timestamp;
                    cell.appendChild(timestampDiv);
                }
            } else {
                // Remove timestamp when unchecked
                if (existingTimestamp) {
                    existingTimestamp.remove();
                }
            }
            
            checkbox.disabled = false;
        } else {
            // Revert checkbox on error
            checkbox.checked = !checked;
            checkbox.disabled = false;
            alert('Σφάλμα κατά την ενημέρωση: ' + (data.message || 'Άγνωστο σφάλμα'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error); // Debug
        // Revert checkbox on error
        checkbox.checked = !checked;
        checkbox.disabled = false;
        alert('Σφάλμα επικοινωνίας με τον server: ' + error.message);
    });
}
</script>
