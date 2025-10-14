<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-calendar-alt"></i> <?= __('appointments.calendar') ?></h2>
    <div>
        <a href="?route=/appointments" class="btn btn-secondary me-2">
            <i class="fas fa-list"></i> <?= __('appointments.appointment_list') ?>
        </a>
        <a href="?route=/appointments/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?= __('appointments.new_appointment') ?>
        </a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div id="calendar-container">
                    <!-- Calendar will be rendered here -->
                    <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-outline-secondary" id="prevMonth">
                            <i class="fas fa-chevron-left"></i> <?= __('appointments.previous') ?>
                        </button>
                        <h4 id="currentMonth" class="mb-0"></h4>
                        <button class="btn btn-outline-secondary" id="nextMonth">
                            <?= __('appointments.next') ?> <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <div class="row mb-2 text-center fw-bold">
                        <div class="col"><?= __('appointments.mon') ?></div>
                        <div class="col"><?= __('appointments.tue') ?></div>
                        <div class="col"><?= __('appointments.wed') ?></div>
                        <div class="col"><?= __('appointments.thu') ?></div>
                        <div class="col"><?= __('appointments.fri') ?></div>
                        <div class="col"><?= __('appointments.sat') ?></div>
                        <div class="col"><?= __('appointments.sun') ?></div>
                    </div>
                    
                    <div id="calendarDays"></div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter"></i> <?= __('appointments.filters') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?= __('appointments.technician') ?></label>
                            <select class="form-select" id="filterTechnician">
                                <option value=""><?= __('appointments.all_technicians') ?></option>
                                <!-- Will be populated via AJAX or PHP -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?= __('appointments.status') ?></label>
                            <select class="form-select" id="filterStatus">
                                <option value=""><?= __('appointments.all_statuses') ?></option>
                                <option value="scheduled"><?= __('appointments.scheduled') ?></option>
                                <option value="confirmed"><?= __('appointments.confirmed') ?></option>
                                <option value="in_progress"><?= __('appointments.in_progress') ?></option>
                                <option value="completed"><?= __('appointments.completed') ?></option>
                            </select>
                        </div>
                        
                        <button class="btn btn-primary w-100" onclick="refreshCalendar()">
                            <i class="fas fa-sync"></i> <?= __('appointments.refresh') ?>
                        </button>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> <?= __('appointments.selected_day') ?>
                        </h5>
                    </div>
                    <div class="card-body" id="selectedDayInfo">
                        <p class="text-muted"><?= __('appointments.select_day_info') ?></p>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('appointments.legend') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info me-2">●</span>
                            <small><?= __('appointments.scheduled') ?></small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">●</span>
                            <small><?= __('appointments.confirmed') ?></small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-2">●</span>
                            <small><?= __('appointments.in_progress') ?></small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary me-2">●</span>
                            <small><?= __('appointments.completed') ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-day {
    border: 1px solid #dee2e6;
    min-height: 100px;
    padding: 5px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.calendar-day:hover {
    background-color: #f8f9fa;
}

.calendar-day.other-month {
    background-color: #f8f9fa;
    color: #adb5bd;
}

.calendar-day.today {
    background-color: #fff3cd;
    font-weight: bold;
}

.calendar-day.selected {
    background-color: #cfe2ff;
    border-color: #0d6efd;
}

.calendar-day-number {
    font-size: 0.9rem;
    font-weight: bold;
}

.appointment-badge {
    font-size: 0.7rem;
    display: block;
    margin: 2px 0;
    padding: 2px 4px;
    border-radius: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
let currentDate = new Date();
let appointments = [];

// Month names - dynamically loaded from translations
const monthNames = [
    '<?= __('appointments.january') ?>',
    '<?= __('appointments.february') ?>',
    '<?= __('appointments.march') ?>',
    '<?= __('appointments.april') ?>',
    '<?= __('appointments.may') ?>',
    '<?= __('appointments.june') ?>',
    '<?= __('appointments.july') ?>',
    '<?= __('appointments.august') ?>',
    '<?= __('appointments.september') ?>',
    '<?= __('appointments.october') ?>',
    '<?= __('appointments.november') ?>',
    '<?= __('appointments.december') ?>'
];

// Status colors
const statusColors = {
    'scheduled': 'info',
    'confirmed': 'success',
    'in_progress': 'primary',
    'completed': 'secondary'
};

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update header
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
    
    // Get first day of month (0 = Sunday, need to adjust for Monday start)
    const firstDay = new Date(year, month, 1);
    let firstDayOfWeek = firstDay.getDay();
    firstDayOfWeek = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1; // Adjust for Monday start
    
    // Get last day of month
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    
    // Get last day of previous month
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    
    const calendarDays = document.getElementById('calendarDays');
    calendarDays.innerHTML = '';
    
    let dayCounter = 1;
    let nextMonthCounter = 1;
    
    // Create 6 rows (weeks)
    for (let week = 0; week < 6; week++) {
        const row = document.createElement('div');
        row.className = 'row g-1 mb-1';
        
        // Create 7 days
        for (let day = 0; day < 7; day++) {
            const cell = document.createElement('div');
            cell.className = 'col';
            
            const dayDiv = document.createElement('div');
            dayDiv.className = 'calendar-day';
            
            let dayNumber;
            let isCurrentMonth = true;
            
            // Previous month days
            if (week === 0 && day < firstDayOfWeek) {
                dayNumber = prevMonthLastDay - firstDayOfWeek + day + 1;
                dayDiv.classList.add('other-month');
                isCurrentMonth = false;
            }
            // Current month days
            else if (dayCounter <= daysInMonth) {
                dayNumber = dayCounter;
                
                // Check if today
                const today = new Date();
                if (year === today.getFullYear() && 
                    month === today.getMonth() && 
                    dayNumber === today.getDate()) {
                    dayDiv.classList.add('today');
                }
                
                dayCounter++;
            }
            // Next month days
            else {
                dayNumber = nextMonthCounter;
                dayDiv.classList.add('other-month');
                isCurrentMonth = false;
                nextMonthCounter++;
            }
            
            const dateStr = isCurrentMonth ? 
                `${year}-${String(month + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}` : '';
            
            dayDiv.innerHTML = `<div class="calendar-day-number">${dayNumber}</div>`;
            
            // Add appointments for this day
            if (isCurrentMonth && dateStr) {
                const dayAppointments = appointments.filter(apt => 
                    apt.appointment_date.startsWith(dateStr)
                );
                
                dayAppointments.forEach(apt => {
                    const badge = document.createElement('div');
                    badge.className = `appointment-badge bg-${statusColors[apt.status] || 'secondary'} text-white`;
                    badge.textContent = new Date(apt.appointment_date).toLocaleTimeString('el-GR', {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) + ' - ' + apt.title;
                    badge.title = apt.title;
                    dayDiv.appendChild(badge);
                });
                
                dayDiv.onclick = () => showDayDetails(dateStr);
            }
            
            cell.appendChild(dayDiv);
            row.appendChild(cell);
        }
        
        calendarDays.appendChild(row);
        
        // Break if we've filled all days
        if (dayCounter > daysInMonth && nextMonthCounter > 7) break;
    }
}

function showDayDetails(dateStr) {
    // Remove previous selection
    document.querySelectorAll('.calendar-day.selected').forEach(el => {
        el.classList.remove('selected');
    });
    
    // Add selection to clicked day
    event.currentTarget.classList.add('selected');
    
    const dayAppointments = appointments.filter(apt => 
        apt.appointment_date.startsWith(dateStr)
    );
    
    const container = document.getElementById('selectedDayInfo');
    
    if (dayAppointments.length === 0) {
        container.innerHTML = `
            <p class="text-muted">Δεν υπάρχουν ραντεβού για ${new Date(dateStr).toLocaleDateString('el-GR')}</p>
            <a href="?route=/appointments/create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Νέο Ραντεβού
            </a>
        `;
    } else {
        let html = `<h6>${new Date(dateStr).toLocaleDateString('el-GR', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        })}</h6>`;
        
        dayAppointments.forEach(apt => {
            html += `
                <div class="card mb-2">
                    <div class="card-body p-2">
                        <h6 class="mb-1">
                            <span class="badge bg-${statusColors[apt.status]}">${apt.status}</span>
                            ${apt.title}
                        </h6>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> ${new Date(apt.appointment_date).toLocaleTimeString('el-GR', {
                                hour: '2-digit',
                                minute: '2-digit'
                            })}
                        </small>
                        <div class="mt-2">
                            <a href="?route=/appointments/details&id=${apt.id}" class="btn btn-sm btn-primary">
                                Προβολή
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
}

function loadAppointments() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const startDate = new Date(year, month, 1).toISOString().split('T')[0];
    const endDate = new Date(year, month + 1, 0).toISOString().split('T')[0];
    
    // Fetch appointments from API
    fetch(`?route=/appointments/api/list&start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            appointments = data;
            renderCalendar();
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            appointments = [];
            renderCalendar();
        });
}

function refreshCalendar() {
    loadAppointments();
}

// Navigation
document.getElementById('prevMonth').addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    loadAppointments();
});

document.getElementById('nextMonth').addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    loadAppointments();
});

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    loadAppointments();
});
</script>
