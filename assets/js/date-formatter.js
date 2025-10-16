/**
 * HandyCRM Date Formatter
 * Converts all date inputs to use dd/mm/yyyy format
 * 
 * @author Theodore Sfakianakis
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Change type to text to allow custom format
        input.setAttribute('type', 'text');
        
        // Add placeholder
        input.setAttribute('placeholder', 'ηη/μμ/εεεε');
        
        // Add pattern for validation
        input.setAttribute('pattern', '\\d{2}/\\d{2}/\\d{4}');
        
        // Add autocomplete off
        input.setAttribute('autocomplete', 'off');
        
        // Convert existing value from YYYY-MM-DD to DD/MM/YYYY
        if (input.value && input.value.match(/^\d{4}-\d{2}-\d{2}$/)) {
            const [year, month, day] = input.value.split('-');
            input.value = `${day}/${month}/${year}`;
        }
        
        // Add input mask for dd/mm/yyyy
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            if (value.length >= 5) {
                value = value.substring(0, 5) + '/' + value.substring(5);
            }
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            
            e.target.value = value;
        });
        
        // Validate on blur
        input.addEventListener('blur', function(e) {
            const value = e.target.value;
            if (value && !value.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                e.target.setCustomValidity('Η ημερομηνία πρέπει να είναι σε μορφή ηη/μμ/εεεε');
                e.target.reportValidity();
            } else {
                e.target.setCustomValidity('');
            }
        });
    });
    
    // Convert form submission to send dates in YYYY-MM-DD format for backend
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const textDateInputs = form.querySelectorAll('input[type="text"][pattern*="\\\\d{2}"]');
            
            textDateInputs.forEach(input => {
                if (input.value && input.value.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                    const [day, month, year] = input.value.split('/');
                    // Create hidden input with YYYY-MM-DD format
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = input.name;
                    hiddenInput.value = `${year}-${month}-${day}`;
                    form.appendChild(hiddenInput);
                    
                    // Remove name from visible input to avoid duplicate submission
                    input.removeAttribute('name');
                }
            });
        });
    });
});
