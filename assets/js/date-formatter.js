/**
 * HandyCRM Date Formatter
 * Converts all date inputs to use dd/mm/yyyy format with calendar picker
 * 
 * @author Theodore Sfakianakis
 * @version 2.0
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
        
        // Wrap input in input-group if not already wrapped
        if (!input.parentElement.classList.contains('input-group')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'input-group';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            
            // Add calendar button
            const calendarBtn = document.createElement('button');
            calendarBtn.className = 'btn btn-outline-secondary';
            calendarBtn.type = 'button';
            calendarBtn.innerHTML = '<i class="fas fa-calendar-alt"></i>';
            calendarBtn.title = 'Επιλογή ημερομηνίας';
            wrapper.appendChild(calendarBtn);
            
            // Add click event to open native date picker
            calendarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showDatePicker(input);
            });
        }
        
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

/**
 * Show a simple date picker modal
 */
function showDatePicker(input) {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    // Create calendar container
    const calendar = document.createElement('div');
    calendar.style.cssText = `
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        max-width: 320px;
        width: 90%;
    `;
    
    // Parse current value or use today
    let currentDate = new Date();
    if (input.value && input.value.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
        const [day, month, year] = input.value.split('/');
        currentDate = new Date(year, month - 1, day);
    }
    
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    
    function renderCalendar() {
        const monthNames = ['Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος',
                           'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος'];
        const dayNames = ['Κυ', 'Δε', 'Τρ', 'Τε', 'Πε', 'Πα', 'Σα'];
        
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        let html = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <button type="button" class="btn btn-sm btn-outline-primary" id="prevMonth">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <strong>${monthNames[currentMonth]} ${currentYear}</strong>
                <button type="button" class="btn btn-sm btn-outline-primary" id="nextMonth">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; text-align: center;">
        `;
        
        // Day headers
        dayNames.forEach(day => {
            html += `<div style="font-weight: bold; padding: 5px; font-size: 12px;">${day}</div>`;
        });
        
        // Empty cells for days before month starts
        for (let i = 0; i < firstDay; i++) {
            html += '<div></div>';
        }
        
        // Days of month
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(currentYear, currentMonth, day);
            const isToday = date.toDateString() === today.toDateString();
            const isSelected = input.value === `${String(day).padStart(2, '0')}/${String(currentMonth + 1).padStart(2, '0')}/${currentYear}`;
            
            html += `
                <button type="button" class="btn btn-sm ${isSelected ? 'btn-primary' : isToday ? 'btn-outline-primary' : 'btn-outline-secondary'}" 
                        style="padding: 8px; font-size: 12px;" data-day="${day}">
                    ${day}
                </button>
            `;
        }
        
        html += `
            </div>
            <div style="margin-top: 15px; display: flex; gap: 10px;">
                <button type="button" class="btn btn-sm btn-secondary flex-fill" id="cancelDate">Άκυρο</button>
                <button type="button" class="btn btn-sm btn-primary flex-fill" id="todayDate">Σήμερα</button>
            </div>
        `;
        
        calendar.innerHTML = html;
        
        // Add event listeners
        calendar.querySelector('#prevMonth').addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        });
        
        calendar.querySelector('#nextMonth').addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        });
        
        calendar.querySelector('#todayDate').addEventListener('click', () => {
            const today = new Date();
            input.value = `${String(today.getDate()).padStart(2, '0')}/${String(today.getMonth() + 1).padStart(2, '0')}/${today.getFullYear()}`;
            document.body.removeChild(overlay);
        });
        
        calendar.querySelector('#cancelDate').addEventListener('click', () => {
            document.body.removeChild(overlay);
        });
        
        // Day selection
        calendar.querySelectorAll('[data-day]').forEach(btn => {
            btn.addEventListener('click', function() {
                const day = this.getAttribute('data-day');
                input.value = `${String(day).padStart(2, '0')}/${String(currentMonth + 1).padStart(2, '0')}/${currentYear}`;
                document.body.removeChild(overlay);
            });
        });
    }
    
    renderCalendar();
    overlay.appendChild(calendar);
    document.body.appendChild(overlay);
    
    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            document.body.removeChild(overlay);
        }
    });
}
