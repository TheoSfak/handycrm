<?php
require_once 'classes/UpdateChecker.php';
require_once 'classes/BackupManager.php';
require_once 'classes/VersionManager.php';

$updateChecker = new UpdateChecker();
$backupManager = new BackupManager();
$versionManager = new VersionManager();
$currentVersion = $updateChecker->getCurrentVersion();

// Handle actions
$actionMessage = '';
$actionType = '';

// Create backup
if (isset($_POST['create_backup'])) {
    $result = $backupManager->createBackup($currentVersion);
    $actionMessage = $result['message'];
    $actionType = $result['success'] ? 'success' : 'danger';
}

// Delete backup
if (isset($_POST['delete_backup']) && isset($_POST['backup_name'])) {
    $result = $backupManager->deleteBackup($_POST['backup_name']);
    $actionMessage = $result['message'];
    $actionType = $result['success'] ? 'success' : 'danger';
}

// Restore from backup
if (isset($_POST['restore_backup']) && isset($_POST['backup_name'])) {
    $result = $backupManager->restoreBackup($_POST['backup_name']);
    $actionMessage = $result['message'];
    $actionType = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        // Reload page after successful restore
        echo '<meta http-equiv="refresh" content="2">';
    }
}

// Automated restore from GitHub version
if (isset($_POST['restore_version']) && isset($_POST['restore_version_number'])) {
    set_time_limit(300); // Allow 5 minutes for download and installation
    
    $result = $versionManager->installVersion(
        $_POST['restore_version'],
        $_POST['restore_version_number']
    );
    
    $actionMessage = $result['message'];
    $actionType = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        // Update current version and reload
        echo '<meta http-equiv="refresh" content="3;url=' . $_SERVER['PHP_SELF'] . '">';
    }
}

// Automated update to newer version
if (isset($_POST['install_update']) && isset($_POST['update_url']) && isset($_POST['update_version'])) {
    set_time_limit(300); // Allow 5 minutes for download and installation
    
    $result = $versionManager->installVersion(
        $_POST['update_url'],
        $_POST['update_version']
    );
    
    $actionMessage = $result['message'];
    $actionType = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        // Update current version and reload
        echo '<meta http-equiv="refresh" content="3;url=' . $_SERVER['PHP_SELF'] . '">';
    }
}

// Only check for updates if explicitly requested
$updateAvailable = false;
$updateInfo = null;

if (isset($_POST['check_update']) || isset($_GET['auto_check'])) {
    $updateAvailable = $updateChecker->checkForUpdates();
    $updateInfo = $updateChecker->getUpdateInfo();
}

// Get version history
$versionHistory = $updateChecker->getAllVersions();

// Get available backups
$availableBackups = $backupManager->getBackups();
?>

<style>
/* Clean Modern Design */
.update-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.update-header {
    text-align: center;
    margin-bottom: 3rem;
}

.update-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.update-header p {
    font-size: 1.2rem;
    color: #7f8c8d;
}

/* Version Card */
.version-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 3rem;
    text-align: center;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.version-number {
    font-size: 4rem;
    font-weight: 800;
    margin: 1rem 0;
}

.version-label {
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 3px;
    opacity: 0.9;
}

.version-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.stat-item {
    background: rgba(255, 255, 255, 0.15);
    padding: 1.5rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.stat-item i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.8;
}

.stat-value {
    font-size: 1.3rem;
    font-weight: 700;
    margin-top: 0.5rem;
}

/* Update Card */
.update-card {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    border-radius: 20px;
    padding: 3rem;
    text-align: center;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
}

.update-card .version-number {
    font-size: 4.5rem;
}

.update-card h2 {
    font-size: 2rem;
    margin: 1.5rem 0;
}

.update-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.btn-update {
    padding: 1rem 3rem;
    font-size: 1.2rem;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-primary-update {
    background: white;
    color: #11998e;
}

.btn-primary-update:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    color: #11998e;
}

.btn-outline-update {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-outline-update:hover {
    background: white;
    color: #11998e;
    transform: translateY(-3px);
}

/* Check Update Section */
.check-section {
    background: #f8f9fa;
    border-radius: 20px;
    padding: 4rem;
    text-align: center;
    margin-bottom: 2rem;
}

.check-section i {
    font-size: 5rem;
    color: #667eea;
    margin-bottom: 2rem;
}

.check-section h2 {
    font-size: 2.2rem;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.check-section p {
    font-size: 1.2rem;
    color: #7f8c8d;
    margin-bottom: 2rem;
}

.btn-check {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.2rem 4rem;
    font-size: 1.3rem;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-check:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

/* Success Section */
.success-section {
    background: #f8f9fa;
    border-radius: 20px;
    padding: 4rem;
    text-align: center;
    margin-bottom: 2rem;
    border: 3px solid #28a745;
}

.success-section i {
    font-size: 6rem;
    color: #28a745;
    margin-bottom: 2rem;
}

.success-section h2 {
    font-size: 2.5rem;
    color: #28a745;
    margin-bottom: 1rem;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.info-box {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.info-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.info-box h3 {
    font-size: 1.3rem;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-box h3 i {
    color: #667eea;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #ecf0f1;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    color: #7f8c8d;
    font-size: 1rem;
}

.info-value {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

/* Instructions */
.instructions {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.instructions h3 {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    text-align: center;
}

.instruction-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.step {
    text-align: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
}

.step-number {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.step h4 {
    font-size: 1.1rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.step p {
    font-size: 0.95rem;
    color: #7f8c8d;
    margin: 0;
}

/* Release Notes */
.release-notes {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.release-notes h3 {
    font-size: 1.5rem;
    color: #2c3e50;
    margin-bottom: 1rem;
    text-align: center;
}

.release-content {
    max-height: 300px;
    overflow-y: auto;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 1rem;
    line-height: 1.6;
}

/* Warning Box */
.warning-box {
    background: #fff3cd;
    border-left: 5px solid #ffc107;
    border-radius: 10px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.warning-box h4 {
    color: #856404;
    font-size: 1.3rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.warning-box ul {
    list-style: none;
    padding: 0;
}

.warning-box li {
    padding: 0.5rem 0;
    color: #856404;
    font-size: 1.05rem;
}

.warning-box li i {
    margin-right: 0.5rem;
}

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
}

.float-animation {
    animation: float 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.pulse-animation {
    animation: pulse 2s ease-in-out infinite;
}

/* Back to Dashboard Button */
.btn-back-dashboard {
    position: fixed;
    top: 100px;
    right: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
}

.btn-back-dashboard:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.6);
    color: white;
}

.btn-back-dashboard i {
    font-size: 1.2rem;
}

/* Version History Section */
.version-history-section {
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2rem;
    text-align: center;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.versions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.version-item {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 3px solid transparent;
}

.version-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.version-item.current-version {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
}

.version-item.newer-version {
    border-color: #28a745;
}

.version-item.older-version {
    border-color: #e9ecef;
}

.version-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.version-number-badge {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.version-date {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.version-notes {
    color: #2c3e50;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 1rem;
    min-height: 80px;
}

.version-actions {
    display: flex;
    gap: 0.5rem;
}

.version-actions .btn {
    flex: 1;
}

/* Backup Section */
.backup-section {
    margin-bottom: 3rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    padding: 3rem 2rem;
}

.backup-create {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.backups-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
}

.backup-item {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.backup-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
    border-color: #667eea;
}

.backup-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.backup-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.backup-info {
    flex: 1;
}

.backup-version {
    font-weight: 700;
    color: #2c3e50;
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.backup-date {
    color: #7f8c8d;
    font-size: 0.85rem;
}

.backup-details {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.backup-detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #2c3e50;
}

.backup-actions {
    margin-top: 1rem;
    display: flex;
    gap: 0.5rem;
    justify-content: space-between;
}

.backup-actions form {
    flex: 1;
}

.backup-actions .btn {
    font-weight: 600;
    transition: all 0.3s ease;
}

.backup-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Loading Overlay */
.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.loading-overlay.active {
    display: flex;
}

.loading-content {
    background: white;
    padding: 3rem;
    border-radius: 20px;
    text-align: center;
    max-width: 500px;
}

.loading-spinner {
    width: 80px;
    height: 80px;
    border: 8px solid #f3f3f3;
    border-top: 8px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 2rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.loading-subtext {
    font-size: 1rem;
    color: #7f8c8d;
}

.progress-steps {
    margin-top: 2rem;
    text-align: left;
}

.progress-step {
    padding: 0.5rem 0;
    color: #7f8c8d;
    font-size: 0.9rem;
}

.progress-step.active {
    color: #667eea;
    font-weight: 600;
}

.progress-step.completed {
    color: #28a745;
}

.progress-step i {
    margin-right: 0.5rem;
}
</style>

<script>
// Show loading overlay when submitting version installation
document.addEventListener('DOMContentLoaded', function() {
    const versionForms = document.querySelectorAll('form[method="POST"]');
    
    versionForms.forEach(form => {
        // Check if form has restore_version or install_update
        if (form.querySelector('input[name="restore_version"]') || 
            form.querySelector('input[name="install_update"]')) {
            
            form.addEventListener('submit', function(e) {
                // Show loading overlay
                const overlay = document.getElementById('loadingOverlay');
                if (overlay) {
                    overlay.classList.add('active');
                    
                    // Animate progress steps
                    setTimeout(() => document.querySelector('.step-1')?.classList.add('active'), 500);
                    setTimeout(() => {
                        document.querySelector('.step-1')?.classList.remove('active');
                        document.querySelector('.step-1')?.classList.add('completed');
                        document.querySelector('.step-2')?.classList.add('active');
                    }, 2000);
                    setTimeout(() => {
                        document.querySelector('.step-2')?.classList.remove('active');
                        document.querySelector('.step-2')?.classList.add('completed');
                        document.querySelector('.step-3')?.classList.add('active');
                    }, 5000);
                    setTimeout(() => {
                        document.querySelector('.step-3')?.classList.remove('active');
                        document.querySelector('.step-3')?.classList.add('completed');
                        document.querySelector('.step-4')?.classList.add('active');
                    }, 10000);
                }
            });
        }
    });
});
</script>

<div class="update-container">
    <!-- Back to Dashboard Button -->
    <a href="<?= BASE_URL ?>/dashboard" class="btn-back-dashboard">
        <i class="fas fa-arrow-left"></i>
        <span>Πίσω στο Dashboard</span>
    </a>

    <!-- Page Header -->
    <div class="update-header">
        <i class="fas fa-sync-alt fa-4x text-primary mb-3 float-animation"></i>
        <h1>Ενημερώσεις Συστήματος</h1>
        <p>Διαχείριση και έλεγχος εκδόσεων HandyCRM</p>
    </div>

    <!-- Current Version -->
    <div class="version-card">
        <div class="version-label">Τρέχουσα Έκδοση</div>
        <div class="version-number">v<?= $currentVersion ?></div>
        
        <div class="version-stats">
            <div class="stat-item">
                <i class="fas fa-check-circle"></i>
                <div class="stat-label">Status</div>
                <div class="stat-value">Active</div>
            </div>
            <div class="stat-item">
                <i class="fas fa-code"></i>
                <div class="stat-label">PHP</div>
                <div class="stat-value"><?= phpversion() ?></div>
            </div>
            <div class="stat-item">
                <i class="fas fa-database"></i>
                <div class="stat-label">Database</div>
                <div class="stat-value">MySQL</div>
            </div>
            <div class="stat-item">
                <i class="fas fa-clock"></i>
                <div class="stat-label">Τελευταίος Έλεγχος</div>
                <div class="stat-value"><?= isset($_SESSION['last_update_check']) ? date('d/m H:i', $_SESSION['last_update_check']) : 'Ποτέ' ?></div>
            </div>
        </div>
    </div>

    <?php if (!isset($_POST['check_update']) && !isset($_GET['auto_check'])): ?>
        <!-- Check for Updates Button -->
        <div class="check-section">
            <i class="fas fa-search pulse-animation"></i>
            <h2>Έλεγχος για Ενημερώσεις</h2>
            <p>Ελέγξτε αν υπάρχουν νέες εκδόσεις διαθέσιμες στο GitHub repository</p>
            <form method="POST">
                <button type="submit" name="check_update" class="btn-check">
                    <i class="fas fa-sync-alt me-2"></i> Έλεγχος Τώρα
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($updateAvailable && $updateInfo): ?>
        <!-- Update Available -->
        <div class="update-card">
            <i class="fas fa-rocket fa-4x mb-3 float-animation"></i>
            <div class="version-label">Νέα Έκδοση Διαθέσιμη</div>
            <div class="version-number">v<?= htmlspecialchars($updateInfo['version']) ?></div>
            <h2><i class="fas fa-gift me-2"></i> Ενημέρωση Διαθέσιμη!</h2>
            <p style="font-size: 1.2rem; opacity: 0.9;">
                <i class="fas fa-calendar-alt me-2"></i>
                Κυκλοφόρησε: <?= date('d/m/Y H:i', strtotime($updateInfo['published_at'])) ?>
            </p>
            
            <div class="update-buttons">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="install_update" value="1">
                    <input type="hidden" name="update_url" value="<?= htmlspecialchars($updateInfo['download_url']) ?>">
                    <input type="hidden" name="update_version" value="<?= htmlspecialchars($updateInfo['version']) ?>">
                    <button type="submit" class="btn-update btn-primary-update" onclick="return confirm('Θα δημιουργηθεί αυτόματα backup και θα εγκατασταθεί η νέα έκδοση. Συνέχεια;');">
                        <i class="fas fa-rocket me-2"></i> Εγκατάσταση Αυτόματα
                    </button>
                </form>
                <a href="<?= htmlspecialchars($updateInfo['download_url']) ?>" class="btn-update btn-outline-update">
                    <i class="fas fa-download me-2"></i> Μόνο Λήψη
                </a>
                <a href="<?= htmlspecialchars($updateInfo['release_url']) ?>" target="_blank" class="btn-update btn-outline-update">
                    <i class="fab fa-github me-2"></i> GitHub
                </a>
            </div>
        </div>

        <!-- Release Notes -->
        <div class="release-notes">
            <h3><i class="fas fa-list-ul me-2"></i> Τι Νέο Υπάρχει</h3>
            <div class="release-content">
                <?= nl2br(htmlspecialchars($updateInfo['release_notes'] ?? 'Δείτε τις λεπτομέρειες στο GitHub.')) ?>
            </div>
        </div>

        <!-- Warning -->
        <div class="warning-box">
            <h4><i class="fas fa-exclamation-triangle"></i> Σημαντικές Οδηγίες</h4>
            <ul>
                <li><i class="fas fa-check-circle text-success"></i> Κάντε backup της βάσης δεδομένων</li>
                <li><i class="fas fa-check-circle text-success"></i> Κάντε αντίγραφο όλων των αρχείων</li>
                <li><i class="fas fa-times-circle text-danger"></i> <strong>ΜΗΝ</strong> αντικαταστήσετε το <code>config/config.php</code></li>
                <li><i class="fas fa-times-circle text-danger"></i> <strong>ΜΗΝ</strong> αντικαταστήσετε τον φάκελο <code>uploads/</code></li>
            </ul>
        </div>

        <!-- Recheck Button -->
        <div class="text-center mb-4">
            <form method="POST" class="d-inline-block">
                <button type="submit" name="check_update" class="btn-check">
                    <i class="fas fa-redo me-2"></i> Έλεγχος Ξανά
                </button>
            </form>
        </div>

    <?php elseif (isset($_POST['check_update']) || isset($_GET['auto_check'])): ?>
        <!-- No Updates -->
        <div class="success-section">
            <i class="fas fa-check-circle"></i>
            <h2>Είστε Ενημερωμένοι!</h2>
            <p style="font-size: 1.2rem; color: #6c757d; margin-bottom: 2rem;">
                Χρησιμοποιείτε την πιο πρόσφατη έκδοση του HandyCRM
            </p>
            <form method="POST" class="d-inline-block">
                <button type="submit" name="check_update" class="btn-check" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="fas fa-redo me-2"></i> Έλεγχος Ξανά
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($actionMessage): ?>
        <!-- Action Message -->
        <div class="alert alert-<?= $actionType ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $actionType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
            <?= htmlspecialchars($actionMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Version History -->
    <?php if (!empty($versionHistory)): ?>
    <div class="version-history-section">
        <h2 class="section-title">
            <i class="fas fa-history me-2"></i> Ιστορικό Εκδόσεων
        </h2>
        <p class="text-center text-muted mb-4">Όλες οι διαθέσιμες εκδόσεις από το GitHub repository</p>
        
        <div class="versions-grid">
            <?php foreach ($versionHistory as $version): ?>
            <div class="version-item <?= $version['is_current'] ? 'current-version' : ($version['is_newer'] ? 'newer-version' : 'older-version') ?>">
                <div class="version-header">
                    <div class="version-number-badge">
                        <i class="fas fa-<?= $version['is_current'] ? 'star' : ($version['is_newer'] ? 'arrow-up' : 'arrow-down') ?>"></i>
                        v<?= htmlspecialchars($version['version']) ?>
                    </div>
                    <?php if ($version['is_current']): ?>
                        <span class="badge bg-primary">Τρέχουσα</span>
                    <?php elseif ($version['is_newer']): ?>
                        <span class="badge bg-success">Νεότερη</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Παλαιότερη</span>
                    <?php endif; ?>
                </div>
                
                <div class="version-date">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <?= date('d/m/Y H:i', strtotime($version['published_at'])) ?>
                </div>
                
                <div class="version-notes">
                    <?php 
                    $notes = $version['release_notes'] ?? 'Χωρίς σημειώσεις';
                    echo nl2br(htmlspecialchars(substr($notes, 0, 150)));
                    if (strlen($notes) > 150) echo '...';
                    ?>
                </div>
                
                <div class="version-actions">
                    <a href="<?= htmlspecialchars($version['release_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fab fa-github me-1"></i> GitHub
                    </a>
                    <?php if (!$version['is_current']): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('<?= $version['is_newer'] ? 'Αυτόματη εγκατάσταση της έκδοσης ' : 'Επιστροφή στην έκδοση ' ?><?= htmlspecialchars($version['version']) ?>? Θα δημιουργηθεί backup πρώτα. Συνέχεια;');">
                            <input type="hidden" name="restore_version" value="<?= htmlspecialchars($version['download_url']) ?>">
                            <input type="hidden" name="restore_version_number" value="<?= htmlspecialchars($version['version']) ?>">
                            <button type="submit" class="btn btn-sm <?= $version['is_newer'] ? 'btn-success' : 'btn-warning' ?>">
                                <i class="fas fa-<?= $version['is_newer'] ? 'arrow-up' : 'undo' ?> me-1"></i>
                                <?= $version['is_newer'] ? 'Εγκατάσταση' : 'Restore' ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Backup Management -->
    <div class="backup-section">
        <h2 class="section-title">
            <i class="fas fa-shield-alt me-2"></i> Διαχείριση Αντιγράφων Ασφαλείας
        </h2>
        <p class="text-center text-muted mb-4">Δημιουργήστε και διαχειριστείτε αντίγραφα ασφαλείας του συστήματος</p>
        
        <div class="backup-create mb-4">
            <form method="POST" class="text-center">
                <button type="submit" name="create_backup" class="btn btn-lg btn-success">
                    <i class="fas fa-plus-circle me-2"></i> Δημιουργία Νέου Backup
                </button>
                <p class="text-muted mt-2 small">
                    <i class="fas fa-info-circle me-1"></i> 
                    Θα δημιουργηθεί πλήρες αντίγραφο των αρχείων και της βάσης δεδομένων
                </p>
            </form>
        </div>

        <?php if (!empty($availableBackups)): ?>
        <div class="backups-grid">
            <?php foreach ($availableBackups as $backup): ?>
            <div class="backup-item">
                <div class="backup-header">
                    <div class="backup-icon">
                        <i class="fas fa-archive"></i>
                    </div>
                    <div class="backup-info">
                        <div class="backup-version">
                            <i class="fas fa-tag me-1"></i>
                            Version <?= htmlspecialchars($backup['version']) ?>
                        </div>
                        <div class="backup-date">
                            <?= date('d/m/Y H:i', strtotime($backup['created_at'])) ?>
                        </div>
                    </div>
                </div>
                
                <div class="backup-details">
                    <div class="backup-detail-item">
                        <i class="fas fa-hdd text-primary"></i>
                        <span><?= $backup['size'] ?></span>
                    </div>
                    <div class="backup-detail-item">
                        <i class="fas fa-code text-success"></i>
                        <span>PHP <?= $backup['php_version'] ?></span>
                    </div>
                </div>
                
                <div class="backup-actions">
                    <form method="POST" style="display: inline-block; width: 48%;" onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να επαναφέρετε αυτό το backup? Η τρέχουσα κατάσταση θα αντικατασταθεί.');">
                        <input type="hidden" name="backup_name" value="<?= htmlspecialchars($backup['name']) ?>">
                        <button type="submit" name="restore_backup" class="btn btn-warning btn-sm w-100">
                            <i class="fas fa-undo me-1"></i> Επαναφορά
                        </button>
                    </form>
                    <form method="POST" style="display: inline-block; width: 48%; margin-left: 4%;" onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το backup? Αυτή η ενέργεια δεν μπορεί να αναιρεθεί.');">
                        <input type="hidden" name="backup_name" value="<?= htmlspecialchars($backup['name']) ?>">
                        <button type="submit" name="delete_backup" class="btn btn-danger btn-sm w-100">
                            <i class="fas fa-trash me-1"></i> Διαγραφή
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            Δεν υπάρχουν διαθέσιμα backups. Δημιουργήστε το πρώτο σας!
        </div>
        <?php endif; ?>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <!-- System Info -->
        <div class="info-box">
            <h3><i class="fas fa-server"></i> Πληροφορίες Συστήματος</h3>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-code text-primary"></i> PHP Version</span>
                <span class="info-value"><?= phpversion() ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-database text-success"></i> Database</span>
                <span class="info-value">MySQL</span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="fas fa-server text-warning"></i> Web Server</span>
                <span class="info-value"><?= explode('/', $_SERVER['SERVER_SOFTWARE'])[0] ?? 'Apache' ?></span>
            </div>
        </div>

        <!-- GitHub Links -->
        <div class="info-box">
            <h3><i class="fab fa-github"></i> GitHub Repository</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                <a href="https://github.com/<?= $updateChecker->githubRepo ?>" target="_blank" class="btn btn-dark w-100">
                    <i class="fab fa-github me-2"></i> View Repository
                </a>
                <a href="https://github.com/<?= $updateChecker->githubRepo ?>/releases" target="_blank" class="btn btn-outline-dark w-100">
                    <i class="fas fa-tags me-2"></i> All Releases
                </a>
                <a href="https://github.com/<?= $updateChecker->githubRepo ?>/issues" target="_blank" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-bug me-2"></i> Report Issue
                </a>
            </div>
        </div>
    </div>

    <!-- Update Instructions -->
    <div class="instructions">
        <h3><i class="fas fa-graduation-cap me-2"></i> Οδηγίες Ενημέρωσης</h3>
        <div class="instruction-steps">
            <div class="step">
                <div class="step-number">1</div>
                <h4>Backup</h4>
                <p>Εξαγωγή βάσης δεδομένων</p>
                <code style="font-size: 0.85rem; display: block; margin-top: 0.5rem;">mysqldump -u user -p db > backup.sql</code>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h4>Κατέβασμα</h4>
                <p>Λήψη νέας έκδοσης από GitHub Releases</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h4>Αντικατάσταση</h4>
                <p>Ανέβασμα αρχείων μέσω FTP</p>
                <small class="text-danger d-block mt-1">Εξαιρέστε: config/ & uploads/</small>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h4>Έλεγχος</h4>
                <p>Επαλήθευση λειτουργίας του συστήματος</p>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-text">Εγκατάσταση σε εξέλιξη...</div>
        <div class="loading-subtext">Παρακαλώ μην κλείσετε τη σελίδα</div>
        
        <div class="progress-steps">
            <div class="progress-step step-1">
                <i class="fas fa-circle-notch fa-spin"></i> Δημιουργία backup...
            </div>
            <div class="progress-step step-2">
                <i class="fas fa-circle-notch"></i> Λήψη έκδοσης από GitHub...
            </div>
            <div class="progress-step step-3">
                <i class="fas fa-circle-notch"></i> Εξαγωγή και εγκατάσταση αρχείων...
            </div>
            <div class="progress-step step-4">
                <i class="fas fa-circle-notch"></i> Ολοκλήρωση...
            </div>
        </div>
    </div>
</div>
