<?php $pageTitle = __('maintenances.view'); ?>

<div class="container-fluid py-1">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-1">
                    <h6 class="mb-0"><i class="fas fa-eye"></i> <?= __('maintenances.view_maintenance') ?></h6>
                    <div>
                        <a href="<?= BASE_URL ?>/maintenances/edit/<?= $maintenance['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> <?= __('maintenances.edit') ?>
                        </a>
                        <a href="<?= BASE_URL ?>/maintenances" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> <?= __('common.back') ?>
                        </a>
                    </div>
                </div>
                <div class="card-body py-1">
                    
                    <!-- Export Buttons -->
                    <div class="row mb-1">
                        <div class="col-12">
                            <div class="btn-group">
                                <a href="<?= BASE_URL ?>/maintenances/exportPDF/<?= $maintenance['id'] ?>" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-file-word"></i> <?= __('maintenances.issue_certificate') ?>
                                </a>
                                <a href="<?= BASE_URL ?>/maintenances/exportExcel/<?= $maintenance['id'] ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel"></i> <?= __('maintenances.export_maintenance_report') ?>
                                </a>
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#sendEmailModal">
                                    <i class="fas fa-envelope"></i> <?= __('maintenances.send_by_email', 'Αποστολή με Email') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-1">

                    <!-- Section 1: Customer Info -->
                    <h6 class="mb-1 text-primary"><i class="fas fa-user"></i> <?= __('customers.customer_info') ?></h6>
                    <div class="row mb-1">
                        <div class="col-md-6">
                            <strong><?= __('maintenances.customer_name') ?>:</strong>
                            <span><?= htmlspecialchars($maintenance['customer_name']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong><?= __('maintenances.phone') ?>:</strong>
                            <span><?= htmlspecialchars($maintenance['phone'] ?? '-') ?></span>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-6">
                            <strong><?= __('maintenances.address') ?>:</strong>
                            <span><?= htmlspecialchars($maintenance['address'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong><?= __('maintenances.other_details') ?>:</strong>
                            <span><?= nl2br(htmlspecialchars($maintenance['other_details'] ?? '-')) ?></span>
                        </div>
                    </div>
                    <hr class="my-1">

                    <!-- Section 2: Maintenance Info -->
                    <h6 class="mb-1 text-primary"><i class="fas fa-calendar"></i> <?= __('maintenances.maintenance_info') ?></h6>
                    <div class="row mb-1">
                        <div class="col-md-4">
                            <strong><?= __('maintenances.maintenance_date') ?>:</strong>
                            <span><?= date('d/m/Y', strtotime($maintenance['maintenance_date'])) ?></span>
                        </div>
                        <div class="col-md-4">
                            <strong><?= __('maintenances.next_maintenance_date') ?>:</strong>
                            <?php
                            $nextDate = new DateTime($maintenance['next_maintenance_date']);
                            $today = new DateTime();
                            $interval = $today->diff($nextDate);
                            $daysUntil = $interval->days;
                            $isUpcoming = !$interval->invert && $daysUntil <= 30;
                            $isPast = $interval->invert;
                            ?>
                            <span class="badge bg-<?= $isPast ? 'danger' : ($isUpcoming ? 'warning' : 'success') ?>">
                                <?= date('d/m/Y', strtotime($maintenance['next_maintenance_date'])) ?>
                            </span>
                            <?php if ($isPast): ?>
                                <small class="text-danger d-block">⚠️ <?= __('maintenances.overdue_label') ?></small>
                            <?php elseif ($isUpcoming): ?>
                                <small class="text-warning d-block">⏰ <?= __('maintenances.in_days', ['days' => $daysUntil]) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-user-cog"></i> Υπεύθυνος Τεχνικός:</strong>
                            <span class="badge bg-primary"><?= htmlspecialchars($maintenance['created_by_name'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    
                    <!-- Additional Technicians -->
                    <?php 
                    if (!empty($maintenance['additional_technicians'])):
                        $additionalTechs = json_decode($maintenance['additional_technicians'], true);
                        if (!empty($additionalTechs)):
                            // Get technician names
                            $userModel = new User();
                            $techNames = [];
                            foreach ($additionalTechs as $techId) {
                                $tech = $userModel->find($techId);
                                if ($tech) {
                                    $techNames[] = $tech['first_name'] . ' ' . $tech['last_name'];
                                }
                            }
                            if (!empty($techNames)):
                    ?>
                    <div class="row mb-1">
                        <div class="col-md-12">
                            <strong><i class="fas fa-users"></i> Επιπλέον Τεχνικοί:</strong>
                            <?php foreach ($techNames as $name): ?>
                                <span class="badge bg-info me-1"><?= htmlspecialchars($name) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php 
                            endif;
                        endif;
                    endif; 
                    ?>                    <hr class="my-1">

                    <!-- Section 3: Transformers Data -->
                    <?php
                    // Parse transformers data from JSON or fall back to legacy fields
                    $transformers = [];
                    if (!empty($maintenance['transformers_data'])) {
                        $transformers = json_decode($maintenance['transformers_data'], true);
                    }
                    
                    // Fallback to legacy single transformer fields
                    if (empty($transformers)) {
                        $transformers = [[
                            'power' => $maintenance['transformer_power'],
                            'type' => $maintenance['transformer_type'] ?? 'oil', // Include type from legacy field
                            'insulation' => $maintenance['insulation_measurements'],
                            'coil_resistance' => $maintenance['coil_resistance_measurements'],
                            'grounding' => $maintenance['grounding_measurement'],
                            'oil_v1' => $maintenance['oil_breakdown_v1'],
                            'oil_v2' => $maintenance['oil_breakdown_v2'],
                            'oil_v3' => $maintenance['oil_breakdown_v3'],
                            'oil_v4' => $maintenance['oil_breakdown_v4'],
                            'oil_v5' => $maintenance['oil_breakdown_v5']
                        ]];
                    }
                    ?>

                    <h6 class="mb-1 text-primary">
                        <i class="fas fa-bolt"></i> <?= __('maintenances.transformers') ?> 
                        <span class="badge bg-info"><?= count($transformers) ?></span>
                    </h6>

                    <?php foreach ($transformers as $index => $transformer): ?>
                    <div class="card mb-1 border-primary">
                        <div class="card-header bg-primary text-white py-1">
                            <h6 class="mb-0 small"><i class="fas fa-bolt"></i> <?= str_replace('{number}', $index + 1, __('maintenances.transformer_number')) ?></h6>
                        </div>
                        <div class="card-body py-1">
                            <!-- Transformer Power and Type -->
                            <div class="row mb-1">
                                <div class="col-md-8">
                                    <strong><?= __('maintenances.transformer_power') ?>:</strong>
                                    <span class="text-info"><?= htmlspecialchars($transformer['power']) ?> kVA</span>
                                </div>
                                <div class="col-md-4">
                                    <strong><?= __('maintenances.transformer_type') ?>:</strong>
                                    <?php 
                                    $type = $transformer['type'] ?? 'oil';
                                    $typeLabel = $type === 'dry' ? __('maintenances.dry_type') : __('maintenances.oil_type');
                                    $badgeClass = $type === 'dry' ? 'bg-warning' : 'bg-info';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $typeLabel ?></span>
                                </div>
                            </div>

                            <!-- Measurements in single row to save space -->
                            <div class="row mb-1">
                                <div class="col-md-4">
                                    <strong><?= __('maintenances.insulation_measurements') ?>:</strong>
                                    <div class="bg-light p-1 rounded small text-truncate" style="max-height: 40px; overflow-y: auto;">
                                        <?= nl2br(htmlspecialchars($transformer['insulation'])) ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <strong><?= __('maintenances.coil_resistance_measurements') ?>:</strong>
                                    <div class="bg-light p-1 rounded small text-truncate" style="max-height: 40px; overflow-y: auto;">
                                        <?= nl2br(htmlspecialchars($transformer['coil_resistance'])) ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <strong><?= __('maintenances.grounding_measurement') ?>:</strong>
                                    <span class="small"><?= htmlspecialchars($transformer['grounding']) ?> Ω</span>
                                </div>
                            </div>

                            <!-- Oil Breakdown (only for oil type transformers) -->
                            <?php if (($transformer['type'] ?? 'oil') === 'oil'): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <strong><?= __('maintenances.oil_breakdown') ?>:</strong>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm small">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="p-1"><?= __('maintenances.oil_breakdown_v1') ?></th>
                                                    <th class="p-1"><?= __('maintenances.oil_breakdown_v2') ?></th>
                                                    <th class="p-1"><?= __('maintenances.oil_breakdown_v3') ?></th>
                                                    <th class="p-1"><?= __('maintenances.oil_breakdown_v4') ?></th>
                                                    <th class="p-1"><?= __('maintenances.oil_breakdown_v5') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="p-1"><?= htmlspecialchars($transformer['oil_v1'] ?? '-') ?> kV</td>
                                                    <td class="p-1"><?= htmlspecialchars($transformer['oil_v2'] ?? '-') ?> kV</td>
                                                    <td class="p-1"><?= htmlspecialchars($transformer['oil_v3'] ?? '-') ?> kV</td>
                                                    <td class="p-1"><?= htmlspecialchars($transformer['oil_v4'] ?? '-') ?> kV</td>
                                                    <td class="p-1"><?= htmlspecialchars($transformer['oil_v5'] ?? '-') ?> kV</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Materials for this transformer -->
                            <?php if (!empty($transformer['materials'])): ?>
                            <div class="row mb-1 mt-2">
                                <div class="col-md-12">
                                    <strong><i class="fas fa-tools"></i> Υλικά:</strong>
                                    <div class="bg-light p-2 rounded small">
                                        <?= nl2br(htmlspecialchars($transformer['materials'])) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Observations for this transformer -->
                            <?php if (!empty($transformer['observations'])): ?>
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <strong><i class="fas fa-comment-alt"></i> <?= __('maintenances.observations') ?>:</strong>
                                    <div class="bg-light p-2 rounded small">
                                        <?= nl2br(htmlspecialchars($transformer['observations'])) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Photos for this transformer -->
                            <?php if (!empty($transformer['photos']) && is_array($transformer['photos']) && count($transformer['photos']) > 0): ?>
                            <div class="row mb-1">
                                <div class="col-md-12">
                                    <strong><i class="fas fa-images"></i> Φωτογραφίες (<?= count($transformer['photos']) ?>):</strong>
                                    <div class="row g-2 mt-1">
                                        <?php foreach ($transformer['photos'] as $photoIndex => $photo): ?>
                                            <div class="col-md-2">
                                                <div class="position-relative">
                                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($photo) ?>" 
                                                         class="img-thumbnail w-100" 
                                                         style="height: 120px; object-fit: cover; cursor: pointer;"
                                                         onclick="openLightbox(<?= $index ?>_<?= $photoIndex ?>)"
                                                         alt="Φωτογραφία Μ/Σ <?= $index + 1 ?> - <?= $photoIndex + 1 ?>">
                                                    <div class="position-absolute bottom-0 start-0 bg-dark bg-opacity-50 text-white px-2 py-1 small">
                                                        #<?= $photoIndex + 1 ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <hr class="my-4">

                    <!-- Section 6: Metadata -->
                    <h5 class="mb-3 text-primary"><i class="fas fa-info-circle"></i> <?= __('maintenances.registration_info') ?></h5>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong><?= __('maintenances.technician') ?>:</strong>
                            <p><?= htmlspecialchars($maintenance['technician_name']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong><?= __('maintenances.created_at') ?>:</strong>
                            <p><?= date('d/m/Y H:i', strtotime($maintenance['created_at'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong><?= __('maintenances.updated_at') ?>:</strong>
                            <p><?= date('d/m/Y H:i', strtotime($maintenance['updated_at'])) ?></p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <a href="<?= BASE_URL ?>/maintenances/edit/<?= $maintenance['id'] ?>" class="btn btn-warning btn-lg">
                                <i class="fas fa-edit"></i> <?= __('maintenances.edit') ?>
                            </a>
                            <button type="button" class="btn btn-danger btn-lg" 
                                    onclick="confirmDelete(<?= $maintenance['id'] ?>)">
                                <i class="fas fa-trash"></i> <?= __('maintenances.delete') ?>
                            </button>
                            <a href="<?= BASE_URL ?>/maintenances" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> <?= __('maintenances.return_to_list') ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/maintenances/sendEmail/<?= $maintenance['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendEmailModalLabel">
                        <i class="fas fa-envelope"></i> <?= __('maintenances.send_report_by_email', 'Αποστολή Αναφοράς με Email') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <?= __('maintenances.email_will_include_pdf', 'Το email θα περιλαμβάνει την αναφορά συντήρησης σε μορφή PDF') ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="recipient_email" class="form-label"><?= __('maintenances.recipient_email', 'Email Παραλήπτη') ?> *</label>
                        <input type="email" class="form-control" id="recipient_email" name="recipient_email" 
                               placeholder="example@email.com" required>
                        <small class="form-text text-muted">Γράψε το email του πελάτη για αποστολή της αναφοράς</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email_subject" class="form-label"><?= __('maintenances.email_subject', 'Θέμα Email') ?>:</label>
                        <input type="text" class="form-control" id="email_subject" name="email_subject" 
                               value="<?= __('maintenances.default_email_subject', 'Αναφορά Συντήρησης') ?> - <?= htmlspecialchars($maintenance['customer_name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email_message" class="form-label"><?= __('maintenances.email_message', 'Μήνυμα') ?>:</label>
                        <textarea class="form-control" id="email_message" name="email_message" rows="5" required><?= __('maintenances.default_email_body', 'Αγαπητέ/ή {customer_name},

Σας αποστέλλουμε την αναφορά συντήρησης που πραγματοποιήθηκε στις {maintenance_date}.

Με εκτίμηση,
HandyCRM Team') ?></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="send_copy_to_me" name="send_copy_to_me" value="1">
                        <label class="form-check-label" for="send_copy_to_me">
                            <?= __('maintenances.send_copy_to_me', 'Αποστολή αντιγράφου σε εμένα') ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= __('common.cancel', 'Ακύρωση') ?>
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-paper-plane"></i> <?= __('maintenances.send_email', 'Αποστολή Email') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
const BASE_URL_JS = <?= json_encode(isset($BASE_URL) ? $BASE_URL : (defined('BASE_URL') ? BASE_URL : '')) ?>;
console.log('BASE_URL from PHP:', BASE_URL_JS);
console.log('BASE_URL empty?', BASE_URL_JS === '');

function confirmDelete(id) {
    if (confirm('<?= __('maintenances.confirm_delete_message') ?>')) {
        const form = document.getElementById('deleteForm');
        form.action = BASE_URL_JS + '/maintenances/delete/' + id;
        console.log('Full Delete URL:', form.action);
        form.submit();
    }
}
</script>

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
const photos = <?= json_encode($maintenance['photos'] ?? []) ?>;
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
</script>
