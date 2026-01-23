<?php
/**
 * System Settings Model
 * Handles system-wide configuration settings
 */
class Settings extends BaseModel {
    protected $table = 'system_settings';
    private static $cache = [];

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
        
        $result = $instance->db->fetchOne($query, [$key]);
        
        if ($result) {
            $value = $instance->castValue($result['setting_value'], $result['setting_type']);
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
        
        $stmt = $this->db->execute($query, [$key, $value, $type, $group, $description]);
        
        if ($stmt) {
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
        $results = $this->db->fetchAll($query, [$group]);
        
        $settings = [];
        foreach ($results as $row) {
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
        $results = $this->db->fetchAll($query);
        
        $settings = [];
        foreach ($results as $row) {
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
        $stmt = $this->db->execute($query, [$key]);
        
        if ($stmt) {
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
