<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-project-diagram"></i> <?= __('projects.new_project') ?></h2>
    <a href="?route=/projects" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> <?= __('projects.back_to_list') ?>
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="?route=/projects/create">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label"><?= __('projects.project_title') ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($_SESSION['old_input']['title'] ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="priority" class="form-label"><?= __('projects.priority') ?></label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="low" <?= (($_SESSION['old_input']['priority'] ?? '') === 'low') ? 'selected' : '' ?>>
                                <?= __('projects.low') ?>
                            </option>
                            <option value="medium" <?= (($_SESSION['old_input']['priority'] ?? 'medium') === 'medium') ? 'selected' : '' ?>>
                                <?= __('projects.normal') ?>
                            </option>
                            <option value="high" <?= (($_SESSION['old_input']['priority'] ?? '') === 'high') ? 'selected' : '' ?>>
                                <?= __('projects.high') ?>
                            </option>
                            <option value="urgent" <?= (($_SESSION['old_input']['priority'] ?? '') === 'urgent') ? 'selected' : '' ?>>
                                <?= __('projects.urgent') ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label"><?= __('projects.description') ?></label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($_SESSION['old_input']['description'] ?? '') ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label"><?= __('projects.customer') ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value=""><?= __('projects.select_customer') ?></option>
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
                                <i class="fas fa-plus"></i> <?= __('projects.add_new_customer') ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category" class="form-label"><?= __('projects.category') ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value=""><?= __('projects.select_category') ?></option>
                            <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= $key ?>" <?= (($_SESSION['old_input']['category'] ?? '') === $key) ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="assigned_technician" class="form-label"><?= __('projects.assigned_technician') ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="assigned_technician" name="assigned_technician" required>
                            <option value=""><?= __('projects.select_technician') ?></option>
                            <?php foreach ($technicians as $tech): ?>
                            <option value="<?= $tech['id'] ?>" 
                                    <?= (($_SESSION['old_input']['assigned_technician'] ?? '') == $tech['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label"><?= __('projects.status') ?></label>
                        <select class="form-select" id="status" name="status">
                            <option value="pending" <?= (($_SESSION['old_input']['status'] ?? 'pending') === 'pending') ? 'selected' : '' ?>>
                                <?= __('projects.not_started') ?>
                            </option>
                            <option value="in_progress" <?= (($_SESSION['old_input']['status'] ?? '') === 'in_progress') ? 'selected' : '' ?>>
                                <?= __('projects.in_progress') ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="start_date" class="form-label"><?= __('projects.start_date_label') ?></label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?= htmlspecialchars($_SESSION['old_input']['start_date'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="completion_date" class="form-label"><?= __('projects.completion_date') ?></label>
                        <input type="date" class="form-control" id="completion_date" name="completion_date" 
                               value="<?= htmlspecialchars($_SESSION['old_input']['completion_date'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="estimated_hours" class="form-label"><?= __('projects.estimated_hours') ?></label>
                        <input type="number" step="0.5" class="form-control" id="estimated_hours" name="estimated_hours" 
                               value="<?= htmlspecialchars($_SESSION['old_input']['estimated_hours'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <!-- Cost fields removed - costs calculated from tasks -->
            
            <div class="d-flex justify-content-between">
                <a href="?route=/projects" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?= __('projects.cancel') ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= __('projects.save_project') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Force Greek date format in date inputs
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        // Set language attribute for Greek locale
        input.setAttribute('lang', 'el-GR');
        
        // Add placeholder for clarity
        input.setAttribute('placeholder', '<?= __('common.date_format_placeholder') ?>');
        
        // Show format hint on focus
        input.addEventListener('focus', function() {
            if (!this.value) {
                this.setAttribute('data-placeholder', this.placeholder);
            }
        });
    });
});
</script>

<?php unset($_SESSION['old_input']); ?>
