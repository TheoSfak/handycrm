<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-tag"></i> Διαχείριση Ρόλων</h2>
                <a href="<?= BASE_URL ?>/roles/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Νέος Ρόλος
                </a>
            </div>

            <!-- Roles Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($roles)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Όνομα Συστήματος</th>
                                        <th>Ετικέτα</th>
                                        <th>Περιγραφή</th>
                                        <th>Αριθμός Χρηστών</th>
                                        <th class="text-end">Ενέργειες</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roles as $role): ?>
                                        <?php
                                        // Determine if system role (cannot be deleted)
                                        $isSystemRole = (bool)($role['is_system'] ?? false);
                                        ?>
                                        <tr>
                                            <td>
                                                <code><?= htmlspecialchars($role['name']) ?></code>
                                                <?php if ($isSystemRole): ?>
                                                    <span class="badge bg-secondary ms-2">Σύστημα</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($role['display_name']) ?></td>
                                            <td><?= htmlspecialchars($role['description'] ?? '') ?></td>
                                            <td>
                                                <?php
                                                // Get users count for this role
                                                $roleModel = new Role();
                                                $usersCount = $roleModel->getUsersCount($role['id']);
                                                ?>
                                                <span class="badge bg-info"><?= $usersCount ?></span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="<?= BASE_URL ?>/roles/permissions/<?= $role['id'] ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       title="Δικαιώματα">
                                                        <i class="fas fa-key"></i>
                                                    </a>
                                                    
                                                    <?php if (!$isSystemRole): ?>
                                                        <a href="<?= BASE_URL ?>/roles/edit/<?= $role['id'] ?>" 
                                                           class="btn btn-sm btn-warning" 
                                                           title="Επεξεργασία">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <?php if ($usersCount == 0): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-danger" 
                                                                    title="Διαγραφή"
                                                                    onclick="confirmDelete(<?= $role['id'] ?>, '<?= htmlspecialchars($role['display_name']) ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-secondary" 
                                                                    disabled
                                                                    title="Δεν μπορεί να διαγραφεί (χρησιμοποιείται)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-secondary" 
                                                                disabled
                                                                title="Οι προεπιλεγμένοι ρόλοι δεν μπορούν να επεξεργαστούν">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Δεν υπάρχουν ρόλοι στο σύστημα.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
function confirmDelete(roleId, roleName) {
    if (confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε τον ρόλο "' + roleName + '";')) {
        const form = document.getElementById('deleteForm');
        form.action = '<?= BASE_URL ?>/roles/delete/' + roleId;
        form.submit();
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
