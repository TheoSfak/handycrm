<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Κατηγορίες Knowledge Base</h2>
            <p class="text-muted mb-0 mt-1">Μόνο Admin/Supervisor μπορούν να διαχειριστούν κατηγορίες.</p>
        </div>
        <a href="<?= BASE_URL ?>/knowledge-base" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Επιστροφή</a>
    </div>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show">Η κατηγορία προστέθηκε.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show">Η κατηγορία διαγράφηκε.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_GET['error'] === 'exists' ? 'Η κατηγορία υπάρχει ήδη.' : 'Σφάλμα καταχώρησης.' ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Νέα κατηγορία</h6>
                    <form method="POST" action="<?= BASE_URL ?>/knowledge-base/categories/store">
                        <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
                        <div class="mb-3">
                            <label class="form-label">Όνομα κατηγορίας</label>
                            <input type="text" name="name" class="form-control" maxlength="120" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Προσθήκη</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Υπάρχουσες κατηγορίες</h6>
                    <?php if (empty($categories)): ?>
                        <p class="text-muted mb-0">Δεν υπάρχουν κατηγορίες.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($categories as $category): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($category['name']) ?></span>
                                    <form method="POST" action="<?= BASE_URL ?>/knowledge-base/categories/delete/<?= (int)$category['id'] ?>" onsubmit="return confirm('ΠΡΟΣΟΧΗ: Η διαγραφή είναι οριστική. Συνέχεια;');">
                                        <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
