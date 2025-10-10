<?php
/**
 * LanguageManager Class
 * Handles multi-language support and translation management
 */
class LanguageManager {
    private $currentLanguage;
    private $translations = [];
    private $languagesPath;
    private $availableLanguages = [];
    
    public function __construct($language = 'el') {
        $this->languagesPath = __DIR__ . '/../languages/';
        $this->currentLanguage = $language;
        $this->loadAvailableLanguages();
        $this->loadTranslations($language);
    }
    
    /**
     * Load available languages from the languages directory
     */
    private function loadAvailableLanguages() {
        if (!is_dir($this->languagesPath)) {
            mkdir($this->languagesPath, 0755, true);
        }
        
        $files = glob($this->languagesPath . '*.json');
        foreach ($files as $file) {
            $code = basename($file, '.json');
            $this->availableLanguages[$code] = $this->getLanguageName($code);
        }
        
        // Ensure default languages exist
        if (empty($this->availableLanguages)) {
            $this->availableLanguages = [
                'el' => 'Ελληνικά',
                'en' => 'English'
            ];
        }
    }
    
    /**
     * Get language name from code
     */
    private function getLanguageName($code) {
        $names = [
            'el' => 'Ελληνικά',
            'en' => 'English',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'es' => 'Español',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'ar' => 'العربية',
            'tr' => 'Türkçe',
            'nl' => 'Nederlands',
            'pl' => 'Polski',
            'sv' => 'Svenska',
            'no' => 'Norsk',
            'da' => 'Dansk',
            'fi' => 'Suomi',
            'cs' => 'Čeština',
            'hu' => 'Magyar',
            'ro' => 'Română',
            'bg' => 'Български',
            'uk' => 'Українська',
            'sr' => 'Српски',
            'hr' => 'Hrvatski',
            'sk' => 'Slovenčina',
            'sl' => 'Slovenščina'
        ];
        
        return $names[$code] ?? ucfirst($code);
    }
    
    /**
     * Load translations for a specific language
     */
    private function loadTranslations($language) {
        $filePath = $this->languagesPath . $language . '.json';
        
        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $this->translations = json_decode($json, true) ?? [];
        } else {
            // Load English as fallback
            $fallbackPath = $this->languagesPath . 'en.json';
            if (file_exists($fallbackPath)) {
                $json = file_get_contents($fallbackPath);
                $this->translations = json_decode($json, true) ?? [];
            }
        }
    }
    
    /**
     * Get translation for a key
     */
    public function get($key, $default = null) {
        // Support nested keys like "menu.dashboard"
        $keys = explode('.', $key);
        $value = $this->translations;
        
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default ?? $key;
            }
        }
        
        return $value;
    }
    
    /**
     * Translate a key (alias for get)
     */
    public function translate($key, $default = null) {
        return $this->get($key, $default);
    }
    
    /**
     * Get all translations
     */
    public function getAll() {
        return $this->translations;
    }
    
    /**
     * Get available languages
     */
    public function getAvailableLanguages() {
        return $this->availableLanguages;
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage() {
        return $this->currentLanguage;
    }
    
    /**
     * Set current language
     */
    public function setLanguage($language) {
        if (isset($this->availableLanguages[$language])) {
            $this->currentLanguage = $language;
            $this->loadTranslations($language);
            return true;
        }
        return false;
    }
    
    /**
     * Save translations to file
     */
    public function saveTranslations($language, $translations) {
        $filePath = $this->languagesPath . $language . '.json';
        
        // Merge with existing translations
        if (file_exists($filePath)) {
            $existing = json_decode(file_get_contents($filePath), true) ?? [];
            $translations = array_merge($existing, $translations);
        }
        
        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($filePath, $json)) {
            // Reload if this is the current language
            if ($language === $this->currentLanguage) {
                $this->loadTranslations($language);
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Create a new language file
     */
    public function createLanguage($code, $name, $baseLanguage = 'en') {
        // Validate language code
        if (!preg_match('/^[a-z]{2}$/', $code)) {
            return false;
        }
        
        $filePath = $this->languagesPath . $code . '.json';
        
        // Don't overwrite existing files
        if (file_exists($filePath)) {
            return false;
        }
        
        // Copy base language structure
        $baseFilePath = $this->languagesPath . $baseLanguage . '.json';
        if (file_exists($baseFilePath)) {
            $baseTranslations = json_decode(file_get_contents($baseFilePath), true);
            
            // Create empty translations with same structure
            $emptyTranslations = $this->createEmptyStructure($baseTranslations);
            
            $json = json_encode($emptyTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if (file_put_contents($filePath, $json)) {
                $this->availableLanguages[$code] = $name;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Create empty structure from base translations
     */
    private function createEmptyStructure($array) {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->createEmptyStructure($value);
            } else {
                $result[$key] = '';
            }
        }
        return $result;
    }
    
    /**
     * Get translation progress for a language
     */
    public function getTranslationProgress($language) {
        $filePath = $this->languagesPath . $language . '.json';
        
        if (!file_exists($filePath)) {
            return 0;
        }
        
        $translations = json_decode(file_get_contents($filePath), true);
        
        $total = $this->countTranslations($translations);
        $completed = $this->countCompletedTranslations($translations);
        
        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }
    
    /**
     * Count total translations
     */
    private function countTranslations($array) {
        $count = 0;
        foreach ($array as $value) {
            if (is_array($value)) {
                $count += $this->countTranslations($value);
            } else {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Count completed translations (non-empty)
     */
    private function countCompletedTranslations($array) {
        $count = 0;
        foreach ($array as $value) {
            if (is_array($value)) {
                $count += $this->countCompletedTranslations($value);
            } else if (!empty(trim($value))) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Delete a language file
     */
    public function deleteLanguage($code) {
        // Protect default languages
        if (in_array($code, ['el', 'en'])) {
            return false;
        }
        
        $filePath = $this->languagesPath . $code . '.json';
        
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                unset($this->availableLanguages[$code]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get all translation keys from current language
     */
    public function getAllKeys() {
        $filePath = $this->languagesPath . $this->currentLanguage . '.json';
        
        if (file_exists($filePath)) {
            $translations = json_decode(file_get_contents($filePath), true);
            return $this->flattenArray($translations);
        }
        
        return [];
    }
    
    /**
     * Flatten nested array to dot notation
     */
    private function flattenArray($array, $prefix = '') {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
}
