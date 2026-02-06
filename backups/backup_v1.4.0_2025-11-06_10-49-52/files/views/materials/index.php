<?php
/**
 * Materials Catalog Index View
 * Lists all materials from the catalog with filtering and statistics
 */

$pageTitle = 'Κατάλογος Υλικών';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home me-1"></i>Αρχική</a>
            </li>
            <li class="breadcrumb-item active">Υλικά Καταλόγου</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-boxes me-2"></i>Κατάλογος Υλικών</h2>
        <div>
            <a href="<?= BASE_URL ?>/materials/duplicates" class="btn btn-warning me-2">
                <i class="fas fa-exclamation-triangle me-1"></i>Έλεγχος Διπλότυπων
            </a>
            <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-success" onclick="exportMaterialsCSV()">
                    <i class="fas fa-file-export me-1"></i>Export CSV
                </button>
                <button type="button" class="btn btn-info" onclick="document.getElementById('importCsvFile').click()">
                    <i class="fas fa-file-import me-1"></i>Import CSV
                </button>
                <button type="button" class="btn btn-secondary" onclick="downloadDemoCSV()">
                    <i class="fas fa-file-download me-1"></i>Demo CSV
                </button>
            </div>
            <a href="<?= BASE_URL ?>/materials/categories" class="btn btn-outline-secondary me-2">
                <i class="fas fa-tags me-1"></i>Κατηγορίες
            </a>
            <a href="<?= BASE_URL ?>/materials/add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Νέο Υλικό
            </a>
        </div>
    </div>
    
    <!-- Bulk Actions Toolbar (hidden by default) -->
    <div id="bulkActionsToolbar" class="alert alert-info mb-4" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-check-square me-2"></i>
                <span id="selectedCount">0</span> επιλεγμένα υλικά
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">
                    <i class="fas fa-trash me-1"></i>Διαγραφή Επιλεγμένων
                </button>
                <button type="button" class="btn btn-sm btn-success" onclick="bulkActivate()">
                    <i class="fas fa-check me-1"></i>Ενεργοποίηση
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="bulkDeactivate()">
                    <i class="fas fa-times me-1"></i>Απενεργοποίηση
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    <i class="fas fa-times me-1"></i>Ακύρωση
                </button>
            </div>
        </div>
    </div>
    
    <!-- Hidden Import File Input -->
    <input type="file" id="importCsvFile" accept=".csv" style="display: none;" onchange="handleImportCSV(this)">

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <!-- Statistics Cards -->
    <?php if (isset($statistics)): ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Σύνολο Υλικών</h6>
                            <h3 class="mb-0"><?= $statistics['total_materials'] ?? 0 ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-boxes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Ενεργά Υλικά</h6>
                            <h3 class="mb-0 text-success"><?= $statistics['active_materials'] ?? 0 ?></h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Κατηγορίες</h6>
                            <h3 class="mb-0"><?= $statistics['total_categories'] ?? 0 ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/materials" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Αναζήτηση</label>
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Αναζήτηση υλικού..."
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Κατηγορία</label>
                    <select name="category_id" class="form-select">
                        <option value="">Όλες οι κατηγορίες</option>
                        <?php foreach ($categories ?? [] as $category): ?>
                        <option value="<?= $category['id'] ?>" 
                                <?= (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Κατάσταση</label>
                    <select name="is_active" class="form-select">
                        <option value="">Όλα</option>
                        <option value="1" <?= (isset($_GET['is_active']) && $_GET['is_active'] === '1') ? 'selected' : '' ?>>Ενεργά</option>
                        <option value="0" <?= (isset($_GET['is_active']) && $_GET['is_active'] === '0') ? 'selected' : '' ?>>Ανενεργά</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Αναζήτηση
                    </button>
                    <a href="<?= BASE_URL ?>/materials" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Καθαρισμός
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Materials Table -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Υλικά (<?= count($materials ?? []) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($materials)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Δεν βρέθηκαν υλικά</p>
                <a href="<?= BASE_URL ?>/materials/add" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Προσθήκη πρώτου υλικού
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th width="4%">#</th>
                            <th width="23%">Ονομασία</th>
                            <th width="15%">Κατηγορία</th>
                            <th width="10%">Μονάδα</th>
                            <th width="10%">Τιμή</th>
                            <th width="15%">Προμηθευτής</th>
                            <th width="10%" class="text-center">Κατάσταση</th>
                            <th width="10%" class="text-center">Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $index => $material): 
                            $rowNumber = ($currentPage - 1) * $perPage + $index + 1;
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="material-checkbox" value="<?= $material['id'] ?>" onchange="updateBulkActions()">
                            </td>
                            <td><?= $rowNumber ?></td>
                            <td>
                                <strong><?= htmlspecialchars($material['name']) ?></strong>
                                <?php if (!empty($material['description'])): ?>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($material['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($material['category_name'] ?? 'Χωρίς κατηγορία') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($material['unit'] ?? '-') ?></td>
                            <td>
                                <?php if ($material['default_price']): ?>
                                <strong><?= number_format($material['default_price'], 2) ?>€</strong>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($material['supplier'])): ?>
                                <small><?= htmlspecialchars($material['supplier']) ?></small>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($material['is_active']): ?>
                                <span class="badge bg-success">Ενεργό</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Ανενεργό</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= BASE_URL ?>/materials/<?= $material['id'] ?>/edit" 
                                       class="btn btn-outline-primary" 
                                       title="Επεξεργασία">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?= $material['id'] ?>, '<?= htmlspecialchars($material['name'], ENT_QUOTES) ?>')"
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
            <?php endif; ?>
            
            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-light">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0">Εμφάνιση:</label>
                            <select class="form-select form-select-sm" style="width: auto;" id="perPageSelect">
                                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                            <span class="text-muted small">από <?= $totalMaterials ?> υλικά</span>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <nav aria-label="Σελιδοποίηση υλικών">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <!-- Previous Button -->
                                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= buildPaginationUrl($currentPage - 1, $perPage, $filters) ?>" aria-label="Προηγούμενη">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <?php
                                // Calculate page range to display
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                
                                // First page
                                if ($startPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPaginationUrl(1, $perPage, $filters) ?>">1</a>
                                    </li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <!-- Page Numbers -->
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildPaginationUrl($i, $perPage, $filters) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Last page -->
                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPaginationUrl($totalPages, $perPage, $filters) ?>"><?= $totalPages ?></a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Next Button -->
                                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= buildPaginationUrl($currentPage + 1, $perPage, $filters) ?>" aria-label="Επόμενη">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function to build pagination URL with current filters
function buildPaginationUrl($page, $perPage, $filters) {
    $params = [
        'page' => $page,
        'per_page' => $perPage
    ];
    
    if (!empty($filters['category_id'])) {
        $params['category_id'] = $filters['category_id'];
    }
    if (!empty($filters['search'])) {
        $params['search'] = $filters['search'];
    }
    if (isset($_GET['show_inactive'])) {
        $params['show_inactive'] = '1';
    }
    
    return BASE_URL . '/materials?' . http_build_query($params);
}
?>

<script>
// Handle per-page change
document.getElementById('perPageSelect').addEventListener('change', function() {
    const perPage = this.value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', 1); // Reset to first page
    window.location.href = url.toString();
});
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Επιβεβαίωση Διαγραφής
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Είστε σίγουροι ότι θέλετε να διαγράψετε το υλικό:</p>
                <p class="text-center"><strong id="deleteMaterialName"></strong></p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Σημείωση:</strong> Αν το υλικό χρησιμοποιείται σε εργασίες, θα γίνει ανενεργό αντί για διαγραφή.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Ακύρωση
                </button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Διαγραφή
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(materialId, materialName) {
    document.getElementById('deleteMaterialName').textContent = materialName;
    document.getElementById('deleteForm').action = '<?= BASE_URL ?>/materials/' + materialId + '/delete';
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

/**
 * Export all materials to CSV
 */
function exportMaterialsCSV() {
    // Fetch all materials (without pagination)
    const url = new URL(window.location.href);
    url.searchParams.delete('page');
    url.searchParams.delete('per_page');
    url.searchParams.set('export', 'csv');
    
    // Create temporary link and trigger download
    window.location.href = url.toString();
}

/**
 * Download demo CSV template
 */
function downloadDemoCSV() {
    const demoData = [
        ['Όνομα', 'Περιγραφή', 'Κατηγορία', 'Μονάδα', 'Τιμή', 'Προμηθευτής', 'Κωδικός Προμηθευτή'],
        ['Καλώδιο ΝΥΜ 3x1.5', 'Καλώδιο τριπολικό 1.5mm', 'Ηλεκτρολογικά', 'μ', '1.20', 'Ηλεκτρονική ΑΕ', 'NYM-3X1.5'],
        ['Σωλήνας PVC Φ32', 'Σωλήνας αποχέτευσης 32mm', 'Υδραυλικά', 'μ', '3.50', 'Υδραυλικά Παπαδόπουλος', 'PVC-32'],
        ['Τσιμέντο 25kg', 'Τσιμέντο Portland 25kg', 'Οικοδομικά', 'τεμ', '5.50', 'Οικοδομικά Γεωργίου', 'CEM-25'],
        ['Πρίζα Σούκο Λευκή', 'Πρίζα σούκο χωνευτή', 'Ηλεκτρολογικά', 'τεμ', '2.80', 'Ηλεκτρονική ΑΕ', 'SOCKET-WHITE']
    ];
    
    // Convert to CSV
    const csvContent = demoData.map(row => 
        row.map(cell => `"${cell}"`).join(',')
    ).join('\n');
    
    // Add UTF-8 BOM for Excel compatibility
    const BOM = '\uFEFF';
    const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
    
    // Create download link
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'materials_demo_template.csv';
    link.click();
}

/**
 * Handle CSV import
 */
function handleImportCSV(input) {
    const file = input.files[0];
    if (!file) return;
    
    if (!file.name.endsWith('.csv')) {
        alert('Παρακαλώ επιλέξτε αρχείο CSV');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const content = e.target.result;
        parseAndImportCSV(content);
    };
    reader.readAsText(file, 'UTF-8');
}

/**
 * Parse CSV and send to server
 */
function parseAndImportCSV(content) {
    // Detect delimiter (comma or tab)
    const firstLine = content.split('\n')[0];
    const delimiter = firstLine.includes('\t') ? '\t' : ',';
    
    // Simple CSV/TSV parser
    const lines = content.split('\n').filter(line => line.trim());
    const headers = parseCSVLine(lines[0], delimiter);
    
    // Clean headers - remove quotes and extra text
    const cleanHeaders = headers.map(h => {
        h = h.replace(/^"|"$/g, ''); // Remove quotes
        h = h.replace(/\s*\(.*?\)\s*/g, ''); // Remove (€) etc
        return h.trim();
    });
    
    const materials = [];
    for (let i = 1; i < lines.length; i++) {
        const values = parseCSVLine(lines[i], delimiter);
        if (values.length >= cleanHeaders.length) {
            const material = {};
            cleanHeaders.forEach((header, index) => {
                // Skip ID column
                if (header !== 'ID' && index < values.length) {
                    let value = values[index].replace(/^"|"$/g, '').trim();
                    
                    // Convert European number format (comma) to decimal point
                    // Only for price fields
                    if (header === 'Τιμή' && value) {
                        value = value.replace(',', '.');
                    }
                    
                    material[header] = value;
                }
            });
            
            // Only add if has name
            if (material['Όνομα']) {
                materials.push(material);
            }
        }
    }
    
    if (materials.length === 0) {
        alert('Δεν βρέθηκαν έγκυρα δεδομένα στο CSV');
        return;
    }
    
    // Confirm import
    if (!confirm(`Θέλετε να εισάγετε ${materials.length} υλικά;`)) {
        return;
    }
    
    // Send to server
    fetch('<?= BASE_URL ?>/materials/import', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({ materials: materials })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Επιτυχής εισαγωγή ${data.imported} υλικών!`);
            window.location.reload();
        } else {
            alert('Σφάλμα κατά την εισαγωγή: ' + (data.error || 'Άγνωστο σφάλμα'));
        }
    })
    .catch(error => {
        alert('Σφάλμα δικτύου: ' + error.message);
    });
}

/**
 * Parse a single CSV line (handles quoted fields with commas)
 */
function parseCSVLine(line, delimiter = ',') {
    const result = [];
    let current = '';
    let inQuotes = false;
    
    for (let i = 0; i < line.length; i++) {
        const char = line[i];
        
        if (char === '"') {
            inQuotes = !inQuotes;
        } else if (char === delimiter && !inQuotes) {
            result.push(current.trim());
            current = '';
        } else {
            current += char;
        }
    }
    result.push(current.trim());
    
    return result;
}

// Bulk Actions Functions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.material-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.material-checkbox:checked');
    const toolbar = document.getElementById('bulkActionsToolbar');
    const count = document.getElementById('selectedCount');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    count.textContent = checked.length;
    toolbar.style.display = checked.length > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.material-checkbox');
    selectAllCheckbox.checked = allCheckboxes.length > 0 && checked.length === allCheckboxes.length;
}

function getSelectedIds() {
    const checked = document.querySelectorAll('.material-checkbox:checked');
    return Array.from(checked).map(cb => cb.value);
}

function clearSelection() {
    document.querySelectorAll('.material-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function bulkDelete() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Δεν έχετε επιλέξει υλικά');
        return;
    }
    
    if (!confirm(`Είστε σίγουρος ότι θέλετε να διαγράψετε ${selectedIds.length} υλικά;`)) {
        return;
    }
    
    fetch('<?= BASE_URL ?>/materials/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({ ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Διαγράφηκαν επιτυχώς ${data.deleted} υλικά`);
            window.location.reload();
        } else {
            alert('Σφάλμα: ' + (data.error || 'Άγνωστο σφάλμα'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά τη διαγραφή');
    });
}

function bulkActivate() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Δεν έχετε επιλέξει υλικά');
        return;
    }
    
    fetch('<?= BASE_URL ?>/materials/bulk-activate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({ ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Ενεργοποιήθηκαν επιτυχώς ${data.activated} υλικά`);
            window.location.reload();
        } else {
            alert('Σφάλμα: ' + (data.error || 'Άγνωστο σφάλμα'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά την ενεργοποίηση');
    });
}

function bulkDeactivate() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('Δεν έχετε επιλέξει υλικά');
        return;
    }
    
    fetch('<?= BASE_URL ?>/materials/bulk-deactivate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({ ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Απενεργοποιήθηκαν επιτυχώς ${data.deactivated} υλικά`);
            window.location.reload();
        } else {
            alert('Σφάλμα: ' + (data.error || 'Άγνωστο σφάλμα'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά την απενεργοποίηση');
    });
}

function checkDuplicates() {
    fetch('<?= BASE_URL ?>/materials/check-duplicates', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.duplicates.length === 0) {
                alert('Δεν βρέθηκαν διπλότυπα υλικά');
            } else {
                // Show duplicates in a modal or alert
                let message = `Βρέθηκαν ${data.duplicates.length} ομάδες διπλότυπων:\n\n`;
                data.duplicates.forEach((group, index) => {
                    message += `Ομάδα ${index + 1}: ${group.name}\n`;
                    message += `Υλικά: ${group.materials.map(m => `#${m.id}`).join(', ')}\n\n`;
                });
                alert(message);
            }
        } else {
            alert('Σφάλμα: ' + (data.error || 'Άγνωστο σφάλμα'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Σφάλμα κατά τον έλεγχο διπλότυπων');
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
