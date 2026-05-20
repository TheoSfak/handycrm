<?php require_once 'views/includes/header.php'; ?>

<div class="container-fluid">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/uploaded-contracts">Συμφωνητικά</a></li>
      <li class="breadcrumb-item active"><?= htmlspecialchars($contract['customer_name']) ?></li>
    </ol>
  </nav>

  <!-- Alerts -->
  <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle me-2"></i>Οι αλλαγές αποθηκεύτηκαν.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if (isset($uploaded) && $uploaded): ?>
    <div class="alert alert-info alert-dismissible fade show">
      <i class="fas fa-info-circle me-2"></i>
      Το αρχείο ανέβηκε. Πατήστε <strong>«Σάρωση PDF»</strong> για αυτόματη εξαγωγή πεδίων.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row g-4">

    <!-- Left: PDF preview + scan -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
          <h6 class="mb-0"><i class="fas fa-file-pdf text-danger me-2"></i>Αρχείο PDF</h6>
          <a href="<?= BASE_URL ?>/uploaded-contracts/file/<?= $contract['id'] ?>"
             target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-external-link-alt me-1"></i>Άνοιγμα
          </a>
        </div>
        <div class="card-body p-0">
          <iframe src="<?= BASE_URL ?>/uploaded-contracts/file/<?= $contract['id'] ?>#toolbar=1"
                  class="w-100 border-0 rounded-bottom"
                  style="height:420px;" title="PDF Preview"></iframe>
        </div>
      </div>

      <!-- Scan button -->
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <p class="text-muted small mb-3">
            <i class="fas fa-robot me-1"></i>
            Αυτόματη ανάγνωση ημερομηνιών, ποσού, τίτλου κ.ά. από το PDF.
            Μπορείτε να διορθώσετε οποιοδήποτε πεδίο μετά.
          </p>
          <button id="scanBtn" class="btn btn-warning w-100" data-id="<?= $contract['id'] ?>">
            <i class="fas fa-search-plus me-2"></i>Σάρωση PDF
          </button>
          <div id="scanStatus" class="mt-2 small text-muted d-none"></div>
        </div>
      </div>
    </div>

    <!-- Right: editable fields -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
          <h6 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Στοιχεία Συμφωνητικού</h6>
          <small class="text-muted">
            Αρχείο: <?= htmlspecialchars($contract['original_filename']) ?>
          </small>
        </div>
        <div class="card-body">
          <form method="POST" action="<?= BASE_URL ?>/uploaded-contracts/update/<?= $contract['id'] ?>" id="contractForm">
            <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold">Πελάτης <span class="text-danger">*</span></label>
              <input type="text" name="customer_name" id="f_customer_name" class="form-control"
                     value="<?= htmlspecialchars($contract['customer_name']) ?>" required maxlength="255">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Τίτλος / Αντικείμενο</label>
              <input type="text" name="title" id="f_title" class="form-control"
                     value="<?= htmlspecialchars($contract['title'] ?? '') ?>"
                     placeholder="π.χ. Συμφωνητικό Ηλεκτρολογικών Εγκαταστάσεων" maxlength="500">
            </div>

            <div class="row g-3 mb-3">
              <div class="col-sm-4">
                <label class="form-label fw-semibold">Ποσό (€)</label>
                <div class="input-group">
                  <input type="text" name="amount" id="f_amount" class="form-control"
                         value="<?= $contract['amount'] !== null ? number_format((float)$contract['amount'], 2, ',', '.') : '' ?>"
                         placeholder="0,00">
                  <span class="input-group-text">€</span>
                </div>
              </div>
              <div class="col-sm-4">
                <label class="form-label fw-semibold">Ημ. Έναρξης</label>
                <input type="text" name="start_date" id="f_start_date" class="form-control"
                       value="<?= $contract['start_date'] ? date('d/m/Y', strtotime($contract['start_date'])) : '' ?>"
                       placeholder="ηη/μμ/εεεε">
              </div>
              <div class="col-sm-4">
                <label class="form-label fw-semibold">Ημ. Λήξης</label>
                <input type="text" name="end_date" id="f_end_date" class="form-control"
                       value="<?= $contract['end_date'] ? date('d/m/Y', strtotime($contract['end_date'])) : '' ?>"
                       placeholder="ηη/μμ/εεεε">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Περιγραφή Εργασιών</label>
              <textarea name="description" id="f_description" class="form-control" rows="3"
                        placeholder="Σύντομη περιγραφή αντικειμένου συμφωνητικού…"
                        maxlength="2000"><?= htmlspecialchars($contract['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Σημειώσεις</label>
              <textarea name="notes" class="form-control" rows="2"
                        placeholder="Ελεύθερες σημειώσεις…"
                        maxlength="2000"><?= htmlspecialchars($contract['notes'] ?? '') ?></textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Αποθήκευση
              </button>

              <button type="button" class="btn btn-outline-danger" id="deleteBtn">
                <i class="fas fa-trash me-1"></i> Διαγραφή
              </button>
            </div>
          </form>

          <!-- Delete form lives OUTSIDE contractForm to avoid nested-form browser bug -->
          <form method="POST" action="<?= BASE_URL ?>/uploaded-contracts/delete/<?= $contract['id'] ?>"
                id="deleteForm" class="d-none">
            <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
          </form>
        </div>
      </div>

      <!-- Raw extracted text (collapsed) -->
      <?php if (!empty($contract['extracted_text'])): ?>
      <div class="mt-3">
        <a class="small text-muted" data-bs-toggle="collapse" href="#rawText">
          <i class="fas fa-code me-1"></i>Εμφάνιση εξαχθέντος κειμένου PDF
        </a>
        <div class="collapse mt-2" id="rawText">
          <div class="card card-body bg-light border-0 small" style="max-height:300px;overflow-y:auto;white-space:pre-wrap;font-family:monospace;">
            <?= htmlspecialchars(mb_substr($contract['extracted_text'], 0, 5000)) ?>
            <?php if (mb_strlen($contract['extracted_text']) > 5000): ?>…<?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// ── Scan PDF ──────────────────────────────────────────────────────────────
document.getElementById('scanBtn')?.addEventListener('click', function () {
  const btn    = this;
  const id     = btn.dataset.id;
  const status = document.getElementById('scanStatus');

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Σάρωση…';
  status.classList.remove('d-none');
  status.textContent = 'Γίνεται ανάλυση του PDF…';

  fetch('<?= BASE_URL ?>/uploaded-contracts/scan/' + id, { method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest',
               'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'csrf_token=<?= $this->generateCsrfToken() ?>'
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) {
      status.textContent = 'Σφάλμα: ' + (data.message || 'άγνωστο');
      return;
    }
    // Fill fields only if they have a value and the field is currently empty
    function fill(id, val) {
      const el = document.getElementById(id);
      if (el && val && el.value.trim() === '') el.value = val;
    }
    fill('f_title',       data.title);
    fill('f_description', data.description);

    // Amount: format as 1.234,56
    if (data.amount) {
      const amtEl = document.getElementById('f_amount');
      if (amtEl && amtEl.value.trim() === '') {
        const n = parseFloat(data.amount);
        if (!isNaN(n)) amtEl.value = n.toLocaleString('el-GR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }
    }

    // Dates: convert from yyyy-mm-dd to dd/mm/yyyy
    function fillDate(id, val) {
      const el = document.getElementById(id);
      if (el && val && el.value.trim() === '') {
        const parts = val.split('-');
        if (parts.length === 3) el.value = parts[2] + '/' + parts[1] + '/' + parts[0];
      }
    }
    fillDate('f_start_date', data.start_date);
    fillDate('f_end_date',   data.end_date);

    const charCount = data.text_length || 0;
    const strategy  = data.strategy ? ' [' + data.strategy + ']' : '';
    status.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>'
      + 'Σάρωση ολοκληρώθηκε (' + charCount + ' χαρακτήρες εξήχθησαν' + strategy + ').'
      + '</span> Ελέγξτε τα πεδία και αποθηκεύστε.';
    btn.innerHTML = '<i class="fas fa-check me-1"></i>Σαρώθηκε';
    btn.classList.replace('btn-warning', 'btn-success');
  })
  .catch(() => {
    status.textContent = 'Αποτυχία επικοινωνίας με τον server.';
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-search-plus me-2"></i>Σάρωση PDF';
  });
});

// ── Delete confirmation ────────────────────────────────────────────────────
document.getElementById('deleteBtn')?.addEventListener('click', function () {
  if (confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το συμφωνητικό;\nΤο αρχείο PDF θα διαγραφεί οριστικά.')) {
    document.getElementById('deleteForm').submit();
  }
});
</script>

<?php require_once 'views/includes/footer.php'; ?>
