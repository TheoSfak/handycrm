<?php
$records = $records ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$pages = $pages ?? 1;
$search = $search ?? '';
$currentUserId = $currentUserId ?? 0;
$isAdminOrSupervisor = $isAdminOrSupervisor ?? false;
$canCreate = $canCreate ?? false;
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Τιμοκατάλογοι</h2>
            <p class="text-muted mb-0 mt-1">Απλή αρχειοθήκη αρχείων τιμοκαταλόγων.</p>
        </div>
        <?php if ($canCreate): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadPriceListModal">
                <i class="fas fa-upload me-1"></i> Ανέβασμα
            </button>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show">Ο τιμοκατάλογος αποθηκεύτηκε.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show">Ο τιμοκατάλογος διαγράφηκε οριστικά.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif (isset($_GET['error'])): ?>
        <?php
        $errors = [
            'missing_title' => 'Συμπλήρωσε τίτλο.',
            'no_file' => 'Επίλεξε αρχείο.',
            'invalid_type' => 'Μη επιτρεπτός τύπος αρχείου.',
            'file_too_large' => 'Το αρχείο υπερβαίνει το επιτρεπτό όριο.',
            'upload_failed' => 'Αποτυχία ανεβάσματος.',
            'not_found' => 'Δεν βρέθηκε εγγραφή.',
            'unauthorized' => 'Δεν έχεις δικαίωμα για αυτή την ενέργεια.',
        ];
        ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($errors[$_GET['error']] ?? 'Σφάλμα.') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= BASE_URL ?>/price-lists" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Αναζήτηση</label>
                    <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Τίτλος ή όνομα αρχείου...">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Αναζήτηση</button>
                    <a href="<?= BASE_URL ?>/price-lists" class="btn btn-outline-secondary ms-1">Καθαρισμός</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($records)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">Δεν υπάρχουν τιμοκατάλογοι.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Τίτλος</th>
                                <th>Αρχείο</th>
                                <th>Καταχώρησε</th>
                                <th>Ημ/νία</th>
                                <th class="text-end">Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $row): ?>
                                <?php $canDelete = $isAdminOrSupervisor || (int)$row['created_by'] === (int)$currentUserId; ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['title']) ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($row['original_filename']) ?></td>
                                    <td><?= htmlspecialchars($row['created_by_name'] ?: '—') ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= BASE_URL ?>/price-lists/file/<?= (int)$row['id'] ?>" title="Προβολή/Λήψη">
                                            <i class="fas fa-file-arrow-down"></i>
                                        </a>
                                        <?php if ($canDelete): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/price-lists/delete/<?= (int)$row['id'] ?>" class="d-inline" onsubmit="return confirm('ΠΡΟΣΟΧΗ: Η διαγραφή είναι οριστική και δεν αναιρείται. Συνέχεια;');">
                                                <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Οριστική διαγραφή">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pages > 1): ?>
                    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                        <small class="text-muted">Εμφανίζονται <?= count($records) ?> από <?= $total ?> αρχεία</small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($p = 1; $p <= $pages; $p++): ?>
                                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= BASE_URL ?>/price-lists?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($canCreate): ?>
<div class="modal fade" id="uploadPriceListModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/price-lists/store" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Ανέβασμα Τιμοκαταλόγου</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Τίτλος <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Αρχείο <span class="text-danger">*</span></label>
                        <input type="file" name="price_list_file" class="form-control" required>
                        <div class="form-text">PDF έως 30MB, εικόνες έως 10MB, Excel/CSV έως 30MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Άκυρο</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Ανέβασμα</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
