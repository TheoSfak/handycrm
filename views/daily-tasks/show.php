<?php $pageTitle = 'Προβολή Εργασίας Ημέρας'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4>
                    <i class="fas fa-clipboard-list"></i> 
                    Εργασία: <strong><?= htmlspecialchars($task['task_number']) ?></strong>
                </h4>
                <div>
                    <a href="<?= BASE_URL ?>/daily-tasks/export-pdf/<?= $task['id'] ?>" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                    <a href="<?= BASE_URL ?>/daily-tasks/send-email/<?= $task['id'] ?>" class="btn btn-success">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                    <a href="<?= BASE_URL ?>/daily-tasks/edit/<?= $task['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Επεξεργασία
                    </a>
                    <a href="<?= BASE_URL ?>/daily-tasks" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Πίσω στη Λίστα
                    </a>
                    <button onclick="window.print()" class="btn btn-info">
                        <i class="fas fa-print"></i> Εκτύπωση
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Info Card -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Βασικά Στοιχεία</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Αρ. Εργασίας:</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-primary fs-6"><?= htmlspecialchars($task['task_number']) ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Ημερομηνία:</strong>
                        </div>
                        <div class="col-md-3">
                            <?= date('d/m/Y', strtotime($task['date'])) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Τύπος Εργασίας:</strong>
                        </div>
                        <div class="col-md-3">
                            <?php
                            $taskTypeLabels = [
                                'electrical' => '<span class="badge bg-warning text-dark">Ηλεκτρολογικές Εργασίες</span>',
                                'inspection' => '<span class="badge bg-info">Επίσκεψη/Έλεγχος</span>',
                                'fault_repair' => '<span class="badge bg-danger">Έλεγχος Βλάβες / Αποκατάσταση Βλάβης</span>',
                                'other' => '<span class="badge bg-secondary">Διάφορα</span>'
                            ];
                            echo $taskTypeLabels[$task['task_type']] ?? '';
                            ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Κατάσταση:</strong>
                        </div>
                        <div class="col-md-3">
                            <?php
                            $statusBadges = [
                                'completed' => '<span class="badge bg-success">Ολοκληρώθηκε</span>',
                                'in_progress' => '<span class="badge bg-primary">Σε Εξέλιξη</span>',
                                'cancelled' => '<span class="badge bg-danger">Ακυρώθηκε</span>'
                            ];
                            echo $statusBadges[$task['status']] ?? '';
                            ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Τιμολογήθηκε:</strong>
                        </div>
                        <div class="col-md-9">
                            <?php if ($task['is_invoiced']): ?>
                                <span class="badge bg-success"><i class="fas fa-check"></i> Ναι</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-times"></i> Όχι</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($task['hours_worked'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Ώρες Εργασίας:</strong>
                    </div>
                    <div class="col-md-9">
                        <i class="fas fa-clock text-primary"></i> 
                        <?= number_format($task['hours_worked'], 2) ?> ώρες
                        
                        <?php 
                        // Only show time range if both times are valid (not null and not 00:00:00)
                        $hasValidTimeFrom = !empty($task['time_from']) && $task['time_from'] !== '00:00:00';
                        $hasValidTimeTo = !empty($task['time_to']) && $task['time_to'] !== '00:00:00';
                        if ($hasValidTimeFrom && $hasValidTimeTo): 
                        ?>
                            <span class="text-muted">
                                (<?= substr($task['time_from'], 0, 5) ?> - <?= substr($task['time_to'], 0, 5) ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <h6 class="mb-3"><i class="fas fa-user"></i> Πελάτης</h6>
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <strong>Όνομα:</strong>
                        </div>
                        <div class="col-md-9">
                            <?= htmlspecialchars($task['customer_name']) ?>
                        </div>
                    </div>

                    <?php if (!empty($task['address'])): ?>
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <strong>Διεύθυνση:</strong>
                        </div>
                        <div class="col-md-9">
                            <i class="fas fa-map-marker-alt text-danger"></i> 
                            <?= htmlspecialchars($task['address']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($task['phone'])): ?>
                    <div class="row mb-2">
                        <div class="col-md-3">
                            <strong>Τηλέφωνο:</strong>
                        </div>
                        <div class="col-md-9">
                            <i class="fas fa-phone text-success"></i> 
                            <a href="tel:<?= htmlspecialchars($task['phone']) ?>">
                                <?= htmlspecialchars($task['phone']) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <h6 class="mb-3"><i class="fas fa-align-left"></i> Περιγραφή Εργασίας</h6>
                    <div class="alert alert-light">
                        <?= nl2br(htmlspecialchars($task['description'])) ?>
                    </div>

                    <?php if (!empty($materials)): ?>
                    <h6 class="mb-3"><i class="fas fa-boxes"></i> Υλικά που Χρησιμοποιήθηκαν</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Υλικό</th>
                                    <th>Μονάδα</th>
                                    <th class="text-end">Τιμή Μονάδας</th>
                                    <th class="text-end">Ποσότητα</th>
                                    <th class="text-end">Σύνολο</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalCost = 0;
                                foreach ($materials as $material): 
                                    $totalCost += $material['subtotal'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($material['name']) ?></td>
                                        <td><?= htmlspecialchars($material['unit'] ?? '-') ?></td>
                                        <td class="text-end"><?= number_format($material['unit_price'], 2) ?> €</td>
                                        <td class="text-end"><?= number_format($material['quantity'], 2) ?></td>
                                        <td class="text-end"><strong><?= number_format($material['subtotal'], 2) ?> €</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Σύνολο Υλικών:</th>
                                    <th class="text-end"><?= number_format($totalCost, 2) ?> €</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($task['notes'])): ?>
                    <h6 class="mb-3"><i class="fas fa-sticky-note"></i> Σημειώσεις</h6>
                    <div class="alert alert-info">
                        <?= nl2br(htmlspecialchars($task['notes'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Photos Gallery -->
            <?php if (!empty($task['photos'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-images"></i> Φωτογραφίες (<?= count($task['photos']) ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($task['photos'] as $index => $photo): ?>
                            <div class="col-md-3">
                                <div class="position-relative">
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($photo) ?>" 
                                         class="img-thumbnail w-100" 
                                         style="height: 200px; object-fit: cover; cursor: pointer;"
                                         onclick="openLightbox(<?= $index ?>)"
                                         alt="Φωτογραφία <?= $index + 1 ?>">
                                    <div class="position-absolute bottom-0 start-0 bg-dark bg-opacity-50 text-white px-2 py-1 small">
                                        #<?= $index + 1 ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Technicians Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Τεχνικοί</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Κύριος Τεχνικός</h6>
                    <?php if (!empty($primaryTechnician)): ?>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="ms-3">
                                <strong><?= htmlspecialchars($primaryTechnician['full_name']) ?></strong>
                                <div class="small text-muted">
                                    <?= number_format($primaryTechnician['hours_worked'], 1) ?> ώρες × 
                                    <?= formatCurrency($primaryTechnician['hourly_rate'] ?? 0) ?>/ώρα = 
                                    <strong><?= formatCurrency(($primaryTechnician['hours_worked'] ?? 0) * ($primaryTechnician['hourly_rate'] ?? 0)) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-primary"><?= number_format($primaryTechnician['hours_worked'], 1) ?>h</span>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="ms-3">
                            <strong><?= htmlspecialchars($task['technician_name']) ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($additionalTechnicians) && count($additionalTechnicians) > 0): ?>
                    <hr>
                    <h6 class="mb-3">Επιπλέον Τεχνικοί</h6>
                    <?php 
                    $totalHours = $primaryTechnician['hours_worked'] ?? 0;
                    $totalLaborCost = ($primaryTechnician['hours_worked'] ?? 0) * ($primaryTechnician['hourly_rate'] ?? 0);
                    
                    foreach ($additionalTechnicians as $tech): 
                        $totalHours += $tech['hours_worked'];
                        $techCost = ($tech['hours_worked'] ?? 0) * ($tech['hourly_rate'] ?? 0);
                        $totalLaborCost += $techCost;
                    ?>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="ms-2 flex-grow-1">
                                    <div><?= htmlspecialchars($tech['full_name']) ?></div>
                                    <div class="small text-muted">
                                        <?= number_format($tech['hours_worked'], 1) ?>h × 
                                        <?= formatCurrency($tech['hourly_rate'] ?? 0) ?> = 
                                        <strong><?= formatCurrency($techCost) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="badge bg-secondary"><?= number_format($tech['hours_worked'], 1) ?>h</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Σύνολο Ωρών:</strong> 
                        <span class="badge bg-info"><?= number_format($totalHours, 1) ?> ώρες</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Κόστος Εργασίας:</strong> 
                        <span class="badge bg-success"><?= formatCurrency($totalLaborCost) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadata Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info"></i> Μεταδεδομένα</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Δημιουργήθηκε</small><br>
                        <i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($task['created_at'])) ?>
                    </div>

                    <?php if (!empty($task['updated_at']) && $task['updated_at'] != $task['created_at']): ?>
                    <div class="mb-2">
                        <small class="text-muted">Τελευταία Ενημέρωση</small><br>
                        <i class="fas fa-calendar-check"></i> <?= date('d/m/Y H:i', strtotime($task['updated_at'])) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($task['created_by_name'])): ?>
                    <div class="mb-2">
                        <small class="text-muted">Δημιουργήθηκε Από</small><br>
                        <i class="fas fa-user"></i> <?= htmlspecialchars($task['created_by_name']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Ενέργειες</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/daily-tasks/send-email/<?= $task['id'] ?>" class="btn btn-success">
                            <i class="fas fa-envelope"></i> Αποστολή Email
                        </a>

                        <a href="<?= BASE_URL ?>/daily-tasks/edit/<?= $task['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Επεξεργασία
                        </a>
                        
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> Εκτύπωση
                        </button>

                        <form method="POST" action="<?= BASE_URL ?>/daily-tasks/delete/<?= $task['id'] ?>" 
                              onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή την εργασία;\n\nΑυτή η ενέργεια δεν μπορεί να αναιρεθεί.');">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Διαγραφή
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="lightboxTitle">Φωτογραφία</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="lightboxImage" src="" class="img-fluid" style="max-height: 80vh;">
            </div>
            <div class="modal-footer border-0 justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="navigateLightbox(-1)">
                    <i class="fas fa-chevron-left"></i> Προηγούμενη
                </button>
                <span class="text-white" id="lightboxCounter"></span>
                <button type="button" class="btn btn-secondary" onclick="navigateLightbox(1)">
                    Επόμενη <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Lightbox functionality
const photos = <?= json_encode($task['photos'] ?? []) ?>;
let currentPhotoIndex = 0;

function openLightbox(index) {
    currentPhotoIndex = index;
    updateLightbox();
    const modal = new bootstrap.Modal(document.getElementById('lightboxModal'));
    modal.show();
}

function navigateLightbox(direction) {
    currentPhotoIndex += direction;
    
    // Loop around
    if (currentPhotoIndex < 0) {
        currentPhotoIndex = photos.length - 1;
    } else if (currentPhotoIndex >= photos.length) {
        currentPhotoIndex = 0;
    }
    
    updateLightbox();
}

function updateLightbox() {
    if (photos.length > 0) {
        const photo = photos[currentPhotoIndex];
        document.getElementById('lightboxImage').src = '<?= BASE_URL ?>/' + photo;
        document.getElementById('lightboxTitle').textContent = `Φωτογραφία ${currentPhotoIndex + 1}`;
        document.getElementById('lightboxCounter').textContent = `${currentPhotoIndex + 1} / ${photos.length}`;
    }
}

// Keyboard navigation for lightbox
document.addEventListener('keydown', function(e) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('lightboxModal'));
    if (modal && modal._isShown) {
        if (e.key === 'ArrowLeft') {
            navigateLightbox(-1);
        } else if (e.key === 'ArrowRight') {
            navigateLightbox(1);
        } else if (e.key === 'Escape') {
            modal.hide();
        }
    }
});

// Print styles
const style = document.createElement('style');
style.textContent = `
    @media print {
        .btn, .navbar, .sidebar, .modal, .no-print {
            display: none !important;
        }
        .card {
            border: 1px solid #dee2e6 !important;
            page-break-inside: avoid;
        }
        .container-fluid {
            width: 100% !important;
            max-width: none !important;
        }
    }
`;
document.head.appendChild(style);
</script>
