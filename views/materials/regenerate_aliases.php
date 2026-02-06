<?php
/**
 * Regenerate Material Aliases (Admin Tool)
 * Access via: /materials?action=regenerate-aliases
 */

session_start();
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/MaterialAliasGenerator.php';

// Simple auth check
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$db = new Database();

// Get all materials
$sql = "SELECT id, name FROM materials_catalog";
$materials = $db->query($sql);

$updated = 0;
$results = [];

foreach ($materials as $material) {
    $aliases = MaterialAliasGenerator::generate($material['name']);
    
    $updateSql = "UPDATE materials_catalog SET aliases = ? WHERE id = ?";
    $updateStmt = $db->execute($updateSql, [$aliases, $material['id']]);
    
    if ($updateStmt) {
        $updated++;
        $results[] = [
            'name' => $material['name'],
            'aliases' => $aliases
        ];
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Aliases Regenerated</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .success { color: green; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1 class="success">✅ Regenerated aliases for <?= $updated ?> materials!</h1>
    
    <table>
        <tr>
            <th>Material Name</th>
            <th>Generated Aliases</th>
        </tr>
        <?php foreach ($results as $result): ?>
        <tr>
            <td><?= htmlspecialchars($result['name']) ?></td>
            <td><small><?= htmlspecialchars($result['aliases']) ?></small></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <p><a href="/handycrm/?route=/materials">← Back to Materials</a></p>
</body>
</html>
