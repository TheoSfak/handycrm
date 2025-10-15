<?php
/**
 * Task Photos Gallery View
 */
require_once 'views/includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">

<style>
.photo-gallery {
    padding: 20px 0;
}

.photo-type-section {
    margin-bottom: 40px;
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.photo-type-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.photo-type-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.3rem;
    font-weight: 600;
}

.photo-type-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.type-before .photo-type-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.type-after .photo-type-icon { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
.type-during .photo-type-icon { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.type-issue .photo-type-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }
.type-other .photo-type-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }

.photo-count {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.photo-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: #f8f9fa;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.photo-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    cursor: pointer;
}

.photo-info {
    padding: 12px;
    background: white;
}

.photo-caption {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 8px;
    min-height: 20px;
}

.photo-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: #999;
}

.photo-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    gap: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-card:hover .photo-actions {
    opacity: 1;
}

.photo-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.95);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    transition: all 0.2s ease;
}

.photo-action-btn:hover {
    transform: scale(1.1);
}

.photo-action-btn.delete {
    color: #dc3545;
}

.upload-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.upload-form {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 15px;
    align-items: end;
}

.upload-dropzone {
    border: 3px dashed rgba(255,255,255,0.5);
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.1);
}

.upload-dropzone:hover {
    border-color: white;
    background: rgba(255,255,255,0.15);
}

.upload-dropzone.dragover {
    border-color: white;
    background: rgba(255,255,255,0.2);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.3;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.85rem;
    color: #666;
    text-transform: uppercase;
}

@media (max-width: 768px) {
    .photos-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .photo-image {
        height: 150px;
    }
    
    .upload-form {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?route=dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php?route=projects/index">Έργα</a></li>
            <li class="breadcrumb-item"><a href="index.php?route=projects/show&id=<?= $project_id ?>">Έργο #<?= $project_id ?></a></li>
            <li class="breadcrumb-item"><a href="index.php?route=projects/<?= $project_id ?>/tasks">Εργασίες</a></li>
            <li class="breadcrumb-item active">Φωτογραφίες</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-2">
                <i class="fas fa-camera"></i>
                Φωτογραφίες Εργασίας
            </h2>
            <p class="text-muted mb-0">
                <?= htmlspecialchars($task['description']) ?>
                <span class="badge bg-info ms-2"><?= date('d/m/Y', strtotime($task['task_date'])) ?></span>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/projects/<?= $project_id ?>/tasks/view/<?= $task['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Πίσω στην Εργασία
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-number"><?= $counts['total'] ?></div>
            <div class="stat-label">Σύνολο</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-primary"><?= $counts['before'] ?></div>
            <div class="stat-label">Πριν</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-success"><?= $counts['after'] ?></div>
            <div class="stat-label">Μετά</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-info"><?= $counts['during'] ?></div>
            <div class="stat-label">Κατά τη διάρκεια</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-warning"><?= $counts['issue'] ?></div>
            <div class="stat-label">Προβλήματα</div>
        </div>
    </div>

    <!-- Upload Section -->
    <div class="upload-section">
        <h4 class="mb-3"><i class="fas fa-cloud-upload-alt"></i> Ανέβασμα Φωτογραφιών</h4>
        <form action="<?= BASE_URL ?>/projects/<?= $project_id ?>/tasks/<?= $task['id'] ?>/photos/upload" 
              method="POST" enctype="multipart/form-data" id="uploadForm">
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Τύπος Φωτογραφίας</label>
                    <select name="photo_type" class="form-select" required>
                        <option value="before">Πριν</option>
                        <option value="during">Κατά τη διάρκεια</option>
                        <option value="after">Μετά</option>
                        <option value="issue">Πρόβλημα/Ζημιά</option>
                        <option value="other">Άλλο</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Επεξήγηση (προαιρετικό)</label>
                    <input type="text" name="caption" class="form-control" 
                           placeholder="π.χ. Κεντρικός πίνακας πριν την επισκευή">
                </div>
            </div>
            
            <div class="upload-dropzone" id="dropzone">
                <i class="fas fa-images fa-3x mb-3" style="opacity: 0.7;"></i>
                <h5>Σύρετε φωτογραφίες εδώ ή κάντε κλικ για επιλογή</h5>
                <p class="mb-2">Υποστήριξη πολλαπλών φωτογραφιών</p>
                <small>Μέγιστο μέγεθος: 10MB ανά φωτογραφία • Μορφές: JPG, PNG, GIF, WebP</small>
                <input type="file" name="photos[]" id="photoInput" accept="image/*" multiple style="display: none;">
            </div>
            
            <div id="selectedFiles" class="mt-3"></div>
            
            <button type="submit" class="btn btn-light btn-lg mt-3" id="uploadBtn" disabled>
                <i class="fas fa-upload"></i> Ανέβασμα Φωτογραφιών
            </button>
        </form>
    </div>

    <!-- Photo Gallery -->
    <div class="photo-gallery">
        <?php 
        $typeLabels = [
            'before' => ['label' => 'Πριν', 'icon' => 'fa-image'],
            'during' => ['label' => 'Κατά τη Διάρκεια', 'icon' => 'fa-hammer'],
            'after' => ['label' => 'Μετά', 'icon' => 'fa-check-circle'],
            'issue' => ['label' => 'Προβλήματα/Ζημιές', 'icon' => 'fa-exclamation-triangle'],
            'other' => ['label' => 'Άλλες', 'icon' => 'fa-ellipsis-h']
        ];
        
        foreach ($typeLabels as $type => $info):
            if (empty($photos[$type])) continue;
        ?>
        
        <div class="photo-type-section type-<?= $type ?>">
            <div class="photo-type-header">
                <div class="photo-type-title">
                    <div class="photo-type-icon">
                        <i class="fas <?= $info['icon'] ?>"></i>
                    </div>
                    <span><?= $info['label'] ?></span>
                </div>
                <span class="photo-count"><?= count($photos[$type]) ?> <?= count($photos[$type]) == 1 ? 'φωτογραφία' : 'φωτογραφίες' ?></span>
            </div>
            
            <div class="photos-grid">
                <?php foreach ($photos[$type] as $photo): ?>
                <div class="photo-card">
                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($photo['file_path']) ?>" data-lightbox="task-<?= $task['id'] ?>" 
                       data-title="<?= htmlspecialchars($photo['caption'] ?: $info['label']) ?>">
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($photo['file_path']) ?>" 
                             alt="<?= htmlspecialchars($photo['caption']) ?>" 
                             class="photo-image">
                    </a>
                    
                    <div class="photo-actions">
                        <form action="<?= BASE_URL ?>/projects/<?= $project_id ?>/tasks/<?= $task['id'] ?>/photos/<?= $photo['id'] ?>/delete" 
                              method="POST" style="display: inline;" 
                              onsubmit="return confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή τη φωτογραφία;');">
                            <button type="submit" class="photo-action-btn delete" title="Διαγραφή">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="photo-info">
                        <div class="photo-caption">
                            <?= htmlspecialchars($photo['caption']) ?: '<em class="text-muted">Χωρίς επεξήγηση</em>' ?>
                        </div>
                        <div class="photo-meta">
                            <span>
                                <i class="fas fa-user"></i> 
                                <?= htmlspecialchars($photo['first_name'] . ' ' . $photo['last_name']) ?>
                            </span>
                            <span>
                                <i class="fas fa-clock"></i> 
                                <?= date('d/m/Y H:i', strtotime($photo['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php endforeach; ?>
        
        <?php if ($counts['total'] == 0): ?>
        <div class="empty-state">
            <i class="fas fa-camera"></i>
            <h4>Δεν υπάρχουν φωτογραφίες</h4>
            <p>Ανεβάστε τις πρώτες φωτογραφίες για αυτή την εργασία</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

<script>
// Drag and drop functionality
const dropzone = document.getElementById('dropzone');
const photoInput = document.getElementById('photoInput');
const selectedFiles = document.getElementById('selectedFiles');
const uploadBtn = document.getElementById('uploadBtn');

dropzone.addEventListener('click', () => photoInput.click());

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.classList.add('dragover');
});

dropzone.addEventListener('dragleave', () => {
    dropzone.classList.remove('dragover');
});

dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    photoInput.files = e.dataTransfer.files;
    displaySelectedFiles();
});

photoInput.addEventListener('change', displaySelectedFiles);

function displaySelectedFiles() {
    const files = photoInput.files;
    
    if (files.length === 0) {
        selectedFiles.innerHTML = '';
        uploadBtn.disabled = true;
        return;
    }
    
    uploadBtn.disabled = false;
    selectedFiles.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-check-circle"></i>
            <strong>${files.length}</strong> φωτογραφί${files.length === 1 ? 'α' : 'ες'} επιλέχθηκ${files.length === 1 ? 'ε' : 'αν'}
        </div>
    `;
}

// Lightbox configuration
lightbox.option({
    'resizeDuration': 200,
    'wrapAround': true,
    'albumLabel': 'Φωτογραφία %1 από %2'
});
</script>

<?php require_once 'views/includes/footer.php'; ?>
