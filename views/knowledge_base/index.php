<?php
$articles = $articles ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$pages = $pages ?? 1;
$search = $search ?? '';
$categoryId = $categoryId ?? null;
$categories = $categories ?? [];
$canCreate = $canCreate ?? false;
$canManageCategories = $canManageCategories ?? false;
$isAdminOrSupervisor = $isAdminOrSupervisor ?? false;
$currentUserId = $currentUserId ?? 0;
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-book-open me-2 text-primary"></i>Βάση Γνώσης</h2>
            <p class="text-muted mb-0 mt-1">Καταγραφή δύσκολων τεχνικών περιστατικών με κείμενο και συνημμένα.</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($canManageCategories): ?>
                <a href="<?= BASE_URL ?>/knowledge-base/categories" class="btn btn-outline-secondary">
                    <i class="fas fa-tags me-1"></i> Κατηγορίες
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/knowledge-base/export-pdf?search=<?= urlencode($search) ?>&category_id=<?= (int)$categoryId ?>" class="btn btn-outline-dark">
                <i class="fas fa-file-pdf me-1"></i> PDF Λίστας
            </a>
            <?php if ($canCreate): ?>
                <a href="<?= BASE_URL ?>/knowledge-base/create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Νέο Case
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Το case διαγράφηκε οριστικά.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= BASE_URL ?>/knowledge-base" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Αναζήτηση</label>
                    <input type="text" name="search" class="form-control" placeholder="Τίτλος ή περιγραφή..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Κατηγορία</label>
                    <select name="category_id" class="form-select">
                        <option value="">Όλες οι κατηγορίες</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['id'] ?>" <?= (int)$categoryId === (int)$category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Φίλτρο</button>
                    <a href="<?= BASE_URL ?>/knowledge-base" class="btn btn-outline-secondary ms-1">Καθαρισμός</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($articles)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">Δεν βρέθηκαν καταχωρήσεις.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Τίτλος</th>
                                <th>Κατηγορίες</th>
                                <th>Συντάκτης</th>
                                <th>Ημ/νία</th>
                                <th class="text-end">Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $row): ?>
                                <?php
                                $canModify = $isAdminOrSupervisor || (int)$row['created_by'] === (int)$currentUserId;
                                $categoryLabels = [];
                                if (!empty($row['categories'])) {
                                    $categoryLabels = array_filter(array_map('trim', explode(',', (string)$row['categories'])));
                                }
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold kb-title-cell"><?= htmlspecialchars($row['title']) ?></div>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-calendar-alt me-1"></i><?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (empty($categoryLabels)): ?>
                                            <span class="text-muted small">—</span>
                                        <?php else: ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach ($categoryLabels as $label): ?>
                                                    <span class="badge rounded-pill kb-cat-badge"><?= htmlspecialchars($label) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['author_name'] ?: '—') ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['updated_at'] ?? $row['created_at'])) ?></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/knowledge-base/show/<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-primary kb-action-btn" title="Προβολή">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($canModify): ?>
                                            <a href="<?= BASE_URL ?>/knowledge-base/edit/<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-secondary kb-action-btn" title="Επεξεργασία">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form method="POST" action="<?= BASE_URL ?>/knowledge-base/delete/<?= (int)$row['id'] ?>" class="d-inline" onsubmit="return confirm('ΠΡΟΣΟΧΗ: Η διαγραφή είναι οριστική και δεν αναιρείται. Συνέχεια;');">
                                                <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger kb-action-btn" title="Οριστική διαγραφή">
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
                        <small class="text-muted">Εμφανίζονται <?= count($articles) ?> από <?= $total ?> cases</small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($p = 1; $p <= $pages; $p++): ?>
                                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= BASE_URL ?>/knowledge-base?page=<?= $p ?>&search=<?= urlencode($search) ?>&category_id=<?= (int)$categoryId ?>"><?= $p ?></a>
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

<style>
.kb-cat-badge {
    background: #eef2ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
    font-weight: 500;
}

.kb-title-cell {
    max-width: 420px;
    line-height: 1.3;
}

.kb-action-btn {
    min-width: 32px;
}
</style>
