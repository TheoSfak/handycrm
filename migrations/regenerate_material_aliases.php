<?php
/**
 * Regenerate Aliases for All Materials
 * Run with: php migrations/regenerate_material_aliases.php
 */

// Load configuration and classes
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/MaterialAliasGenerator.php';

echo "=== Material Aliases Regeneration ===\n\n";

try {
    $db = new Database();
    $db->connect();
    $conn = $db->getPdo();
    
    // Get all materials
    $stmt = $conn->query("SELECT id, name FROM materials_catalog ORDER BY id");
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($materials);
    echo "Found {$total} materials to process...\n\n";
    
    $updated = 0;
    $errors = 0;
    
    foreach ($materials as $material) {
        $id = $material['id'];
        $name = $material['name'];
        
        // Generate aliases
        $aliases = MaterialAliasGenerator::generate($name);
        
        if (!empty($aliases)) {
            // Update material with new aliases
            $updateSql = "UPDATE materials_catalog SET aliases = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            
            if ($updateStmt->execute([$aliases, $id])) {
                $updated++;
                echo sprintf("[%03d] ✓ %s\n", $id, $name);
                echo "      Aliases: " . substr($aliases, 0, 80) . "...\n";
            } else {
                $errors++;
                echo sprintf("[%03d] ✗ ERROR updating %s\n", $id, $name);
            }
        } else {
            echo sprintf("[%03d] ⚠ No aliases generated for %s\n", $id, $name);
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Total materials: {$total}\n";
    echo "Successfully updated: {$updated}\n";
    echo "Errors: {$errors}\n";
    echo "Skipped: " . ($total - $updated - $errors) . "\n";
    echo "\n✓ Alias regeneration completed!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
