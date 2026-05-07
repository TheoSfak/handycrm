<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice"></i> <?= __('quotes.title') ?> #<?= $quote['quote_number'] ?></h2>
    <div>
        <a href="<?= BASE_URL ?>/quotes/edit/<?= $quote['id'] ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> <?= __('quotes.edit') ?>
        </a>
        <a href="<?= BASE_URL ?>/quotes" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?= __('common.back') ?>
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Quote Status -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2"><?= __('quotes.status') ?></h6>
                        <?php
                            $statusColors = [
                                'draft' => 'secondary',
                                'sent' => 'info',
                                'accepted' => 'success',
                                'rejected' => 'danger',
                                'expired' => 'warning'
                            ];
                            $statusLabels = [
                                'draft' => __('quotes.draft'),
                                'sent' => __('quotes.status_sent'),
                                'accepted' => __('quotes.status_accepted'),
                                'rejected' => __('quotes.status_rejected'),
                                'expired' => __('quotes.expired')
                            ];
                            $badgeColor = $statusColors[$quote['status']] ?? 'secondary';
                            $statusLabel = $statusLabels[$quote['status']] ?? $quote['status'];
                        ?>
                        <span class="badge bg-<?= $badgeColor ?> fs-6"><?= $statusLabel ?></span>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2"><?= __('quotes.created_date') ?></h6>
                        <p class="mb-0"><?= date('d/m/Y', strtotime($quote['created_at'])) ?></p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2"><?= __('quotes.valid_until') ?></h6>
                        <p class="mb-0"><?= date('d/m/Y', strtotime($quote['valid_until'])) ?></p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2"><?= __('quotes.created_by') ?></h6>
                        <p class="mb-0"><?= htmlspecialchars(trim(($quote['creator_first_name'] ?? '') . ' ' . ($quote['creator_last_name'] ?? '')) ?: '—') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quote Details -->
<div class="row">
    <!-- Left Column -->
    <div class="col-md-8">
        <!-- Basic Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('quotes.quote_details') ?></h5>
            </div>
            <div class="card-body">
                <h4><?= htmlspecialchars($quote['title']) ?></h4>
                <?php if (!empty($quote['description'])): ?>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($quote['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('quotes.customer_details') ?></h5>
            </div>
            <div class="card-body">
                <h6>
                    <?php
                        $customerName = $quote['customer_type'] === 'company' && !empty($quote['customer_company_name']) 
                            ? $quote['customer_company_name'] 
                            : $quote['customer_first_name'] . ' ' . $quote['customer_last_name'];
                        echo htmlspecialchars($customerName);
                    ?>
                </h6>
                <?php if (!empty($quote['customer_phone'])): ?>
                    <p class="mb-1"><i class="fas fa-phone"></i> <?= htmlspecialchars($quote['customer_phone']) ?></p>
                <?php endif; ?>
                <?php if (!empty($quote['customer_email'])): ?>
                    <p class="mb-1"><i class="fas fa-envelope"></i> <?= htmlspecialchars($quote['customer_email']) ?></p>
                <?php endif; ?>
                <?php if (!empty($quote['customer_address'])): ?>
                    <p class="mb-0"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($quote['customer_address']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quote Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('quotes.quote_items') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50%"><?= __('quotes.item_description') ?></th>
                                <th width="15%" class="text-center"><?= __('quotes.quantity') ?></th>
                                <th width="17.5%" class="text-end"><?= __('quotes.unit_price') ?></th>
                                <th width="17.5%" class="text-end"><?= __('quotes.total') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($quote['items'])): ?>
                                <?php foreach ($quote['items'] as $item): ?>
                                    <tr>
                                        <td><?= nl2br(htmlspecialchars($item['description'])) ?></td>
                                        <td class="text-center"><?= number_format($item['quantity'], 2) ?></td>
                                        <td class="text-end"><?= number_format($item['unit_price'], 2) ?>€</td>
                                        <td class="text-end"><?= number_format($item['total_price'], 2) ?>€</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted"><?= __('quotes.no_items') ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Notes and Terms -->
        <?php if (!empty($quote['notes']) || !empty($quote['terms'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><?= __('quotes.notes_terms') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($quote['notes'])): ?>
                    <h6><?= __('quotes.notes') ?></h6>
                    <p><?= nl2br(htmlspecialchars($quote['notes'])) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($quote['terms_conditions'])): ?>
                    <h6 class="mt-3"><?= __('quotes.terms_conditions') ?></h6>
                    <p><?= nl2br(htmlspecialchars($quote['terms_conditions'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column - Summary -->
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header">
                <h5 class="mb-0"><?= __('quotes.summary') ?></h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span><?= __('quotes.subtotal') ?>:</span>
                    <strong><?= number_format($quote['subtotal'], 2) ?>€</strong>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span><?= __('quotes.vat') ?> (<?= number_format($quote['vat_rate'], 0) ?>%):</span>
                    <strong><?= number_format($quote['vat_amount'], 2) ?>€</strong>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="h5"><?= __('quotes.total') ?>:</span>
                    <strong class="h5"><?= number_format($quote['total_amount'], 2) ?>€</strong>
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/quotes/export-pdf?id=<?= $quote['id'] ?>" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> <?= __('quotes.export_pdf') ?>
                    </a>

                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#sendEmailModal">
                        <i class="fas fa-envelope"></i> Αποστολή με Email
                    </button>
                    
                    <a href="<?= BASE_URL ?>/quotes/edit/<?= $quote['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> <?= __('quotes.edit') ?>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/quotes/delete/<?= $quote['id'] ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('<?= __('quotes.confirm_delete') ?>')">
                        <i class="fas fa-trash"></i> <?= __('quotes.delete') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportToPDF() {
    alert('<?= __('quotes.pdf_coming_soon') ?>');
}

// Print styles
window.onbeforeprint = function() {
    document.querySelectorAll('.btn, .card-header').forEach(el => {
        el.style.display = 'none';
    });
};

window.onafterprint = function() {
    document.querySelectorAll('.btn, .card-header').forEach(el => {
        el.style.display = '';
    });
};
</script>

<!-- Send by Email Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendEmailModalLabel">
          <i class="fas fa-envelope me-2"></i>Αποστολή Προσφοράς με Email
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="sendEmailAlert" class="alert d-none mb-3"></div>
        <form id="sendEmailForm">
          <input type="hidden" name="id" value="<?= (int)$quote['id'] ?>">
          <div class="mb-3">
            <label for="sendToEmail" class="form-label fw-semibold">Email Παραλήπτη <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="sendToEmail" name="to_email"
                   value="<?= htmlspecialchars($quote['customer_email'] ?? '') ?>"
                   placeholder="email@example.com" required>
            <div class="form-text">Προ-συμπληρώθηκε από τα στοιχεία πελάτη, εφόσον υπάρχει.</div>
          </div>
          <div class="mb-3">
            <label for="sendCustomMessage" class="form-label fw-semibold">Επιπλέον μήνυμα <span class="text-muted fw-normal">(προαιρετικό)</span></label>
            <textarea class="form-control" id="sendCustomMessage" name="custom_message" rows="4"
                      placeholder="π.χ. Σας αποστέλλουμε την προσφορά μας μετά από την τηλεφωνική μας επικοινωνία..."></textarea>
          </div>
          <div class="alert alert-info py-2 px-3 mb-0" style="font-size:0.875rem;">
            <i class="fas fa-paperclip me-1"></i>Το PDF της προσφοράς θα επισυναφθεί αυτόματα.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Άκυρο</button>
        <button type="button" class="btn btn-success" id="sendEmailBtn" onclick="submitSendEmail()">
          <span id="sendEmailSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
          <i class="fas fa-paper-plane me-1" id="sendEmailIcon"></i>Αποστολή
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function submitSendEmail() {
    const form    = document.getElementById('sendEmailForm');
    const alert   = document.getElementById('sendEmailAlert');
    const btn     = document.getElementById('sendEmailBtn');
    const spinner = document.getElementById('sendEmailSpinner');
    const icon    = document.getElementById('sendEmailIcon');
    const email   = document.getElementById('sendToEmail').value.trim();

    if (!email) {
        showEmailAlert('danger', 'Παρακαλώ εισάγετε διεύθυνση email.');
        return;
    }

    // Loading state
    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    alert.classList.add('d-none');

    const data = new FormData(form);

    fetch('<?= BASE_URL ?>/quotes/send-email', {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showEmailAlert('success', '<i class="fas fa-check-circle me-1"></i>' + res.message);
            // Close modal after 2s on success
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('sendEmailModal')).hide();
                alert.classList.add('d-none');
            }, 2500);
        } else {
            showEmailAlert('danger', '<i class="fas fa-exclamation-circle me-1"></i>' + res.error);
        }
    })
    .catch(() => showEmailAlert('danger', 'Παρουσιάστηκε σφάλμα. Παρακαλώ δοκιμάστε ξανά.'))
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
    });
}

function showEmailAlert(type, msg) {
    const el = document.getElementById('sendEmailAlert');
    el.className = 'alert alert-' + type + ' mb-3';
    el.innerHTML = msg;
    el.classList.remove('d-none');
}

// Reset modal state when it's closed
document.getElementById('sendEmailModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('sendEmailAlert').classList.add('d-none');
});
</script>

<style>
@media print {
    .btn, .card-header, nav, footer {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
