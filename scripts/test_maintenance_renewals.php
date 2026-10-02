<?php
/**
 * Test Suite for Maintenance Renewals, History Chaining, and Customer Merging
 */

require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../models/TransformerMaintenance.php';

echo "===============================================\n";
echo "Testing Maintenance Renewals & Customer Merge\n";
echo "===============================================\n\n";

$errors = [];

// 1. Check Migration file exists
$migrationFile = __DIR__ . '/../migrations/025_add_renewal_and_chain_to_transformer_maintenances.sql';
if (file_exists($migrationFile)) {
    $content = file_get_contents($migrationFile);
    if (strpos($content, 'previous_id') !== false && strpos($content, 'renewed_by_id') !== false && strpos($content, 'is_renewed') !== false) {
        echo " [PASS] Migration 025 exists and contains renewal schema\n";
    } else {
        $errors[] = "Migration 025 missing required columns";
        echo " [FAIL] Migration 025 missing required columns\n";
    }
} else {
    $errors[] = "Migration 025 file not found";
    echo " [FAIL] Migration 025 file not found\n";
}

// 2. Check TransformerMaintenance model methods
$reflector = new ReflectionClass('TransformerMaintenance');
$expectedMethods = [
    'getMaintenanceCustomers',
    'getMaintenanceHistory',
    'markAsRenewed',
    'mergeCustomers',
    'getOverdueCount',
    'getUpcomingCount'
];

foreach ($expectedMethods as $method) {
    if ($reflector->hasMethod($method)) {
        echo " [PASS] TransformerMaintenance has method '{$method}'\n";
    } else {
        $errors[] = "TransformerMaintenance missing method '{$method}'";
        echo " [FAIL] TransformerMaintenance missing method '{$method}'\n";
    }
}

// 3. Check Controller methods
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../controllers/TransformerMaintenanceController.php';

$ctrlReflector = new ReflectionClass('TransformerMaintenanceController');
$expectedCtrlMethods = [
    'mergeCustomers',
    'markRenewed',
    'create',
    'store',
    'show',
    'edit'
];

foreach ($expectedCtrlMethods as $method) {
    if ($ctrlReflector->hasMethod($method)) {
        echo " [PASS] TransformerMaintenanceController has method '{$method}'\n";
    } else {
        $errors[] = "TransformerMaintenanceController missing method '{$method}'";
        echo " [FAIL] TransformerMaintenanceController missing method '{$method}'\n";
    }
}

// 4. Verify views contain the new UX elements
$createView = file_get_contents(__DIR__ . '/../views/maintenances/create.php');
if (strpos($createView, 'maintenanceCustomersList') !== false && strpos($createView, 'previous_id') !== false) {
    echo " [PASS] views/maintenances/create.php has datalist and previous_id\n";
} else {
    $errors[] = "views/maintenances/create.php missing datalist or previous_id";
    echo " [FAIL] views/maintenances/create.php missing datalist or previous_id\n";
}

$indexView = file_get_contents(__DIR__ . '/../views/maintenances/index.php');
if (strpos($indexView, 'mergeCustomersModal') !== false && strpos($indexView, 'renew_id') !== false) {
    echo " [PASS] views/maintenances/index.php has merge modal and renew_id button\n";
} else {
    $errors[] = "views/maintenances/index.php missing merge modal or renew_id button";
    echo " [FAIL] views/maintenances/index.php missing merge modal or renew_id button\n";
}

$viewView = file_get_contents(__DIR__ . '/../views/maintenances/view.php');
if (strpos($viewView, 'Ιστορικό Ετήσιων Συντηρήσεων') !== false && strpos($viewView, 'renew_id') !== false) {
    echo " [PASS] views/maintenances/view.php has history table and renewal button\n";
} else {
    $errors[] = "views/maintenances/view.php missing history table or renewal button";
    echo " [FAIL] views/maintenances/view.php missing history table or renewal button\n";
}

// 5. Verify index.php routing
$indexPhp = file_get_contents(__DIR__ . '/../index.php');
if (strpos($indexPhp, 'mergeCustomers') !== false && strpos($indexPhp, 'markRenewed') !== false) {
    echo " [PASS] index.php routes merge-customers and mark-renewed\n";
} else {
    $errors[] = "index.php missing merge-customers or mark-renewed routes";
    echo " [FAIL] index.php missing routes\n";
}

echo "\n-----------------------------------------------\n";
echo "Total Errors: " . count($errors) . "\n";
echo "Status: " . (empty($errors) ? "ALL TESTS PASSED" : "FAILED") . "\n";
echo "===============================================\n";

if (!empty($errors)) {
    exit(1);
}
