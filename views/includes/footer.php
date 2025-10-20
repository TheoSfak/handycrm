<?php
// Prevent duplicate footer rendering
if (defined('FOOTER_RENDERED')) {
    return;
}
define('FOOTER_RENDERED', true);
?>
        </div> <!-- End content-wrapper -->
        
        <!-- Application Footer -->
        <footer class="app-footer mt-auto py-3 border-top">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center text-md-start mb-2 mb-md-0">
                        <span class="text-muted">
                            <i class="fas fa-briefcase"></i> <strong>HandyCRM</strong> 
                            <span class="badge bg-primary ms-1">v<?php 
                                require_once 'classes/UpdateChecker.php';
                                $versionChecker = new UpdateChecker();
                                echo $versionChecker->getCurrentVersion();
                            ?></span>
                        </span>
                    </div>
                    <div class="col-md-4 text-center mb-2 mb-md-0">
                        <span class="text-muted">
                            <i class="fas fa-code"></i> Created by <strong>Theodore Sfakianakis</strong>
                        </span>
                    </div>
                    <div class="col-md-4 text-center text-md-end">
                        <span class="text-muted">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:theodore.sfakianakis@gmail.com" class="text-decoration-none">
                                theodore.sfakianakis@gmail.com
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        </footer>
        
    </div> <!-- End main-content -->
    
    <!-- jQuery (load first for compatibility) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target) && 
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // CSRF Token for AJAX requests
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                    xhr.setRequestHeader("X-CSRFToken", $('meta[name=csrf-token]').attr('content'));
                }
            }
        });
        
        // Load notifications
        function loadNotifications() {
            $.get('/api/notifications', function(data) {
                if (data.success && data.notifications) {
                    const notificationsList = $('#notifications-list');
                    const notificationCount = $('#notification-count');
                    
                    // Clear existing notifications
                    notificationsList.find('li:not(.dropdown-header):not(.dropdown-divider)').remove();
                    
                    if (data.notifications.length > 0) {
                        notificationCount.text(data.notifications.length).show();
                        
                        data.notifications.forEach(function(notification) {
                            const notificationItem = $(`
                                <li>
                                    <a class="dropdown-item notification-item" href="#" data-id="${notification.id}">
                                        <div class="d-flex align-items-start">
                                            <i class="fas fa-${getNotificationIcon(notification.type)} me-2 text-primary"></i>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">${notification.title}</h6>
                                                <p class="mb-1 small text-muted">${notification.message}</p>
                                                <small class="text-muted">${formatDate(notification.created_at)}</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            `);
                            notificationsList.append(notificationItem);
                        });
                    } else {
                        notificationCount.text('0').hide();
                        notificationsList.append('<li><a class="dropdown-item text-muted" href="#">Δεν υπάρχουν νέες ειδοποιήσεις</a></li>');
                    }
                }
            });
        }
        
        // Get notification icon based on type
        function getNotificationIcon(type) {
            switch(type) {
                case 'appointment_reminder': return 'calendar-alt';
                case 'project_deadline': return 'exclamation-triangle';
                case 'low_stock': return 'boxes';
                case 'payment_due': return 'credit-card';
                default: return 'bell';
            }
        }
        
        // Format date for notifications
        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (minutes < 60) {
                return minutes + ' λεπτά πριν';
            } else if (hours < 24) {
                return hours + ' ώρες πριν';
            } else {
                return days + ' μέρες πριν';
            }
        }
        
        // Mark notification as read
        $(document).on('click', '.notification-item', function(e) {
            e.preventDefault();
            const notificationId = $(this).data('id');
            
            $.post('/api/notifications/mark-read', {
                notification_id: notificationId
            }, function(data) {
                if (data.success) {
                    loadNotifications();
                }
            });
        });
        
        // Check for updates
        function checkForUpdates() {
            $.ajax({
                url: '<?= BASE_URL ?>/api/check-updates.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.success && data.update_available) {
                        // Add update notification to the bell
                        addUpdateNotification(data.update_info);
                    }
                },
                error: function() {
                    console.log('Failed to check for updates');
                }
            });
        }
        
        // Add update notification to notification list
        function addUpdateNotification(updateInfo) {
            const notificationsList = $('#notifications-list');
            const notificationCount = $('#notification-count');
            
            // Check if update notification already exists
            if ($('.update-notification').length > 0) {
                return;
            }
            
            // Remove "no notifications" message if exists
            notificationsList.find('.text-muted').parent().remove();
            
            // Add update notification at the top
            const updateNotification = $(`
                <li>
                    <a class="dropdown-item notification-item update-notification" 
                       href="<?= BASE_URL ?>/settings/update" 
                       style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; margin: 0.5rem;">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="fas fa-rocket fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><i class="fas fa-star"></i> Νέα Έκδοση Διαθέσιμη!</h6>
                                <p class="mb-1 small">v${updateInfo.version} - Κάντε κλικ για ενημέρωση</p>
                                <small>${new Date(updateInfo.published_at).toLocaleDateString('el-GR')}</small>
                            </div>
                        </div>
                    </a>
                </li>
            `);
            
            // Insert after header and divider
            notificationsList.find('.dropdown-divider').after(updateNotification);
            
            // Update notification count
            let currentCount = parseInt(notificationCount.text()) || 0;
            currentCount += 1;
            notificationCount.text(currentCount).show();
        }
        
        // Load notifications on page load
        $(document).ready(function() {
            <?php if (isset($_SESSION['user_id'])): ?>
            loadNotifications();
            
            // Check for updates on load
            checkForUpdates();
            
            // Refresh notifications every 5 minutes
            setInterval(loadNotifications, 300000);
            
            // Check for updates every hour
            setInterval(checkForUpdates, 3600000);
            <?php endif; ?>
        });
        
        // Confirmation dialogs helper (not used by index pages - they have their own confirmDelete)
        function confirmAction(message) {
            return confirm(message || 'Είστε σίγουρος ότι θέλετε να διαγράψετε αυτό το στοιχείο;');
        }
        
        // Form validation helper
        function validateForm(formId, rules) {
            const form = document.getElementById(formId);
            let isValid = true;
            
            // Clear previous errors
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            
            // Validate each field
            Object.keys(rules).forEach(fieldName => {
                const field = form.querySelector(`[name="${fieldName}"]`);
                const rule = rules[fieldName];
                
                if (rule.required && (!field.value || field.value.trim() === '')) {
                    showFieldError(field, rule.message || 'Το πεδίο είναι υποχρεωτικό');
                    isValid = false;
                }
                
                if (rule.email && field.value && !isValidEmail(field.value)) {
                    showFieldError(field, 'Παρακαλώ εισάγετε έγκυρο email');
                    isValid = false;
                }
                
                if (rule.phone && field.value && !isValidPhone(field.value)) {
                    showFieldError(field, 'Παρακαλώ εισάγετε έγκυρο τηλέφωνο');
                    isValid = false;
                }
            });
            
            return isValid;
        }
        
        function showFieldError(field, message) {
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function isValidPhone(phone) {
            const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
            return phoneRegex.test(phone);
        }
        
        // Currency formatting
        function formatCurrency(amount) {
            return new Intl.NumberFormat('el-GR', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        }
        
        // Date formatting
        function formatDate(dateString, format = 'dd/mm/yyyy') {
            const date = new Date(dateString);
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            
            return `${day}/${month}/${year}`;
        }
        
        // Search functionality
        function initSearch(inputId, searchUrl, resultCallback) {
            const searchInput = document.getElementById(inputId);
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        $.get(searchUrl + '?q=' + encodeURIComponent(query), resultCallback);
                    }, 300);
                }
            });
        }
        
        // File upload preview
        function previewFile(input, previewContainer) {
            const file = input.files[0];
            const container = document.getElementById(previewContainer);
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        container.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">`;
                    } else {
                        container.innerHTML = `<div class="alert alert-info"><i class="fas fa-file"></i> ${file.name}</div>`;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                container.innerHTML = '';
            }
        }
    </script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_scripts)): ?>
        <script>
            <?= $inline_scripts ?>
        </script>
    <?php endif; ?>
    
    <!-- Date Formatter - Converts all date inputs to dd/mm/yyyy format -->
    <script src="<?= BASE_URL ?>/assets/js/date-formatter.js"></script>
</body>
</html>