<?php
/**
 * Quick script to add costs to a project
 * Usage: http://localhost/handycrm/add_project_costs.php?project_id=X&materials=100&labor=200
 */

require_once 'config/config.php';

$projectId = $_GET['project_id'] ?? null;
$materialCost = $_GET['materials'] ?? 100;
$laborCost = $_GET['labor'] ?? 200;

if (!$projectId) {
    die('Usage: add_project_costs.php?project_id=X&materials=100&labor=200');
}

global $db;

echo "<h2>Adding Costs to Project #$projectId</h2>";
echo "<hr>";

// Get project
$project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$projectId]);

if (!$project) {
    die("<p style='color: red;'>❌ Project not found!</p>");
}

echo "<h3>✅ Project Found:</h3>";
echo "<p><strong>Title:</strong> " . htmlspecialchars($project['title']) . "</p>";
echo "<p><strong>Customer ID:</strong> " . $project['customer_id'] . "</p>";
echo "<p><strong>Status:</strong> " . $project['status'] . "</p>";
echo "<hr>";

echo "<h3>Current Costs:</h3>";
echo "<p><strong>Materials:</strong> " . number_format($project['material_cost'], 2) . "€</p>";
echo "<p><strong>Labor:</strong> " . number_format($project['labor_cost'], 2) . "€</p>";
echo "<p><strong>Total:</strong> " . number_format($project['total_cost'], 2) . "€</p>";
echo "<hr>";

// Calculate new costs
$vatRate = $project['vat_rate'] ?? 24;
$subtotal = $materialCost + $laborCost;
$vatAmount = $subtotal * ($vatRate / 100);
$totalCost = $subtotal + $vatAmount;

// Update project costs
$sql = "UPDATE projects 
        SET material_cost = ?, 
            labor_cost = ?, 
            vat_rate = ?,
            total_cost = ?,
            updated_at = NOW()
        WHERE id = ?";

$result = $db->execute($sql, [$materialCost, $laborCost, $vatRate, $totalCost, $projectId]);

if ($result) {
    echo "<h3 style='color: green;'>✅ SUCCESS! Costs Updated:</h3>";
    echo "<p><strong>Materials:</strong> " . number_format($materialCost, 2) . "€</p>";
    echo "<p><strong>Labor:</strong> " . number_format($laborCost, 2) . "€</p>";
    echo "<p><strong>Subtotal:</strong> " . number_format($subtotal, 2) . "€</p>";
    echo "<p><strong>VAT ($vatRate%):</strong> " . number_format($vatAmount, 2) . "€</p>";
    echo "<p><strong>TOTAL:</strong> <strong>" . number_format($totalCost, 2) . "€</strong></p>";
    echo "<hr>";
    
    echo "<h3>🎯 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Go to: <a href='?route=/projects/show&id=$projectId' target='_blank'>View Project #$projectId</a></li>";
    echo "<li>Click dropdown <strong>'Αλλαγή Κατάστασης'</strong></li>";
    echo "<li>Select <strong>'Τιμολογημένο'</strong></li>";
    echo "<li>Check for success message and new invoice!</li>";
    echo "</ol>";
} else {
    echo "<p style='color: red;'>❌ Failed to update costs!</p>";
}

echo "<hr>";
echo "<p><strong>⚠️ DELETE THIS FILE AFTER TESTING!</strong></p>";
?>
