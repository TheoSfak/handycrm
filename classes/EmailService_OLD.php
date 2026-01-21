<?php
/**
 * EmailService - Enhanced email functionality for HandyCRM with PHPMailer
 * Handles SMTP configuration, template processing, and email sending
 */

require_once __DIR__ . '/../vendor/autoload.php';
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
            // Create database connection if not provided
            try {
                $this->pdo = new PDO('mysql:host=localhost;dbname=handycrm;charset=utf8mb4', 'handycrm_user', 'handycrm123');
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
            $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM smtp_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $this->smtp_settings = [
                'smtp_host' => $settings['smtp_host'] ?? '',
                'smtp_port' => $settings['smtp_port'] ?? 587,
                'smtp_username' => $settings['smtp_username'] ?? '',
                'smtp_password' => $settings['smtp_password'] ?? '',
                'smtp_encryption' => $settings['smtp_encryption'] ?? 'tls',
                'from_email' => $settings['from_email'] ?? '',
                'from_name' => $settings['from_name'] ?? 'HandyCRM',
                'reply_to' => $settings['reply_to'] ?? ''
            ];
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
               !empty($this->smtp_settings['smtp_password']) && 
               !empty($this->smtp_settings['from_email']);
    }
    
    /**
     * Create and configure PHPMailer instance
     */
    private function createMailer() {
        if (!$this->isConfigured()) {
            throw new Exception('SMTP configuration is incomplete. Please configure email settings first.');
        }
        
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_settings['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_settings['smtp_username'];
            $mail->Password = $this->smtp_settings['smtp_password'];
            $mail->SMTPSecure = $this->smtp_settings['smtp_encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)$this->smtp_settings['smtp_port'];
            $mail->CharSet = 'UTF-8';
            
            // Default sender
            $mail->setFrom($this->smtp_settings['from_email'], $this->smtp_settings['from_name']);
            
            // Reply-To if configured
            if (!empty($this->smtp_settings['reply_to'])) {
                $mail->addReplyTo($this->smtp_settings['reply_to']);
            }
            
            return $mail;
        } catch (Exception $e) {
            throw new Exception("Failed to configure mailer: " . $e->getMessage());
        }
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
        return "
        <html>
        <body style='font-family: Arial, sans-serif; margin: 20px;'>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 4px solid #007bff;'>
                <h2 style='color: #007bff; margin-top: 0;'>✅ SMTP Configuration Test</h2>
                <p><strong>Congratulations!</strong> Your SMTP email configuration is working correctly.</p>
                
                <div style='background: white; padding: 15px; margin: 15px 0; border-radius: 3px;'>
                    <h4>Test Details:</h4>
                    <ul style='margin: 10px 0;'>
                        <li><strong>Sent:</strong> " . date('d/m/Y H:i:s') . "</li>
                        <li><strong>SMTP Host:</strong> " . htmlspecialchars($this->smtp_settings['smtp_host']) . "</li>
                        <li><strong>SMTP Port:</strong> " . htmlspecialchars($this->smtp_settings['smtp_port']) . "</li>
                        <li><strong>Encryption:</strong> " . strtoupper($this->smtp_settings['smtp_encryption']) . "</li>
                        <li><strong>From:</strong> " . htmlspecialchars($this->smtp_settings['from_email']) . "</li>
                    </ul>
                </div>
                
                <p style='color: #28a745;'><strong>Your email system is ready to send maintenance reminders!</strong></p>
                
                <hr style='margin: 20px 0;'>
                <p style='font-size: 12px; color: #6c757d;'>
                    This is an automated test email from HandyCRM Email System.<br>
                    If you received this email, your SMTP configuration is working properly.
                </p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Send maintenance reminder email
     */
    public function sendMaintenanceReminder($customerEmail, $customerName, $maintenanceDate, $maintenanceDetails = []) {
        try {
            $mail = $this->createMailer();
            
            // Get maintenance reminder template
            $template = $this->getEmailTemplate('maintenance_reminder');
            if (!$template) {
                throw new Exception('Maintenance reminder email template not found');
            }
            
            // Process template variables
            $variables = [
                '{customer_name}' => $customerName,
                '{maintenance_date}' => date('d/m/Y', strtotime($maintenanceDate)),
                '{maintenance_time}' => $maintenanceDetails['time'] ?? '10:00',
                '{equipment_type}' => $maintenanceDetails['equipment_type'] ?? 'Μετασχηματιστής',
                '{location}' => $maintenanceDetails['location'] ?? '',
                '{contact_phone}' => $this->smtp_settings['reply_to'] ?? '210-1234567',
                '{company_name}' => $this->smtp_settings['from_name'] ?? 'HandyCRM',
                '{current_date}' => date('d/m/Y'),
                '{year}' => date('Y')
            ];
            
            $subject = str_replace(array_keys($variables), array_values($variables), $template['subject']);
            $body = str_replace(array_keys($variables), array_values($variables), $template['body']);
            
            // Recipients
            $mail->addAddress($customerEmail, $customerName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br(htmlspecialchars($body));
            
            $result = $mail->send();
            
            // Log the email
            $this->logEmail($customerEmail, $subject, 'Maintenance Reminder', 'sent');
            
            return ['success' => true, 'message' => 'Maintenance reminder sent successfully'];
        } catch (Exception $e) {
            // Log the error
            $this->logEmail($customerEmail, 'Maintenance Reminder', 'Maintenance Reminder', 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get email template from database
     */
    private function getEmailTemplate($type) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM email_templates WHERE template_type = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$type]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("EmailService_OLD::getEmailTemplate - Error fetching template '{$type}': " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Log email activity to database
     */
    private function logEmail($recipient, $subject, $emailType, $status, $errorMessage = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO email_notifications (recipient, subject, email_type, status, error_message, created_at) 
                VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([$recipient, $subject, $emailType, $status, $errorMessage]);
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
    
    /**
     * Get upcoming maintenances that need email reminders
     */
    public function getUpcomingMaintenances($days_ahead = 7) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    tm.*,
                    c.email,
                    COALESCE(c.company_name, CONCAT(c.first_name, ' ', c.last_name)) as customer_display_name
                FROM transformer_maintenances tm
                LEFT JOIN customers c ON (
                    c.company_name = tm.customer_name OR 
                    CONCAT(c.first_name, ' ', c.last_name) = tm.customer_name
                )
                WHERE tm.next_maintenance_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND c.email IS NOT NULL 
                AND c.email != ''
                AND tm.next_maintenance_date >= CURDATE()
                ORDER BY tm.next_maintenance_date ASC
            ");
            $stmt->execute([$days_ahead]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Failed to get upcoming maintenances: " . $e->getMessage());
        }
    }
    
    /**
     * Process maintenance reminders for upcoming maintenances
     */
    public function processMaintenanceReminders($days_ahead = 7) {
        $processed = 0;
        $errors = [];
        
        try {
            $maintenances = $this->getUpcomingMaintenances($days_ahead);
            
            foreach ($maintenances as $maintenance) {
                // Check if we already sent a reminder for this maintenance
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as count 
                    FROM email_notifications 
                    WHERE recipient = ? 
                    AND email_type = 'Maintenance Reminder' 
                    AND DATE(created_at) = CURDATE()
                ");
                $stmt->execute([$maintenance['email']]);
                $already_sent = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
                
                if (!$already_sent) {
                    $result = $this->sendMaintenanceReminder(
                        $maintenance['email'],
                        $maintenance['customer_display_name'],
                        $maintenance['next_maintenance_date'],
                        [
                            'equipment_type' => $maintenance['equipment_type'] ?? 'Μετασχηματιστής',
                            'location' => $maintenance['location'] ?? ''
                        ]
                    );
                    
                    if ($result['success']) {
                        $processed++;
                    } else {
                        $errors[] = "Failed to send to {$maintenance['email']}: " . $result['error'];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("EmailService_OLD::sendMaintenanceReminders - Error: " . $e->getMessage());
            $errors[] = "Failed to process maintenance reminders: " . $e->getMessage();
        }
        
        return [
            'processed' => $processed,
            'errors' => $errors,
            'total_maintenances' => count($maintenances ?? [])
        ];
    }
    
    /**
     * Get email statistics
     */
    public function getEmailStats() {
        try {
            $stats = [];
            
            // Total emails sent today
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM email_notifications WHERE DATE(created_at) = CURDATE() AND status = 'sent'");
            $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Total emails sent this week
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM email_notifications WHERE WEEK(created_at) = WEEK(CURDATE()) AND status = 'sent'");
            $stats['this_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Failed emails today
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM email_notifications WHERE DATE(created_at) = CURDATE() AND status = 'failed'");
            $stats['failed_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("EmailService_OLD::getEmailStats - Error fetching stats: " . $e->getMessage());
            return ['today' => 0, 'this_week' => 0, 'failed_today' => 0];
        }
    }
    
    /**
     * Get notification history - compatible with admin interfaces
     */
    public function getNotificationHistory($page = 1, $perPage = 20, $type = null) {
        try {
            $offset = ($page - 1) * $perPage;
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($type) {
                $whereClause .= " AND email_type = ?";
                $params[] = $type;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT *, 
                       CASE 
                           WHEN status = 'sent' THEN 'success'
                           WHEN status = 'failed' THEN 'danger' 
                           ELSE 'warning'
                       END as status_class
                FROM email_notifications 
                {$whereClause}
                ORDER BY created_at DESC 
                LIMIT {$offset}, {$perPage}
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("EmailService: Failed to get notification history - " . $e->getMessage());
            return [];
        }
    }
}
?>
            throw new Exception('Δεν βρέθηκε το template για test email');
        }
        
        $variables = [
            'current_time' => date('d/m/Y H:i:s'),
            'sender_name' => $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
        ];
        
        $subject = $this->replaceVariables($template['subject'], $variables);
        $bodyHtml = $this->replaceVariables($template['body_html'], $variables);
        $bodyText = $this->replaceVariables($template['body_text'], $variables);
        
        return $this->sendEmail($toEmail, $toName, $subject, $bodyHtml, $bodyText, 'test_email');
    }
    
    /**
     * Send maintenance reminder email
     */
    public function sendMaintenanceReminder($maintenance, $daysUntil) {
        if (!$this->isConfigured()) {
            return false;
        }
        
        $template = $this->getEmailTemplate('maintenance_reminder');
        if (!$template) {
            return false;
        }
        
        // Prepare variables for template
        $variables = [
            'customer_name' => $maintenance['customer_name'],
            'maintenance_date' => date('d/m/Y', strtotime($maintenance['next_maintenance_date'])),
            'days_until' => $daysUntil,
            'transformer_power' => $maintenance['transformer_power'],
            'address' => $maintenance['address'] ?? 'Δεν έχει οριστεί',
            'technician_name' => $maintenance['technician_name'] ?? 'Θα οριστεί',
            'company_phone' => defined('COMPANY_PHONE') ? COMPANY_PHONE : '210-1234567',
            'company_name' => defined('APP_NAME') ? APP_NAME : 'HandyCRM'
        ];
        
        $subject = $this->replaceVariables($template['subject'], $variables);
        $bodyHtml = $this->replaceVariables($template['body_html'], $variables);
        $bodyText = $this->replaceVariables($template['body_text'], $variables);
        
        // Use customer email if available, otherwise try to get it from customer record
        $customerEmail = $this->getCustomerEmail($maintenance['customer_name']);
        if (!$customerEmail) {
            $this->logNotification('maintenance_reminder', '', $maintenance['customer_name'], 
                                  $subject, 'Δεν βρέθηκε email για τον πελάτη', 'failed', 
                                  'Δεν υπάρχει email για τον πελάτη', $maintenance['id'], 'maintenance');
            return false;
        }
        
        return $this->sendEmail($customerEmail, $maintenance['customer_name'], $subject, $bodyHtml, $bodyText, 
                               'maintenance_reminder', $maintenance['id'], 'maintenance');
    }
    
    /**
     * Core email sending function using PHP mail() - will upgrade to PHPMailer later
     */
    private function sendEmail($toEmail, $toName, $subject, $bodyHtml, $bodyText = '', $type = 'general', $relatedId = null, $relatedType = null) {
        try {
            // For now, use simple mail() function
            // TODO: Implement PHPMailer for better SMTP support
            
            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=utf-8';
            $headers[] = 'From: ' . $this->smtp_from_name . ' <' . $this->smtp_from_email . '>';
            $headers[] = 'Reply-To: ' . $this->smtp_from_email;
            $headers[] = 'X-Mailer: HandyCRM Email System';
            
            $headerString = implode("\r\n", $headers);
            
            // Log notification before sending
            $notificationId = $this->logNotification($type, $toEmail, $toName, $subject, $bodyHtml, 'pending', null, $relatedId, $relatedType);
            
            // Attempt to send email
            $result = mail($toEmail, $subject, $bodyHtml, $headerString);
            
            if ($result) {
                // Update notification as sent
                $this->updateNotificationStatus($notificationId, 'sent');
                return true;
            } else {
                // Update notification as failed
                $this->updateNotificationStatus($notificationId, 'failed', 'mail() function returned false');
                return false;
            }
            
        } catch (Exception $e) {
            // Update notification as failed
            if (isset($notificationId)) {
                $this->updateNotificationStatus($notificationId, 'failed', $e->getMessage());
            }
            error_log("EmailService: Failed to send email - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get email template by type
     */
    private function getEmailTemplate($type) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM email_templates WHERE type = ? AND is_active = 1");
            $stmt->execute([$type]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("EmailService: Failed to get email template - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Replace variables in template content
     */
    private function replaceVariables($content, $variables) {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }
    
    /**
     * Get customer email - placeholder function for now
     */
    private function getCustomerEmail($customerName) {
        try {
            // Try to find customer email in customers table
            $stmt = $this->db->prepare("SELECT email FROM customers WHERE name = ? LIMIT 1");
            $stmt->execute([$customerName]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer && !empty($customer['email'])) {
                return $customer['email'];
            }
            
            // If not found, return null - will need manual input later
            return null;
            
        } catch (Exception $e) {
            error_log("EmailService: Failed to get customer email - " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Log notification to database
     */
    private function logNotification($type, $email, $name, $subject, $content, $status = 'pending', $errorMessage = null, $relatedId = null, $relatedType = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO email_notifications (type, recipient_email, recipient_name, subject, content, status, error_message, related_id, related_type, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $type, 
                $email, 
                $name, 
                $subject, 
                $content, 
                $status, 
                $errorMessage, 
                $relatedId, 
                $relatedType,
                $_SESSION['user_id'] ?? null
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("EmailService: Failed to log notification - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update notification status
     */
    private function updateNotificationStatus($notificationId, $status, $errorMessage = null) {
        try {
            $sentAt = ($status === 'sent') ? date('Y-m-d H:i:s') : null;
            
            $stmt = $this->db->prepare("
                UPDATE email_notifications 
                SET status = ?, error_message = ?, sent_at = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            $stmt->execute([$status, $errorMessage, $sentAt, $notificationId]);
            
        } catch (Exception $e) {
            error_log("EmailService: Failed to update notification status - " . $e->getMessage());
        }
    }
    
    /**
     * Get notification history
     */
    public function getNotificationHistory($page = 1, $perPage = 20, $type = null) {
        try {
            $offset = ($page - 1) * $perPage;
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($type) {
                $whereClause .= " AND type = ?";
                $params[] = $type;
            }
            
            $stmt = $this->db->prepare("
                SELECT *, 
                       CASE 
                           WHEN status = 'sent' THEN 'success'
                           WHEN status = 'failed' THEN 'danger' 
                           ELSE 'warning'
                       END as status_class
                FROM email_notifications 
                {$whereClause}
                ORDER BY created_at DESC 
                LIMIT {$offset}, {$perPage}
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("EmailService: Failed to get notification history - " . $e->getMessage());
            return [];
        }
    }
}