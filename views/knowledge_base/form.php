<?php
$article = $article ?? null;
$formAction = $formAction ?? (BASE_URL . '/knowledge-base/store');
$categories = $categories ?? [];
$selectedCategoryIds = $selectedCategoryIds ?? [];
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-book me-2 text-primary"></i>
                <?= $article ? 'Επεξεργασία Case' : 'Νέο Case' ?>
            </h2>
            <p class="text-muted mb-0 mt-1">Συμπλήρωσε τίτλο, περιγραφή και συνημμένα.</p>
        </div>
        <a href="<?= BASE_URL ?>/knowledge-base" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Επιστροφή
        </a>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'missing_fields'): ?>
        <div class="alert alert-danger">Συμπλήρωσε υποχρεωτικά πεδία (τίτλος, περιγραφή).</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= $formAction ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Τίτλος <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" maxlength="255" required value="<?= htmlspecialchars($article['title'] ?? '') ?>" placeholder="π.χ. How to commission Huawei 500JTL-M3">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Κατηγορίες</label>
                    <select name="category_ids[]" class="form-select" multiple size="6">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['id'] ?>" <?= in_array((int)$category['id'], $selectedCategoryIds, true) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Επιλογή πολλαπλών κατηγοριών με Ctrl/Cmd + click.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Περιγραφή Case <span class="text-danger">*</span></label>
                    <textarea name="content" class="form-control" rows="14" required placeholder="Γράψε αναλυτικά τα βήματα, τα συμπτώματα και τη λύση..."><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                </div>

                <?php if (!empty($article['attachments'])): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Υπάρχοντα συνημμένα</label>
                        <ul class="list-group">
                            <?php foreach ($article['attachments'] as $attachment): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas <?= strpos($attachment['mime_type'], 'image/') === 0 ? 'fa-image' : 'fa-file-pdf text-danger' ?> me-2"></i>
                                        <?= htmlspecialchars($attachment['original_filename']) ?>
                                    </span>
                                    <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= BASE_URL ?>/knowledge-base/file/<?= (int)$attachment['id'] ?>">
                                        Προβολή
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Νέα συνημμένα (προαιρετικά)</label>
                    <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,image/jpeg,image/png,image/webp">
                    <div class="form-text">
                        Εικόνες έως 10MB ανά αρχείο, PDF έως 30MB.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Αποθήκευση
                    </button>
                    <a href="<?= BASE_URL ?>/knowledge-base" class="btn btn-outline-secondary">Άκυρο</a>
                </div>
            </form>
        </div>
    </div>
</div>
