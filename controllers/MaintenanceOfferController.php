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
                    $this->Image($this->logoPath, 20, 8, 52, 0, 'JPEG');
                }
                // "ΠΡΟΣΦΟΡΑ" badge with Ecowatt brand colors
                $this->SetXY(156, 10);
                $this->SetFont('dejavusans', 'B', 8.5);
                $this->SetTextColor(234, 88, 12); // #ea580c (vibrant orange)
                $this->SetDrawColor(249, 115, 22); // #f97316
                $this->SetFillColor(255, 247, 237); // #fff7ed
                $this->SetLineWidth(0.3);
                $this->Cell(34, 6.5, 'ΠΡΟΣΦΟΡΑ', 1, 0, 'C', true);

                // Subtle two-tone decorative divider line below header (Y=19)
                $this->SetDrawColor(132, 204, 22); // Lime green #84cc16
                $this->SetLineWidth(0.6);
                $this->Line(20, 19, 105, 19);
                $this->SetDrawColor(249, 115, 22); // Orange #f97316
                $this->Line(105, 19, 190, 19);
            }
        }

        public function Footer() {
            $this->SetY(-22);
            $this->SetFont('dejavusans', '', 8);
            $this->SetTextColor(71, 85, 105);

            // Subtle two-tone decorative divider line above footer (Y=271)
            $this->SetDrawColor(249, 115, 22); // Orange #f97316
            $this->SetLineWidth(0.4);
            $this->Line(20, 271.5, 105, 271.5);
            $this->SetDrawColor(132, 204, 22); // Lime green #84cc16
            $this->Line(105, 271.5, 190, 271.5);

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
                    $this->SetXY(150, 278);
                    $this->SetFont('dejavusans', 'B', 8.5);
                    $this->SetTextColor(100, 116, 139);
                    $this->Cell(40, 5, 'Σελίδα: ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
                } else {
                    // Pages 2, 5, 6: Compact company info from settings on left/center + Page number on right
                    if (!empty($footerCompact)) {
                        $this->writeHTMLCell(135, 0, 20, 274, $footerCompact, 0, 0, false, true, 'C');
                    }
                    $this->SetXY(155, 278);
                    $this->SetFont('dejavusans', 'B', 8.5);
                    $this->SetTextColor(100, 116, 139);
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
            'company_tax_office'   => '',
            'company_legal_form'   => '',
            'company_legal_rep'    => '',
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
            'title'              => trim($_POST['title'] ?? '') ?: null,
            'company_name'       => trim($_POST['company_name'] ?? ''),
            'address'            => trim($_POST['address'] ?? ''),
            'phone'              => trim($_POST['phone'] ?? ''),
            'email'              => trim($_POST['email'] ?? ''),
            'transformers_count' => $count,
            'transformer_power'  => trim($_POST['transformer_power'] ?? '') ?: null,
            'price'              => $price,
            'offer_expiry_date'  => $_POST['offer_expiry_date'] ?: null,
            'terms'              => trim($_POST['terms'] ?? '') ?: null,
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
            'title'              => trim($_POST['title'] ?? '') ?: null,
            'company_name'       => trim($_POST['company_name'] ?? ''),
            'address'            => trim($_POST['address'] ?? ''),
            'phone'              => trim($_POST['phone'] ?? ''),
            'email'              => trim($_POST['email'] ?? ''),
            'transformers_count' => $count,
            'transformer_power'  => trim($_POST['transformer_power'] ?? '') ?: null,
            'price'              => $price,
            'offer_expiry_date'  => $_POST['offer_expiry_date'] ?: null,
            'terms'              => trim($_POST['terms'] ?? '') ?: null,
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
    // Build clean download filename for offer exports including client company name
    // ──────────────────────────────────────────────────────────────────────────
    public function buildOfferFilename(array $offer, string $extension): string {
        $cleanCompany = trim(preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $offer['company_name'] ?? ''), '_');
        $cleanNum     = preg_replace('/[^a-zA-Z0-9_-]/u', '_', $offer['offer_number'] ?? '');
        $prefix       = 'Προσφορά_Συντήρησης';
        return ($cleanCompany !== '')
            ? "{$prefix}_{$cleanCompany}_{$cleanNum}.{$extension}"
            : "{$prefix}_{$cleanNum}.{$extension}";
    }

    // ──────────────────────────────────────────────────────────────────────────
    // In-Memory Puppeteer PDF Generator & Helper Methods
    // ──────────────────────────────────────────────────────────────────────────
    private function getBase64Image(string $path): string {
        if (!file_exists($path)) {
            return '';
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'   => 'image/png',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'webp'  => 'image/webp',
            default => 'image/jpeg',
        };
        $data = file_get_contents($path);
        if ($data === false || $data === '') {
            return '';
        }
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    public function generateOfferPdfWithPuppeteer(string $html): ?string {
        $projectRoot = dirname(__DIR__);
        $scriptPath  = $projectRoot . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'generate_pdf_puppeteer.js';
        if (!file_exists($scriptPath)) {
            return null;
        }

        $nodeBin = 'node';
        $possiblePaths = [
            'C:\\Program Files\\nodejs\\node.exe',
            'C:\\Program Files (x86)\\nodejs\\node.exe',
        ];
        foreach ($possiblePaths as $cand) {
            if (file_exists($cand)) {
                $nodeBin = '"' . $cand . '"';
                break;
            }
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $cmd = $nodeBin . ' "' . $scriptPath . '"';
        $proc = proc_open($cmd, $descriptors, $pipes, $projectRoot);
        if (!is_resource($proc)) {
            return null;
        }

        fwrite($pipes[0], $html);
        fclose($pipes[0]);

        $pdfBinary = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($proc);
        if ($exitCode === 0 && strlen($pdfBinary) > 1000) {
            return $pdfBinary;
        }

        if (!empty($stderr)) {
            error_log('Puppeteer PDF generation error: ' . $stderr);
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Build 6-Page Clean HTML Document matching Modern Mockup & Brand Palette
    // ──────────────────────────────────────────────────────────────────────────
    public function buildOfferHtml(array $offer): string {
        $co = $this->getCompanySettings();

        $companyRaw   = trim($offer['company_name'] ?? '');
        $company      = htmlspecialchars($companyRaw);
        $offerNum     = htmlspecialchars($offer['offer_number'] ?? '');
        $today        = !empty($offer['created_at']) ? date('d/m/Y', strtotime($offer['created_at'])) : date('d/m/Y');
        $transCount   = max(1, (int)($offer['transformers_count'] ?? 1));
        $priceVal     = (float)($offer['price'] ?? 0);
        $vatVal       = $priceVal * 0.24;
        $priceWithVat = $priceVal + $vatVal;
        $notes        = trim($offer['notes'] ?? '');
        $terms        = trim($offer['terms'] ?? '');

        $customTitle = trim($offer['title'] ?? '');
        if (!empty($customTitle)) {
            if ($companyRaw !== '' && mb_stripos($customTitle, $companyRaw) === false) {
                $offerTitle = htmlspecialchars($customTitle . ' - ' . $companyRaw);
            } else {
                $offerTitle = htmlspecialchars($customTitle);
            }
        } else {
            $offerTitle = 'Προσφορά ΣΥΝΤΗΡΗΣΗΣ Υ/Σ Μέσης Τάσης στο ' . $company . ' (' . $transCount . 'Μ/Σ)';
        }

        // Image paths & fallbacks
        $imgDirUploads   = __DIR__ . '/../uploads/template_images/';
        $imgDirTemplates = __DIR__ . '/../templates/docx_images/';

        $findImg = function(string $filename, ?string $fallback = null) use ($imgDirUploads, $imgDirTemplates): string {
            if (file_exists($imgDirUploads . $filename)) return $imgDirUploads . $filename;
            if (file_exists($imgDirTemplates . $filename)) return $imgDirTemplates . $filename;
            if ($fallback && file_exists($imgDirUploads . $fallback)) return $imgDirUploads . $fallback;
            if ($fallback && file_exists($imgDirTemplates . $fallback)) return $imgDirTemplates . $fallback;
            return '';
        };

        $logoPath = '';
        if (!empty($co['company_logo'])) {
            $candidateLogo = __DIR__ . '/../' . ltrim($co['company_logo'], '/');
            if (file_exists($candidateLogo)) {
                $logoPath = $candidateLogo;
            }
        }
        if (!$logoPath) {
            $logoPath = $findImg('image1.jpeg');
        }

        $transPath = $findImg('substation_hero_mockup.jpeg', 'image2.jpeg');
        $isoCertPath = $findImg('iso_certificate_greek.jpeg', 'image5.jpeg');
        $signPath    = $findImg('image6.jpeg');

        $logoBase64  = $this->getBase64Image($logoPath);
        $transBase64 = $this->getBase64Image($transPath);
        $certBase64  = $this->getBase64Image($isoCertPath);
        $signBase64  = $this->getBase64Image($signPath);

        $coDisplayName = !empty($co['company_display_name']) ? trim($co['company_display_name']) : 'Ecowatt Energy';
        $coLegalName   = !empty($co['company_name']) ? trim($co['company_name']) : $coDisplayName;
        $topWeb        = !empty($co['company_website']) ? $co['company_website'] : 'https://ecowatt-energy.gr';
        $topEmail      = !empty($co['company_email']) ? $co['company_email'] : 'info@ecowatt-energy.gr';
        $topPhone      = !empty($co['company_phone']) ? $co['company_phone'] : '2811 113851';
        $coName        = $coLegalName ?: $coDisplayName;
        $coAddr        = !empty($co['company_address']) ? $co['company_address'] : 'ΝΙΚΟΛΑΟΥ ΚΟΚΚΙΝΟΥ 3, Ηράκλειο';
        $coTax         = !empty($co['company_tax_id']) ? $co['company_tax_id'] : '';
        $coTaxOffice   = !empty($co['company_tax_office']) ? $co['company_tax_office'] : '';
        $coLegalRep    = !empty($co['company_legal_rep']) ? $co['company_legal_rep'] : 'ΣΦΑΚΙΑΝΑΚΗΣ ΚΩΝΣΤΑΝΤΙΝΟΣ';

        // Human-friendly formatted legal representative name
        $repWords = preg_split('/\s+/u', trim($coLegalRep));
        $repFormattedArr = [];
        foreach ($repWords as $w) {
            $repFormattedArr[] = mb_convert_case($w, MB_CASE_TITLE, 'UTF-8');
        }
        $legalRepFormatted = implode(' ', $repFormattedArr);
        $legalRepFormatted = str_replace(['Σφακιανακησ', 'Κωνσταντινοσ'], ['Σφακιανάκης', 'Κωνσταντίνος'], $legalRepFormatted);

        // Build comprehensive running footer string
        $footerParts = [];
        if (!empty($coAddr)) $footerParts[] = htmlspecialchars($coAddr);
        if (!empty($coTax)) $footerParts[] = 'ΑΦΜ: ' . htmlspecialchars($coTax) . (!empty($coTaxOffice) ? ' (ΔΟΥ ' . htmlspecialchars($coTaxOffice) . ')' : '');
        if (!empty($topPhone)) $footerParts[] = 'Τηλ: ' . htmlspecialchars($topPhone);
        if (!empty($topEmail)) $footerParts[] = htmlspecialchars($topEmail);
        $runningFooterHtml = '<strong>' . htmlspecialchars($coDisplayName ?: $coLegalName) . '</strong> &bull; ' . implode(' &bull; ', $footerParts);

        $expiryText = !empty($offer['offer_expiry_date']) ? date('d/m/Y', strtotime($offer['offer_expiry_date'])) : '30 Ημέρες';

        // Resolve Transformer Power rating (e.g. '630 kVA' or '500 kVA & 630 kVA')
        $transPower = trim($offer['transformer_power'] ?? '');
        if ($transPower === '') {
            $textPool = ($offer['title'] ?? '') . ' ' . ($offer['notes'] ?? '');
            if (preg_match('/\b(\d{2,4}\s*(?:kVA|kva|KVA)(?:\s*(?:&|,|\+)\s*\d{2,4}\s*(?:kVA|kva|KVA))*)\b/u', $textPool, $matches)) {
                $transPower = $matches[1];
            }
        }
        if ($transPower === '') {
            if ($transCount === 1) {
                $transPower = '630 kVA';
            } elseif ($transCount === 2) {
                $transPower = '500 kVA & 630 kVA';
            } else {
                $transPower = $transCount . 'x 630 kVA';
            }
        }

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<title>Οικονομική Προσφορά <?= $offerNum ?> - <?= $company ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  @page {
    size: A4 portrait;
    margin: 0;
  }
  @media print {
    @page {
      size: A4 portrait;
      margin: 0 !important;
    }
    html, body {
      background: #ffffff !important;
      padding: 0 !important;
      margin: 0 !important;
      width: 210mm !important;
      height: auto !important;
      overflow: visible !important;
    }
    .no-print { display: none !important; }
    .page {
      margin: 0 !important;
      box-shadow: none !important;
      border-radius: 0 !important;
      page-break-after: always !important;
      break-after: page !important;
      page-break-inside: avoid !important;
      break-inside: avoid !important;
      height: 295.5mm !important;
      max-height: 295.5mm !important;
    }
    .page:last-child,
    .page:last-of-type {
      page-break-after: avoid !important;
      break-after: avoid !important;
      page-break-inside: avoid !important;
      break-inside: avoid !important;
    }
    .page-cover {
      height: 295.5mm !important;
      max-height: 295.5mm !important;
      padding: 0 !important;
    }
    .cover-photo-card {
      width: 100% !important;
      height: 110mm !important;
      max-height: 110mm !important;
      box-sizing: border-box !important;
    }
  }
  @media screen {
    body {
      background-color: #e2e8f0;
      padding: 72px 0 60px 0;
    }
    .page {
      margin: 0 auto 30px auto;
      box-shadow: 0 14px 40px rgba(10, 25, 47, 0.16), 0 2px 10px rgba(10, 25, 47, 0.08);
      border-radius: 4px;
    }
  }
  * {
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    font-size: 13px;
    line-height: 1.5;
  }
  .page {
    width: 210mm;
    height: 295.5mm;
    max-height: 295.5mm;
    overflow: hidden;
    position: relative;
    background: #ffffff;
    page-break-after: always;
    break-after: page;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    padding: 12mm 18mm 12mm 18mm;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-sizing: border-box;
  }
  .page:last-child,
  .page:last-of-type {
    page-break-after: avoid !important;
    break-after: avoid !important;
  }
  
  /* Running Header */
  .header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    position: relative;
  }
  .header-bar::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #84cc16 0%, #ea580c 100%);
    border-radius: 2px;
  }
  .header-logo {
    height: 38px;
    object-fit: contain;
  }
  .header-badge {
    background: #fff7ed;
    color: #ea580c;
    border: 1px solid #fed7aa;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 4px 14px;
    border-radius: 20px;
    text-transform: uppercase;
  }

  /* Running Footer */
  .footer-bar {
    position: relative;
    padding-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    color: #64748b;
  }
  .footer-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, #ea580c 0%, #84cc16 100%);
    border-radius: 2px;
  }
  .footer-co {
    font-size: 9.3px;
    line-height: 1.35;
    white-space: nowrap;
    max-width: 88%;
  }
  .footer-page {
    font-weight: 700;
    color: #0f172a;
    font-size: 11px;
    white-space: nowrap;
  }

  /* PAGE 1: COVER (EXACT REPRODUCTION OF CORPORATE MOCKUP) */
  .page-cover {
    padding: 0;
    position: relative;
    overflow: hidden;
    background: #ffffff;
    display: block;
    width: 210mm;
    height: 295.5mm;
    max-height: 295.5mm;
    font-family: 'Montserrat', 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
  }
  .cover-bg-svg {
    position: absolute;
    top: 0;
    left: 0;
    width: 210mm;
    height: 100%;
    pointer-events: none;
    z-index: 1;
  }
  .cover-content {
    position: relative;
    z-index: 2;
    padding: 10mm 15mm 12mm 15mm;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-sizing: border-box;
  }

  /* Header Group: Logo & Titles grouped towards the top */
  .cover-header-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }
  .cover-top-logo {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }
  .cover-logo-img {
    height: 70px;
    max-width: 360px;
    object-fit: contain;
    object-position: left center;
    display: block;
  }
  .cover-logo-title-row {
    display: flex;
    align-items: baseline;
    gap: 6px;
    font-size: 32px;
    font-weight: 900;
    line-height: 1.05;
    letter-spacing: -0.3px;
  }
  .cover-logo-navy { color: #0a192f; }
  .cover-logo-sub {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2.2px;
    color: #65a30d;
    text-transform: uppercase;
    margin-top: 4px;
  }

  /* Titles: directly under logo towards top */
  .cover-titles {
    margin-top: 10px;
    margin-bottom: 0;
  }
  .cover-proposal-tag {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 2.5px;
    color: #475569;
    text-transform: uppercase;
    margin-bottom: 4px;
  }
  .cover-h1 {
    font-size: 26.5px;
    font-weight: 900;
    color: #0a192f;
    line-height: 1.16;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: -0.2px;
  }

  /* Photo Frame: matches mockup identically with 110mm height, rounded white border, corporate shadow */
  .cover-photo-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 4.5mm;
    box-shadow: 0 14px 34px rgba(10, 25, 47, 0.13);
    border: 1px solid rgba(226, 232, 240, 0.9);
    width: 100%;
    height: 110mm;
    max-height: 110mm;
    display: flex;
    overflow: hidden;
    box-sizing: border-box;
  }
  .cover-photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    border-radius: 14px;
    display: block;
  }

  /* Client Metadata Card: light gray background, rounded, crisp typography */
  .cover-meta-card {
    background: #f1f5f9;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 6px 20px rgba(10, 25, 47, 0.04);
    padding: 16px 24px;
    margin-top: 6px;
    width: 100%;
    box-sizing: border-box;
  }
  .cover-client-title {
    font-size: 22px;
    font-weight: 900;
    color: #0a192f;
    margin-bottom: 10px;
    letter-spacing: -0.3px;
    text-transform: uppercase;
  }
  .cover-meta-grid {
    display: flex;
    flex-direction: column;
    gap: 5px;
    font-size: 12.5px;
    color: #1e293b;
    line-height: 1.45;
  }
  .cover-meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px 6px;
  }
  .label-lime {
    color: #65a30d;
    font-weight: 700;
  }
  .label-orange {
    color: #ea580c;
    font-weight: 700;
  }
  .val-text {
    font-weight: 600;
    color: #1e293b;
  }

  /* Footer: zero obstruction with white contrast pills */
  .cover-footer-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    font-size: 11px;
    padding-top: 8px;
    position: relative;
    z-index: 10;
  }
  .cover-footer-left {
    font-weight: 800;
    color: #0a192f;
    letter-spacing: 0.3px;
    background: #ffffff;
    padding: 2px 6px;
    border-radius: 4px;
  }
  .cover-footer-left span {
    font-weight: 600;
    color: #64748b;
  }
  .cover-footer-right {
    text-align: right;
    font-weight: 700;
    color: #0a192f;
    line-height: 1.35;
    background: #ffffff;
    padding: 2px 6px;
    border-radius: 4px;
  }
  .cover-footer-right-top {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 5px;
  }
  .cover-footer-right-sub {
    font-size: 10.5px;
    font-weight: 600;
    color: #64748b;
  }

  /* PAGE 2: LETTER */
  .letter-salutation {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 14px;
  }
  .letter-para {
    font-size: 14.2px;
    line-height: 1.95;
    color: #334155;
    margin: 0;
    text-align: justify;
  }
  .callout-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 5px solid #ea580c;
    border-radius: 14px;
    padding: 24px 28px;
    margin: 0;
  }
  .callout-label {
    font-size: 11.5px;
    font-weight: 800;
    color: #ea580c;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 6px;
  }
  .callout-title {
    font-size: 17.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
  }
  .pillars-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin: 0;
  }
  .pillar-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 26px 20px;
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
  }
  .pillar-card.lime { border-top: 4px solid #65a30d; }
  .pillar-card.orange { border-top: 4px solid #ea580c; }
  .pillar-icon {
    font-size: 24px;
    margin-bottom: 10px;
    display: inline-block;
  }
  .pillar-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
  }
  .pillar-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.6;
  }

  /* PAGE 3: CERTIFICATE */
  .cert-header {
    text-align: center;
    margin-bottom: 16px;
  }
  .cert-badge {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 16px;
    border-radius: 20px;
    display: inline-block;
    text-transform: uppercase;
    margin-bottom: 8px;
  }
  .cert-title {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px 0;
  }
  .cert-sub {
    font-size: 12.5px;
    color: #64748b;
  }
  .cert-frame {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    padding: 12px;
    overflow: hidden;
  }
  .cert-frame img {
    max-width: 100%;
    max-height: 175mm;
    object-fit: contain;
    border-radius: 8px;
  }

  /* PAGE 4: TASKS */
  .tasks-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 16px;
  }
  .tasks-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 16px;
    break-inside: avoid;
    page-break-inside: avoid;
  }
  .task-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    break-inside: avoid;
    page-break-inside: avoid;
  }
  .task-card.orange { border-top: 4px solid #ea580c; }
  .task-card.lime { border-top: 4px solid #65a30d; }
  .task-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    padding-bottom: 9px;
    border-bottom: 1px solid #e2e8f0;
  }
  .task-card-header.orange .task-title { color: #ea580c; }
  .task-card-header.lime .task-title { color: #65a30d; }
  .task-icon-circle {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .task-icon-circle.orange { background: #fff7ed; color: #ea580c; }
  .task-icon-circle.lime { background: #f7fee7; color: #65a30d; }
  .task-icon-svg {
    width: 17px;
    height: 17px;
    display: block;
  }
  .task-title {
    font-size: 13.2px;
    font-weight: 800;
    margin: 0;
    letter-spacing: -0.2px;
  }
  .task-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 9.5px;
    font-size: 11.6px;
    color: #334155;
  }
  .task-list li {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    line-height: 1.45;
  }
  .check-icon {
    width: 14px;
    height: 14px;
    fill: #65a30d;
    flex-shrink: 0;
    margin-top: 1.5px;
  }
  .trust-banner {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 5px solid #ea580c;
    border-radius: 10px;
    padding: 14px 20px;
    text-align: center;
    margin-top: 12px;
    font-size: 11.2px;
    break-inside: avoid;
    page-break-inside: avoid;
  }
  .trust-banner strong { color: #0f172a; font-size: 12px; }
  .trust-banner span { color: #64748b; font-size: 10.5px; }

  /* PAGE 5: CLIENTS */
  .clients-header {
    text-align: center;
    margin-bottom: 18px;
  }
  .clients-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 16px;
  }
  .client-pill {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
  }
  .client-pill.orange { border-left: 4px solid #ea580c; }
  .client-pill.lime { border-left: 4px solid #65a30d; }
  .client-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: 10.5px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  /* PAGE 6: FINANCIAL & SIGNATURE */
  .finance-header {
    text-align: center;
    margin-bottom: 24px;
  }
  .pricing-table-card {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05);
    margin-bottom: 22px;
  }
  .pricing-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
  }
  .pricing-table th {
    background: #0f172a;
    color: #ffffff;
    font-weight: 700;
    padding: 16px 20px;
    text-align: left;
    font-size: 12.5px;
    letter-spacing: 0.5px;
  }
  .pricing-table th:last-child { text-align: right; }
  .pricing-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    color: #1e293b;
  }
  .pricing-table td:last-child {
    text-align: right;
    font-weight: 700;
    color: #0f172a;
  }
  .pricing-table tr.vat-row td {
    background: #f8fafc;
    color: #64748b;
    font-weight: 500;
  }
  .grand-total-banner {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    color: #ffffff;
    padding: 18px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px;
    margin-bottom: 26px;
    box-shadow: 0 6px 18px rgba(234, 88, 12, 0.25);
  }
  .grand-total-label {
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }
  .grand-total-val {
    font-size: 24px;
    font-weight: 900;
  }
  .terms-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 5px solid #65a30d;
    border-radius: 12px;
    padding: 20px 22px;
    margin-bottom: 28px;
    font-size: 12.5px;
    color: #334155;
    line-height: 1.65;
  }
  .terms-card-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #65a30d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
  }
  .signatures-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 28px;
    align-items: stretch;
  }
  .sig-col-left {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    font-size: 13px;
  }
  .sig-img {
    height: 70px;
    object-fit: contain;
    margin: 10px 0;
    display: block;
  }
  .acceptance-box {
    border: 2px dashed #ea580c;
    border-radius: 14px;
    background: #fffcf7;
    padding: 22px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .acceptance-title {
    color: #ea580c;
    font-size: 14.5px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 5px;
  }
  .acceptance-sub {
    color: #64748b;
    font-size: 12px;
    margin-bottom: 36px;
  }
  .acceptance-date {
    color: #94a3b8;
    font-size: 11.5px;
    border-top: 1px dashed #fed7aa;
    padding-top: 12px;
  }
</style>
</head>
<body>

<div class="no-print" style="position:fixed; top:12px; right:20px; z-index:99999; display:flex; align-items:center; gap:10px; font-family:'Segoe UI',sans-serif; background:rgba(15, 23, 42, 0.92); backdrop-filter:blur(8px); padding:8px 14px; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.15);">
  <div style="background:#0f172a; color:#f8fafc; padding:8px 12px; border-radius:8px; font-size:12px; border:1px solid #334155;">
    💡 <strong>Tip εκτύπωσης:</strong> Επιλέξτε <em>Περιθώρια: Κανένα (None)</em>
  </div>
  <button onclick="window.print()" style="background:#ea580c; color:#ffffff; border:none; padding:10px 20px; border-radius:8px; font-size:13.5px; font-weight:700; cursor:pointer; box-shadow:0 6px 16px rgba(234,88,12,0.45); display:flex; align-items:center; gap:7px;">
    🖨️ Εκτύπωση / Αποθήκευση σε PDF
  </button>
  <button onclick="window.close()" style="background:#334155; color:#ffffff; border:none; padding:10px 16px; border-radius:8px; font-size:13.5px; font-weight:600; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.2);">
    ✕ Κλείσιμο
  </button>
</div>

<!-- ==================== PAGE 1: COVER ==================== -->
<div class="page page-cover">
  <!-- Exact Geometry matching mockup with Lime Green & Orange Accents -->
  <svg class="cover-bg-svg" viewBox="0 0 210 297" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <linearGradient id="limeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#84cc16"/>
        <stop offset="100%" stop-color="#65a30d"/>
      </linearGradient>
      <linearGradient id="orangeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#f97316"/>
        <stop offset="100%" stop-color="#ea580c"/>
      </linearGradient>
    </defs>

    <!-- 1. TOP RIGHT ARTWORK -->
    <polygon points="120,0 210,0 210,120 178,82 142,35" fill="#f8fafc"/>
    <polygon points="135,0 210,0 210,100 182,68 152,26" fill="#f1f5f9"/>
    <polygon points="144,0 210,0 210,76 176,38 154,12" fill="#0a192f"/>

    <!-- Accent Diagonal Bars in Top Right -->
    <polygon points="152,0 162,0 176,14 168,20" fill="url(#limeGrad)"/>
    <polygon points="166,0 173,0 182,9 177,13" fill="url(#orangeGrad)"/>

    <!-- Architectural Diamond Grid Lines (Top-Right) -->
    <line x1="130" y1="0" x2="210" y2="80" stroke="#84cc16" stroke-width="0.45" opacity="0.85"/>
    <line x1="148" y1="0" x2="210" y2="62" stroke="#ea580c" stroke-width="0.35" opacity="0.75"/>
    <line x1="165" y1="0" x2="210" y2="45" stroke="#94a3b8" stroke-width="0.3" stroke-dasharray="2,2"/>
    <line x1="210" y1="16" x2="148" y2="78" stroke="#84cc16" stroke-width="0.45" opacity="0.85"/>
    <line x1="210" y1="42" x2="165" y2="87" stroke="#ea580c" stroke-width="0.35" opacity="0.7"/>

    <!-- 2. LEFT SIDE ARTWORK -->
    <polygon points="0,150 0,250 32,218 34,178 0,150" fill="#0a192f"/>
    <line x1="0" y1="162" x2="40" y2="198" stroke="#84cc16" stroke-width="0.4" opacity="0.8"/>
    <line x1="0" y1="190" x2="35" y2="222" stroke="#ea580c" stroke-width="0.35" opacity="0.7"/>
    <line x1="0" y1="225" x2="35" y2="196" stroke="#84cc16" stroke-width="0.4" opacity="0.8"/>

    <!-- Green & Orange diamond chevrons between photo & card -->
    <polygon points="18,198 42,176 56,192 32,214" fill="url(#limeGrad)"/>
    <polygon points="32,214 40,222 58,206 50,198" fill="url(#orangeGrad)"/>

    <!-- 3. BOTTOM CHEVRON & ARTWORK (Strictly Y <= 260mm, zero overlap with footer zone) -->
    <polygon points="65,260 106,211 138,243 122,260" fill="#0a192f"/>
    <polygon points="98,211 112,197 150,235 136,249" fill="url(#limeGrad)"/>
    <polygon points="112,197 120,189 156,225 148,233" fill="url(#orangeGrad)"/>
    <line x1="88" y1="225" x2="165" y2="148" stroke="#84cc16" stroke-width="0.4" opacity="0.75"/>
    <line x1="102" y1="239" x2="178" y2="163" stroke="#ea580c" stroke-width="0.35" opacity="0.6"/>
  </svg>

  <div class="cover-content">
    <!-- Header Block: Top Logo + Titles grouped towards the top -->
    <div class="cover-header-group">
      <div class="cover-top-logo">
        <?php if (!empty($logoBase64)): ?>
          <img src="<?= $logoBase64 ?>" class="cover-logo-img" alt="<?= htmlspecialchars($coDisplayName) ?>">
        <?php else: ?>
          <div class="cover-logo-title-row">
            <span class="cover-logo-navy"><?= htmlspecialchars($coDisplayName) ?></span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Titles -->
      <div class="cover-titles">
        <div class="cover-proposal-tag">CORPORATE PROPOSAL</div>
        <h1 class="cover-h1">
          ΠΡΟΣΦΟΡΑ ΣΥΝΤΗΡΗΣΗΣ<br>
          ΥΠΟΣΤΑΘΜΟΥ ΜΕΣΗΣ ΤΑΣΗΣ
        </h1>
      </div>
    </div>

    <!-- Transformer Hero Photo Frame matching mockup (white frame, shadow, rounded) -->
    <div class="cover-photo-card">
      <?php if ($transBase64): ?>
        <img src="<?= $transBase64 ?>" alt="Υποσταθμός Μέσης Τάσης">
      <?php endif; ?>
    </div>

    <!-- Executive Client Metadata Card matching mockup -->
    <div class="cover-meta-card">
      <div class="cover-client-title"><?= $company ?></div>
      <div class="cover-meta-grid">
        <div class="cover-meta-row">
          <span class="label-lime">Project:</span>
          <span class="val-text"><?= $offerTitle ?></span>
          <span class="label-orange" style="margin-left: 6px;">| Προσφορά:</span>
          <span class="val-text"><?= $offerNum ?></span>
        </div>
        <div class="cover-meta-row">
          <span class="label-lime">Ημερομηνία:</span>
          <span class="val-text"><?= $today ?></span>
          <span class="label-orange" style="margin-left: 10px;">| Ισχύς:</span>
          <span class="val-text"><?= htmlspecialchars($transPower) ?></span>
        </div>
        <div class="cover-meta-row">
          <span class="label-lime">Τοποθεσία:</span>
          <span class="val-text"><?= !empty($offer['address']) ? htmlspecialchars($offer['address']) : htmlspecialchars($coAddr) ?></span>
          <span class="label-orange" style="margin-left: 10px;">| Εξοπλισμός:</span>
          <span class="val-text"><?= $transCount ?> Μετασχηματιστής/ές Μ.Τ.</span>
        </div>
      </div>
    </div>

    <!-- Cover Footer Row from Company Settings -->
    <div class="cover-footer-row">
      <div class="cover-footer-left">
        <?= htmlspecialchars($coDisplayName) ?> <span>| <?= htmlspecialchars($topWeb) ?></span>
      </div>
      <div class="cover-footer-right">
        <div class="cover-footer-right-top">
          <span style="color:#65a30d;">📞</span> <?= htmlspecialchars($topPhone) ?> | <?= htmlspecialchars(parse_url($topWeb, PHP_URL_HOST) ?? 'ecowatt-energy.gr') ?>
        </div>
        <div class="cover-footer-right-sub"><?= htmlspecialchars($topEmail) ?></div>
      </div>
    </div>
  </div>
</div>

<!-- ==================== PAGE 2: LETTER ==================== -->
<div class="page">
  <div class="header-bar">
    <?php if ($logoBase64): ?>
      <img src="<?= $logoBase64 ?>" class="header-logo" alt="Logo">
    <?php else: ?>
      <strong><?= htmlspecialchars($coName) ?></strong>
    <?php endif; ?>
    <div class="header-badge">ΠΡΟΣΦΟΡΑ <?= $offerNum ?></div>
  </div>

  <div style="flex: 1; padding: 22px 0 10px 0; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="letter-intro-block">
      <div class="letter-salutation">Αξιότιμοι κύριοι,</div>
      <p class="letter-para">
        Θα θέλαμε να σας ευχαριστήσουμε θερμά για το ενδιαφέρον σας σχετικά με τις παρεχόμενες τεχνικές υπηρεσίες και τον εξοπλισμό της εταιρείας μας. Σας διαβεβαιώνουμε ότι παραμένουμε πάντα στη διάθεσή σας για να σας προσφέρουμε υψηλής ποιότητας, ασφαλείς και προηγμένης τεχνολογίας λύσεις.
      </p>
    </div>

    <div class="callout-box">
      <div class="callout-label">ΑΝΤΙΚΕΙΜΕΝΟ ΤΕΧΝΙΚΗΣ ΠΡΟΣΦΟΡΑΣ</div>
      <div class="callout-title"><?= $offerTitle ?></div>
    </div>

    <p class="letter-para">
      Η περιγραφή των εργασιών που ακολουθεί συντάχθηκε βάσει της πολυετούς εμπειρίας και της εξειδικευμένης τεχνογνωσίας που διαθέτει η εταιρεία μας στους Υποσταθμούς Μέσης Τάσης (Μ.Τ.). Μελετούμε, σχεδιάζουμε και υλοποιούμε κάθε έργο σύμφωνα με τις ιδιαίτερες τεχνικές απαιτήσεις και τις προδιαγραφές ασφαλείας της εγκατάστασής σας.
    </p>

    <!-- 3 Pillars of Excellence -->
    <div class="pillars-grid">
      <div class="pillar-card lime">
        <div class="pillar-icon">⚡</div>
        <div class="pillar-title">Εξειδίκευση Μ.Τ.</div>
        <div class="pillar-desc">Πολυετής εμπειρία & βαθιά τεχνογνωσία σε βιομηχανικούς υποσταθμούς μέσης τάσης.</div>
      </div>
      <div class="pillar-card orange">
        <div class="pillar-icon">🏆</div>
        <div class="pillar-title">Πιστοποίηση ISO</div>
        <div class="pillar-desc">Τυποποιημένες διαδικασίες ελέγχου βάσει διεθνούς προτύπου διαχείρισης ποιότητας ISO 9001:2015.</div>
      </div>
      <div class="pillar-card lime">
        <div class="pillar-icon">🔬</div>
        <div class="pillar-title">Σύγχρονος Έλεγχος</div>
        <div class="pillar-desc">Διακριβωμένα όργανα μετρήσεων υψηλής ακρίβειας.</div>
      </div>
    </div>
  </div>

  <div class="footer-bar">
    <div class="footer-co">
      <?= $runningFooterHtml ?>
    </div>
    <div class="footer-page">Σελίδα 2 από 6</div>
  </div>
</div>

<!-- ==================== PAGE 3: CERTIFICATE ==================== -->
<div class="page">
  <div class="header-bar">
    <?php if ($logoBase64): ?>
      <img src="<?= $logoBase64 ?>" class="header-logo" alt="Logo">
    <?php else: ?>
      <strong><?= htmlspecialchars($coDisplayName ?: $coName) ?></strong>
    <?php endif; ?>
    <div class="header-badge">ΠΙΣΤΟΠΟΙΗΣΗ ΠΟΙΟΤΗΤΑΣ</div>
  </div>

  <div style="flex: 1; display: flex; flex-direction: column; padding: 10px 0;">
    <div class="cert-header">
      <div class="cert-badge">Εγγύηση Ποιότητας & Ασφάλειας</div>
      <h2 class="cert-title">ΣΥΣΤΗΜΑ ΔΙΑΧΕΙΡΙΣΗΣ ΠΟΙΟΤΗΤΑΣ &mdash; ISO 9001:2015</h2>
      <div class="cert-sub">Πιστοποιημένες υπηρεσίες μελέτης, εγκατάστασης και συντήρησης υποσταθμών μέσης τάσης</div>
    </div>

    <div class="cert-frame">
      <?php if ($certBase64): ?>
        <img src="<?= $certBase64 ?>" alt="ISO 9001:2015 Certificate">
      <?php endif; ?>
    </div>
  </div>

  <div class="footer-bar">
    <div class="footer-co">
      <?= $runningFooterHtml ?>
    </div>
    <div class="footer-page">Σελίδα 3 από 6</div>
  </div>
</div>

<!-- ==================== PAGE 4: TASKS 2x2 GRID ==================== -->
<div class="page">
  <div class="header-bar">
    <?php if ($logoBase64): ?>
      <img src="<?= $logoBase64 ?>" class="header-logo" alt="Logo">
    <?php else: ?>
      <strong><?= htmlspecialchars($coDisplayName ?: $coName) ?></strong>
    <?php endif; ?>
    <div class="header-badge">ΤΕΧΝΙΚΕΣ ΕΡΓΑΣΙΕΣ</div>
  </div>

  <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; padding: 10px 0 4px 0;">
    <div class="tasks-top">
      <div>
        <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 3px 0;">ΕΡΓΑΣΙΕΣ ΣΥΝΤΗΡΗΣΗΣ ΥΠΟΣΤΑΘΜΟΥ</h2>
        <div style="font-size: 11.5px; color: #64748b;">Αναλυτικό πρωτόκολλο προληπτικού ελέγχου &amp; μετρήσεων εξοπλισμού Μέσης Τάσης</div>
      </div>
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 10px; font-weight: 800; color: #0f172a; padding: 4px 12px; border-radius: 20px;">
        SCOPE OF WORK
      </div>
    </div>

    <!-- 2x2 Quadrant Grid -->
    <div class="tasks-grid">
      <!-- 1. General Maintenance -->
      <div class="task-card orange">
        <div class="task-card-header orange">
          <div class="task-icon-circle orange">
            <svg class="task-icon-svg" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="m9 14 2 2 4-4"></path></svg>
          </div>
          <h3 class="task-title">1. ΓΕΝΙΚΗ ΣΥΝΤΗΡΗΣΗ</h3>
        </div>
        <ul class="task-list">
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Καθαριότητα στους χώρους Μ/Σ, κυψέλης Μ.Τ.</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος ύπαρξης πυροσβεστήρων στο χώρο Υ/Σ</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος αερισμού &amp; θερμοκρασίας χώρου Υ/Σ</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος φωτισμού ασφαλείας &amp; κυρίου φωτισμού Υ/Σ</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος ύπαρξης πινακίδων κινδύνου &amp; ασφαλείας</span>
          </li>
        </ul>
      </div>

      <!-- 2. Medium Voltage Switchgear -->
      <div class="task-card lime">
        <div class="task-card-header lime">
          <div class="task-icon-circle lime">
            <svg class="task-icon-svg" viewBox="0 0 24 24" fill="none" stroke="#65a30d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
          </div>
          <h3 class="task-title">2. ΣΥΝΤΗΡΗΣΗ ΣΤΗ ΜΕΣΗ ΤΑΣΗ &ndash; ΚΥΨΕΛΗ</h3>
        </div>
        <ul class="task-list">
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος ακροκιβωτίων εσωτερικού χώρου</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος μονωτήρων κυψέλης &amp; καθαρισμός</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Καθαρισμός εξαρτημάτων, επαφών &amp; συσφίξεις</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος λειτουργίας αφύγρανσης κυψέλης</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Μέτρηση μονώσεων καλωδίων Μ.Τ.</span>
          </li>
        </ul>
      </div>

      <!-- 3. Transformer Maintenance -->
      <div class="task-card orange">
        <div class="task-card-header orange">
          <div class="task-icon-circle orange">
            <svg class="task-icon-svg" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
          </div>
          <h3 class="task-title">3. ΣΥΝΤΗΡΗΣΗ ΣΤΟ ΜΕΤΑΣΧΗΜΑΤΙΣΤΗ</h3>
        </div>
        <ul class="task-list">
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος θερμοκρασίας λειτουργίας Μ/Σ</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος μονωτήρων Μ.Τ. &ndash; Χ.Τ. &amp; καθαρισμός</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος ακροκιβωτίων &amp; κελύφους Μ/Σ</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος στεγανότητας &amp; διαρροών ελαίου</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Καθαρισμός εξαρτημάτων &amp; συσφίξεις ακροδεκτών</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Μέτρηση τυλιγμάτων &amp; μονώσεων Μ/Σ</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Μέτρηση διηλεκτρικής αντοχής ελαίου</span>
          </li>
        </ul>
      </div>

      <!-- 4. Earthing & Protection -->
      <div class="task-card lime">
        <div class="task-card-header lime">
          <div class="task-icon-circle lime">
            <svg class="task-icon-svg" viewBox="0 0 24 24" fill="none" stroke="#65a30d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          </div>
          <h3 class="task-title">4. ΓΕΙΩΣΕΙΣ &ndash; ΠΡΟΣΤΑΣΙΑ</h3>
        </div>
        <ul class="task-list">
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Μέτρηση αντίστασης γείωσης (όπου εφικτό)</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος λειτουργίας αυτοματισμού προστασίας</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Έλεγχος διατάξεων αποζεύξεως &amp; ασφαλειών</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Τελικός έλεγχος ασφαλούς επαναφοράς</span>
          </li>
          <li>
            <svg class="check-icon" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Σύνταξη &amp; έκδοση τεχνικού πρωτοκόλλου</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="trust-banner">
      <strong>Τήρηση Διεθνών Προτύπων Ασφαλείας &amp; Προδιαγραφών Κατασκευαστή</strong><br/>
      <span>Όλες οι εργασίες και μετρήσεις εκτελούνται από εξειδικευμένους διπλωματούχους τεχνικούς με διακριβωμένο εξοπλισμό.</span>
    </div>
  </div>

  <div class="footer-bar">
    <div class="footer-co">
      <?= $runningFooterHtml ?>
    </div>
    <div class="footer-page">Σελίδα 4 από 6</div>
  </div>
</div>

<!-- ==================== PAGE 5: CLIENTS ==================== -->
<div class="page">
  <div class="header-bar">
    <?php if ($logoBase64): ?>
      <img src="<?= $logoBase64 ?>" class="header-logo" alt="Logo">
    <?php else: ?>
      <strong><?= htmlspecialchars($coDisplayName ?: $coName) ?></strong>
    <?php endif; ?>
    <div class="header-badge">ΕΜΠΕΙΡΙΑ & ΑΞΙΟΠΙΣΤΙΑ</div>
  </div>

  <div style="flex: 1; display: flex; flex-direction: column; padding: 12px 0;">
    <div class="clients-header">
      <div class="cert-badge" style="background:#fff7ed; color:#ea580c; border-color:#fed7aa;">Ενδεικτικό Πελατολόγιο</div>
      <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 4px 0 3px 0;">ΕΝΔΕΙΚΤΙΚΟ ΠΕΛΑΤΟΛΟΓΙΟ</h2>
      <div style="font-size: 12.5px; color: #64748b;">Η <?= htmlspecialchars($coDisplayName ?: $coName) ?> σας παρουσιάζει ενδεικτικά ένα μέρος από το πελατολόγιό της:</div>
    </div>

    <?php
    $clients = [
        'ΤΕΑΒ Α.Ε.', 'ΧΑΛΚΙΑΔΑΚΗΣ Super Markets', 'DAIOS COVE LUXURY RESORT', 'LYTTOS BEACH HOTEL',
        'IKAROS BEACH RESORT & SPA', 'ΔΕΥΑ ΗΡΑΚΛΕΙΟΥ', 'ENEL GREEN POWER', 'ENERCON',
        'KOSTA MARE RESORT', 'ΔΕΗ Α.Ε.', 'ΔΕΥΑ ΧΕΡΣΟΝΗΣΟΥ', 'ΟΤΕ Α.Ε.',
        'BLUE BAY RESORT', 'CRETA MARIS RESORT', 'GALAXY HOTEL', 'CANDIA MARIS HOTEL'
    ];
    ?>
    <div class="clients-grid">
      <?php foreach ($clients as $idx => $clientName): ?>
        <?php $isOrange = ($idx % 2 === 0); ?>
        <div class="client-pill <?= $isOrange ? 'orange' : 'lime' ?>">
          <div class="client-num"><?= $idx + 1 ?></div>
          <span><?= $clientName ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="trust-banner" style="border-left-color: #65a30d; margin-top: auto;">
      <strong style="font-size: 13px;">Είμαστε στη διάθεσή σας για οποιαδήποτε τεχνική ερώτηση ή διευκρίνιση.</strong><br/>
      <span>Τηλέφωνο: <?= htmlspecialchars($topPhone) ?> &bull; Email: <?= htmlspecialchars($topEmail) ?> &bull; Website: <?= htmlspecialchars($topWeb) ?></span>
    </div>
  </div>

  <div class="footer-bar">
    <div class="footer-co">
      <?= $runningFooterHtml ?>
    </div>
    <div class="footer-page">Σελίδα 5 από 6</div>
  </div>
</div>

<!-- ==================== PAGE 6: FINANCIAL & SIGNATURE ==================== -->
<div class="page">
  <div class="header-bar">
    <?php if ($logoBase64): ?>
      <img src="<?= $logoBase64 ?>" class="header-logo" alt="Logo">
    <?php else: ?>
      <strong><?= htmlspecialchars($coName) ?></strong>
    <?php endif; ?>
    <div class="header-badge">ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ</div>
  </div>

  <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; padding: 16px 0 8px 0;">
    <div class="finance-header">
      <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0 0 5px 0; text-transform: uppercase;">ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ</h2>
      <div style="font-size: 15.5px; font-weight: 800; color: #ea580c;"><?= $company ?></div>
    </div>

    <!-- Executive Pricing Table -->
    <div class="pricing-table-card">
      <table class="pricing-table">
        <thead>
          <tr>
            <th>ΠΕΡΙΓΡΑΦΗ ΕΡΓΑΣΙΩΝ ΣΥΝΤΗΡΗΣΗΣ</th>
            <th>ΚΑΘΑΡΗ ΑΞΙΑ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Σύνολο Εργασιών Συντήρησης Υ/Σ (χωρίς Φ.Π.Α.)</td>
            <td><?= number_format($priceVal, 2, ',', '.') ?> €</td>
          </tr>
          <tr class="vat-row">
            <td>Φ.Π.Α. (24%)</td>
            <td><?= number_format($vatVal, 2, ',', '.') ?> €</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Grand Total Banner -->
    <div class="grand-total-banner">
      <div class="grand-total-label">ΤΕΛΙΚΟ ΣΥΝΟΛΟ ΜΕ Φ.Π.Α.:</div>
      <div class="grand-total-val"><?= number_format($priceWithVat, 2, ',', '.') ?> €</div>
    </div>

    <!-- Terms Card -->
    <div class="terms-card">
      <div class="terms-card-title">Οικονομικοί Όροι & Στοιχεία Προσφοράς</div>
      <div style="margin-bottom: 8px;">
        <strong>Αντικείμενο:</strong> <?= $offerTitle ?>
      </div>
      <div>
        <?= !empty($terms) ? nl2br(htmlspecialchars($terms)) : 'Στις παραπάνω τιμές περιλαμβάνεται η εκτέλεση όλων των περιγραφόμενων εργασιών και η παράδοση πλήρους επίσημου πρωτοκόλλου συντήρησης.' ?>
      </div>
      <?php if (!empty($notes)): ?>
        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #cbd5e1;">
          <strong>Παρατηρήσεις:</strong> <?= nl2br(htmlspecialchars($notes)) ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Signatures Grid -->
    <div class="signatures-grid">
      <div class="sig-col-left">
        <div style="color: #64748b; font-size: 12.5px;">Εκ μέρους της εταιρείας,</div>
        <div style="color: #0f172a; font-size: 15px; font-weight: 800; margin-bottom: 4px;">Με εκτίμηση,</div>
        <?php if ($signBase64): ?>
          <img src="<?= $signBase64 ?>" class="sig-img" alt="Signature">
        <?php else: ?>
          <div style="height: 60px;"></div>
        <?php endif; ?>
        <div style="color: #0f172a; font-size: 14.5px; font-weight: 800;"><?= htmlspecialchars($legalRepFormatted) ?></div>
        <div style="color: #64748b; font-size: 12px;">Ηλεκτρολόγος Μηχανικός ΤΕ</div>
      </div>

      <div class="acceptance-box">
        <div>
          <div class="acceptance-title">Αποδοχή Προσφοράς</div>
          <div class="acceptance-sub">Υπογραφή & Σφραγίδα Πελάτη</div>
        </div>
        <div class="acceptance-date">
          Ημερομηνία: _____ / _____ / 202___
        </div>
      </div>
    </div>
  </div>

  <div class="footer-bar">
    <div class="footer-co">
      <?= $runningFooterHtml ?>
    </div>
    <div class="footer-page">Σελίδα 6 από 6</div>
  </div>
</div></body>
</html>
        <?php
        return ob_get_clean();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // VIEW & PRINT OFFER (6-Page High-Fidelity Document -> Print to PDF)
    // ──────────────────────────────────────────────────────────────────────────
    public function exportPDF(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        $html = $this->buildOfferHtml($offer);

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $html;
        exit;
    }


    // ──────────────────────────────────────────────────────────────────────────
    // Build 6-page Offer PDF matching the target template layout and images
    // ──────────────────────────────────────────────────────────────────────────
    public function buildOfferPDF(array $offer): CustomOfferPDF {
        $co = $this->getCompanySettings();

        $companyRaw  = trim($offer['company_name'] ?? '');
        $company     = htmlspecialchars($companyRaw);
        $offerNum    = htmlspecialchars($offer['offer_number'] ?? '');
        $today       = !empty($offer['created_at']) ? date('d/m/Y', strtotime($offer['created_at'])) : date('d/m/Y');
        $transCount  = max(1, (int)($offer['transformers_count'] ?? 1));
        $priceVal    = (float)($offer['price'] ?? 0);
        $vatVal       = $priceVal * 0.24;
        $priceWithVat = $priceVal + $vatVal;
        $notes        = trim($offer['notes'] ?? '');
        $terms        = trim($offer['terms'] ?? '');

        // Resolve Transformer Power rating (e.g. '630 kVA' or '500 kVA & 630 kVA')
        $transPower = trim($offer['transformer_power'] ?? '');
        if ($transPower === '') {
            $textPool = ($offer['title'] ?? '') . ' ' . ($offer['notes'] ?? '');
            if (preg_match('/\b(\d{2,4}\s*(?:kVA|kva|KVA)(?:\s*(?:&|,|\+)\s*\d{2,4}\s*(?:kVA|kva|KVA))*)\b/u', $textPool, $matches)) {
                $transPower = $matches[1];
            }
        }
        if ($transPower === '') {
            if ($transCount === 1) {
                $transPower = '630 kVA';
            } elseif ($transCount === 2) {
                $transPower = '500 kVA & 630 kVA';
            } else {
                $transPower = $transCount . 'x 630 kVA';
            }
        }

        // Title / Scenario resolution with fallback & company inclusion
        $customTitle = trim($offer['title'] ?? '');
        if (!empty($customTitle)) {
            // If custom title does not already contain the company name, append it
            if ($companyRaw !== '' && mb_stripos($customTitle, $companyRaw) === false) {
                $offerTitle = htmlspecialchars($customTitle . ' - ' . $companyRaw);
            } else {
                $offerTitle = htmlspecialchars($customTitle);
            }
        } else {
            $offerTitle = 'Προσφορά ΣΥΝΤΗΡΗΣΗΣ Υ/Σ Μέσης Τάσης στο ' . $company . ' (' . $transCount . 'Μ/Σ)';
        }

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

        $transPath   = $getImage('substation_hero_mockup.jpeg', 'image2.jpeg');
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
        $pdf->SetTitle('Οικονομική Προσφορά ' . $offerNum . ($companyRaw !== '' ? ' - ' . $companyRaw : ''));

        $pdf->SetMargins(20, 24, 20);
        $pdf->SetHeaderMargin(8);
        $pdf->SetFooterMargin(20);
        $pdf->SetAutoPageBreak(false);

        // Contacts at top of Page 1 from Settings
        $topWeb   = !empty($co['company_website']) ? $co['company_website'] : 'https://ecowatt-energy.gr';
        $topEmail = !empty($co['company_email']) ? $co['company_email'] : 'info@ecowatt-energy.gr';
        $topPhone = !empty($co['company_phone']) ? $co['company_phone'] : '2811 113851';

        // ================= PAGE 1: COVER (MODERN MOCKUP ALIGNED) =================
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        if ($logoPath && file_exists($logoPath)) {
            $pdf->Image($logoPath, 20, 9, 62, 0, 'JPEG');
        }

        // Top-right clean meta block
        $p1TopMetaHtml = '
        <table width="100%" cellpadding="2" cellspacing="0" style="font-family:dejavusans; font-size:8pt; text-align:right;">
          <tr>
            <td>
              <span style="color:#ea580c; font-weight:bold; font-size:8.5pt;">ΑΡ. ΠΡΟΣΦΟΡΑΣ: ' . $offerNum . '</span> &nbsp;|&nbsp; 
              <span style="color:#64748b; font-weight:bold;">' . $today . '</span>
            </td>
          </tr>
          <tr>
            <td style="color:#64748b; font-size:7.5pt; padding-top:2px;">
              ' . htmlspecialchars($topWeb) . ' &bull; ' . htmlspecialchars($topEmail) . '
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(90, 0, 100, 10, $p1TopMetaHtml, 0, 1, false, true, 'R');

        // Clean subtle two-tone divider line at Y=23
        $pdf->SetDrawColor(132, 204, 22); // Lime #84cc16
        $pdf->SetLineWidth(0.6);
        $pdf->Line(20, 23, 105, 23);
        $pdf->SetDrawColor(249, 115, 22); // Orange #f97316
        $pdf->Line(105, 23, 190, 23);

        // Prominent Modern Title Header towards top (Y=26)
        $p1HeaderHtml = '
        <table width="100%" cellpadding="0" cellspacing="0" style="font-family:dejavusans; text-align:center;">
          <tr>
            <td style="font-size:13.5pt; font-weight:bold; color:#0f172a; letter-spacing:0.5px;">
              ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ
            </td>
          </tr>
          <tr>
            <td style="font-size:11.5pt; font-weight:bold; color:#ea580c; padding-top:2px;">
              ' . $company . '
            </td>
          </tr>
          <tr>
            <td style="font-size:9.5pt; font-weight:bold; color:#65a30d; padding-top:2px;">
              ΣΥΝΤΗΡΗΣΗ ΥΠΟΣΤΑΘΜΟΥ ΜΕΣΗΣ ΤΑΣΗΣ
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 26, $p1HeaderHtml, 0, 1, false, true, '');

        // Large High-Impact Transformer Hero Photo Card (centered at Y=49, width 142mm)
        if ($transPath && file_exists($transPath)) {
            $pdf->SetDrawColor(226, 232, 240); // Soft border #e2e8f0
            $pdf->SetLineWidth(0.4);
            $pdf->SetFillColor(248, 250, 252);
            $pdf->RoundedRect(32, 47, 146, 88, 3, '1111', 'DF');
            $pdf->Image($transPath, 34, 49, 142, 0, 'JPEG');
        }

        // Scenario / Offer Title Callout below image (Y=143)
        $scenarioPrefix = (mb_stripos($offerTitle, 'Σενάριο') === 0 || mb_stripos($offerTitle, 'Σεναριο') === 0) ? '' : 'Σενάριο: ';
        $scenarioHtml = '
        <table width="100%" cellpadding="6" cellspacing="0" style="font-family:dejavusans; background-color:#f8fafc; border-left:4px solid #ea580c; border-top:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
          <tr>
            <td align="center" style="font-size:9pt; font-weight:bold; color:#0f172a;">
              ' . $scenarioPrefix . $offerTitle . '
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 143, $scenarioHtml, 0, 1, false, true, '');

        // Clean 2-column Metadata below scenario (Y=160)
        $p1MetaHtml = '
        <table width="100%" cellpadding="6" cellspacing="0" style="font-family:dejavusans; background-color:#ffffff; border:1px solid #e2e8f0; font-size:8.5pt; line-height:1.45; color:#1e293b;">
          <tr>
            <td width="50%" style="border-right:1px solid #e2e8f0;">
              <b style="color:#ea580c; font-size:8.5pt;">ΣΤΟΙΧΕΙΑ ΠΕΛΑΤΗ</b><br/>
              <b>Επωνυμία:</b> ' . $company . '<br/>
              ' . (!empty($offer['address']) ? '<b>Διεύθυνση:</b> ' . htmlspecialchars($offer['address']) . '<br/>' : '') . '
              ' . (!empty($offer['phone']) ? '<b>Τηλέφωνο:</b> ' . htmlspecialchars($offer['phone']) . '<br/>' : '') . '
              ' . (!empty($offer['email']) ? '<b>Email:</b> ' . htmlspecialchars($offer['email']) : '') . '
            </td>
            <td width="50%">
              <b style="color:#65a30d; font-size:8.5pt;">ΣΤΟΙΧΕΙΑ ΠΡΟΣΦΟΡΑΣ</b><br/>
              <b>Αρ. Προσφοράς:</b> ' . $offerNum . '<br/>
              <b>Ημερομηνία:</b> ' . $today . '<br/>
              <b>Εξοπλισμός:</b> ' . $transCount . ' Μετασχηματιστής/ές Μ.Τ.<br/>
              <b>Ισχύς (kVA):</b> ' . htmlspecialchars($transPower) . '<br/>
              <b>Ισχύς Προσφοράς:</b> ' . (!empty($offer['offer_expiry_date']) ? date('d/m/Y', strtotime($offer['offer_expiry_date'])) : '30 ημέρες') . '
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 160, $p1MetaHtml, 0, 1, false, true, '');

        // ================= PAGE 2: LETTER =================
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        $p2Html = '
        <div style="font-family:dejavusans; font-size:9pt; line-height:1.65; color:#1e293b;">
          <p style="font-size:10.5pt; font-weight:bold; color:#0f172a; margin-bottom:14px;">Αξιότιμοι κύριοι,</p>
          
          <p style="margin-bottom:16px;">
            Θα θέλαμε να σας ευχαριστήσουμε θερμά για το ενδιαφέρον σας σχετικά με τις παρεχόμενες τεχνικές υπηρεσίες και τον εξοπλισμό της εταιρείας μας. Σας διαβεβαιώνουμε ότι παραμένουμε πάντα στη διάθεσή σας για να σας προσφέρουμε υψηλής ποιότητας, ασφαλείς και προηγμένης τεχνολογίας λύσεις.
          </p>

          <!-- Title Callout Box with Orange Accent -->
          <table width="100%" cellpadding="8" cellspacing="0" style="background-color:#f8fafc; border-left:4px solid #ea580c; border-top:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; margin-bottom:18px;">
            <tr>
              <td>
                <span style="font-size:7.5pt; color:#ea580c; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">ΑΝΤΙΚΕΙΜΕΝΟ ΤΕΧΝΙΚΗΣ ΠΡΟΣΦΟΡΑΣ</span><br/>
                <span style="font-size:10.5pt; font-weight:bold; color:#0f172a;">' . $offerTitle . '</span>
              </td>
            </tr>
          </table>

          <p style="margin-bottom:20px;">
            Η περιγραφή των εργασιών που ακολουθεί συντάχθηκε βάσει της πολυετούς εμπειρίας και της εξειδικευμένης τεχνογνωσίας που διαθέτει η εταιρεία μας στους Υποσταθμούς Μέσης Τάσης (Μ.Τ.). Μελετούμε, σχεδιάζουμε και υλοποιούμε κάθε έργο σύμφωνα με τις ιδιαίτερες τεχνικές απαιτήσεις και τις προδιαγραφές ασφαλείας της εγκατάστασής σας.
          </p>

          <!-- 3 Pillars of Excellence -->
          <table width="100%" cellpadding="8" cellspacing="5" style="margin-top:16px;">
            <tr>
              <td width="33%" style="background-color:#f8fafc; border-top:3px solid #65a30d; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; font-size:7.5pt; line-height:1.45;">
                <b style="color:#0f172a; font-size:8.5pt;">✔ Εξειδίκευση Μ.Τ.</b><br/>
                <span style="color:#64748b;">Πολυετής εμπειρία &amp; βαθιά τεχνογνωσία σε βιομηχανικούς υποσταθμούς.</span>
              </td>
              <td width="34%" style="background-color:#f8fafc; border-top:3px solid #ea580c; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; font-size:7.5pt; line-height:1.45;">
                <b style="color:#0f172a; font-size:8.5pt;">✔ Πιστοποίηση ISO</b><br/>
                <span style="color:#64748b;">Τυποποιημένες διαδικασίες ελέγχου βάσει διεθνούς προτύπου ISO 9001:2015.</span>
              </td>
              <td width="33%" style="background-color:#f8fafc; border-top:3px solid #65a30d; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; font-size:7.5pt; line-height:1.45;">
                <b style="color:#0f172a; font-size:8.5pt;">✔ Σύγχρονος Έλεγχος</b><br/>
                <span style="color:#64748b;">Διακριβωμένα όργανα μετρήσεων υψηλής ακρίβειας.</span>
              </td>
            </tr>
          </table>
        </div>';
        $pdf->writeHTMLCell(170, 0, 20, 28, $p2Html, 0, 1, false, true, '');

        // ================= PAGE 3: CERTIFICATE =================
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        $p3SubHeaderHtml = '
        <table width="100%" cellpadding="3" cellspacing="0" style="font-family:dejavusans; text-align:center; margin-bottom:6px;">
          <tr>
            <td style="background-color:#f8fafc; border:1px solid #e2e8f0; border-top:2px solid #65a30d;">
              <span style="font-size:9.5pt; font-weight:bold; color:#0f172a;">ΣΥΣΤΗΜΑ ΔΙΑΧΕΙΡΙΣΗΣ ΠΟΙΟΤΗΤΑΣ &mdash; ISO 9001:2015</span><br/>
              <span style="font-size:7.5pt; color:#64748b;">Πιστοποιημένες υπηρεσίες μελέτης, εγκατάστασης και συντήρησης υποσταθμών</span>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 24, $p3SubHeaderHtml, 0, 1, false, true, '');

        if ($isoCertPath && file_exists($isoCertPath)) {
            $pdf->Image($isoCertPath, 30, 36, 150, 0, 'JPEG');
        }

        // ================= PAGE 4: MAINTENANCE TASKS (2x2 Grid) =================
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        $p4HeaderHtml = '
        <table width="100%" cellpadding="2" cellspacing="0" style="font-family:dejavusans; text-align:center;">
          <tr>
            <td>
              <span style="font-size:11.5pt; font-weight:bold; color:#0f172a;">ΕΡΓΑΣΙΕΣ ΣΥΝΤΗΡΗΣΗΣ ΥΠΟΣΤΑΘΜΟΥ</span><br/>
              <span style="font-size:8pt; color:#64748b;">Αναλυτικό πρωτόκολλο προληπτικού ελέγχου &amp; μετρήσεων εξοπλισμού Μέσης Τάσης</span>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 25, $p4HeaderHtml, 0, 1, false, true, '');

        $p4GridHtml = '
        <table width="100%" cellpadding="6" cellspacing="6" style="font-family:dejavusans; font-size:8pt; line-height:1.45;">
          <!-- ROW 1 -->
          <tr>
            <td width="50%" valign="top" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-top:3px solid #ea580c;">
              <div style="font-weight:bold; font-size:8.5pt; color:#ea580c; margin-bottom:5px;">
                1. ΓΕΝΙΚΗ ΣΥΝΤΗΡΗΣΗ
              </div>
              <div style="color:#1e293b;">
                &bull; Καθαριότητα στους χώρους Μ/Σ, κυψέλης Μ.Τ.<br/>
                &bull; Έλεγχος ύπαρξης πυροσβεστήρων στο χώρο Υ/Σ<br/>
                &bull; Έλεγχος εξαερισμού &amp; θερμοκρασίας Υ/Σ<br/>
                &bull; Έλεγχος φωτισμού στον χώρο του Υ/Σ<br/>
                &bull; Έλεγχος ύπαρξης πινακίδων ασφαλείας &amp; κινδύνου
              </div>
            </td>
            <td width="50%" valign="top" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-top:3px solid #65a30d;">
              <div style="font-weight:bold; font-size:8.5pt; color:#65a30d; margin-bottom:5px;">
                2. ΣΥΝΤΗΡΗΣΗ ΣΤΗ ΜΕΣΗ ΤΑΣΗ &ndash; ΚΥΨΕΛΗ
              </div>
              <div style="color:#1e293b;">
                &bull; Έλεγχος ακροκιβωτίων εσωτερικού χώρου<br/>
                &bull; Έλεγχος μονωτήρων κυψέλης &amp; καθαρισμός<br/>
                &bull; Καθαρισμός εξαρτημάτων, επαφών &amp; συσφίξεις<br/>
                &bull; Έλεγχος λειτουργίας αφύγρανσης κυψέλης<br/>
                &bull; Μέτρηση μονώσεων καλωδίων Μ.Τ.
              </div>
            </td>
          </tr>
          <!-- ROW 2 -->
          <tr>
            <td width="50%" valign="top" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-top:3px solid #ea580c;">
              <div style="font-weight:bold; font-size:8.5pt; color:#ea580c; margin-bottom:5px;">
                3. ΣΥΝΤΗΡΗΣΗ ΣΤΟ ΜΕΤΑΣΧΗΜΑΤΙΣΤΗ
              </div>
              <div style="color:#1e293b;">
                &bull; Έλεγχος θερμοκρασίας λειτουργίας Μ/Σ<br/>
                &bull; Έλεγχος μονωτήρων Μ.Τ. &ndash; Χ.Τ. &amp; καθαρισμός<br/>
                &bull; Έλεγχος ακροκιβωτίων &amp; κελύφους Μ/Σ<br/>
                &bull; Καθαρισμός εξαρτημάτων &amp; συσφίξεις ακροδεκτών<br/>
                &bull; Μέτρηση τυλιγμάτων &amp; μονώσεων Μ/Σ<br/>
                &bull; Μέτρηση διηλεκτρικής αντοχής ελαίου
              </div>
            </td>
            <td width="50%" valign="top" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-top:3px solid #65a30d;">
              <div style="font-weight:bold; font-size:8.5pt; color:#65a30d; margin-bottom:5px;">
                4. ΓΕΙΩΣΕΙΣ &ndash; ΠΡΟΣΤΑΣΙΑ
              </div>
              <div style="color:#1e293b;">
                &bull; Μέτρηση αντίστασης γείωσης (όπου εφικτό)<br/>
                &bull; Έλεγχος λειτουργίας αυτοματισμού προστασίας<br/>
                &bull; Έλεγχος διατάξεων αποζεύξεως &amp; ασφαλειών<br/>
                &bull; Τελικός έλεγχος ασφαλούς επαναφοράς<br/>
                &bull; Σύνταξη &amp; έκδοση τεχνικού πρωτοκόλλου
              </div>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 36, $p4GridHtml, 0, 1, false, true, '');

        $p4TrustHtml = '
        <table width="100%" cellpadding="6" cellspacing="0" style="font-family:dejavusans; background-color:#f8fafc; border-left:4px solid #ea580c; border-top:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
          <tr>
            <td align="center" style="font-size:8pt; color:#1e293b;">
              <b style="color:#0f172a;">Τήρηση Διεθνών Προτύπων Ασφαλείας &amp; Προδιαγραφών Κατασκευαστή</b><br/>
              <span style="font-size:7.5pt; color:#64748b;">Όλες οι εργασίες και μετρήσεις εκτελούνται από εξειδικευμένους διπλωματούχους τεχνικούς με διακριβωμένο εξοπλισμό.</span>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 134, $p4TrustHtml, 0, 1, false, true, '');

        // ================= PAGE 5: CLIENTS =================
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        $p5IntroCo = !empty($co['company_name']) ? $co['company_name'] : (!empty($co['company_display_name']) ? $co['company_display_name'] : 'εταιρεία μας');
        $p5HeaderHtml = '
        <table width="100%" cellpadding="2" cellspacing="0" style="font-family:dejavusans; text-align:center;">
          <tr>
            <td>
              <span style="font-size:11.5pt; font-weight:bold; color:#0f172a;">ΕΝΔΕΙΚΤΙΚΟ ΠΕΛΑΤΟΛΟΓΙΟ</span><br/>
              <span style="font-size:8pt; color:#64748b;">Η ' . htmlspecialchars($p5IntroCo) . ' σας παρουσιάζει ενδεικτικά ένα μέρος από το πελατολόγιό της:</span>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 25, $p5HeaderHtml, 0, 1, false, true, '');

        // 16 Clients in a modern 2-column table with pill-cards
        $clients = [
            '1. ΤΕΑΒ Α.Ε.',
            '2. ΧΑΛΚΙΑΔΑΚΗΣ Super Markets',
            '3. DAIOS COVE LUXURY RESORT',
            '4. LYTTOS BEACH HOTEL',
            '5. IKAROS BEACH RESORT & SPA',
            '6. ΔΕΥΑ ΗΡΑΚΛΕΙΟΥ',
            '7. ENEL GREEN POWER',
            '8. ENERCON',
            '9. KOSTA MARE RESORT',
            '10. ΔΕΗ Α.Ε.',
            '11. ΔΕΥΑ ΧΕΡΣΟΝΗΣΟΥ',
            '12. ΟΤΕ Α.Ε.',
            '13. BLUE BAY RESORT',
            '14. CRETA MARIS RESORT',
            '15. GALAXY HOTEL',
            '16. CANDIA MARIS HOTEL',
        ];

        $p5GridHtml = '<table width="100%" cellpadding="5" cellspacing="4" style="font-family:dejavusans; font-size:8.5pt;">';
        for ($i = 0; $i < 8; $i++) {
            $leftClient  = $clients[$i];
            $rightClient = $clients[$i + 8];
            $p5GridHtml .= '
            <tr>
              <td width="50%" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-left:3px solid #ea580c; font-weight:bold; color:#1e293b;">
                ' . $leftClient . '
              </td>
              <td width="50%" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-left:3px solid #65a30d; font-weight:bold; color:#1e293b;">
                ' . $rightClient . '
              </td>
            </tr>';
        }
        $p5GridHtml .= '</table>';
        $pdf->writeHTMLCell(170, 0, 20, 36, $p5GridHtml, 0, 1, false, true, '');

        // Availability callout card
        $p5TrustHtml = '
        <table width="100%" cellpadding="6" cellspacing="0" style="font-family:dejavusans; background-color:#f8fafc; border-left:4px solid #65a30d; border-top:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
          <tr>
            <td align="center" style="font-size:8.5pt; color:#1e293b;">
              <b style="color:#0f172a;">Είμαστε στη διάθεσή σας για οποιαδήποτε τεχνική ερώτηση ή διευκρίνιση.</b><br/>
              <span style="font-size:7.5pt; color:#64748b;">Τηλέφωνο: ' . htmlspecialchars($topPhone) . ' &bull; Email: ' . htmlspecialchars($topEmail) . ' &bull; Website: ' . htmlspecialchars($topWeb) . '</span>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 142, $p5TrustHtml, 0, 1, false, true, '');

        // ================= PAGE 6: FINANCIAL OFFER & SIGNATURES =================
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);
        
        $p6HeaderHtml = '
        <table width="100%" cellpadding="0" cellspacing="0" style="font-family:dejavusans; text-align:center; margin-bottom:6px;">
          <tr><td style="font-size:12.5pt; font-weight:bold; color:#0f172a;">ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ</td></tr>
          <tr><td style="font-size:9.5pt; font-weight:bold; color:#ea580c; padding-top:2px;">' . $company . '</td></tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 25, $p6HeaderHtml, 0, 1, false, true, '');

        // Executive Pricing Table with integrated Grand Total orange row
        $p6PricingHtml = '
        <table border="1" cellpadding="7" cellspacing="0" width="100%" style="font-family:dejavusans; border-collapse:collapse; font-size:8.5pt; border:1px solid #cbd5e1;">
          <tr style="background-color:#0f172a; color:#ffffff; font-weight:bold;">
            <td width="70%">ΠΕΡΙΓΡΑΦΗ ΕΡΓΑΣΙΩΝ ΣΥΝΤΗΡΗΣΗΣ</td>
            <td width="30%" align="right">ΚΑΘΑΡΗ ΑΞΙΑ</td>
          </tr>
          <tr>
            <td style="color:#1e293b;">ΣΥΝΟΛΟ ΕΡΓΑΣΙΩΝ ΣΥΝΤΗΡΗΣΗΣ Υ/Σ (χωρίς Φ.Π.Α.)</td>
            <td align="right" style="color:#0f172a; font-weight:bold;">' . number_format($priceVal, 2, ',', '.') . ' €</td>
          </tr>
          <tr style="background-color:#f8fafc;">
            <td style="color:#64748b;">Φ.Π.Α. (24%)</td>
            <td align="right" style="color:#64748b; font-weight:bold;">' . number_format($vatVal, 2, ',', '.') . ' €</td>
          </tr>
          <tr style="background-color:#ea580c; color:#ffffff; font-weight:bold;">
            <td style="font-size:9.5pt; color:#ffffff;">ΤΕΛΙΚΟ ΣΥΝΟΛΟ ΜΕ Φ.Π.Α.:</td>
            <td align="right" style="font-size:11pt; color:#ffffff;">' . number_format($priceWithVat, 2, ',', '.') . ' €</td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, 36, $p6PricingHtml, 0, 1, false, true, '');

        $afterTableY = $pdf->GetY() + 6;

        // Terms & Title Card
        $termsContent = !empty($terms) ? nl2br(htmlspecialchars($terms)) : 'Στις παραπάνω τιμές περιλαμβάνεται η εκτέλεση όλων των περιγραφόμενων εργασιών και η παράδοση πλήρους επίσημου πρωτοκόλλου συντήρησης.';
        $notesContent = $notes ? '<br/><br/><b>Παρατηρήσεις:</b> ' . nl2br(htmlspecialchars($notes)) : '';
        $p6TermsHtml = '
        <table width="100%" cellpadding="7" cellspacing="0" style="font-family:dejavusans; background-color:#f8fafc; border-left:4px solid #65a30d; border-top:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;">
          <tr>
            <td style="font-size:8pt; line-height:1.5; color:#1e293b;">
              <span style="font-size:8.5pt; font-weight:bold; color:#65a30d;">ΟΙΚΟΝΟΜΙΚΟΙ ΟΡΟΙ &amp; ΣΤΟΙΧΕΙΑ ΠΡΟΣΦΟΡΑΣ</span><br/>
              <b>Αντικείμενο:</b> ' . $offerTitle . '<br/><br/>
              ' . $termsContent . '
              ' . $notesContent . '
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, $afterTableY, $p6TermsHtml, 0, 1, false, true, '');

        // Signatures block at dynamic Y position
        $currentY = $pdf->GetY();
        $signY = max(152, $currentY + 8);
        if ($signY > 205) {
            $signY = 205;
        }

        $p6SignHtml = '
        <table width="100%" cellpadding="0" cellspacing="0" style="font-family:dejavusans; font-size:8.5pt; line-height:1.5;">
          <tr>
            <td width="55%" valign="top">
              <span style="color:#64748b; font-size:8pt;">Εκ μέρους της εταιρείας,</span><br/>
              <b style="color:#0f172a; font-size:9pt;">Με εκτίμηση,</b><br/><br/>
              ' . ($signPath && file_exists($signPath) ? '<img src="' . $signPath . '" width="105" /><br/>' : '<br/><br/>') . '
              <b style="color:#0f172a;">Σφακιανάκης Κωνσταντίνος</b><br/>
              <span style="color:#64748b; font-size:8pt;">Ηλεκτρολόγος Μηχανικός ΤΕ</span>
            </td>
            <td width="45%" valign="top">
              <table width="100%" cellpadding="8" cellspacing="0" style="border:1.5px dashed #ea580c; background-color:#fffcf7; text-align:center;">
                <tr>
                  <td>
                    <span style="font-size:9pt; font-weight:bold; color:#ea580c;">ΑΠΟΔΟΧΗ ΠΡΟΣΦΟΡΑΣ</span><br/>
                    <span style="font-size:7.5pt; color:#64748b;">Υπογραφή &amp; Σφραγίδα Πελάτη</span><br/><br/><br/>
                    <span style="font-size:7.5pt; color:#94a3b8;">Ημερομηνία: _____ / _____ / 202___</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>';
        $pdf->writeHTMLCell(170, 0, 20, $signY, $p6SignHtml, 0, 1, false, true, '');

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

        $filename = $this->buildOfferFilename($offer, 'docx');
        $encodedFilename = rawurlencode($filename);

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $encodedFilename . '"; filename*=UTF-8\'\'' . $encodedFilename);
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

                // Generate PDF attachment via Puppeteer (in-memory) or TCPDF fallback
                $pdfFilename    = $this->buildOfferFilename($offer, 'pdf');
                $attachmentPath = sys_get_temp_dir() . '/offer_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $offer['offer_number']) . '.pdf';

                $html = $this->buildOfferHtml($offer);
                $pdfBinary = $this->generateOfferPdfWithPuppeteer($html);
                if ($pdfBinary !== null && strlen($pdfBinary) > 1000) {
                    file_put_contents($attachmentPath, $pdfBinary);
                } else {
                    $pdfMail = $this->buildOfferPDF($offer);
                    $pdfMail->Output($attachmentPath, 'F');
                }

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
