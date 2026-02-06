<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-check"></i> <?= __('menu.appointments') ?></h2>
    <div>
        <a href="?route=/appointments/calendar" class="btn btn-info me-2">
            <i class="fas fa-calendar-alt"></i> <?= __('appointments.calendar') ?>
        </a>
        <a href="?route=/appointments/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?= __('appointments.new_appointment') ?>
        </a>
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

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="?" class="row g-3">
            <input type="hidden" name="route" value="/appointments">
            
            <div class="col-md-3">
                <label class="form-label"><?= __('appointments.status') ?></label>
                <select name="status" class="form-select">
                    <option value=""><?= __('appointments.all') ?></option>
                    <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['status'] === $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label"><?= __('appointments.technician') ?></label>
                <select name="technician" class="form-select">
                    <option value=""><?= __('appointments.all') ?></option>
                    <?php foreach ($technicians as $tech): ?>
                    <option value="<?= $tech['id'] ?>" <?= ($filters['technician'] == $tech['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= __('appointments.from') ?></label>
                <input type="date" name="date_from" class="form-control" 
                       value="<?= htmlspecialchars($filters['date_from']) ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= __('appointments.to') ?></label>
                <input type="date" name="date_to" class="form-control" 
                       value="<?= htmlspecialchars($filters['date_to']) ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><?= __('appointments.search') ?></label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="<?= __('appointments.search_placeholder') ?>" 
                           value="<?= htmlspecialchars($filters['search']) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Appointments Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($appointments)): ?>
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <p class="text-muted"><?= __('appointments.no_appointments_found') ?></p>
            <a href="?route=/appointments/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= __('appointments.create_new_appointment') ?>
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('appointments.date_time') ?></th>
                        <th><?= __('appointments.appointment_title') ?></th>
                        <th><?= __('appointments.customer') ?></th>
                        <th><?= __('appointments.technician') ?></th>
                        <th><?= __('appointments.duration') ?></th>
                        <th><?= __('appointments.status') ?></th>
                        <th><?= __('appointments.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                    <?php
                        $customerName = $appointment['customer_type'] === 'company' && !empty($appointment['customer_company_name']) 
                            ? $appointment['customer_company_name'] 
                            : $appointment['customer_first_name'] . ' ' . $appointment['customer_last_name'];
                        
                        // Status badge colors
                        $statusColors = [
                            'scheduled' => 'info',
                            'confirmed' => 'success',
                            'in_progress' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'no_show' => 'warning'
                        ];
                        $statusColor = $statusColors[$appointment['status']] ?? 'secondary';
                        
                        // Check if appointment is today
                        $isToday = date('Y-m-d', strtotime($appointment['appointment_date'])) === date('Y-m-d');
                        
                        // Check if appointment is past
                        $isPast = strtotime($appointment['appointment_date']) < time();
                    ?>
                    <tr class="<?= $isToday ? 'table-warning' : '' ?>">
                        <td>
                            <strong><?= date('d/m/Y', strtotime($appointment['appointment_date'])) ?></strong><br>
                            <small class="text-muted"><?= date('H:i', strtotime($appointment['appointment_date'])) ?></small>
                            <?php if ($isToday): ?>
                            <br><span class="badge bg-warning text-dark"><?= __('appointments.today') ?></span>
                            <?php elseif ($isPast && $appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                            <br><span class="badge bg-danger"><?= __('appointments.past') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>" 
                               class="text-decoration-none fw-bold">
                                <?= htmlspecialchars($appointment['title']) ?>
                            </a>
                            <?php if ($appointment['project_title']): ?>
                            <br><small class="text-muted">
                                <i class="fas fa-project-diagram"></i> 
                                <?= htmlspecialchars($appointment['project_title']) ?>
                            </small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($customerName) ?></td>
                        <td><?= htmlspecialchars($appointment['tech_first_name'] . ' ' . $appointment['tech_last_name']) ?></td>
                        <td><?= $appointment['duration_minutes'] ?> <?= __('appointments.minutes') ?></td>
                        <td>
                            <span class="badge bg-<?= $statusColor ?>">
                                <?= $statuses[$appointment['status']] ?? $appointment['status'] ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>" 
                                   class="btn btn-sm btn-info" title="<?= __('appointments.view') ?>">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="?route=/appointments/edit&id=<?= $appointment['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="<?= __('appointments.edit') ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="confirmDelete(<?= $appointment['id'] ?>)" title="<?= __('appointments.delete') ?>">
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
                    <a class="page-link" href="?route=/appointments&page=<?= $pagination['current_page'] - 1 ?>&<?= http_build_query($filters) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= ($i === $pagination['current_page']) ? 'active' : '' ?>">
                    <a class="page-link" href="?route=/appointments&page=<?= $i ?>&<?= http_build_query($filters) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?route=/appointments&page=<?= $pagination['current_page'] + 1 ?>&<?= http_build_query($filters) ?>">
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

<!-- Delete Confirmation Modal -->
<form id="deleteForm" method="POST" action="index.php?route=/appointments/delete">
    <input type="hidden" name="id" id="deleteAppointmentId">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
</form>

<script>
function confirmDelete(appointmentId) {
    if (confirm('<?= __('appointments.confirm_delete') ?>')) {
        document.getElementById('deleteAppointmentId').value = appointmentId;
        document.getElementById('deleteForm').submit();
    }
}
</script>
