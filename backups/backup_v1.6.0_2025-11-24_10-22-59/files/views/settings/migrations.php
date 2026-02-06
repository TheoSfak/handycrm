<?php
/**
 * Database Migrations Status Page
 * Shows executed and pending migrations
 */

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ?route=/login');
    exit;
}

require_once 'classes/AutoMigration.php';
require_once 'classes/Database.php';

$db = new Database();
$autoMigration = new AutoMigration($db);

// Get migration status
$executedMigrations = $autoMigration->getExecutedMigrations();
$pendingMigrations = [];

// Get all migration files
$migrationsPath = __DIR__ . '/../../migrations/';
$allMigrations = glob($migrationsPath . '*.sql');
$executedFiles = array_column($executedMigrations, 'migration');

foreach ($allMigrations as $file) {
    $filename = basename($file);
    if (!in_array($filename, $executedFiles)) {
        $pendingMigrations[] = $filename;
    }
}

// Handle manual migration run
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migrations'])) {
    $results = $autoMigration->checkAndRun();
    
    if ($results['executed'] > 0) {
        $successMsg = "Successfully executed {$results['executed']} migration(s)!";
    } elseif (empty($pendingMigrations)) {
        $infoMsg = "No pending migrations to execute.";
    }
    
    if (!empty($results['errors'])) {
        $errorMsg = "Some migrations had errors. Check error log for details.";
    }
    
    // Refresh data
    $executedMigrations = $autoMigration->getExecutedMigrations();
    $pendingMigrations = [];
    
    $allMigrations = glob($migrationsPath . '*.sql');
    $executedFiles = array_column($executedMigrations, 'migration');
    
    foreach ($allMigrations as $file) {
        $filename = basename($file);
        if (!in_array($filename, $executedFiles)) {
            $pendingMigrations[] = $filename;
        }
    }
}

require_once 'views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-database"></i> Database Migrations</h2>
                    <p class="text-muted mb-0">Status of database schema updates</p>
                </div>
                <a href="?route=/settings" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Settings
                </a>
            </div>

            <?php if (isset($successMsg)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMsg)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($errorMsg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($infoMsg)): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <i class="fas fa-info-circle"></i> <?= htmlspecialchars($infoMsg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Pending Migrations -->
            <?php if (!empty($pendingMigrations)): ?>
                <div class="card border-warning mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Pending Migrations (<?= count($pendingMigrations) ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">The following migrations have not been executed yet:</p>
                        <ul class="list-group mb-3">
                            <?php foreach ($pendingMigrations as $migration): ?>
                                <li class="list-group-item">
                                    <i class="fas fa-clock text-warning"></i> <?= htmlspecialchars($migration) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <form method="POST" onsubmit="return confirm('Are you sure you want to run pending migrations? Make sure you have a backup!');">
                            <button type="submit" name="run_migrations" class="btn btn-warning">
                                <i class="fas fa-play"></i> Run Pending Migrations Now
                            </button>
                        </form>
                        
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Migrations run automatically on page load. 
                            Use this button only if you want to force execution now.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <strong>All migrations are up to date!</strong> No pending migrations.
                </div>
            <?php endif; ?>

            <!-- Executed Migrations -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle"></i> Executed Migrations (<?= count($executedMigrations) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($executedMigrations)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file-code"></i> Migration File</th>
                                        <th><i class="fas fa-calendar"></i> Executed At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($executedMigrations as $migration): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-check text-success"></i> 
                                                <?= htmlspecialchars($migration['migration']) ?>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i:s', strtotime($migration['executed_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No migrations have been executed yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- How It Works -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> How Auto-Migrations Work</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">
                            <strong>Automatic Execution:</strong> Every time you load the application, 
                            it checks for pending migrations and runs them automatically in the background.
                        </li>
                        <li class="mb-2">
                            <strong>No Duplicates:</strong> The system tracks which migrations have been executed 
                            in the <code>migrations</code> table to prevent running them twice.
                        </li>
                        <li class="mb-2">
                            <strong>Error Resilient:</strong> If a table or column already exists, 
                            the migration silently skips that step and continues.
                        </li>
                        <li class="mb-2">
                            <strong>Upgrade Safe:</strong> When you upgrade HandyCRM to a new version, 
                            just replace the files - migrations run automatically on next page load!
                        </li>
                        <li class="mb-0">
                            <strong>Fresh Installs:</strong> New installations via <code>install.php</code> 
                            run all migrations automatically during setup.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/includes/footer.php'; ?>
