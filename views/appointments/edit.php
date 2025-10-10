<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-edit"></i> <?= __('appointments.edit_appointment') ?></h2>
    <div>
        <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>" class="btn btn-info me-2">
            <i class="fas fa-eye"></i> <?= __('common.view') ?>
        </a>
        <a href="?route=/appointments" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?= __('appointments.back_to_list') ?>
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
                        <label for="title" class="form-label"><?= __('appointments.appointment_title') ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($appointment['title']) ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label"><?= __('appointments.status') ?></label>
                        <select class="form-select" id="status" name="status">
                            <option value="scheduled" <?= $appointment['status'] === 'scheduled' ? 'selected' : '' ?>>
                                <?= __('appointments.scheduled') ?>
                            </option>
                            <option value="confirmed" <?= $appointment['status'] === 'confirmed' ? 'selected' : '' ?>>
                                <?= __('appointments.confirmed') ?>
                            </option>
                            <option value="in_progress" <?= $appointment['status'] === 'in_progress' ? 'selected' : '' ?>>
                                <?= __('appointments.in_progress') ?>
                            </option>
                            <option value="completed" <?= $appointment['status'] === 'completed' ? 'selected' : '' ?>>
                                <?= __('appointments.completed') ?>
                            </option>
                            <option value="cancelled" <?= $appointment['status'] === 'cancelled' ? 'selected' : '' ?>>
                                <?= __('appointments.cancelled') ?>
                            </option>
                            <option value="no_show" <?= $appointment['status'] === 'no_show' ? 'selected' : '' ?>>
                                <?= __('appointments.no_show') ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label"><?= __('appointments.description') ?></label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($appointment['description'] ?? '') ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label"><?= __('appointments.customer') ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="customer_id" name="customer_id" required onchange="loadCustomerProjects(this.value)">
                            <option value=""><?= __('appointments.select_customer') ?></option>
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
                        <label for="technician_id" class="form-label"><?= __('appointments.technician') ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="technician_id" name="technician_id" required>
                            <option value=""><?= __('appointments.select_technician') ?></option>
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
                        <label for="project_id" class="form-label"><?= __('appointments.project_optional') ?></label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value=""><?= __('appointments.no_project') ?></option>
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
                        <label for="address" class="form-label"><?= __('appointments.location') ?></label>
                        <input type="text" class="form-control" id="address" name="address" 
                               placeholder="<?= __('appointments.location_placeholder') ?>"
                               value="<?= htmlspecialchars($appointment['address'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="appointment_date" class="form-label"><?= __('appointments.date') ?> <span class="text-danger">*</span></label>
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
                        <label for="appointment_time" class="form-label"><?= __('appointments.time') ?> <span class="text-danger">*</span></label>
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
                        <label for="duration_minutes" class="form-label"><?= __('appointments.duration_minutes_label') ?></label>
                        <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" 
                               value="<?= htmlspecialchars($appointment['duration_minutes'] ?? 60) ?>" 
                               min="15" step="15">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label"><?= __('appointments.notes') ?></label>
                <textarea class="form-control" id="notes" name="notes" rows="3" 
                          placeholder="<?= __('appointments.notes_placeholder') ?>"><?= htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?= __('appointments.cancel') ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= __('appointments.save_changes') ?>
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
