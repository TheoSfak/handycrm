<?php
$article = $article ?? [];
$canEdit = $canEdit ?? false;
$canDelete = $canDelete ?? false;
$canExport = $canExport ?? false;
$article['categories'] = $article['categories'] ?? [];
$article['attachments'] = $article['attachments'] ?? [];
$imageAttachments = array_values(array_filter($article['attachments'], static function ($attachment) {
    return isset($attachment['mime_type']) && strpos((string)$attachment['mime_type'], 'image/') === 0;
}));
$fileAttachments = array_values(array_filter($article['attachments'], static function ($attachment) {
    return !isset($attachment['mime_type']) || strpos((string)$attachment['mime_type'], 'image/') !== 0;
}));
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-book-open me-2 text-primary"></i><?= htmlspecialchars($article['title']) ?></h2>
            <p class="text-muted mb-0 mt-1">
                Συντάκτης: <?= htmlspecialchars($article['author_name'] ?: '—') ?> | <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/knowledge-base" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Επιστροφή</a>
            <button type="button" class="btn btn-outline-dark" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
            <?php if ($canExport): ?>
                <a href="<?= BASE_URL ?>/knowledge-base/export-pdf/<?= (int)$article['id'] ?>" class="btn btn-outline-danger"><i class="fas fa-file-pdf me-1"></i> PDF</a>
            <?php endif; ?>
            <?php if ($canEdit): ?>
                <a href="<?= BASE_URL ?>/knowledge-base/edit/<?= (int)$article['id'] ?>" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Επεξεργασία</a>
            <?php endif; ?>
            <?php if ($canDelete): ?>
                <form method="POST" action="<?= BASE_URL ?>/knowledge-base/delete/<?= (int)$article['id'] ?>" onsubmit="return confirm('ΠΡΟΣΟΧΗ: Η διαγραφή είναι οριστική και δεν αναιρείται. Συνέχεια;');">
                    <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Οριστική Διαγραφή</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Η καταχώρηση αποθηκεύτηκε επιτυχώς.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Περιγραφή Περιστατικού</h5>
                    <div class="lh-lg"><?= nl2br(htmlspecialchars($article['content'])) ?></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold">Κατηγορίες</h6>
                    <?php if (empty($article['categories'])): ?>
                        <p class="text-muted mb-0">Χωρίς κατηγορία</p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($article['categories'] as $category): ?>
                                <span class="badge rounded-pill kb-cat-badge"><?= htmlspecialchars($category['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Συνημμένα</h6>
                    <?php if (empty($article['attachments'])): ?>
                        <p class="text-muted mb-0">Δεν υπάρχουν συνημμένα.</p>
                    <?php else: ?>
                        <?php if (!empty($imageAttachments)): ?>
                            <div class="mb-3">
                                <div class="small text-muted fw-semibold mb-2">Προεπισκόπηση εικόνων</div>
                                <div class="row g-2">
                                    <?php foreach ($imageAttachments as $attachment): ?>
                                        <div class="col-6">
                                            <a href="<?= BASE_URL ?>/knowledge-base/file/<?= (int)$attachment['id'] ?>" target="_blank" class="text-decoration-none">
                                                <div class="card kb-image-card h-100">
                                                    <img
                                                        src="<?= BASE_URL ?>/knowledge-base/file/<?= (int)$attachment['id'] ?>"
                                                        class="card-img-top kb-image-preview"
                                                        alt="<?= htmlspecialchars($attachment['original_filename']) ?>"
                                                        loading="lazy"
                                                    >
                                                    <div class="card-body p-2">
                                                        <div class="small text-truncate" title="<?= htmlspecialchars($attachment['original_filename']) ?>">
                                                            <?= htmlspecialchars($attachment['original_filename']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($fileAttachments)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($fileAttachments as $attachment): ?>
                                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank" href="<?= BASE_URL ?>/knowledge-base/file/<?= (int)$attachment['id'] ?>">
                                        <span class="small text-truncate me-2">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <?= htmlspecialchars($attachment['original_filename']) ?>
                                        </span>
                                        <i class="fas fa-external-link-alt text-muted"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
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

.kb-image-card {
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    overflow: hidden;
}

.kb-image-preview {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-bottom: 1px solid #e5e7eb;
    transition: transform 0.25s ease;
}

.kb-image-card:hover {
    transform: translateY(-2px);
    border-color: #c7d2fe;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.14);
}

.kb-image-card:hover .kb-image-preview {
    transform: scale(1.03);
}

@media print {
    .btn,
    .navbar,
    .sidebar,
    .no-print,
    form {
        display: none !important;
    }

    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
