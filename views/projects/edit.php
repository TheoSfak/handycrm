<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-project-diagram"></i> <?= __('projects.edit_project') ?></h2>
    <div>
        <a href="?route=/projects" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> <?= __('projects.back_to_list') ?>
        </a>
        <a href="?route=/projects/show&id=<?= $project['id'] ?>" class="btn btn-info">
            <i class="fas fa-eye"></i> <?= __('projects.view') ?>
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
        <form method="POST" action="?route=/projects/edit&id=<?= $project['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label"><?= __('projects.project_title') ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($project['title']) ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="priority" class="form-label"><?= __('projects.priority') ?></label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="low" <?= ($project['priority'] === 'low') ? 'selected' : '' ?>>
                                <?= __('projects.low') ?>
                            </option>
                            <option value="medium" <?= ($project['priority'] === 'medium') ? 'selected' : '' ?>>
                                <?= __('projects.normal') ?>
                            </option>
                            <option value="high" <?= ($project['priority'] === 'high') ? 'selected' : '' ?>>
                                <?= __('projects.high') ?>
                            </option>
                            <option value="urgent" <?= ($project['priority'] === 'urgent') ? 'selected' : '' ?>>
                                <?= __('projects.urgent') ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label"><?= __('projects.description') ?></label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
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
                                ?>
                                <option value="<?= $customer['id'] ?>" 
                                        <?= ($project['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customerName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category" class="form-label"><?= __('projects.category') ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="electrical" <?= ($project['category'] === 'electrical') ? 'selected' : '' ?>>
                                <?= __('projects.electrical') ?>
                            </option>
                            <option value="plumbing" <?= ($project['category'] === 'plumbing') ? 'selected' : '' ?>>
                                <?= __('projects.plumbing') ?>
                            </option>
                            <option value="maintenance" <?= ($project['category'] === 'maintenance') ? 'selected' : '' ?>>
                                <?= __('projects.maintenance') ?>
                            </option>
                            <option value="emergency" <?= ($project['category'] === 'emergency') ? 'selected' : '' ?>>
                                <?= __('projects.emergency') ?>
                            </option>
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
                                        <?= ($project['assigned_technician'] == $tech['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label"><?= __('projects.status') ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="new" <?= ($project['status'] === 'new') ? 'selected' : '' ?>>
                                <?= __('projects.new') ?>
                            </option>
                            <option value="in_progress" <?= ($project['status'] === 'in_progress') ? 'selected' : '' ?>>
                                <?= __('projects.in_progress') ?>
                            </option>
                            <option value="completed" <?= ($project['status'] === 'completed') ? 'selected' : '' ?>>
                                <?= __('projects.completed') ?>
                            </option>
                            <option value="invoiced" <?= ($project['status'] === 'invoiced') ? 'selected' : '' ?>>
                                <?= __('projects.invoiced') ?>
                            </option>
                            <option value="cancelled" <?= ($project['status'] === 'cancelled') ? 'selected' : '' ?>>
                                <?= __('projects.cancelled') ?>
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
                               value="<?= htmlspecialchars($project['start_date'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="completion_date" class="form-label"><?= __('projects.completion_date') ?></label>
                        <input type="date" class="form-control" id="completion_date" name="completion_date" 
                               value="<?= htmlspecialchars($project['completion_date'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="estimated_hours" class="form-label"><?= __('projects.estimated_hours') ?></label>
                        <input type="number" step="0.5" class="form-control" id="estimated_hours" name="estimated_hours" 
                               value="<?= htmlspecialchars($project['estimated_hours'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <!-- Cost fields removed - costs calculated from tasks -->
            
            <div class="mb-3">
                <label for="notes" class="form-label"><?= __('projects.notes') ?></label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($project['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="?route=/projects" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?= __('projects.cancel') ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= __('projects.save_changes') ?>
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
        input.setAttribute('placeholder', 'ΗΗ/ΜΜ/ΕΕΕΕ');
        
        // Show format hint on focus
        input.addEventListener('focus', function() {
            if (!this.value) {
                this.setAttribute('data-placeholder', this.placeholder);
            }
        });
    });
});
</script>

<?php
// Clear old input after displaying
if (isset($_SESSION['old_input'])) {
    unset($_SESSION['old_input']);
}
?>
