<?php
require_once __DIR__ . '/../layout/header.php';

$lang = $_SESSION['lang'] ?? 'el';
$translations = require __DIR__ . '/../../languages/' . $lang . '.json';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="bi bi-arrow-repeat"></i>
                        <?= $lang == 'el' ? 'Ενημέρωση Εφαρμογής' : 'Application Update' ?>
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- Current Version Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-file-earmark-code"></i>
                                        <?= $lang == 'el' ? 'Τρέχουσα Έκδοση Εφαρμογής' : 'Current Application Version' ?>
                                    </h5>
                                    <h2 class="text-primary mb-0">v<?= htmlspecialchars($updateInfo['current_version']) ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-database"></i>
                                        <?= $lang == 'el' ? 'Έκδοση Βάσης Δεδομένων' : 'Database Version' ?>
                                    </h5>
                                    <h2 class="text-warning mb-0">v<?= htmlspecialchars($updateInfo['database_version']) ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Status -->
                    <?php if ($updateInfo['update_available']): ?>
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <?= $lang == 'el' ? 'Διαθέσιμη Ενημέρωση!' : 'Update Available!' ?>
                            </h4>
                            <p>
                                <?= $lang == 'el' 
                                    ? 'Η βάση δεδομένων χρειάζεται ενημέρωση. Παρακαλώ εφαρμόστε τις παρακάτω ενημερώσεις.'
                                    : 'Your database needs to be updated. Please apply the updates below.' 
                                ?>
                            </p>
                        </div>

                        <!-- Available Updates -->
                        <div class="card mb-4">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-check"></i>
                                    <?= $lang == 'el' ? 'Ενημερώσεις προς Εφαρμογή' : 'Updates to Apply' ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group" id="updatesList">
                                    <?php foreach ($updateInfo['updates_needed'] as $index => $update): ?>
                                        <div class="list-group-item" data-update-index="<?= $index ?>">
                                            <div class="d-flex w-100 justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="mb-1">
                                                        <span class="badge bg-primary">v<?= htmlspecialchars($update['version']) ?></span>
                                                        <?= htmlspecialchars($update['name']) ?>
                                                    </h5>
                                                    <p class="mb-1 text-muted"><?= htmlspecialchars($update['description']) ?></p>
                                                    
                                                    <!-- Migration Files -->
                                                    <?php if (!empty($update['migrations'])): ?>
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <i class="bi bi-file-earmark-arrow-down"></i>
                                                                <?= $lang == 'el' ? 'Migrations:' : 'Migrations:' ?>
                                                            </small>
                                                            <ul class="small mb-0">
                                                                <?php foreach ($update['migrations'] as $migration): ?>
                                                                    <li><code><?= htmlspecialchars($migration) ?></code></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Scripts -->
                                                    <?php if (!empty($update['scripts'])): ?>
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <i class="bi bi-file-earmark-code"></i>
                                                                <?= $lang == 'el' ? 'Scripts:' : 'Scripts:' ?>
                                                            </small>
                                                            <ul class="small mb-0">
                                                                <?php foreach ($update['scripts'] as $script): ?>
                                                                    <li><code><?= htmlspecialchars($script) ?></code></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <span class="badge bg-secondary update-status" data-status="pending">
                                                        <?= $lang == 'el' ? 'Αναμονή' : 'Pending' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Progress Bar -->
                                            <div class="progress mt-3 d-none update-progress">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                     role="progressbar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Update Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-lg btn-success" id="btnApplyUpdates">
                                <i class="bi bi-arrow-repeat"></i>
                                <?= $lang == 'el' ? 'Εφαρμογή Όλων των Ενημερώσεων' : 'Apply All Updates' ?>
                            </button>
                            <a href="/settings" class="btn btn-lg btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                <?= $lang == 'el' ? 'Ακύρωση' : 'Cancel' ?>
                            </a>
                        </div>

                    <?php else: ?>
                        <!-- Up to Date -->
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading">
                                <i class="bi bi-check-circle-fill"></i>
                                <?= $lang == 'el' ? 'Η Εφαρμογή είναι Ενημερωμένη!' : 'Application is Up to Date!' ?>
                            </h4>
                            <p class="mb-0">
                                <?= $lang == 'el' 
                                    ? 'Η εφαρμογή και η βάση δεδομένων είναι στην τελευταία έκδοση.'
                                    : 'Your application and database are running the latest version.' 
                                ?>
                            </p>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="/settings" class="btn btn-lg btn-primary">
                                <i class="bi bi-arrow-left"></i>
                                <?= $lang == 'el' ? 'Επιστροφή στις Ρυθμίσεις' : 'Back to Settings' ?>
                            </a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Progress Modal -->
<div class="modal fade" id="updateProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat"></i>
                    <?= $lang == 'el' ? 'Εφαρμογή Ενημερώσεων' : 'Applying Updates' ?>
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <p class="text-center" id="updateProgressText">
                    <?= $lang == 'el' ? 'Παρακαλώ περιμένετε...' : 'Please wait...' ?>
                </p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" id="mainProgressBar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnApplyUpdates = document.getElementById('btnApplyUpdates');
    const progressModal = new bootstrap.Modal(document.getElementById('updateProgressModal'));
    const progressText = document.getElementById('updateProgressText');
    const progressBar = document.getElementById('mainProgressBar');
    
    if (btnApplyUpdates) {
        btnApplyUpdates.addEventListener('click', async function() {
            if (!confirm('<?= $lang == 'el' 
                ? "Είστε σίγουροι ότι θέλετε να εφαρμόσετε όλες τις ενημερώσεις; Συνιστάται να έχετε αντίγραφο ασφαλείας της βάσης δεδομένων."
                : "Are you sure you want to apply all updates? It is recommended to have a database backup." ?>')) {
                return;
            }
            
            // Disable button
            btnApplyUpdates.disabled = true;
            
            // Show modal
            progressModal.show();
            progressText.textContent = '<?= $lang == 'el' ? 'Εφαρμογή ενημερώσεων...' : 'Applying updates...' ?>';
            progressBar.style.width = '10%';
            
            try {
                const response = await fetch('/update/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                progressBar.style.width = '50%';
                
                const result = await response.json();
                
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animated');
                
                if (result.success) {
                    progressBar.classList.remove('bg-primary');
                    progressBar.classList.add('bg-success');
                    progressText.textContent = result.message;
                    
                    // Show success and reload
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    progressBar.classList.remove('bg-primary');
                    progressBar.classList.add('bg-danger');
                    progressText.textContent = result.message;
                    
                    setTimeout(() => {
                        progressModal.hide();
                        btnApplyUpdates.disabled = false;
                    }, 3000);
                }
                
            } catch (error) {
                progressBar.style.width = '100%';
                progressBar.classList.remove('bg-primary', 'progress-bar-animated');
                progressBar.classList.add('bg-danger');
                progressText.textContent = '<?= $lang == 'el' ? 'Σφάλμα:' : 'Error:' ?> ' + error.message;
                
                setTimeout(() => {
                    progressModal.hide();
                    btnApplyUpdates.disabled = false;
                }, 3000);
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
