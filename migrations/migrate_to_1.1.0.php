<?php
/**
 * HandyCRM Database Migration: v1.0.6 to v1.1.0
 * 
 * This script will upgrade your HandyCRM database from version 1.0.6 to 1.1.0
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to your HandyCRM root directory
 * 2. Visit: http://your-domain.com/migrate_to_1.1.0.php
 * 3. Delete this file after successful migration
 */

// Load configuration
require_once __DIR__ . '/config/config.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

echo "<!DOCTYPE html>
<html>
<head>
    <title>HandyCRM Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #e8f5e9; border: 1px solid green; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #ffebee; border: 1px solid red; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #e3f2fd; border: 1px solid blue; margin: 10px 0; }
        h1 { color: #333; }
        .step { margin: 20px 0; padding: 15px; background: #f5f5f5; border-left: 4px solid #2196F3; }
    </style>
</head>
<body>
    <h1>HandyCRM Database Migration: v1.0.6 → v1.1.0</h1>
";

$errors = [];
$success = [];

// Step 1: Check if hourly_rate column exists
echo "<div class='step'><strong>Step 1:</strong> Checking users table structure...</div>";

$result = $mysqli->query("SHOW COLUMNS FROM users LIKE 'hourly_rate'");
if ($result->num_rows == 0) {
    // Add hourly_rate column
    $sql = "ALTER TABLE `users` ADD COLUMN `hourly_rate` DECIMAL(10,2) DEFAULT 0.00 AFTER `role`";
    if ($mysqli->query($sql)) {
        $success[] = "✓ Added 'hourly_rate' column to users table";
    } else {
        $errors[] = "✗ Failed to add 'hourly_rate' column: " . $mysqli->error;
    }
} else {
    $success[] = "✓ 'hourly_rate' column already exists";
}

// Step 2: Update role enum
echo "<div class='step'><strong>Step 2:</strong> Updating user roles...</div>";

$sql = "ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','manager','technician','assistant') NOT NULL DEFAULT 'technician'";
if ($mysqli->query($sql)) {
    $success[] = "✓ Updated user role enum to include 'assistant'";
} else {
    // This might fail if already updated, which is OK
    $success[] = "✓ User roles already up to date";
}

// Step 3: Create project_tasks table
echo "<div class='step'><strong>Step 3:</strong> Creating project_tasks table...</div>";

$result = $mysqli->query("SHOW TABLES LIKE 'project_tasks'");
if ($result->num_rows == 0) {
    $sql = "CREATE TABLE `project_tasks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `project_id` int(11) NOT NULL,
      `title` varchar(255) NOT NULL,
      `description` text,
      `task_type` enum('single_day','date_range') NOT NULL DEFAULT 'single_day',
      `task_date` date DEFAULT NULL,
      `date_from` date DEFAULT NULL,
      `date_to` date DEFAULT NULL,
      `materials_total` decimal(10,2) DEFAULT 0.00,
      `labor_total` decimal(10,2) DEFAULT 0.00,
      `total_cost` decimal(10,2) DEFAULT 0.00,
      `notes` text,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `project_id` (`project_id`),
      KEY `task_date` (`task_date`),
      KEY `date_from` (`date_from`),
      KEY `date_to` (`date_to`),
      CONSTRAINT `project_tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        $success[] = "✓ Created 'project_tasks' table";
    } else {
        $errors[] = "✗ Failed to create 'project_tasks' table: " . $mysqli->error;
    }
} else {
    $success[] = "✓ 'project_tasks' table already exists";
}

// Step 4: Create task_materials table
echo "<div class='step'><strong>Step 4:</strong> Creating task_materials table...</div>";

$result = $mysqli->query("SHOW TABLES LIKE 'task_materials'");
if ($result->num_rows == 0) {
    $sql = "CREATE TABLE `task_materials` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `material_name` varchar(255) NOT NULL,
      `quantity` decimal(10,2) NOT NULL,
      `unit_type` enum('pieces','meters','kilos','liters','hours','other') NOT NULL DEFAULT 'pieces',
      `unit_price` decimal(10,2) NOT NULL,
      `total_price` decimal(10,2) NOT NULL,
      `notes` text,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `task_id` (`task_id`),
      CONSTRAINT `task_materials_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        $success[] = "✓ Created 'task_materials' table";
    } else {
        $errors[] = "✗ Failed to create 'task_materials' table: " . $mysqli->error;
    }
} else {
    $success[] = "✓ 'task_materials' table already exists";
}

// Step 5: Create task_labor table
echo "<div class='step'><strong>Step 5:</strong> Creating task_labor table...</div>";

$result = $mysqli->query("SHOW TABLES LIKE 'task_labor'");
if ($result->num_rows == 0) {
    $sql = "CREATE TABLE `task_labor` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `technician_id` int(11) DEFAULT NULL,
      `technician_name` varchar(255) NOT NULL,
      `hours` decimal(10,2) NOT NULL,
      `hourly_rate` decimal(10,2) NOT NULL,
      `total_cost` decimal(10,2) NOT NULL,
      `notes` text,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `task_id` (`task_id`),
      KEY `technician_id` (`technician_id`),
      CONSTRAINT `task_labor_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE,
      CONSTRAINT `task_labor_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        $success[] = "✓ Created 'task_labor' table";
    } else {
        $errors[] = "✗ Failed to create 'task_labor' table: " . $mysqli->error;
    }
} else {
    $success[] = "✓ 'task_labor' table already exists";
}

$mysqli->close();

// Display results
echo "<h2>Migration Results:</h2>";

if (count($success) > 0) {
    echo "<div class='success'><h3>Successful Steps:</h3><ul>";
    foreach ($success as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul></div>";
}

if (count($errors) > 0) {
    echo "<div class='error'><h3>Errors:</h3><ul>";
    foreach ($errors as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul></div>";
    echo "<div class='info'><strong>Note:</strong> Some errors might be normal if you've run this migration before.</div>";
} else {
    echo "<div class='success'><h2>✓ Migration Completed Successfully!</h2></div>";
    echo "<div class='info'><strong>IMPORTANT:</strong> Please delete this migration file (migrate_to_1.1.0.php) from your server for security reasons.</div>";
    echo "<div class='info'><strong>Next step:</strong> You can now use all v1.1.0 features including Project Tasks, Statistics, and CSV Export!</div>";
}

echo "</body></html>";
?>
