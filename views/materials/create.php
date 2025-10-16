<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-boxes"></i> <?= __('materials.new_material') ?></h2>
    <a href="?route=/materials" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> <?= __('materials.back') ?>
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
                <form method="POST" action="?route=/materials/create">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label"><?= __('materials.name') ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label"><?= __('materials.description') ?></label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label"><?= __('materials.category') ?> <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value=""><?= __('materials.select') ?></option>
                                    <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="unit" class="form-label"><?= __('materials.unit_measurement') ?></label>
                                <select class="form-select" id="unit" name="unit">
                                    <option value="τεμ">Τεμάχια (τεμ)</option>
                                    <option value="μ">Μέτρα (μ)</option>
                                    <option value="μ²">Τετραγωνικά Μέτρα (μ²)</option>
                                    <option value="μ³">Κυβικά Μέτρα (μ³)</option>
                                    <option value="κιλά">Κιλά (κιλά)</option>
                                    <option value="γρ">Γραμμάρια (γρ)</option>
                                    <option value="τόνοι">Τόνοι (τόνοι)</option>
                                    <option value="λίτρα">Λίτρα (λίτρα)</option>
                                    <option value="ml">Χιλιοστόλιτρα (ml)</option>
                                    <option value="σετ">Σετ (σετ)</option>
                                    <option value="κουτί">Κουτί (κουτί)</option>
                                    <option value="σακί">Σακί (σακί)</option>
                                    <option value="παλέτα">Παλέτα (παλέτα)</option>
                                    <option value="ρολό">Ρολό (ρολό)</option>
                                    <option value="φύλλο">Φύλλο (φύλλο)</option>
                                    <option value="κιβώτιο">Κιβώτιο (κιβώτιο)</option>
                                    <option value="ώρες">Ώρες (ώρες)</option>
                                    <option value="ημέρες">Ημέρες (ημέρες)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="unit_price" class="form-label"><?= __('materials.unit_price') ?></label>
                                <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                       value="0" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="current_stock" class="form-label"><?= __('materials.current_stock') ?></label>
                                <input type="number" class="form-control" id="current_stock" name="current_stock" 
                                       value="0" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="min_stock" class="form-label"><?= __('materials.min_stock') ?></label>
                                <input type="number" class="form-control" id="min_stock" name="min_stock" 
                                       value="0" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier" class="form-label"><?= __('materials.supplier') ?></label>
                                <input type="text" class="form-control" id="supplier" name="supplier">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="supplier_code" class="form-label"><?= __('materials.supplier_code') ?></label>
                                <input type="text" class="form-control" id="supplier_code" name="supplier_code">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= __('materials.save') ?>
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
