<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-project-diagram"></i> Επεξεργασία Έργου</h2>
    <div>
        <a href="?route=/projects" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Πίσω στη Λίστα
        </a>
        <a href="?route=/projects/show&id=<?= $project['id'] ?>" class="btn btn-info">
            <i class="fas fa-eye"></i> Προβολή
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="?route=/projects/edit&id=<?= $project['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Τίτλος Έργου <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($project['title']) ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="priority" class="form-label">Προτεραιότητα</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="low" <?= ($project['priority'] === 'low') ? 'selected' : '' ?>>
                                Χαμηλή
                            </option>
                            <option value="medium" <?= ($project['priority'] === 'medium') ? 'selected' : '' ?>>
                                Κανονική
                            </option>
                            <option value="high" <?= ($project['priority'] === 'high') ? 'selected' : '' ?>>
                                Υψηλή
                            </option>
                            <option value="urgent" <?= ($project['priority'] === 'urgent') ? 'selected' : '' ?>>
                                Επείγουσα
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Περιγραφή</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Πελάτης <span class="text-danger">*</span></label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Επιλέξτε πελάτη...</option>
                            <?php foreach ($customers as $customer): ?>
                                <?php
                                    $customerName = $customer['customer_type'] === 'company' && !empty($customer['company_name']) 
                                        ? $customer['company_name'] 
                                        : $customer['first_name'] . ' ' . $customer['last_name'];
                                ?>
                                <option value="<?= $customer['id'] ?>" 
                                        <?= ($project['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customerName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category" class="form-label">Κατηγορία <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="electrical" <?= ($project['category'] === 'electrical') ? 'selected' : '' ?>>
                                Ηλεκτρολογικά
                            </option>
                            <option value="plumbing" <?= ($project['category'] === 'plumbing') ? 'selected' : '' ?>>
                                Υδραυλικά
                            </option>
                            <option value="maintenance" <?= ($project['category'] === 'maintenance') ? 'selected' : '' ?>>
                                Συντήρηση
                            </option>
                            <option value="emergency" <?= ($project['category'] === 'emergency') ? 'selected' : '' ?>>
                                Έκτακτη Ανάγκη
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="assigned_technician" class="form-label">Τεχνικός <span class="text-danger">*</span></label>
                        <select class="form-select" id="assigned_technician" name="assigned_technician" required>
                            <option value="">Επιλέξτε τεχνικό...</option>
                            <?php foreach ($technicians as $tech): ?>
                                <option value="<?= $tech['id'] ?>" 
                                        <?= ($project['assigned_technician'] == $tech['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Κατάσταση <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="new" <?= ($project['status'] === 'new') ? 'selected' : '' ?>>
                                Νέο
                            </option>
                            <option value="in_progress" <?= ($project['status'] === 'in_progress') ? 'selected' : '' ?>>
                                Σε Εξέλιξη
                            </option>
                            <option value="completed" <?= ($project['status'] === 'completed') ? 'selected' : '' ?>>
                                Ολοκληρωμένο
                            </option>
                            <option value="invoiced" <?= ($project['status'] === 'invoiced') ? 'selected' : '' ?>>
                                Τιμολογημένο
                            </option>
                            <option value="cancelled" <?= ($project['status'] === 'cancelled') ? 'selected' : '' ?>>
                                Ακυρωμένο
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Ημερομηνία Έναρξης</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?= htmlspecialchars($project['start_date'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="completion_date" class="form-label">Ημερομηνία Ολοκλήρωσης</label>
                        <input type="date" class="form-control" id="completion_date" name="completion_date" 
                               value="<?= htmlspecialchars($project['completion_date'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="estimated_hours" class="form-label">Εκτιμώμενες Ώρες</label>
                        <input type="number" step="0.5" class="form-control" id="estimated_hours" name="estimated_hours" 
                               value="<?= htmlspecialchars($project['estimated_hours'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="material_cost" class="form-label">Κόστος Υλικών (€)</label>
                        <input type="number" step="0.01" class="form-control" id="material_cost" name="material_cost" 
                               value="<?= htmlspecialchars($project['material_cost'] ?? '0.00') ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="labor_cost" class="form-label">Κόστος Εργασίας (€)</label>
                        <input type="number" step="0.01" class="form-control" id="labor_cost" name="labor_cost" 
                               value="<?= htmlspecialchars($project['labor_cost'] ?? '0.00') ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="notes" class="form-label">Σημειώσεις</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($project['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="?route=/projects" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Ακύρωση
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Αποθήκευση Αλλαγών
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Force Greek date format in date inputs
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        // Set language attribute for Greek locale
        input.setAttribute('lang', 'el-GR');
        
        // Add placeholder for clarity
        input.setAttribute('placeholder', 'ΗΗ/ΜΜ/ΕΕΕΕ');
        
        // Show format hint on focus
        input.addEventListener('focus', function() {
            if (!this.value) {
                this.setAttribute('data-placeholder', this.placeholder);
            }
        });
    });
});
</script>

<?php
// Clear old input after displaying
if (isset($_SESSION['old_input'])) {
    unset($_SESSION['old_input']);
}
?>
