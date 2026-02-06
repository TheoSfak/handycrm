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
                            <div class="col-md-6">
                                <label class="form-label">Ημερομηνία Συντήρησης <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="maintenance_date" required onchange="calculateNextMaintenance()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Επόμενη Συντήρηση (Αυτόματα +1 έτος)</label>
                                <input type="date" class="form-control" name="next_maintenance_date" readonly 
                                       style="background-color: #e9ecef;">
                                <small class="text-muted">Υπολογίζεται αυτόματα</small>
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

                        <!-- Section 5: Observations & Photo (Πεδία 16-17) -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-comment-alt"></i> Παρατηρήσεις & Φωτογραφία</h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Παρατηρήσεις</label>
                                <textarea class="form-control" name="observations" rows="4" 
                                          placeholder="Εισάγετε τυχόν παρατηρήσεις..."></textarea>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Φωτογραφία</label>
                                <input type="file" class="form-control" name="photo" accept="image/*" 
                                       onchange="previewPhoto(this)">
                                <small class="text-muted">Επιτρεπόμενοι τύποι: JPG, PNG, GIF. Μέγιστο μέγεθος: 5MB</small>
                                <div id="photoPreview" class="mt-3" style="display: none;">
                                    <img id="previewImg" src="" style="max-width: 300px; max-height: 300px;" class="img-thumbnail">
                                </div>
                            </div>
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

// Photo preview
function previewPhoto(input) {
    const preview = document.getElementById('photoPreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
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
