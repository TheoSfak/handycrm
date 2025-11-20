<?php $pageTitle = 'Αποστολή Email - Εργασία Ημέρας'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4>
                    <i class="fas fa-envelope"></i> 
                    Αποστολή Email - Εργασία: <strong><?= htmlspecialchars($task['task_number']) ?></strong>
                </h4>
                <div>
                    <a href="<?= BASE_URL ?>/daily-tasks/view/<?= $task['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Πίσω
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paper-plane"></i> Στοιχεία Email</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/daily-tasks/send-email/<?= $task['id'] ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Παραλήπτη <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   required placeholder="email@example.com">
                            <small class="text-muted">Το email όπου θα σταλεί η εργασία</small>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Θέμα <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   required value="Εργασία Ημέρας <?= htmlspecialchars($task['task_number']) ?> - <?= htmlspecialchars($task['customer_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Μήνυμα Email</label>
                            <textarea class="form-control" id="message" name="message" rows="6"><?php 
                            echo "Αγαπητέ/ή " . htmlspecialchars($task['customer_name']) . ",\n\n";
                            echo "Σας αποστέλλουμε συνημμένα τα στοιχεία της εργασίας με αριθμό " . htmlspecialchars($task['task_number']) . ".\n\n";
                            echo "Ημερομηνία: " . date('d/m/Y', strtotime($task['date'])) . "\n";
                            echo "Τύπος Εργασίας: ";
                            $taskTypes = [
                                'electrical' => 'Ηλεκτρολογικές Εργασίες',
                                'inspection' => 'Επίσκεψη/Έλεγχος',
                                'fault_repair' => 'Έλεγχος Βλάβες / Αποκατάσταση Βλάβης',
                                'other' => 'Διάφορα'
                            ];
                            echo $taskTypes[$task['task_type']] ?? '';
                            echo "\n\n";
                            echo "Για οποιαδήποτε διευκρίνιση παρακαλούμε επικοινωνήστε μαζί μας.\n\n";
                            echo "Με εκτίμηση,\n";
                            echo "ECOWATT Ενεργειακές Λύσεις";
                            ?></textarea>
                            <small class="text-muted">Το κείμενο του email που θα συνοδεύει το PDF</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Σημείωση:</strong> Θα δημιουργηθεί αυτόματα PDF με όλα τα στοιχεία της εργασίας και θα αποσταλεί ως συνημμένο.
                        </div>

                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Αποστολή Email
                            </button>
                            <a href="<?= BASE_URL ?>/daily-tasks/view/<?= $task['id'] ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Ακύρωση
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-pdf"></i> Προεπισκόπηση PDF</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Το PDF θα περιλαμβάνει:</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Αριθμό Εργασίας</li>
                        <li><i class="fas fa-check text-success"></i> Ημερομηνία</li>
                        <li><i class="fas fa-check text-success"></i> Στοιχεία Πελάτη</li>
                        <li><i class="fas fa-check text-success"></i> Τύπο & Κατάσταση Εργασίας</li>
                        <li><i class="fas fa-check text-success"></i> Ώρες Εργασίας</li>
                        <li><i class="fas fa-check text-success"></i> Περιγραφή Εργασίας</li>
                        <li><i class="fas fa-check text-success"></i> Υλικά</li>
                        <li><i class="fas fa-check text-success"></i> Τεχνικούς</li>
                        <li><i class="fas fa-check text-success"></i> Σημειώσεις</li>
                    </ul>

                    <hr>

                    <h6>Στοιχεία Εργασίας:</h6>
                    <table class="table table-sm">
                        <tr>
                            <th>Αρ. Εργασίας:</th>
                            <td><?= htmlspecialchars($task['task_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Πελάτης:</th>
                            <td><?= htmlspecialchars($task['customer_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Ημερομηνία:</th>
                            <td><?= date('d/m/Y', strtotime($task['date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Τεχνικός:</th>
                            <td><?= htmlspecialchars($task['technician_name']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
