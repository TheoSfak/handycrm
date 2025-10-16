<?php
/**
 * Regenerate aliases for all existing materials
 * Run this once after adding the aliases feature
 */

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/MaterialAliasGenerator.php';

$db = Database::getInstance();

// Get all materials
$sql = "SELECT id, name FROM materials_catalog";
$stmt = $db->query($sql);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
foreach ($materials as $material) {
    $aliases = MaterialAliasGenerator::generate($material['name']);
    
    $updateSql = "UPDATE materials_catalog SET aliases = ? WHERE id = ?";
    $updateStmt = $db->execute($updateSql, [$aliases, $material['id']]);
    
    if ($updateStmt) {
        $updated++;
        echo "✓ Updated: {$material['name']} → " . substr($aliases, 0, 50) . "...\n";
    }
}

echo "\n✅ Regenerated aliases for {$updated} materials!\n";
