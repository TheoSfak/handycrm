<?php
/**
 * Quote Export Controller
 * Handles PDF export for quotes
 */

class QuoteExportController extends BaseController {
    
    /**
     * Generate PDF for a quote
     */
    public function generatePDF() {
        // Get quote ID
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            $_SESSION['error'] = 'Μη έγκυρο αναγνωριστικό προσφοράς';
            $this->redirect('/quotes');
        }
        
        // Get quote details
        $quoteModel = new Quote();
        $quote = $quoteModel->getWithDetails($id);
        
        if (!$quote) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε';
            $this->redirect('/quotes');
        }
        
        // Get company settings
        $companyName = $this->getSetting('company_name', 'HandyCRM');
        $companyAddress = $this->getSetting('company_address', '');
        $companyPhone = $this->getSetting('company_phone', '');
        $companyEmail = $this->getSetting('company_email', '');
        $companyVat = $this->getSetting('company_vat', '');
        $companyLogo = $this->getSetting('company_logo', '');
        
        // Generate PDF
        $this->generateQuotePDF($quote, $companyName, $companyAddress, $companyPhone, $companyEmail, $companyVat, $companyLogo);
    }
    
    /**
     * Get setting from database
     */
    private function getSetting($key, $default = '') {
        $database = new Database();
        $db = $database->connect();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : $default;
    }
    
    /**
     * Generate PDF using TCPDF
     * $outputMode: 'I' = inline browser, 'D' = download, 'F' = save to file (pass $outputFile), 'S' = return string
     */
    private function generateQuotePDF($quote, $companyName, $companyAddress, $companyPhone, $companyEmail, $companyVat, $companyLogo, $outputMode = 'I', $outputFile = '') {
        // Include TCPDF library
        require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($companyName);
        $pdf->SetTitle('Προσφορά #' . $quote['quote_number']);
        $pdf->SetSubject('Προσφορά');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Set font
        $pdf->SetFont('dejavusans', '', 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Logo (center top) — large
        $logoPath = '';
        if (!empty($companyLogo)) {
            $logoPath = __DIR__ . '/../' . $companyLogo;
            if (file_exists($logoPath)) {
                $logoWidth = 80;
                $pageWidth = $pdf->getPageWidth();
                $logoX = ($pageWidth - $logoWidth) / 2;
                $pdf->Image($logoPath, $logoX, 15, $logoWidth, 0, '', '', '', false, 300, '', false, false, 0);
                $pdf->Ln(45);
            }
        }

        // Quote title and details (now BELOW logo, ABOVE customer/company info)
        $html = '<h1 style="color: #e74c3c; text-align: center; margin: 10px 0 8px 0;">ΠΡΟΣΦΟΡΑ #' . htmlspecialchars($quote['quote_number']) . '</h1>';
        $html .= '<table cellpadding="3" style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td style="width: 33%;"><strong>Ημερομηνία:</strong> ' . date('d/m/Y', strtotime($quote['created_at'])) . '</td>
                <td style="width: 33%;"><strong>Ισχύει μέχρι:</strong> ' . date('d/m/Y', strtotime($quote['valid_until'])) . '</td>
                <td style="width: 34%;"><strong>Κατάσταση:</strong> ' . $this->getStatusLabel($quote['status']) . '</td>
            </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(5);

        // Header with customer and company info (now below the quote title box)
        $html = '<table cellpadding="5" style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <h3 style="color: #2c3e50; margin-bottom: 10px;">ΣΤΟΙΧΕΙΑ ΠΕΛΑΤΗ</h3>';
        
        // Customer name
        $customerName = $quote['customer_type'] === 'company' && !empty($quote['customer_company_name']) 
            ? $quote['customer_company_name'] 
            : $quote['customer_first_name'] . ' ' . $quote['customer_last_name'];
        
        $html .= '<p style="margin: 0; font-size: 11px;"><strong>' . htmlspecialchars($customerName) . '</strong></p>';
        
        if (!empty($quote['customer_address'])) {
            $html .= '<p style="margin: 2px 0; font-size: 10px;">' . htmlspecialchars($quote['customer_address']) . '</p>';
        }
        if (!empty($quote['customer_phone'])) {
            $html .= '<p style="margin: 2px 0; font-size: 10px;">Τηλ: ' . htmlspecialchars($quote['customer_phone']) . '</p>';
        }
        if (!empty($quote['customer_email'])) {
            $html .= '<p style="margin: 2px 0; font-size: 10px;">Email: ' . htmlspecialchars($quote['customer_email']) . '</p>';
        }
        if (!empty($quote['customer_vat'])) {
            $html .= '<p style="margin: 2px 0; font-size: 10px;">ΑΦΜ: ' . htmlspecialchars($quote['customer_vat']) . '</p>';
        }
        
        $html .= '</td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <h3 style="color: #2c3e50; margin-bottom: 10px;">ΣΤΟΙΧΕΙΑ ΕΤΑΙΡΙΑΣ</h3>
                    <p style="margin: 0; font-size: 11px;"><strong>' . htmlspecialchars($companyName) . '</strong></p>';
        
        if (!empty($companyAddress)) {
            $html .= '<p style="margin: 2px 0; font-size: 10px;">' . htmlspecialchars($companyAddress) . '</p>';
        }
        if (!empty($companyPhone)) {
            $html .= '<p style="margin: 2px 0; font-size: 10px;">Τηλ: ' . htmlspecialchars($companyPhone) . '</p>';
        }
        if (!empty($companyEmail)) {
            $html .= '<p style="margin: 2px 0; font-size: 10px;">Email: ' . htmlspecialchars($companyEmail) . '</p>';
        }
        if (!empty($companyVat)) {
            $html .= '<p style="margin: 2px 0; font-size: 10px;">ΑΦΜ: ' . htmlspecialchars($companyVat) . '</p>';
        }
        
        $html .= '</td>
            </tr>
        </table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln(5);
        
        // Quote title and description
        if (!empty($quote['title'])) {
            $html = '<h3 style="color: #2c3e50; margin-bottom: 5px;">' . htmlspecialchars($quote['title']) . '</h3>';
            $pdf->writeHTML($html, true, false, true, false, '');
        }
        
        if (!empty($quote['description'])) {
            $html = '<p style="margin-bottom: 15px; font-size: 10px;">' . nl2br(htmlspecialchars($quote['description'])) . '</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
        }
        
        // Items table
        $html = '<table border="1" cellpadding="5" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #3498db; color: white;">
                    <th style="width: 50%; text-align: left;">Περιγραφή</th>
                    <th style="width: 15%; text-align: center;">Ποσότητα</th>
                    <th style="width: 17.5%; text-align: right;">Τιμή μονάδας</th>
                    <th style="width: 17.5%; text-align: right;">Σύνολο</th>
                </tr>
            </thead>
            <tbody>';
        
        $subtotal = 0;
        foreach ($quote['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
            
            $html .= '<tr>
                <td>' . htmlspecialchars($item['description']) . '</td>
                <td style="text-align: center;">' . $item['quantity'] . '</td>
                <td style="text-align: right;">' . number_format($item['unit_price'], 2) . '€</td>
                <td style="text-align: right;">' . number_format($itemTotal, 2) . '€</td>
            </tr>';
        }
        
        // Discount if any
        $discount = $quote['discount_amount'] ?? 0;
        
        $html .= '</tbody>
            <tfoot>';
        
        // Subtotal
        $html .= '<tr>
            <td colspan="3" style="text-align: right;"><strong>Υποσύνολο:</strong></td>
            <td style="text-align: right;"><strong>' . number_format($subtotal, 2) . '€</strong></td>
        </tr>';
        
        // Discount
        if ($discount > 0) {
            $html .= '<tr>
                <td colspan="3" style="text-align: right;">Έκπτωση:</td>
                <td style="text-align: right;">-' . number_format($discount, 2) . '€</td>
            </tr>';
        }
        
        // Total
        $html .= '<tr style="background-color: #ecf0f1;">
            <td colspan="3" style="text-align: right; font-size: 12px;"><strong>ΣΥΝΟΛΟ:</strong></td>
            <td style="text-align: right; font-size: 12px;"><strong>' . number_format($quote['total_amount'], 2) . '€</strong></td>
        </tr>';
        
        $html .= '</tfoot>
        </table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Notes if any
        if (!empty($quote['notes'])) {
            $pdf->Ln(10);
            $html = '<h4 style="color: #2c3e50;">Σημειώσεις:</h4>
                <p style="font-size: 9px;">' . nl2br(htmlspecialchars($quote['notes'])) . '</p>';
            $pdf->writeHTML($html, true, false, true, false, '');
        }

        // Footer — company info bar at the bottom of the last page
        $pageHeight  = $pdf->getPageHeight();
        $marginBottom = 15;
        $footerH     = 14;
        $footerY     = $pageHeight - $marginBottom - $footerH;

        $pdf->SetY($footerY);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(15, $footerY, $pdf->getPageWidth() - 15, $footerY);

        $footerParts = [];
        if (!empty($companyName))    $footerParts[] = $companyName;
        if (!empty($companyAddress)) $footerParts[] = $companyAddress;
        if (!empty($companyPhone))   $footerParts[] = 'Τηλ: ' . $companyPhone;
        if (!empty($companyEmail))   $footerParts[] = $companyEmail;
        if (!empty($companyVat))     $footerParts[] = 'ΑΦΜ: ' . $companyVat;
        $footerText = implode('  |  ', $footerParts);

        $pdf->SetFont('dejavusans', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(15, $footerY + 3);
        $pdf->Cell($pdf->getPageWidth() - 30, 8, $footerText, 0, 0, 'C');

        // Output PDF
        $filename = 'Προσφορα_' . $quote['quote_number'] . '_' . date('Y-m-d') . '.pdf';
        if ($outputMode === 'F' && $outputFile) {
            $pdf->Output($outputFile, 'F');
        } else {
            $pdf->Output($filename, $outputMode);
        }
    }
    
    /**
     * Send quote PDF by email
     */
    public function sendByEmail() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Μη έγκυρη μέθοδος']);
            exit;
        }

        $id            = (int)($_POST['id'] ?? 0);
        $toEmail       = trim($_POST['to_email'] ?? '');
        $customMessage = trim($_POST['custom_message'] ?? '');

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Μη έγκυρη προσφορά']);
            exit;
        }
        if (!$toEmail || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Παρακαλώ εισάγετε έγκυρη διεύθυνση email']);
            exit;
        }

        $quoteModel = new Quote();
        $quote = $quoteModel->getWithDetails($id);
        if (!$quote) {
            echo json_encode(['success' => false, 'error' => 'Η προσφορά δεν βρέθηκε']);
            exit;
        }

        $companyName    = $this->getSetting('company_name', 'HandyCRM');
        $companyAddress = $this->getSetting('company_address', '');
        $companyPhone   = $this->getSetting('company_phone', '');
        $companyEmail   = $this->getSetting('company_email', '');
        $companyVat     = $this->getSetting('company_vat', '');
        $companyLogo    = $this->getSetting('company_logo', '');

        // Generate PDF to temp file
        $safeNumber = preg_replace('/[^a-zA-Z0-9_-]/', '_', $quote['quote_number']);
        $tmpFile = sys_get_temp_dir() . '/quote_' . $safeNumber . '_' . uniqid() . '.pdf';
        $this->generateQuotePDF($quote, $companyName, $companyAddress, $companyPhone, $companyEmail, $companyVat, $companyLogo, 'F', $tmpFile);

        if (!file_exists($tmpFile)) {
            echo json_encode(['success' => false, 'error' => 'Αποτυχία δημιουργίας PDF']);
            exit;
        }

        try {
            require_once __DIR__ . '/../classes/EmailService.php';
            $emailService = new EmailService();

            if (!$emailService->isConfigured()) {
                @unlink($tmpFile);
                echo json_encode(['success' => false, 'error' => 'Το SMTP δεν έχει ρυθμιστεί. Παρακαλώ ελέγξτε τις ρυθμίσεις email.']);
                exit;
            }

            $mail = $emailService->createMailer();
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Προσφορά #' . $quote['quote_number'] . ' — ' . $companyName;
            $mail->Body    = $this->buildEmailBody($quote, $companyName, $companyPhone, $companyEmail, $customMessage);
            $mail->addAttachment($tmpFile, 'Προσφορά_' . $quote['quote_number'] . '.pdf');
            $mail->send();

            @unlink($tmpFile);
            echo json_encode(['success' => true, 'message' => 'Η προσφορά εστάλη με επιτυχία στο ' . $toEmail]);
        } catch (\Exception $e) {
            @unlink($tmpFile);
            echo json_encode(['success' => false, 'error' => 'Σφάλμα αποστολής: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Build HTML email body for quote
     */
    private function buildEmailBody($quote, $companyName, $companyPhone, $companyEmail, $customMessage = '') {
        $customerName = trim(($quote['customer_first_name'] ?? '') . ' ' . ($quote['customer_last_name'] ?? ''));
        if (($quote['customer_type'] ?? '') === 'company' && !empty($quote['customer_company_name'])) {
            $customerName = $quote['customer_company_name'];
        }
        if (empty($customerName)) {
            $customerName = 'Πελάτη';
        }

        $quoteTitle  = htmlspecialchars($quote['title'] ?? '');
        $quoteNumber = htmlspecialchars($quote['quote_number']);
        $validUntil  = date('d/m/Y', strtotime($quote['valid_until']));
        $totalAmount = number_format($quote['total_amount'], 2) . ' €';
        $companyH    = htmlspecialchars($companyName);

        $customHtml = '';
        if (!empty($customMessage)) {
            $customHtml = '<p style="margin:18px 0;font-size:14px;color:#444;line-height:1.7;background:#f0f4f8;border-left:4px solid #3498db;padding:12px 16px;border-radius:0 6px 6px 0;">'
                        . nl2br(htmlspecialchars($customMessage)) . '</p>';
        }

        $contactParts = [];
        if (!empty($companyPhone)) $contactParts[] = '📞 ' . htmlspecialchars($companyPhone);
        if (!empty($companyEmail)) $contactParts[] = '✉️ ' . htmlspecialchars($companyEmail);
        $contactLine = !empty($contactParts)
            ? '<p style="margin:4px 0;font-size:13px;color:#555;">' . implode(' &nbsp;|&nbsp; ', $contactParts) . '</p>'
            : '';

        return '<!DOCTYPE html>
<html lang="el">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.10);">

        <!-- Header -->
        <tr>
          <td style="background:#2c3e50;padding:28px 36px;">
            <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;">📄 Προσφορά #' . $quoteNumber . '</h1>
            <p style="margin:6px 0 0;color:#bdc3cb;font-size:14px;">' . $companyH . '</p>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:32px 36px;">
            <p style="margin:0 0 16px;font-size:15px;color:#333;">Αγαπητέ/ή <strong>' . htmlspecialchars($customerName) . '</strong>,</p>
            <p style="margin:0 0 16px;font-size:15px;color:#333;line-height:1.6;">
              Σας αποστέλλουμε τη συνημμένη προσφορά μας σχετικά με:<br>
              <strong style="color:#2c3e50;font-size:16px;">' . $quoteTitle . '</strong>
            </p>

            ' . $customHtml . '

            <!-- Quote summary -->
            <table width="100%" cellpadding="12" cellspacing="0" style="background:#f8f9fa;border-radius:6px;border:1px solid #e9ecef;margin:20px 0;">
              <tr>
                <td style="font-size:13px;color:#666;">Αριθμός Προσφοράς</td>
                <td style="font-size:13px;font-weight:700;color:#333;text-align:right;">#' . $quoteNumber . '</td>
              </tr>
              <tr>
                <td style="font-size:13px;color:#666;border-top:1px solid #e9ecef;">Ισχύει Έως</td>
                <td style="font-size:13px;font-weight:700;color:#e67e22;text-align:right;border-top:1px solid #e9ecef;">' . $validUntil . '</td>
              </tr>
              <tr>
                <td style="font-size:14px;color:#2c3e50;font-weight:700;border-top:1px solid #e9ecef;">Συνολικό Ποσό</td>
                <td style="font-size:16px;font-weight:700;color:#27ae60;text-align:right;border-top:1px solid #e9ecef;">' . $totalAmount . '</td>
              </tr>
            </table>

            <p style="margin:0 0 6px;font-size:14px;color:#555;line-height:1.7;">
              Το PDF της προσφοράς επισυνάπτεται στο παρόν email για την ευκολία σας.<br>
              Για οποιαδήποτε διευκρίνιση ή πρόσθετη πληροφορία, είμαστε πάντα στη διάθεσή σας.
            </p>

            <p style="margin:24px 0 0;font-size:15px;color:#333;">
              Με εκτίμηση,<br>
              <strong>' . $companyH . '</strong>
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f8f9fa;padding:18px 36px;border-top:1px solid #e9ecef;text-align:center;">
            ' . $contactLine . '
            <p style="margin:8px 0 0;font-size:11px;color:#aaa;">Αυτό το μήνυμα εστάλη αυτόματα από το σύστημα διαχείρισης ' . $companyH . '</p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
    }

    /**
     * Get status label in Greek
     */
    private function getStatusLabel($status) {
        $labels = [
            'draft' => 'Πρόχειρο',
            'sent' => 'Απεσταλμένο',
            'accepted' => 'Αποδεκτό',
            'rejected' => 'Απορριφθέν',
            'expired' => 'Ληγμένο'
        ];
        return $labels[$status] ?? $status;
    }
}
