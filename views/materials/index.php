<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-boxes"></i> <?= __('menu.materials') ?></h2>
    <a href="?route=/materials/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> <?= __('materials.new_material') ?>
    </a>
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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted"><?= __('materials.total_materials') ?></h6>
                <h3><?= $stats['total_materials'] ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted"><?= __('materials.total_value') ?></h6>
                <h3><?= formatCurrency($stats['total_value']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <h6 class="text-muted"><?= __('materials.low_stock') ?></h6>
                <h3 class="text-warning"><?= $stats['low_stock_count'] ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body">
                <h6 class="text-muted"><?= __('materials.out_of_stock') ?></h6>
                <h3 class="text-danger"><?= $stats['out_of_stock_count'] ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="?" class="row g-3">
            <input type="hidden" name="route" value="/materials">
            
            <div class="col-md-3">
                <label class="form-label"><?= __('materials.category') ?></label>
                <select name="category" class="form-select">
                    <option value=""><?= __('common.all') ?></option>
                    <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['category'] === $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label"><?= __('materials.low_stock') ?></label>
                <select name="low_stock" class="form-select">
                    <option value=""><?= __('common.all') ?></option>
                    <option value="1" <?= $filters['low_stock'] ? 'selected' : '' ?>><?= __('materials.only_low') ?></option>
                </select>
            </div>
            
            <div class="col-md-6">
                <label class="form-label"><?= __('common.search') ?></label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="<?= __('materials.search_placeholder') ?>" 
                           value="<?= htmlspecialchars($filters['search']) ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Materials Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($materials)): ?>
        <div class="text-center py-5">
            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
            <p class="text-muted"><?= __('materials.no_materials_found') ?></p>
            <a href="?route=/materials/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= __('materials.new_material') ?>
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('materials.name') ?></th>
                        <th><?= __('materials.category') ?></th>
                        <th><?= __('common.price') ?></th>
                        <th><?= __('materials.stock') ?></th>
                        <th><?= __('materials.unit') ?></th>
                        <th><?= __('materials.supplier') ?></th>
                        <th><?= __('common.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $material): ?>
                    <?php
                        $isLowStock = $material['current_stock'] <= $material['min_stock'];
                        $isOutOfStock = $material['current_stock'] == 0;
                    ?>
                    <tr class="<?= $isOutOfStock ? 'table-danger' : ($isLowStock ? 'table-warning' : '') ?>">
                        <td>
                            <strong><?= htmlspecialchars($material['name']) ?></strong>
                            <?php if ($material['description']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($material['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $categories[$material['category']] ?? $material['category'] ?></td>
                        <td><?= formatCurrency($material['unit_price']) ?></td>
                        <td>
                            <strong><?= number_format($material['current_stock'], 2, ',', '.') ?></strong>
                            <?php if ($isOutOfStock): ?>
                            <br><span class="badge bg-danger"><?= __('materials.out_of_stock') ?></span>
                            <?php elseif ($isLowStock): ?>
                            <br><span class="badge bg-warning"><?= __('materials.low') ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($material['unit']) ?></td>
                        <td><?= htmlspecialchars($material['supplier'] ?: '-') ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="?route=/materials/edit&id=<?= $material['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="<?= __('common.edit') ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $material['id'] ?>)" title="<?= __('common.delete') ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?route=/materials&page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= ($i === $pagination['current_page']) ? 'active' : '' ?>">
                    <a class="page-link" href="?route=/materials&page=<?= $i ?>&<?= http_build_query($filters) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?route=/materials&page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="index.php?route=/materials/delete">
    <input type="hidden" name="id" id="deleteMaterialId">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<script>
function confirmDelete(materialId) {
    if (confirm('<?= __('materials.confirm_delete') ?>')) {
        document.getElementById('deleteMaterialId').value = materialId;
        document.getElementById('deleteForm').submit();
    }
}
</script>
