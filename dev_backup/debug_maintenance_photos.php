<?php
/**
 * Debug Maintenance Photos - Simplified Version
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'classes/Database.php';

$maintenanceId = isset($_GET['id']) ? (int)$_GET['id'] : null;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Maintenance Photos</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #4CAF50; color: white; }
        img { max-width: 200px; margin: 5px; border: 2px solid #ddd; }
        .photo-preview { display: inline-block; margin: 10px; text-align: center; }
    </style>
</head>
<body>
<h1>Debug Maintenance Photos</h1>

<?php

$db = new Database();
$pdo = $db->connect();

if (!$maintenanceId) {
    echo "<div class='section error'>❌ Δώσε maintenance ID: ?id=XXX</div>";
    
    $stmt = $pdo->query("SELECT id, maintenance_date, created_at FROM transformer_maintenances ORDER BY created_at DESC LIMIT 20");
    $maintenances = $stmt->fetchAll();
    
    echo "<div class='section'><h3>Πρόσφατες Συντηρήσεις:</h3><table>";
    echo "<tr><th>ID</th><th>Ημ/νία</th><th>Action</th></tr>";
    foreach ($maintenances as $m) {
        echo "<tr><td>{$m['id']}</td><td>{$m['maintenance_date']}</td>";
        echo "<td><a href='?id={$m['id']}'>Debug</a></td></tr>";
    }
    echo "</table></div>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM transformer_maintenances WHERE id = ?");
$stmt->execute([$maintenanceId]);
$maintenance = $stmt->fetch();

if (!$maintenance) {
    die("<div class='error'>❌ Συντήρηση δεν βρέθηκε</div>");
}

echo "<div class='section'><h2>Συντήρηση ID: {$maintenanceId}</h2></div>";

// Check transformers JSON
echo "<div class='section'><h3>Transformers & Photos</h3>";

$allPhotos = [];

// Try both field names
$transformersJson = $maintenance['transformers_data'] ?? $maintenance['transformers'] ?? null;

if (!empty($transformersJson)) {
    $transformers = json_decode($transformersJson, true);
    
    if ($transformers && is_array($transformers)) {
        echo "<p class='success'>✅ " . count($transformers) . " transformers</p>";
        
        foreach ($transformers as $idx => $tr) {
            echo "<h4>Transformer " . ($idx + 1) . "</h4>";
            
            if (!empty($tr['photos'])) {
                echo "<p class='success'>✅ " . count($tr['photos']) . " φωτογραφίες</p>";
                foreach ($tr['photos'] as $photoPath) {
                    $allPhotos[] = ['source' => "TR" . ($idx+1), 'path' => $photoPath];
                    echo "<div>{$photoPath}</div>";
                }
            } else {
                echo "<p class='warning'>⚠️ Χωρίς φωτογραφίες</p>";
            }
        }
    }
} else {
    echo "<p class='error'>❌ Δεν βρέθηκε transformers_data ή transformers JSON</p>";
}

echo "</div>";

// Check files
$uploadsDir = __DIR__ . '/uploads/maintenances/';
echo "<div class='section'><h3>Αρχεία</h3>";
echo "<p>Uploads Dir: {$uploadsDir}</p>";
echo "<p>Exists: " . (is_dir($uploadsDir) ? '<span class="success">✅</span>' : '<span class="error">❌</span>') . "</p>";

if (count($allPhotos) > 0) {
    echo "<table><tr><th>Source</th><th>Path</th><th>Exists?</th><th>Size</th></tr>";
    
    foreach ($allPhotos as $photo) {
        $fullPath = __DIR__ . '/' . $photo['path'];
        $exists = file_exists($fullPath);
        $size = $exists ? filesize($fullPath) : 0;
        
        echo "<tr>";
        echo "<td>{$photo['source']}</td>";
        echo "<td>{$photo['path']}</td>";
        echo "<td>" . ($exists ? '<span class="success">✅</span>' : '<span class="error">❌</span>') . "</td>";
        echo "<td>" . ($size ? number_format($size/1024, 2) . ' KB' : '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show images
    echo "<h4>Preview:</h4>";
    foreach ($allPhotos as $photo) {
        $webPath = $photo['path'];
        if (file_exists(__DIR__ . '/' . $webPath)) {
            echo "<div class='photo-preview'>";
            echo "<img src='{$webPath}'><br><small>{$photo['source']}</small>";
            echo "</div>";
        }
    }
} else {
    echo "<p class='warning'>⚠️ Δεν βρέθηκαν φωτογραφίες</p>";
}

// List all files in uploads
if (is_dir($uploadsDir)) {
    echo "<h4>Αρχεία στον φάκελο:</h4>";
    
    // Try to match files with this maintenance
    $maintenanceTimestamp = strtotime($maintenance['created_at']);
    
    echo "<table><tr><th>Filename</th><th>Size</th><th>Match?</th></tr>";
    $files = scandir($uploadsDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $fullPath = $uploadsDir . $file;
            $size = filesize($fullPath);
            
            // Check if filename contains maintenance ID or timestamp
            $isMatch = false;
            $reason = '';
            
            if (strpos($file, "_" . $maintenanceId . "_") !== false) {
                $isMatch = true;
                $reason = "Contains maintenance ID";
            } elseif (strpos($file, "maintenance_" . $maintenanceTimestamp) !== false) {
                $isMatch = true;
                $reason = "Contains timestamp";
            }
            
            echo "<tr>";
            echo "<td>{$file}</td>";
            echo "<td>" . number_format($size/1024, 2) . " KB</td>";
            echo "<td>" . ($isMatch ? "<span class='success'>✅ {$reason}</span>" : "") . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
}

echo "</div>";

echo "<div class='section'><a href='?'>← Πίσω</a></div>";

?>
</body>
</html>
