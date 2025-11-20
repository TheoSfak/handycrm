<?php
/**
 * Settings Controller
 * Handles application settings (Admin only)
 */

class SettingsController extends BaseController {
    
    /**
     * Show settings page
     */
    public function index() {
        $user = $this->getCurrentUser();
        
        $database = new Database();
        $db = $database->connect();
        
        // Get all settings from settings table
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $settingsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to key-value array
        $settings = [];
        foreach ($settingsArray as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        // Set defaults if not exists
        $defaults = [
            'company_name' => '',
            'company_display_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_tax_id' => '',
            'company_website' => '',
            'default_vat_rate' => '24',
            'display_vat_notes' => '1',
            'prices_include_vat' => '0',
            'currency' => 'EUR',
            'currency_symbol' => '€',
            'date_format' => 'd/m/Y',
            'items_per_page' => '20',
            'maintenance_price_1_transformer' => '400.00',
            'maintenance_price_2_transformers' => '600.00',
            'maintenance_price_3_transformers' => '900.00'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        
        // Initialize language manager
        global $lang;
        
        $data = [
            'title' => __('settings.title') . ' - ' . APP_NAME,
            'user' => $user,
            'settings' => $settings,
            'lang' => $lang
        ];
        
        $this->view('settings/index', $data);
    }
    
    /**
     * Update settings
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
        }
        
        if (!DEBUG_MODE) {
            try {
                $this->validateCsrfToken();
            } catch (Exception $e) {
                $_SESSION['error'] = 'Μη έγκυρο token ασφαλείας';
                $this->redirect('/settings');
            }
        }
        
        $database = new Database();
        $db = $database->connect();
        
        try {
            $db->beginTransaction();
            
            // Handle logo upload
            $logoPath = null;
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
                $logoPath = $this->handleLogoUpload($_FILES['company_logo']);
                if ($logoPath === false) {
                    throw new Exception('Αποτυχία ανεβάσματος λογότυπου');
                }
            }
            
            // Handle logo removal
            if (isset($_POST['remove_logo']) && $_POST['remove_logo'] === '1') {
                $this->removeLogo($db);
                $logoPath = ''; // Clear logo
            }
            
            // List of allowed settings
            $allowedSettings = [
                'company_name',
                'company_display_name',
                'company_address',
                'company_phone',
                'company_email',
                'company_tax_id',
                'company_website',
                'default_vat_rate',
                'display_vat_notes',
                'prices_include_vat',
                'currency',
                'currency_symbol',
                'date_format',
                'items_per_page'
            ];
            
            // Maintenance pricing settings (stored in smtp_settings table)
            $maintenancePricing = [
                'maintenance_price_1_transformer',
                'maintenance_price_2_transformers',
                'maintenance_price_3_transformers'
            ];
            
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE setting_value = ?");
            
            foreach ($allowedSettings as $key) {
                // Handle checkboxes specially
                if (in_array($key, ['display_vat_notes', 'prices_include_vat'])) {
                    $value = isset($_POST[$key]) ? '1' : '0';
                    $stmt->execute([$key, $value, $value]);
                } elseif (isset($_POST[$key])) {
                    $value = trim($_POST[$key]);
                    $stmt->execute([$key, $value, $value]);
                }
            }
            
            // Save maintenance pricing to settings table
            $settingsStmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_type) VALUES (?, ?, 'decimal') 
                                      ON DUPLICATE KEY UPDATE setting_value = ?");
            
            foreach ($maintenancePricing as $key) {
                if (isset($_POST[$key])) {
                    $value = floatval($_POST[$key]);
                    $settingsStmt->execute([$key, $value, $value]);
                }
            }
            
            // Save logo path if uploaded
            if ($logoPath !== null) {
                $stmt->execute(['company_logo', $logoPath, $logoPath]);
            }
            
            $db->commit();
            $_SESSION['success'] = __('settings.success');
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Settings update error: " . $e->getMessage());
            $_SESSION['error'] = __('settings.error') . ': ' . $e->getMessage();
        }
        
        $this->redirect('/settings');
    }
    
    /**
     * Handle logo file upload
     */
    private function handleLogoUpload($file) {
        // Validate file
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = 'Μη έγκυρος τύπος αρχείου. Χρησιμοποιήστε PNG, JPG, GIF ή WebP';
            return false;
        }
        
        if ($file['size'] > $maxSize) {
            $_SESSION['error'] = 'Το αρχείο είναι πολύ μεγάλο. Μέγιστο μέγεθος: 2MB';
            return false;
        }
        
        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../uploads/company/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Return relative path for database (without leading slash for BASE_URL compatibility)
            return 'uploads/company/' . $filename;
        }
        
        return false;
    }
    
    /**
     * Remove company logo
     */
    private function removeLogo($db) {
        // Get current logo path
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && !empty($result['setting_value'])) {
            // Path is stored as "uploads/company/filename.ext"
            $logoPath = __DIR__ . '/../' . $result['setting_value'];
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
        }
    }
    
    /**
     * Show reset data page and handle reset
     */
    public function resetData() {
        $user = $this->getCurrentUser();
        
        // Only admin can reset data
        if ($user['role'] !== 'admin') {
            $_SESSION['error'] = 'Δεν έχετε δικαίωμα πρόσβασης';
            $this->redirect('/dashboard');
        }
        
        $database = new Database();
        $db = $database->connect();
        
        $success = false;
        $error = '';
        
        // Handle POST request (reset action)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $confirmation = $_POST['confirmation'] ?? '';
            
            if ($confirmation === 'RESET DATA') {
                try {
                    // Tables to clear (in order to respect foreign keys)
                    $tablesToClear = [
                        'invoice_items',
                        'invoices',
                        'quote_items',
                        'quotes',
                        'project_files',
                        'material_movements',
                        'notifications',
                        'projects',
                        'appointments',
                        'customer_communications',
                        'customers',
                        'materials'
                    ];
                    
                    // Disable foreign key checks for faster deletion
                    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
                    
                    // Delete all data
                    foreach ($tablesToClear as $table) {
                        $stmt = $db->prepare("DELETE FROM `$table`");
                        $stmt->execute();
                    }
                    
                    // Reset AUTO_INCREMENT for all tables
                    // Note: ALTER TABLE commits any active transaction automatically
                    foreach ($tablesToClear as $table) {
                        $stmt = $db->prepare("ALTER TABLE `$table` AUTO_INCREMENT = 1");
                        $stmt->execute();
                    }
                    
                    // Re-enable foreign key checks
                    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
                    
                    $success = true;
                    
                } catch (Exception $e) {
                    // Re-enable foreign key checks on error
                    try {
                        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
                    } catch (Exception $ex) {
                        // Ignore if already enabled
                    }
                    $error = $e->getMessage();
                    error_log("Reset data error: " . $e->getMessage());
                }
            } else {
                $error = 'Λάθος κωδικός επιβεβαίωσης! Πρέπει να πληκτρολογήσετε: RESET DATA';
            }
        }
        
        // Get counts for display
        $counts = [];
        if (!$success) {
            $tables = [
                'customers' => 'Πελάτες',
                'projects' => 'Έργα', 
                'invoices' => 'Τιμολόγια',
                'quotes' => 'Προσφορές',
                'appointments' => 'Ραντεβού',
                'materials' => 'Υλικά'
            ];
            
            foreach ($tables as $table => $label) {
                $stmt = $db->query("SELECT COUNT(*) as count FROM `$table`");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $counts[$label] = $result['count'];
            }
        }
        
        $data = [
            'title' => 'Μηδενισμός Δεδομένων - ' . APP_NAME,
            'user' => $user,
            'counts' => $counts,
            'success' => $success,
            'error' => $error
        ];
        
        $this->view('settings/reset-data', $data);
    }
    
    /**
     * Change user language
     */
    public function changeLanguage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
        }

        $language = $_POST['language'] ?? 'el';

        // Validate language exists
        $languagesPath = APP_ROOT . '/languages/';
        if (file_exists($languagesPath . $language . '.json')) {
            $_SESSION['language'] = $language;

            // Reload global language manager with new language
            global $lang;
            $lang = new LanguageManager($language);

            // If user is logged in, save to database
            if (isset($_SESSION['user_id'])) {
                $database = new Database();
                $db = $database->connect();

                try {
                    $stmt = $db->prepare("UPDATE users SET language = ? WHERE id = ?");
                    $stmt->execute([$language, $_SESSION['user_id']]);
                } catch (Exception $e) {
                    error_log("Error saving language preference: " . $e->getMessage());
                }
            }

            $_SESSION['success'] = __('settings.success');
        } else {
            $_SESSION['error'] = __('settings.error');
        }

        $this->redirect('/settings');
    }
    
    /**
     * Show translations management page
     */
    public function translations() {
        $selectedLanguage = $_GET['lang'] ?? 'el';

        $data = [
            'title' => __('settings.translations') . ' - ' . APP_NAME,
            'selectedLanguage' => $selectedLanguage
        ];

        $this->view('settings/translations', $data);
    }
    
    /**
     * Show updates page
     */
    public function updates() {
        $data = [
            'title' => __('settings.updates') . ' - ' . APP_NAME,
        ];

        $this->view('settings/update', $data);
    }
}
