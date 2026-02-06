<?php $pageTitle = 'Νέα Εργασία Ημέρας'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-clipboard-list"></i> Νέα Εργασία Ημέρας</h4>
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
                    <form method="POST" action="<?= BASE_URL ?>/daily-tasks/store" enctype="multipart/form-data" id="taskForm">
                        <div class="row">
                            <!-- Date -->
                            <div class="col-md-3 mb-3">
                                <label for="date" class="form-label">Ημερομηνία <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <!-- Task Type -->
                            <div class="col-md-3 mb-3">
                                <label for="task_type" class="form-label">Τύπος Εργασίας <span class="text-danger">*</span></label>
                                <select class="form-select" id="task_type" name="task_type" required>
                                    <option value="">-- Επιλέξτε --</option>
                                    <option value="electrical">Ηλεκτρολογικές Εργασίες</option>
                                    <option value="inspection">Επίσκεψη/Έλεγχος</option>
                                    <option value="fault_repair">Έλεγχος Βλάβες / Αποκατάσταση Βλάβης</option>
                                    <option value="other">Διάφορα</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Κατάσταση <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="completed" selected>Ολοκληρώθηκε</option>
                                    <option value="in_progress">Σε Εξέλιξη</option>
                                    <option value="cancelled">Ακυρώθηκε</option>
                                </select>
                            </div>

                            <!-- Is Invoiced -->
                            <div class="col-md-3 mb-3">
                                <label for="is_invoiced" class="form-label">Τιμολογήθηκε</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_invoiced" 
                                           name="is_invoiced" value="1">
                                    <label class="form-check-label" for="is_invoiced">Ναι</label>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="customer_name" class="form-label">Πελάτης <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" 
                                       name="customer_name" required placeholder="Όνομα πελάτη">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="address" class="form-label">Διεύθυνση</label>
                                <input type="text" class="form-control" id="address" 
                                       name="address" placeholder="Διεύθυνση">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="phone" class="form-label">Τηλέφωνο</label>
                                <input type="text" class="form-control" id="phone" 
                                       name="phone" placeholder="Τηλέφωνο επικοινωνίας">
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Περιγραφή Εργασίας <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="4" required placeholder="Λεπτομερής περιγραφή της εργασίας..."></textarea>
                            </div>
                        </div>

                        <!-- Time Tracking -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Καταγραφή Ωρών</label>
                                <div class="btn-group mb-3" role="group">
                                    <input type="radio" class="btn-check" name="time_mode" id="time_mode_manual" value="manual" checked>
                                    <label class="btn btn-outline-primary" for="time_mode_manual">
                                        <i class="fas fa-keyboard"></i> Χειροκίνητη Εισαγωγή
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="time_mode" id="time_mode_range" value="range">
                                    <label class="btn btn-outline-primary" for="time_mode_range">
                                        <i class="fas fa-clock"></i> Εύρος Ωρών
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="manual_hours_container">
                            <div class="col-md-3 mb-3">
                                <label for="hours_worked" class="form-label">Συνολικές Ώρες</label>
                                <input type="number" class="form-control" id="hours_worked" 
                                       name="hours_worked" step="0.25" min="0" placeholder="π.χ. 2.5">
                                <small class="text-muted">Σε δεκαδικές ώρες (π.χ. 2.5 = 2 ώρες 30 λεπτά)</small>
                            </div>
                        </div>

                        <div class="row" id="time_range_container" style="display: none;">
                            <div class="col-md-3 mb-3">
                                <label for="time_from" class="form-label">Από Ώρα</label>
                                <input type="time" class="form-control" id="time_from" name="time_from">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="time_to" class="form-label">Έως Ώρα</label>
                                <input type="time" class="form-control" id="time_to" name="time_to">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Υπολογισμένες Ώρες</label>
                                <input type="text" class="form-control" id="calculated_hours" readonly 
                                       placeholder="Θα υπολογιστούν αυτόματα">
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
                                <div id="materials_empty" class="text-muted small mb-2">
                                    Δεν έχουν προστεθεί υλικά ακόμα
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addDailyTaskMaterialRow()">
                                    <i class="fas fa-plus"></i> Προσθήκη Υλικού
                                </button>
                            </div>
                        </div>

                        <!-- Technician -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="technician_id" class="form-label">Κύριος Τεχνικός <span class="text-danger">*</span></label>
                                <select class="form-select" id="technician_id" name="technician_id" required>
                                    <?php foreach ($technicians as $tech): ?>
                                        <option value="<?= $tech['id'] ?>" 
                                            <?= $tech['id'] == $_SESSION['user_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tech['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2 mb-3">
                                <label for="primary_technician_hours" class="form-label">Ώρες</label>
                                <input type="number" class="form-control" id="primary_technician_hours" 
                                       name="primary_technician_hours" value="8" min="0" max="24" step="0.5">
                            </div>

                            <!-- Additional Technicians -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Επιπλέον Τεχνικοί</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;" id="additional_technicians_container">
                                    <?php foreach ($technicians as $tech): ?>
                                        <div class="form-check mb-2">
                                            <div class="row align-items-center">
                                                <div class="col-7">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="additional_technicians[]" 
                                                           value="<?= $tech['id'] ?>" 
                                                           id="tech_<?= $tech['id'] ?>"
                                                           onchange="toggleTechnicianHours(<?= $tech['id'] ?>)">
                                                    <label class="form-check-label" for="tech_<?= $tech['id'] ?>">
                                                        <?= htmlspecialchars($tech['name']) ?>
                                                    </label>
                                                </div>
                                                <div class="col-5">
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="technician_hours[<?= $tech['id'] ?>]" 
                                                           id="hours_<?= $tech['id'] ?>"
                                                           value="8" min="0" max="24" step="0.5" 
                                                           disabled style="display: none;">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted">Επιλέξτε τεχνικούς και ορίστε τις ώρες τους</small>
                            </div>
                        </div>

                        <!-- Photos -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="photos" class="form-label">Φωτογραφίες</label>
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
                                          rows="3" placeholder="Επιπλέον σημειώσεις..."></textarea>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Αποθήκευση
                                </button>
                                <a href="<?= BASE_URL ?>/daily-tasks" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Ακύρωση
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
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

// Photo preview
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

// Remove photo from preview
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
</script>

<!-- Include material autocomplete script -->
<script src="<?= BASE_URL ?>/public/js/material-autocomplete.js"></script>

