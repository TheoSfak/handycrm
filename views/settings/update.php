<?php
require_once 'classes/UpdateChecker.php';

$updateChecker = new UpdateChecker();
$currentVersion = $updateChecker->getCurrentVersion();
$updateAvailable = $updateChecker->checkForUpdates();
$updateInfo = $updateChecker->getUpdateInfo();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-sync-alt"></i> Ενημερώσεις Συστήματος</h2>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Current Version Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle"></i> Τρέχουσα Έκδοση
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Έκδοση:</strong> v<?= $currentVersion ?></p>
                                    <p><strong>Εγκατάσταση:</strong> HandyCRM Professional</p>
                                    <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-success">Ενεργό</span>
                                    </p>
                                    <p><strong>Τελευταίος Έλεγχος:</strong> 
                                        <?= isset($_SESSION['last_update_check']) ? date('d/m/Y H:i', $_SESSION['last_update_check']) : 'Ποτέ' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($updateAvailable && $updateInfo): ?>
                        <!-- Update Available Card -->
                        <div class="card border-success mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-cloud-download-alt"></i> 
                                    Νέα Ενημέρωση Διαθέσιμη!
                                </h5>
                            </div>
                            <div class="card-body">
                                <h4>Έκδοση <?= htmlspecialchars($updateInfo['version']) ?></h4>
                                <p class="text-muted">
                                    Κυκλοφόρησε: <?= date('d/m/Y', strtotime($updateInfo['published_at'])) ?>
                                </p>
                                
                                <div class="alert alert-info">
                                    <h6>Νέα Χαρακτηριστικά:</h6>
                                    <div class="release-notes">
                                        <?= nl2br(htmlspecialchars($updateInfo['release_notes'])) ?>
                                    </div>
                                </div>

                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Πριν την Ενημέρωση:</h6>
                                    <ol class="mb-0">
                                        <li>Κάντε backup τη βάση δεδομένων</li>
                                        <li>Κάντε backup τα αρχεία σας</li>
                                        <li>Ελέγξτε ότι έχετε πρόσβαση FTP</li>
                                        <li>Διαβάστε τις σημειώσεις έκδοσης</li>
                                    </ol>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="<?= htmlspecialchars($updateInfo['release_url']) ?>" 
                                       target="_blank" 
                                       class="btn btn-success">
                                        <i class="fas fa-download"></i> Λήψη από GitHub
                                    </a>
                                    <a href="<?= htmlspecialchars($updateInfo['release_url']) ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-secondary">
                                        <i class="fas fa-book"></i> Οδηγίες Ενημέρωσης
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No Updates Available -->
                        <div class="card border-success mb-4">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Το Σύστημα Είναι Ενημερωμένο!</h4>
                                <p class="text-muted">Χρησιμοποιείτε την τελευταία διαθέσιμη έκδοση.</p>
                                <form method="POST" action="?route=/settings/update" class="mt-3">
                                    <input type="hidden" name="action" value="force_check">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync-alt"></i> Έλεγχος για Ενημερώσεις
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Manual Update Instructions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-book-open"></i> Οδηγίες Χειροκίνητης Ενημέρωσης
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Βήμα 1: Backup</h6>
                            <pre class="bg-light p-3"><code>mysqldump -u username -p database_name > backup.sql</code></pre>
                            
                            <h6>Βήμα 2: Λήψη Νέας Έκδοσης</h6>
                            <ol>
                                <li>Επισκεφθείτε: <a href="https://github.com/<?= $updateChecker->githubRepo ?>/releases" target="_blank">GitHub Releases</a></li>
                                <li>Κατεβάστε την τελευταία έκδοση (ZIP)</li>
                            </ol>

                            <h6>Βήμα 3: Αντικατάσταση Αρχείων</h6>
                            <ol>
                                <li>Εξάγετε το ZIP</li>
                                <li>Ανεβάστε τα νέα αρχεία μέσω FTP</li>
                                <li><strong>ΜΗΝ</strong> αντικαταστήσετε το <code>config/config.php</code></li>
                                <li><strong>ΜΗΝ</strong> αντικαταστήσετε τον φάκελο <code>uploads/</code></li>
                            </ol>

                            <h6>Βήμα 4: Ολοκλήρωση</h6>
                            <ol>
                                <li>Επισκεφθείτε το site σας</li>
                                <li>Ελέγξτε για migration scripts</li>
                                <li>Δοκιμάστε όλες τις λειτουργίες</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- GitHub Info Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">
                                <i class="fab fa-github"></i> GitHub Repository
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>Παρακολουθήστε τις εξελίξεις στο GitHub:</p>
                            <a href="https://github.com/<?= $updateChecker->githubRepo ?>" 
                               target="_blank" 
                               class="btn btn-dark w-100 mb-2">
                                <i class="fab fa-github"></i> View Repository
                            </a>
                            <a href="https://github.com/<?= $updateChecker->githubRepo ?>/releases" 
                               target="_blank" 
                               class="btn btn-outline-dark w-100">
                                <i class="fas fa-tag"></i> All Releases
                            </a>
                        </div>
                    </div>

                    <!-- Changelog Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history"></i> Changelog
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>v1.0.0 <small class="text-muted">(09/01/2025)</small></h6>
                            <ul>
                                <li>Αρχική έκδοση</li>
                                <li>SEO-friendly URLs</li>
                                <li>Διαχείριση πελατών</li>
                                <li>Έργα & Τιμολόγια</li>
                                <li>Προσφορές</li>
                                <li>Dashboard analytics</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.release-notes {
    max-height: 300px;
    overflow-y: auto;
}
</style>