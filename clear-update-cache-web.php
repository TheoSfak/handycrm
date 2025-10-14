<?php
/**
 * Clear Update Cache - Web Version
 * Visit this page in your browser to clear update cache
 */

session_start();

// Clear update cache
unset($_SESSION['last_update_check']);
unset($_SESSION['update_available']);
unset($_SESSION['update_info']);

// Load config and checker
require_once 'config/config.php';
require_once 'classes/UpdateChecker.php';

$checker = new UpdateChecker();
$currentVersion = $checker->getCurrentVersion();

// Force fresh check
$updateAvailable = $checker->checkForUpdates();
$updateInfo = $updateAvailable ? $checker->getUpdateInfo() : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Cache Cleared</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card { 
            max-width: 600px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .success-icon { 
            font-size: 64px; 
            color: #28a745; 
        }
        .version-badge { 
            font-size: 24px; 
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body text-center p-5">
                <i class="fas fa-check-circle success-icon mb-4"></i>
                <h1 class="mb-4">Update Cache Cleared!</h1>
                
                <div class="alert alert-info mb-4">
                    <h4><i class="fas fa-info-circle"></i> Current Version</h4>
                    <span class="badge bg-primary version-badge">v<?= $currentVersion ?></span>
                </div>
                
                <?php if ($updateAvailable): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Update Available</h5>
                        <p class="mb-0">Version <?= $updateInfo['version'] ?> is available</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check"></i> Up to Date</h5>
                        <p class="mb-0">You have the latest version!</p>
                    </div>
                <?php endif; ?>
                
                <hr class="my-4">
                
                <div class="d-grid gap-3">
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-home"></i> Go to Dashboard
                    </a>
                    <a href="?route=/settings/update" class="btn btn-outline-secondary">
                        <i class="fas fa-cog"></i> Update Settings
                    </a>
                </div>
                
                <div class="mt-4 text-muted">
                    <small>
                        <i class="fas fa-info-circle"></i> 
                        Session cache has been cleared. The system will now show the correct update status.
                    </small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
