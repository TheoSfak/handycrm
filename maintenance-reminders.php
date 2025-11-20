<?php
/**
 * Maintenance Reminders Command Line Script
 * This script checks for upcoming maintenances and sends email reminders
 */

// Include required files
require_once 'classes/EmailService.php';

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=handycrm;charset=utf8mb4', 'handycrm_user', 'handycrm123');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Initialize EmailService
try {
    $emailService = new EmailService($pdo);
} catch (Exception $e) {
    echo "Failed to initialize EmailService: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if SMTP is configured
if (!$emailService->isConfigured()) {
    echo "SMTP is not configured. Please configure email settings first.\n";
    exit(1);
}

// Get command line arguments
$days_ahead = isset($argv[1]) ? (int)$argv[1] : 7;

echo "Starting maintenance reminder process...\n";
echo "Checking for maintenances in the next {$days_ahead} days.\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Process maintenance reminders
    $result = $emailService->processMaintenanceReminders($days_ahead);
    
    echo "=== MAINTENANCE REMINDER RESULTS ===\n";
    echo "Total maintenances found: " . $result['total_maintenances'] . "\n";
    echo "Emails sent successfully: " . $result['processed'] . "\n";
    echo "Errors encountered: " . count($result['errors']) . "\n";
    
    if (!empty($result['errors'])) {
        echo "\n=== ERRORS ===\n";
        foreach ($result['errors'] as $error) {
            echo "ERROR: " . $error . "\n";
        }
    }
    
    echo "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";
    
    // Exit with appropriate code
    exit(count($result['errors']) > 0 ? 1 : 0);
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Failed at: " . date('Y-m-d H:i:s') . "\n";
    exit(1);
}
?>