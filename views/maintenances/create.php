<?php $pageTitle = 'Νέα Συντήρηση Μ/Σ'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-plus"></i> Νέα Συντήρηση Μετασχηματιστή</h4>
                    <a href="<?= BASE_URL ?>/maintenances" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Πίσω
                    </a>
                </div>
                <div class="card-body">
                    <form id="maintenanceForm" method="POST" action="<?= BASE_URL ?>/maintenances/store" enctype="multipart/form-data">
                        
                        <!-- Section 1: Customer Info (Πεδία 1-4) -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-user"></i> Στοιχεία Πελάτη</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Όνομα Πελάτη <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Τηλέφωνο</label>
                                <input type="text" class="form-control" name="phone">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Διεύθυνση</label>
                                <input type="text" class="form-control" name="address">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Άλλα Στοιχεία</label>
                                <textarea class="form-control" name="other_details" rows="2"></textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Section 2: Maintenance Info (Πεδία 5-6) -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-calendar"></i> Στοιχεία Συντήρησης</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Ημερομηνία Συντήρησης <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="maintenance_date" required onchange="calculateNextMaintenance()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Επόμενη Συντήρηση (Αυτόματα +1 έτος)</label>
                                <input type="date" class="form-control" name="next_maintenance_date" readonly 
                                       style="background-color: #e9ecef;">
                                <small class="text-muted">Υπολογίζεται αυτόματα</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Υπεύθυνος Τεχνικός <span class="text-danger">*</span></label>
                                <select class="form-select" name="created_by" required>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= $user['id'] == $_SESSION['user_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['name']) ?>
                                            <?php if (!empty($user['role'])): ?>
                                                (<?= ucfirst($user['role']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Προεπιλεγμένος: Εσείς</small>
                            </div>
                        </div>
                        
                        <!-- Additional Technicians -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label"><i class="fas fa-users"></i> Επιπλέον Τεχνικοί (Προαιρετικό)</label>
                                <select class="form-select" name="additional_technicians[]" multiple size="4">
                                    <?php foreach ($users as $user): ?>
                                        <?php if ($user['id'] != $_SESSION['user_id']): // Don't show current user ?>
                                            <option value="<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['name']) ?>
                                                <?php if (!empty($user['role'])): ?>
                                                    (<?= ucfirst($user['role']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Κρατήστε πατημένο το Ctrl (ή Cmd) για να επιλέξετε πολλαπλούς</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Section 3: Transformers (Multiple) -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-bolt"></i> Μετασχηματιστές</h5>
                        
                        <div id="transformersContainer">
                            <!-- Transformers will be added here dynamically -->
                        </div>

                        <!-- Add Transformer Button -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-success" onclick="addTransformer()">
                                <i class="fas fa-plus-circle"></i> Προσθήκη Μετασχηματιστή
                            </button>
                        </div>

                        <hr class="my-4">

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Αποθήκευση
                                </button>
                                <a href="<?= BASE_URL ?>/maintenances" class="btn btn-secondary btn-lg">
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
// Auto-calculate next maintenance date (+1 year)
document.querySelector('input[name="maintenance_date"]').addEventListener('change', function() {
    const maintenanceDate = new Date(this.value);
    if (!isNaN(maintenanceDate)) {
        maintenanceDate.setFullYear(maintenanceDate.getFullYear() + 1);
        const nextDate = maintenanceDate.toISOString().split('T')[0];
        document.querySelector('input[name="next_maintenance_date"]').value = nextDate;
    }
});

// Photo preview for transformer-specific photos
function previewTransformerPhotos(input, transformerIndex) {
    const preview = document.getElementById('photo_preview_' + transformerIndex);
    preview.innerHTML = '';
    
    Array.from(input.files).forEach((file, index) => {
        if (!file.type.match('image.*')) {
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-md-2';
            
            const imgContainer = document.createElement('div');
            imgContainer.className = 'position-relative';
            imgContainer.innerHTML = `
                <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 150px; object-fit: cover;">
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                        onclick="removeTransformerPhoto(this, ${transformerIndex}, ${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            col.appendChild(imgContainer);
            preview.appendChild(col);
        };
        
        reader.readAsDataURL(file);
    });
}

// Remove photo from transformer preview
function removeTransformerPhoto(button, transformerIndex, photoIndex) {
    const photoInput = document.querySelector(`input[name="transformer_photos[${transformerIndex}][]"]`);
    const dt = new DataTransfer();
    const files = Array.from(photoInput.files);
    
    files.forEach((file, i) => {
        if (i !== photoIndex) {
            dt.items.add(file);
        }
    });
    
    photoInput.files = dt.files;
    button.closest('.col-md-2').remove();
}

// Warn before leaving if form has data
let formChanged = false;
document.getElementById('maintenanceForm').addEventListener('input', function() {
    formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

document.getElementById('maintenanceForm').addEventListener('submit', function() {
    formChanged = false;
});

// Calculate next maintenance date automatically
function calculateNextMaintenance() {
    const maintenanceDate = document.querySelector('input[name="maintenance_date"]').value;
    if (maintenanceDate) {
        const date = new Date(maintenanceDate);
        date.setFullYear(date.getFullYear() + 1);
        const nextDate = date.toISOString().split('T')[0];
        document.querySelector('input[name="next_maintenance_date"]').value = nextDate;
    }
}

// Transformer Management
let transformerCount = 0;

// Initialize with one transformer on page load
document.addEventListener('DOMContentLoaded', function() {
    addTransformer();
});

function addTransformer() {
    transformerCount++;
    
    const transformerHTML = `
        <div class="card mb-4 transformer-block" id="transformer-${transformerCount}" data-index="${transformerCount}">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-bolt"></i> Μετασχηματιστής <span class="transformer-number">${transformerCount}</span></h6>
                <button type="button" class="btn btn-sm btn-danger remove-transformer" onclick="removeTransformer(${transformerCount})" ${transformerCount === 1 ? 'style="display:none;"' : ''}>
                    <i class="fas fa-trash"></i> Αφαίρεση
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Ισχύς Μ/Σ (kVA) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="transformers[${transformerCount}][power]" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Τύπος Μετασχηματιστή <span class="text-danger">*</span></label>
                        <select class="form-select" name="transformers[${transformerCount}][type]" 
                                onchange="toggleOilFields(${transformerCount})" required>
                            <option value="oil">Ελαίου</option>
                            <option value="dry">Ξηρού Τύπου</option>
                        </select>
                    </div>
                </div>

                <h6 class="text-secondary mt-3 mb-2"><i class="fas fa-clipboard-check"></i> Μετρήσεις Μόνωσης</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <textarea class="form-control" name="transformers[${transformerCount}][insulation]" rows="3" 
                                  required placeholder="Εισάγετε τις μετρήσεις μόνωσης..."></textarea>
                    </div>
                </div>

                <h6 class="text-secondary mt-3 mb-2"><i class="fas fa-magnet"></i> Αντίσταση Πηνίων</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <textarea class="form-control" name="transformers[${transformerCount}][coil_resistance]" rows="3" 
                                  required placeholder="Εισάγετε τις μετρήσεις αντίστασης πηνίων..."></textarea>
                    </div>
                </div>

                <h6 class="text-secondary mt-3 mb-2"><i class="fas fa-plug"></i> Γείωση</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <input type="text" class="form-control" name="transformers[${transformerCount}][grounding]" 
                               required placeholder="π.χ. 2.5 Ω">
                    </div>
                </div>

                <h6 class="text-secondary mt-3 mb-2"><i class="fas fa-flask"></i> Διηλεκτρική Αντοχή Λαδιού</h6>
                <div class="row mb-3 oil-fields" id="oil-fields-${transformerCount}">
                    <div class="col-md-2">
                        <label class="form-label">Τιμή 1 (kV)</label>
                        <input type="text" class="form-control oil-input" name="transformers[${transformerCount}][oil_v1]">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Τιμή 2 (kV)</label>
                        <input type="text" class="form-control oil-input" name="transformers[${transformerCount}][oil_v2]">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Τιμή 3 (kV)</label>
                        <input type="text" class="form-control oil-input" name="transformers[${transformerCount}][oil_v3]">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Τιμή 4 (kV)</label>
                        <input type="text" class="form-control oil-input" name="transformers[${transformerCount}][oil_v4]">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Τιμή 5 (kV)</label>
                        <input type="text" class="form-control oil-input" name="transformers[${transformerCount}][oil_v5]">
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="text-secondary mt-3 mb-2"><i class="fas fa-tools"></i> Υλικά</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <textarea class="form-control" name="transformers[${transformerCount}][materials]" rows="3" 
                                  placeholder="Εισάγετε τα υλικά που χρησιμοποιήθηκαν..."></textarea>
                    </div>
                </div>

                <h6 class="text-secondary mt-3 mb-2"><i class="fas fa-comment-alt"></i> Παρατηρήσεις</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <textarea class="form-control" name="transformers[${transformerCount}][observations]" rows="3" 
                                  placeholder="Εισάγετε τυχόν παρατηρήσεις για αυτόν τον μετασχηματιστή..."></textarea>
                    </div>
                </div>

                <h6 class="text-secondary mt-3 mb-2"><i class="fas fa-camera"></i> Φωτογραφίες</h6>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <input type="file" class="form-control photo-input" name="transformer_photos[${transformerCount}][]" 
                               multiple accept="image/*" onchange="previewTransformerPhotos(this, ${transformerCount})">
                        <small class="text-muted">Μέγιστο μέγεθος αρχείου: 5MB ανά φωτογραφία. Επιτρεπόμενοι τύποι: JPG, PNG, GIF</small>
                        
                        <!-- Photo Preview for this transformer -->
                        <div id="photo_preview_${transformerCount}" class="mt-3 row g-2"></div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('transformersContainer').insertAdjacentHTML('beforeend', transformerHTML);
    updateRemoveButtons();
}

function removeTransformer(index) {
    const visibleTransformers = document.querySelectorAll('.transformer-block');
    if (visibleTransformers.length <= 1) {
        alert('Πρέπει να υπάρχει τουλάχιστον ένας μετασχηματιστής!');
        return;
    }
    
    const transformer = document.getElementById('transformer-' + index);
    if (transformer) {
        transformer.remove();
        renumberTransformers();
    }
}

function renumberTransformers() {
    const transformers = document.querySelectorAll('.transformer-block');
    transformers.forEach((transformer, index) => {
        const number = index + 1;
        transformer.querySelector('.transformer-number').textContent = number;
    });
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const transformers = document.querySelectorAll('.transformer-block');
    const removeButtons = document.querySelectorAll('.remove-transformer');
    removeButtons.forEach(btn => {
        btn.style.display = transformers.length > 1 ? 'inline-block' : 'none';
    });
}

// Toggle oil fields based on transformer type
function toggleOilFields(transformerIndex) {
    const typeSelect = document.querySelector(`select[name="transformers[${transformerIndex}][type]"]`);
    const oilFieldsContainer = document.getElementById(`oil-fields-${transformerIndex}`);
    const oilTitle = oilFieldsContainer.previousElementSibling; // The h6 title
    const oilInputs = oilFieldsContainer.querySelectorAll('.oil-input');
    
    if (typeSelect.value === 'dry') {
        // Hide oil fields for dry type transformers
        oilFieldsContainer.style.display = 'none';
        oilTitle.style.display = 'none';
        
        // Clear and remove required attribute from oil inputs
        oilInputs.forEach(input => {
            input.value = '';
            input.removeAttribute('required');
        });
    } else {
        // Show oil fields for oil type transformers
        oilFieldsContainer.style.display = 'flex';
        oilTitle.style.display = 'block';
        
        // No need to add required attribute as oil fields are optional
    }
}
</script>
