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
                        <div class="col-md-6">
                            <strong><?= __('maintenances.maintenance_date') ?>:</strong>
                            <span><?= date('d/m/Y', strtotime($maintenance['maintenance_date'])) ?></span>
                        </div>
                        <div class="col-md-6">
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
                    </div>

                    <hr class="my-1">

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
                            <h6 class="mb-0 small"><i class="fas fa-bolt"></i> <?= __('maintenances.transformer_number', ['number' => $index + 1]) ?></h6>
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
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <hr class="my-1">

                    <!-- Section 5: Observations & Photo -->
                    <h6 class="mb-1 text-primary"><i class="fas fa-comment-alt"></i> <?= __('maintenances.observations_and_photo') ?></h6>
                    <div class="row mb-1">
                        <div class="col-md-12">
                            <strong><?= __('maintenances.observations') ?>:</strong>
                            <div class="bg-light p-2 rounded small" style="max-height: 100px; overflow-y: auto;">
                                <?= nl2br(htmlspecialchars($maintenance['observations'] ?? __('maintenances.no_observations'))) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-12">
                            <strong><?= __('maintenances.photo') ?>:</strong>
                            <?php if (!empty($maintenance['photo_path'])): ?>
                                <div class="mt-1">
                                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($maintenance['photo_path']) ?>" target="_blank">
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($maintenance['photo_path']) ?>" 
                                             class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    </a>
                                    <br>
                                    <small class="text-muted"><?= __('maintenances.click_to_enlarge') ?></small>
                                </div>
                            <?php else: ?>
                                <p class="text-muted"><?= __('maintenances.no_photo') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

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
