<?php
/**
 * System Settings Model
 * Handles system-wide configuration settings
 */
class Settings {
    private $db;
    private $table = 'system_settings';
    private static $cache = [];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Get a setting value by key
     * Uses cache to avoid repeated database queries
     */
    public static function get($key, $default = null) {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $instance = new self();
        $query = "SELECT setting_value, setting_type FROM " . $instance->table . " WHERE setting_key = ? LIMIT 1";
        
        $stmt = $instance->db->prepare($query);
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $value = $instance->castValue($row['setting_value'], $row['setting_type']);
            self::$cache[$key] = $value;
            return $value;
        }
        
        return $default;
    }

    /**
     * Set a setting value
     */
    public function set($key, $value, $type = 'text', $group = 'general', $description = '') {
        $query = "INSERT INTO " . $this->table . " 
                  (setting_key, setting_value, setting_type, setting_group, description) 
                  VALUES (?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE 
                  setting_value = VALUES(setting_value),
                  setting_type = VALUES(setting_type),
                  setting_group = VALUES(setting_group),
                  description = VALUES(description)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('sssss', $key, $value, $type, $group, $description);
        
        if ($stmt->execute()) {
            // Clear cache for this key
            unset(self::$cache[$key]);
            return true;
        }
        
        return false;
    }

    /**
     * Get all settings by group
     */
    public function getByGroup($group) {
        $query = "SELECT * FROM " . $this->table . " WHERE setting_group = ? ORDER BY setting_key";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $group);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = [
                'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                'type' => $row['setting_type'],
                'description' => $row['description']
            ];
        }
        
        return $settings;
    }

    /**
     * Get all settings
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY setting_group, setting_key";
        $result = $this->db->query($query);
        
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            if (!isset($settings[$row['setting_group']])) {
                $settings[$row['setting_group']] = [];
            }
            
            $settings[$row['setting_group']][$row['setting_key']] = [
                'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                'type' => $row['setting_type'],
                'description' => $row['description']
            ];
        }
        
        return $settings;
    }

    /**
     * Delete a setting
     */
    public function delete($key) {
        $query = "DELETE FROM " . $this->table . " WHERE setting_key = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $key);
        
        if ($stmt->execute()) {
            unset(self::$cache[$key]);
            return true;
        }
        
        return false;
    }

    /**
     * Cast value to appropriate type
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'boolean':
                return (bool)$value;
            case 'integer':
            case 'int':
                return (int)$value;
            case 'decimal':
            case 'float':
                return (float)$value;
            case 'json':
                return json_decode($value, true);
            case 'text':
            default:
                return $value;
        }
    }

    /**
     * Clear all cache
     */
    public static function clearCache() {
        self::$cache = [];
    }

    /**
     * Helper: Check if VAT notes should be displayed
     */
    public static function shouldDisplayVatNotes() {
        return (bool)self::get('display_vat_notes', true);
    }

    /**
     * Helper: Check if prices include VAT
     */
    public static function pricesIncludeVat() {
        return (bool)self::get('prices_include_vat', false);
    }

    /**
     * Helper: Get VAT rate
     */
    public static function getVatRate() {
        return (float)self::get('vat_rate', 24.00);
    }
}
