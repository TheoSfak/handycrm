<?php
/**
 * Add Project Task View
 * Complex form with dynamic materials and labor rows
 */

$pageTitle = $title ?? 'Νέα Εργασία';
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
                <a href="<?= BASE_URL ?>/projects/details/<?= $project['id'] ?>">
                    <?= htmlspecialchars($project['title'] ?? $project['name'] ?? 'Project') ?>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks">Εργασίες</a>
            </li>
            <li class="breadcrumb-item active">Νέα Εργασία</li>
        </ol>
    </nav>

    <form method="POST" action="<?= BASE_URL ?>/projects/<?= $project['id'] ?>/tasks/add" id="taskForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

        <div class="row">
            <!-- Left Column: Basic Info -->
            <div class="col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Βασικές Πληροφορίες</h5>
                    </div>
                    <div class="card-body">
                        <!-- Task Type -->
                        <div class="mb-3">
                            <label class="form-label">Τύπος Εργασίας <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="task_type" id="type_single" value="single_day" checked>
                                <label class="btn btn-outline-primary" for="type_single">
                                    <i class="fas fa-calendar-day me-1"></i>Μονοήμερη
                                </label>
                                <input type="radio" class="btn-check" name="task_type" id="type_range" value="date_range">
                                <label class="btn btn-outline-info" for="type_range">
                                    <i class="fas fa-calendar-week me-1"></i>Εύρος Ημερομηνιών
                                </label>
                            </div>
                        </div>

                        <!-- Single Day Date -->
                        <div class="mb-3" id="single_date_field">
                            <label for="task_date" class="form-label">
                                Ημερομηνία <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="task_date" name="task_date" 
                                   value="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Date Range Fields -->
                        <div id="date_range_fields" style="display:none;">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">
                                    Από Ημερομηνία <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                            <div class="mb-3">
                                <label for="date_to" class="form-label">
                                    Έως Ημερομηνία <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                            <div id="overlap_warning" class="alert alert-warning" style="display:none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Προσοχή!</strong> Υπάρχουν ήδη εργασίες σε αυτό το χρονικό διάστημα.
                                <div id="overlap_list" class="mt-2 small"></div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Περιγραφή <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
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
                                    <span id="total_materials">0.00</span> €
                                </h4>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Εργατικά</small>
                                <h4 class="text-info mb-0">
                                    <span id="total_labor">0.00</span> €
                                </h4>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <small class="text-muted d-block">Γενικό Σύνολο</small>
                            <h3 class="text-success mb-0">
                                <span id="grand_total">0.00</span> €
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
                            <button type="button" class="btn btn-sm btn-light" onclick="addMaterialRow()">
                                <i class="fas fa-plus me-1"></i>Προσθήκη Υλικού
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="materials_container">
                            <!-- Material rows will be added here -->
                        </div>
                        <div class="text-muted text-center py-3" id="materials_empty">
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
                            <button type="button" class="btn btn-sm btn-light" onclick="addLaborRow()">
                                <i class="fas fa-plus me-1"></i>Προσθήκη Τεχνικού
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="labor_container">
                            <!-- Labor rows will be added here -->
                        </div>
                        <div class="text-muted text-center py-3" id="labor_empty">
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
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Αποθήκευση Εργασίας
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

// Material and Labor row counters
let materialCounter = 0;
let laborCounter = 0;
</script>
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
        
        // Check for overlaps if date range
        if (!isSingleDay) {
            checkOverlap();
        }
    });
});

// Check for overlaps on date change
document.getElementById('date_from')?.addEventListener('change', checkOverlap);
document.getElementById('date_to')?.addEventListener('change', checkOverlap);

// Form validation
document.getElementById('taskForm').addEventListener('submit', function(e) {
    const description = document.getElementById('description').value.trim();
    
    if (!description) {
        e.preventDefault();
        alert('Η περιγραφή είναι υποχρεωτική');
        return false;
    }
    
    // Check if at least one material or labor entry exists
    const hasMaterials = document.querySelectorAll('#materials_container .material-row').length > 0;
    const hasLabor = document.querySelectorAll('#labor_container .labor-row').length > 0;
    
    if (!hasMaterials && !hasLabor) {
        e.preventDefault();
        alert('Πρέπει να προσθέσετε τουλάχιστον ένα υλικό ή ένα εργατικό');
        return false;
    }
});

// Initialize with one material and one labor row
window.addEventListener('DOMContentLoaded', function() {
    addMaterialRow();
    addLaborRow();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
