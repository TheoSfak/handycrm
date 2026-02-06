<?php
/**
 * Material Aliases Generator
 * Automatically generates search aliases for materials
 * Supports Greeklish, synonyms, and code extraction
 */

class MaterialAliasGenerator {
    
    /**
     * Greeklish to Greek mapping
     */
    private static $greekToGreeklish = [
        // Regular letters
        'α' => ['a'], 'Α' => ['A'],
        'β' => ['b', 'v'], 'Β' => ['B', 'V'],
        'γ' => ['g'], 'Γ' => ['G'],
        'δ' => ['d'], 'Δ' => ['D'],
        'ε' => ['e'], 'Ε' => ['E'],
        'ζ' => ['z'], 'Ζ' => ['Z'],
        'η' => ['h', 'i'], 'Η' => ['H', 'I'],
        'θ' => ['th', '8'], 'Θ' => ['TH', 'Th'],
        'ι' => ['i'], 'Ι' => ['I'],
        'κ' => ['k', 'c'], 'Κ' => ['K', 'C'],
        'λ' => ['l'], 'Λ' => ['L'],
        'μ' => ['m'], 'Μ' => ['M'],
        'ν' => ['n'], 'Ν' => ['N'],
        'ξ' => ['ks', 'x', 'j'], 'Ξ' => ['KS', 'X', 'J'],
        'ο' => ['o'], 'Ο' => ['O'],
        'π' => ['p'], 'Π' => ['P'],
        'ρ' => ['r'], 'Ρ' => ['R'],
        'σ' => ['s'], 'Σ' => ['S'], 'ς' => ['s'],
        'τ' => ['t'], 'Τ' => ['T'],
        'υ' => ['y', 'i', 'u'], 'Υ' => ['Y', 'I', 'U'],
        'φ' => ['f', 'ph'], 'Φ' => ['F', 'PH', 'Ph'],
        'χ' => ['x', 'ch', 'h'], 'Χ' => ['X', 'CH', 'Ch'],
        'ψ' => ['ps'], 'Ψ' => ['PS', 'Ps'],
        'ω' => ['w', 'o'], 'Ω' => ['W', 'O'],
        // Accented letters (τονισμένα)
        'ά' => ['a'], 'Ά' => ['A'],
        'έ' => ['e'], 'Έ' => ['E'],
        'ή' => ['h', 'i'], 'Ή' => ['H', 'I'],
        'ί' => ['i'], 'Ί' => ['I'],
        'ό' => ['o'], 'Ό' => ['O'],
        'ύ' => ['y', 'i', 'u'], 'Ύ' => ['Y', 'I', 'U'],
        'ώ' => ['w', 'o'], 'Ώ' => ['W', 'O'],
        // Dieresis (διαλυτικά)
        'ϊ' => ['i'], 'ΐ' => ['i'], 'Ϊ' => ['I'],
        'ϋ' => ['y', 'i', 'u'], 'ΰ' => ['y', 'i', 'u'], 'Ϋ' => ['Y', 'I', 'U'],
        // Diphthongs
        'αι' => ['ai', 'e'], 'ΑΙ' => ['AI', 'E'],
        'ει' => ['ei', 'i'], 'ΕΙ' => ['EI', 'I'],
        'οι' => ['oi', 'i'], 'ΟΙ' => ['OI', 'I'],
        'ου' => ['ou', 'u'], 'ΟΥ' => ['OU', 'U'],
        'αυ' => ['au', 'av'], 'ΑΥ' => ['AU', 'AV'],
        'ευ' => ['eu', 'ev'], 'ΕΥ' => ['EU', 'EV']
    ];
    
    /**
     * Common Greek to English synonyms for materials
     */
    private static $synonyms = [
        'καλώδιο' => ['cable', 'wire', 'kalodio', 'kalwdio'],
        'καλωδιο' => ['cable', 'wire', 'kalodio'],
        'σωλήνας' => ['pipe', 'tube', 'swlhnas', 'solinas'],
        'σωληνας' => ['pipe', 'tube', 'swlinas'],
        'λάμπα' => ['lamp', 'bulb', 'light', 'lampa'],
        'λαμπα' => ['lamp', 'bulb', 'light'],
        'διακόπτης' => ['switch', 'diakopths', 'diakoptis'],
        'διακοπτης' => ['switch', 'diakoptis'],
        'πρίζα' => ['socket', 'outlet', 'priza'],
        'πριζα' => ['socket', 'outlet'],
        'κουτί' => ['box', 'kouti'],
        'κουτι' => ['box'],
        'ταινία' => ['tape', 'tenia', 'tainia'],
        'ταινια' => ['tape', 'tenia'],
        'βίδα' => ['screw', 'vida', 'vida'],
        'βιδα' => ['screw', 'vida'],
        'παξιμάδι' => ['nut', 'paksimadi'],
        'παξιμαδι' => ['nut', 'paksimadi']
    ];
    
    /**
     * Generate aliases for a material name
     * 
     * @param string $name Material name
     * @return string Comma-separated aliases
     */
    public static function generate($name) {
        if (empty($name)) {
            return '';
        }
        
        $aliases = [];
        $nameLower = mb_strtolower($name, 'UTF-8');
        
        // Split name into words to process separately
        $words = preg_split('/[\s\-]+/u', $name);
        
        foreach ($words as $word) {
            $wordLower = mb_strtolower($word, 'UTF-8');
            
            // Skip empty words
            if (empty($word)) continue;
            
            // 1. Check if word is a code/number (e.g., "NYM", "3x1.5", "PVC")
            if (preg_match('/^[A-Z0-9]{2,}$|^\d+[x×]\d+\.?\d*$/u', $word)) {
                $aliases[] = $word;
                $aliases[] = strtolower($word);
                continue; // Don't convert codes to Greeklish
            }
            
            // 2. Check if word is Greek (contains Greek letters)
            if (preg_match('/[\x{0370}-\x{03FF}\x{1F00}-\x{1FFF}]/u', $word)) {
                // Convert Greek word to Greeklish
                $greeklish = self::toGreeklish($wordLower);
                if ($greeklish && count($greeklish) > 0) {
                    $aliases = array_merge($aliases, $greeklish);
                }
                
                // Add English synonyms for this Greek word
                foreach (self::$synonyms as $greek => $englishList) {
                    if (mb_stripos($wordLower, $greek, 0, 'UTF-8') !== false) {
                        $aliases = array_merge($aliases, $englishList);
                        break; // Only first match per word
                    }
                }
            } else {
                // Word is already Latin/English - add lowercase version
                if ($word !== $wordLower) {
                    $aliases[] = $wordLower;
                }
            }
        }
        
        // 3. Remove duplicates and empty values
        $aliases = array_filter(array_unique($aliases));
        
        // 4. Remove aliases that are too short or same as original
        $aliases = array_filter($aliases, function($alias) use ($name, $nameLower, $words) {
            $aliasLower = mb_strtolower($alias, 'UTF-8');
            
            // Remove if too short
            if (mb_strlen($alias, 'UTF-8') <= 1) return false;
            
            // Remove if same as full name
            if ($aliasLower === $nameLower || $alias === $name) return false;
            
            // Remove if same as any original word (already searchable)
            foreach ($words as $word) {
                if (mb_strtolower($word, 'UTF-8') === $aliasLower) return false;
            }
            
            return true;
        });
        
        return implode(', ', $aliases);
    }
    
    /**
     * Convert Greek text to Greeklish variants
     * 
     * @param string $text Greek text
     * @return array Array of Greeklish variants
     */
    private static function toGreeklish($text) {
        $variants = [];
        
        // Process in order: diphthongs first, then single characters
        $simple = $text;
        
        // 1. Replace diphthongs (must be before single characters)
        $simple = str_replace(['ου', 'ΟΥ'], ['ou', 'OU'], $simple);
        $simple = str_replace(['αι', 'ΑΙ'], ['ai', 'AI'], $simple);
        $simple = str_replace(['ει', 'ΕΙ'], ['ei', 'EI'], $simple);
        $simple = str_replace(['οι', 'ΟΙ'], ['oi', 'OI'], $simple);
        $simple = str_replace(['αυ', 'ΑΥ'], ['au', 'AU'], $simple);
        $simple = str_replace(['ευ', 'ΕΥ'], ['eu', 'EU'], $simple);
        
        // 2. Replace multi-character Greek letters
        $simple = str_replace(['θ', 'Θ'], ['th', 'TH'], $simple);
        $simple = str_replace(['χ', 'Χ'], ['ch', 'CH'], $simple);
        $simple = str_replace(['ψ', 'Ψ'], ['ps', 'PS'], $simple);
        
        // 3. Replace accented vowels (τονισμένα)
        $simple = str_replace(['ά', 'Ά'], ['a', 'A'], $simple);
        $simple = str_replace(['έ', 'Έ'], ['e', 'E'], $simple);
        $simple = str_replace(['ή', 'Ή'], ['h', 'H'], $simple);
        $simple = str_replace(['ί', 'Ί'], ['i', 'I'], $simple);
        $simple = str_replace(['ϊ', 'ΐ', 'Ϊ'], ['i', 'i', 'I'], $simple);
        $simple = str_replace(['ό', 'Ό'], ['o', 'O'], $simple);
        $simple = str_replace(['ύ', 'Ύ'], ['y', 'Y'], $simple);
        $simple = str_replace(['ϋ', 'ΰ', 'Ϋ'], ['y', 'y', 'Y'], $simple);
        $simple = str_replace(['ώ', 'Ώ'], ['w', 'W'], $simple);
        
        // 4. Replace regular single characters
        foreach (self::$greekToGreeklish as $greek => $latinList) {
            if (mb_strlen($greek, 'UTF-8') == 1) {
                $simple = str_replace($greek, $latinList[0], $simple);
            }
        }
        
        // 5. Convert to lowercase for consistency
        $simple = mb_strtolower($simple, 'UTF-8');
        
        if ($simple !== mb_strtolower($text, 'UTF-8')) {
            $variants[] = $simple;
            
            // Add variant with 'o' instead of 'w' for omega (more common)
            if (strpos($simple, 'w') !== false) {
                $withO = str_replace('w', 'o', $simple);
                if ($withO !== $simple) {
                    $variants[] = $withO;
                }
            }
        }
        
        return array_unique($variants);
    }
}
