<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-plus"></i> <?= __('users.new_user') ?></h2>
    <a href="?route=/users" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> <?= __('users.back') ?>
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="?route=/users/create">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label"><?= __('users.first_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label"><?= __('users.last_name') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label"><?= __('users.username') ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label"><?= __('users.email') ?> <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label"><?= __('users.password') ?> <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label"><?= __('users.phone') ?></label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label"><?= __('users.role') ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin">Διαχειριστής</option>
                            <option value="supervisor">Υπεύθυνος Συνεργείου</option>
                            <option value="technician" selected>Τεχνικός</option>
                            <option value="assistant">Βοηθός Τεχνικού</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="hourly_rate" class="form-label">Ωριαία Χρέωση (€)</label>
                        <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" 
                               min="0" step="0.01" value="0.00" 
                               placeholder="π.χ. 20.00">
                        <div class="form-text">Προαιρετικό - για τεχνικούς και βοηθούς που χρεώνονται ανά ώρα</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= __('users.save') ?>
                        </button>
                        <a href="?route=/users" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <?= __('users.cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
