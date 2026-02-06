<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-key"></i> Δικαιώματα Ρόλου</h2>
                    <p class="text-muted mb-0">Ρόλος: <strong><?= htmlspecialchars($role['display_name']) ?></strong></p>
                </div>
                <a href="<?= BASE_URL ?>/roles" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Επιστροφή
                </a>
            </div>

            <!-- Permissions Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/roles/permissions/<?= $role['id'] ?>">
                        <?php if (!empty($permissions)): ?>
                            <?php 
                            // Create array of role permission IDs for easy checking
                            $rolePermissionIds = array_column($rolePermissions, 'id');
                            ?>
                            
                            <!-- Quick Actions -->
                            <div class="mb-4 p-3 bg-light rounded">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="selectAll()">
                                        <i class="fas fa-check-double"></i> Επιλογή Όλων
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAll()">
                                        <i class="fas fa-times"></i> Αποεπιλογή Όλων
                                    </button>
                                </div>
                            </div>

                            <!-- Permissions by Module -->
                            <?php foreach ($permissions as $module => $modulePermissions): ?>
                                <?php
                                // Count how many permissions in this module are selected
                                $modulePermissionIds = array_column($modulePermissions, 'id');
                                $selectedInModule = count(array_intersect($modulePermissionIds, $rolePermissionIds));
                                $allSelected = $selectedInModule === count($modulePermissions);
                                ?>
                                
                                <div class="card mb-3 border-primary">
                                    <div class="card-header bg-primary bg-opacity-10">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input module-checkbox" 
                                                       type="checkbox" 
                                                       id="module_<?= $module ?>"
                                                       <?= $allSelected ? 'checked' : '' ?>
                                                       onchange="toggleModule('<?= $module ?>')">
                                                <label class="form-check-label fw-bold fs-5" for="module_<?= $module ?>">
                                                    <i class="fas fa-folder"></i> <?= htmlspecialchars(ucfirst($module)) ?>
                                                </label>
                                            </div>
                                            <span class="badge bg-primary">
                                                <span id="count_<?= $module ?>"><?= $selectedInModule ?></span> / <?= count($modulePermissions) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <?php foreach ($modulePermissions as $permission): ?>
                                                <?php $isChecked = in_array($permission['id'], $rolePermissionIds); ?>
                                                <div class="col-md-6 col-lg-4 col-xl-3">
                                                    <div class="form-check p-3 border rounded h-100 <?= $isChecked ? 'bg-success bg-opacity-10 border-success' : '' ?>">
                                                        <input class="form-check-input permission-checkbox module-<?= $module ?>" 
                                                               type="checkbox" 
                                                               name="permissions[]" 
                                                               value="<?= $permission['id'] ?>"
                                                               id="permission_<?= $permission['id'] ?>"
                                                               <?= $isChecked ? 'checked' : '' ?>>
                                                        <label class="form-check-label w-100" for="permission_<?= $permission['id'] ?>">
                                                            <strong><?= htmlspecialchars($permission['display_name']) ?></strong>
                                                            <?php if (!empty($permission['description'])): ?>
                                                                <br>
                                                                <small class="text-muted"><?= htmlspecialchars($permission['description']) ?></small>
                                                            <?php endif; ?>
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
                                <i class="fas fa-exclamation-triangle"></i> Δεν υπάρχουν διαθέσιμα δικαιώματα στο σύστημα.
                            </div>
                        <?php endif; ?>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?= BASE_URL ?>/roles" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Ακύρωση
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Αποθήκευση Δικαιωμάτων
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
        updateCheckboxStyle(checkbox);
    });
    document.querySelectorAll('.module-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateAllCounts();
}

// Deselect all permissions
function deselectAll() {
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        updateCheckboxStyle(checkbox);
    });
    document.querySelectorAll('.module-checkbox').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.indeterminate = false;
    });
    updateAllCounts();
}

// Toggle all permissions in a module
function toggleModule(module) {
    const moduleCheckbox = document.getElementById('module_' + module);
    const permissionCheckboxes = document.querySelectorAll('.module-' + module);
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = moduleCheckbox.checked;
        updateCheckboxStyle(checkbox);
    });
    
    updateModuleCount(module);
}

// Update visual style of checkbox container
function updateCheckboxStyle(checkbox) {
    const container = checkbox.closest('.form-check');
    if (checkbox.checked) {
        container.classList.add('bg-success', 'bg-opacity-10', 'border-success');
    } else {
        container.classList.remove('bg-success', 'bg-opacity-10', 'border-success');
    }
}

// Update count for a specific module
function updateModuleCount(module) {
    const checkedCount = document.querySelectorAll('.module-' + module + ':checked').length;
    const countElement = document.getElementById('count_' + module);
    if (countElement) {
        countElement.textContent = checkedCount;
    }
}

// Update all module counts
function updateAllCounts() {
    document.querySelectorAll('.module-checkbox').forEach(checkbox => {
        const module = checkbox.id.replace('module_', '');
        updateModuleCount(module);
    });
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
        
        updateCheckboxStyle(this);
        updateModuleCount(module);
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
