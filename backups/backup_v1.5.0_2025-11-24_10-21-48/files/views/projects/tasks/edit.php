<?php
/**
 * Edit Project Task View
 * Similar to add.php but with prepopulated data
 */

$pageTitle = $title ?? 'Επεξεργασία Εργασίας';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects"><i class="fas fa-briefcase me-1"></i>Έργα</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects/<?= $project['slug'] ?>">
                    <?= htmlspecialchars($project['title'] ?? $project['name'] ?? 'Project') ?>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks">Εργασίες</a>
            </li>
            <li class="breadcrumb-item active">Επεξεργασία</li>
        </ol>
    </nav>

    <!-- Header with Photos Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Επεξεργασία Εργασίας</h4>
        <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/<?= $task['id'] ?>/photos" 
           class="btn btn-primary">
            <i class="fas fa-camera me-2"></i>Φωτογραφίες
        </a>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/edit/<?= $task['id'] ?>" id="taskForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">

        <div class="row">
            <!-- Left Column: Basic Info -->
            <div class="col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Επεξεργασία Εργασίας</h5>
                    </div>
                    <div class="card-body">
                        <!-- Task Type -->
                        <div class="mb-3">
                            <label class="form-label">Τύπος Εργασίας <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="task_type" id="type_single" value="single_day" 
                                       <?= $task['task_type'] === 'single_day' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="type_single">
                                    <i class="fas fa-calendar-day me-1"></i>Μονοήμερη
                                </label>
                                <input type="radio" class="btn-check" name="task_type" id="type_range" value="date_range"
                                       <?= $task['task_type'] === 'date_range' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-info" for="type_range">
                                    <i class="fas fa-calendar-week me-1"></i>Εύρος Ημερομηνιών
                                </label>
                            </div>
                        </div>

                        <!-- Single Day Date -->
                        <div class="mb-3" id="single_date_field" 
                             style="display:<?= $task['task_type'] === 'single_day' ? 'block' : 'none' ?>">
                            <label for="task_date" class="form-label">
                                Ημερομηνία <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="task_date" name="task_date" 
                                   value="<?= htmlspecialchars($task['task_date'] ?? '') ?>"
                                   <?= $task['task_type'] === 'single_day' ? 'required' : '' ?>>
                        </div>

                        <!-- Date Range Fields -->
                        <div id="date_range_fields" 
                             style="display:<?= $task['task_type'] === 'date_range' ? 'block' : 'none' ?>">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">
                                    Από Ημερομηνία <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="date_from" name="date_from"
                                       value="<?= htmlspecialchars($task['date_from'] ?? '') ?>"
                                       <?= $task['task_type'] === 'date_range' ? 'required' : '' ?>>
                            </div>
                            <div class="mb-3">
                                <label for="date_to" class="form-label">
                                    Έως Ημερομηνία <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="date_to" name="date_to"
                                       value="<?= htmlspecialchars($task['date_to'] ?? '') ?>"
                                       <?= $task['task_type'] === 'date_range' ? 'required' : '' ?>>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Περιγραφή <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($task['description']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="card shadow border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Συνολικό Κόστος</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="text-muted d-block">Υλικά</small>
                                <h4 class="text-warning mb-0">
                                    <span id="total_materials"><?= number_format($task['materials_total'], 2) ?></span> €
                                </h4>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Εργατικά</small>
                                <h4 class="text-info mb-0">
                                    <span id="total_labor"><?= number_format($task['labor_total'], 2) ?></span> €
                                </h4>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <small class="text-muted d-block">Γενικό Σύνολο</small>
                            <h3 class="text-success mb-0">
                                <span id="grand_total"><?= number_format($task['materials_total'] + $task['labor_total'], 2) ?></span> €
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Materials & Labor -->
            <div class="col-lg-7">
                <!-- Materials Section -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Υλικά</h5>
                            <button type="button" class="btn btn-sm btn-dark" onclick="addMaterialRow()">
                                <i class="fas fa-plus me-1"></i>Προσθήκη Υλικού
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="materials_container">
                            <!-- Material rows will be populated from existing data -->
                        </div>
                        <div class="text-muted text-center py-3" id="materials_empty" style="display:none;">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">Πατήστε "Προσθήκη Υλικού" για να προσθέσετε υλικά</p>
                        </div>
                    </div>
                </div>

                <!-- Labor Section -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-user-hard-hat me-2"></i>Εργατικά</h5>
                            <button type="button" class="btn btn-sm btn-dark" onclick="addLaborRow()">
                                <i class="fas fa-plus me-1"></i>Προσθήκη Τεχνικού
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="labor_container">
                            <!-- Labor rows will be populated from existing data -->
                        </div>
                        <div class="text-muted text-center py-3" id="labor_empty" style="display:none;">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">Πατήστε "Προσθήκη Τεχνικού" για να προσθέσετε εργατικά</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Ακύρωση
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save me-2"></i>Ενημέρωση Εργασίας
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Include Project Tasks JavaScript -->
<script>
// Technicians data for dropdowns
const technicians = <?= json_encode($technicians ?? []) ?>;
const projectId = <?= $project['id'] ?>;
const taskId = <?= $task['id'] ?>;

// Existing materials and labor data
// Always use the current data from database, not pending data
// The pending data materials/labor have wrong structure (POST array format)
const existingMaterials = <?= json_encode($materials ?? []) ?>;
const existingLabor = <?= json_encode($labor ?? []) ?>;

// Material and Labor row counters
let materialCounter = 0;
let laborCounter = 0;
</script>

<!-- Material Unit Types Datalist -->
<datalist id="unit_types">
    <option value="τεμάχια">
    <option value="τεμ">
    <option value="μέτρα">
    <option value="μ">
    <option value="τ.μ.">
    <option value="κ.μ.">
    <option value="κιλά">
    <option value="kg">
    <option value="λίτρα">
    <option value="λ">
    <option value="τόνοι">
    <option value="κουτιά">
    <option value="συσκευασίες">
</datalist>

<script src="<?= BASE_URL ?>/public/js/material-autocomplete.js"></script>
<script src="<?= BASE_URL ?>/public/js/project-tasks.js"></script>

<script>
// Task type toggle
document.querySelectorAll('input[name="task_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const isSingleDay = this.value === 'single_day';
        document.getElementById('single_date_field').style.display = isSingleDay ? 'block' : 'none';
        document.getElementById('date_range_fields').style.display = isSingleDay ? 'none' : 'block';
        
        // Update required attributes
        document.getElementById('task_date').required = isSingleDay;
        document.getElementById('date_from').required = !isSingleDay;
        document.getElementById('date_to').required = !isSingleDay;
        
    });
});

// Form validation
document.getElementById('taskForm').addEventListener('submit', function(e) {
    const description = document.getElementById('description').value.trim();
    
    if (!description) {
        e.preventDefault();
        alert('Η περιγραφή είναι υποχρεωτική');
        return false;
    }
});

// Populate existing data on load (using project-tasks.js functions)
window.addEventListener('DOMContentLoaded', function() {
    // Add existing materials
    if (existingMaterials && existingMaterials.length > 0) {
        existingMaterials.forEach(material => {
            addMaterialRow(material);
        });
    }
    // Don't add empty row if no materials exist
    
    // Add existing labor
    if (existingLabor && existingLabor.length > 0) {
        existingLabor.forEach(labor => {
            addLaborRow(labor);
        });
    }
    // Don't add empty row if no labor exists
    
    // Recalculate totals
    calculateGrandTotal();
});

// Track if form has unsaved changes (new materials or labor added during editing)
let hasUnsavedChanges = false;
let isSubmitting = false;
let initialMaterialCount = 0;
let initialLaborCount = 0;

// Store initial counts after page loads
window.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        initialMaterialCount = document.querySelectorAll('.material-row').length;
        initialLaborCount = document.querySelectorAll('.labor-row').length;
    }, 100);
});

// Monitor when materials or labor are added
function markFormAsModified() {
    hasUnsavedChanges = true;
}

// Check if there are NEW materials or labor added (not just the existing ones)
function checkForUnsavedData() {
    const currentMaterials = document.querySelectorAll('.material-row').length;
    const currentLabor = document.querySelectorAll('.labor-row').length;
    
    // Check if new items were added OR if any input has changed
    const hasNewItems = (currentMaterials > initialMaterialCount) || (currentLabor > initialLaborCount);
    
    return hasNewItems || hasUnsavedChanges;
}

// Warn user before leaving page if they have unsaved changes
window.addEventListener('beforeunload', function(e) {
    // Don't show warning if form is being submitted
    if (isSubmitting) {
        return undefined;
    }
    
    // Check if there are unsaved materials or labor
    if (checkForUnsavedData()) {
        const confirmationMessage = 'Έχετε μη αποθηκευμένες αλλαγές (υλικά ή εργατικά). Είστε σίγουροι ότι θέλετε να φύγετε;';
        e.preventDefault();
        e.returnValue = confirmationMessage;
        return confirmationMessage;
    }
});

// Mark form as submitting when user clicks submit button
document.getElementById('taskForm').addEventListener('submit', function() {
    isSubmitting = true;
});

// Override the original addMaterialRow to mark form as modified
const originalAddMaterialRow = window.addMaterialRow;
window.addMaterialRow = function(material) {
    const result = originalAddMaterialRow(material);
    markFormAsModified();
    return result;
};

// Override the original addLaborRow to mark form as modified
const originalAddLaborRow = window.addLaborRow;
window.addLaborRow = function(labor) {
    const result = originalAddLaborRow(labor);
    markFormAsModified();
    return result;
};

</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>


