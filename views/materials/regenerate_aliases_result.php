<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Success!</h4>
                <p class="mb-0">Regenerated aliases for <strong><?= $updated ?></strong> materials!</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Generated Aliases</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Material Name</th>
                                    <th>Generated Aliases</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($result['name']) ?></strong></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($result['aliases']) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= BASE_URL ?>/materials" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Materials
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
