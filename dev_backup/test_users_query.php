<?php
// Test UserController query
// Upload to ecowatt.gr/crm/test_users_query.php

session_start();
require_once 'config/config.php';
require_once 'classes/Database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== USERS QUERY TEST ===\n\n";

$database = new Database();
$db = $database->connect();

// The exact query from UserController::index()
$stmt = $db->query("
    SELECT u.*, r.display_name as role_display_name, r.name as role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.first_name
    LIMIT 3
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($users) . " users\n\n";

foreach ($users as $u) {
    echo "Username: {$u['username']}\n";
    echo "  - role_id: " . ($u['role_id'] ?? 'NULL') . "\n";
    echo "  - role_name: " . ($u['role_name'] ?? 'NULL') . "\n";
    echo "  - role_display_name: " . ($u['role_display_name'] ?? 'NULL') . "\n";
    
    // Check if old 'role' column exists in result
    if (isset($u['role'])) {
        echo "  - OLD 'role': {$u['role']} ⚠️\n";
    } else {
        echo "  - OLD 'role': NOT IN RESULT ✓\n";
    }
    
    echo "\n";
}

echo "=== END TEST ===\n";
