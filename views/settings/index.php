<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-cog"></i> Ρυθμίσεις Συστήματος</h2>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-10 mx-auto">
        <form method="POST" action="?route=/settings">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
            
            <!-- Company Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building"></i> Στοιχεία Εταιρείας</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Επωνυμία Εταιρείας</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" 
                               value="<?= htmlspecialchars($settings['company_name']) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_address" class="form-label">Διεύθυνση</label>
                        <input type="text" class="form-control" id="company_address" name="company_address" 
                               value="<?= htmlspecialchars($settings['company_address']) ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="company_phone" class="form-label">Τηλέφωνο</label>
                                <input type="text" class="form-control" id="company_phone" name="company_phone" 
                                       value="<?= htmlspecialchars($settings['company_phone']) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="company_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="company_email" name="company_email" 
                                       value="<?= htmlspecialchars($settings['company_email']) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="company_tax_id" class="form-label">ΑΦΜ</label>
                                <input type="text" class="form-control" id="company_tax_id" name="company_tax_id" 
                                       value="<?= htmlspecialchars($settings['company_tax_id']) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="company_website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="company_website" name="company_website" 
                               value="<?= htmlspecialchars($settings['company_website']) ?>">
                    </div>
                </div>
            </div>
            
            <!-- Financial Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator"></i> Οικονομικές Ρυθμίσεις</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="default_vat_rate" class="form-label">Προεπιλεγμένος ΦΠΑ (%)</label>
                                <input type="number" class="form-control" id="default_vat_rate" name="default_vat_rate" 
                                       value="<?= htmlspecialchars($settings['default_vat_rate']) ?>" 
                                       min="0" max="100" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Νόμισμα</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="EUR" <?= $settings['currency'] === 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                                    <option value="USD" <?= $settings['currency'] === 'USD' ? 'selected' : '' ?>>USD (Dollar)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="currency_symbol" class="form-label">Σύμβολο Νομίσματος</label>
                                <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" 
                                       value="<?= htmlspecialchars($settings['currency_symbol']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sliders-h"></i> Ρυθμίσεις Συστήματος</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_format" class="form-label">Μορφή Ημερομηνίας</label>
                                <select class="form-select" id="date_format" name="date_format">
                                    <option value="d/m/Y" <?= $settings['date_format'] === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY (Ελληνική)</option>
                                    <option value="Y-m-d" <?= $settings['date_format'] === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD (ISO)</option>
                                    <option value="m/d/Y" <?= $settings['date_format'] === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY (US)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="items_per_page" class="form-label">Αποτελέσματα ανά Σελίδα</label>
                                <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                                       value="<?= htmlspecialchars($settings['items_per_page']) ?>" 
                                       min="10" max="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Αποθήκευση Ρυθμίσεων
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Danger Zone -->
        <div class="card border-danger mt-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Επικίνδυνη Ζώνη</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1"><strong>Μηδενισμός Δεδομένων (Reset Database)</strong></h6>
                        <p class="text-muted mb-0">
                            Διαγράφει ΟΛΟΥΣ τους πελάτες, έργα, τιμολόγια, προσφορές, ραντεβού κτλ. 
                            Κρατάει μόνο users και ρυθμίσεις.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="<?= BASE_URL ?>/settings/reset-data" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> Μηδενισμός Βάσης
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-link"></i> Γρήγοροι Σύνδεσμοι</h6>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/settings/update" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt"></i> Ενημερώσεις Συστήματος
                    </a>
                    <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary">
                        <i class="fas fa-users"></i> Διαχείριση Χρηστών
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
