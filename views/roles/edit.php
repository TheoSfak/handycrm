<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-tag"></i> Επεξεργασία Ρόλου</h2>
                <a href="<?= BASE_URL ?>/roles" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Επιστροφή
                </a>
            </div>

            <!-- Edit Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/roles/edit/<?= $role['id'] ?>">
                        <!-- Basic Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Όνομα Συστήματος</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           value="<?= htmlspecialchars($role['name']) ?>"
                                           disabled>
                                    <small class="form-text text-muted">Το όνομα συστήματος δεν μπορεί να αλλάξει</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Ετικέτα <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="display_name" 
                                           name="display_name" 
                                           value="<?= htmlspecialchars($role['display_name']) ?>"
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Περιγραφή</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?= htmlspecialchars($role['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Δικαιώματα</h5>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                        <i class="fas fa-check-double"></i> Επιλογή Όλων
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                        <i class="fas fa-times"></i> Αποεπιλογή Όλων
                                    </button>
                                </div>
                            </div>
                            
                            <?php if (!empty($permissions)): ?>
                                <?php 
                                // Create array of role permission IDs for easy checking
                                $rolePermissionIds = array_column($rolePermissions, 'id');
                                ?>
                                
                                <?php foreach ($permissions as $module => $modulePermissions): ?>
                                    <?php
                                    // Count how many permissions in this module are selected
                                    $modulePermissionIds = array_column($modulePermissions, 'id');
                                    $selectedInModule = count(array_intersect($modulePermissionIds, $rolePermissionIds));
                                    $allSelected = $selectedInModule === count($modulePermissions);
                                    ?>
                                    
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <div class="form-check">
                                                <input class="form-check-input module-checkbox" 
                                                       type="checkbox" 
                                                       id="module_<?= $module ?>"
                                                       <?= $allSelected ? 'checked' : '' ?>
                                                       onchange="toggleModule('<?= $module ?>')">
                                                <label class="form-check-label fw-bold" for="module_<?= $module ?>">
                                                    <?= htmlspecialchars(ucfirst($module)) ?>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <?php foreach ($modulePermissions as $permission): ?>
                                                    <?php $isChecked = in_array($permission['id'], $rolePermissionIds); ?>
                                                    <div class="col-md-4 col-lg-3 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-checkbox module-<?= $module ?>" 
                                                                   type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="<?= $permission['id'] ?>"
                                                                   id="permission_<?= $permission['id'] ?>"
                                                                   <?= $isChecked ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="permission_<?= $permission['id'] ?>">
                                                                <?= htmlspecialchars($permission['display_name']) ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Δεν υπάρχουν διαθέσιμα δικαιώματα.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/roles" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Ακύρωση
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Ενημέρωση
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Select all permissions
function selectAll() {
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.querySelectorAll('.module-checkbox').forEach(checkbox => {
        checkbox.checked = true;
        checkbox.indeterminate = false;
    });
}

// Deselect all permissions
function deselectAll() {
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.querySelectorAll('.module-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.indeterminate = false;
    });
}

// Toggle all permissions in a module
function toggleModule(module) {
    const moduleCheckbox = document.getElementById('module_' + module);
    const permissionCheckboxes = document.querySelectorAll('.module-' + module);
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = moduleCheckbox.checked;
    });
    moduleCheckbox.indeterminate = false;
}

// Update module checkbox based on permissions
document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const module = this.className.match(/module-(\w+)/)[1];
        const moduleCheckbox = document.getElementById('module_' + module);
        const permissionCheckboxes = document.querySelectorAll('.module-' + module);
        const checkedCount = document.querySelectorAll('.module-' + module + ':checked').length;
        
        moduleCheckbox.checked = checkedCount === permissionCheckboxes.length;
        moduleCheckbox.indeterminate = checkedCount > 0 && checkedCount < permissionCheckboxes.length;
    });
});

// Initialize indeterminate state on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.module-checkbox').forEach(moduleCheckbox => {
        const module = moduleCheckbox.id.replace('module_', '');
        const permissionCheckboxes = document.querySelectorAll('.module-' + module);
        const checkedCount = document.querySelectorAll('.module-' + module + ':checked').length;
        
        if (checkedCount > 0 && checkedCount < permissionCheckboxes.length) {
            moduleCheckbox.indeterminate = true;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
