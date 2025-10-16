<?php
/**
 * Test UTF-8 encoding for materials
 */

require_once 'config/config.php';
require_once 'classes/Database.php';

header('Content-Type: text/html; charset=UTF-8');

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Test query
    $stmt = $conn->query("SELECT id, name, unit, default_price FROM materials_catalog ORDER BY id LIMIT 5");
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<!DOCTYPE html>";
    echo "<html><head><meta charset='UTF-8'><title>Test Materials UTF-8</title></head><body>";
    echo "<h1>Materials Catalog - UTF-8 Test</h1>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Unit</th><th>Price</th></tr>";
    
    foreach ($materials as $material) {
        echo "<tr>";
        echo "<td>" . $material['id'] . "</td>";
        echo "<td>" . htmlspecialchars($material['name']) . "</td>";
        echo "<td>" . htmlspecialchars($material['unit']) . "</td>";
        echo "<td>" . number_format($material['default_price'], 2) . "€</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
