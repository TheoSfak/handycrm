<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-plus"></i> Νέο Ραντεβού</h2>
    <a href="?route=/appointments" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Πίσω στη Λίστα
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="?route=/appointments/create">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Τίτλος Ραντεβού <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($_SESSION['old_input']['title'] ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Κατάσταση</label>
                        <select class="form-select" id="status" name="status">
                            <option value="scheduled" <?= (($_SESSION['old_input']['status'] ?? 'scheduled') === 'scheduled') ? 'selected' : '' ?>>
                                Προγραμματισμένο
                            </option>
                            <option value="confirmed" <?= (($_SESSION['old_input']['status'] ?? '') === 'confirmed') ? 'selected' : '' ?>>
                                Επιβεβαιωμένο
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Περιγραφή</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_SESSION['old_input']['description'] ?? '') ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Πελάτης <span class="text-danger">*</span></label>
                        <select class="form-select" id="customer_id" name="customer_id" required onchange="loadCustomerProjects(this.value)">
                            <option value="">Επιλέξτε πελάτη...</option>
                            <?php foreach ($customers as $customer): ?>
                                <?php
                                    $customerName = $customer['customer_type'] === 'company' && !empty($customer['company_name']) 
                                        ? $customer['company_name'] 
                                        : $customer['first_name'] . ' ' . $customer['last_name'];
                                    
                                    // Check for preselected customer (from URL) or old input (from form error)
                                    $isSelected = false;
                                    if (isset($_SESSION['old_input']['customer_id'])) {
                                        $isSelected = $_SESSION['old_input']['customer_id'] == $customer['id'];
                                    } elseif (isset($preselected_customer_id)) {
                                        $isSelected = $preselected_customer_id == $customer['id'];
                                    }
                                ?>
                                <option value="<?= $customer['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customerName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            <a href="?route=/customers/create" target="_blank">
                                <i class="fas fa-plus"></i> Προσθήκη νέου πελάτη
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="technician_id" class="form-label">Τεχνικός <span class="text-danger">*</span></label>
                        <select class="form-select" id="technician_id" name="technician_id" required>
                            <option value="">Επιλέξτε τεχνικό...</option>
                            <?php foreach ($technicians as $tech): ?>
                            <option value="<?= $tech['id'] ?>" 
                                    <?= (($_SESSION['old_input']['technician_id'] ?? '') == $tech['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Έργο (Προαιρετικό)</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">Χωρίς έργο</option>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id'] ?>" 
                                    data-customer="<?= $project['customer_id'] ?>"
                                    <?= (($_SESSION['old_input']['project_id'] ?? '') == $project['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($project['title']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            Συνδέστε το ραντεβού με ένα υπάρχον έργο
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="address" class="form-label">Τοποθεσία</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               placeholder="Διεύθυνση ή τοποθεσία"
                               value="<?= htmlspecialchars($_SESSION['old_input']['address'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="appointment_date" class="form-label">Ημερομηνία <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                               value="<?= htmlspecialchars($_SESSION['old_input']['appointment_date'] ?? date('Y-m-d')) ?>" 
                               required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="appointment_time" class="form-label">Ώρα <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                               value="<?= htmlspecialchars($_SESSION['old_input']['appointment_time'] ?? '09:00') ?>" 
                               required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="duration_minutes" class="form-label">Διάρκεια (λεπτά)</label>
                        <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                               value="<?= htmlspecialchars($_SESSION['old_input']['duration_minutes'] ?? '60') ?>" 
                               min="15" step="15">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Σημειώσεις</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" 
                          placeholder="Επιπλέον σημειώσεις ή οδηγίες..."><?= htmlspecialchars($_SESSION['old_input']['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="?route=/appointments" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Ακύρωση
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Αποθήκευση
                </button>
            </div>
        </form>
    </div>
</div>

<?php unset($_SESSION['old_input']); ?>

<script>
// Filter projects based on selected customer
function loadCustomerProjects(customerId) {
    const projectSelect = document.getElementById('project_id');
    const allOptions = projectSelect.querySelectorAll('option[data-customer]');
    
    allOptions.forEach(option => {
        if (customerId === '' || option.dataset.customer === customerId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
            if (option.selected) {
                projectSelect.value = '';
            }
        }
    });
}

// Initialize on page load if customer is already selected
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customer_id');
    if (customerSelect.value) {
        loadCustomerProjects(customerSelect.value);
    }
});

// Combine date and time before submitting
document.querySelector('form').addEventListener('submit', function(e) {
    const date = document.getElementById('appointment_date').value;
    const time = document.getElementById('appointment_time').value;
    
    if (date && time) {
        const datetime = date + ' ' + time + ':00';
        
        // Create hidden input with combined datetime
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'appointment_date';
        hiddenInput.value = datetime;
        
        // Remove the original date input's name so it doesn't conflict
        document.getElementById('appointment_date').removeAttribute('name');
        document.getElementById('appointment_time').removeAttribute('name');
        
        this.appendChild(hiddenInput);
    }
});
</script>
