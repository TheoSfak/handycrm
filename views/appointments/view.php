<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check"></i> Προβολή Ραντεβού</h2>
    <div>
        <a href="?route=/appointments" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Πίσω στη Λίστα
        </a>
        <a href="?route=/appointments/edit&id=<?= $appointment['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Επεξεργασία
        </a>
        <button type="button" class="btn btn-danger" onclick="confirmDeleteAppointment()">
            <i class="fas fa-trash"></i> Διαγραφή
        </button>
    </div>
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

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Στοιχεία Ραντεβού
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Τίτλος</label>
                        <p class="mb-0"><strong><?= htmlspecialchars($appointment['title']) ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Κατάσταση</label>
                        <p class="mb-0">
                            <?php
                            $statusColors = [
                                'scheduled' => 'primary',
                                'confirmed' => 'success',
                                'in_progress' => 'warning',
                                'completed' => 'secondary',
                                'cancelled' => 'danger',
                                'no_show' => 'dark'
                            ];
                            $statuses = [
                                'scheduled' => 'Προγραμματισμένο',
                                'confirmed' => 'Επιβεβαιωμένο',
                                'in_progress' => 'Σε εξέλιξη',
                                'completed' => 'Ολοκληρωμένο',
                                'cancelled' => 'Ακυρωμένο',
                                'no_show' => 'Δεν εμφανίστηκε'
                            ];
                            $statusColor = $statusColors[$appointment['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= $statuses[$appointment['status']] ?? $appointment['status'] ?>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Ημερομηνία & Ώρα</label>
                        <p class="mb-0">
                            <i class="fas fa-calendar"></i> 
                            <?= formatDate($appointment['appointment_date'], true) ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Διάρκεια</label>
                        <p class="mb-0">
                            <i class="fas fa-clock"></i> 
                            <?= $appointment['duration_minutes'] ?> λεπτά
                        </p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="text-muted small">Περιγραφή</label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($appointment['description'] ?? '-')) ?></p>
                    </div>
                </div>

                <?php if (!empty($appointment['address'])): ?>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="text-muted small">Διεύθυνση</label>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?= nl2br(htmlspecialchars($appointment['address'])) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($appointment['notes'])): ?>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="text-muted small">Σημειώσεις</label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($appointment['notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small">Υπενθύμιση</label>
                        <p class="mb-0">
                            <?php if ($appointment['reminder_sent']): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Στάλθηκε
                                </span>
                                <?php if ($appointment['reminder_date']): ?>
                                    <br><small class="text-muted">
                                        <?= formatDate($appointment['reminder_date'], true) ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-secondary">Δεν στάλθηκε</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Customer Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user"></i> Πελάτης
                </h6>
            </div>
            <div class="card-body">
                <?php if ($appointment['customer_id']): ?>
                    <p class="mb-2">
                        <strong><?= htmlspecialchars($appointment['customer_name']) ?></strong>
                    </p>
                    <?php if ($appointment['customer_phone']): ?>
                    <p class="mb-1">
                        <i class="fas fa-phone text-muted"></i>
                        <a href="tel:<?= htmlspecialchars($appointment['customer_phone']) ?>">
                            <?= htmlspecialchars($appointment['customer_phone']) ?>
                        </a>
                    </p>
                    <?php endif; ?>
                    <?php if ($appointment['customer_email']): ?>
                    <p class="mb-1">
                        <i class="fas fa-envelope text-muted"></i>
                        <a href="mailto:<?= htmlspecialchars($appointment['customer_email']) ?>">
                            <?= htmlspecialchars($appointment['customer_email']) ?>
                        </a>
                    </p>
                    <?php endif; ?>
                    <a href="?route=/customers/view&id=<?= $appointment['customer_id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-eye"></i> Προβολή Πελάτη
                    </a>
                <?php else: ?>
                    <p class="text-muted">Δεν έχει οριστεί πελάτης</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Technician Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-cog"></i> Τεχνικός
                </h6>
            </div>
            <div class="card-body">
                <?php if ($appointment['technician_id']): ?>
                    <p class="mb-2">
                        <strong>
                            <?= htmlspecialchars($appointment['tech_first_name'] . ' ' . $appointment['tech_last_name']) ?>
                        </strong>
                    </p>
                    <?php if ($appointment['tech_email']): ?>
                    <p class="mb-1">
                        <i class="fas fa-envelope text-muted"></i>
                        <?= htmlspecialchars($appointment['tech_email']) ?>
                    </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Δεν έχει οριστεί τεχνικός</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Info -->
        <?php if ($appointment['project_id']): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-project-diagram"></i> Έργο
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong><?= htmlspecialchars($appointment['project_title']) ?></strong>
                </p>
                <?php if ($appointment['project_description']): ?>
                <p class="mb-1 text-muted small">
                    <?= htmlspecialchars(mb_substr($appointment['project_description'], 0, 100)) ?>
                    <?= mb_strlen($appointment['project_description']) > 100 ? '...' : '' ?>
                </p>
                <?php endif; ?>
                <a href="?route=/projects/show&id=<?= $appointment['project_id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="fas fa-eye"></i> Προβολή Έργου
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Meta Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle"></i> Πληροφορίες
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2 small">
                    <span class="text-muted">Δημιουργήθηκε:</span><br>
                    <?= formatDate($appointment['created_at'], true) ?>
                </p>
                <p class="mb-0 small">
                    <span class="text-muted">Τελευταία ενημέρωση:</span><br>
                    <?= formatDate($appointment['updated_at'], true) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteAppointmentForm" method="POST" action="?route=/appointments/delete">
    <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<script>
function confirmDeleteAppointment() {
    if (confirm('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το ραντεβού;')) {
        document.getElementById('deleteAppointmentForm').submit();
    }
}
</script>
