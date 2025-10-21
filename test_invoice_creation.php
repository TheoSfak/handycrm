<?php
/**
 * Test Invoice Creation
 * Δοκιμή δημιουργίας τιμολογίου για έργο
 * Access: http://localhost/handycrm/test_invoice_creation.php?project_id=XX
 */

require_once 'config/config.php';

// Get project ID from URL
$projectId = $_GET['project_id'] ?? null;

if (!$projectId) {
    die('Usage: test_invoice_creation.php?project_id=XX');
}

// Use the global $db from config
global $db;
if (!isset($db)) {
    die('Database connection not available. Check config/database.php');
}

echo "<h2>Testing Invoice Creation for Project #$projectId</h2>";
echo "<hr>";

// Get project
$project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$projectId]);

if (!$project) {
    die("<p style='color: red;'>Project not found!</p>");
}

echo "<h3>✅ Project Found:</h3>";
echo "<pre>";
print_r($project);
echo "</pre>";
echo "<hr>";

// Check existing invoice
$existing = $db->fetchOne("SELECT * FROM invoices WHERE project_id = ?", [$projectId]);

if ($existing) {
    echo "<h3>⚠️ Invoice Already Exists:</h3>";
    echo "<pre>";
    print_r($existing);
    echo "</pre>";
    echo "<hr>";
}

// Get materials and labor from project directly
$materialsTotal = $project['material_cost'] ?? 0;
$laborTotal = $project['labor_cost'] ?? 0;

echo "<h3>📦 Materials Cost (from project):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><td><strong>Total Materials:</strong></td><td>" . number_format($materialsTotal, 2) . "€</td></tr>";
echo "</table>";
echo "<hr>";

echo "<h3>👷 Labor Cost (from project):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><td><strong>Total Labor:</strong></td><td>" . number_format($laborTotal, 2) . "€</td></tr>";
echo "</table>";
echo "<hr>";

// Calculate totals
$subtotal = $materialsTotal + $laborTotal;
$vatRate = $project['vat_rate'] ?? DEFAULT_VAT_RATE;
$vatAmount = $subtotal * ($vatRate / 100);
$totalAmount = $subtotal + $vatAmount;

echo "<h3>💰 Final Calculation:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><td><strong>Materials:</strong></td><td>" . number_format($materialsTotal, 2) . "€</td></tr>";
echo "<tr><td><strong>Labor:</strong></td><td>" . number_format($laborTotal, 2) . "€</td></tr>";
echo "<tr><td><strong>Subtotal:</strong></td><td>" . number_format($subtotal, 2) . "€</td></tr>";
echo "<tr><td><strong>VAT ($vatRate%):</strong></td><td>" . number_format($vatAmount, 2) . "€</td></tr>";
echo "<tr><td><strong>TOTAL:</strong></td><td><strong>" . number_format($totalAmount, 2) . "€</strong></td></tr>";
echo "</table>";
echo "<hr>";

if ($totalAmount == 0) {
    echo "<p style='color: orange;'>⚠️ Total is 0, invoice will NOT be created</p>";
} else {
    echo "<p style='color: green;'>✅ Total is $totalAmount€, invoice SHOULD be created</p>";
}

echo "<hr>";
echo "<p><strong>⚠️ DELETE THIS FILE AFTER TESTING!</strong></p>";
?>
