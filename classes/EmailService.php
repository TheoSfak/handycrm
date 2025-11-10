<?php
/**
 * EmailService - Clean Enhanced email functionality for HandyCRM with PHPMailer
 * Handles SMTP configuration, template processing, and email sending
 */

// Load PHPMailer manually (no Composer)
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $pdo;
    private $smtp_settings;
    
    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            // Create database connection using Database class
            try {
                require_once __DIR__ . '/Database.php';
                $database = new Database();
                $this->pdo = $database->connect();
            } catch (Exception $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        $this->loadSMTPSettings();
    }
    
    /**
     * Load SMTP settings from smtp_settings table
     */
    private function loadSMTPSettings() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM smtp_settings LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $this->smtp_settings = [
                    'smtp_host' => $row['host'] ?? '',
                    'smtp_port' => $row['port'] ?? 587,
                    'smtp_username' => $row['username'] ?? '',
                    'smtp_password' => $row['password'] ?? '',
                    'smtp_encryption' => $row['encryption'] ?? 'tls',
                    'from_email' => $row['from_email'] ?? '',
                    'from_name' => $row['from_name'] ?? 'HandyCRM',
                    'reply_to' => $row['from_email'] ?? ''
                ];
            } else {
                $this->smtp_settings = [];
            }
        } catch (Exception $e) {
            throw new Exception("Failed to load SMTP settings: " . $e->getMessage());
        }
    }
    
    /**
     * Check if SMTP is properly configured
     */
    public function isConfigured() {
        return !empty($this->smtp_settings['smtp_host']) && 
               !empty($this->smtp_settings['smtp_username']) && 
               !empty($this->smtp_settings['from_email']);
    }
    
    /**
     * Create PHPMailer instance with SMTP configuration
     */
    public function createMailer() {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $this->smtp_settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->smtp_settings['smtp_username'];
        $mail->Password = $this->smtp_settings['smtp_password'];
        $mail->SMTPSecure = $this->smtp_settings['smtp_encryption'];
        $mail->Port = $this->smtp_settings['smtp_port'];
        $mail->CharSet = 'UTF-8';
        
        // From
        $mail->setFrom($this->smtp_settings['from_email'], $this->smtp_settings['from_name']);
        
        // Reply to
        if (!empty($this->smtp_settings['reply_to'])) {
            $mail->addReplyTo($this->smtp_settings['reply_to']);
        }
        
        return $mail;
    }
    
    /**
     * Send a test email to verify SMTP configuration
     */
    public function sendTestEmail($toEmail) {
        try {
            $mail = $this->createMailer();
            
            // Recipients
            $mail->addAddress($toEmail);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'HandyCRM - SMTP Test Email (' . date('Y-m-d H:i:s') . ')';
            $mail->Body = $this->generateTestEmailBody();
            
            $result = $mail->send();
            
            // Log the email
            $this->logEmail($toEmail, $mail->Subject, 'Test Email', 'sent');
            
            return ['success' => true, 'message' => 'Test email sent successfully'];
        } catch (Exception $e) {
            // Log the error
            $this->logEmail($toEmail, 'Test Email', 'Test Email', 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate test email body
     */
    private function generateTestEmailBody() {
        return '
        <html>
        <head>
            <meta charset="UTF-8">
        </head>
        <body style="font-family: Arial, sans-serif; margin: 20px;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                <h2 style="color: #28a745;">✅ SMTP Test Email Successful!</h2>
                
                <p><strong>Congratulations!</strong> Your HandyCRM email configuration is working correctly.</p>
                
                <div style="background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff;">
                    <h4>📧 Configuration Details:</h4>
                    <ul>
                        <li><strong>SMTP Host:</strong> ' . htmlspecialchars($this->smtp_settings['smtp_host']) . '</li>
                        <li><strong>Port:</strong> ' . htmlspecialchars($this->smtp_settings['smtp_port']) . '</li>
                        <li><strong>Encryption:</strong> ' . strtoupper($this->smtp_settings['smtp_encryption']) . '</li>
                        <li><strong>From Email:</strong> ' . htmlspecialchars($this->smtp_settings['from_email']) . '</li>
                        <li><strong>Sent At:</strong> ' . date('d/m/Y H:i:s') . '</li>
                    </ul>
                </div>
                
                <div style="background: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 5px;">
                    <h4>🚀 Next Steps:</h4>
                    <p>Your email system is now ready! You can:</p>
                    <ul>
                        <li>🔧 Manage customer emails</li>
                        <li>📋 Import/export email lists</li>
                        <li>📝 Create email templates</li>
                        <li>⚡ Set up automatic maintenance reminders</li>
                    </ul>
                </div>
                
                <hr style="margin: 20px 0;">
                <p style="color: #6c757d; font-size: 12px;">
                    This is an automated test email from HandyCRM Email System<br>
                    Generated at: ' . date('Y-m-d H:i:s') . '
                </p>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Log email to database
     */
    public function logEmail($toEmail, $subject, $content, $status, $errorMessage = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO email_notifications (type, recipient_email, subject, body, status, error_message, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute(['test_email', $toEmail, $subject, $content, $status, $errorMessage]);
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
    
    /**
     * Get SMTP settings for display
     */
    public function getSettings() {
        return $this->smtp_settings;
    }
}