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
        
        // Get all settings
        $stmt = $db->query("SELECT * FROM settings");
        $settingsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to key-value array
        $settings = [];
        foreach ($settingsArray as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        // Set defaults if not exists
        $defaults = [
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_tax_id' => '',
            'company_website' => '',
            'default_vat_rate' => '24',
            'currency' => 'EUR',
            'currency_symbol' => '€',
            'date_format' => 'd/m/Y',
            'items_per_page' => '20'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        
        $data = [
            'title' => 'Ρυθμίσεις - ' . APP_NAME,
            'user' => $user,
            'settings' => $settings
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
            
            // List of allowed settings
            $allowedSettings = [
                'company_name',
                'company_address',
                'company_phone',
                'company_email',
                'company_tax_id',
                'company_website',
                'default_vat_rate',
                'currency',
                'currency_symbol',
                'date_format',
                'items_per_page'
            ];
            
            $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE setting_value = ?");
            
            foreach ($allowedSettings as $key) {
                if (isset($_POST[$key])) {
                    $value = trim($_POST[$key]);
                    $stmt->execute([$key, $value, $value]);
                }
            }
            
            $db->commit();
            $_SESSION['success'] = 'Οι ρυθμίσεις ενημερώθηκαν με επιτυχία';
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Settings update error: " . $e->getMessage());
            $_SESSION['error'] = 'Σφάλμα κατά την ενημέρωση των ρυθμίσεων';
        }
        
        $this->redirect('/settings');
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
}
