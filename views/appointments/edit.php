<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-edit"></i> Επεξεργασία Ραντεβού</h2>
    <div>
        <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>" class="btn btn-info me-2">
            <i class="fas fa-eye"></i> Προβολή
        </a>
        <a href="?route=/appointments" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Πίσω στη Λίστα
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="?route=/appointments/edit&id=<?= $appointment['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Τίτλος Ραντεβού <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($appointment['title']) ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Κατάσταση</label>
                        <select class="form-select" id="status" name="status">
                            <option value="scheduled" <?= $appointment['status'] === 'scheduled' ? 'selected' : '' ?>>
                                Προγραμματισμένο
                            </option>
                            <option value="confirmed" <?= $appointment['status'] === 'confirmed' ? 'selected' : '' ?>>
                                Επιβεβαιωμένο
                            </option>
                            <option value="in_progress" <?= $appointment['status'] === 'in_progress' ? 'selected' : '' ?>>
                                Σε Εξέλιξη
                            </option>
                            <option value="completed" <?= $appointment['status'] === 'completed' ? 'selected' : '' ?>>
                                Ολοκληρωμένο
                            </option>
                            <option value="cancelled" <?= $appointment['status'] === 'cancelled' ? 'selected' : '' ?>>
                                Ακυρωμένο
                            </option>
                            <option value="no_show" <?= $appointment['status'] === 'no_show' ? 'selected' : '' ?>>
                                Δεν Εμφανίστηκε
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Περιγραφή</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($appointment['description'] ?? '') ?></textarea>
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
                                ?>
                                <option value="<?= $customer['id'] ?>" 
                                        <?= $appointment['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customerName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="technician_id" class="form-label">Τεχνικός <span class="text-danger">*</span></label>
                        <select class="form-select" id="technician_id" name="technician_id" required>
                            <option value="">Επιλέξτε τεχνικό...</option>
                            <?php foreach ($technicians as $tech): ?>
                            <option value="<?= $tech['id'] ?>" 
                                    <?= $appointment['technician_id'] == $tech['id'] ? 'selected' : '' ?>>
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
                                    <?= ($appointment['project_id'] ?? '') == $project['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($project['title']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="address" class="form-label">Τοποθεσία</label>
                        <input type="text" class="form-control" id="address" name="address" 
                               placeholder="Διεύθυνση ή τοποθεσία"
                               value="<?= htmlspecialchars($appointment['address'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="appointment_date" class="form-label">Ημερομηνία <span class="text-danger">*</span></label>
                        <?php
                        // Extract date from appointment_date (which is datetime)
                        $dateOnly = date('Y-m-d', strtotime($appointment['appointment_date']));
                        ?>
                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                               value="<?= $dateOnly ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="appointment_time" class="form-label">Ώρα <span class="text-danger">*</span></label>
                        <?php
                        // Extract time from appointment_date (which is datetime)
                        $timeOnly = date('H:i', strtotime($appointment['appointment_date']));
                        ?>
                        <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                               value="<?= $timeOnly ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="duration_minutes" class="form-label">Διάρκεια (λεπτά)</label>
                        <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                               value="<?= htmlspecialchars($appointment['duration_minutes'] ?? 60) ?>" 
                               min="15" step="15">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Σημειώσεις</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" 
                          placeholder="Επιπλέον σημειώσεις ή οδηγίες..."><?= htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Ακύρωση
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Αποθήκευση Αλλαγών
                </button>
            </div>
        </form>
    </div>
</div>

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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customer_id');
    if (customerSelect.value) {
        loadCustomerProjects(customerSelect.value);
    }
});
</script>
