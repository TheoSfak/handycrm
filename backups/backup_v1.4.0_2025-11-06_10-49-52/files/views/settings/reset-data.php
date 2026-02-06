<?php
/**
 * Reset Database View
 * Data is passed from controller: $counts, $success, $error
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Μηδενισμός Δεδομένων (Reset Database)
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (isset($success) && $success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i>
                            <strong>Επιτυχία!</strong> Όλα τα δεδομένα διαγράφηκαν επιτυχώς!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div class="text-center mt-4">
                            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary">
                                <i class="fas fa-home"></i> Επιστροφή στο Dashboard
                            </a>
                        </div>
                    <?php else: ?>
                        
                        <?php if (isset($error) && $error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-times-circle"></i>
                                <strong>Σφάλμα:</strong> <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> ΠΡΟΣΟΧΗ!</h5>
                            <p>Αυτή η ενέργεια θα <strong>ΔΙΑΓΡΑΨΕΙ ΟΡΙΣΤΙΚΑ</strong> όλα τα παρακάτω δεδομένα:</p>
                        </div>

                        <div class="row mb-4">
                            <?php if (isset($counts)): foreach ($counts as $label => $count): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h2 class="text-danger mb-0"><?= $count ?></h2>
                                            <p class="mb-0"><?= $label ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Τι θα διαγραφεί:</h6>
                            <ul class="mb-0">
                                <li>Όλοι οι πελάτες</li>
                                <li>Όλα τα έργα</li>
                                <li>Όλα τα τιμολόγια</li>
                                <li>Όλες οι προσφορές</li>
                                <li>Όλα τα ραντεβού</li>
                                <li>Όλα τα υλικά</li>
                                <li>Όλες οι επικοινωνίες</li>
                                <li>Όλα τα αρχεία έργων</li>
                            </ul>
                        </div>

                        <div class="alert alert-success">
                            <h6><i class="fas fa-shield-alt"></i> Τι θα ΚΡΑΤΗΘΕΙ:</h6>
                            <ul class="mb-0">
                                <li>Λογαριασμοί χρηστών (users)</li>
                                <li>Ρυθμίσεις συστήματος (settings)</li>
                            </ul>
                        </div>

                        <div class="card border-danger mt-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="fas fa-lock"></i> Επιβεβαίωση Μηδενισμού</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?= BASE_URL ?>/settings/reset-data" id="resetForm" onsubmit="return confirmReset()">
                                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            Για να συνεχίσετε, πληκτρολογήστε: 
                                            <strong class="text-danger">RESET DATA</strong>
                                        </label>
                                        <input type="text" 
                                               name="confirmation" 
                                               class="form-control form-control-lg" 
                                               placeholder="Πληκτρολογήστε: RESET DATA"
                                               required
                                               autocomplete="off">
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="<?= BASE_URL ?>/settings" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Ακύρωση
                                        </a>
                                        <button type="submit" class="btn btn-danger btn-lg">
                                            <i class="fas fa-trash-alt"></i> ΜΗΔΕΝΙΣΜΟΣ ΒΑΣΗΣ
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-lightbulb"></i>
                            <strong>Συμβουλή:</strong> Κάντε backup της βάσης δεδομένων πριν συνεχίσετε!
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    return confirm('ΤΕΛΙΚΗ ΕΠΙΒΕΒΑΙΩΣΗ: Είστε 100% σίγουρος/η ότι θέλετε να διαγράψετε ΟΛΑ τα δεδομένα; Αυτή η ενέργεια ΔΕΝ μπορεί να αναιρεθεί!');
}

// Add red border when typing
document.addEventListener('DOMContentLoaded', function() {
    var input = document.querySelector('input[name="confirmation"]');
    if (input) {
        input.addEventListener('input', function() {
            if (this.value.toUpperCase() === 'RESET DATA') {
                this.classList.remove('border-danger');
                this.classList.add('border-success');
            } else {
                this.classList.remove('border-success');
                this.classList.add('border-danger');
            }
        });
    }
});
</script>
