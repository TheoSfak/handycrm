<?php
/**
 * User Profile View
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-circle"></i> Προφίλ Χρήστη</h2>
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

            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Στοιχεία Προφίλ</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/profile/update">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Όνομα *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Επώνυμο *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Τηλέφωνο</label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                <small class="text-muted">Το username δεν μπορεί να αλλάξει</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ρόλος</label>
                                <input type="text" class="form-control" value="<?= $user['role'] === 'admin' ? 'Διαχειριστής' : 'Χρήστης' ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Αποθήκευση Αλλαγών
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key"></i> Αλλαγή Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/profile/change-password">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Τρέχον Password *</label>
                            <input type="password" class="form-control" id="current_password" 
                                   name="current_password" required autocomplete="current-password">
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Νέο Password *</label>
                            <input type="password" class="form-control" id="new_password" 
                                   name="new_password" required minlength="6" autocomplete="new-password">
                            <small class="text-muted">Τουλάχιστον 6 χαρακτήρες</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Επιβεβαίωση Password *</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required minlength="6" autocomplete="new-password">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-lock"></i> Αλλαγή Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Check password match
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = this.value;
    
    if (confirmPass && newPass !== confirmPass) {
        this.setCustomValidity('Τα passwords δεν ταιριάζουν');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('new_password').addEventListener('input', function() {
    const confirmPass = document.getElementById('confirm_password');
    if (confirmPass.value) {
        confirmPass.dispatchEvent(new Event('input'));
    }
});
</script>
