<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-cog"></i> Χρήστες</h2>
    <a href="?route=/users/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Νέος Χρήστης
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Όνομα</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Τηλέφωνο</th>
                        <th>Ρόλος</th>
                        <th>Κατάσταση</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?: '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                <?= $u['role'] === 'admin' ? 'Admin' : 'Τεχνικός' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $u['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $u['is_active'] ? 'Ενεργός' : 'Ανενεργός' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="?route=/users/edit&id=<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Επεξεργασία">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $u['id'] ?>)" title="Διαγραφή">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" action="?route=/users/delete">
    <input type="hidden" name="id" id="deleteUserId">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<script>
function confirmDelete(userId) {
    if (confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτόν τον χρήστη;')) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteForm').submit();
    }
}
</script>
