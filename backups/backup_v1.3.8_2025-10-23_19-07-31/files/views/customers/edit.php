<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-user-plus"></i> 
                    <?= isset($customer) ? __('customers.edit_customer') : __('customers.new_customer') ?>
                </h4>
            </div>
            
            <div class="card-body">
                <form method="POST" id="customerForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <!-- Customer Type Selection -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label"><?= __('customers.customer_type') ?> <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group" aria-label="Customer Type">
                                <input type="radio" class="btn-check" name="customer_type" id="individual" value="individual" 
                                       <?= (!isset($customer) || $customer['customer_type'] === 'individual') ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="individual">
                                    <i class="fas fa-user"></i> <?= __('customers.individual') ?>
                                </label>
                                
                                <input type="radio" class="btn-check" name="customer_type" id="company" value="company"
                                       <?= (isset($customer) && $customer['customer_type'] === 'company') ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="company">
                                    <i class="fas fa-building"></i> <?= __('customers.company') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Individual/Company Contact Person Fields -->
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label" id="first_name_label"><?= __('customers.first_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($_SESSION['form_errors']['first_name']) ? 'is-invalid' : '' ?>" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="<?= htmlspecialchars($customer['first_name'] ?? $_SESSION['form_data']['first_name'] ?? '') ?>"
                                   required>
                            <?php if (isset($_SESSION['form_errors']['first_name'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['form_errors']['first_name'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6 mb-3" id="last_name_group">
                            <label for="last_name" class="form-label"><?= __('customers.last_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($_SESSION['form_errors']['last_name']) ? 'is-invalid' : '' ?>" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="<?= htmlspecialchars($customer['last_name'] ?? $_SESSION['form_data']['last_name'] ?? '') ?>"
                                   required>
                            <?php if (isset($_SESSION['form_errors']['last_name'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['form_errors']['last_name'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Company Fields -->
                    <div class="row" id="company-fields" style="display: none;">
                        <div class="col-md-8 mb-3">
                            <label for="company_name" class="form-label"><?= __('customers.company_name') ?></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="company_name" 
                                   name="company_name" 
                                   value="<?= htmlspecialchars($customer['company_name'] ?? $_SESSION['form_data']['company_name'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="tax_id" class="form-label"><?= __('customers.tax_id') ?></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="tax_id" 
                                   name="tax_id" 
                                   value="<?= htmlspecialchars($customer['tax_id'] ?? $_SESSION['form_data']['tax_id'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label"><?= __('customers.phone') ?> <span class="text-danger">*</span></label>
                            <input type="tel" 
                                   class="form-control <?= isset($_SESSION['form_errors']['phone']) ? 'is-invalid' : '' ?>" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($customer['phone'] ?? $_SESSION['form_data']['phone'] ?? '') ?>"
                                   required>
                            <?php if (isset($_SESSION['form_errors']['phone'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['form_errors']['phone'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label"><?= __('customers.mobile') ?></label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="mobile" 
                                   name="mobile" 
                                   value="<?= htmlspecialchars($customer['mobile'] ?? $_SESSION['form_data']['mobile'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control <?= isset($_SESSION['form_errors']['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($customer['email'] ?? $_SESSION['form_data']['email'] ?? '') ?>">
                            <?php if (isset($_SESSION['form_errors']['email'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['form_errors']['email'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Address Information -->
                    <h6 class="mt-4 mb-3"><i class="fas fa-map-marker-alt"></i> <?= __('customers.address_details') ?></h6>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label"><?= __('customers.address') ?> <span class="text-danger">*</span></label>
                            <textarea class="form-control <?= isset($_SESSION['form_errors']['address']) ? 'is-invalid' : '' ?>" 
                                      id="address" 
                                      name="address" 
                                      rows="2" 
                                      required><?= htmlspecialchars($customer['address'] ?? $_SESSION['form_data']['address'] ?? '') ?></textarea>
                            <?php if (isset($_SESSION['form_errors']['address'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['form_errors']['address'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="city" class="form-label"><?= __('customers.city') ?></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="city" 
                                   name="city" 
                                   value="<?= htmlspecialchars($customer['city'] ?? $_SESSION['form_data']['city'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label"><?= __('customers.postal_code') ?></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="postal_code" 
                                   name="postal_code" 
                                   value="<?= htmlspecialchars($customer['postal_code'] ?? $_SESSION['form_data']['postal_code'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label"><?= __('common.notes') ?></label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="<?= __('customers.notes_placeholder') ?>"><?= htmlspecialchars($customer['notes'] ?? $_SESSION['form_data']['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="/customers" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> <?= __('customers.return') ?>
                                </a>
                                
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> 
                                        <?= isset($customer) ? __('common.update') : __('common.create') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const individualRadio = document.getElementById('individual');
    const companyRadio = document.getElementById('company');
    const companyFields = document.getElementById('company-fields');
    const companyNameField = document.getElementById('company_name');
    const firstNameLabel = document.getElementById('first_name_label');
    const lastNameGroup = document.getElementById('last_name_group');
    
    // Toggle company fields visibility
    function toggleCompanyFields() {
        if (companyRadio.checked) {
            // Company mode
            companyFields.style.display = 'block';
            companyNameField.required = true;
            
            // Change labels for company contact person
            firstNameLabel.innerHTML = 'Όνομα Επώνυμο <span class="text-danger">*</span>';
            lastNameGroup.style.display = 'none';
            document.getElementById('last_name').required = false;
        } else {
            // Individual mode
            companyFields.style.display = 'none';
            companyNameField.required = false;
            companyNameField.value = '';
            document.getElementById('tax_id').value = '';
            
            // Restore original labels
            firstNameLabel.innerHTML = '<?= __('customers.first_name') ?> <span class="text-danger">*</span>';
            lastNameGroup.style.display = 'block';
            document.getElementById('last_name').required = true;
        }
    }
    
    // Initial state
    toggleCompanyFields();
    
    // Event listeners
    individualRadio.addEventListener('change', toggleCompanyFields);
    companyRadio.addEventListener('change', toggleCompanyFields);
    
    // Form validation
    document.getElementById('customerForm').addEventListener('submit', function(e) {
        const formData = new FormData(this);
        const customerType = formData.get('customer_type');
        
        // Additional validation rules
        const validationRules = {
            first_name: { required: true, message: 'Το όνομα είναι υποχρεωτικό' },
            last_name: { required: true, message: 'Το επώνυμο είναι υποχρεωτικό' },
            phone: { required: true, phone: true, message: 'Το τηλέφωνο είναι υποχρεωτικό' },
            address: { required: true, message: 'Η διεύθυνση είναι υποχρεωτική' },
            email: { email: true, message: 'Παρακαλώ εισάγετε έγκυρο email' }
        };
        
        if (customerType === 'company') {
            validationRules.company_name = { required: true, message: 'Η επωνυμία εταιρείας είναι υποχρεωτική' };
        }
        
        if (!validateForm('customerForm', validationRules)) {
            e.preventDefault();
        }
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Basic phone number formatting for Greek numbers
            let value = this.value.replace(/\D/g, '');
            if (value.startsWith('30')) {
                value = '+' + value;
            }
            this.value = value;
        });
    });
    
    // Auto-capitalize names
    const nameInputs = document.querySelectorAll('#first_name, #last_name, #company_name, #city');
    nameInputs.forEach(input => {
        input.addEventListener('blur', function() {
            this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
        });
    });
    
    // Postal code validation (Greek postal codes)
    const postalCodeInput = document.getElementById('postal_code');
    postalCodeInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substr(0, 5);
        }
        this.value = value;
    });
    
    // Tax ID formatting (Greek AFM)
    const taxIdInput = document.getElementById('tax_id');
    taxIdInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 9) {
            value = value.substr(0, 9);
        }
        this.value = value;
    });
    
    // Auto-focus first field
    document.getElementById('first_name').focus();
});

// Clear form errors when user starts typing
document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        const feedback = this.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    });
});
</script>

<style>
.btn-check:checked + .btn-outline-primary {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-label {
    font-weight: 600;
}

.text-danger {
    font-size: 0.875em;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}
</style>

<?php 
// Clear form data and errors from session
unset($_SESSION['form_data']);
unset($_SESSION['form_errors']);
?>