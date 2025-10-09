<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-file-invoice"></i> Επεξεργασία Προσφοράς</h2>
    <div>
        <a href="<?= BASE_URL ?>/quotes/<?= $quote['slug'] ?>" class="btn btn-secondary">
            <i class="fas fa-eye"></i> Προβολή
        </a>
        <a href="<?= BASE_URL ?>/quotes" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Πίσω
        </a>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/quotes/edit/<?= $quote['id'] ?>" id="quoteForm">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="id" value="<?= $quote['id'] ?>">
    <input type="hidden" name="subtotal" id="subtotal" value="<?= $quote['subtotal'] ?>">
    <input type="hidden" name="tax_amount" id="tax_amount" value="<?= $quote['vat_amount'] ?>">
    <input type="hidden" name="total_amount" id="total_amount" value="<?= $quote['total_amount'] ?>">
    
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
                                <input type="text" class="form-control" value="<?= $quote['quote_number'] ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Κατάσταση</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft" <?= $quote['status'] === 'draft' ? 'selected' : '' ?>>Πρόχειρο</option>
                                    <option value="sent" <?= $quote['status'] === 'sent' ? 'selected' : '' ?>>Απεσταλμένο</option>
                                    <option value="accepted" <?= $quote['status'] === 'accepted' ? 'selected' : '' ?>>Αποδεκτό</option>
                                    <option value="rejected" <?= $quote['status'] === 'rejected' ? 'selected' : '' ?>>Απορριφθέν</option>
                                    <option value="expired" <?= $quote['status'] === 'expired' ? 'selected' : '' ?>>Ληγμένο</option>
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
                                <option value="<?= $customer['id'] ?>" <?= $customer['id'] == $quote['customer_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($customerName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Τίτλος <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($quote['title']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Περιγραφή</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($quote['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="valid_until" class="form-label">Ισχύει Έως</label>
                        <input type="date" class="form-control" id="valid_until" name="valid_until" 
                               value="<?= $quote['valid_until'] ?>">
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
                                <!-- Existing items will be loaded here -->
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
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($quote['notes'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="terms" class="form-label">Όροι και Προϋποθέσεις</label>
                        <textarea class="form-control" id="terms" name="terms" rows="3"><?= htmlspecialchars($quote['terms_conditions'] ?? '') ?></textarea>
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
                        <strong id="subtotal_display"><?= number_format($quote['subtotal'], 2) ?>€</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tax_rate" class="form-label">ΦΠΑ (%)</label>
                        <input type="number" step="0.01" class="form-control" id="tax_rate" name="tax_rate" 
                               value="<?= $quote['vat_rate'] ?>" onchange="calculateTotals()">
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>ΦΠΑ:</span>
                        <strong id="tax_display"><?= number_format($quote['vat_amount'], 2) ?>€</strong>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span class="h5">Σύνολο:</span>
                        <strong class="h5" id="total_display"><?= number_format($quote['total_amount'], 2) ?>€</strong>
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

// Load existing items
const existingItems = <?= json_encode($quote['items'] ?? []) ?>;

function addItem(itemData = null) {
    itemCounter++;
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.id = 'item_' + itemCounter;
    
    const description = itemData ? itemData.description : '';
    const quantity = itemData ? itemData.quantity : 1;
    const unitPrice = itemData ? itemData.unit_price : 0;
    const totalPrice = itemData ? itemData.total_price : 0;
    const itemType = itemData ? itemData.item_type : 'service';
    
    row.innerHTML = `
        <td>
            <input type="hidden" name="items[${itemCounter}][item_type]" value="${itemType}">
            <input type="text" class="form-control" name="items[${itemCounter}][description]" 
                   value="${description}" placeholder="π.χ. Εγκατάσταση ηλεκτρικού πίνακα" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="items[${itemCounter}][quantity]" 
                   value="${quantity}" onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="items[${itemCounter}][unit_price]" 
                   value="${unitPrice}" onchange="calculateItemTotal(${itemCounter})" required>
        </td>
        <td>
            <input type="number" step="0.01" class="form-control" name="items[${itemCounter}][total_price]" 
                   value="${totalPrice}" readonly>
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

// Load existing items on page load
document.addEventListener('DOMContentLoaded', function() {
    if (existingItems.length > 0) {
        existingItems.forEach(item => {
            addItem(item);
        });
    } else {
        addItem();
    }
});
</script>
