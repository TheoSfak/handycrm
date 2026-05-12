<?php $pageTitle = 'Νέα Προσφορά Συντήρησης'; ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <i class="fas fa-file-contract me-2 text-primary"></i>
                    <h5 class="mb-0">Νέα Προσφορά Συντήρησης Υποσταθμού</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/maintenance-offers/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <!-- Στοιχεία Επιχείρησης -->
                        <h6 class="text-primary mb-3 mt-1 border-bottom pb-2">
                            <i class="fas fa-building me-1"></i> Στοιχεία Επιχείρησης
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Επωνυμία Επιχείρησης <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="company_name"
                                       value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>"
                                       placeholder="π.χ. ABC Ενεργειακή Α.Ε." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Διεύθυνση</label>
                                <input type="text" class="form-control" name="address"
                                       value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
                                       placeholder="Οδός, Αριθμός, ΤΚ, Πόλη">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Τηλέφωνο</label>
                                <input type="text" class="form-control" name="phone"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                       placeholder="210 0000000">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Email Αποστολής</label>
                                <input type="email" class="form-control" name="email"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="info@company.gr">
                            </div>
                        </div>

                        <!-- Στοιχεία Προσφοράς -->
                        <h6 class="text-primary mb-3 border-bottom pb-2">
                            <i class="fas fa-bolt me-1"></i> Στοιχεία Προσφοράς
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Αριθμός Μετασχηματιστών <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="transformers_count"
                                       id="transformersCount"
                                       value="<?= (int)($_POST['transformers_count'] ?? 1) ?>"
                                       min="1" max="99" required>
                                <div class="form-text">1 Μ/Σ = 400€ | 2 = 600€ | 3 = 900€ | 4+ = 1.200€ (+300€/επιπλέον)</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Τιμή (€)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="custom_price"
                                           id="customPrice"
                                           value="<?= htmlspecialchars($_POST['custom_price'] ?? '') ?>"
                                           min="0" step="0.01"
                                           placeholder="Αυτόματη">
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="form-text">Αφήστε κενό για αυτόματη τιμή βάσει αρ. Μ/Σ</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Λήξη Προσφοράς</label>
                                <input type="date" class="form-control" name="offer_expiry_date"
                                       value="<?= htmlspecialchars($_POST['offer_expiry_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                            </div>
                        </div>

                        <!-- Calculated price preview -->
                        <div class="alert alert-light border mb-4" id="pricePreview">
                            <i class="fas fa-calculator me-1 text-primary"></i>
                            Υπολογιζόμενη τιμή: <strong id="calcPrice">400,00 €</strong>
                            <span class="text-muted small ms-2" id="customPriceNote"></span>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Σημειώσεις</label>
                            <textarea class="form-control" name="notes" rows="3"
                                      placeholder="Τυχόν ειδικοί όροι ή παρατηρήσεις..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Αποθήκευση Προσφοράς
                            </button>
                            <a href="<?= BASE_URL ?>/maintenance-offers" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Πίσω
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const PRICES = {1: 400, 2: 600, 3: 900, 4: 1200};

function getAutoPrice(count) {
    if (count <= 0) return 400;
    if (count <= 4) return PRICES[count];
    return 1200 + (count - 4) * 300;
}

function formatEuro(val) {
    return val.toLocaleString('el-GR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
}

function updatePricePreview() {
    const count       = parseInt(document.getElementById('transformersCount').value) || 1;
    const customRaw   = document.getElementById('customPrice').value.trim();
    const custom      = customRaw !== '' ? parseFloat(customRaw) : null;
    const autoPrice   = getAutoPrice(count);
    const displayPrice = custom !== null && !isNaN(custom) ? custom : autoPrice;

    document.getElementById('calcPrice').textContent = formatEuro(displayPrice);
    document.getElementById('customPriceNote').textContent =
        (custom !== null && !isNaN(custom)) ? '(custom τιμή)' : '(βάσει ' + count + ' Μ/Σ)';
}

document.getElementById('transformersCount').addEventListener('input', updatePricePreview);
document.getElementById('customPrice').addEventListener('input', updatePricePreview);

// Init on load
updatePricePreview();
</script>
