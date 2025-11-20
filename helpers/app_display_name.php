<?php
/**
 * Get the application display name
 * Returns company_display_name from settings if set, otherwise returns 'HandyCRM'
 */
function getAppDisplayName() {
    static $displayName = null;
    
    if ($displayName === null) {
        try {
            require_once __DIR__ . '/../classes/Database.php';
            $database = new Database();
            $db = $database->connect();
            
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'company_display_name'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty(trim($result['setting_value']))) {
                $displayName = trim($result['setting_value']);
            } else {
                $displayName = 'HandyCRM';
            }
        } catch (Exception $e) {
            // Fallback to default if any error
            $displayName = 'HandyCRM';
        }
    }
    
    return $displayName;
}
