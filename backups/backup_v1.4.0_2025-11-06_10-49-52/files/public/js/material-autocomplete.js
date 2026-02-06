/**
 * Material Autocomplete JavaScript
 * Handles autocomplete functionality for material name inputs
 * Fetches data from /api/materials/search and populates unit and price
 */

(function() {
    'use strict';
    
    // Greeklish to Greek conversion map
    const greeklishMap = {
        'a': 'α', 'A': 'Α',
        'b': 'β', 'B': 'Β',
        'g': 'γ', 'G': 'Γ',
        'd': 'δ', 'D': 'Δ',
        'e': 'ε', 'E': 'Ε',
        'z': 'ζ', 'Z': 'Ζ',
        'h': 'η', 'H': 'Η',
        '8': 'θ', // th
        'i': 'ι', 'I': 'Ι',
        'k': 'κ', 'K': 'Κ',
        'l': 'λ', 'L': 'Λ',
        'm': 'μ', 'M': 'Μ',
        'n': 'ν', 'N': 'Ν',
        'j': 'ξ', 'J': 'Ξ', 'x': 'ξ', 'X': 'Ξ',
        'o': 'ο', 'O': 'Ο',
        'p': 'π', 'P': 'Π',
        'r': 'ρ', 'R': 'Ρ',
        's': 'σ', 'S': 'Σ',
        't': 'τ', 'T': 'Τ',
        'y': 'υ', 'Y': 'Υ',
        'f': 'φ', 'F': 'Φ',
        'c': 'χ', 'C': 'Χ',
        'v': 'β', 'V': 'Β',
        'w': 'ω', 'W': 'Ω'
    };
    
    /**
     * Convert Greeklish to Greek
     * @param {string} text - Input text in Greeklish
     * @return {string} - Converted Greek text
     */
    function greeklishToGreek(text) {
        if (!text) return '';
        
        let result = text;
        
        // Handle multi-character combinations first
        result = result.replace(/th/gi, match => match === 'th' ? 'θ' : 'Θ');
        result = result.replace(/Th/g, 'Θ');
        result = result.replace(/TH/g, 'Θ');
        result = result.replace(/ps/gi, match => match === 'ps' ? 'ψ' : 'Ψ');
        result = result.replace(/Ps/g, 'Ψ');
        result = result.replace(/PS/g, 'Ψ');
        result = result.replace(/ch/gi, match => match === 'ch' ? 'χ' : 'Χ');
        result = result.replace(/Ch/g, 'Χ');
        result = result.replace(/CH/g, 'Χ');
        
        // Handle diphthongs
        result = result.replace(/ou/gi, match => match === 'ou' ? 'ου' : 'ΟΥ');
        result = result.replace(/Ou/g, 'Ου');
        result = result.replace(/OU/g, 'ΟΥ');
        result = result.replace(/ai/gi, match => match === 'ai' ? 'αι' : 'ΑΙ');
        result = result.replace(/Ai/g, 'Αι');
        result = result.replace(/AI/g, 'ΑΙ');
        result = result.replace(/ei/gi, match => match === 'ei' ? 'ει' : 'ΕΙ');
        result = result.replace(/Ei/g, 'Ει');
        result = result.replace(/EI/g, 'ΕΙ');
        result = result.replace(/oi/gi, match => match === 'oi' ? 'οι' : 'ΟΙ');
        result = result.replace(/Oi/g, 'Οι');
        result = result.replace(/OI/g, 'ΟΙ');
        
        // Convert single characters
        result = result.split('').map(char => greeklishMap[char] || char).join('');
        
        return result;
    }
    
    // Debounce function to limit API calls
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Initialize autocomplete for a material name input
     * @param {HTMLInputElement} input - The input element to attach autocomplete to
     * @param {number} rowIndex - The row index for the material
     */
    window.initMaterialAutocomplete = function(input, rowIndex) {
        if (!input) {
            console.error('Material autocomplete: No input provided');
            return;
        }
        
        console.log('Material autocomplete initialized for row', rowIndex);
        
        // Create dropdown container
        const dropdown = document.createElement('div');
        dropdown.className = 'material-autocomplete-dropdown';
        dropdown.style.cssText = `
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            width: 100%;
        `;
        
        // Insert dropdown after input's parent
        input.closest('.col-md-12').style.position = 'relative';
        input.parentElement.appendChild(dropdown);
        
        let currentFocus = -1;
        let materials = [];
        
        // Handle input changes
        const handleInput = debounce(async function() {
            const query = input.value.trim();
            
            // Clear dropdown if query is too short
            if (query.length < 2) {
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                materials = [];
                return;
            }
            
            try {
                // For now, just use the original query as-is
                // The backend does case-insensitive search with LOWER()
                // This allows searching for "nym", "NYM", "3x1.5", etc.
                
                // Fetch materials from API (case-insensitive search on backend)
                const response = await fetch(`${window.BASE_URL || ''}?route=/api/materials/search&q=${encodeURIComponent(query)}&limit=10`);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                materials = await response.json();
                
                // Clear previous results
                dropdown.innerHTML = '';
                
                if (materials.length === 0) {
                    dropdown.innerHTML = `
                        <div style="padding: 12px; color: #6c757d; text-align: center;">
                            <i class="fas fa-search me-2"></i>Δεν βρέθηκαν αποτελέσματα
                        </div>
                    `;
                    dropdown.style.display = 'block';
                    return;
                }
                
                // Create dropdown items
                materials.forEach((material, index) => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    item.style.cssText = `
                        padding: 10px 12px;
                        cursor: pointer;
                        border-bottom: 1px solid #f0f0f0;
                        transition: background-color 0.2s;
                    `;
                    
                    item.innerHTML = `
                        <div style="font-weight: 500; color: #212529;">
                            ${escapeHtml(material.name)}
                        </div>
                        <div style="font-size: 0.875rem; color: #6c757d;">
                            <span class="badge bg-secondary me-2">${escapeHtml(material.category || 'Χωρίς κατηγορία')}</span>
                            ${material.unit ? `<span class="me-2"><i class="fas fa-ruler me-1"></i>${escapeHtml(material.unit)}</span>` : ''}
                            ${material.price ? `<span><i class="fas fa-euro-sign me-1"></i>${parseFloat(material.price).toFixed(2)}€</span>` : ''}
                        </div>
                    `;
                    
                    // Hover effect
                    item.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#f8f9fa';
                    });
                    
                    item.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = 'white';
                    });
                    
                    // Click handler
                    item.addEventListener('click', function() {
                        selectMaterial(material, rowIndex, input, dropdown);
                    });
                    
                    dropdown.appendChild(item);
                });
                
                dropdown.style.display = 'block';
                currentFocus = -1;
                
            } catch (error) {
                console.error('Error fetching materials:', error);
                dropdown.innerHTML = `
                    <div style="padding: 12px; color: #dc3545; text-align: center;">
                        <i class="fas fa-exclamation-triangle me-2"></i>Σφάλμα φόρτωσης δεδομένων
                    </div>
                `;
                dropdown.style.display = 'block';
            }
        }, 300);
        
        // Input event listener
        input.addEventListener('input', handleInput);
        
        // Keyboard navigation
        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.autocomplete-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocus++;
                if (currentFocus >= items.length) currentFocus = 0;
                setActive(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocus--;
                if (currentFocus < 0) currentFocus = items.length - 1;
                setActive(items);
            } else if (e.key === 'Enter') {
                if (currentFocus > -1 && items[currentFocus]) {
                    e.preventDefault();
                    items[currentFocus].click();
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });
        
        // Set active item
        function setActive(items) {
            if (!items) return;
            
            // Remove active class from all items
            items.forEach(item => {
                item.style.backgroundColor = 'white';
            });
            
            // Add active class to current item
            if (items[currentFocus]) {
                items[currentFocus].style.backgroundColor = '#e9ecef';
                items[currentFocus].scrollIntoView({ block: 'nearest' });
            }
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    };
    
    /**
     * Select a material and populate the form fields
     * @param {Object} material - The selected material object
     * @param {number} rowIndex - The row index
     * @param {HTMLInputElement} input - The name input element
     * @param {HTMLElement} dropdown - The dropdown element
     */
    function selectMaterial(material, rowIndex, input, dropdown) {
        // Set the name
        input.value = material.name;
        
        // Get the material row container
        const row = input.closest('.material-row');
        if (!row) return;
        
        // Store catalog material ID in hidden field (if exists)
        const catalogIdInput = row.querySelector(`input[name*="[catalog_material_id]"]`);
        if (catalogIdInput) {
            catalogIdInput.value = material.id || '';
        }
        
        // Populate unit if available
        if (material.unit) {
            const unitInput = row.querySelector(`input[name*="[unit]"]`);
            if (unitInput && !unitInput.value) {
                unitInput.value = material.unit;
            }
            
            // Also try to match with unit_type select if it's a dropdown
            const unitTypeSelect = document.querySelector(`select[name="materials[${rowIndex}][unit_type]"]`);
            if (unitTypeSelect) {
                // Try to match unit with select options
                const unitLower = material.unit.toLowerCase();
                const optionMap = {
                    'τεμάχια': 'pieces',
                    'τεμ': 'pieces',
                    'μέτρα': 'meters',
                    'μ': 'meters',
                    'κιλά': 'kg',
                    'κιλό': 'kg',
                    'kg': 'kg',
                    'λίτρα': 'liters',
                    'λ': 'liters',
                    'κουτιά': 'boxes',
                    'κουτί': 'boxes'
                };
                
                const mappedValue = optionMap[unitLower];
                if (mappedValue) {
                    unitTypeSelect.value = mappedValue;
                } else {
                    unitTypeSelect.value = 'other';
                }
            }
        }
        
        // Populate default price if available (try both price and default_price)
        const materialPrice = material.price || material.default_price;
        if (materialPrice) {
            // Try finding the price input with class first (more reliable)
            let priceInput = row.querySelector('.material-price');
            
            // Fallback to name attributes (try both unit_price and price)
            if (!priceInput) {
                priceInput = row.querySelector(`input[name*="[unit_price]"]`) ||
                             row.querySelector(`input[name*="[price]"]`);
            }
            
            if (priceInput && !priceInput.value) {
                priceInput.value = parseFloat(materialPrice).toFixed(2);
                
                // Trigger calculation if quantity is set
                const quantityInput = row.querySelector('.material-quantity') || 
                                     row.querySelector(`input[name*="[quantity]"]`);
                if (quantityInput && quantityInput.value) {
                    // Trigger onchange event to recalculate subtotal
                    priceInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }
        
        // Close dropdown
        dropdown.style.display = 'none';
        
        // Focus on quantity input
        const quantityInput = document.querySelector(`input[name="materials[${rowIndex}][quantity]"]`);
        if (quantityInput) {
            quantityInput.focus();
        }
    }
    
    /**
     * Escape HTML to prevent XSS
     * @param {string} text - The text to escape
     * @returns {string} - Escaped text
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
    
})();
