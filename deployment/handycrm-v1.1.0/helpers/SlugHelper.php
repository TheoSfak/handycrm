<?php

class SlugHelper {
    /**
     * Μετατρέπει ελληνικό ή αγγλικό κείμενο σε URL-safe slug
     * Π.χ. "Εργασία Ψυγείου" -> "ergasia-psygeiou"
     */
    public static function createSlug($text, $id = null) {
        // Transliteration map για ελληνικά
        $greekMap = [
            'α' => 'a', 'ά' => 'a', 'Α' => 'a', 'Ά' => 'a',
            'β' => 'v', 'Β' => 'v',
            'γ' => 'g', 'Γ' => 'g',
            'δ' => 'd', 'Δ' => 'd',
            'ε' => 'e', 'έ' => 'e', 'Ε' => 'e', 'Έ' => 'e',
            'ζ' => 'z', 'Ζ' => 'z',
            'η' => 'i', 'ή' => 'i', 'Η' => 'i', 'Ή' => 'i',
            'θ' => 'th', 'Θ' => 'th',
            'ι' => 'i', 'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'Ι' => 'i', 'Ί' => 'i', 'Ϊ' => 'i',
            'κ' => 'k', 'Κ' => 'k',
            'λ' => 'l', 'Λ' => 'l',
            'μ' => 'm', 'Μ' => 'm',
            'ν' => 'n', 'Ν' => 'n',
            'ξ' => 'ks', 'Ξ' => 'ks',
            'ο' => 'o', 'ό' => 'o', 'Ο' => 'o', 'Ό' => 'o',
            'π' => 'p', 'Π' => 'p',
            'ρ' => 'r', 'Ρ' => 'r',
            'σ' => 's', 'ς' => 's', 'Σ' => 's',
            'τ' => 't', 'Τ' => 't',
            'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y', 'Υ' => 'y', 'Ύ' => 'y', 'Ϋ' => 'y',
            'φ' => 'f', 'Φ' => 'f',
            'χ' => 'ch', 'Χ' => 'ch',
            'ψ' => 'ps', 'Ψ' => 'ps',
            'ω' => 'o', 'ώ' => 'o', 'Ω' => 'o', 'Ώ' => 'o',
        ];
        
        // Μετατροπή σε πεζά
        $text = mb_strtolower($text, 'UTF-8');
        
        // Αντικατάσταση ελληνικών χαρακτήρων
        $text = strtr($text, $greekMap);
        
        // Αφαίρεση ειδικών χαρακτήρων και αντικατάσταση με παύλες
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        
        // Αφαίρεση παύλων στην αρχή και το τέλος
        $text = trim($text, '-');
        
        // Αν το slug είναι άδειο, χρησιμοποίησε το ID
        if (empty($text) && $id) {
            $text = 'project-' . $id;
        }
        
        return $text;
    }
    
    /**
     * Δημιουργεί unique slug προσθέτοντας αριθμό αν χρειάζεται
     */
    public static function createUniqueSlug($text, $table, $id = null, $slugColumn = 'slug') {
        $database = new Database();
        $db = $database->connect();
        
        $baseSlug = self::createSlug($text, $id);
        $slug = $baseSlug;
        $counter = 1;
        
        // Έλεγχος αν το slug υπάρχει ήδη
        while (true) {
            $sql = "SELECT id FROM $table WHERE $slugColumn = :slug";
            if ($id) {
                $sql .= " AND id != :id";
            }
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':slug', $slug);
            if ($id) {
                $stmt->bindValue(':id', $id);
            }
            $stmt->execute();
            
            // Αν δεν υπάρχει, το slug είναι unique
            if ($stmt->rowCount() === 0) {
                break;
            }
            
            // Αν υπάρχει, προσθέτουμε αριθμό
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
