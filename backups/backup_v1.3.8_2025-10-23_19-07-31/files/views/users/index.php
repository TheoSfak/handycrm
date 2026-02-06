<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-cog"></i> <?= __('menu.users') ?></h2>
    <a href="?route=/users/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> <?= __('users.new_user') ?>
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
                        <th><?= __('users.name') ?></th>
                        <th><?= __('users.username') ?></th>
                        <th><?= __('users.email') ?></th>
                        <th><?= __('users.phone') ?></th>
                        <th><?= __('users.role') ?></th>
                        <th><?= __('users.status') ?></th>
                        <th><?= __('users.actions') ?></th>
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
                            <?php
                            // Define role colors and labels
                            $roleColors = [
                                'admin' => 'danger',
                                'supervisor' => 'warning',
                                'technician' => 'primary',
                                'assistant' => 'info'
                            ];
                            $roleLabels = [
                                'admin' => __('users.admin'),
                                'supervisor' => __('users.supervisor'),
                                'technician' => __('users.technician'),
                                'assistant' => __('users.assistant')
                            ];
                            $roleColor = $roleColors[$u['role']] ?? 'secondary';
                            $roleLabel = $roleLabels[$u['role']] ?? ucfirst($u['role']);
                            ?>
                            <span class="badge bg-<?= $roleColor ?>">
                                <?= $roleLabel ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $u['is_active'] ? 'success' : 'secondary' ?>"><?= $u['is_active'] ? __('users.active') : __('users.inactive') ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?= BASE_URL ?>/users/show/<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-info" title="<?= __('common.view') ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?route=/users/edit&id=<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="<?= __('common.edit') ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $u['id'] ?>)" title="<?= __('common.delete') ?>">
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

<form id="deleteForm" method="POST" action="index.php?route=/users/delete">
    <input type="hidden" name="id" id="deleteUserId">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<script>
function confirmDelete(userId) {
    if (confirm('<?= __('users.confirm_delete') ?>')) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteForm').submit();
    }
}
</script>
