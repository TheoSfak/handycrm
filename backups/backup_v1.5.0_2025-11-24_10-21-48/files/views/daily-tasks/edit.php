<?php $pageTitle = 'Επεξεργασία Εργασίας Ημέρας'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-edit"></i> Επεξεργασία Εργασίας: <?= htmlspecialchars($task['task_number']) ?></h4>
                <a href="<?= BASE_URL ?>/daily-tasks" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Πίσω στη Λίστα
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Στοιχεία Εργασίας</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/daily-tasks/update/<?= $task['id'] ?>" enctype="multipart/form-data" id="taskForm">
                        <div class="row">
                            <!-- Date -->
                            <div class="col-md-3 mb-3">
                                <label for="date" class="form-label">Ημερομηνία <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?= htmlspecialchars($task['date']) ?>" required>
                            </div>

                            <!-- Task Type -->
                            <div class="col-md-3 mb-3">
                                <label for="task_type" class="form-label">Τύπος Εργασίας <span class="text-danger">*</span></label>
                                <select class="form-select" id="task_type" name="task_type" required>
                                    <option value="">-- Επιλέξτε --</option>
                                    <option value="electrical" <?= $task['task_type'] === 'electrical' ? 'selected' : '' ?>>Ηλεκτρολογικές Εργασίες</option>
                                    <option value="inspection" <?= $task['task_type'] === 'inspection' ? 'selected' : '' ?>>Επίσκεψη/Έλεγχος</option>
                                    <option value="fault_repair" <?= $task['task_type'] === 'fault_repair' ? 'selected' : '' ?>>Έλεγχος Βλάβες / Αποκατάσταση Βλάβης</option>
                                    <option value="other" <?= $task['task_type'] === 'other' ? 'selected' : '' ?>>Διάφορα</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Κατάσταση <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Ολοκληρώθηκε</option>
                                    <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>Σε Εξέλιξη</option>
                                    <option value="cancelled" <?= $task['status'] === 'cancelled' ? 'selected' : '' ?>>Ακυρώθηκε</option>
                                </select>
                            </div>

                            <!-- Is Invoiced -->
                            <div class="col-md-3 mb-3">
                                <label for="is_invoiced" class="form-label">Τιμολογήθηκε</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_invoiced" 
                                           name="is_invoiced" value="1" <?= $task['is_invoiced'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_invoiced">Ναι</label>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="customer_name" class="form-label">Πελάτης <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" 
                                       name="customer_name" required placeholder="Όνομα πελάτη"
                                       value="<?= htmlspecialchars($task['customer_name']) ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="address" class="form-label">Διεύθυνση</label>
                                <input type="text" class="form-control" id="address" 
                                       name="address" placeholder="Διεύθυνση"
                                       value="<?= htmlspecialchars($task['address'] ?? '') ?>">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label">Τηλέφωνο</label>
                                <input type="text" class="form-control" id="phone" 
                                       name="phone" placeholder="Τηλέφωνο επικοινωνίας"
                                       value="<?= htmlspecialchars($task['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Περιγραφή Εργασίας <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="4" required placeholder="Λεπτομερής περιγραφή της εργασίας..."><?= htmlspecialchars($task['description']) ?></textarea>
                            </div>
                        </div>

                        <!-- Time Tracking -->
                        <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Καταγραφή Ωρών</label>
                            <div class="btn-group mb-3" role="group">
                                <?php
                                // Check if time range is valid (not null and not 00:00:00)
                                $hasValidTimeFrom = !empty($task['time_from']) && $task['time_from'] !== '00:00:00';
                                $hasValidTimeTo = !empty($task['time_to']) && $task['time_to'] !== '00:00:00';
                                $hasTimeRange = $hasValidTimeFrom && $hasValidTimeTo;
                                $hasManualHours = !empty($task['hours_worked']) && !$hasTimeRange;
                                ?>
                                <input type="radio" class="btn-check" name="time_mode" id="time_mode_manual" value="manual" <?= !$hasTimeRange ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="time_mode_manual">
                                    <i class="fas fa-keyboard"></i> Χειροκίνητη Εισαγωγή
                                </label>
                                
                                <input type="radio" class="btn-check" name="time_mode" id="time_mode_range" value="range" <?= $hasTimeRange ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="time_mode_range">
                                    <i class="fas fa-clock"></i> Εύρος Ωρών
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="manual_hours_container" style="<?= $hasTimeRange ? 'display: none;' : '' ?>">
                        <div class="col-md-3 mb-3">
                            <label for="hours_worked" class="form-label">Συνολικές Ώρες</label>
                            <input type="number" class="form-control" id="hours_worked" 
                                   name="hours_worked" step="0.25" min="0" placeholder="π.χ. 2.5"
                                   value="<?= !$hasTimeRange && !empty($task['hours_worked']) ? htmlspecialchars($task['hours_worked']) : '' ?>">
                            <small class="text-muted">Σε δεκαδικές ώρες (π.χ. 2.5 = 2 ώρες 30 λεπτά)</small>
                        </div>
                    </div>

                    <div class="row" id="time_range_container" style="<?= $hasTimeRange ? '' : 'display: none;' ?>">
                        <div class="col-md-3 mb-3">
                            <label for="time_from" class="form-label">Από Ώρα</label>
                            <input type="time" class="form-control" id="time_from" name="time_from"
                                   value="<?= $hasTimeRange ? htmlspecialchars(substr($task['time_from'], 0, 5)) : '' ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="time_to" class="form-label">Έως Ώρα</label>
                                <input type="time" class="form-control" id="time_to" name="time_to"
                                       value="<?= $hasTimeRange ? htmlspecialchars(substr($task['time_to'], 0, 5)) : '' ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Υπολογισμένες Ώρες</label>
                                <input type="text" class="form-control" id="calculated_hours" readonly 
                                       placeholder="Θα υπολογιστούν αυτόματα"
                                       value="<?= $hasTimeRange && $task['hours_worked'] ? htmlspecialchars(number_format($task['hours_worked'], 2)) . ' ώρες' : '' ?>">
                            </div>
                        </div>

                        <!-- Materials -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-boxes"></i> Υλικά που Χρησιμοποιήθηκαν
                                    <span class="text-muted" style="font-size: 0.9rem;">(Προαιρετικό)</span>
                                </label>
                                <div id="materials_container"></div>
                                <div id="materials_empty" class="text-muted small mb-2" style="display: <?= empty($materials) ? 'block' : 'none' ?>;">
                                    Δεν έχουν προστεθεί υλικά ακόμα
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDailyTaskMaterialRow()">
                                    <i class="fas fa-plus"></i> Προσθήκη Υλικού
                                </button>
                            </div>
                        </div>

                        <!-- Technician -->
                        <?php 
                        // Create helper array for technician hours
                        $techHours = [];
                        $primaryHours = 8;
                        foreach ($taskTechnicians as $tt) {
                            if ($tt['is_primary'] == 1) {
                                $primaryHours = $tt['hours_worked'];
                            } else {
                                $techHours[$tt['user_id']] = $tt['hours_worked'];
                            }
                        }
                        $additionalTechs = array_keys($techHours);
                        ?>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="technician_id" class="form-label">Κύριος Τεχνικός <span class="text-danger">*</span></label>
                                <select class="form-select" id="technician_id" name="technician_id" required>
                                    <?php foreach ($technicians as $tech): ?>
                                        <option value="<?= $tech['id'] ?>" 
                                            <?= $tech['id'] == $task['technician_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tech['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2 mb-3">
                                <label for="primary_technician_hours" class="form-label">Ώρες</label>
                                <input type="number" class="form-control" id="primary_technician_hours" 
                                       name="primary_technician_hours" value="<?= $primaryHours ?>" 
                                       min="0" max="24" step="0.5">
                            </div>

                            <!-- Additional Technicians -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Επιπλέον Τεχνικοί</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;" id="additional_technicians_container">
                                    <?php foreach ($technicians as $tech): 
                                        $isChecked = in_array($tech['id'], $additionalTechs);
                                        $hours = $techHours[$tech['id']] ?? 8;
                                    ?>
                                        <div class="form-check mb-2">
                                            <div class="row align-items-center">
                                                <div class="col-7">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="additional_technicians[]" 
                                                           value="<?= $tech['id'] ?>" 
                                                           id="tech_<?= $tech['id'] ?>"
                                                           <?= $isChecked ? 'checked' : '' ?>
                                                           onchange="toggleTechnicianHours(<?= $tech['id'] ?>)">
                                                    <label class="form-check-label" for="tech_<?= $tech['id'] ?>">
                                                        <?= htmlspecialchars($tech['name']) ?>
                                                    </label>
                                                </div>
                                                <div class="col-5">
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="technician_hours[<?= $tech['id'] ?>]" 
                                                           id="hours_<?= $tech['id'] ?>"
                                                           value="<?= $hours ?>" min="0" max="24" step="0.5" 
                                                           <?= !$isChecked ? 'disabled' : '' ?>
                                                           style="display: <?= $isChecked ? 'block' : 'none' ?>;">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted">Επιλέξτε τυχόν επιπλέον τεχνικούς που συμμετείχαν</small>
                            </div>
                        </div>

                        <!-- Existing Photos -->
                        <?php if (!empty($task['photos'])): ?>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Υπάρχουσες Φωτογραφίες</label>
                                <div class="row g-2" id="existing_photos">
                                    <?php foreach ($task['photos'] as $photo): ?>
                                        <div class="col-md-2" data-photo="<?= htmlspecialchars($photo) ?>">
                                            <div class="position-relative">
                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($photo) ?>" 
                                                     class="img-thumbnail" 
                                                     style="width: 100%; height: 150px; object-fit: cover;"
                                                     onclick="viewImage('<?= BASE_URL ?>/<?= htmlspecialchars($photo) ?>')">
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                                        onclick="deleteExistingPhoto(this, <?= $task['id'] ?>, '<?= htmlspecialchars($photo) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- New Photos -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="photos" class="form-label">Προσθήκη Νέων Φωτογραφιών</label>
                                <input type="file" class="form-control" id="photos" name="photos[]" 
                                       multiple accept="image/*">
                                <small class="text-muted">Μέγιστο μέγεθος αρχείου: 5MB ανά φωτογραφία. Επιτρεπόμενοι τύποι: JPG, PNG, GIF</small>
                                
                                <!-- Photo Preview -->
                                <div id="photo_preview" class="mt-3 row g-2"></div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Σημειώσεις</label>
                                <textarea class="form-control" id="notes" name="notes" 
                                          rows="3" placeholder="Επιπλέον σημειώσεις..."><?= htmlspecialchars($task['notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Ενημέρωση
                                </button>
                                <a href="<?= BASE_URL ?>/daily-tasks" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Ακύρωση
                                </a>
                                <a href="<?= BASE_URL ?>/daily-tasks/view/<?= $task['id'] ?>" class="btn btn-info">
                                    <i class="fas fa-eye"></i> Προβολή
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Προβολή Φωτογραφίας</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script>
// Time mode toggle
document.querySelectorAll('input[name="time_mode"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'manual') {
            document.getElementById('manual_hours_container').style.display = 'flex';
            document.getElementById('time_range_container').style.display = 'none';
            // Clear time range inputs
            document.getElementById('time_from').value = '';
            document.getElementById('time_to').value = '';
            document.getElementById('calculated_hours').value = '';
        } else {
            document.getElementById('manual_hours_container').style.display = 'none';
            document.getElementById('time_range_container').style.display = 'flex';
            // Clear manual hours
            document.getElementById('hours_worked').value = '';
        }
    });
});

// Auto-calculate hours from time range
function calculateHours() {
    const timeFrom = document.getElementById('time_from').value;
    const timeTo = document.getElementById('time_to').value;
    
    if (timeFrom && timeTo) {
        const from = new Date('2000-01-01 ' + timeFrom);
        const to = new Date('2000-01-01 ' + timeTo);
        
        let diff = (to - from) / 1000 / 60 / 60; // Difference in hours
        
        // Handle overnight shifts
        if (diff < 0) {
            diff += 24;
        }
        
        document.getElementById('calculated_hours').value = diff.toFixed(2) + ' ώρες';
        document.getElementById('hours_worked').value = diff.toFixed(2);
    }
}

document.getElementById('time_from').addEventListener('change', calculateHours);
document.getElementById('time_to').addEventListener('change', calculateHours);

// Photo preview for new uploads
document.getElementById('photos').addEventListener('change', function(e) {
    const preview = document.getElementById('photo_preview');
    preview.innerHTML = '';
    
    const files = Array.from(e.target.files);
    
    files.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-2';
                
                const imgContainer = document.createElement('div');
                imgContainer.className = 'position-relative';
                imgContainer.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 150px; object-fit: cover;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                            onclick="removePreview(this, ${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                col.appendChild(imgContainer);
                preview.appendChild(col);
            };
            
            reader.readAsDataURL(file);
        }
    });
});

// Remove photo from preview (new uploads)
function removePreview(button, index) {
    const photoInput = document.getElementById('photos');
    const dt = new DataTransfer();
    const files = Array.from(photoInput.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    photoInput.files = dt.files;
    button.closest('.col-md-2').remove();
}

// Delete existing photo via AJAX
function deleteExistingPhoto(button, taskId, photoPath) {
    if (!confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή τη φωτογραφία;')) {
        return;
    }
    
    fetch(`<?= BASE_URL ?>/daily-tasks/delete-photo/${taskId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ photo: photoPath })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the photo container
            const photoContainer = button.closest('[data-photo]');
            photoContainer.remove();
            
            // Show success message
            alert('Η φωτογραφία διαγράφηκε επιτυχώς');
        } else {
            alert('Σφάλμα κατά τη διαγραφή: ' + (data.message || 'Άγνωστο σφάλμα'));
        }
    })
    .catch(error => {
        alert('Σφάλμα κατά τη διαγραφή της φωτογραφίας');
        console.error('Error:', error);
    });
}

// View image in modal
function viewImage(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

// Prevent main technician from being selected as additional
document.getElementById('technician_id').addEventListener('change', function() {
    const mainTechId = this.value;
    const checkboxes = document.querySelectorAll('input[name="additional_technicians[]"]');
    
    checkboxes.forEach(cb => {
        if (cb.value === mainTechId) {
            cb.checked = false;
            cb.disabled = true;
            // Hide hours input for main technician
            const hoursInput = document.getElementById('hours_' + mainTechId);
            if (hoursInput) {
                hoursInput.style.display = 'none';
                hoursInput.disabled = true;
            }
        } else {
            cb.disabled = false;
        }
    });
});

// Toggle technician hours input visibility
function toggleTechnicianHours(techId) {
    const checkbox = document.getElementById('tech_' + techId);
    const hoursInput = document.getElementById('hours_' + techId);
    
    if (checkbox && hoursInput) {
        if (checkbox.checked) {
            hoursInput.style.display = 'block';
            hoursInput.disabled = false;
        } else {
            hoursInput.style.display = 'none';
            hoursInput.disabled = true;
        }
    }
}

// Trigger on page load
document.getElementById('technician_id').dispatchEvent(new Event('change'));

// ===================================
// MATERIALS MANAGEMENT
// ===================================
let dailyTaskMaterialCounter = 0;

/**
 * Add a new material row
 */
function addDailyTaskMaterialRow(data = null) {
    dailyTaskMaterialCounter++;
    const container = document.getElementById('materials_container');
    const emptyMessage = document.getElementById('materials_empty');
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    const row = document.createElement('div');
    row.className = 'material-row border rounded p-3 mb-3 bg-light';
    row.dataset.index = dailyTaskMaterialCounter;
    
    row.innerHTML = `
        <div class="row g-2">
            <div class="col-md-12 mb-2">
                <label class="form-label small">
                    Ονομασία Υλικού
                    <span class="text-muted" style="font-size: 0.8rem;">(Ξεκινήστε να γράφετε για προτάσεις)</span>
                </label>
                <input type="text" 
                       class="form-control material-name-input" 
                       name="materials[${dailyTaskMaterialCounter}][name]" 
                       value="${data?.name || ''}"
                       placeholder="π.χ. Τσιμέντο, Σωλήνας PVC, Καλώδιο"
                       autocomplete="off"
                       data-row-index="${dailyTaskMaterialCounter}"
                       required>
                <input type="hidden" 
                       name="materials[${dailyTaskMaterialCounter}][catalog_material_id]" 
                       value="${data?.catalog_material_id || ''}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Μονάδα Μέτρησης</label>
                <input type="text" 
                       class="form-control" 
                       name="materials[${dailyTaskMaterialCounter}][unit]" 
                       value="${data?.unit || ''}"
                       placeholder="τεμ, μέτρα, κιλά"
                       list="unit_types"
                       required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Τιμή Μονάδας (€)</label>
                <input type="number" 
                       class="form-control material-price" 
                       name="materials[${dailyTaskMaterialCounter}][unit_price]" 
                       value="${data?.unit_price || ''}"
                       step="0.01" 
                       min="0"
                       placeholder="0.00"
                       onchange="calculateDailyTaskMaterialSubtotal(${dailyTaskMaterialCounter})"
                       required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Ποσότητα</label>
                <input type="number" 
                       class="form-control material-quantity" 
                       name="materials[${dailyTaskMaterialCounter}][quantity]" 
                       value="${data?.quantity || ''}"
                       step="0.01" 
                       min="0"
                       placeholder="0"
                       onchange="calculateDailyTaskMaterialSubtotal(${dailyTaskMaterialCounter})"
                       required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Σύνολο</label>
                <div class="input-group">
                    <input type="text" 
                           class="form-control material-subtotal" 
                           id="material_subtotal_${dailyTaskMaterialCounter}"
                           value="${data ? (data.unit_price * data.quantity).toFixed(2) : '0.00'}"
                           readonly>
                    <span class="input-group-text">€</span>
                    <button type="button" 
                            class="btn btn-danger" 
                            onclick="removeDailyTaskMaterialRow(${dailyTaskMaterialCounter})"
                            title="Αφαίρεση">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(row);
    
    // Initialize autocomplete for the material name input
    const nameInput = row.querySelector('.material-name-input');
    if (nameInput && typeof window.initMaterialAutocomplete === 'function') {
        window.initMaterialAutocomplete(nameInput, dailyTaskMaterialCounter);
    }
}

/**
 * Remove a material row
 */
function removeDailyTaskMaterialRow(index) {
    const row = document.querySelector(`.material-row[data-index="${index}"]`);
    if (row) {
        row.remove();
        
        // Check if container is empty
        const container = document.getElementById('materials_container');
        const emptyMessage = document.getElementById('materials_empty');
        
        if (container.children.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        }
    }
}

/**
 * Calculate material subtotal
 */
function calculateDailyTaskMaterialSubtotal(index) {
    const row = document.querySelector(`.material-row[data-index="${index}"]`);
    if (!row) return;
    
    const price = parseFloat(row.querySelector('.material-price').value) || 0;
    const quantity = parseFloat(row.querySelector('.material-quantity').value) || 0;
    const subtotal = price * quantity;
    
    row.querySelector('.material-subtotal').value = subtotal.toFixed(2);
}

// Load existing materials on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($materials)): ?>
        <?php foreach ($materials as $material): ?>
            addDailyTaskMaterialRow({
                name: <?= json_encode($material['name']) ?>,
                unit: <?= json_encode($material['unit'] ?? '') ?>,
                unit_price: <?= json_encode($material['unit_price']) ?>,
                quantity: <?= json_encode($material['quantity']) ?>,
                catalog_material_id: <?= json_encode($material['catalog_material_id'] ?? '') ?>
            });
        <?php endforeach; ?>
    <?php endif; ?>
});
</script>

<!-- Include material autocomplete script -->
<script src="<?= BASE_URL ?>/public/js/material-autocomplete.js"></script>

