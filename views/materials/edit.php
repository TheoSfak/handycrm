<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-boxes"></i> <?= __('materials.edit_material') ?></h2>
    <a href="?route=/materials" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> <?= __('materials.back') ?>
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
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
                        <label for="name" class="form-label"><?= __('materials.name') ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($material['name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label"><?= __('materials.description') ?></label>
                        <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($material['description']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label"><?= __('materials.category') ?> <span class="text-danger">*</span></label>
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
                                <label for="unit" class="form-label"><?= __('materials.unit_measurement') ?></label>
                                <select class="form-select" id="unit" name="unit">
                                    <option value="τεμ" <?= $material['unit'] === 'τεμ' ? 'selected' : '' ?>>Τεμάχια (τεμ)</option>
                                    <option value="μ" <?= $material['unit'] === 'μ' ? 'selected' : '' ?>>Μέτρα (μ)</option>
                                    <option value="μ²" <?= $material['unit'] === 'μ²' ? 'selected' : '' ?>>Τετραγωνικά Μέτρα (μ²)</option>
                                    <option value="μ³" <?= $material['unit'] === 'μ³' ? 'selected' : '' ?>>Κυβικά Μέτρα (μ³)</option>
                                    <option value="κιλά" <?= $material['unit'] === 'κιλά' ? 'selected' : '' ?>>Κιλά (κιλά)</option>
                                    <option value="γρ" <?= $material['unit'] === 'γρ' ? 'selected' : '' ?>>Γραμμάρια (γρ)</option>
                                    <option value="τόνοι" <?= $material['unit'] === 'τόνοι' ? 'selected' : '' ?>>Τόνοι (τόνοι)</option>
                                    <option value="λίτρα" <?= $material['unit'] === 'λίτρα' ? 'selected' : '' ?>>Λίτρα (λίτρα)</option>
                                    <option value="ml" <?= $material['unit'] === 'ml' ? 'selected' : '' ?>>Χιλιοστόλιτρα (ml)</option>
                                    <option value="σετ" <?= $material['unit'] === 'σετ' ? 'selected' : '' ?>>Σετ (σετ)</option>
                                    <option value="κουτί" <?= $material['unit'] === 'κουτί' ? 'selected' : '' ?>>Κουτί (κουτί)</option>
                                    <option value="σακί" <?= $material['unit'] === 'σακί' ? 'selected' : '' ?>>Σακί (σακί)</option>
                                    <option value="παλέτα" <?= $material['unit'] === 'παλέτα' ? 'selected' : '' ?>>Παλέτα (παλέτα)</option>
                                    <option value="ρολό" <?= $material['unit'] === 'ρολό' ? 'selected' : '' ?>>Ρολό (ρολό)</option>
                                    <option value="φύλλο" <?= $material['unit'] === 'φύλλο' ? 'selected' : '' ?>>Φύλλο (φύλλο)</option>
                                    <option value="κιβώτιο" <?= $material['unit'] === 'κιβώτιο' ? 'selected' : '' ?>>Κιβώτιο (κιβώτιο)</option>
                                    <option value="ώρες" <?= $material['unit'] === 'ώρες' ? 'selected' : '' ?>>Ώρες (ώρες)</option>
                                    <option value="ημέρες" <?= $material['unit'] === 'ημέρες' ? 'selected' : '' ?>>Ημέρες (ημέρες)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="unit_price" class="form-label"><?= __('materials.unit_price') ?></label>
                                <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                       value="<?= $material['unit_price'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="current_stock" class="form-label"><?= __('materials.current_stock') ?></label>
                                <input type="number" class="form-control" id="current_stock" name="current_stock" 
                                       value="<?= $material['current_stock'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="min_stock" class="form-label"><?= __('materials.min_stock') ?></label>
                                <input type="number" class="form-control" id="min_stock" name="min_stock" 
                                       value="<?= $material['min_stock'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier" class="form-label"><?= __('materials.supplier') ?></label>
                                <input type="text" class="form-control" id="supplier" name="supplier" 
                                       value="<?= htmlspecialchars($material['supplier']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_code" class="form-label"><?= __('materials.supplier_code') ?></label>
                                <input type="text" class="form-control" id="supplier_code" name="supplier_code" 
                                       value="<?= htmlspecialchars($material['supplier_code']) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= __('materials.update') ?>
                        </button>
                        <a href="?route=/materials" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <?= __('materials.cancel') ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
