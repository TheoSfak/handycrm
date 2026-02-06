<?php
/**
 * Technicians List View
 * Display all technicians with filters
 */

$pageTitle = $title ?? 'Τεχνικοί & Βοηθοί';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-user-hard-hat me-2"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h1>
            <p class="text-muted mb-0">Διαχείριση τεχνικών και βοηθών</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= BASE_URL ?>/technicians/add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Νέος Τεχνικός
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/technicians" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Φίλτρο</label>
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="active" <?= ($current_filter ?? 'active') === 'active' ? 'selected' : '' ?>>
                            Ενεργοί
                        </option>
                        <option value="all" <?= ($current_filter ?? '') === 'all' ? 'selected' : '' ?>>
                            Όλοι
                        </option>
                        <option value="technician" <?= ($current_filter ?? '') === 'technician' ? 'selected' : '' ?>>
                            Μόνο Τεχνικοί
                        </option>
                        <option value="assistant" <?= ($current_filter ?? '') === 'assistant' ? 'selected' : '' ?>>
                            Μόνο Βοηθοί
                        </option>
                    </select>
                </div>
                <div class="col-md-9 d-flex align-items-end">
                    <span class="text-muted">
                        Σύνολο: <strong><?= count($technicians ?? []) ?></strong> τεχνικοί
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Technicians List -->
    <?php if (empty($technicians)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Δεν βρέθηκαν τεχνικοί με τα τρέχοντα φίλτρα.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($technicians as $tech): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <div class="card-body">
                            <!-- Role Badge -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <?php if ($tech['role'] === 'technician'): ?>
                                        <i class="fas fa-user-tie text-primary me-2"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user text-info me-2"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($tech['name']) ?>
                                </h5>
                                <?php if ($tech['role'] === 'technician'): ?>
                                    <span class="badge bg-primary">Τεχνικός</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Βοηθός</span>
                                <?php endif; ?>
                            </div>

                            <!-- Hourly Rate -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-euro-sign text-success me-2"></i>
                                    <span class="h4 mb-0 text-success">
                                        <?= number_format($tech['hourly_rate'], 2) ?>€
                                    </span>
                                    <span class="text-muted ms-2">/ώρα</span>
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <?php if (!empty($tech['phone'])): ?>
                                <div class="mb-2">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <a href="tel:<?= htmlspecialchars($tech['phone']) ?>">
                                        <?= htmlspecialchars($tech['phone']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($tech['email'])): ?>
                                <div class="mb-3">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    <a href="mailto:<?= htmlspecialchars($tech['email']) ?>">
                                        <?= htmlspecialchars($tech['email']) ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Status -->
                            <div class="mb-3">
                                <?php if ($tech['is_active']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Ενεργός
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times-circle me-1"></i>Ανενεργός
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="btn-group w-100" role="group">
                                <a href="<?= BASE_URL ?>/technicians/view/<?= $tech['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Προβολή
                                </a>
                                <a href="<?= BASE_URL ?>/technicians/edit/<?= $tech['id'] ?>" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit me-1"></i>Επεξεργασία
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
