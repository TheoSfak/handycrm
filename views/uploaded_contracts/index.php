<?php require_once 'views/includes/header.php'; ?>

<div class="container-fluid">

  <!-- Header row -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h2 class="mb-0"><i class="fas fa-file-signature me-2 text-primary"></i>Συμφωνητικά</h2>
      <p class="text-muted mb-0 mt-1">Ανέβασμα &amp; διαχείριση υπογεγραμμένων συμφωνητικών</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
      <i class="fas fa-upload me-1"></i> Ανέβασμα Συμφωνητικού
    </button>
  </div>

  <!-- Alerts -->
  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle me-2"></i>Το συμφωνητικό διαγράφηκε.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php elseif (isset($_GET['error'])): ?>
    <?php $errors = [
      'missing_customer' => 'Παρακαλώ συμπληρώστε το όνομα πελάτη.',
      'no_file'          => 'Δεν επιλέξατε αρχείο.',
      'not_pdf'          => 'Το αρχείο πρέπει να είναι PDF.',
      'file_too_large'   => 'Το αρχείο υπερβαίνει τα 20 MB.',
      'upload_failed'    => 'Αποτυχία αποθήκευσης αρχείου.',
      'not_found'        => 'Το συμφωνητικό δεν βρέθηκε.',
    ]; ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="fas fa-exclamation-circle me-2"></i>
      <?= htmlspecialchars($errors[$_GET['error']] ?? 'Άγνωστο σφάλμα.') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Search -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
      <form method="GET" action="<?= BASE_URL ?>/uploaded-contracts" class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label small text-muted mb-1">Αναζήτηση</label>
          <input type="text" name="search" class="form-control"
                 placeholder="Πελάτης, τίτλος, όνομα αρχείου…"
                 value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-search me-1"></i> Αναζήτηση
          </button>
          <?php if ($search): ?>
          <a href="<?= BASE_URL ?>/uploaded-contracts" class="btn btn-outline-secondary ms-1">
            <i class="fas fa-times"></i>
          </a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <?php if (empty($contracts)): ?>
        <div class="text-center py-5 text-muted">
          <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
          <p class="mb-0">Δεν υπάρχουν συμφωνητικά<?= $search ? ' που να ταιριάζουν στην αναζήτηση' : '' ?>.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Πελάτης</th>
                <th>Τίτλος</th>
                <th>Ποσό €</th>
                <th>Έναρξη</th>
                <th>Λήξη</th>
                <th>Αρχείο</th>
                <th>Καταχωρήθηκε</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($contracts as $c): ?>
              <?php
                $daysLeft = $c['end_date'] ? (int)floor((strtotime($c['end_date']) - time()) / 86400) : null;
              ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($c['customer_name']) ?></td>
                <td class="text-muted small"><?= htmlspecialchars($c['title'] ?: '—') ?></td>
                <td>
                  <?= $c['amount'] !== null ? number_format((float)$c['amount'], 2, ',', '.') . ' €' : '—' ?>
                </td>
                <td><?= $c['start_date'] ? date('d/m/Y', strtotime($c['start_date'])) : '—' ?></td>
                <td>
                  <?php if ($c['end_date']): ?>
                    <?= date('d/m/Y', strtotime($c['end_date'])) ?>
                    <?php if ($daysLeft !== null && $daysLeft <= 30): ?>
                      <?php if ($daysLeft < 0): ?>
                        <span class="badge bg-danger ms-1">Έληξε</span>
                      <?php elseif ($daysLeft === 0): ?>
                        <span class="badge bg-danger ms-1">Σήμερα!</span>
                      <?php else: ?>
                        <span class="badge bg-warning text-dark ms-1"><?= $daysLeft ?> ημ.</span>
                      <?php endif; ?>
                    <?php endif; ?>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                  <a href="<?= BASE_URL ?>/<?= htmlspecialchars($c['file_path']) ?>"
                     target="_blank" class="btn btn-sm btn-outline-secondary" title="Άνοιγμα PDF">
                    <i class="fas fa-file-pdf text-danger"></i>
                    <span class="d-none d-md-inline ms-1 small"><?= htmlspecialchars(mb_substr($c['original_filename'], 0, 30)) ?></span>
                  </a>
                </td>
                <td class="small text-muted"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                <td class="text-end">
                  <a href="<?= BASE_URL ?>/uploaded-contracts/show/<?= $c['id'] ?>"
                     class="btn btn-sm btn-outline-primary" title="Προβολή / Επεξεργασία">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
          <small class="text-muted">Εμφανίζονται <?= count($contracts) ?> από <?= $total ?> συμφωνητικά</small>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <?php for ($p = 1; $p <= $pages; $p++): ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= BASE_URL ?>/uploaded-contracts?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
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

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= BASE_URL ?>/uploaded-contracts/store" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $this->generateCsrfToken() ?>">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Ανέβασμα Συμφωνητικού</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Όνομα Πελάτη <span class="text-danger">*</span></label>
            <input type="text" name="customer_name" class="form-control"
                   placeholder="π.χ. ΑΒΕΕ Παπαδόπουλος" required maxlength="255">
            <div class="form-text">Ελεύθερο κείμενο — δεν χρειάζεται να υπάρχει στο σύστημα.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Αρχείο PDF <span class="text-danger">*</span></label>
            <input type="file" name="contract_file" class="form-control" accept=".pdf" required>
            <div class="form-text">Μέγιστο μέγεθος: 20 MB</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Άκυρο</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload me-1"></i> Ανέβασμα
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once 'views/includes/footer.php'; ?>
