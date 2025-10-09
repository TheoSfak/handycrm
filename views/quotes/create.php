<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice"></i> Νέα Προσφορά</h2>
    <a href="?route=/quotes" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Πίσω στη Λίστα
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="?route=/quotes/create" id="quoteForm">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="quote_number" value="<?= $quote_number ?>">
    <input type="hidden" name="subtotal" id="subtotal" value="0">
    <input type="hidden" name="tax_amount" id="tax_amount" value="0">
    <input type="hidden" name="total_amount" id="total_amount" value="0">
    
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Βασικές Πληροφορίες</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Αριθμός Προσφοράς</label>
                                <input type="text" class="form-control" value="<?= $quote_number ?>" disabled>
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
                        <label for="title" class="form-label">Τίτλος <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Περιγραφή</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="valid_until" class="form-label">Ισχύει Έως</label>
                        <input type="date" class="form-control" id="valid_until" name="valid_until" 
                               value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        <small class="text-muted">Η ημερομηνία έκδοσης θα καταγραφεί αυτόματα</small>
                    </div>
                </div>
            </div>
            
            <!-- Quote Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Στοιχεία Προσφοράς</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                        <i class="fas fa-plus"></i> Προσθήκη Γραμμής
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="40%">Περιγραφή</th>
                                    <th width="15%">Ποσότητα</th>
                                    <th width="15%">Τιμή Μονάδας</th>
                                    <th width="20%">Σύνολο</th>
                                    <th width="10%"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Items will be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Notes and Terms -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Σημειώσεις & Όροι</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Σημειώσεις</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="terms" class="form-label">Όροι και Προϋποθέσεις</label>
                        <textarea class="form-control" id="terms" name="terms" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column - Summary -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0">Σύνοψη</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Υποσύνολο:</span>
                        <strong id="subtotal_display">0.00€</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tax_rate" class="form-label">ΦΠΑ (%)</label>
                        <input type="number" step="0.01" class="form-control" id="tax_rate" name="tax_rate" 
                               value="24" onchange="calculateTotals()">
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>ΦΠΑ:</span>
                        <strong id="tax_display">0.00€</strong>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="h5">Σύνολο:</span>
                        <strong class="h5" id="total_display">0.00€</strong>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save"></i> Αποθήκευση
                    </button>
                    
                    <a href="?route=/quotes" class="btn btn-secondary w-100">
                        <i class="fas fa-times"></i> Ακύρωση
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
let itemCounter = 0;

function addItem() {
    itemCounter++;
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.id = 'item_' + itemCounter;
    row.innerHTML = `
        <td>
            <input type="hidden" name="items[${itemCounter}][item_type]" value="service">
            <input type="text" class="form-control" name="items[${itemCounter}][description]" 
                   placeholder="π.χ. Εγκατάσταση ηλεκτρικού πίνακα" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="items[${itemCounter}][quantity]" 
                   value="1" onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="items[${itemCounter}][unit_price]" 
                   value="0" onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="items[${itemCounter}][total_price]" 
                   value="0" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${itemCounter})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
}

function removeItem(id) {
    const row = document.getElementById('item_' + id);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

function calculateItemTotal(id) {
    const quantity = parseFloat(document.querySelector(`input[name="items[${id}][quantity]"]`).value) || 0;
    const unitPrice = parseFloat(document.querySelector(`input[name="items[${id}][unit_price]"]`).value) || 0;
    const total = quantity * unitPrice;
    
    document.querySelector(`input[name="items[${id}][total_price]"]`).value = total.toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    
    // Sum all item totals
    document.querySelectorAll('input[name$="[total_price]"]').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount;
    
    // Update displays
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('subtotal_display').textContent = subtotal.toFixed(2) + '€';
    
    document.getElementById('tax_amount').value = taxAmount.toFixed(2);
    document.getElementById('tax_display').textContent = taxAmount.toFixed(2) + '€';
    
    document.getElementById('total_amount').value = total.toFixed(2);
    document.getElementById('total_display').textContent = total.toFixed(2) + '€';
}

// Add first item on load
document.addEventListener('DOMContentLoaded', function() {
    addItem();
});
</script>
