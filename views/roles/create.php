<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-tag"></i> Νέος Ρόλος</h2>
                <a href="<?= BASE_URL ?>/roles" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Επιστροφή
                </a>
            </div>

            <!-- Create Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/roles/create">
                        <!-- Basic Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Όνομα Συστήματος <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           required
                                           pattern="[a-z_]+"
                                           placeholder="π.χ. project_manager">
                                    <small class="form-text text-muted">Μόνο πεζά αγγλικά γράμματα και underscore (_)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Ετικέτα <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="display_name" 
                                           name="display_name" 
                                           required
                                           placeholder="π.χ. Υπεύθυνος Έργων">
                                    <small class="form-text text-muted">Η ετικέτα που θα εμφανίζεται</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Περιγραφή</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Σύντομη περιγραφή του ρόλου"></textarea>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4">
                            <h5 class="mb-3">Δικαιώματα</h5>
                            
                            <?php if (!empty($permissions)): ?>
                                <?php foreach ($permissions as $module => $modulePermissions): ?>
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <div class="form-check">
                                                <input class="form-check-input module-checkbox" 
                                                       type="checkbox" 
                                                       id="module_<?= $module ?>"
                                                       onchange="toggleModule('<?= $module ?>')">
                                                <label class="form-check-label fw-bold" for="module_<?= $module ?>">
                                                    <?= htmlspecialchars(ucfirst($module)) ?>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <?php foreach ($modulePermissions as $permission): ?>
                                                    <div class="col-md-4 col-lg-3 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-checkbox module-<?= $module ?>" 
                                                                   type="checkbox" 
                                                                   name="permissions[]" 
                                                                   value="<?= $permission['id'] ?>"
                                                                   id="permission_<?= $permission['id'] ?>">
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
                                <i class="fas fa-save"></i> Αποθήκευση
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle all permissions in a module
function toggleModule(module) {
    const moduleCheckbox = document.getElementById('module_' + module);
    const permissionCheckboxes = document.querySelectorAll('.module-' + module);
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = moduleCheckbox.checked;
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
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
