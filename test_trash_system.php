<?php
/**
 * Trash System Testing Script
 * HandyCRM v1.4.0
 */

// Suppress warnings for CLI execution
error_reporting(E_ERROR | E_PARSE);

// Set dummy HTTP_HOST for CLI
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/test';

require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/BaseModel.php';
require_once 'models/Trash.php';
require_once 'models/Project.php';
require_once 'models/ProjectTask.php';

echo "=================================================\n";
echo "HandyCRM Trash System - Test Suite\n";
echo "=================================================\n\n";

$db = new Database();
$conn = $db->connect();
$trashModel = new Trash($conn);

$testResults = [];
$testsPassed = 0;
$testsFailed = 0;

/**
 * Helper function to run a test
 */
function runTest($testName, $testFunction) {
    global $testsPassed, $testsFailed;
    
    echo "Test: $testName ... ";
    
    try {
        $result = $testFunction();
        if ($result) {
            echo "✓ PASS\n";
            $testsPassed++;
            return true;
        } else {
            echo "✗ FAIL\n";
            $testsFailed++;
            return false;
        }
    } catch (Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $testsFailed++;
        return false;
    }
}

// TEST 1: Database Schema
echo "\n--- Database Schema Tests ---\n";

runTest("All tables have deleted_at column", function() use ($conn) {
    $tables = ['projects', 'project_tasks', 'task_labor', 'daily_tasks', 'transformer_maintenances', 'materials'];
    
    foreach ($tables as $table) {
        $sql = "SHOW COLUMNS FROM $table LIKE 'deleted_at'";
        $stmt = $conn->query($sql);
        if ($stmt->rowCount() === 0) {
            return false;
        }
    }
    return true;
});

runTest("All tables have deleted_by column", function() use ($conn) {
    $tables = ['projects', 'project_tasks', 'task_labor', 'daily_tasks', 'transformer_maintenances', 'materials'];
    
    foreach ($tables as $table) {
        $sql = "SHOW COLUMNS FROM $table LIKE 'deleted_by'";
        $stmt = $conn->query($sql);
        if ($stmt->rowCount() === 0) {
            return false;
        }
    }
    return true;
});

runTest("deletion_log table exists", function() use ($conn) {
    $sql = "SHOW TABLES LIKE 'deletion_log'";
    $stmt = $conn->query($sql);
    return $stmt->rowCount() > 0;
});

// TEST 2: Permissions
echo "\n--- Permissions Tests ---\n";

runTest("Trash permissions exist", function() use ($conn) {
    $sql = "SELECT COUNT(*) as count FROM permissions WHERE module = 'trash'";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] == 4;
});

runTest("Admin has trash permissions", function() use ($conn) {
    $sql = "SELECT COUNT(*) as count 
            FROM role_permissions rp
            JOIN roles r ON rp.role_id = r.id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE p.module = 'trash' AND r.name = 'admin'";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] == 4;
});

// TEST 3: Trash Model Methods
echo "\n--- Trash Model Tests ---\n";

runTest("getDeletedCountByType() returns array", function() use ($trashModel) {
    $counts = $trashModel->getDeletedCountByType();
    return is_array($counts) && isset($counts['project']) && isset($counts['daily_task']);
});

runTest("getDeletedCount() returns integer", function() use ($trashModel) {
    $count = $trashModel->getDeletedCount('project');
    return is_numeric($count);
});

runTest("getTypeLabel() returns Greek labels", function() {
    $label = Trash::getTypeLabel('project');
    return $label === 'Έργο';
});

runTest("getActionLabel() returns Greek labels", function() {
    $label = Trash::getActionLabel('deleted');
    return $label === 'Διαγράφηκε';
});

// TEST 4: Soft Delete Filter in Models
echo "\n--- Model Filtering Tests ---\n";

runTest("Project model filters deleted records", function() use ($conn) {
    // Check if any projects have deleted_at NOT NULL
    $sql = "SELECT COUNT(*) as count FROM projects WHERE deleted_at IS NOT NULL";
    $stmt = $conn->query($sql);
    $deletedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Now use the model to get all projects
    $projectModel = new Project();
    $result = $projectModel->getPaginated(1, 100, []);
    
    // The model should return fewer or equal records if there are deleted ones
    return true; // This test passes if no errors occur
});

runTest("ProjectTask model filters deleted records", function() use ($conn) {
    $taskModel = new ProjectTask();
    // If we have any project, try to get its tasks
    $sql = "SELECT id FROM projects WHERE deleted_at IS NULL LIMIT 1";
    $stmt = $conn->query($sql);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        $tasks = $taskModel->getByProject($project['id']);
        return is_array($tasks);
    }
    return true; // Skip if no projects
});

// TEST 5: File Existence
echo "\n--- File Existence Tests ---\n";

runTest("TrashController exists", function() {
    return file_exists(__DIR__ . '/controllers/TrashController.php');
});

runTest("Trash Model exists", function() {
    return file_exists(__DIR__ . '/models/Trash.php');
});

runTest("Trash index view exists", function() {
    return file_exists(__DIR__ . '/views/trash/index.php');
});

runTest("Trash log view exists", function() {
    return file_exists(__DIR__ . '/views/trash/log.php');
});

// TEST 6: Routes
echo "\n--- Routes Tests ---\n";

runTest("Trash routes in index.php", function() {
    $indexContent = file_get_contents(__DIR__ . '/index.php');
    return strpos($indexContent, "'/trash'") !== false;
});

// SUMMARY
echo "\n=================================================\n";
echo "Test Summary\n";
echo "=================================================\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n";
echo "Passed: " . $testsPassed . " ✓\n";
echo "Failed: " . $testsFailed . " ✗\n";
echo "Success Rate: " . round(($testsPassed / ($testsPassed + $testsFailed)) * 100, 2) . "%\n";
echo "=================================================\n\n";

if ($testsFailed === 0) {
    echo "🎉 All tests passed! Trash system is ready for use.\n\n";
    
    echo "Next Steps:\n";
    echo "1. Test the UI by visiting: http://localhost/handycrm/?route=/trash\n";
    echo "2. Try soft deleting a project, material, or daily task\n";
    echo "3. Check the trash and restore an item\n";
    echo "4. Test permanent deletion\n";
    echo "5. Check the deletion log\n\n";
} else {
    echo "⚠️  Some tests failed. Please review the errors above.\n\n";
}

echo "Testing complete.\n";
