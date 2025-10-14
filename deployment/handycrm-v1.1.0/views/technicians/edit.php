<?php
/**
 * Edit Technician View
 */

$pageTitle = $title ?? 'Επεξεργασία Τεχνικού';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/technicians">
                    <i class="fas fa-user-hard-hat me-1"></i>Τεχνικοί
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/technicians/view/<?= $technician['id'] ?>">
                    <?= htmlspecialchars($technician['name']) ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Επεξεργασία</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        <?= htmlspecialchars($pageTitle) ?>
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/technicians/edit/<?= $technician['id'] ?>" id="technicianForm">
                        <!-- CSRF Token would go here -->
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="id" value="<?= $technician['id'] ?>">

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Όνομα <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($technician['name']) ?>"
                                   required>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label class="form-label">
                                Ρόλος <span class="text-danger">*</span>
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="role" 
                                               id="role_technician" 
                                               value="technician"
                                               <?= $technician['role'] === 'technician' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="role_technician">
                                            <i class="fas fa-user-tie text-primary me-2"></i>
                                            <strong>Τεχνικός</strong>
                                            <small class="d-block text-muted">Κύριος τεχνικός με πλήρη εμπειρία</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="role" 
                                               id="role_assistant" 
                                               value="assistant"
                                               <?= $technician['role'] === 'assistant' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="role_assistant">
                                            <i class="fas fa-user text-info me-2"></i>
                                            <strong>Βοηθός Τεχνικού</strong>
                                            <small class="d-block text-muted">Βοηθός με περιορισμένη εμπειρία</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hourly Rate -->
                        <div class="mb-3">
                            <label for="hourly_rate" class="form-label">
                                Τιμή Μεροκαμάτου (€/ώρα) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="hourly_rate" 
                                       name="hourly_rate" 
                                       step="0.01" 
                                       min="0"
                                       value="<?= htmlspecialchars($technician['hourly_rate']) ?>"
                                       required>
                                <span class="input-group-text">€/ώρα</span>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Αυτή η τιμή θα χρησιμοποιείται αυτόματα στις εργασίες
                            </small>
                        </div>

                        <hr class="my-4">

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                Τηλέφωνο
                            </label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($technician['phone'] ?? '') ?>"
                                   placeholder="6912345678">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email
                            </label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($technician['email'] ?? '') ?>"
                                   placeholder="example@domain.com">
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                Σημειώσεις
                            </label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"><?= htmlspecialchars($technician['notes'] ?? '') ?></textarea>
                        </div>

                        <!-- Active Status -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       <?= $technician['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    <strong>Ενεργός</strong>
                                    <small class="d-block text-muted">Ο τεχνικός θα εμφανίζεται στις επιλογές εργασιών</small>
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/technicians/view/<?= $technician['id'] ?>" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Ακύρωση
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Ενημέρωση
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('technicianForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const hourlyRate = parseFloat(document.getElementById('hourly_rate').value);
    
    if (!name) {
        e.preventDefault();
        alert('Το όνομα είναι υποχρεωτικό');
        return false;
    }
    
    if (isNaN(hourlyRate) || hourlyRate < 0) {
        e.preventDefault();
        alert('Η τιμή ώρας πρέπει να είναι θετικός αριθμός');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
