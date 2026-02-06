<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-cog"></i> <?= __('settings.title') ?></h2>
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
    <div class="col-md-11 mx-auto">
        <form method="POST" action="?route=/settings" enctype="multipart/form-data" id="settingsForm">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
            
            <!-- Bootstrap Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab">
                        <i class="fas fa-building"></i> <?= __('settings.company_info') ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" role="tab">
                        <i class="fas fa-calculator"></i> <?= __('settings.financial_settings') ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                        <i class="fas fa-sliders-h"></i> <?= __('settings.system_settings') ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced" type="button" role="tab">
                        <i class="fas fa-tools"></i> <?= __('settings.advanced') ?? 'Προχωρημένα' ?>
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="settingsTabContent">
                
                <!-- Company Tab -->
                <div class="tab-pane fade show active" id="company" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <!-- Company Logo -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-image"></i> <?= __('settings.company_logo') ?? 'Λογότυπο Εταιρείας' ?>
                                </label>
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <?php if (!empty($settings['company_logo'])): ?>
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($settings['company_logo']) ?>" 
                                                 alt="Company Logo" 
                                                 class="img-thumbnail" 
                                                 id="logoPreview"
                                                 style="max-height: 150px; width: auto;">
                                        <?php else: ?>
                                            <div class="border rounded p-4 text-center bg-light" id="logoPreview">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                                <p class="text-muted mt-2 mb-0 small">
                                                    <?= __('settings.no_logo') ?? 'Δεν έχει ανέβει λογότυπο' ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="file" 
                                               class="form-control mb-2" 
                                               id="company_logo" 
                                               name="company_logo" 
                                               accept="image/png,image/jpeg,image/jpg,image/gif,image/webp"
                                               onchange="previewLogo(this)">
                                        <small class="text-muted">
                                            <?= __('settings.logo_requirements') ?? 'Επιτρεπόμενοι τύποι: PNG, JPG, GIF, WebP. Μέγιστο μέγεθος: 2MB. Συνιστώμενες διαστάσεις: 300x100px' ?>
                                        </small>
                                        <?php if (!empty($settings['company_logo'])): ?>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeLogo()">
                                                    <i class="fas fa-trash"></i> <?= __('settings.remove_logo') ?? 'Διαγραφή Λογότυπου' ?>
                                                </button>
                                                <input type="hidden" name="remove_logo" id="remove_logo" value="0">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="company_name" class="form-label"><?= __('settings.company_name') ?></label>
                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                       value="<?= htmlspecialchars($settings['company_name']) ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="company_address" class="form-label"><?= __('settings.company_address') ?></label>
                                <input type="text" class="form-control" id="company_address" name="company_address" 
                                       value="<?= htmlspecialchars($settings['company_address']) ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="company_phone" class="form-label"><?= __('settings.company_phone') ?></label>
                                        <input type="text" class="form-control" id="company_phone" name="company_phone" 
                                               value="<?= htmlspecialchars($settings['company_phone']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="company_email" class="form-label"><?= __('settings.company_email') ?></label>
                                        <input type="email" class="form-control" id="company_email" name="company_email" 
                                               value="<?= htmlspecialchars($settings['company_email']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="company_tax_id" class="form-label"><?= __('settings.company_tax_id') ?></label>
                                        <input type="text" class="form-control" id="company_tax_id" name="company_tax_id" 
                                               value="<?= htmlspecialchars($settings['company_tax_id']) ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="company_website" class="form-label"><?= __('settings.company_website') ?></label>
                                <input type="url" class="form-control" id="company_website" name="company_website" 
                                       value="<?= htmlspecialchars($settings['company_website']) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Tab -->
                <div class="tab-pane fade" id="financial" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="default_vat_rate" class="form-label"><?= __('settings.default_vat') ?></label>
                                        <input type="number" class="form-control" id="default_vat_rate" name="default_vat_rate" 
                                               value="<?= htmlspecialchars($settings['default_vat_rate']) ?>" 
                                               min="0" max="100" step="0.01">
                                        <small class="form-text text-muted">Π.χ. 24 για 24% ΦΠΑ</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="currency" class="form-label"><?= __('settings.currency') ?></label>
                                        <select class="form-select" id="currency" name="currency">
                                            <option value="EUR" <?= $settings['currency'] === 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                                            <option value="USD" <?= $settings['currency'] === 'USD' ? 'selected' : '' ?>>USD (Dollar)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="currency_symbol" class="form-label"><?= __('settings.currency_symbol') ?></label>
                                        <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" 
                                               value="<?= htmlspecialchars($settings['currency_symbol']) ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- VAT Display Settings -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Ρυθμίσεις Εμφάνισης ΦΠΑ</strong>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="prices_include_vat" 
                                               name="prices_include_vat" value="1"
                                               <?= ($settings['prices_include_vat'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="prices_include_vat">
                                            <strong>Οι τιμές περιλαμβάνουν ΦΠΑ</strong>
                                        </label>
                                        <br>
                                        <small class="text-muted">
                                            Ενεργοποίησε αν οι τιμές που εισάγεις περιλαμβάνουν ήδη ΦΠΑ
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="display_vat_notes" 
                                               name="display_vat_notes" value="1"
                                               <?= ($settings['display_vat_notes'] ?? '1') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="display_vat_notes">
                                            <strong>Εμφάνιση σημειώσεων ΦΠΑ</strong>
                                        </label>
                                        <br>
                                        <small class="text-muted">
                                            Εμφανίζει "(χωρίς ΦΠΑ)" ή "(με ΦΠΑ)" δίπλα στις τιμές
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <strong>Πώς λειτουργεί:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Αν οι τιμές σου είναι <strong>χωρίς ΦΠΑ</strong>: Κράτα το "Οι τιμές περιλαμβάνουν ΦΠΑ" <u>απενεργοποιημένο</u></li>
                                            <li>Αν οι τιμές σου είναι <strong>με ΦΠΑ</strong>: Ενεργοποίησε το "Οι τιμές περιλαμβάνουν ΦΠΑ"</li>
                                            <li>Η ενεργοποίηση "Εμφάνιση σημειώσεων ΦΠΑ" θα δείχνει την ένδειξη σε όλο το σύστημα</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Preview -->
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-header">
                                            <h6 class="mb-0"><i class="fas fa-eye"></i> Προεπισκόπηση</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-2"><strong>Πώς θα φαίνονται οι τιμές:</strong></p>
                                            <div id="vat-preview" class="border p-3 bg-white">
                                                <span class="fs-5" id="preview-price">100,00 €</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- System Tab -->
                <div class="tab-pane fade" id="system" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <!-- Language Settings -->
                            <div class="mb-4 pb-4 border-bottom">
                                <h5 class="mb-3"><i class="fas fa-language"></i> <?= __('settings.language_section') ?></h5>
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <label for="languageSelect" class="form-label"><?= __('settings.language_select_label') ?></label>
                                        <select class="form-select form-select-lg" id="languageSelect" onchange="changeLanguage(this.value)">
                                            <?php
                                            $currentLang = $_SESSION['language'] ?? 'el';
                                            $availableLanguages = $lang->getAvailableLanguages();
                                            foreach ($availableLanguages as $code => $name):
                                            ?>
                                                <option value="<?= $code ?>" <?= $currentLang === $code ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted"><?= __('settings.language_changes_immediate') ?></small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <a href="<?= BASE_URL ?>/settings/translations" class="btn btn-outline-primary">
                                            <i class="fas fa-globe"></i> <?= __('settings.manage_translations_btn') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Date Format & Items Per Page -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_format" class="form-label"><?= __('settings.date_format') ?></label>
                                        <select class="form-select" id="date_format" name="date_format">
                                            <option value="d/m/Y" <?= $settings['date_format'] === 'd/m/Y' ? 'selected' : '' ?>><?= __('settings.date_format_greek') ?></option>
                                            <option value="Y-m-d" <?= $settings['date_format'] === 'Y-m-d' ? 'selected' : '' ?>><?= __('settings.date_format_iso') ?></option>
                                            <option value="m/d/Y" <?= $settings['date_format'] === 'm/d/Y' ? 'selected' : '' ?>><?= __('settings.date_format_us') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="items_per_page" class="form-label"><?= __('settings.items_per_page') ?></label>
                                        <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                                               value="<?= htmlspecialchars($settings['items_per_page']) ?>" 
                                               min="10" max="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Tab -->
                <div class="tab-pane fade" id="advanced" role="tabpanel">
                    
                    <!-- Danger Zone -->
                    <div class="card border-danger mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> <?= __('settings.danger_zone') ?></h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1"><strong><?= __('settings.reset_database') ?></strong></h6>
                        <p class="text-muted mb-0">
                            <?= __('settings.reset_database_desc') ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="<?= BASE_URL ?>/settings/reset-data" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> <?= __('settings.reset_database_btn') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

                    <!-- Quick Links -->
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="fas fa-link"></i> <?= __('settings.quick_links') ?></h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="<?= BASE_URL ?>/settings/update" class="btn btn-outline-primary">
                                    <i class="fas fa-sync-alt"></i> <?= __('settings.system_updates') ?>
                                </a>
                                <a href="<?= BASE_URL ?>/settings/migrations" class="btn btn-outline-info">
                                    <i class="fas fa-database"></i> Database Migrations
                                </a>
                                <a href="<?= BASE_URL ?>/settings/translations" class="btn btn-outline-success">
                                    <i class="fas fa-language"></i> <?= __('settings.translations') ?>
                                </a>
                                <a href="<?= BASE_URL ?>/users" class="btn btn-outline-secondary">
                                    <i class="fas fa-users"></i> <?= __('settings.user_management') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div><!-- /advanced tab -->
                
            </div><!-- /tab-content -->
            
            <!-- Save Button (Fixed at bottom) -->
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> <?= __('settings.save') ?>
                        </button>
                    </div>
                </div>
            </div>
            
        </form>
    </div>
</div>

<script>
function changeLanguage(languageCode) {
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>/settings/change-language';
    
    const langInput = document.createElement('input');
    langInput.type = 'hidden';
    langInput.name = 'language';
    langInput.value = languageCode;
    
    const redirectInput = document.createElement('input');
    redirectInput.type = 'hidden';
    redirectInput.name = 'redirect';
    redirectInput.value = '/settings';
    
    form.appendChild(langInput);
    form.appendChild(redirectInput);
    document.body.appendChild(form);
    form.submit();
}

// Logo preview function
function previewLogo(input) {
    const preview = document.getElementById('logoPreview');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('<?= __('settings.logo_too_large') ?? 'Το αρχείο είναι πολύ μεγάλο. Μέγιστο μέγεθος: 2MB' ?>');
            input.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('<?= __('settings.logo_invalid_type') ?? 'Μη έγκυρος τύπος αρχείου. Χρησιμοποιήστε PNG, JPG, GIF ή WebP' ?>');
            input.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Logo Preview" class="img-thumbnail" style="max-height: 150px; width: auto;">';
        };
        reader.readAsDataURL(file);
    }
}

// Remove logo function
function removeLogo() {
    if (confirm('<?= __('settings.confirm_remove_logo') ?? 'Είστε σίγουροι ότι θέλετε να διαγράψετε το λογότυπο;' ?>')) {
        document.getElementById('remove_logo').value = '1';
        document.getElementById('logoPreview').innerHTML = '<div class="border rounded p-4 text-center bg-light"><i class="fas fa-image fa-3x text-muted"></i><p class="text-muted mt-2 mb-0 small"><?= __('settings.logo_will_be_removed') ?? 'Το λογότυπο θα διαγραφεί κατά την αποθήκευση' ?></p></div>';
        document.getElementById('company_logo').value = '';
    }
}

// VAT Display Preview
function updateVatPreview() {
    const displayNotes = document.getElementById('display_vat_notes')?.checked || false;
    const includeVat = document.getElementById('prices_include_vat')?.checked || false;
    const previewElement = document.getElementById('preview-price');
    
    if (!previewElement) return;
    
    let priceText = '100,00 €';
    
    if (displayNotes) {
        const vatText = includeVat ? 'με ΦΠΑ' : 'χωρίς ΦΠΑ';
        priceText += ` <small class="text-muted">(${vatText})</small>`;
    }
    
    previewElement.innerHTML = priceText;
}

// Attach event listeners for VAT settings
document.addEventListener('DOMContentLoaded', function() {
    const displayVatCheckbox = document.getElementById('display_vat_notes');
    const pricesIncludeVatCheckbox = document.getElementById('prices_include_vat');
    
    if (displayVatCheckbox) {
        displayVatCheckbox.addEventListener('change', updateVatPreview);
    }
    
    if (pricesIncludeVatCheckbox) {
        pricesIncludeVatCheckbox.addEventListener('change', updateVatPreview);
    }
    
    // Initial preview
    updateVatPreview();
});
</script>
