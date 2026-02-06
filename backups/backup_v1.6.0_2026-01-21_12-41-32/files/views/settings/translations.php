<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/LanguageManager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/login');
    exit;
}

// Initialize language manager
$languageManager = new LanguageManager($_SESSION['language'] ?? 'el');

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save_translations':
                $languageCode = $_POST['language_code'] ?? '';
                $translations = $_POST['translations'] ?? [];
                
                if ($languageCode && !empty($translations)) {
                    // Convert flat array back to nested structure
                    $nestedTranslations = [];
                    foreach ($translations as $key => $value) {
                        $keys = explode('.', $key);
                        $temp = &$nestedTranslations;
                        
                        foreach ($keys as $i => $k) {
                            if ($i === count($keys) - 1) {
                                $temp[$k] = $value;
                            } else {
                                if (!isset($temp[$k])) {
                                    $temp[$k] = [];
                                }
                                $temp = &$temp[$k];
                            }
                        }
                    }
                    
                    if ($languageManager->saveTranslations($languageCode, $nestedTranslations)) {
                        $success = __('translations.translation_saved');
                    } else {
                        $error = __('settings.error');
                    }
                }
                break;
                
            case 'create_language':
                $code = strtolower(trim($_POST['language_code'] ?? ''));
                $name = trim($_POST['language_name'] ?? '');
                $base = $_POST['base_language'] ?? 'en';
                
                if (empty($code) || empty($name)) {
                    $error = __('translations.invalid_code');
                } else if ($languageManager->createLanguage($code, $name, $base)) {
                    $success = __('translations.language_created');
                } else {
                    $error = __('translations.language_exists');
                }
                break;
                
            case 'delete_language':
                $code = $_POST['language_code'] ?? '';
                
                if (in_array($code, ['el', 'en'])) {
                    $error = __('translations.cannot_delete_default');
                } else if ($languageManager->deleteLanguage($code)) {
                    $success = __('translations.language_deleted');
                } else {
                    $error = __('settings.error');
                }
                break;
        }
    }
}

// Get selected language for editing
$selectedLanguage = $_GET['lang'] ?? 'el';
$editLanguage = new LanguageManager($selectedLanguage);
$allTranslations = $editLanguage->getAllKeys();

// Get English translations for reference
$englishManager = new LanguageManager('en');
$englishTranslations = $englishManager->getAllKeys();

$availableLanguages = $languageManager->getAvailableLanguages();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="list-group sticky-top" style="top: 80px;">
                <a href="<?= BASE_URL ?>/settings" class="list-group-item list-group-item-action">
                    <i class="fas fa-cog me-2"></i> <?= __('settings.general') ?>
                </a>
                <a href="<?= BASE_URL ?>/settings/profile" class="list-group-item list-group-item-action">
                    <i class="fas fa-user me-2"></i> <?= __('settings.profile') ?>
                </a>
                <a href="<?= BASE_URL ?>/settings/update" class="list-group-item list-group-item-action">
                    <i class="fas fa-sync-alt me-2"></i> <?= __('settings.updates') ?>
                </a>
                <a href="<?= BASE_URL ?>/settings/translations" class="list-group-item list-group-item-action active">
                    <i class="fas fa-language me-2"></i> <?= __('settings.translations') ?>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-language text-primary"></i> <?= __('translations.title') ?></h2>
                    <p class="text-muted"><?= __('translations.description') ?></p>
                </div>
                <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i><?= __('common.back') ?>
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Language Selector & Create New -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('translations.select_language') ?></h5>
                            <div class="d-flex gap-2">
                                <select class="form-select" id="languageSelect" onchange="window.location.href='?lang=' + this.value">
                                    <?php foreach ($availableLanguages as $code => $name): ?>
                                        <option value="<?= $code ?>" <?= $selectedLanguage === $code ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($name) ?> (<?= strtoupper($code) ?>)
                                            <?php if ($code !== 'en' && $code !== 'el'): ?>
                                                - <?= $languageManager->getTranslationProgress($code) ?>% <?= __('translations.completed') ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($selectedLanguage !== 'en' && $selectedLanguage !== 'el'): ?>
                                    <button type="button" class="btn btn-danger" onclick="deleteLanguage('<?= $selectedLanguage ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('translations.create_new') ?></h5>
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#createLanguageModal">
                                <i class="fas fa-plus me-2"></i><?= __('translations.add_language') ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Translation Progress (for non-default languages) -->
            <?php if ($selectedLanguage !== 'en' && $selectedLanguage !== 'el'): ?>
                <?php
                $progress = $languageManager->getTranslationProgress($selectedLanguage);
                $total = count($englishTranslations);
                $completed = round(($progress / 100) * $total);
                $remaining = $total - $completed;
                ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="mb-0"><?= __('translations.progress') ?></h5>
                            <span class="badge bg-primary"><?= $progress ?>%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress ?>%">
                                <?= $completed ?> / <?= $total ?>
                            </div>
                        </div>
                        <div class="mt-2 d-flex justify-content-between text-muted small">
                            <span><i class="fas fa-check text-success"></i> <?= $completed ?> <?= __('translations.completed') ?></span>
                            <span><i class="fas fa-clock text-warning"></i> <?= $remaining ?> <?= __('translations.empty_translations') ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Filter Options -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" id="searchTranslations" class="form-control" 
                                   placeholder="<?= __('translations.search_translations') ?>">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="showEmptyOnly">
                                <label class="form-check-label" for="showEmptyOnly">
                                    <?= __('translations.show_empty_only') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Translation Form -->
            <form method="POST" id="translationForm">
                <input type="hidden" name="action" value="save_translations">
                <input type="hidden" name="language_code" value="<?= $selectedLanguage ?>">

                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-language me-2"></i><?= __('translations.translation') ?>: <?= $availableLanguages[$selectedLanguage] ?></span>
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="fas fa-save me-2"></i><?= __('translations.save_translations') ?>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%"><?= __('translations.english_term') ?></th>
                                        <th style="width: 70%"><?= __('translations.translation') ?> (<?= $availableLanguages[$selectedLanguage] ?>)</th>
                                    </tr>
                                </thead>
                                <tbody id="translationsBody">
                                    <?php foreach ($englishTranslations as $key => $englishValue): ?>
                                        <?php $currentValue = $allTranslations[$key] ?? ''; ?>
                                        <tr class="translation-row" data-empty="<?= empty($currentValue) ? '1' : '0' ?>">
                                            <td>
                                                <small class="text-muted d-block"><?= htmlspecialchars($key) ?></small>
                                                <div class="mt-1"><?= htmlspecialchars($englishValue) ?></div>
                                            </td>
                                            <td>
                                                <textarea 
                                                    name="translations[<?= htmlspecialchars($key) ?>]" 
                                                    class="form-control translation-input <?= empty($currentValue) ? 'border-warning' : '' ?>" 
                                                    rows="2"
                                                    placeholder="<?= __('translations.translation') ?>..."><?= htmlspecialchars($currentValue) ?></textarea>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?= __('translations.save_translations') ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Language Modal -->
<div class="modal fade" id="createLanguageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create_language">
                <div class="modal-header">
                    <h5 class="modal-title"><?= __('translations.create_new') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?= __('translations.language_code') ?> *</label>
                        <input type="text" name="language_code" class="form-control" 
                               placeholder="e.g., fr, de, es" required maxlength="2" pattern="[a-z]{2}">
                        <small class="text-muted"><?= __('translations.invalid_code') ?></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('translations.language_name') ?> *</label>
                        <input type="text" name="language_name" class="form-control" 
                               placeholder="e.g., Français, Deutsch, Español" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('translations.base_language') ?></label>
                        <select name="base_language" class="form-select">
                            <option value="en">English</option>
                            <option value="el">Ελληνικά</option>
                        </select>
                        <small class="text-muted">The base language structure to copy</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('common.cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= __('translations.add_language') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Language Form (hidden) -->
<form method="POST" id="deleteLanguageForm" style="display: none;">
    <input type="hidden" name="action" value="delete_language">
    <input type="hidden" name="language_code" id="deleteLanguageCode">
</form>

<script>
// Search translations
document.getElementById('searchTranslations').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.translation-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Show empty only filter
document.getElementById('showEmptyOnly').addEventListener('change', function(e) {
    const showEmpty = e.target.checked;
    const rows = document.querySelectorAll('.translation-row');
    
    rows.forEach(row => {
        if (showEmpty) {
            row.style.display = row.dataset.empty === '1' ? '' : 'none';
        } else {
            row.style.display = '';
        }
    });
});

// Delete language confirmation
function deleteLanguage(code) {
    if (confirm('<?= __('translations.confirm_delete_language') ?>')) {
        document.getElementById('deleteLanguageCode').value = code;
        document.getElementById('deleteLanguageForm').submit();
    }
}

// Auto-save indicator
let saveTimeout;
document.querySelectorAll('.translation-input').forEach(input => {
    input.addEventListener('input', function() {
        clearTimeout(saveTimeout);
        this.classList.add('border-warning');
        
        saveTimeout = setTimeout(() => {
            // Visual feedback only - actual save happens on submit
            this.classList.remove('border-warning');
        }, 1000);
    });
});

// Form submit with loading
document.getElementById('translationForm').addEventListener('submit', function() {
    const submitButtons = this.querySelectorAll('[type="submit"]');
    submitButtons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><?= __('common.loading') ?>';
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
