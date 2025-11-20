<?php
/**
 * Session Debug - Check current user session
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "=================================================\n";
echo "HandyCRM Session Debug\n";
echo "=================================================\n\n";

echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "✓ ACTIVE" : "✗ NOT ACTIVE") . "\n";
echo "Session ID: " . session_id() . "\n\n";

echo "--- Session Variables ---\n";

if (empty($_SESSION)) {
    echo "⚠️  Session is EMPTY - You need to login!\n\n";
    echo "To fix:\n";
    echo "1. Visit: http://localhost/handycrm/?route=/login\n";
    echo "2. Login with username: admin\n";
    echo "3. Check sidebar for 'Κάδος Απορριμμάτων'\n";
} else {
    foreach ($_SESSION as $key => $value) {
        if (is_array($value)) {
            echo "$key => " . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "$key => $value\n";
        }
    }
    
    echo "\n--- Role Check ---\n";
    $role = $_SESSION['role'] ?? 'NOT SET';
    echo "Role: $role\n";
    
    $isAdmin = ($role === 'admin');
    echo "Is Admin: " . ($isAdmin ? "✓ YES" : "✗ NO") . "\n";
    
    if ($isAdmin) {
        echo "\n✅ You ARE admin - Trash menu SHOULD be visible\n";
        echo "\nIf you don't see the trash menu:\n";
        echo "1. Clear browser cache (Ctrl+Shift+Delete)\n";
        echo "2. Hard refresh (Ctrl+F5)\n";
        echo "3. Check browser console for JavaScript errors (F12)\n";
    } else {
        echo "\n⚠️  You are NOT admin - Trash menu will NOT be visible\n";
        echo "\nTo see trash menu:\n";
        echo "1. Logout from current account\n";
        echo "2. Login with admin username\n";
    }
}

echo "\n=================================================\n";
echo "Visit http://localhost/handycrm/?route=/trash to test\n";
echo "=================================================\n";
