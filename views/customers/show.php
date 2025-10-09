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
            <?= $customer['customer_type'] === 'company' ? 'Εταιρεία' : 'Ιδιώτης' ?>
        </span>
        <?php if ($customer['is_active']): ?>
            <span class="badge bg-success">Ενεργός</span>
        <?php else: ?>
            <span class="badge bg-danger">Ανενεργός</span>
        <?php endif; ?>
    </div>
    <div>
        <a href="?route=/customers/edit&id=<?= $customer['id'] ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Επεξεργασία
        </a>
        <a href="?route=/customers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Πίσω
        </a>
    </div>
</div>

<div class="row">
    <!-- Customer Information -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Πληροφορίες Πελάτη</h5>
            </div>
            <div class="card-body">
                <?php if ($customer['customer_type'] === 'company'): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Επωνυμία Εταιρείας</label>
                        <div><?= htmlspecialchars($customer['company_name']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Υπεύθυνος</label>
                        <div><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></div>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <label class="text-muted small">Ονοματεπώνυμο</label>
                        <div><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></div>
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="text-muted small">Τηλέφωνο</label>
                    <div>
                        <a href="tel:<?= htmlspecialchars($customer['phone']) ?>">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone']) ?>
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($customer['mobile'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Κινητό</label>
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
                    <label class="text-muted small">Διεύθυνση</label>
                    <div><?= nl2br(htmlspecialchars($customer['address'])) ?></div>
                </div>
                
                <?php if (!empty($customer['city'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Πόλη</label>
                    <div><?= htmlspecialchars($customer['city']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customer['postal_code'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Τ.Κ.</label>
                    <div><?= htmlspecialchars($customer['postal_code']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customer['tax_id'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">ΑΦΜ</label>
                    <div><?= htmlspecialchars($customer['tax_id']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($customer['notes'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Σημειώσεις</label>
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
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Γρήγορες Ενέργειες</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="?route=/projects/create&customer_id=<?= $customer['id'] ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-project-diagram text-primary"></i> Νέο Έργο
                </a>
                <a href="?route=/appointments/create&customer_id=<?= $customer['id'] ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-calendar-plus text-success"></i> Νέο Ραντεβού
                </a>
                <a href="?route=/quotes/create&customer_id=<?= $customer['id'] ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-invoice text-info"></i> Νέα Προσφορά
                </a>
                <a href="?route=/invoices/create&customer_id=<?= $customer['id'] ?>" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-invoice-dollar text-warning"></i> Νέο Τιμολόγιο
                </a>
            </div>
        </div>
    </div>
    
    <!-- Projects, Appointments, Quotes, Invoices -->
    <div class="col-lg-8">
        <!-- Projects -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-project-diagram"></i> Έργα</h5>
                <a href="?route=/projects/create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Νέο
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($customer['projects'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Τίτλος</th>
                                    <th>Κατηγορία</th>
                                    <th>Κατάσταση</th>
                                    <th>Ημ/νία</th>
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
                                            'electrical' => 'Ηλεκτρολογικά',
                                            'plumbing' => 'Υδραυλικά',
                                            'maintenance' => 'Συντήρηση',
                                            'emergency' => 'Επείγον'
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
                                            'new' => 'Νέο',
                                            'in_progress' => 'Σε Εξέλιξη',
                                            'completed' => 'Ολοκληρωμένο',
                                            'invoiced' => 'Τιμολογημένο',
                                            'cancelled' => 'Ακυρωμένο'
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
                    <p class="text-muted mb-0">Δεν υπάρχουν έργα για αυτόν τον πελάτη</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Appointments -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Ραντεβού</h5>
                <a href="?route=/appointments/create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Νέο
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($customer['appointments'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Τίτλος</th>
                                    <th>Ημερομηνία</th>
                                    <th>Τεχνικός</th>
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
                                            'scheduled' => 'Προγραμματισμένο',
                                            'confirmed' => 'Επιβεβαιωμένο',
                                            'in_progress' => 'Σε Εξέλιξη',
                                            'completed' => 'Ολοκληρωμένο',
                                            'cancelled' => 'Ακυρωμένο',
                                            'no_show' => 'Δεν Εμφανίστηκε'
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
                    <p class="text-muted mb-0">Δεν υπάρχουν ραντεβού για αυτόν τον πελάτη</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quotes -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Προσφορές</h5>
                <a href="?route=/quotes/create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-plus"></i> Νέα
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($customer['quotes'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Αριθμός</th>
                                    <th>Τίτλος</th>
                                    <th>Ποσό</th>
                                    <th>Κατάσταση</th>
                                    <th>Ημ/νία</th>
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
                                            'draft' => 'Πρόχειρο',
                                            'sent' => 'Απεσταλμένη',
                                            'accepted' => 'Αποδεκτή',
                                            'rejected' => 'Απορριφθείσα',
                                            'expired' => 'Ληγμένη'
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
                    <p class="text-muted mb-0">Δεν υπάρχουν προσφορές για αυτόν τον πελάτη</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Invoices -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Τιμολόγια</h5>
                <a href="?route=/invoices/create&customer_id=<?= $customer['id'] ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-plus"></i> Νέο
                </a>
            </div>
            <div class="card-body">
                <?php if (!empty($customer['invoices'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Αριθμός</th>
                                    <th>Τίτλος</th>
                                    <th>Ποσό</th>
                                    <th>Κατάσταση</th>
                                    <th>Ημ/νία</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customer['invoices'] as $invoice): ?>
                                <tr>
                                    <td><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/invoices/<?= $invoice['slug'] ?>">
                                            <?= htmlspecialchars($invoice['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= formatCurrency($invoice['total_amount']) ?></td>
                                    <td>
                                        <?php
                                        $statusClasses = [
                                            'draft' => 'secondary',
                                            'sent' => 'info',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Πρόχειρο',
                                            'sent' => 'Απεσταλμένο',
                                            'paid' => 'Πληρωμένο',
                                            'overdue' => 'Ληξιπρόθεσμο',
                                            'cancelled' => 'Ακυρωμένο'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $statusClasses[$invoice['status']] ?? 'secondary' ?>">
                                            <?= $statusLabels[$invoice['status']] ?? $invoice['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= formatDate($invoice['created_at']) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/invoices/<?= $invoice['slug'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Δεν υπάρχουν τιμολόγια για αυτόν τον πελάτη</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
