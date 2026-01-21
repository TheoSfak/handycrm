<?php
/**
 * Migration Script: Μεταφορά παλιών φωτογραφιών στο νέο format (ανά transformer)
 * 
 * Πριν: Οι φωτογραφίες ήταν στο field 'photos' (JSON array)
 * Μετά: Οι φωτογραφίες είναι μέσα στο 'transformers_data' JSON, ανά transformer
 */

require_once 'config/config.php';
require_once 'classes/Database.php';

echo "<h1>Migration: Photos to Transformers</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    .warning { color: orange; }
    table { border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #4CAF50; color: white; }
</style>";

$db = new Database();
$pdo = $db->connect();

// Βρες όλες τις συντηρήσεις που έχουν photos αλλά δεν έχουν φωτογραφίες στο transformers_data
$sql = "SELECT id, photos, transformers_data, customer_name, maintenance_date 
        FROM transformer_maintenances 
        WHERE photos IS NOT NULL AND photos != '' AND photos != '[]'
        ORDER BY id";

$stmt = $pdo->query($sql);
$maintenances = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p class='info'>Βρέθηκαν " . count($maintenances) . " συντηρήσεις με παλιές φωτογραφίες</p>";

if (count($maintenances) === 0) {
    echo "<p class='success'>✅ Δεν χρειάζεται migration!</p>";
    exit;
}

echo "<table>";
echo "<tr><th>ID</th><th>Customer</th><th>Date</th><th>Old Photos</th><th>Status</th><th>Action</th></tr>";

$dryRun = !isset($_GET['execute']);

foreach ($maintenances as $maintenance) {
    $id = $maintenance['id'];
    $oldPhotos = json_decode($maintenance['photos'], true);
    
    if (!is_array($oldPhotos) || empty($oldPhotos)) {
        continue;
    }
    
    // Parse transformers_data
    $transformersData = [];
    if (!empty($maintenance['transformers_data'])) {
        $transformersData = json_decode($maintenance['transformers_data'], true);
    }
    
    if (!is_array($transformersData) || empty($transformersData)) {
        echo "<tr>";
        echo "<td>{$id}</td>";
        echo "<td>{$maintenance['customer_name']}</td>";
        echo "<td>{$maintenance['maintenance_date']}</td>";
        echo "<td>" . count($oldPhotos) . " photos</td>";
        echo "<td class='error'>❌ Δεν υπάρχει transformers_data</td>";
        echo "<td>-</td>";
        echo "</tr>";
        continue;
    }
    
    // Έλεγχος: Έχουν ήδη φωτογραφίες;
    $alreadyHasPhotos = false;
    foreach ($transformersData as $tr) {
        if (!empty($tr['photos']) && is_array($tr['photos']) && count($tr['photos']) > 0) {
            $alreadyHasPhotos = true;
            break;
        }
    }
    
    if ($alreadyHasPhotos) {
        echo "<tr>";
        echo "<td>{$id}</td>";
        echo "<td>{$maintenance['customer_name']}</td>";
        echo "<td>{$maintenance['maintenance_date']}</td>";
        echo "<td>" . count($oldPhotos) . " photos</td>";
        echo "<td class='info'>ℹ️ Έχει ήδη φωτογραφίες στο νέο format</td>";
        echo "<td>-</td>";
        echo "</tr>";
        continue;
    }
    
    // Μεταφορά: Βάλε όλες τις παλιές φωτογραφίες στον πρώτο transformer
    $transformersData[0]['photos'] = $oldPhotos;
    
    $newTransformersDataJson = json_encode($transformersData);
    
    echo "<tr>";
    echo "<td>{$id}</td>";
    echo "<td>{$maintenance['customer_name']}</td>";
    echo "<td>{$maintenance['maintenance_date']}</td>";
    echo "<td>" . count($oldPhotos) . " photos</td>";
    
    if ($dryRun) {
        echo "<td class='warning'>⚠️ DRY RUN - θα μεταφερθούν στον Transformer 1</td>";
        echo "<td>-</td>";
    } else {
        // Εκτέλεση migration
        try {
            $updateSql = "UPDATE transformer_maintenances SET transformers_data = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$newTransformersDataJson, $id]);
            
            echo "<td class='success'>✅ Μεταφέρθηκαν επιτυχώς</td>";
            echo "<td><a href='debug_maintenance_photos.php?id={$id}'>Έλεγχος</a></td>";
        } catch (Exception $e) {
            echo "<td class='error'>❌ Error: " . $e->getMessage() . "</td>";
            echo "<td>-</td>";
        }
    }
    
    echo "</tr>";
}

echo "</table>";

if ($dryRun) {
    echo "<div style='background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin: 20px 0;'>";
    echo "<h3>🔍 DRY RUN MODE</h3>";
    echo "<p>Αυτό είναι preview. Δεν έγιναν αλλαγές στη βάση.</p>";
    echo "<p><strong>Για να εκτελέσεις το migration:</strong></p>";
    echo "<p><a href='?execute=1' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>✅ Εκτέλεση Migration</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px; margin: 20px 0;'>";
    echo "<h3>✅ Migration Completed!</h3>";
    echo "<p>Οι φωτογραφίες μεταφέρθηκαν επιτυχώς στο νέο format.</p>";
    echo "<p><a href='maintenances'>← Πίσω στις Συντηρήσεις</a></p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='debug_maintenance_photos.php'>← Πίσω στο Debug</a></p>";
