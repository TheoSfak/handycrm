<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice-dollar"></i> Νέο Τιμολόγιο</h2>
    <a href="?route=/invoices" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Πίσω στη Λίστα
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="?route=/invoices/create" id="invoiceForm">
    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="invoice_number" value="<?= $invoiceNumber ?>">
    <input type="hidden" name="subtotal" id="subtotal" value="0">
    <input type="hidden" name="vat_amount" id="vat_amount" value="0">
    <input type="hidden" name="total_amount" id="total_amount" value="0">
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Βασικές Πληροφορίες</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Αριθμός Τιμολογίου</label>
                                <input type="text" class="form-control" value="<?= $invoiceNumber ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Κατάσταση</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft" selected>Πρόχειρο</option>
                                    <option value="sent">Απεσταλμένο</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Πελάτης <span class="text-danger">*</span></label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Επιλέξτε πελάτη...</option>
                            <?php foreach ($customers as $customer): ?>
                                <?php
                                    $customerName = $customer['customer_type'] === 'company' && !empty($customer['company_name']) 
                                        ? $customer['company_name'] 
                                        : $customer['first_name'] . ' ' . $customer['last_name'];
                                ?>
                                <option value="<?= $customer['id'] ?>">
                                    <?= htmlspecialchars($customerName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Έργο (προαιρετικό)</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">Χωρίς έργο</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id'] ?>">
                                    <?= htmlspecialchars($project['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Τίτλος</label>
                        <input type="text" class="form-control" id="title" name="title">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Περιγραφή</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="issue_date" class="form-label">Ημερομηνία Τιμολογίου <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Ημερομηνία Λήξης <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="due_date" name="due_date" 
                                       value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Invoice Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Στοιχεία Τιμολογίου</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                        <i class="fas fa-plus"></i> Προσθήκη Γραμμής
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="45%">Περιγραφή</th>
                                    <th width="15%">Ποσότητα</th>
                                    <th width="18%">Τιμή</th>
                                    <th width="18%">Σύνολο</th>
                                    <th width="4%"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td>
                                        <input type="text" class="form-control" name="items[0][description]" required>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-quantity" name="items[0][quantity]" 
                                               value="1" min="0" step="0.01" required onchange="calculateItem(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-price" name="items[0][unit_price]" 
                                               value="0" min="0" step="0.01" required onchange="calculateItem(this)">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control item-total" name="items[0][total_price]" 
                                               value="0" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column - Summary -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Σύνοψη</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="vat_rate" class="form-label">ΦΠΑ %</label>
                        <input type="number" class="form-control" id="vat_rate" name="vat_rate" 
                               value="24" min="0" max="100" step="0.01" onchange="calculateTotals()">
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Υποσύνολο:</span>
                        <strong id="display_subtotal">0,00 €</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>ΦΠΑ <span id="display_vat_rate">24</span>%:</span>
                        <strong id="display_vat_amount">0,00 €</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="h5">Σύνολο:</span>
                        <strong class="h5 text-primary" id="display_total">0,00 €</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Σημειώσεις</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Αποθήκευση
                        </button>
                        <a href="?route=/invoices" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Ακύρωση
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
let itemCount = 1;

function addItem() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    row.innerHTML = `
        <td>
            <input type="text" class="form-control" name="items[${itemCount}][description]" required>
        </td>
        <td>
            <input type="number" class="form-control item-quantity" name="items[${itemCount}][quantity]" 
                   value="1" min="0" step="0.01" required onchange="calculateItem(this)">
        </td>
        <td>
            <input type="number" class="form-control item-price" name="items[${itemCount}][unit_price]" 
                   value="0" min="0" step="0.01" required onchange="calculateItem(this)">
        </td>
        <td>
            <input type="number" class="form-control item-total" name="items[${itemCount}][total_price]" 
                   value="0" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    itemCount++;
}

function removeItem(button) {
    const tbody = document.getElementById('itemsBody');
    if (tbody.children.length > 1) {
        button.closest('tr').remove();
        calculateTotals();
    } else {
        alert('Πρέπει να υπάρχει τουλάχιστον μία γραμμή');
    }
}

function calculateItem(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = quantity * price;
    row.querySelector('.item-total').value = total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-total').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const vatRate = parseFloat(document.getElementById('vat_rate').value) || 0;
    const vatAmount = subtotal * (vatRate / 100);
    const total = subtotal + vatAmount;
    
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('vat_amount').value = vatAmount.toFixed(2);
    document.getElementById('total_amount').value = total.toFixed(2);
    
    document.getElementById('display_subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('display_vat_rate').textContent = vatRate.toFixed(0);
    document.getElementById('display_vat_amount').textContent = formatCurrency(vatAmount);
    document.getElementById('display_total').textContent = formatCurrency(total);
}

function formatCurrency(amount) {
    return amount.toFixed(2).replace('.', ',') + ' €';
}

// Initialize calculations
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});
</script>
