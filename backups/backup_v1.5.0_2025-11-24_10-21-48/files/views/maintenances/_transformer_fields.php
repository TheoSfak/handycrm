<!-- Transformer Form Partial - Multiple Transformers Support -->
<div id="transformersContainer">
    <!-- Transformer Template (hidden, will be cloned) -->
    <div id="transformerTemplate" class="transformer-block card mb-4" style="display: none;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bolt"></i> Μετασχηματιστής <span class="transformer-number">1</span></h5>
            <button type="button" class="btn btn-sm btn-danger remove-transformer" onclick="removeTransformer(this)">
                <i class="fas fa-trash"></i> Αφαίρεση
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Ισχύς Μ/Σ (kVA) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control transformer-power" name="transformers[INDEX][power]" required>
                </div>
            </div>

            <h6 class="text-secondary mt-4 mb-3"><i class="fas fa-clipboard-check"></i> Μετρήσεις Μόνωσης</h6>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Μετρήσεις Μόνωσης <span class="text-danger">*</span></label>
                    <textarea class="form-control transformer-insulation" name="transformers[INDEX][insulation]" rows="3" 
                              required placeholder="Εισάγετε τις μετρήσεις μόνωσης..."></textarea>
                </div>
            </div>

            <h6 class="text-secondary mt-4 mb-3"><i class="fas fa-magnet"></i> Αντίσταση Πηνίων</h6>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Μετρήσεις Αντίστασης Πηνίων <span class="text-danger">*</span></label>
                    <textarea class="form-control transformer-coil" name="transformers[INDEX][coil_resistance]" rows="3" 
                              required placeholder="Εισάγετε τις μετρήσεις αντίστασης πηνίων..."></textarea>
                </div>
            </div>

            <h6 class="text-secondary mt-4 mb-3"><i class="fas fa-plug"></i> Γείωση</h6>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Μέτρηση Γείωσης (Ω) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control transformer-grounding" name="transformers[INDEX][grounding]" 
                           required placeholder="π.χ. 2.5 Ω">
                </div>
            </div>

            <h6 class="text-secondary mt-4 mb-3"><i class="fas fa-flask"></i> Διηλεκτρική Αντοχή Λαδιού</h6>
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">Τιμή 1 (kV)</label>
                    <input type="text" class="form-control transformer-oil1" name="transformers[INDEX][oil_v1]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Τιμή 2 (kV)</label>
                    <input type="text" class="form-control transformer-oil2" name="transformers[INDEX][oil_v2]">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Τιμή 3 (kV)</label>
                    <input type="text" class="form-control transformer-oil3" name="transformers[INDEX][oil_v3]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Τιμή 4 (kV)</label>
                    <input type="text" class="form-control transformer-oil4" name="transformers[INDEX][oil_v4]">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Τιμή 5 (kV)</label>
                    <input type="text" class="form-control transformer-oil5" name="transformers[INDEX][oil_v5]">
                </div>
            </div>
        </div>
    </div>

    <!-- Initial Transformer (visible by default) -->
</div>

<!-- Add Transformer Button -->
<div class="text-center mb-4">
    <button type="button" class="btn btn-success btn-lg" onclick="addTransformer()">
        <i class="fas fa-plus-circle"></i> Προσθήκη Μετασχηματιστή
    </button>
</div>

<script>
let transformerCount = 0;

// Initialize with one transformer on page load
document.addEventListener('DOMContentLoaded', function() {
    if (transformerCount === 0) {
        addTransformer();
    }
});

function addTransformer() {
    transformerCount++;
    const template = document.getElementById('transformerTemplate');
    const clone = template.cloneNode(true);
    
    // Make visible and give unique ID
    clone.style.display = 'block';
    clone.id = 'transformer-' + transformerCount;
    clone.setAttribute('data-index', transformerCount);
    
    // Update transformer number
    clone.querySelector('.transformer-number').textContent = transformerCount;
    
    // Replace INDEX placeholder in all name attributes
    const inputs = clone.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        if (input.name) {
            input.name = input.name.replace('INDEX', transformerCount);
        }
    });
    
    // Hide remove button if this is the first transformer
    if (transformerCount === 1) {
        const removeBtn = clone.querySelector('.remove-transformer');
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    }
    
    // Add to container
    document.getElementById('transformersContainer').appendChild(clone);
}

function removeTransformer(button) {
    const block = button.closest('.transformer-block');
    const index = parseInt(block.getAttribute('data-index'));
    
    // Don't allow removing if only one transformer
    const visibleTransformers = document.querySelectorAll('.transformer-block[style*="display: block"]');
    if (visibleTransformers.length <= 1) {
        alert('Πρέπει να υπάρχει τουλάχιστον ένας μετασχηματιστής!');
        return;
    }
    
    // Remove the transformer block
    block.remove();
    
    // Renumber remaining transformers
    renumberTransformers();
}

function renumberTransformers() {
    const transformers = document.querySelectorAll('.transformer-block[style*="display: block"]');
    transformers.forEach((transformer, index) => {
        const number = index + 1;
        transformer.querySelector('.transformer-number').textContent = number;
        
        // Show/hide remove button
        const removeBtn = transformer.querySelector('.remove-transformer');
        if (removeBtn) {
            removeBtn.style.display = (transformers.length > 1) ? 'inline-block' : 'none';
        }
    });
}
</script>
