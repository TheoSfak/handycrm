/**
 * Project Tasks JavaScript
 * Handles dynamic rows, calculations, and AJAX operations
 */

// ============================================================================
// MATERIAL ROWS
// ============================================================================

/**
 * Add a new material row to the form
 * @param {Object} data - Optional prepopulated data for edit mode
 */
function addMaterialRow(data = null) {
    materialCounter++;
    const container = document.getElementById('materials_container');
    const emptyMessage = document.getElementById('materials_empty');
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    const row = document.createElement('div');
    row.className = 'material-row border rounded p-3 mb-3 bg-light';
    row.dataset.index = materialCounter;
    
    row.innerHTML = `
        <div class="row g-2">
            <div class="col-md-12 mb-2">
                <label class="form-label small">
                    Ονομασία Υλικού
                    <span class="text-muted" style="font-size: 0.8rem;">(Ξεκινήστε να γράφετε για προτάσεις)</span>
                </label>
                <input type="text" 
                       class="form-control material-name-input" 
                       name="materials[${materialCounter}][name]" 
                       value="${data?.name || data?.description || ''}"
                       placeholder="π.χ. Τσιμέντο, Σωλήνας PVC, Καλώδιο"
                       autocomplete="off"
                       data-row-index="${materialCounter}"
                       required>
                <input type="hidden" 
                       name="materials[${materialCounter}][catalog_material_id]" 
                       value="${data?.catalog_material_id || ''}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Μονάδα Μέτρησης</label>
                <input type="text" 
                       class="form-control" 
                       name="materials[${materialCounter}][unit]" 
                       value="${data?.unit || ''}"
                       placeholder="τεμ, μέτρα, κιλά"
                       list="unit_types"
                       required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Τιμή Μονάδας (€)</label>
                <input type="number" 
                       class="form-control material-price" 
                       name="materials[${materialCounter}][unit_price]" 
                       value="${data?.unit_price || ''}"
                       step="0.01" 
                       min="0"
                       placeholder="0.00"
                       onchange="calculateMaterialSubtotal(${materialCounter})"
                       required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Ποσότητα</label>
                <input type="number" 
                       class="form-control material-quantity" 
                       name="materials[${materialCounter}][quantity]" 
                       value="${data?.quantity || ''}"
                       step="0.01" 
                       min="0"
                       placeholder="0"
                       onchange="calculateMaterialSubtotal(${materialCounter})"
                       required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Σύνολο</label>
                <div class="input-group">
                    <input type="text" 
                           class="form-control material-subtotal" 
                           id="material_subtotal_${materialCounter}"
                           value="${data ? (data.unit_price * data.quantity).toFixed(2) : '0.00'}"
                           readonly>
                    <span class="input-group-text">€</span>
                    <button type="button" 
                            class="btn btn-danger" 
                            onclick="removeMaterialRow(${materialCounter})"
                            title="Αφαίρεση">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(row);
    
    // Initialize autocomplete for the material name input
    const nameInput = row.querySelector('.material-name-input');
    if (nameInput && typeof window.initMaterialAutocomplete === 'function') {
        window.initMaterialAutocomplete(nameInput, materialCounter);
    }
    
    calculateGrandTotal();
}

/**
 * Remove a material row
 * @param {number} index - Row index to remove
 */
function removeMaterialRow(index) {
    const row = document.querySelector(`.material-row[data-index="${index}"]`);
    if (row) {
        row.remove();
        
        // Check if container is empty
        const container = document.getElementById('materials_container');
        const emptyMessage = document.getElementById('materials_empty');
        
        if (container.children.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        }
        
        calculateGrandTotal();
    }
}

/**
 * Calculate subtotal for a specific material row
 * @param {number} index - Row index
 */
function calculateMaterialSubtotal(index) {
    const row = document.querySelector(`.material-row[data-index="${index}"]`);
    if (!row) return;
    
    const price = parseFloat(row.querySelector('.material-price').value) || 0;
    const quantity = parseFloat(row.querySelector('.material-quantity').value) || 0;
    const subtotal = price * quantity;
    
    row.querySelector('.material-subtotal').value = subtotal.toFixed(2);
    calculateGrandTotal();
}

/**
 * Calculate total materials cost
 * @returns {number} Total materials cost
 */
function calculateMaterialsTotal() {
    let total = 0;
    document.querySelectorAll('.material-subtotal').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    return total;
}

// ============================================================================
// LABOR ROWS
// ============================================================================

/**
 * Add a new labor row to the form
 * @param {Object} data - Optional prepopulated data for edit mode
 */
function addLaborRow(data = null) {
    laborCounter++;
    const container = document.getElementById('labor_container');
    const emptyMessage = document.getElementById('labor_empty');
    
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    const row = document.createElement('div');
    row.className = 'labor-row border rounded p-3 mb-3 bg-light';
    row.dataset.index = laborCounter;
    
    // Build technician options
    let technicianOptions = '<option value="">Άλλο Εργατικό (χωρίς τεχνικό)</option>';
    if (typeof technicians !== 'undefined' && technicians.length > 0) {
        technicians.forEach(tech => {
            const selected = data?.technician_id == tech.id ? 'selected' : '';
            const roleLabel = tech.role_display || tech.role || 'Χρήστης';
            technicianOptions += `<option value="${tech.id}" data-rate="${tech.hourly_rate}" data-role-id="${tech.role_id || ''}" ${selected}>
                ${tech.name} (${roleLabel} - ${parseFloat(tech.hourly_rate).toFixed(2)}€/ώρα)
            </option>`;
        });
    }
    
    // Determine technician name and role for hidden fields
    let techName = data?.technician_name || '';
    let techRoleId = data?.role_id || '';
    
    // If we have a technician_id but no name, try to find it
    if (data?.technician_id && !techName && typeof technicians !== 'undefined') {
        const tech = technicians.find(t => t.id == data.technician_id);
        if (tech) {
            techName = tech.name;
            techRoleId = tech.role_id;
        }
    }
    
    // If still no name, use default
    if (!techName && !data?.technician_id) {
        techName = 'Άλλο Εργατικό';
        techRoleId = '';
    }
    
    row.innerHTML = `
        <div class="row g-2">
            <div class="col-md-6 mb-2">
                <label class="form-label small">Τεχνικός</label>
                <select class="form-select technician-select" 
                        name="labor[${laborCounter}][technician_id]"
                        onchange="loadTechnicianData(${laborCounter})">
                    ${technicianOptions}
                </select>
                <input type="hidden" 
                       name="labor[${laborCounter}][technician_name]" 
                       class="technician-name-hidden"
                       value="${techName}">
                <input type="hidden" 
                       name="labor[${laborCounter}][role_id]" 
                       class="technician-role-hidden"
                       value="${techRoleId}">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label small">Τιμή/Ώρα (€)</label>
                <input type="number" 
                       class="form-control labor-rate" 
                       name="labor[${laborCounter}][hourly_rate]" 
                       value="${data?.hourly_rate || ''}"
                       step="0.01" 
                       min="0"
                       placeholder="0.00"
                       onchange="calculateLaborSubtotal(${laborCounter})"
                       required>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Ώρες Εργασίας</label>
                <input type="number" 
                       class="form-control labor-hours" 
                       name="labor[${laborCounter}][hours_worked]" 
                       value="${data?.hours_worked || ''}"
                       step="0.5" 
                       min="0"
                       placeholder="0.0"
                       onchange="calculateLaborSubtotal(${laborCounter})"
                       required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Από</label>
                <input type="time" 
                       class="form-control labor-time-from" 
                       name="labor[${laborCounter}][time_from]" 
                       value="${data?.time_from ? data.time_from.substring(0, 5) : ''}"
                       onchange="calculateHoursFromTime(${laborCounter})">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Έως</label>
                <input type="time" 
                       class="form-control labor-time-to" 
                       name="labor[${laborCounter}][time_to]" 
                       value="${data?.time_to ? data.time_to.substring(0, 5) : ''}"
                       onchange="calculateHoursFromTime(${laborCounter})">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Σύνολο</label>
                <div class="input-group">
                    <input type="text" 
                           class="form-control labor-subtotal" 
                           id="labor_subtotal_${laborCounter}"
                           value="${data ? (data.hours_worked * data.hourly_rate).toFixed(2) : '0.00'}"
                           readonly>
                    <span class="input-group-text">€</span>
                    <button type="button" 
                            class="btn btn-danger" 
                            onclick="removeLaborRow(${laborCounter})"
                            title="Αφαίρεση">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(row);
    
    // If prepopulated data, trigger calculation
    if (data) {
        calculateLaborSubtotal(laborCounter);
    }
    
    calculateGrandTotal();
}

/**
 * Remove a labor row
 * @param {number} index - Row index to remove
 */
function removeLaborRow(index) {
    const row = document.querySelector(`.labor-row[data-index="${index}"]`);
    if (row) {
        row.remove();
        
        // Check if container is empty
        const container = document.getElementById('labor_container');
        const emptyMessage = document.getElementById('labor_empty');
        
        if (container.children.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        }
        
        calculateGrandTotal();
    }
}

/**
 * Load technician data when selected (AJAX)
 * @param {number} index - Row index
 */
function loadTechnicianData(index) {
    const row = document.querySelector(`.labor-row[data-index="${index}"]`);
    if (!row) return;
    
    const select = row.querySelector('.technician-select');
    const selectedOption = select.options[select.selectedIndex];
    const rate = selectedOption.dataset.rate;
    const roleId = selectedOption.dataset.roleId;
    const techId = select.value;
    
    // Find technician in the technicians array
    let techName = '';
    let techRoleId = '';
    
    if (techId && typeof technicians !== 'undefined') {
        const tech = technicians.find(t => t.id == techId);
        if (tech) {
            techName = tech.name;
            techRoleId = tech.role_id;
        }
    }
    
    // If no technician selected, use "Other"
    if (!techName) {
        techName = 'Άλλο Εργατικό';
        techRoleId = '';
    }
    
    // Update hidden fields
    const nameField = row.querySelector('.technician-name-hidden');
    const roleField = row.querySelector('.technician-role-hidden');
    if (nameField) nameField.value = techName;
    if (roleField) roleField.value = techRoleId;
    
    // Update hourly rate
    if (rate) {
        row.querySelector('.labor-rate').value = parseFloat(rate).toFixed(2);
        calculateLaborSubtotal(index);
    }
}

/**
 * Calculate hours from time range
 * @param {number} index - Row index
 */
function calculateHoursFromTime(index) {
    const row = document.querySelector(`.labor-row[data-index="${index}"]`);
    if (!row) return;
    
    const timeFrom = row.querySelector('.labor-time-from').value;
    const timeTo = row.querySelector('.labor-time-to').value;
    
    if (!timeFrom || !timeTo) return;
    
    // Convert times to minutes
    const [fromHour, fromMin] = timeFrom.split(':').map(Number);
    const [toHour, toMin] = timeTo.split(':').map(Number);
    
    let fromMinutes = fromHour * 60 + fromMin;
    let toMinutes = toHour * 60 + toMin;
    
    // Handle overnight shifts
    if (toMinutes < fromMinutes) {
        toMinutes += 24 * 60;
    }
    
    const diffMinutes = toMinutes - fromMinutes;
    const hours = diffMinutes / 60;
    
    row.querySelector('.labor-hours').value = hours.toFixed(1);
    calculateLaborSubtotal(index);
}

/**
 * Calculate subtotal for a specific labor row
 * @param {number} index - Row index
 */
function calculateLaborSubtotal(index) {
    const row = document.querySelector(`.labor-row[data-index="${index}"]`);
    if (!row) return;
    
    const hours = parseFloat(row.querySelector('.labor-hours').value) || 0;
    const rate = parseFloat(row.querySelector('.labor-rate').value) || 0;
    const subtotal = hours * rate;
    
    row.querySelector('.labor-subtotal').value = subtotal.toFixed(2);
    calculateGrandTotal();
}

/**
 * Calculate total labor cost
 * @returns {number} Total labor cost
 */
function calculateLaborTotal() {
    let total = 0;
    document.querySelectorAll('.labor-subtotal').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    return total;
}

// ============================================================================
// GRAND TOTAL CALCULATION
// ============================================================================

/**
 * Calculate and update grand total
 */
function calculateGrandTotal() {
    const materialsTotal = calculateMaterialsTotal();
    const laborTotal = calculateLaborTotal();
    const grandTotal = materialsTotal + laborTotal;
    
    // Update display elements
    const materialsTotalEl = document.getElementById('total_materials');
    const laborTotalEl = document.getElementById('total_labor');
    const grandTotalEl = document.getElementById('grand_total');
    
    if (materialsTotalEl) {
        materialsTotalEl.textContent = materialsTotal.toFixed(2);
    }
    
    if (laborTotalEl) {
        laborTotalEl.textContent = laborTotal.toFixed(2);
    }
    
    if (grandTotalEl) {
        grandTotalEl.textContent = grandTotal.toFixed(2);
    }
}

// ============================================================================
// OVERLAP DETECTION (AJAX)
// ============================================================================

/**
 * Check for overlapping tasks (AJAX call)
 */
function checkOverlap() {
    const dateFrom = document.getElementById('date_from')?.value;
    const dateTo = document.getElementById('date_to')?.value;
    const taskType = document.querySelector('input[name="task_type"]:checked')?.value;
    
    // Only check for date range tasks
    if (taskType !== 'date_range' || !dateFrom || !dateTo) {
        document.getElementById('overlap_warning').style.display = 'none';
        return;
    }
    
    // Prepare data
    const data = new FormData();
    data.append('project_id', projectId);
    data.append('date_from', dateFrom);
    data.append('date_to', dateTo);
    
    if (typeof taskId !== 'undefined') {
        data.append('exclude_task_id', taskId);
    }
    
    // AJAX request
    fetch(`${window.location.origin}/api/tasks/check-overlap`, {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(result => {
        const warningDiv = document.getElementById('overlap_warning');
        const listDiv = document.getElementById('overlap_list');
        
        if (result.overlaps && result.overlaps.length > 0) {
            // Show warning
            warningDiv.style.display = 'block';
            
            // Build list of overlapping tasks
            let html = '<ul class="mb-0">';
            result.overlaps.forEach(task => {
                html += `<li>${task.description} (${task.date_from} - ${task.date_to})</li>`;
            });
            html += '</ul>';
            
            listDiv.innerHTML = html;
        } else {
            warningDiv.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Overlap check error:', error);
    });
}

// ============================================================================
// FORM VALIDATION
// ============================================================================

/**
 * Ensure all labor rows have technician_name before submit
 */
function validateLaborBeforeSubmit() {
    document.querySelectorAll('.labor-row').forEach(row => {
        const index = row.dataset.index;
        const nameField = row.querySelector('.technician-name-hidden');
        const roleField = row.querySelector('.technician-role-hidden');
        const select = row.querySelector('.technician-select');
        
        // If technician_name is empty, fill it from selected option
        if (nameField && !nameField.value) {
            const techId = select ? select.value : '';
            
            if (techId && typeof technicians !== 'undefined') {
                const tech = technicians.find(t => t.id == techId);
                if (tech) {
                    nameField.value = tech.name;
                    if (roleField) roleField.value = tech.role;
                } else {
                    nameField.value = 'Άλλο Εργατικό';
                    if (roleField) roleField.value = 'other';
                }
            } else {
                nameField.value = 'Άλλο Εργατικό';
                if (roleField) roleField.value = 'other';
            }
        }
    });
}

// ============================================================================
// INITIALIZATION
// ============================================================================

// Auto-calculate on page load if editing
document.addEventListener('DOMContentLoaded', function() {
    // Attach form submit handler
    const form = document.getElementById('taskForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            validateLaborBeforeSubmit();
        });
    }
    // Recalculate all existing rows
    document.querySelectorAll('.material-row').forEach(row => {
        const index = row.dataset.index;
        calculateMaterialSubtotal(index);
    });
    
    document.querySelectorAll('.labor-row').forEach(row => {
        const index = row.dataset.index;
        calculateLaborSubtotal(index);
    });
    
    calculateGrandTotal();
});

// ============================================================================
// TECHNICIAN OVERLAP CHECK
// ============================================================================

/**
 * Check if a technician has overlapping tasks
 */
function checkTechnicianOverlap(technicianId, rowIndex) {
    if (!technicianId || technicianId === 'other') {
        // Clear any previous warnings for this row
        const warningDiv = document.getElementById(`technician_warning_${rowIndex}`);
        if (warningDiv) warningDiv.style.display = 'none';
        return;
    }
    
    // Get date range
    const taskType = document.querySelector('input[name="task_type"]:checked')?.value;
    let dateFrom, dateTo, taskDate;
    
    if (taskType === 'single_day') {
        taskDate = document.getElementById('task_date')?.value;
        if (!taskDate) return;
        dateFrom = dateTo = taskDate;
    } else {
        dateFrom = document.getElementById('date_from')?.value;
        dateTo = document.getElementById('date_to')?.value;
        if (!dateFrom || !dateTo) return;
    }
    
    // Prepare data
    const formData = new FormData();
    formData.append('technician_id', technicianId);
    formData.append('date_from', dateFrom);
    formData.append('date_to', dateTo);
    if (taskDate) formData.append('task_date', taskDate);
    if (typeof taskId !== 'undefined') {
        formData.append('exclude_task_id', taskId);
    }
    
    // AJAX request
    fetch(`${BASE_URL}/api/tasks/check-technician-overlap`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        const row = document.querySelector(`.labor-row[data-index="${rowIndex}"]`);
        if (!row) return;
        
        // Check if warning div exists, create if not
        let warningDiv = document.getElementById(`technician_warning_${rowIndex}`);
        if (!warningDiv) {
            warningDiv = document.createElement('div');
            warningDiv.id = `technician_warning_${rowIndex}`;
            warningDiv.className = 'alert alert-warning alert-sm mt-2 mb-0';
            row.appendChild(warningDiv);
        }
        
        if (result.has_overlap && result.tasks && result.tasks.length > 0) {
            // Show warning
            let html = `<i class="fas fa-exclamation-triangle me-2"></i>`;
            html += `<strong>Προσοχή!</strong> Ο τεχνικός έχει ήδη ${result.count} εργασί${result.count > 1 ? 'ες' : 'α'} στο ίδιο διάστημα:<br>`;
            html += '<ul class="mb-0 mt-1">';
            result.tasks.forEach(task => {
                const dateStr = task.task_type === 'single_day' 
                    ? new Date(task.task_date).toLocaleDateString('el-GR')
                    : `${new Date(task.date_from).toLocaleDateString('el-GR')} - ${new Date(task.date_to).toLocaleDateString('el-GR')}`;
                html += `<li>${task.description} - ${task.project_name || 'Έργο'} (${dateStr})</li>`;
            });
            html += '</ul>';
            warningDiv.innerHTML = html;
            warningDiv.style.display = 'block';
        } else {
            warningDiv.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Technician overlap check error:', error);
    });
}

// Attach technician overlap check to technician select changes
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically added rows
    document.getElementById('labor_table')?.addEventListener('change', function(e) {
        if (e.target.classList.contains('technician-select')) {
            const row = e.target.closest('.labor-row');
            if (row) {
                const rowIndex = row.dataset.index;
                const technicianId = e.target.value;
                checkTechnicianOverlap(technicianId, rowIndex);
            }
        }
    });
    
    // Also check when date changes
    ['task_date', 'date_from', 'date_to'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('change', function() {
                // Re-check all technicians
                document.querySelectorAll('.technician-select').forEach(select => {
                    const row = select.closest('.labor-row');
                    if (row && select.value && select.value !== 'other') {
                        checkTechnicianOverlap(select.value, row.dataset.index);
                    }
                });
            });
        }
    });
});
