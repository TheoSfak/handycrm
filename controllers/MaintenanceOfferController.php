<?php

require_once 'models/MaintenanceOffer.php';
require_once __DIR__ . '/../models/Settings.php';

// Load TCPDF only once
if (!class_exists('TCPDF')) {
    require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
}

/**
 * Custom TCPDF class for Maintenance Offer PDF
 */
if (!class_exists('CustomOfferPDF')) {
    class CustomOfferPDF extends TCPDF {
        public string $logoPath = '';
        public string $companyFooterHtml = '';
        public string $companyFooterCompactHtml = '';
        public static string $staticFooterHtml = '';
        public static string $staticFooterCompactHtml = '';

        public function Header() {
            if ($this->page > 1) {
                if ($this->logoPath && file_exists($this->logoPath)) {
                    $this->Image($this->logoPath, 20, 10, 52, 0, 'JPEG');
                }
                // "ΠΡΟΣΦΟΡΑ" box at top-right
                $this->SetXY(158, 12);
                $this->SetFont('dejavusans', 'B', 9);
                $this->SetTextColor(30, 30, 30);
                $this->SetDrawColor(80, 80, 80);
                $this->SetLineWidth(0.3);
                $this->Cell(32, 6.5, 'ΠΡΟΣΦΟΡΑ', 1, 0, 'C');
            }
        }

        public function Footer() {
            $this->SetY(-22);
            $this->SetFont('dejavusans', '', 8);
            $this->SetTextColor(50, 50, 50);

            $footerFull    = !empty($this->companyFooterHtml) ? $this->companyFooterHtml : self::$staticFooterHtml;
            $footerCompact = !empty($this->companyFooterCompactHtml) ? $this->companyFooterCompactHtml : (self::$staticFooterCompactHtml ?: $footerFull);

            if ($this->page === 1) {
                // Page 1 footer: centered company info from settings, no page number
                if (!empty($footerFull)) {
                    $this->writeHTMLCell(170, 0, 20, 274, $footerFull, 0, 0, false, true, 'C');
                }
            } else {
                if ($this->page === 3 || $this->page === 4) {
                    // Page 3 (ISO cert) and Page 4 (Tasks): Page number on right
                    $this->SetXY(150, 280);
                    $this->SetFont('dejavusans', 'B', 8.5);
                    $this->Cell(40, 5, 'Σελίδα: ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
                } else {
                    // Pages 2, 5, 6: Compact company info from settings on left/center + Page number on right
                    if (!empty($footerCompact)) {
                        $this->writeHTMLCell(135, 0, 20, 275, $footerCompact, 0, 0, false, true, 'C');
                    }
                    $this->SetXY(155, 280);
                    $this->SetFont('dejavusans', 'B', 8.5);
                    $this->Cell(35, 5, 'Σελίδα: ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
                }
            }
        }
    }
}

class MaintenanceOfferController extends BaseController {

    private MaintenanceOffer $offerModel;
    private const ITEMS_PER_PAGE = 20;

    public function getCompanySettings(): array {
        $keys = [
            'company_name'         => '',
            'company_display_name' => '',
            'company_address'      => '',
            'company_phone'        => '',
            'company_email'        => '',
            'company_tax_id'       => '',
            'company_website'      => '',
            'company_logo'         => ''
        ];

        $settings = [];
        foreach ($keys as $key => $defaultVal) {
            try {
                if (class_exists('Settings')) {
                    $val = Settings::get($key, $defaultVal);
                    $settings[$key] = ($val !== null && $val !== '') ? (string)$val : $defaultVal;
                } else {
                    $settings[$key] = $defaultVal;
                }
            } catch (\Throwable $e) {
                $settings[$key] = $defaultVal;
            }
        }
        return $settings;
    }

    public static function buildCompanyFooterHtml(array $settings, bool $compact = false): string {
        $name = trim($settings['company_name'] ?? '');
        if ($name === '') {
            $name = trim($settings['company_display_name'] ?? '');
        }

        $addr  = trim($settings['company_address'] ?? '');
        $taxId = trim($settings['company_tax_id'] ?? '');
        $phone = trim($settings['company_phone'] ?? '');

        $lines = [];

        // Line 1: Company Name
        if ($name !== '') {
            $lines[] = '<b>' . htmlspecialchars($name) . '</b>';
        }

        // Line 2: Address and/or Tax ID (ΑΦΜ)
        $line2Parts = [];
        if ($addr !== '') {
            $addrText = preg_match('/^(Έδρα|Εδρα|Διεύθυνση)/iu', $addr) ? $addr : ('Έδρα: ' . $addr);
            $line2Parts[] = htmlspecialchars($addrText);
        }
        if ($taxId !== '' && stripos($addr, $taxId) === false) {
            $taxText = preg_match('/^ΑΦΜ/iu', $taxId) ? $taxId : ('ΑΦΜ: ' . $taxId);
            $line2Parts[] = htmlspecialchars($taxText);
        }
        if (!empty($line2Parts)) {
            $lines[] = implode(' - ', $line2Parts);
        }

        // Line 3: Phone / Mobile / Contact
        $contactParts = [];
        if ($phone !== '') {
            $phoneText = preg_match('/^(Τηλ|Τηλέφωνο)/iu', $phone) ? $phone : ('Τηλέφωνο: ' . $phone);
            $contactParts[] = htmlspecialchars($phoneText);
        }
        if (empty($contactParts) && !empty($settings['company_email'])) {
            $contactParts[] = 'Email: ' . htmlspecialchars($settings['company_email']);
        }
        if (!empty($contactParts)) {
            $lines[] = implode(' - ', $contactParts);
        }

        if (empty($lines)) {
            return '';
        }

        $fontSize = $compact ? '7.5pt' : '8pt';
        $lineHeight = $compact ? '1.35' : '1.4';

        return '<div style="text-align:center; font-size:' . $fontSize . '; line-height:' . $lineHeight . '; color:#333333;">'
            . implode('<br/>', $lines)
            . '</div>';
    }

    private static function buildPdfFooterText(): string {
        try {
            $name    = Settings::get('company_name',    '');
            $website = Settings::get('company_website', '');
            $email   = Settings::get('company_email',   '');
            $phone   = Settings::get('company_phone',   '');
        } catch (\Exception $e) {
            $name = ''; $website = ''; $email = ''; $phone = '';
        }
        $parts = array_filter([$name, $website, $email, $phone]);
        return implode(' | ', $parts);
    }

    public function __construct() {
        parent::__construct();
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'Δεν έχετε πρόσβαση σε αυτή τη λειτουργία.';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        $this->offerModel = new MaintenanceOffer();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LIST
    // ──────────────────────────────────────────────────────────────────────────
    public function index(): void {
        $search   = trim($_GET['search'] ?? '');
        $accepted = $_GET['accepted'] ?? '';
        $page     = max(1, (int)($_GET['page'] ?? 1));

        $offers     = $this->offerModel->getAll($page, self::ITEMS_PER_PAGE, $search ?: null, $accepted !== '' ? $accepted : null);
        $totalCount = $this->offerModel->getTotalCount($search ?: null, $accepted !== '' ? $accepted : null);
        $totalPages = (int)ceil($totalCount / self::ITEMS_PER_PAGE);

        $this->view('maintenance_offers/index', [
            'title'       => 'Προσφορές Συντήρησης - ' . APP_NAME,
            'offers'      => $offers,
            'totalCount'  => $totalCount,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'search'      => $search,
            'accepted'    => $accepted,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CREATE FORM
    // ──────────────────────────────────────────────────────────────────────────
    public function create(): void {
        $this->view('maintenance_offers/create', [
            'title' => 'Νέα Προσφορά Συντήρησης - ' . APP_NAME,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────────────────
    public function store(): void {
        try {
            $this->validateCsrfToken();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Μη έγκυρο αίτημα.';
            header('Location: ' . BASE_URL . '/maintenance-offers/create');
            exit;
        }

        $count       = max(1, (int)($_POST['transformers_count'] ?? 1));
        $customPrice = isset($_POST['custom_price']) && $_POST['custom_price'] !== ''
            ? (float)$_POST['custom_price']
            : null;
        $price = $customPrice ?? MaintenanceOffer::calculatePrice($count);

        $data = [
            'offer_number'       => $this->offerModel->generateOfferNumber(),
            'company_name'       => trim($_POST['company_name'] ?? ''),
            'address'            => trim($_POST['address'] ?? ''),
            'phone'              => trim($_POST['phone'] ?? ''),
            'email'              => trim($_POST['email'] ?? ''),
            'transformers_count' => $count,
            'price'              => $price,
            'offer_expiry_date'  => $_POST['offer_expiry_date'] ?: null,
            'notes'              => trim($_POST['notes'] ?? ''),
            'sent_at'            => date('Y-m-d H:i:s'),
            'created_by'         => $_SESSION['user_id'],
        ];

        if (empty($data['company_name'])) {
            $_SESSION['error'] = 'Η επωνυμία επιχείρησης είναι υποχρεωτική.';
            header('Location: ' . BASE_URL . '/maintenance-offers/create');
            exit;
        }

        $id = $this->offerModel->create($data);
        $_SESSION['success'] = 'Η προσφορά ' . $data['offer_number'] . ' καταχωρήθηκε επιτυχώς.';
        header('Location: ' . BASE_URL . '/maintenance-offers');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SHOW (view details)
    // ──────────────────────────────────────────────────────────────────────────
    public function show(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        // Fetch created_by_name if not already joined
        if (empty($offer['created_by_name']) && !empty($offer['created_by'])) {
            try {
                $db = $this->db->connect();
                $stmt = $db->prepare("SELECT CONCAT(first_name, ' ', last_name) FROM users WHERE id = ?");
                $stmt->execute([$offer['created_by']]);
                $offer['created_by_name'] = $stmt->fetchColumn() ?: '';
            } catch (\Exception $e) {
                $offer['created_by_name'] = '';
            }
        }

        $this->view('maintenance_offers/show', [
            'title' => 'Προσφορά ' . $offer['offer_number'] . ' - ' . APP_NAME,
            'offer' => $offer,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EDIT FORM
    // ──────────────────────────────────────────────────────────────────────────
    public function edit(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        $this->view('maintenance_offers/edit', [
            'title' => 'Επεξεργασία Προσφοράς - ' . APP_NAME,
            'offer' => $offer,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────────────────
    public function update(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        try {
            $this->validateCsrfToken();
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Μη έγκυρο αίτημα.';
            header('Location: ' . BASE_URL . '/maintenance-offers/edit/' . $id);
            exit;
        }

        $count       = max(1, (int)($_POST['transformers_count'] ?? 1));
        $customPrice = isset($_POST['custom_price']) && $_POST['custom_price'] !== ''
            ? (float)$_POST['custom_price']
            : null;
        $price = $customPrice ?? MaintenanceOffer::calculatePrice($count);

        $data = [
            'company_name'       => trim($_POST['company_name'] ?? ''),
            'address'            => trim($_POST['address'] ?? ''),
            'phone'              => trim($_POST['phone'] ?? ''),
            'email'              => trim($_POST['email'] ?? ''),
            'transformers_count' => $count,
            'price'              => $price,
            'offer_expiry_date'  => $_POST['offer_expiry_date'] ?: null,
            'notes'              => trim($_POST['notes'] ?? ''),
        ];

        if (empty($data['company_name'])) {
            $_SESSION['error'] = 'Η επωνυμία επιχείρησης είναι υποχρεωτική.';
            header('Location: ' . BASE_URL . '/maintenance-offers/edit/' . $id);
            exit;
        }

        $this->offerModel->update($id, $data);
        $_SESSION['success'] = 'Η προσφορά ' . htmlspecialchars($offer['offer_number']) . ' ενημερώθηκε επιτυχώς.';
        header('Location: ' . BASE_URL . '/maintenance-offers');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TOGGLE ACCEPTED (AJAX / radio button click)
    // ──────────────────────────────────────────────────────────────────────────
    public function toggleAccepted(int $id): void {
        header('Content-Type: application/json');

        $offer = $this->offerModel->find($id);
        if (!$offer) {
            echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκε']);
            exit;
        }

        $input  = json_decode(file_get_contents('php://input'), true);
        $accept = isset($input['accepted']) ? (int)$input['accepted'] : 1;

        $updateData = ['accepted' => $accept];
        if ($accept) {
            $updateData['accepted_at'] = date('Y-m-d H:i:s');
        } else {
            $updateData['accepted_at'] = null;
        }

        $this->offerModel->update($id, $updateData);

        $offer = $this->offerModel->find($id);
        echo json_encode([
            'success'     => true,
            'accepted'    => (bool)$offer['accepted'],
            'accepted_at' => $offer['accepted_at']
                ? date('d/m/Y H:i', strtotime($offer['accepted_at']))
                : null,
        ]);
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SAVE SCHEDULED DATE (AJAX)
    // ──────────────────────────────────────────────────────────────────────────
    public function saveScheduledDate(int $id): void {
        header('Content-Type: application/json');

        $offer = $this->offerModel->find($id);
        if (!$offer) {
            echo json_encode(['success' => false, 'message' => 'Δεν βρέθηκε']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $date  = isset($input['scheduled_date']) ? trim($input['scheduled_date']) : null;

        // Validate and normalise date format
        if ($date !== null && $date !== '') {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $m)) {
                // Convert DD/MM/YYYY → YYYY-MM-DD
                $date = sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate(
                (int)substr($date, 5, 2),
                (int)substr($date, 8, 2),
                (int)substr($date, 0, 4)
            )) {
                echo json_encode(['success' => false, 'message' => 'Μη έγκυρη ημερομηνία (χρησιμοποιήστε το ημερολόγιο)']);
                exit;
            }
        } else {
            $date = null;
        }

        try {
            $this->offerModel->update($id, ['scheduled_date' => $date ?: null]);
        } catch (\Exception $e) {
            error_log('saveScheduledDate error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage()]);
            exit;
        }

        echo json_encode([
            'success'        => true,
            'scheduled_date' => $date ? date('d/m/Y', strtotime($date)) : null,
        ]);
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE
    // ──────────────────────────────────────────────────────────────────────────
    public function delete(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        $this->offerModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        $_SESSION['success'] = 'Η προσφορά διαγράφηκε.';
        header('Location: ' . BASE_URL . '/maintenance-offers');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EXPORT PDF (TCPDF — 6-page template matching exact original design)
    // ──────────────────────────────────────────────────────────────────────────
    public function exportPDF(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        $pdf = $this->buildOfferPDF($offer);
        $filename = 'Prosfora_Sintirisis_' . preg_replace('/[^a-zA-Z0-9_-]/u', '_', $offer['offer_number']) . '.pdf';
        
        $mode = isset($_GET['download']) ? 'D' : 'I';
        $pdf->Output($filename, $mode);
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Build 6-page Offer PDF matching the target template layout and images
    // ──────────────────────────────────────────────────────────────────────────
    public function buildOfferPDF(array $offer): CustomOfferPDF {
        $co = $this->getCompanySettings();

        $company    = htmlspecialchars($offer['company_name'] ?? '');
        $offerNum   = htmlspecialchars($offer['offer_number'] ?? '');
        $today      = !empty($offer['created_at']) ? date('d/m/Y', strtotime($offer['created_at'])) : date('d/m/Y');
        $transCount = max(1, (int)($offer['transformers_count'] ?? 1));
        $priceVal   = (float)($offer['price'] ?? 0);
        $priceWithVat = $priceVal * 1.24;
        $notes      = trim($offer['notes'] ?? '');

        // Resolve images with robust fallback
        $imgDirUploads   = __DIR__ . '/../uploads/template_images/';
        $imgDirTemplates = __DIR__ . '/../templates/docx_images/';

        if (!is_dir($imgDirUploads)) {
            @mkdir($imgDirUploads, 0755, true);
        }

        $getImage = function(string $filename, ?string $fallback = null) use ($imgDirUploads, $imgDirTemplates): string {
            if (file_exists($imgDirUploads . $filename)) {
                return $imgDirUploads . $filename;
            }
            if (file_exists($imgDirTemplates . $filename)) {
                @copy($imgDirTemplates . $filename, $imgDirUploads . $filename);
                return $imgDirTemplates . $filename;
            }
            if ($fallback && file_exists($imgDirUploads . $fallback)) {
                return $imgDirUploads . $fallback;
            }
            if ($fallback && file_exists($imgDirTemplates . $fallback)) {
                return $imgDirTemplates . $fallback;
            }
            return '';
        };

        // Logo: use uploaded company logo from settings if available, otherwise template logo
        $logoPath = '';
        if (!empty($co['company_logo'])) {
            $candidateLogo = __DIR__ . '/../' . ltrim($co['company_logo'], '/');
            if (file_exists($candidateLogo)) {
                $logoPath = $candidateLogo;
            }
        }
        if (!$logoPath) {
            $logoPath = $getImage('image1.jpeg');
        }

        $transPath   = $getImage('image2.jpeg');
        $isoCertPath = $getImage('iso_certificate_greek.jpeg', 'image5.jpeg');
        $signPath    = $getImage('image6.jpeg');

        // Extract Greek ISO certificate if not already present
        if (!$isoCertPath || !file_exists($isoCertPath)) {
            $certDocx = __DIR__ . '/../templates/maintenance certificate template.docx';
            if (file_exists($certDocx)) {
                $zip = new \ZipArchive();
                if ($zip->open($certDocx) === true) {
                    $certData = $zip->getFromName('word/media/image4.jpeg');
                    if ($certData) {
                        file_put_contents($imgDirUploads . 'iso_certificate_greek.jpeg', $certData);
                        $isoCertPath = $imgDirUploads . 'iso_certificate_greek.jpeg';
                    }
                    $zip->close();
                }
            }
        }

        $footerHtml        = self::buildCompanyFooterHtml($co, false);
        $footerCompactHtml = self::buildCompanyFooterHtml($co, true);

        $pdf = new CustomOfferPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->logoPath                  = $logoPath;
        $pdf->companyFooterHtml         = $footerHtml;
        $pdf->companyFooterCompactHtml  = $footerCompactHtml;
        CustomOfferPDF::$staticFooterHtml        = $footerHtml;
        CustomOfferPDF::$staticFooterCompactHtml = $footerCompactHtml;

        $coAuthorName = !empty($co['company_name']) ? $co['company_name'] : (!empty($co['company_display_name']) ? $co['company_display_name'] : 'HandyCRM');
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor($coAuthorName);
        $pdf->SetTitle('Οικονομική Προσφορά ' . $offerNum);

        $pdf->SetMargins(20, 25, 20);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(20);
        $pdf->SetAutoPageBreak(false);

        // ================= PAGE 1: COVER =================
        $pdf->AddPage();
        if ($logoPath && file_exists($logoPath)) {
            $pdf->Image($logoPath, 20, 16, 68, 0, 'JPEG');
        }

        $pdf->SetY(48);
        $pdf->SetFont('dejavusans', 'B', 13);
        $pdf->Cell(0, 8, 'ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ', 0, 1, 'C');

        // Contacts at top of Page 1 from Settings
        $topWeb   = !empty($co['company_website']) ? $co['company_website'] : 'https://ecowatt-energy.gr';
        $topEmail = !empty($co['company_email']) ? $co['company_email'] : 'info@ecowatt-energy.gr';
        $p1MetaHtml = '
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td width="65%" style="font-size:8.5pt; line-height:1.5;">
              ' . htmlspecialchars($topWeb) . ' - email: ' . htmlspecialchars($topEmail) . '<br/>
              <b>ΑΡΙΘΜΟΣ ΠΡΟΣΦΟΡΑΣ : ' . $offerNum . '</b>
            </td>
            <td width="35%" align="right" style="font-size:8.5pt;">
              <b>ΗΜΕΡΟΜΗΝΙΑ: ' . $today . '</b>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 60, $p1MetaHtml, 0, 1, false, true, '');

        if ($transPath && file_exists($transPath)) {
            $pdf->Image($transPath, 52.5, 82, 105, 0, 'JPEG');
        }

        $scenarioHtml = '<p style="text-align:center; font-size:9.5pt;">Σενάριο: Προσφορά ΣΥΝΤΗΡΗΣΗΣ Υ/Σ Μέσης Τάσης στο ' . $company . ' (' . $transCount . 'Μ/Σ)</p>';
        $pdf->writeHTMLCell(170, 0, 20, 190, $scenarioHtml, 0, 1, false, true, '');

        // ================= PAGE 2: LETTER =================
        $pdf->AddPage();
        $p2Html = '
        <div style="font-family:dejavusans; font-size:9.5pt; line-height:1.65; color:#1a1a1a;">
          <p>Αγαπητοί κύριοι,</p>
          <br/>
          <p>θα ήθελαν να σας ευχαριστήσουμε για το ενδιαφέρον σας σχετικά τις παρεχόμενες υπηρεσίες και εξοπλισμό της εταιρείας και να σας διαβεβαιώσουν ότι θα είναι πάντα διαθέσιμοι για να σας προσφέρουν υψηλής ποιότητας και προηγμένης τεχνολογίας λύσεις.</p>
          <br/>
          <p><b>Προσφορά ΣΥΝΤΗΡΗΣΗΣ Υ/Σ Μέσης Τάσης στο ' . $company . ' (' . $transCount . 'Μ/Σ)</b></p>
          <br/>
          <p>Η περιγραφή που ακολουθεί προέκυψε βάσει της πολυετής εμπειρίας και της τεχνογνωσίας που έχει η εταιρία μας πάνω στους υποσταθμούς Μ.Τ.. Ωστόσο, θα πρέπει να γνωρίζετε ότι μελετάμε, σχεδιάζουμε και υλοποιούμε κάθε έργο σύμφωνα τις ιδιαιτερότητές του.</p>
        </div>';
        $pdf->writeHTMLCell(170, 0, 20, 38, $p2Html, 0, 1, false, true, '');

        // ================= PAGE 3: CERTIFICATE =================
        $pdf->AddPage();
        if ($isoCertPath && file_exists($isoCertPath)) {
            $pdf->Image($isoCertPath, 30, 28, 150, 0, 'JPEG');
        }

        // ================= PAGE 4: MAINTENANCE TASKS =================
        $pdf->AddPage();
        $p4Html = '
        <div style="font-family:dejavusans; font-size:9pt; line-height:1.45; color:#1a1a1a;">
          <p style="text-align:center; font-weight:bold; font-size:10.5pt; margin-bottom:12px;">ΕΡΓΑΣΙΕΣ ΣΥΝΤΗΡΗΣΗΣ</p>

          <p style="font-weight:bold; font-size:9.5pt; margin-bottom:4px;">1. ΓΕΝΙΚΗ ΣΥΝΤΗΡΗΣΗ</p>
          <ul style="list-style-type:disc; margin-left:15px; margin-top:2px; margin-bottom:12px;">
            <li>Καθαριότητα στους χώρους Μ/Σ, κυψέλης Μ.Τ</li>
            <li>Έλεγχος ύπαρξης πυροσβεστήρων στο χώρο του Υ/Σ</li>
            <li>Έλεγχος αερισμού Υ/Σ</li>
            <li>Έλεγχος φωτισμού στον χώρο του Υ/Σ</li>
            <li>Έλεγχος ύπαρξης πινακίδων κινδύνου</li>
          </ul>

          <p style="font-weight:bold; font-size:9.5pt; margin-bottom:4px;">2. ΣΥΝΤΗΡΗΣΗ ΣΤΗ ΜΕΣΗ ΤΑΣΗ – ΚΥΨΕΛΗ</p>
          <ul style="list-style-type:disc; margin-left:15px; margin-top:2px; margin-bottom:12px;">
            <li>Έλεγχος ακροκιβωτίων εσωτερικού χώρου</li>
            <li>Έλεγχος μονωτήρων κυψέλης, καθαρισμός</li>
            <li>Καθαρισμός εξαρτημάτων, επαφών, συσφίξεις ακροδεκτών</li>
            <li>Έλεγχος λειτουργίας αφύγρανσης κυψέλης</li>
            <li>Μέτρηση μονώσεων καλωδίων Μ.Τ.</li>
          </ul>

          <p style="font-weight:bold; font-size:9.5pt; margin-bottom:4px;">3. ΣΥΝΤΗΡΗΣΗ ΣΤΟ ΜΕΤΑΣΧΗΜΑΤΙΣΤΗ</p>
          <ul style="list-style-type:disc; margin-left:15px; margin-top:2px; margin-bottom:12px;">
            <li>Έλεγχος θερμοκρασίας Μ/Σ</li>
            <li>Έλεγχος μονωτήρων Μ.Τ. - Χ.Τ. , καθαρισμός</li>
            <li>Έλεγχος ακροκιβωτίων</li>
            <li>Έλεγχος καλής κατάστασης κελύφους Μ/Σ</li>
            <li>Καθαρισμός εξαρτημάτων , συσφίξεις ακροδεκτών</li>
            <li>Μέτρηση τυλιγμάτων Μ/Σ</li>
            <li>Μέτρηση μονώσεων Μ/Σ</li>
            <li>Μέτρηση διηλεκτρικής αντοχής ελαίου</li>
          </ul>

          <p style="font-weight:bold; font-size:9.5pt; margin-bottom:4px;">4. ΓΕΙΩΣΕΙΣ- ΠΡΟΣΤΑΣΙΑ</p>
          <ul style="list-style-type:disc; margin-left:15px; margin-top:2px; margin-bottom:12px;">
            <li>Μέτρηση γείωσης( Εφόσον είναι εφικτή η μέτρηση)</li>
            <li>Έλεγχος λειτουργίας αυτοματισμού προστασίας</li>
          </ul>
        </div>';
        $pdf->writeHTMLCell(170, 0, 20, 32, $p4Html, 0, 1, false, true, '');

        // ================= PAGE 5: CLIENTS =================
        $pdf->AddPage();
        $p5IntroCo = !empty($co['company_name']) ? $co['company_name'] : (!empty($co['company_display_name']) ? $co['company_display_name'] : 'εταιρεία μας');
        $p5Html = '
        <div style="font-family:dejavusans; font-size:9.5pt; line-height:1.65; color:#1a1a1a;">
          <p style="text-align:center; font-weight:bold; font-size:11pt; margin-bottom:20px;"><u>ΠΕΛΑΤΟΛΟΓΙΟ</u></p>

          <p style="margin-bottom:14px;"><u>Η ' . htmlspecialchars($p5IntroCo) . ' σας παρουσιάζει ένα μέρος από το πελατολόγιο της.</u></p>

          <p style="line-height:1.75;">
            <u>1. ΤΕΑΒ ΑΕ</u><br/>
            <u>2. ΧΑΛΚΙΑΔΑΚΗΣ</u><br/>
            <u>3. DAIOS HOTELS</u><br/>
            <u>4. LYTOS</u><br/>
            <u>5. IKAROS RESORT HOTEL</u><br/>
            <u>6. ΔΕΥΑ ΗΡΑΚΛΕΙΟΥ</u><br/>
            <u>7. ENEL</u><br/>
            <u>8. ENERCON</u><br/>
            <u>9. KOSTA MARE</u><br/>
            <u>10. ΔΕΗ</u><br/>
            <u>11. ΔΕΥΑ ΧΕΡΣΟΝΗΣΟΥ</u><br/>
            <u>12. ΟΤΕ</u><br/>
            <u>13. BLUE BAY</u><br/>
            <u>14. CRETA MARIS</u><br/>
            <u>15. GALAXY</u><br/>
            <u>16. CANDIA MARIS</u>
          </p>
          <br/>
          <p><u>Θα θέλαμε να σας κάνουμε γνωστό ότι είμαστε στη διάθεσής σας για οποιαδήποτε ερώτηση – διευκρίνιση.</u></p>
        </div>';
        $pdf->writeHTMLCell(170, 0, 20, 32, $p5Html, 0, 1, false, true, '');

        // ================= PAGE 6: FINANCIAL OFFER & SIGNATURES =================
        $pdf->AddPage();
        $notesHtml = $notes ? '<br/><br/><p style="font-size:9pt; color:#333;"><b>Παρατηρήσεις:</b> ' . nl2br(htmlspecialchars($notes)) . '</p>' : '';
        $p6Html = '
        <div style="font-family:dejavusans; font-size:9.5pt; color:#1a1a1a;">
          <p style="text-align:center; font-weight:bold; font-size:11pt; margin-bottom:14px;">ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ</p>

          <table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:9.5pt; border:1px solid #000000;">
            <tr style="font-weight:bold;">
              <td width="75%">ΤΙΜΗ ΣΥΝΟΛΙΚΟΥ ΕΡΓΟΥ</td>
              <td width="25%" align="center">ΤΙΜΗ</td>
            </tr>
            <tr>
              <td>ΣΥΝΟΛΟ ΕΡΓΟΥ ΧΩΡΙΣ Φ.Π.Α</td>
              <td align="right"><b>' . number_format($priceVal, 2, '.', '') . '</b></td>
            </tr>
            <tr>
              <td>ΣΥΝΟΛΟ ΕΡΓΟΥ ΜΕ Φ.Π.Α</td>
              <td align="right"><b>' . number_format($priceWithVat, 2, '.', '') . '</b></td>
            </tr>
          </table>

          <br/><br/>
          <p style="margin-bottom:8px;"><u>Οικονομικοί Όροι</u></p>
          <br/>
          <p>Προσφορά ΣΥΝΤΗΡΗΣΗΣ Υ/Σ Μέσης Τάσης στο ' . $company . ' (' . $transCount . 'Μ/Σ)</p>
          ' . $notesHtml . '
        </div>';
        $pdf->writeHTMLCell(170, 0, 20, 32, $p6Html, 0, 1, false, true, '');

        // Signatures block
        $p6SignHtml = '
        <table width="100%" cellpadding="0" cellspacing="0" style="font-family:dejavusans; font-size:9pt; line-height:1.5;">
          <tr>
            <td width="55%" valign="top">
              Με εκτίμηση,<br/><br/>
              ' . ($signPath && file_exists($signPath) ? '<img src="' . $signPath . '" width="110" /><br/>' : '<br/><br/>') . '
              <u>Σφακιανάκης Κωνσταντίνος</u><br/>
              <u>Ηλεκτρολόγος Μηχανικός ΤΕ</u>
            </td>
            <td width="45%" valign="top" align="center">
              <b>Αποδοχή προσφοράς</b><br/><br/><br/><br/><br/>
              <b>Υπογραφή - Σφραγίδα</b>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 175, $p6SignHtml, 0, 1, false, true, '');

        return $pdf;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EXPORT WORD — fills .docx template with TemplateProcessor
    // ──────────────────────────────────────────────────────────────────────────
    public function exportWord(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        $templatePath = __DIR__ . '/../templates/maintenance_offer_template.docx';
        if (!file_exists($templatePath)) {
            $_SESSION['error'] = 'Το πρότυπο Word δεν βρέθηκε. Τοποθετήστε το αρχείο maintenance_offer_template.docx στον φάκελο templates/.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        require_once __DIR__ . '/../vendor/autoload.php';

        $processor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
        $processor->setValue('XXXXX', number_format((float)$offer['price'], 2, ',', '.') . ' €');
        $processor->setValue('XXXX',  (string)$offer['transformers_count']);
        $processor->setValue('XXX',   $offer['company_name']);
        $processor->setValue('OFFER_NUMBER', $offer['offer_number']);
        $processor->setValue('DATE',         date('d/m/Y'));
        $processor->setValue('EXPIRY_DATE',  $offer['offer_expiry_date'] ? date('d/m/Y', strtotime($offer['offer_expiry_date'])) : '');
        $processor->setValue('ADDRESS',      $offer['address'] ?? '');
        $processor->setValue('PHONE',        $offer['phone'] ?? '');

        $filename = 'Προσφορά_Συντήρησης_' . preg_replace('/[^a-zA-Z0-9_-]/u', '_', $offer['company_name']) . '_' . $offer['offer_number'] . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $processor->saveAs('php://output');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SEND EMAIL (with Word attachment generated from template)
    // ──────────────────────────────────────────────────────────────────────────
    public function sendEmail(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../classes/EmailService.php';
            require_once __DIR__ . '/../classes/Database.php';

            try {
                $database    = new Database();
                $pdo         = $database->connect();
                $emailService = new EmailService($pdo);

                $recipientEmail = trim($_POST['recipient_email'] ?? $offer['email']);
                $subject        = trim($_POST['email_subject'] ?? 'Προσφορά Συντήρησης Υποσταθμού - ' . $offer['offer_number']);
                $message        = trim($_POST['email_message'] ?? '');

                if (empty($recipientEmail)) {
                    throw new \Exception('Δεν έχει οριστεί email παραλήπτη.');
                }

                // Generate PDF attachment via TCPDF
                $pdfFilename    = 'Prosfora_Sintirisis_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $offer['offer_number']) . '.pdf';
                $attachmentPath = sys_get_temp_dir() . '/' . $pdfFilename;

                $pdfMail = $this->buildOfferPDF($offer);
                $pdfMail->Output($attachmentPath, 'F');

                if (!file_exists($attachmentPath)) {
                    throw new \Exception('Η δημιουργία PDF για αποστολή απέτυχε.');
                }

                $mail = $emailService->createMailer();
                $mail->addAddress($recipientEmail, $offer['company_name']);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = nl2br(htmlspecialchars($message));
                $mail->addAttachment($attachmentPath, $pdfFilename);

                if ($mail->send()) {
                    if (file_exists($attachmentPath)) {
                        unlink($attachmentPath);
                    }
                    // Mark sent_at if not already set
                    if (empty($offer['sent_at'])) {
                        $this->offerModel->update($id, ['sent_at' => date('Y-m-d H:i:s')]);
                    }
                    $_SESSION['success'] = 'Το email στάλθηκε επιτυχώς στο ' . htmlspecialchars($recipientEmail) . '.';
                } else {
                    throw new \Exception('Email sending failed');
                }

            } catch (\Exception $e) {
                error_log('MaintenanceOfferController::sendEmail error: ' . $e->getMessage());
                $_SESSION['error'] = 'Σφάλμα κατά την αποστολή email: ' . $e->getMessage();
            }

            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        // GET → show email modal data as JSON (called by JS fetch)
        header('Content-Type: application/json');
        echo json_encode([
            'email'        => $offer['email'] ?? '',
            'company_name' => $offer['company_name'],
            'offer_number' => $offer['offer_number'],
        ]);
        exit;
    }
}
