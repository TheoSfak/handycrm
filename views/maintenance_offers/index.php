<?php $pageTitle = 'Προσφορές Συντήρησης'; ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-end align-items-center">
                <a href="<?= BASE_URL ?>/maintenance-offers/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Νέα Προσφορά
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/maintenance-offers" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Αναζήτηση</label>
                    <input type="text" class="form-control" name="search"
                           placeholder="Επωνυμία, αρ. προσφοράς, τηλέφωνο..."
                           value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Αποδοχή</label>
                    <select class="form-select" name="accepted">
                        <option value="">Όλες</option>
                        <option value="1" <?= ($accepted === '1') ? 'selected' : '' ?>>Αποδεκτές</option>
                        <option value="0" <?= ($accepted === '0') ? 'selected' : '' ?>>Εκκρεμείς</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Αναζήτηση
                    </button>
                    <?php if ($search || $accepted !== ''): ?>
                        <a href="<?= BASE_URL ?>/maintenance-offers" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Συνολικά: <?= $totalCount ?> προσφορές</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($offers)): ?>
                <div class="alert alert-info m-3">
                    <i class="fas fa-info-circle"></i>
                    Δεν βρέθηκαν προσφορές<?= ($search || $accepted !== '') ? ' με τα κριτήρια αναζήτησης.' : '.' ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:10%;">Αρ. Προσφοράς</th>
                                <th style="width:18%;">Επωνυμία</th>
                                <th style="width:12%;">Διεύθυνση</th>
                                <th style="width:9%;">Τηλέφωνο</th>
                                <th style="width:6%;" class="text-center">Αρ. Μ/Σ</th>
                                <th style="width:8%;" class="text-center">Τιμή</th>
                                <th style="width:8%;">Λήξη Προσφ.</th>
                                <th style="width:8%;">Απεστάλη</th>
                                <th style="width:9%;" class="text-center">Αποδοχή</th>
                                <th style="width:10%;" class="text-center">Προγραμματίστηκε</th>
                                <th style="width:12%;" class="text-end">Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offers as $offer): ?>
                                <tr id="row-<?= $offer['id'] ?>">
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($offer['offer_number']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($offer['company_name']) ?></strong>
                                        <?php if (!empty($offer['email'])): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($offer['email']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?= htmlspecialchars($offer['address'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($offer['phone'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?= (int)$offer['transformers_count'] ?></span>
                                    </td>
                                    <td class="text-center fw-bold">
                                        <?= number_format((float)$offer['price'], 2, ',', '.') ?> €
                                    </td>
                                    <td>
                                        <?php if ($offer['offer_expiry_date']): ?>
                                            <?php
                                            $expiry = new DateTime($offer['offer_expiry_date']);
                                            $today  = new DateTime();
                                            $expired = $today > $expiry;
                                            ?>
                                            <span class="badge bg-<?= $expired ? 'danger' : 'success' ?>">
                                                <?= date('d/m/Y', strtotime($offer['offer_expiry_date'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= $offer['sent_at'] ? date('d/m/Y', strtotime($offer['sent_at'])) : '-' ?>
                                    </td>

                                    <!-- Αποδοχή: radio button (one-way toggle) -->
                                    <td class="text-center" id="accepted-cell-<?= $offer['id'] ?>">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input accept-radio"
                                                   type="radio"
                                                   name="accept_<?= $offer['id'] ?>"
                                                   id="accept_<?= $offer['id'] ?>"
                                                   data-id="<?= $offer['id'] ?>"
                                                   <?= $offer['accepted'] ? 'checked' : '' ?>
                                                   title="Αποδοχή Προσφοράς">
                                        </div>
                                        <?php if ($offer['accepted_at']): ?>
                                            <div class="text-success small mt-1" id="accepted-ts-<?= $offer['id'] ?>" style="font-size: 0.72rem;">
                                                <?= date('d/m/Y H:i', strtotime($offer['accepted_at'])) ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small mt-1" id="accepted-ts-<?= $offer['id'] ?>" style="font-size: 0.72rem;"></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Προγραμματίστηκε: inline date input + save button -->
                                    <td class="text-center" id="sched-cell-<?= $offer['id'] ?>">
                                        <div class="d-flex align-items-center gap-1 justify-content-center">
                                            <input type="date"
                                                   class="form-control form-control-sm sched-date"
                                                   id="sched-input-<?= $offer['id'] ?>"
                                                   data-id="<?= $offer['id'] ?>"
                                                   value="<?= htmlspecialchars($offer['scheduled_date'] ?? '') ?>"
                                                   style="width:145px;"
                                                   title="Ημερομηνία Προγραμματισμού">
                                            <button class="btn btn-sm btn-outline-secondary sched-save-btn"
                                                    type="button"
                                                    data-id="<?= $offer['id'] ?>"
                                                    title="Αποθήκευση ημερομηνίας">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Export PDF -->
                                            <a href="<?= BASE_URL ?>/maintenance-offers/export-pdf/<?= $offer['id'] ?>"
                                               class="btn btn-outline-danger" title="Λήψη PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <!-- Export Word -->
                                            <a href="<?= BASE_URL ?>/maintenance-offers/export-word/<?= $offer['id'] ?>"
                                               class="btn btn-outline-primary" title="Λήψη Word">
                                                <i class="fas fa-file-word"></i>
                                            </a>
                                            <!-- Send Email -->
                                            <button type="button" class="btn btn-outline-success btn-send-email"
                                                    data-id="<?= $offer['id'] ?>"
                                                    data-email="<?= htmlspecialchars($offer['email'] ?? '') ?>"
                                                    data-company="<?= htmlspecialchars($offer['company_name']) ?>"
                                                    data-offer-number="<?= htmlspecialchars($offer['offer_number']) ?>"
                                                    title="Αποστολή Email">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <!-- Delete -->
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="confirmDelete(<?= $offer['id'] ?>)"
                                                    title="Διαγραφή">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-3 mb-2 px-3">
                        <ul class="pagination justify-content-center mb-0">
                            <?php
                            $qp = [];
                            if ($search)      $qp['search']   = $search;
                            if ($accepted !== '') $qp['accepted'] = $accepted;
                            $qs = http_build_query($qp);
                            $sep = $qs ? '&' : '';
                            ?>
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= BASE_URL ?>/maintenance-offers?page=<?= $currentPage - 1 ?><?= $sep . $qs ?>">Προηγούμενη</a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/maintenance-offers?page=<?= $i ?><?= $sep . $qs ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= BASE_URL ?>/maintenance-offers?page=<?= $currentPage + 1 ?><?= $sep . $qs ?>">Επόμενη</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Επιβεβαίωση Διαγραφής</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή την προσφορά;</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Άκυρο</button>
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-danger">Διαγραφή</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Email modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-envelope me-2"></i>Αποστολή Προσφοράς</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="emailForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Παραλήπτης (Email)</label>
                        <input type="email" class="form-control" name="recipient_email" id="emailRecipient" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Θέμα</label>
                        <input type="text" class="form-control" name="email_subject" id="emailSubject">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Μήνυμα</label>
                        <textarea class="form-control" name="email_message" id="emailMessage" rows="5"></textarea>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-paperclip me-1"></i>
                        Η προσφορά θα επισυναφθεί αυτόματα ως αρχείο Word (εφόσον υπάρχει το πρότυπο).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Άκυρο</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Αποστολή
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const BASE_URL_JS = '<?= BASE_URL ?>';

// ── Accept radio ────────────────────────────────────────────────────────────
document.querySelectorAll('.accept-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        const id = this.dataset.id;
        const checked = this.checked ? 1 : 0;
        this.disabled = true;

        fetch(BASE_URL_JS + '/maintenance-offers/toggle-accepted/' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({accepted: checked})
        })
        .then(r => r.json())
        .then(data => {
            this.disabled = false;
            if (data.success) {
                const ts = document.getElementById('accepted-ts-' + id);
                if (ts) {
                    ts.textContent = data.accepted_at ?? '';
                    ts.className = data.accepted ? 'text-success small mt-1' : 'text-muted small mt-1';
                }
            } else {
                this.checked = !this.checked;
                alert('Σφάλμα: ' + (data.message ?? 'Άγνωστο σφάλμα'));
            }
        })
        .catch(() => {
            this.disabled = false;
            this.checked = !this.checked;
        });
    });
});

// ── Scheduled date ──────────────────────────────────────────────────────────
function saveSchedDate(id) {
    const input = document.getElementById('sched-input-' + id);
    const btn   = document.querySelector('.sched-save-btn[data-id="' + id + '"]');
    const date  = input.value;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(BASE_URL_JS + '/maintenance-offers/save-scheduled-date/' + id, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({scheduled_date: date})
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.replace('btn-outline-secondary', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-save"></i>';
                btn.classList.replace('btn-success', 'btn-outline-secondary');
            }, 2000);
        } else {
            btn.innerHTML = '<i class="fas fa-save"></i>';
            alert('Σφάλμα αποθήκευσης: ' + (data.message ?? 'Άγνωστο σφάλμα'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i>';
        alert('Σφάλμα σύνδεσης κατά την αποθήκευση ημερομηνίας.');
    });
}
document.querySelectorAll('.sched-save-btn').forEach(btn => {
    btn.addEventListener('click', function () { saveSchedDate(this.dataset.id); });
});

// ── Delete confirm ──────────────────────────────────────────────────────────
function confirmDelete(id) {
    const form = document.getElementById('deleteForm');
    form.action = BASE_URL_JS + '/maintenance-offers/delete/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Email modal ─────────────────────────────────────────────────────────────
document.querySelectorAll('.btn-send-email').forEach(btn => {
    btn.addEventListener('click', function () {
        const id          = this.dataset.id;
        const email       = this.dataset.email;
        const company     = this.dataset.company;
        const offerNumber = this.dataset.offerNumber;

        document.getElementById('emailRecipient').value = email;
        document.getElementById('emailSubject').value   = 'Προσφορά Συντήρησης Υποσταθμού - ' + offerNumber;
        document.getElementById('emailMessage').value   =
            'Αγαπητοί,\n\nΣας αποστέλλουμε επισυναπτόμενη την προσφορά μας για τη συντήρηση του υποσταθμού σας.\n\nΜε εκτίμηση,\n';

        const form = document.getElementById('emailForm');
        form.action = BASE_URL_JS + '/maintenance-offers/send-email/' + id;

        new bootstrap.Modal(document.getElementById('emailModal')).show();
    });
});
</script>
