<?php
// Test if index.php has the correct code
// Upload to ecowatt.gr/crm/test_index.php

$indexContent = file_get_contents('index.php');

echo "=== INDEX.PHP TEST ===\n\n";

// Check for the updated users route
if (strpos($indexContent, "Permission check is handled in UserController methods") !== false) {
    echo "✓ index.php HAS the updated /users route (NO hardcoded admin check)\n";
} else {
    echo "✗ index.php DOES NOT have the updated /users route\n";
}

// Check for old hardcoded check
if (strpos($indexContent, "\$_SESSION['role'] !== 'admin'") !== false) {
    // Find all occurrences
    preg_match_all('/.*\$_SESSION\[\'role\'\] !== \'admin\'.*/', $indexContent, $matches);
    echo "\n⚠️ Found " . count($matches[0]) . " hardcoded admin checks:\n";
    foreach ($matches[0] as $line) {
        echo "  - " . trim($line) . "\n";
    }
}

// Check file modification time
echo "\nindex.php last modified: " . date('Y-m-d H:i:s', filemtime('index.php')) . "\n";

echo "\n=== END TEST ===\n";
