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
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['value'] : $default;
    }
    
    /**
     * Generate PDF using TCPDF
     */
    private function generateQuotePDF($quote, $companyName, $companyAddress, $companyPhone, $companyEmail, $companyVat, $companyLogo) {
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
        
        // Logo (center top)
        $logoPath = '';
        if (!empty($companyLogo)) {
            $logoPath = __DIR__ . '/../' . $companyLogo;
            if (file_exists($logoPath)) {
                // Calculate center position
                $logoWidth = 40;
                $pageWidth = $pdf->getPageWidth();
                $logoX = ($pageWidth - $logoWidth) / 2;
                $pdf->Image($logoPath, $logoX, 15, $logoWidth, 0, '', '', '', false, 300, '', false, false, 0);
                $pdf->Ln(25);
            }
        }
        
        // Header with customer and company info
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
        
        // Quote title and details
        $html = '<h1 style="color: #e74c3c; text-align: center; margin: 20px 0;">ΠΡΟΣΦΟΡΑ #' . htmlspecialchars($quote['quote_number']) . '</h1>';
        
        $html .= '<table cellpadding="3" style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td style="width: 33%;"><strong>Ημερομηνία:</strong> ' . date('d/m/Y', strtotime($quote['created_at'])) . '</td>
                <td style="width: 33%;"><strong>Ισχύει μέχρι:</strong> ' . date('d/m/Y', strtotime($quote['valid_until'])) . '</td>
                <td style="width: 34%;"><strong>Κατάσταση:</strong> ' . $this->getStatusLabel($quote['status']) . '</td>
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
        
        // Output PDF
        $filename = 'Προσφορα_' . $quote['quote_number'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'I'); // 'I' = inline display, 'D' = download
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
