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
                            <div class="col-md-12">
                                <label class="form-label">
                                    Τίτλος / Σενάριο Προσφοράς <small class="text-muted fw-normal">(προαιρετικό - αν μείνει κενό, παράγεται αυτόματα από την επωνυμία και τους Μ/Σ)</small>
                                </label>
                                <input type="text" class="form-control" name="title"
                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                       placeholder="π.χ. Προσφορά ΣΥΝΤΗΡΗΣΗΣ Υ/Σ Μέσης Τάσης στο Cretan Village (1Μ/Σ)">
                                <div class="form-text">Εμφανίζεται σε 3 σημεία στο PDF: Εξώφυλλο (Σενάριο), Συνοδευτική Επιστολή & Οικονομικοί Όροι.</div>
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
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Αριθμός Μετασχηματιστών <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="transformers_count"
                                       id="transformersCount"
                                       value="<?= (int)($_POST['transformers_count'] ?? 1) ?>"
                                       min="1" max="99" required>
                                <div class="form-text">1 Μ/Σ = 400€ | 2 = 600€...</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ισχύς (kVA)</label>
                                <input type="text" class="form-control" name="transformer_power"
                                       value="<?= htmlspecialchars($_POST['transformer_power'] ?? '') ?>"
                                       placeholder="π.χ. 630 kVA ή 500 kVA & 630 kVA">
                                <div class="form-text">Εμφανίζεται στο εξώφυλλο (Ισχύς)</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Τιμή Συντήρησης (€)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="custom_price"
                                           id="customPrice"
                                           value="<?= htmlspecialchars($_POST['custom_price'] ?? '') ?>"
                                           min="0" step="0.01"
                                           placeholder="Αυτόματη">
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="form-text">Αυτόματη βάσει Μ/Σ αν κενό</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Λήξη Προσφοράς</label>
                                <input type="date" class="form-control" name="offer_expiry_date"
                                       value="<?= htmlspecialchars($_POST['offer_expiry_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                            </div>
                        </div>

                        <!-- Δεύτερη / Επιπλέον Υπηρεσία (Προαιρετικό) -->
                        <h6 class="text-primary mb-3 border-bottom pb-2">
                            <i class="fas fa-plus-circle me-1"></i> Δεύτερη / Επιπλέον Υπηρεσία <small class="text-muted fw-normal">(προαιρετικό - π.χ. καθαρισμός κυψέλης Χ.Τ.)</small>
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Περιγραφή Υπηρεσίας</label>
                                <input type="text" class="form-control" name="extra_service_name" id="extraServiceName"
                                       value="<?= htmlspecialchars($_POST['extra_service_name'] ?? '') ?>"
                                       placeholder="π.χ. Καθαρισμός Κυψέλης Χαμηλής Τάσης (Χ.Τ.)">
                                <div class="form-text">Εμφανίζεται ως 2η γραμμή στον πίνακα της 6ης σελίδας.</div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Ποσότητα</label>
                                <input type="number" class="form-control" name="extra_service_qty" id="extraServiceQty"
                                       value="<?= (int)($_POST['extra_service_qty'] ?? 1) ?>"
                                       min="1" max="999">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Τιμή Μονάδας χωρίς Φ.Π.Α. (€)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="extra_service_price" id="extraServicePrice"
                                           value="<?= htmlspecialchars($_POST['extra_service_price'] ?? '') ?>"
                                           min="0" step="0.01"
                                           placeholder="π.χ. 250">
                                    <span class="input-group-text">€</span>
                                </div>
                                <div class="form-text">Αφήστε κενό αν δεν υπάρχει επιπλέον υπηρεσία</div>
                            </div>
                        </div>

                        <!-- Calculated price preview -->
                        <div class="alert alert-light border mb-4" id="pricePreview">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-calculator me-2 text-primary fs-5"></i>
                                <span class="fw-bold">Οικονομική Σύνοψη Προσφοράς</span>
                            </div>
                            <div id="pricePreviewContent" class="ms-4 small">
                                <!-- Populated dynamically by JS -->
                            </div>
                        </div>

                        <!-- Economic Terms -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Οικονομικοί Όροι <small class="text-muted fw-normal">(προαιρετικό - εμφανίζεται στην τελευταία σελίδα του PDF)</small>
                            </label>
                            <textarea class="form-control" name="terms" rows="3"
                                      placeholder="Ειδικοί όροι πληρωμής, παραδόσεων κ.λπ..."><?= htmlspecialchars($_POST['terms'] ?? '') ?></textarea>
                            <div class="form-text">Εμφανίζεται στην 6η σελίδα (Οικονομική Προσφορά) κάτω από τον τίτλο της προσφοράς.</div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Σημειώσεις / Παρατηρήσεις</label>
                            <textarea class="form-control" name="notes" rows="3"
                                      placeholder="Τυχόν παρατηρήσεις..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
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
    const displayPrice = (custom !== null && !isNaN(custom)) ? custom : autoPrice;

    // Extra service calculation
    const extraNameElem  = document.getElementById('extraServiceName');
    const extraQtyElem   = document.getElementById('extraServiceQty');
    const extraPriceElem = document.getElementById('extraServicePrice');

    const extraName   = extraNameElem ? extraNameElem.value.trim() : '';
    const extraQty    = extraQtyElem ? (parseInt(extraQtyElem.value) || 1) : 1;
    const extraRaw    = extraPriceElem ? extraPriceElem.value.trim() : '';
    const extraPrice  = extraRaw !== '' ? parseFloat(extraRaw) : 0;
    const hasExtra    = (extraName !== '' || extraPrice > 0) && !isNaN(extraPrice) && extraPrice > 0;
    const extraTotal  = hasExtra ? (extraPrice * Math.max(1, extraQty)) : 0;

    const subtotal    = displayPrice + extraTotal;
    const vat         = subtotal * 0.24;
    const grandTotal  = subtotal + vat;

    let lines = [];
    let baseNote = (custom !== null && !isNaN(custom)) ? '(χειροκίνητη τιμή)' : '(αυτόματη βάσει ' + count + ' Μ/Σ)';
    lines.push('<div>&bull; Συντήρηση Υ/Σ (' + count + ' Μ/Σ): <strong>' + formatEuro(displayPrice) + '</strong> <span class="text-muted">' + baseNote + '</span></div>');

    if (hasExtra) {
        let label = extraName || 'Επιπλέον Υπηρεσία';
        let qtyText = extraQty > 1 ? ' (' + extraQty + ' &times; ' + formatEuro(extraPrice) + ')' : '';
        lines.push('<div>&bull; ' + label + qtyText + ': <strong>' + formatEuro(extraTotal) + '</strong></div>');
        lines.push('<div class="mt-1 pt-1 border-top fw-bold text-dark">&bull; Σύνολο Καθαρής Αξίας: ' + formatEuro(subtotal) + '</div>');
    }

    lines.push('<div>&bull; Φ.Π.Α. (24%): <span class="text-muted">' + formatEuro(vat) + '</span></div>');
    lines.push('<div class="mt-1 text-success fw-bold fs-6">&bull; Τελικό Σύνολο με Φ.Π.Α.: ' + formatEuro(grandTotal) + '</div>');

    document.getElementById('pricePreviewContent').innerHTML = lines.join('');
}

document.getElementById('transformersCount').addEventListener('input', updatePricePreview);
document.getElementById('customPrice').addEventListener('input', updatePricePreview);
document.getElementById('extraServiceName').addEventListener('input', updatePricePreview);
document.getElementById('extraServiceQty').addEventListener('input', updatePricePreview);
document.getElementById('extraServicePrice').addEventListener('input', updatePricePreview);

// Init on load
updatePricePreview();
</script>
