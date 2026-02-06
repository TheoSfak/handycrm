<?php
/**
 * Materials Duplicates View
 * Shows duplicate materials and allows deletion
 */

$pageTitle = 'Διπλότυπα Υλικά';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home me-1"></i>Αρχική</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/materials"><i class="fas fa-boxes me-1"></i>Υλικά</a>
            </li>
            <li class="breadcrumb-item active">Διπλότυπα</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Διπλότυπα Υλικά</h2>
        <a href="<?= BASE_URL ?>/materials" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Επιστροφή
        </a>
    </div>

    <?php if (empty($duplicates)): ?>
        <!-- No Duplicates Found -->
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Δεν βρέθηκαν διπλότυπα!</strong> Όλα τα υλικά έχουν μοναδικές ονομασίες.
        </div>
    <?php else: ?>
        <!-- Info Alert -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            Βρέθηκαν <strong><?= count($duplicates) ?></strong> ομάδες διπλότυπων υλικών. 
            Μπορείτε να επιλέξετε ποια υλικά θέλετε να διατηρήσετε και ποια να διαγράψετε.
        </div>

        <!-- Quick Action: Delete All Duplicates (Keep First) -->
        <div class="alert alert-warning mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-magic me-2"></i>
                    <strong>Γρήγορη Ενέργεια:</strong> Διαγραφή όλων των διπλότυπων αυτόματα (διατήρηση μόνο του πρώτου από κάθε ομάδα)
                </div>
                <button type="button" class="btn btn-danger" onclick="deleteAllDuplicatesKeepFirst()">
                    <i class="fas fa-trash-alt me-1"></i>Διαγραφή Όλων των Διπλότυπων
                </button>
            </div>
        </div>

        <!-- Duplicates Groups -->
        <?php foreach ($duplicates as $index => $group): ?>
            <div class="card mb-4">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="mb-0">
                        <i class="fas fa-copy me-2"></i>
                        Ομάδα #<?= $index + 1 ?>: "<?= htmlspecialchars($group['name']) ?>"
                        <span class="badge bg-warning text-dark ms-2"><?= $group['count'] ?> υλικά</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">
                                        <input type="checkbox" class="form-check-input group-select-all" 
                                               onchange="toggleGroupSelectAll(this, <?= $index ?>)">
                                    </th>
                                    <th style="width: 8%;">ID</th>
                                    <th style="width: 30%;">Ονομασία</th>
                                    <th style="width: 15%;">SKU</th>
                                    <th style="width: 15%;">Κατηγορία</th>
                                    <th style="width: 12%;">Τιμή</th>
                                    <th style="width: 15%;">Ενέργειες</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['materials'] as $material): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input duplicate-checkbox group-<?= $index ?>" 
                                                   value="<?= $material['id'] ?>"
                                                   onchange="updateDeleteButton()">
                                        </td>
                                        <td><?= $material['id'] ?></td>
                                        <td><?= htmlspecialchars($material['name']) ?></td>
                                        <td><?= htmlspecialchars($material['sku']) ?></td>
                                        <td><?= htmlspecialchars($material['category']) ?></td>
                                        <td>
                                            <?php if (isset($material['price'])): ?>
                                                €<?= number_format($material['price'], 2) ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/materials/view/<?= $material['id'] ?>" 
                                               class="btn btn-sm btn-info" title="Προβολή" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteSingle(<?= $material['id'] ?>)" title="Διαγραφή">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Bulk Delete Section -->
        <div class="card border-danger mb-4" id="bulkDeleteCard" style="display: none;">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-trash-alt me-2"></i>
                    Μαζική Διαγραφή
                </h5>
            </div>
            <div class="card-body">
                <p>
                    <strong id="selectedCount">0</strong> υλικά επιλεγμένα για διαγραφή
                </p>
                <button type="button" class="btn btn-danger" onclick="bulkDeleteDuplicates()">
                    <i class="fas fa-trash me-1"></i>Διαγραφή Επιλεγμένων
                </button>
                <button type="button" class="btn btn-secondary" onclick="clearAllSelections()">
                    <i class="fas fa-times me-1"></i>Καθαρισμός Επιλογών
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Toggle select all for a specific group
function toggleGroupSelectAll(checkbox, groupIndex) {
    const groupCheckboxes = document.querySelectorAll('.group-' + groupIndex);
    groupCheckboxes.forEach(cb => cb.checked = checkbox.checked);
    updateDeleteButton();
}

// Update the bulk delete card visibility
function updateDeleteButton() {
    const selected = document.querySelectorAll('.duplicate-checkbox:checked');
    const bulkCard = document.getElementById('bulkDeleteCard');
    const countSpan = document.getElementById('selectedCount');
    
    countSpan.textContent = selected.length;
    bulkCard.style.display = selected.length > 0 ? 'block' : 'none';
}

// Clear all selections
function clearAllSelections() {
    document.querySelectorAll('.duplicate-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.group-select-all').forEach(cb => cb.checked = false);
    updateDeleteButton();
}

// Delete a single material
function deleteSingle(id) {
    if (!confirm('Είστε σίγουρος ότι θέλετε να διαγράψετε αυτό το υλικό;')) {
        return;
    }
    
    fetch('<?= BASE_URL ?>/materials/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({ ids: [id] })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Το υλικό διαγράφηκε επιτυχώς');
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

// Bulk delete selected materials
function bulkDeleteDuplicates() {
    const selected = document.querySelectorAll('.duplicate-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) {
        alert('Δεν έχετε επιλέξει υλικά');
        return;
    }
    
    if (!confirm(`Είστε σίγουρος ότι θέλετε να διαγράψετε ${ids.length} υλικά;`)) {
        return;
    }
    
    fetch('<?= BASE_URL ?>/materials/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({ ids: ids })
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

// Delete all duplicates automatically, keeping only the first from each group
function deleteAllDuplicatesKeepFirst() {
    const duplicateGroups = <?= json_encode($duplicates) ?>;
    
    // Calculate total items to delete
    let totalToDelete = 0;
    const idsToDelete = [];
    
    duplicateGroups.forEach(group => {
        // Keep first (index 0), delete the rest
        for (let i = 1; i < group.materials.length; i++) {
            idsToDelete.push(group.materials[i].id);
            totalToDelete++;
        }
    });
    
    if (totalToDelete === 0) {
        alert('Δεν υπάρχουν διπλότυπα για διαγραφή');
        return;
    }
    
    const message = `Θα διαγραφούν ${totalToDelete} διπλότυπα υλικά.\n\n` +
                    `Από κάθε ομάδα θα διατηρηθεί μόνο το πρώτο υλικό.\n\n` +
                    `Είστε σίγουρος;`;
    
    if (!confirm(message)) {
        return;
    }
    
    // Show loading indicator
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Διαγραφή...';
    
    fetch('<?= BASE_URL ?>/materials/bulk-delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
        },
        body: JSON.stringify({ ids: idsToDelete })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Επιτυχής διαγραφή!\n\nΔιαγράφηκαν ${data.deleted} διπλότυπα υλικά.`);
            window.location.reload();
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert('Σφάλμα: ' + (data.error || 'Άγνωστο σφάλμα'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = originalText;
        alert('Σφάλμα κατά τη διαγραφή');
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
