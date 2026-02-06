<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>
            <i class="fas fa-<?= $customer['customer_type'] === 'company' ? 'building' : 'user' ?>"></i>
            <?php 
            if ($customer['customer_type'] === 'company' && !empty($customer['company_name'])) {
                echo htmlspecialchars($customer['company_name']);
            } else {
                echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']);
            }
            ?>
        </h2>
        <span class="badge <?= $customer['customer_type'] === 'company' ? 'bg-info' : 'bg-primary' ?>">
            <?= $customer['customer_type'] === 'company' ? __('customers.company') : __('customers.individual') ?>
        </span>
        <?php if ($customer['is_active']): ?>
            <span class="badge bg-success"><?= __('customers.active') ?></span>
        <?php else: ?>
            <span class="badge bg-danger"><?= __('customers.inactive') ?></span>
        <?php endif; ?>
    </div>
    <div>
        <a href="?route=/customers/edit&id=<?= $customer['id'] ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> <?= __('customers.edit') ?>
        </a>
        <a href="?route=/customers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?= __('common.back') ?>
        </a>
    </div>
</div>

<div class="row">
    <!-- Customer Information -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> <?= __('customers.customer_info') ?></h5>
            </div>
            <div class="card-body">
                <?php if ($customer['customer_type'] === 'company'): ?>
                    <div class="mb-3">
                        <label class="text-muted small"><?= __('customers.company_name') ?></label>
                        <div><?= htmlspecialchars($customer['company_name']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small"><?= __('customers.contact_person') ?></label>
                        <div><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></div>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <label class="text-muted small"><?= __('customers.full_name') ?></label>
                        <div><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></div>
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="text-muted small"><?= __('customers.phone') ?></label>
                    <div>
                        <a href="tel:<?= htmlspecialchars($customer['phone']) ?>">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone']) ?>
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($customer['mobile'])): ?>
                <div class="mb-3">
                    <label class="text-muted small"><?= __('customers.mobile') ?></label>
                    <div>
                        <a href="tel:<?= htmlspecialchars($customer['mobile']) ?>">
                            <i class="fas fa-mobile-alt"></i> <?= htmlspecialchars($customer['mobile']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customer['email'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Email</label>
                    <div>
                        <a href="mailto:<?= htmlspecialchars($customer['email']) ?>">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($customer['email']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="text-muted small"><?= __('customers.address') ?></label>
                    <div><?= nl2br(htmlspecialchars($customer['address'])) ?></div>
                </div>
                
                <?php if (!empty($customer['city'])): ?>
                <div class="mb-3">
                    <label class="text-muted small"><?= __('customers.city') ?></label>
                    <div><?= htmlspecialchars($customer['city']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customer['postal_code'])): ?>
                <div class="mb-3">
                    <label class="text-muted small"><?= __('customers.postal_code') ?></label>
                    <div><?= htmlspecialchars($customer['postal_code']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customer['tax_id'])): ?>
                <div class="mb-3">
                    <label class="text-muted small"><?= __('customers.tax_id') ?></label>
                    <div><?= htmlspecialchars($customer['tax_id']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customer['notes'])): ?>
                <div class="mb-3">
                    <label class="text-muted small"><?= __('common.notes') ?></label>
                    <div><?= nl2br(htmlspecialchars($customer['notes'])) ?></div>
                </div>
                <?php endif; ?>
                
                <div class="mb-0">
                    <label class="text-muted small">Δημιουργήθηκε</label>
                    <div><?= formatDate($customer['created_at'], true) ?></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> <?= __('customers.quick_actions') ?></h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="?route=/projects/create&customer_id=<?= $customer['id'] ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-project-diagram text-primary"></i> <?= __('customers.new_project') ?>
                </a>
                <a href="?route=/appointments/create&customer_id=<?= $customer['id'] ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar-plus text-success"></i> <?= __('customers.new_appointment') ?>
                </a>
                <a href="?route=/quotes/create&customer_id=<?= $customer['id'] ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-invoice text-info"></i> <?= __('customers.new_quote') ?>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Projects, Appointments, Quotes -->
    <div class="col-lg-8">
        <!-- Projects -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-project-diagram"></i> <?= __('projects.projects') ?></h5>
                <a href="?route=/projects/create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> <?= __('common.new') ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($customer['projects'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Τίτλος</th>
                                    <th><?= __('projects.category') ?></th>
                                    <th>Κατάσταση</th>
                                    <th><?= __('common.date') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customer['projects'] as $project): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/projects/<?= $project['slug'] ?>">
                                            <?= htmlspecialchars($project['title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        $categories = [
                                            'electrical' => __('projects.electrical'),
                                            'plumbing' => __('projects.plumbing'),
                                            'maintenance' => __('projects.maintenance'),
                                            'emergency' => __('projects.emergency')
                                        ];
                                        echo $categories[$project['category']] ?? $project['category'];
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'new' => 'secondary',
                                            'in_progress' => 'primary',
                                            'completed' => 'success',
                                            'invoiced' => 'info',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusLabels = [
                                            'new' => __('projects.new'),
                                            'in_progress' => __('projects.in_progress'),
                                            'completed' => __('projects.completed'),
                                            'invoiced' => __('projects.invoiced'),
                                            'cancelled' => __('projects.cancelled')
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $statusClasses[$project['status']] ?? 'secondary' ?>">
                                            <?= $statusLabels[$project['status']] ?? $project['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($project['created_at']) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/projects/<?= $project['slug'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0"><?= __('customers.no_projects') ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Appointments -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> <?= __('appointments.appointments') ?></h5>
                <a href="?route=/appointments/create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> <?= __('common.new') ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($customer['appointments'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Τίτλος</th>
                                    <th><?= __('common.date') ?></th>
                                    <th><?= __('appointments.technician') ?></th>
                                    <th>Κατάσταση</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customer['appointments'] as $appointment): ?>
                                <tr>
                                    <td>
                                        <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>">
                                            <?= htmlspecialchars($appointment['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= formatDate($appointment['appointment_date'], true) ?></td>
                                    <td><?= htmlspecialchars($appointment['technician_name']) ?></td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'scheduled' => 'secondary',
                                            'confirmed' => 'info',
                                            'in_progress' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            'no_show' => 'warning'
                                        ];
                                        $statusLabels = [
                                            'scheduled' => __('appointments.scheduled'),
                                            'confirmed' => __('appointments.confirmed'),
                                            'in_progress' => __('appointments.in_progress'),
                                            'completed' => __('appointments.completed'),
                                            'cancelled' => __('appointments.cancelled'),
                                            'no_show' => __('appointments.no_show')
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $statusClasses[$appointment['status']] ?? 'secondary' ?>">
                                            <?= $statusLabels[$appointment['status']] ?? $appointment['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?route=/appointments/details&id=<?= $appointment['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0"><?= __('customers.no_appointments') ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quotes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-invoice"></i> <?= __('quotes.quotes') ?></h5>
                <a href="?route=/quotes/create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-plus"></i> <?= __('common.new') ?>
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($customer['quotes'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th><?= __('quotes.number') ?></th>
                                    <th>Τίτλος</th>
                                    <th><?= __('quotes.amount') ?></th>
                                    <th>Κατάσταση</th>
                                    <th><?= __('common.date') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customer['quotes'] as $quote): ?>
                                <tr>
                                    <td><?= htmlspecialchars($quote['quote_number']) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/quotes/<?= $quote['slug'] ?>">
                                            <?= htmlspecialchars($quote['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= formatCurrency($quote['total_amount']) ?></td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'draft' => 'secondary',
                                            'sent' => 'info',
                                            'accepted' => 'success',
                                            'rejected' => 'danger',
                                            'expired' => 'warning'
                                        ];
                                        $statusLabels = [
                                            'draft' => __('quotes.draft'),
                                            'sent' => __('quotes.sent'),
                                            'accepted' => __('quotes.accepted'),
                                            'rejected' => __('quotes.rejected'),
                                            'expired' => __('quotes.expired')
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $statusClasses[$quote['status']] ?? 'secondary' ?>">
                                            <?= $statusLabels[$quote['status']] ?? $quote['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($quote['created_at']) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/quotes/<?= $quote['slug'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0"><?= __('customers.no_quotes') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
