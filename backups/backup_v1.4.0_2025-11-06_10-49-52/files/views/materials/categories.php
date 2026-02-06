<?php
/**
 * Material Categories Management
 * List and manage material categories
 */

$pageTitle = $pageTitle ?? 'Κατηγορίες Υλικών';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/materials">Υλικά</a></li>
            <li class="breadcrumb-item active">Κατηγορίες</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-folder me-2"></i>Κατηγορίες Υλικών</h2>
        <div>
            <a href="<?= BASE_URL ?>/materials" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Πίσω στα Υλικά
            </a>
            <button type="button" class="btn btn-primary" onclick="showAddModal()">
                <i class="fas fa-plus me-1"></i>Νέα Κατηγορία
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Categories List -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Λίστα Κατηγοριών
                <span class="badge bg-light text-dark ms-2"><?= count($categories) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h5 class="text-muted">Δεν υπάρχουν κατηγορίες</h5>
                    <p class="text-muted">Δημιουργήστε κατηγορίες για να οργανώσετε τα υλικά σας</p>
                    <button type="button" class="btn btn-primary" onclick="showAddModal()">
                        <i class="fas fa-plus me-2"></i>Δημιουργία Πρώτης Κατηγορίας
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Όνομα</th>
                                <th width="40%">Περιγραφή</th>
                                <th width="15%" class="text-center">Υλικά</th>
                                <th width="15%" class="text-end">Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $index => $category): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><i class="fas fa-folder text-info me-2"></i><?= htmlspecialchars($category['name']) ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($category['description'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($category['material_count'] > 0): ?>
                                            <a href="<?= BASE_URL ?>/materials?category_id=<?= $category['id'] ?>" 
                                               class="badge bg-info text-decoration-none">
                                                <?= $category['material_count'] ?> υλικά
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">0 υλικά</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-outline-primary" 
                                                    onclick='editCategory(<?= json_encode($category) ?>)'
                                                    title="Επεξεργασία">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>', <?= $category['material_count'] ?>)"
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
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Νέα Κατηγορία</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="categoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">
                            Όνομα Κατηγορίας <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="categoryName" 
                               name="name" 
                               required
                               placeholder="π.χ. Ηλεκτρολογικά">
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Περιγραφή</label>
                        <textarea class="form-control" 
                                  id="categoryDescription" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Προαιρετική περιγραφή..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Αποθήκευση
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Επιβεβαίωση Διαγραφής</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Είστε σίγουροι ότι θέλετε να διαγράψετε την κατηγορία:</p>
                <p><strong id="deleteCategoryName"></strong></p>
                <div id="deleteWarning" class="alert alert-warning d-none">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Προσοχή!</strong> Η κατηγορία περιέχει <span id="materialCount"></span> υλικά.
                    Δεν μπορεί να διαγραφεί.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    <button type="submit" class="btn btn-danger" id="deleteButton">Διαγραφή</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let categoryModal = null;
let deleteModal = null;

document.addEventListener('DOMContentLoaded', function() {
    categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
});

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Νέα Κατηγορία';
    document.getElementById('categoryForm').action = '<?= BASE_URL ?>/materials/categories/add';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    categoryModal.show();
}

function editCategory(category) {
    document.getElementById('modalTitle').textContent = 'Επεξεργασία Κατηγορίας';
    document.getElementById('categoryForm').action = '<?= BASE_URL ?>/materials/categories/' + category.id + '/update';
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryDescription').value = category.description || '';
    categoryModal.show();
}

function deleteCategory(id, name, materialCount) {
    document.getElementById('deleteCategoryName').textContent = name;
    document.getElementById('deleteForm').action = '<?= BASE_URL ?>/materials/categories/' + id + '/delete';
    
    const warning = document.getElementById('deleteWarning');
    const deleteButton = document.getElementById('deleteButton');
    
    if (materialCount > 0) {
        warning.classList.remove('d-none');
        document.getElementById('materialCount').textContent = materialCount;
        deleteButton.disabled = true;
        deleteButton.classList.add('disabled');
    } else {
        warning.classList.add('d-none');
        deleteButton.disabled = false;
        deleteButton.classList.remove('disabled');
    }
    
    deleteModal.show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
