<?php
/**
 * System Settings Model
 * Handles system-wide configuration settings
 */
require_once __DIR__ . '/../classes/Database.php';

class Settings {
    private $db;
    private $table = 'settings';
    private static $cache = [];

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
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
        $stmt->execute([$key]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
        
        if ($stmt->execute([$key, $value, $type, $group, $description])) {
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
        $stmt->execute([$group]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        $stmt = $this->db->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute([$key])) {
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

    const DEFAULT_WORK_DAY_START = '07:30';
    const DEFAULT_WORK_DAY_END   = '15:30';

    /**
     * Helper: Check that a value is a valid HH:MM time (00:00 - 23:59)
     */
    public static function isValidTime($value) {
        return is_string($value) && (bool)preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value);
    }

    /**
     * Helper: Get a HH:MM time setting, falling back to the default if missing or malformed
     */
    private static function getTimeSetting($key, $default) {
        $value = substr(trim((string)self::get($key, $default)), 0, 5);
        return self::isValidTime($value) ? $value : $default;
    }

    /**
     * Helper: Standard work day start (HH:MM)
     */
    public static function getWorkDayStart() {
        return self::getTimeSetting('work_day_start', self::DEFAULT_WORK_DAY_START);
    }

    /**
     * Helper: Standard work day end (HH:MM)
     */
    public static function getWorkDayEnd() {
        return self::getTimeSetting('work_day_end', self::DEFAULT_WORK_DAY_END);
    }

    /**
     * Helper: Standard work day length in hours, derived from start/end
     * (an end earlier than the start is treated as an overnight shift)
     */
    public static function getWorkDayHours() {
        return self::workDayHours(self::getWorkDayStart(), self::getWorkDayEnd());
    }

    /**
     * Helper: Standard work day as ['start' => 'HH:MM', 'end' => 'HH:MM', 'hours' => float]
     */
    public static function getWorkDay() {
        $start = self::getWorkDayStart();
        $end   = self::getWorkDayEnd();
        return ['start' => $start, 'end' => $end, 'hours' => self::workDayHours($start, $end)];
    }

    private static function workDayHours($start, $end) {
        list($sh, $sm) = array_map('intval', explode(':', $start));
        list($eh, $em) = array_map('intval', explode(':', $end));
        $minutes = ($eh * 60 + $em) - ($sh * 60 + $sm);
        if ($minutes < 0) {
            $minutes += 24 * 60;
        }
        return $minutes > 0 ? round($minutes / 60, 2) : 8.0;
    }
}
