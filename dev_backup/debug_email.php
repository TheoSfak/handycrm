<?php
/**
 * Debug script for email functionality
 * Upload this to production and access it via browser
 * Example: https://your-site.com/debug_email.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Email Debug Information</h1>";
echo "<pre>";

// 1. Check PHP mail configuration
echo "\n=== PHP MAIL CONFIGURATION ===\n";
echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "SMTP: " . ini_get('SMTP') . "\n";
echo "smtp_port: " . ini_get('smtp_port') . "\n";

// 2. Check if PHPMailer exists
echo "\n=== PHPMAILER CHECK ===\n";
$phpmailerPath = __DIR__ . '/classes/EmailService.php';
if (file_exists($phpmailerPath)) {
    echo "✓ EmailService.php EXISTS at: $phpmailerPath\n";
    require_once $phpmailerPath;
    echo "✓ EmailService.php loaded successfully\n";
} else {
    echo "✗ EmailService.php NOT FOUND at: $phpmailerPath\n";
}

// 3. Check database connection
echo "\n=== DATABASE CONNECTION ===\n";
try {
    require_once __DIR__ . '/classes/Database.php';
    $db = new Database();
    $pdo = $db->connect();
    echo "✓ Database connected successfully\n";
    
    // Check email_notifications table
    $stmt = $pdo->query("SHOW TABLES LIKE 'email_notifications'");
    if ($stmt->rowCount() > 0) {
        echo "✓ email_notifications table EXISTS\n";
        
        // Get recent emails
        $stmt = $pdo->query("SELECT * FROM email_notifications ORDER BY created_at DESC LIMIT 5");
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\nRecent email attempts (" . count($emails) . "):\n";
        foreach ($emails as $email) {
            echo "  - " . $email['created_at'] . " | " . $email['type'] . " | " . $email['status'] . " | " . $email['recipient_email'] . "\n";
        }
    } else {
        echo "✗ email_notifications table DOES NOT EXIST\n";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

// 4. Check config.php for email settings
echo "\n=== EMAIL CONFIGURATION ===\n";
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
    
    if (defined('SMTP_HOST')) {
        echo "SMTP_HOST: " . SMTP_HOST . "\n";
    } else {
        echo "✗ SMTP_HOST not defined\n";
    }
    
    if (defined('SMTP_PORT')) {
        echo "SMTP_PORT: " . SMTP_PORT . "\n";
    } else {
        echo "✗ SMTP_PORT not defined\n";
    }
    
    if (defined('SMTP_USERNAME')) {
        echo "SMTP_USERNAME: " . SMTP_USERNAME . "\n";
    } else {
        echo "✗ SMTP_USERNAME not defined\n";
    }
    
    if (defined('SMTP_PASSWORD')) {
        echo "SMTP_PASSWORD: " . (SMTP_PASSWORD ? '***SET***' : 'NOT SET') . "\n";
    } else {
        echo "✗ SMTP_PASSWORD not defined\n";
    }
    
    if (defined('SMTP_FROM_EMAIL')) {
        echo "SMTP_FROM_EMAIL: " . SMTP_FROM_EMAIL . "\n";
    } else {
        echo "✗ SMTP_FROM_EMAIL not defined\n";
    }
    
    if (defined('SMTP_FROM_NAME')) {
        echo "SMTP_FROM_NAME: " . SMTP_FROM_NAME . "\n";
    } else {
        echo "✗ SMTP_FROM_NAME not defined\n";
    }
} else {
    echo "✗ config.php NOT FOUND\n";
}

// 5. Test basic email sending
echo "\n=== EMAIL SEND TEST ===\n";
echo "Enter test email in browser: ?test_email=your@email.com\n";

if (isset($_GET['test_email']) && filter_var($_GET['test_email'], FILTER_VALIDATE_EMAIL)) {
    $testEmail = $_GET['test_email'];
    echo "\nAttempting to send test email to: $testEmail\n\n";
    
    try {
        require_once __DIR__ . '/classes/EmailService.php';
        require_once __DIR__ . '/classes/Database.php';
        
        $database = new Database();
        $pdo = $database->connect();
        $emailService = new EmailService($pdo);
        
        $mail = $emailService->createMailer();
        $mail->addAddress($testEmail);
        $mail->Subject = 'Test Email from HandyCRM';
        $mail->Body = 'This is a test email sent at ' . date('Y-m-d H:i:s');
        
        if ($mail->send()) {
            echo "✓✓✓ EMAIL SENT SUCCESSFULLY! ✓✓✓\n";
            echo "Check inbox of: $testEmail\n";
        } else {
            echo "✗✗✗ EMAIL FAILED TO SEND ✗✗✗\n";
            echo "Error: " . $mail->ErrorInfo . "\n";
        }
        
    } catch (Exception $e) {
        echo "✗✗✗ EXCEPTION OCCURRED ✗✗✗\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    }
}

// 6. Check TCPDF (for PDF generation)
echo "\n=== TCPDF CHECK ===\n";
$tcpdfPath = __DIR__ . '/lib/tcpdf/tcpdf.php';
if (file_exists($tcpdfPath)) {
    echo "✓ TCPDF EXISTS at: $tcpdfPath\n";
} else {
    echo "✗ TCPDF NOT FOUND at: $tcpdfPath\n";
}

// 7. Check temp directory permissions
echo "\n=== TEMP DIRECTORY CHECK ===\n";
$tempDir = sys_get_temp_dir();
echo "Temp directory: $tempDir\n";
if (is_writable($tempDir)) {
    echo "✓ Temp directory is WRITABLE\n";
} else {
    echo "✗ Temp directory is NOT WRITABLE\n";
}

echo "\n=== END DEBUG ===\n";
echo "</pre>";

echo "<hr>";
echo "<h3>Test Email Form</h3>";
echo "<form method='GET'>";
echo "Email: <input type='email' name='test_email' required>";
echo " <button type='submit'>Send Test Email</button>";
echo "</form>";
?>
