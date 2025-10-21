<?php
/**
 * List all projects
 * Usage: http://localhost/handycrm/list_projects.php
 */

require_once 'config/config.php';

global $db;

echo "<!DOCTYPE html>
<html lang='el'>
<head>
    <meta charset='UTF-8'>
    <title>Projects List</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #667eea; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .btn { padding: 5px 10px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>📋 All Projects</h1>
    <hr>";

$projects = $db->fetchAll("SELECT p.*, 
                                   CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                                   CONCAT(u.first_name, ' ', u.last_name) as technician_name
                            FROM projects p
                            LEFT JOIN customers c ON p.customer_id = c.id
                            LEFT JOIN users u ON p.assigned_technician = u.id
                            ORDER BY p.id DESC");

if (empty($projects)) {
    echo "<p style='color: red;'>❌ No projects found!</p>";
    echo "<p><a href='?route=/projects/create' class='btn'>Create New Project</a></p>";
} else {
    echo "<p>Found <strong>" . count($projects) . "</strong> projects</p>";
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Title</th>
            <th>Customer</th>
            <th>Technician</th>
            <th>Status</th>
            <th>Materials</th>
            <th>Labor</th>
            <th>Total</th>
            <th>Actions</th>
          </tr>";
    
    foreach ($projects as $p) {
        $statusColors = [
            'new' => '#6c757d',
            'in_progress' => '#0d6efd',
            'completed' => '#198754',
            'invoiced' => '#0dcaf0',
            'cancelled' => '#dc3545'
        ];
        
        $statusLabels = [
            'new' => 'Νέο',
            'in_progress' => 'Σε Εξέλιξη',
            'completed' => 'Ολοκληρωμένο',
            'invoiced' => 'Τιμολογημένο',
            'cancelled' => 'Ακυρωμένο'
        ];
        
        $statusColor = $statusColors[$p['status']] ?? '#6c757d';
        $statusLabel = $statusLabels[$p['status']] ?? $p['status'];
        
        echo "<tr>";
        echo "<td><strong>" . $p['id'] . "</strong></td>";
        echo "<td>" . htmlspecialchars($p['title']) . "</td>";
        echo "<td>" . htmlspecialchars($p['customer_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($p['technician_name'] ?? 'N/A') . "</td>";
        echo "<td><span style='background: $statusColor; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;'>$statusLabel</span></td>";
        echo "<td>" . number_format($p['material_cost'], 2) . "€</td>";
        echo "<td>" . number_format($p['labor_cost'], 2) . "€</td>";
        echo "<td><strong>" . number_format($p['total_cost'], 2) . "€</strong></td>";
        echo "<td>";
        
        if ($p['material_cost'] == 0 && $p['labor_cost'] == 0) {
            echo "<a href='add_project_costs.php?project_id={$p['id']}&materials=150&labor=250' class='btn' style='background: #ffc107; color: black; font-size: 11px;'>Add Costs</a> ";
        }
        
        if ($p['status'] !== 'invoiced') {
            echo "<a href='?route=/projects/show&id={$p['id']}' class='btn' style='background: #198754; font-size: 11px;'>View & Test</a>";
        } else {
            echo "<a href='?route=/projects/show&id={$p['id']}' class='btn' style='background: #0dcaf0; color: black; font-size: 11px;'>View</a>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='?route=/projects' class='btn'>Go to Projects Page</a></p>";
echo "<p><a href='?route=/projects/create' class='btn' style='background: #198754;'>Create New Project</a></p>";

echo "</body></html>";
?>
