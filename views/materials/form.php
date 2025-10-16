<?php
/**
 * Material Add/Edit Form
 * Form for creating or updating materials in the catalog
 */

$pageTitle = $pageTitle ?? ($material ? 'Επεξεργασία Υλικού' : 'Προσθήκη Υλικού');
$isEdit = !empty($material);
require_once __DIR__ . '/../includes/header.php';

// Get form data from session or material
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

if ($isEdit && empty($formData)) {
    $formData = $material;
}
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/materials">Υλικά</a></li>
            <li class="breadcrumb-item active"><?= $isEdit ? 'Επεξεργασία' : 'Νέο Υλικό' ?></li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?> me-2"></i>
            <?= $isEdit ? 'Επεξεργασία Υλικού' : 'Νέο Υλικό' ?>
        </h2>
        <a href="<?= BASE_URL ?>/materials" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Επιστροφή
        </a>
    </div>

    <!-- Errors -->
    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Σφάλματα</h6>
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="<?= BASE_URL ?>/materials/<?= $isEdit ? $material['id'] . '/edit' : 'add' ?>" class="needs-validation" novalidate>
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Βασικές Πληροφορίες</h5>
                    </div>
                    <div class="card-body">
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Όνομα Υλικού <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                                   required
                                   placeholder="π.χ. Σωλήνας PVC Φ32">
                            <div class="invalid-feedback">
                                Το όνομα είναι υποχρεωτικό
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Περιγραφή</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Προαιρετική περιγραφή του υλικού..."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <!-- Category -->
                            <div class="col-md-4 mb-3">
                                <label for="category_id" class="form-label">Κατηγορία</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Χωρίς Κατηγορία</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                                <?= ($formData['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">
                                    <a href="<?= BASE_URL ?>/materials/categories" target="_blank">Διαχείριση κατηγοριών</a>
                                </small>
                            </div>

                            <!-- Unit -->
                            <div class="col-md-4 mb-3">
                                <label for="unit" class="form-label">Μονάδα Μέτρησης</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="unit" 
                                       name="unit" 
                                       value="<?= htmlspecialchars($formData['unit'] ?? '') ?>"
                                       placeholder="π.χ. τεμάχια, μέτρα, κιλά"
                                       list="unitsList">
                                <datalist id="unitsList">
                                    <option value="τεμάχια">
                                    <option value="μέτρα">
                                    <option value="τ.μ.">
                                    <option value="κ.μ.">
                                    <option value="κιλά">
                                    <option value="λίτρα">
                                    <option value="τόνοι">
                                    <option value="κουτιά">
                                    <option value="συσκευασίες">
                                </datalist>
                            </div>

                            <!-- Price -->
                            <div class="col-md-4 mb-3">
                                <label for="default_price" class="form-label">Προεπιλεγμένη Τιμή (€)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="default_price" 
                                       name="default_price" 
                                       step="0.01" 
                                       min="0"
                                       value="<?= htmlspecialchars($formData['default_price'] ?? '') ?>"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <!-- Supplier -->
                        <div class="mb-3">
                            <label for="supplier" class="form-label">Προμηθευτής</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="supplier" 
                                   name="supplier" 
                                   value="<?= htmlspecialchars($formData['supplier'] ?? '') ?>"
                                   placeholder="π.χ. ACME Υδραυλικά">
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Σημειώσεις</label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="2"
                                      placeholder="Επιπλέον σημειώσεις..."><?= htmlspecialchars($formData['notes'] ?? '') ?></textarea>
                        </div>
                        
                        <!-- Aliases (Search Keywords) -->
                        <div class="mb-3">
                            <label for="aliases" class="form-label">
                                Λέξεις Κλειδιά Αναζήτησης
                                <i class="fas fa-magic text-primary ms-1" title="Auto-generated"></i>
                            </label>
                            <textarea class="form-control" 
                                      id="aliases" 
                                      name="aliases" 
                                      rows="3"
                                      placeholder="Αυτόματα: kalodio, cable, wire, NYM, 3x1.5..."><?= htmlspecialchars($formData['aliases'] ?? '') ?></textarea>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Δημιουργούνται αυτόματα από το όνομα (Greeklish, συνώνυμα, κωδικοί). 
                                Μπορείς να προσθέσεις επιπλέον, χωρισμένα με κόμμα.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Κατάσταση</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   <?= empty($formData) || ($formData['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                <strong>Ενεργό Υλικό</strong>
                            </label>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Τα ανενεργά υλικά δεν εμφανίζονται στο autocomplete των εργασιών
                        </small>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card shadow-sm border-primary">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Οδηγίες</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li class="mb-2">Το <strong>όνομα</strong> είναι υποχρεωτικό και θα εμφανίζεται στο autocomplete</li>
                            <li class="mb-2">Η <strong>κατηγορία</strong> βοηθά στην οργάνωση των υλικών</li>
                            <li class="mb-2">Η <strong>μονάδα</strong> (π.χ. τεμάχια, μέτρα) συμπληρώνεται αυτόματα στις εργασίες</li>
                            <li class="mb-2">Η <strong>προεπιλεγμένη τιμή</strong> χρησιμοποιείται ως αρχική τιμή</li>
                            <li>Τα υλικά που χρησιμοποιούνται σε εργασίες δεν διαγράφονται, απενεργοποιούνται</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/materials" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Ακύρωση
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        <?= $isEdit ? 'Ενημέρωση' : 'Αποθήκευση' ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Bootstrap form validation
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Auto-preview aliases as user types the name
const nameInput = document.getElementById('name');
const aliasesTextarea = document.getElementById('aliases');

if (nameInput && aliasesTextarea) {
    // Show preview hint when typing name
    nameInput.addEventListener('input', function() {
        if (!aliasesTextarea.value.trim()) {
            const name = this.value.trim();
            if (name.length > 2) {
                aliasesTextarea.placeholder = `Θα δημιουργηθούν αυτόματα από: "${name}"`;
            } else {
                aliasesTextarea.placeholder = 'Αυτόματα: kalodio, cable, wire, NYM...';
            }
        }
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
