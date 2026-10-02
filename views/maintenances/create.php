<?php 
$pageTitle = 'Νέα Συντήρηση Μ/Σ';
$isRenewal = !empty($prefill['is_renewal']);
$customerName = $prefill['customer_name'] ?? '';
$phone = $prefill['phone'] ?? '';
$address = $prefill['address'] ?? '';
$otherDetails = $prefill['other_details'] ?? '';
$previousId = $prefill['previous_id'] ?? '';
$maintenanceDate = date('Y-m-d');
$nextMaintenanceDate = date('Y-m-d', strtotime('+1 year'));
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <?php if ($isRenewal): ?>
                <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm" role="alert">
                    <i class="fas fa-sync-alt fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">🔄 Ετήσια Ανανέωση Συντήρησης</h5>
                        <span>Εκτελείται νέα συντήρηση για τον πελάτη <strong><?= htmlspecialchars($customerName) ?></strong>. Τα πάγια στοιχεία και οι μετασχηματιστές έχουν προ-συμπληρωθεί. Μόλις αποθηκεύσετε, η προηγούμενη συντήρηση θα αρχειοθετηθεί αυτόματα ως ανανεωμένη.</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-<?= $isRenewal ? 'sync-alt text-success' : 'plus text-primary' ?>"></i> 
                        <?= $isRenewal ? 'Ετήσια Ανανέωση Συντήρησης Μ/Σ' : 'Νέα Συντήρηση Μετασχηματιστή' ?>
                    </h4>
                    <a href="<?= BASE_URL ?>/maintenances" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Πίσω
                    </a>
                </div>
                <div class="card-body">
                    <form id="maintenanceForm" method="POST" action="<?= BASE_URL ?>/maintenances/store" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="previous_id" id="previous_id" value="<?= htmlspecialchars((string)$previousId) ?>">
                        
                        <!-- Section 1: Customer Info (Πεδία 1-4) -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 text-primary"><i class="fas fa-user"></i> Στοιχεία Πελάτη</h5>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-database text-info me-1"></i> <?= count($maintenanceCustomers ?? []) ?> καταχωρημένοι πελάτες συντηρήσεων
                            </span>
                        </div>

                        <!-- Match banner (hidden by default) -->
                        <div id="customerMatchAlert" class="alert alert-info py-2 px-3 mb-3 d-none align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span id="customerMatchText">Επιλέχθηκε υπάρχων πελάτης συντηρήσεων.</span>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Όνομα Πελάτη <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="customer_name" id="customer_name_input"
                                           list="maintenanceCustomersList" autocomplete="off" required
                                           value="<?= htmlspecialchars($customerName) ?>"
                                           placeholder="Αναζήτηση ή πληκτρολόγηση νέου πελάτη...">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Προβολή λίστας πελατών">
                                        <i class="fas fa-list"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow" style="max-height: 280px; overflow-y: auto;">
                                        <li><h6 class="dropdown-header">Πελάτες Συντηρήσεων</h6></li>
                                        <?php if (empty($maintenanceCustomers)): ?>
                                            <li><span class="dropdown-item text-muted">Δεν υπάρχουν καταχωρημένοι πελάτες</span></li>
                                        <?php else: ?>
                                            <?php foreach ($maintenanceCustomers as $mc): ?>
                                                <li>
                                                    <a class="dropdown-item customer-quick-select" href="javascript:void(0)"
                                                       data-name="<?= htmlspecialchars($mc['customer_name']) ?>">
                                                        <i class="fas fa-bolt text-warning me-1"></i>
                                                        <strong><?= htmlspecialchars($mc['customer_name']) ?></strong>
                                                        <?php if (!empty($mc['phone'])): ?>
                                                            <small class="text-muted ms-1">(<?= htmlspecialchars($mc['phone']) ?>)</small>
                                                        <?php endif; ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <datalist id="maintenanceCustomersList">
                                    <?php foreach ($maintenanceCustomers ?? [] as $mc): ?>
                                        <option value="<?= htmlspecialchars($mc['customer_name']) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                                <small class="text-muted">Επιλέξτε από το dropdown ή πληκτρολογήστε ελεύθερα νέο πελάτη.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Τηλέφωνο</label>
                                <input type="text" class="form-control" name="phone" id="phone_input" value="<?= htmlspecialchars($phone) ?>">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Διεύθυνση</label>
                                <input type="text" class="form-control" name="address" id="address_input" value="<?= htmlspecialchars($address) ?>">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Άλλα Στοιχεία</label>
                                <textarea class="form-control" name="other_details" id="other_details_input" rows="2"><?= htmlspecialchars($otherDetails) ?></textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Section 2: Maintenance Info (Πεδία 5-6) -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-calendar"></i> Στοιχεία Συντήρησης</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ημερομηνία Συντήρησης <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="maintenance_date" value="<?= $maintenanceDate ?>" required onchange="calculateNextMaintenance()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Επόμενη Συντήρηση (Αυτόματα +1 έτος)</label>
                                <input type="date" class="form-control" name="next_maintenance_date" value="<?= $nextMaintenanceDate ?>" readonly 
                                       style="background-color: #e9ecef;">
                                <small class="text-muted">Υπολογίζεται αυτόματα</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Υπεύθυνος Τεχνικός <span class="text-danger">*</span></label>
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
                                <select class="form-select" name="additional_technicians[]" multiple size="3">
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 text-primary"><i class="fas fa-bolt"></i> Μετασχηματιστές</h5>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="addTransformer()">
                                <i class="fas fa-plus-circle me-1"></i> Προσθήκη Επιπλέον Μ/Σ
                            </button>
                        </div>
                        
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
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="fas fa-save me-1"></i> Αποθήκευση Συντήρησης
                                </button>
                                <a href="<?= BASE_URL ?>/maintenances" class="btn btn-secondary btn-lg ms-2">
                                    <i class="fas fa-times me-1"></i> Ακύρωση
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
// Known maintenance customers map
const maintenanceCustomersMap = <?= json_encode($maintenanceCustomers ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

// Prefilled transformers configuration if renewing
const prefilledTransformers = <?= !empty($prefill['transformers_data']) ? $prefill['transformers_data'] : (!empty($prefill['transformer_power']) ? json_encode([['power' => $prefill['transformer_power'], 'type' => $prefill['transformer_type'] ?? 'oil']]) : 'null') ?>;

// Customer selection handler
function onCustomerSelected(customerName) {
    if (!customerName) return;
    const trimmed = customerName.trim().toLowerCase();
    const found = maintenanceCustomersMap.find(c => c.customer_name && c.customer_name.trim().toLowerCase() === trimmed);
    
    const alertBox = document.getElementById('customerMatchAlert');
    const alertText = document.getElementById('customerMatchText');
    
    if (found) {
        if (found.phone) document.getElementById('phone_input').value = found.phone;
        if (found.address) document.getElementById('address_input').value = found.address;
        if (found.other_details) document.getElementById('other_details_input').value = found.other_details;
        if (found.last_maintenance_id) {
            document.getElementById('previous_id').value = found.last_maintenance_id;
        }

        // If user hasn't filled transformers and found record has transformers_data
        let tfCount = document.querySelectorAll('.transformer-block').length;
        if (tfCount <= 1 && found.transformers_data) {
            try {
                const tfData = JSON.parse(found.transformers_data);
                if (Array.isArray(tfData) && tfData.length > 0) {
                    // Check if current transformer is untouched
                    const firstPower = document.querySelector('input[name="transformers[1][power]"]');
                    if (firstPower && !firstPower.value) {
                        document.getElementById('transformersContainer').innerHTML = '';
                        transformerCount = 0;
                        tfData.forEach(t => {
                            addTransformer(t.power || '', t.type || 'oil');
                        });
                    }
                }
            } catch(e) {}
        }

        if (alertBox && alertText) {
            alertText.innerHTML = `<strong>${found.customer_name}</strong>: Φορτώθηκαν τα στοιχεία και συνδέθηκε με την προηγούμενη συντήρηση (#${found.last_maintenance_id || ''}).`;
            alertBox.classList.remove('d-none');
            alertBox.classList.add('d-flex');
        }
    } else {
        if (alertBox) {
            alertBox.classList.add('d-none');
            alertBox.classList.remove('d-flex');
        }
    }
}

// Quick select click
document.querySelectorAll('.customer-quick-select').forEach(item => {
    item.addEventListener('click', function() {
        const name = this.getAttribute('data-name');
        const input = document.getElementById('customer_name_input');
        input.value = name;
        onCustomerSelected(name);
    });
});

// Auto-fill on input / change
document.getElementById('customer_name_input').addEventListener('input', function() {
    onCustomerSelected(this.value);
});
document.getElementById('customer_name_input').addEventListener('change', function() {
    onCustomerSelected(this.value);
});

// Calculate next maintenance date (+1 year)
function calculateNextMaintenance() {
    const maintenanceDateInput = document.querySelector('input[name="maintenance_date"]');
    if (maintenanceDateInput && maintenanceDateInput.value) {
        const date = new Date(maintenanceDateInput.value);
        date.setFullYear(date.getFullYear() + 1);
        const nextDate = date.toISOString().split('T')[0];
        document.querySelector('input[name="next_maintenance_date"]').value = nextDate;
    }
}

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

// Transformer Management
let transformerCount = 0;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (Array.isArray(prefilledTransformers) && prefilledTransformers.length > 0) {
        prefilledTransformers.forEach(tf => {
            addTransformer(tf.power || '', tf.type || 'oil');
        });
    } else {
        addTransformer();
    }
});

function addTransformer(defaultPower = '', defaultType = 'oil') {
    transformerCount++;
    const isDry = (defaultType === 'dry');
    
    const transformerHTML = `
        <div class="card mb-4 transformer-block shadow-sm" id="transformer-${transformerCount}" data-index="${transformerCount}">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-bolt"></i> Μετασχηματιστής <span class="transformer-number">${transformerCount}</span></h6>
                <button type="button" class="btn btn-sm btn-danger remove-transformer" onclick="removeTransformer(${transformerCount})" ${transformerCount === 1 ? 'style="display:none;"' : ''}>
                    <i class="fas fa-trash"></i> Αφαίρεση
                </button>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Ισχύς Μ/Σ (kVA) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="transformers[${transformerCount}][power]" 
                               value="${defaultPower}" placeholder="π.χ. 630" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Τύπος Μετασχηματιστή <span class="text-danger">*</span></label>
                        <select class="form-select" name="transformers[${transformerCount}][type]" 
                                onchange="toggleOilFields(${transformerCount})" required>
                            <option value="oil" ${!isDry ? 'selected' : ''}>Ελαίου</option>
                            <option value="dry" ${isDry ? 'selected' : ''}>Ξηρού Τύπου</option>
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

                <h6 class="text-secondary mt-3 mb-2 oil-title" style="display: ${isDry ? 'none' : 'block'};"><i class="fas fa-flask"></i> Διηλεκτρική Αντοχή Λαδιού</h6>
                <div class="row mb-3 oil-fields" id="oil-fields-${transformerCount}" style="display: ${isDry ? 'none' : 'flex'};">
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
    const oilTitle = oilFieldsContainer.previousElementSibling;
    const oilInputs = oilFieldsContainer.querySelectorAll('.oil-input');
    
    if (typeSelect.value === 'dry') {
        oilFieldsContainer.style.display = 'none';
        if (oilTitle) oilTitle.style.display = 'none';
        oilInputs.forEach(input => {
            input.value = '';
        });
    } else {
        oilFieldsContainer.style.display = 'flex';
        if (oilTitle) oilTitle.style.display = 'block';
    }
}
</script>
