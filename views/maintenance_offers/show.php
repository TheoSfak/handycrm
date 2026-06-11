<?php $pageTitle = 'Προβολή Προσφοράς Συντήρησης'; ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">
                        <i class="fas fa-file-contract text-primary me-2"></i>
                        Προσφορά <span class="badge bg-secondary"><?= htmlspecialchars($offer['offer_number']) ?></span>
                    </h4>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/maintenance-offers/edit/<?= $offer['id'] ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i> Επεξεργασία
                    </a>
                    <a href="<?= BASE_URL ?>/maintenance-offers/export-pdf/<?= $offer['id'] ?>" class="btn btn-danger btn-sm" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                    <a href="<?= BASE_URL ?>/maintenance-offers/export-word/<?= $offer['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-word me-1"></i> Word
                    </a>
                    <a href="<?= BASE_URL ?>/maintenance-offers" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Πίσω
                    </a>
                </div>
            </div>

            <!-- Status bar -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Κατάσταση</div>
                            <?php if ($offer['accepted']): ?>
                                <span class="badge bg-success fs-6">Αποδεκτή</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark fs-6">Εκκρεμεί</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Ημ/νία Αποστολής</div>
                            <div class="fw-semibold"><?= $offer['sent_at'] ? date('d/m/Y', strtotime($offer['sent_at'])) : '—' ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Λήξη Προσφοράς</div>
                            <?php if ($offer['offer_expiry_date']): ?>
                                <?php
                                    $expired = new DateTime() > new DateTime($offer['offer_expiry_date']);
                                ?>
                                <span class="badge bg-<?= $expired ? 'danger' : 'success' ?>">
                                    <?= date('d/m/Y', strtotime($offer['offer_expiry_date'])) ?>
                                </span>
                            <?php else: ?>
                                <div class="text-muted">—</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Προγραμματίστηκε</div>
                            <div class="fw-semibold">
                                <?= !empty($offer['scheduled_date']) ? date('d/m/Y', strtotime($offer['scheduled_date'])) : '—' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Left: Company info + Offer details -->
                <div class="col-md-7">

                    <!-- Company Info -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-building me-2 text-primary"></i>Στοιχεία Επιχείρησης</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted">Επωνυμία</dt>
                                <dd class="col-sm-8 fw-semibold"><?= htmlspecialchars($offer['company_name']) ?></dd>

                                <dt class="col-sm-4 text-muted">Διεύθυνση</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($offer['address'] ?: '—') ?></dd>

                                <dt class="col-sm-4 text-muted">Τηλέφωνο</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($offer['phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($offer['phone']) ?>"><?= htmlspecialchars($offer['phone']) ?></a>
                                    <?php else: ?>—<?php endif; ?>
                                </dd>

                                <dt class="col-sm-4 text-muted">Email</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($offer['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($offer['email']) ?>"><?= htmlspecialchars($offer['email']) ?></a>
                                    <?php else: ?>—<?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if (!empty($offer['notes'])): ?>
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-sticky-note me-2 text-warning"></i>Σημειώσεις</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($offer['notes'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Offer pricing + meta -->
                <div class="col-md-5">

                    <!-- Offer Details -->
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-bolt me-2 text-primary"></i>Στοιχεία Προσφοράς</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-7 text-muted">Αρ. Μετασχηματιστών</dt>
                                <dd class="col-sm-5">
                                    <span class="badge bg-info fs-6"><?= (int)$offer['transformers_count'] ?></span>
                                </dd>

                                <dt class="col-sm-7 text-muted">Τιμή Προσφοράς</dt>
                                <dd class="col-sm-5 fw-bold text-success fs-5">
                                    <?= number_format((float)$offer['price'], 2, ',', '.') ?> €
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Acceptance Info -->
                    <?php if ($offer['accepted'] && $offer['accepted_at']): ?>
                    <div class="card mb-4 shadow-sm border-success">
                        <div class="card-header bg-success bg-opacity-10">
                            <h6 class="mb-0 text-success"><i class="fas fa-check-circle me-2"></i>Αποδοχή</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-muted small">Αποδέχθηκε στις</p>
                            <p class="mb-0 fw-semibold"><?= date('d/m/Y H:i', strtotime($offer['accepted_at'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Meta -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2 text-secondary"></i>Πληροφορίες</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0 small">
                                <dt class="col-sm-5 text-muted">Δημιουργήθηκε</dt>
                                <dd class="col-sm-7"><?= $offer['created_at'] ? date('d/m/Y H:i', strtotime($offer['created_at'])) : '—' ?></dd>

                                <?php if (!empty($offer['created_by_name'])): ?>
                                <dt class="col-sm-5 text-muted">Από</dt>
                                <dd class="col-sm-7"><?= htmlspecialchars($offer['created_by_name']) ?></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
