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
                ${window.buildUnitSelectHTML(`materials[${materialCounter}][unit]`, data?.unit || '')}
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
 * Standard work day (start, end, hours) from Settings, exposed by the page as `workDay`.
 * Falls back to 07:30-15:30 (8h) if the page didn't provide it or the values are malformed.
 */
function getWorkDayDefaults() {
    const fallback = { start: '07:30', end: '15:30', hours: 8 };
    if (typeof workDay === 'undefined' || !workDay) {
        return fallback;
    }
    const timePattern = /^([01]\d|2[0-3]):[0-5]\d$/;
    const hours = parseFloat(workDay.hours);
    if (!timePattern.test(workDay.start) || !timePattern.test(workDay.end) || !(hours > 0)) {
        return fallback;
    }
    return { start: workDay.start, end: workDay.end, hours: hours };
}

function escapeHtmlAttr(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

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
    
    const isOtherTech = !data || !data.technician_id || data.technician_id === '0' || data.technician_id === 0 || Number(data.is_temporary) === 1;

    // Build technician options
    let technicianOptions = `<option value="" ${isOtherTech ? 'selected' : ''}>Άλλος Τεχνικός (προσωρινός / εξωτερικός)</option>`;
    if (typeof technicians !== 'undefined' && technicians.length > 0) {
        technicians.forEach(tech => {
            const selected = (!isOtherTech && data?.technician_id == tech.id) ? 'selected' : '';
            const roleLabel = tech.role_display || tech.role || 'Χρήστης';
            technicianOptions += `<option value="${tech.id}" data-rate="${tech.hourly_rate}" data-role-id="${tech.role_id || ''}" ${selected}>
                ${escapeHtmlAttr(tech.name)} (${escapeHtmlAttr(roleLabel)} - ${parseFloat(tech.hourly_rate).toFixed(2)}€/ώρα)
            </option>`;
        });
    }
    
    const workDayDefaults = getWorkDayDefaults();
    const defaultTimeFrom = data?.time_from ? data.time_from.substring(0, 5) : workDayDefaults.start;
    const defaultTimeTo   = data?.time_to   ? data.time_to.substring(0, 5)   : workDayDefaults.end;
    // New rows start with the standard work day length; existing rows keep their saved hours
    const defaultHours    = data ? (data.hours_worked || '') : workDayDefaults.hours;
    let techName = (data?.technician_name ?? '').toString().trim();
    let techRoleId = data?.role_id || '';
    let customNameValue = '';
    
    if (!isOtherTech && typeof technicians !== 'undefined') {
        const tech = technicians.find(t => t.id == data.technician_id);
        if (tech) {
            if (!techName) techName = tech.name;
            if (!techRoleId) techRoleId = tech.role_id || '';
        }
    } else if (isOtherTech) {
        customNameValue = techName;
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
                <div class="custom-technician-wrapper mt-2" style="display: ${isOtherTech ? 'block' : 'none'};">
                    <label class="form-label small mb-1">Όνομα Τεχνικού <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control form-control-sm custom-technician-name"
                           name="labor[${laborCounter}][custom_technician_name]"
                           value="${escapeHtmlAttr(customNameValue)}"
                           placeholder="Ονοματεπώνυμο τεχνικού (π.χ. εξωτερικός)"
                           maxlength="100"
                           autocomplete="off"
                           oninput="syncCustomTechnicianName(${laborCounter})"
                           ${isOtherTech ? 'required' : ''}>
                </div>
                <input type="hidden" 
                       name="labor[${laborCounter}][technician_name]" 
                       class="technician-name-hidden"
                       value="${escapeHtmlAttr(techName)}">
                <input type="hidden" 
                       name="labor[${laborCounter}][role_id]" 
                       class="technician-role-hidden"
                       value="${escapeHtmlAttr(techRoleId)}">
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
                       value="${defaultHours}"
                       step="any"
                       min="0"
                       placeholder="0.0"
                       onchange="calculateTimeToFromHours(${laborCounter})"
                       required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Από</label>
                <input type="time" 
                       class="form-control labor-time-from" 
                       name="labor[${laborCounter}][time_from]" 
                       value="${defaultTimeFrom}"
                       onchange="calculateHoursFromTime(${laborCounter})">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Έως</label>
                <input type="time" 
                       class="form-control labor-time-to" 
                       name="labor[${laborCounter}][time_to]" 
                       value="${defaultTimeTo}"
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

    // Ensure DOM values are explicitly populated after append
    const selectEl = row.querySelector('.technician-select');
    if (selectEl) {
        selectEl.value = isOtherTech ? '' : String(data.technician_id);
    }
    const hiddenNameEl = row.querySelector('.technician-name-hidden');
    if (hiddenNameEl) {
        hiddenNameEl.value = techName;
    }
    if (isOtherTech) {
        const customInputEl = row.querySelector('.custom-technician-name');
        if (customInputEl) {
            customInputEl.value = customNameValue;
        }
    }
    
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
 * Sync custom technician name input to hidden technician_name field
 * @param {number} index - Row index
 */
function syncCustomTechnicianName(index) {
    const row = document.querySelector(`.labor-row[data-index="${index}"]`);
    if (!row) return;
    const customInput = row.querySelector('.custom-technician-name');
    const nameField = row.querySelector('.technician-name-hidden');
    if (nameField && customInput) {
        nameField.value = customInput.value.trim();
    }
}

/**
 * Load technician data when selected
 * @param {number} index - Row index
 */
function loadTechnicianData(index) {
    const row = document.querySelector(`.labor-row[data-index="${index}"]`);
    if (!row) return;
    
    const select = row.querySelector('.technician-select');
    const selectedOption = select.options[select.selectedIndex];
    const rate = selectedOption ? selectedOption.dataset.rate : '';
    const techId = select.value;
    
    const nameField = row.querySelector('.technician-name-hidden');
    const roleField = row.querySelector('.technician-role-hidden');
    const customWrapper = row.querySelector('.custom-technician-wrapper');
    const customInput = row.querySelector('.custom-technician-name');
    
    if (techId && typeof technicians !== 'undefined') {
        const tech = technicians.find(t => t.id == techId);
        if (tech) {
            if (nameField) nameField.value = tech.name;
            if (roleField) roleField.value = tech.role_id || '';
        }
        if (customWrapper) customWrapper.style.display = 'none';
        if (customInput) customInput.required = false;
        
        // Update hourly rate for selected company technician
        if (rate) {
            row.querySelector('.labor-rate').value = parseFloat(rate).toFixed(2);
            calculateLaborSubtotal(index);
        }
    } else {
        // "Άλλος Τεχνικός": open custom name field and clear role_id
        if (customWrapper) customWrapper.style.display = 'block';
        if (customInput) {
            customInput.required = true;
            customInput.focus();
        }
        if (nameField) nameField.value = customInput ? customInput.value.trim() : '';
        if (roleField) roleField.value = '';
    }
}

/**
 * Calculate time_to from time_from + hours_worked
 * Called when the user edits the hours field directly
 * @param {number} index - Row index
 */
function calculateTimeToFromHours(index) {
    const row = document.querySelector(`.labor-row[data-index="${index}"]`);
    if (!row) return;

    const hoursInput = row.querySelector('.labor-hours');
    const hours = parseFloat(hoursInput.value);

    const timeFromInput = row.querySelector('.labor-time-from');
    const timeFrom = timeFromInput ? timeFromInput.value : '';

    if (timeFrom && !isNaN(hours) && hours > 0) {
        const [fromHour, fromMin] = timeFrom.split(':').map(Number);
        const totalMinutes = fromHour * 60 + fromMin + Math.round(hours * 60);
        const toHour = Math.floor(totalMinutes / 60) % 24;
        const toMin = totalMinutes % 60;
        const timeToStr = String(toHour).padStart(2, '0') + ':' + String(toMin).padStart(2, '0');
        const timeToInput = row.querySelector('.labor-time-to');
        if (timeToInput) timeToInput.value = timeToStr;
    }

    calculateLaborSubtotal(index);
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
    
    const hoursInput = row.querySelector('.labor-hours');
    const hours = parseFloat(hoursInput.value) || 0;
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

    const csrfMetaOverlap = document.querySelector('meta[name="csrf-token"]');
    if (csrfMetaOverlap) {
        data.append('csrf_token', csrfMetaOverlap.content);
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
        const nameField = row.querySelector('.technician-name-hidden');
        const roleField = row.querySelector('.technician-role-hidden');
        const customInput = row.querySelector('.custom-technician-name');
        const select = row.querySelector('.technician-select');
        const techId = select ? select.value : '';

        if (!techId) {
            // Temporary / external technician: never send 'other' as role_id
            if (roleField) roleField.value = '';
            const customName = customInput ? customInput.value.trim() : '';
            if (nameField) {
                nameField.value = customName || 'Άλλος Τεχνικός';
            }
            return;
        }

        if (typeof technicians !== 'undefined') {
            const tech = technicians.find(t => t.id == techId);
            if (tech) {
                if (nameField && !nameField.value.trim()) nameField.value = tech.name;
                if (roleField) roleField.value = tech.role_id || '';
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
            // Sync all labor rows: recalculate hours from time fields before submit
            // so that whatever the user last changed (hours OR time) is reflected correctly
            document.querySelectorAll('.labor-row').forEach(row => {
                const index = row.dataset.index;
                calculateHoursFromTime(index);
            });
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

    const csrfMetaTech = document.querySelector('meta[name="csrf-token"]');
    if (csrfMetaTech) {
        formData.append('csrf_token', csrfMetaTech.content);
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
