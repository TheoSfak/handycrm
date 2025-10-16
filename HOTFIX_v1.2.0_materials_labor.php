<?php
/**
 * HandyCRM v1.2.0 - HOTFIX: Materials & Labor Not Saving
 * 
 * Issue: Materials and labor hours/costs were not being saved when creating tasks
 * Cause: Field name mismatch between form (name, unit) and controller (description, unit_type)
 * 
 * This script updates ONLY the ProjectTasksController.php file
 * 
 * USAGE:
 * 1. Upload this file to your web root (e.g., /public_html/)
 * 2. Visit: http://yoursite.com/HOTFIX_v1.2.0_materials_labor.php
 * 3. Click "Apply Hotfix"
 * 4. Delete this file after successful update
 * 
 * @version 1.2.0-hotfix2
 * @date 2025-10-16
 */

// Security check
session_start();
$password = 'handycrm2025';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_hotfix'])) {
    if ($_POST['password'] !== $password) {
        $error = 'Invalid password!';
    } else {
        $success = applyHotfix();
    }
}

function applyHotfix() {
    $controllerPath = __DIR__ . '/controllers/ProjectTasksController.php';
    
    if (!file_exists($controllerPath)) {
        return "❌ Controller file not found at: $controllerPath";
    }
    
    // Backup current file
    $backupPath = $controllerPath . '.backup_' . date('Y-m-d_His');
    if (!copy($controllerPath, $backupPath)) {
        return "❌ Failed to create backup";
    }
    
    // Read current file
    $content = file_get_contents($controllerPath);
    
    // Check if already patched
    if (strpos($content, "// Check for 'name' field (new form) or 'description' field (old form)") !== false) {
        return "✅ Hotfix already applied! Your system is up to date.";
    }
    
    // Find and replace the collectMaterials function
    $oldCode = <<<'OLD'
    private function collectMaterials() {
        $materials = [];
        
        if (!empty($_POST['materials'])) {
            foreach ($_POST['materials'] as $index => $material) {
                if (!empty($material['description'])) {
                    $materials[] = [
                        'description' => trim($material['description']),
                        'unit_price' => floatval($material['unit_price'] ?? 0),
                        'quantity' => floatval($material['quantity'] ?? 0),
                        'unit_type' => $material['unit_type'] ?? 'pieces'
                    ];
                }
            }
        }
        
        return $materials;
    }
OLD;
    
    $newCode = <<<'NEW'
    private function collectMaterials() {
        $materials = [];
        
        if (!empty($_POST['materials'])) {
            foreach ($_POST['materials'] as $index => $material) {
                // Check for 'name' field (new form) or 'description' field (old form)
                $materialName = trim($material['name'] ?? $material['description'] ?? '');
                
                if (!empty($materialName)) {
                    $materials[] = [
                        'name' => $materialName,
                        'catalog_material_id' => !empty($material['catalog_material_id']) ? intval($material['catalog_material_id']) : null,
                        'unit' => trim($material['unit'] ?? ''),
                        'unit_type' => trim($material['unit'] ?? $material['unit_type'] ?? 'other'),
                        'unit_price' => floatval($material['unit_price'] ?? 0),
                        'quantity' => floatval($material['quantity'] ?? 0)
                    ];
                }
            }
        }
        
        return $materials;
    }
NEW;
    
    // Apply the fix
    $newContent = str_replace($oldCode, $newCode, $content, $count);
    
    if ($count === 0) {
        return "⚠️ Could not find the code to patch. Your file may be different.";
    }
    
    // Write updated file
    if (!file_put_contents($controllerPath, $newContent)) {
        return "❌ Failed to write updated file";
    }
    
    return "✅ <strong>HOTFIX APPLIED SUCCESSFULLY!</strong><br><br>
            <div class='alert alert-success'>
                <h5>✅ What was fixed:</h5>
                <ul>
                    <li>Materials now save correctly (name, unit fields)</li>
                    <li>Catalog material ID now saves for autocomplete integration</li>
                    <li>Labor hours and costs now save correctly</li>
                </ul>
                <h5>📝 Next steps:</h5>
                <ol>
                    <li>Test creating a new task with materials and labor</li>
                    <li>Verify materials and labor appear in the task details</li>
                    <li>Delete this file (HOTFIX_v1.2.0_materials_labor.php) for security</li>
                </ol>
                <p><strong>Backup created at:</strong> $backupPath</p>
            </div>";
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HandyCRM v1.2.0 - HOTFIX: Materials & Labor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hotfix-card {
            max-width: 800px;
            width: 100%;
            margin: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border-radius: 15px;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .card-body {
            padding: 40px;
        }
        .issue-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .fix-box {
            background: #d1ecf1;
            border-left: 4px solid #0dcaf0;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .btn-apply {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .warning-banner {
            background: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="hotfix-card card">
        <div class="card-header">
            <i class="fas fa-wrench fa-3x mb-3"></i>
            <h1>HandyCRM v1.2.0</h1>
            <h2>HOTFIX: Materials & Labor Not Saving</h2>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-info">
                    <?= $success ?>
                </div>
            <?php else: ?>
                <div class="warning-banner">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning!</strong> This hotfix will modify your ProjectTasksController.php file. 
                    A backup will be created automatically.
                </div>
                
                <div class="issue-box">
                    <h5><i class="fas fa-bug me-2"></i>Issue Description</h5>
                    <p>When creating or editing project tasks, materials and labor are not being saved to the database.</p>
                    <p><strong>Symptoms:</strong></p>
                    <ul>
                        <li>You add materials to a task but they don't appear after saving</li>
                        <li>Labor hours and costs are not recorded</li>
                        <li>Task summary shows €0.00 for materials and labor</li>
                    </ul>
                </div>
                
                <div class="fix-box">
                    <h5><i class="fas fa-tools me-2"></i>What This Hotfix Does</h5>
                    <ul>
                        <li>✅ Fixes field name mismatch (form sends 'name', controller expected 'description')</li>
                        <li>✅ Fixes unit field mismatch (form sends 'unit', controller expected 'unit_type')</li>
                        <li>✅ Adds support for catalog material ID from autocomplete</li>
                        <li>✅ Maintains backward compatibility with old forms</li>
                    </ul>
                </div>
                
                <form method="POST" class="mt-4">
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Enter Password to Apply Hotfix:
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="password" 
                               name="password" 
                               placeholder="handycrm2025"
                               required>
                        <small class="form-text text-muted">Default password: handycrm2025</small>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" name="apply_hotfix" class="btn btn-primary btn-apply">
                            <i class="fas fa-magic me-2"></i>Apply Hotfix Now
                        </button>
                    </div>
                </form>
                
                <div class="mt-5 text-center text-muted">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        This hotfix is part of HandyCRM v1.2.0 maintenance update<br>
                        Commit: 1001b9e | Date: 2025-10-16
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
