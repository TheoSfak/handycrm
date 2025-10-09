<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-boxes"></i> Επεξεργασία Υλικού</h2>
    <a href="?route=/materials" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Πίσω
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="?route=/materials/edit&id=<?= $material['id'] ?>">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Όνομα <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($material['name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Περιγραφή</label>
                        <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($material['description']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Κατηγορία <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $material['category'] === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="unit" class="form-label">Μονάδα Μέτρησης</label>
                                <input type="text" class="form-control" id="unit" name="unit" 
                                       value="<?= htmlspecialchars($material['unit']) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="unit_price" class="form-label">Τιμή Μονάδας (€)</label>
                                <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                       value="<?= $material['unit_price'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="current_stock" class="form-label">Τρέχον Stock</label>
                                <input type="number" class="form-control" id="current_stock" name="current_stock" 
                                       value="<?= $material['current_stock'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="min_stock" class="form-label">Ελάχιστο Stock</label>
                                <input type="number" class="form-control" id="min_stock" name="min_stock" 
                                       value="<?= $material['min_stock'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier" class="form-label">Προμηθευτής</label>
                                <input type="text" class="form-control" id="supplier" name="supplier" 
                                       value="<?= htmlspecialchars($material['supplier']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_code" class="form-label">Κωδικός Προμηθευτή</label>
                                <input type="text" class="form-control" id="supplier_code" name="supplier_code" 
                                       value="<?= htmlspecialchars($material['supplier_code']) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Ενημέρωση
                        </button>
                        <a href="?route=/materials" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Ακύρωση
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
