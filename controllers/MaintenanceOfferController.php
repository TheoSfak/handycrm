<?php

require_once 'models/MaintenanceOffer.php';

// Load TCPDF only once
if (!class_exists('TCPDF')) {
    require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
}

/**
 * Custom TCPDF class for Maintenance Offer PDF
 */
if (!class_exists('CustomOfferPDF')) {
    class CustomOfferPDF extends TCPDF {
        public static $footerText = '';
        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('dejavusans', '', 8);
            $this->SetFillColor(240, 240, 240);
            $this->Rect(0, $this->GetY(), $this->getPageWidth(), 15, 'F');
            $this->SetLineWidth(0.5);
            $this->SetDrawColor(0, 102, 204);
            $this->Line(10, $this->GetY(), $this->getPageWidth() - 10, $this->GetY());
            $this->SetY(-12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(0, 5, self::$footerText, 0, 1, 'C');
            $this->SetY(-8);
            $this->SetFont('dejavusans', 'I', 7);
            $this->Cell(0, 3, 'Σελίδα ' . $this->getAliasNumPage() . ' από ' . $this->getAliasNbPages(), 0, 0, 'C');
        }
    }
}

class MaintenanceOfferController extends BaseController {

    private MaintenanceOffer $offerModel;
    private const ITEMS_PER_PAGE = 20;

    private static function buildPdfFooterText(): string {
        try {
            $name    = Settings::get('company_name',    'ECOWATT Ενεργειακές Λύσεις');
            $website = Settings::get('company_website', 'ecowatt-energy.gr');
            $email   = Settings::get('company_email',   'info@ecowatt-energy.gr');
            $phone   = Settings::get('company_phone',   '');
        } catch (\Exception $e) {
            $name = 'ECOWATT Ενεργειακές Λύσεις'; $website = 'ecowatt-energy.gr';
            $email = 'info@ecowatt-energy.gr'; $phone = '';
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
    // EXPORT PDF (TCPDF — full template layout with images)
    // ──────────────────────────────────────────────────────────────────────────
    public function exportPDF(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        CustomOfferPDF::$footerText = self::buildPdfFooterText();
        $pdf = new CustomOfferPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor('ECOWATT - Κώστας Γ. Σφακιανάκης & ΣΙΑ Ο.Ε.');
        $pdf->SetTitle('Οικονομική Προσφορά ' . $offer['offer_number']);
        $pdf->SetPrintHeader(false);
        $pdf->SetFont('dejavusans', '', 10, '', true);
        $pdf->SetMargins(20, 15, 20);
        $pdf->SetFooterMargin(15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();
        $pdf->writeHTML($this->generateOfferHTML($offer), true, false, true, false, '');

        $filename = 'Prosfora_Sintirisis_' . preg_replace('/[^a-zA-Z0-9_-]/u', '_', $offer['offer_number']) . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Generate HTML content matching the Word template layout (with images)
    // ──────────────────────────────────────────────────────────────────────────
    private function generateOfferHTML(array $offer): string {
        $company    = htmlspecialchars($offer['company_name']);
        $offerNum   = htmlspecialchars($offer['offer_number']);
        $today      = date('d/m/Y');
        $expiry     = $offer['offer_expiry_date'] ? date('d/m/Y', strtotime($offer['offer_expiry_date'])) : '-';
        $transCount = (int)$offer['transformers_count'];
        $priceNum   = number_format((float)$offer['price'], 2, ',', '.') . ' €';
        $notes      = nl2br(htmlspecialchars($offer['notes'] ?? ''));

        // Load company info from settings (with fallback)
        try {
            $coName    = Settings::get('company_name',         'ΚΩΣΤΑΣ Γ. ΣΦΑΚΙΑΝΑΚΗΣ & ΣΙΑ Ο.Ε.');
            $coDisplay = Settings::get('company_display_name', $coName);
            $coEmail   = Settings::get('company_email',        'info@ecowatt-energy.gr');
            $coPhone   = Settings::get('company_phone',        '');
            $coWebsite = Settings::get('company_website',      'ecowatt-energy.gr');
            $coTaxId   = Settings::get('company_tax_id',       '');
            $coAddress = Settings::get('company_address',      '');
        } catch (\Exception $e) {
            $coName = $coDisplay = 'ΚΩΣΤΑΣ Γ. ΣΦΑΚΙΑΝΑΚΗΣ & ΣΙΑ Ο.Ε.';
            $coEmail = 'info@ecowatt-energy.gr'; $coPhone = ''; $coWebsite = 'ecowatt-energy.gr';
            $coTaxId = ''; $coAddress = '';
        }

        $coInfoLines = [];
        if ($coDisplay) $coInfoLines[] = '<b>' . htmlspecialchars($coDisplay) . '</b>';
        if ($coAddress) $coInfoLines[] = htmlspecialchars($coAddress);
        if ($coPhone)   $coInfoLines[] = 'Τηλ: ' . htmlspecialchars($coPhone);
        if ($coEmail)   $coInfoLines[] = 'Email: ' . htmlspecialchars($coEmail);
        if ($coWebsite) $coInfoLines[] = 'Web: ' . htmlspecialchars($coWebsite);
        if ($coTaxId)   $coInfoLines[] = 'ΑΦΜ: ' . htmlspecialchars($coTaxId);
        $coInfoHtml = '<div style="font-size:8pt;color:#1a3c6e;line-height:1.6;text-align:right;">' . implode('<br/>', $coInfoLines) . '</div>';

        // Auto-extract images from .docx template if not already present
        $imgDir = __DIR__ . '/../uploads/template_images/';
        if (!is_dir($imgDir) || !file_exists($imgDir . 'image1.jpeg')) {
            $templateDocx = __DIR__ . '/../templates/maintenance_offer_template.docx';
            if (file_exists($templateDocx)) {
                if (!is_dir($imgDir)) {
                    mkdir($imgDir, 0755, true);
                }
                $zip = new \ZipArchive();
                if ($zip->open($templateDocx) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $name = $zip->getNameIndex($i);
                        if (strpos($name, 'word/media/') === 0) {
                            file_put_contents($imgDir . basename($name), $zip->getFromName($name));
                        }
                    }
                    $zip->close();
                }
            }
        }

        $logoSrc  = file_exists($imgDir . 'image1.jpeg')
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($imgDir . 'image1.jpeg')) : '';
        $isoSrc   = file_exists($imgDir . 'image3.png')
            ? 'data:image/png;base64,'  . base64_encode(file_get_contents($imgDir . 'image3.png'))  : '';
        $transSrc = file_exists($imgDir . 'image2.jpeg')
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($imgDir . 'image2.jpeg')) : '';
        $signSrc  = file_exists($imgDir . 'image6.jpeg')
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($imgDir . 'image6.jpeg')) : '';

        $logoImg  = $logoSrc  ? '<img src="' . $logoSrc  . '" width="170" />' : '<b>ECOWATT energy</b>';
        $isoImg   = $isoSrc   ? '<img src="' . $isoSrc   . '" width="70" />'  : '';
        $transImg = $transSrc ? '<img src="' . $transSrc . '" width="130" />' : '';
        $signImg  = $signSrc  ? '<img src="' . $signSrc  . '" width="90" />'  : '';

        $notesSection = !empty($offer['notes'])
            ? '<p style="font-size:9pt; color:#444; margin-top:4px;"><b>Παρατηρήσεις:</b> ' . $notes . '</p>'
            : '';

        return '
<style>
    body { font-family: dejavusans; font-size: 9.5pt; color: #1a1a1a; }
    h1   { font-size: 15pt; text-align: center; color: #1a3c6e; letter-spacing: 1px; margin: 5px 0; }
    .sec  { background-color: #1a3c6e; color: #ffffff; font-size: 10pt; font-weight: bold; padding: 4px 8px; }
    .sub  { background-color: #dce6f5; color: #1a3c6e; font-size: 9.5pt; font-weight: bold; padding: 3px 6px; margin: 6px 0 2px 0; }
    table { font-size: 9.5pt; }
    .ct th { background-color: #1a3c6e; color: #ffffff; padding: 5px 8px; text-align: center; border: 1px solid #1a3c6e; }
    .ct td { border: 1px solid #aaaaaa; padding: 5px 8px; }
    .ctot  { background-color: #dce6f5; font-weight: bold; font-size: 10.5pt; }
</style>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:6px;">
  <tr>
    <td width="50%" valign="middle">' . $logoImg . '</td>
    <td width="50%" align="right" valign="middle">
      ' . $coInfoHtml . '
      ' . ($isoImg ? '<div style="margin-top:4px;text-align:right;">' . $isoImg . '</div>' : '') . '
    </td>
  </tr>
</table>
<hr style="border:2px solid #1a3c6e; margin:0 0 8px 0;" />

<h1>ΟΙΚΟΝΟΜΙΚΗ ΠΡΟΣΦΟΡΑ</h1>
<p style="text-align:center; font-size:10pt; margin:2px 0 6px 0;">Συντήρηση υποσταθμού μέσης τάσης &nbsp;—&nbsp; <b>' . $company . '</b></p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
  <tr>
    <td style="font-size:8pt; color:#666;">ISO 9001 : 2008 &nbsp;&nbsp; OHSAS 18001 : 2007</td>
    <td align="right" style="font-size:9pt;"><b>Ημερομηνία:</b> ' . $today . ' &nbsp;&nbsp; <b>Αρ. Προσφοράς:</b> ' . $offerNum . '</td>
  </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="6" style="margin-bottom:10px;">
  <tr>
    <td width="63%" valign="top" style="font-size:9pt; line-height:1.55;">
      <p>Αγαπητοί κύριοι,</p>
      <p>Θα θέλαμε να σας ευχαριστήσουμε για το ενδιαφέρον σας σχετικά με τις παρεχόμενες υπηρεσίες της εταιρείας μας και να σας διαβεβαιώσουμε ότι θα είμαστε πάντα διαθέσιμοι για να σας προσφέρουμε υψηλής ποιότητας λύσεις.</p>
      <p>Σε συνέχεια των επαφών που είχαμε, σας αποστέλλουμε την οικονομική προσφορά για <b>Συντήρηση υποσταθμού μέσης τάσης ' . $company . '</b>.</p>
      <p>Η περιγραφή προέκυψε βάσει της πολυετούς εμπειρίας και τεχνογνωσίας της εταιρίας μας στους υποσταθμούς Μ.Τ..</p>
    </td>
    <td width="37%" valign="top" align="center">' . $transImg . '</td>
  </tr>
</table>

<p class="sub">ΠΕΛΑΤΟΛΟΓΙΟ</p>
<p style="font-size:9pt; margin:3px 0 2px 0;">Η <b>ΚΩΣΤΑΣ Γ. ΣΦΑΚΙΑΝΑΚΗΣ ΚΑΙ ΣΙΑ ΟΕ</b> σας παρουσιάζει ένα μέρος από το πελατολόγιο της:</p>
<table width="100%" cellpadding="0" cellspacing="0" style="font-size:8.5pt; margin-bottom:8px;">
  <tr>
    <td width="50%">1. ΤΕΑΒ ΑΕ &nbsp; 2. ΧΑΛΚΙΑΔΑΚΗΣ &nbsp; 3. DAIOS HOTELS &nbsp; 4. LYTOS<br/>5. IKAROS RESORT HOTEL &nbsp; 6. ΔΕΥΑ ΗΡΑΚΛΕΙΟΥ &nbsp; 7. ENEL &nbsp; 8. ENERCON</td>
    <td width="50%">9. KOSTA MARE &nbsp; 10. ΔΕΗ &nbsp; 11. ΔΕΥΑ ΧΕΡΣΟΝΗΣΟΥ &nbsp; 12. ΟΤΕ<br/>13. BLUE BAY &nbsp; 14. CRETA MARIS &nbsp; 15. GALAXY &nbsp; 16. CANDIA MARIS</td>
  </tr>
</table>
<p style="font-size:8.5pt; color:#444; margin-bottom:10px;">Θα θέλαμε να σας κάνουμε γνωστό ότι είμαστε στη διάθεσή σας για οποιαδήποτε ερώτηση – διευκρίνιση.</p>

<p class="sec">ΕΡΓΑΣΙΕΣ ΣΥΝΤΗΡΗΣΗΣ</p>

<p class="sub">1. ΓΕΝΙΚΗ ΣΥΝΤΗΡΗΣΗ</p>
<ul style="font-size:9pt; margin:2px 0 5px 0;">
  <li>Καθαριότητα στους χώρους Μ/Σ, κυψέλης Μ.Τ.</li>
  <li>Έλεγχος ύπαρξης πυροσβεστήρων στο χώρο του Υ/Σ</li>
  <li>Έλεγχος αερισμού Υ/Σ</li>
  <li>Έλεγχος φωτισμού στον χώρο του Υ/Σ</li>
  <li>Έλεγχος ύπαρξης πινακίδων κινδύνου</li>
</ul>

<p class="sub">2. ΣΥΝΤΗΡΗΣΗ ΣΤΗ ΜΕΣΗ ΤΑΣΗ – ΚΥΨΕΛΗ</p>
<ul style="font-size:9pt; margin:2px 0 5px 0;">
  <li>Έλεγχος ακροκιβωτίων εσωτερικού χώρου</li>
  <li>Έλεγχος μονωτήρων κυψέλης, καθαρισμός</li>
  <li>Καθαρισμός εξαρτημάτων, επαφών, συσφίξεις ακροδεκτών</li>
  <li>Έλεγχος λειτουργίας αφύγρανσης κυψέλης</li>
  <li>Μέτρηση μονώσεων καλωδίων Μ.Τ.</li>
</ul>

<p class="sub">3. ΣΥΝΤΗΡΗΣΗ ΣΤΟ ΜΕΤΑΣΧΗΜΑΤΙΣΤΗ</p>
<ul style="font-size:9pt; margin:2px 0 5px 0;">
  <li>Έλεγχος θερμοκρασίας Μ/Σ</li>
  <li>Έλεγχος μονωτήρων Μ.Τ. - Χ.Τ., καθαρισμός</li>
  <li>Έλεγχος ακροκιβωτίων</li>
  <li>Έλεγχος καλής κατάστασης κελύφους Μ/Σ</li>
  <li>Καθαρισμός εξαρτημάτων, συσφίξεις ακροδεκτών</li>
  <li>Μέτρηση τυλιγμάτων Μ/Σ</li>
  <li>Μέτρηση μονώσεων Μ/Σ</li>
  <li>Μέτρηση διηλεκτρικής αντοχής ελαίου</li>
</ul>

<p class="sub">4. ΓΕΙΩΣΕΙΣ - ΠΡΟΣΤΑΣΙΑ</p>
<ul style="font-size:9pt; margin:2px 0 10px 0;">
  <li>Μέτρηση γείωσης (εφόσον είναι εφικτή η μέτρηση)</li>
  <li>Έλεγχος λειτουργίας αυτοματισμού προστασίας</li>
</ul>

<p class="sec">ΚΟΣΤΟΛΟΓΗΣΗ</p>
<table class="ct" width="100%" cellpadding="0" cellspacing="0" style="margin-top:4px; border-collapse:collapse;">
  <tr>
    <th width="8%">Α/Α</th>
    <th width="57%">ΠΕΡΙΓΡΑΦΗ ΕΞΟΠΛΙΣΜΟΥ - ΕΡΓΑΣΙΕΣ</th>
    <th width="15%">ΤΕΜ / ΜΕΤΡΑ</th>
    <th width="20%">ΤΙΜΗ</th>
  </tr>
  <tr>
    <td align="center">1</td>
    <td>Συντήρηση υποσταθμού μέσης τάσης<br/><span style="font-size:8.5pt;">Μετασχηματιστής</span></td>
    <td align="center">' . $transCount . '</td>
    <td align="center" style="font-size:11pt; font-weight:bold;">' . $priceNum . '</td>
  </tr>
  <tr>
    <td colspan="3" align="right" class="ctot" style="padding:5px 10px;">Σύνολο (χωρίς Φ.Π.Α.):</td>
    <td align="center" class="ctot">' . $priceNum . '</td>
  </tr>
</table>

' . $notesSection . '

<p style="font-size:9pt; margin-top:10px;"><b>Οικονομικοί Όροι</b><br/>Στις παραπάνω τιμές δεν περιλαμβάνεται ο Φ.Π.Α. 24%</p>
<p style="font-size:9pt;">Ελπίζουμε η προσφορά μας να ανταποκρίνεται στις απαιτήσεις σας. Για οποιαδήποτε περαιτέρω διευκρίνηση ή πληροφορία, παρακαλώ μη διστάσετε να επικοινωνήσετε μαζί μας.</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;">
  <tr>
    <td width="50%" valign="top" style="font-size:9pt;">
      Με εκτίμηση,<br/><br/>
      ' . $signImg . '<br/>
      <b>Σφακιανάκης Κωνσταντίνος</b><br/>
      Ηλεκτρολόγος Μηχανικός ΤΕ
    </td>
    <td width="50%" valign="bottom" style="font-size:9pt;">
      <p style="margin-bottom:40px;">Ισχύς προσφοράς έως: <b>' . $expiry . '</b></p>
      <p style="border-top:1px solid #555; padding-top:5px;">Σφραγίδα &amp; Υπογραφή <b>' . $company . '</b></p>
    </td>
  </tr>
</table>
';
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

                CustomOfferPDF::$footerText = self::buildPdfFooterText();
                $pdfMail = new CustomOfferPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdfMail->SetCreator('HandyCRM');
                $pdfMail->SetAuthor('ECOWATT - Κώστας Γ. Σφακιανάκης & ΣΙΑ Ο.Ε.');
                $pdfMail->SetTitle('Οικονομική Προσφορά ' . $offer['offer_number']);
                $pdfMail->SetPrintHeader(false);
                $pdfMail->SetFont('dejavusans', '', 10, '', true);
                $pdfMail->SetMargins(20, 15, 20);
                $pdfMail->SetFooterMargin(15);
                $pdfMail->SetAutoPageBreak(true, 20);
                $pdfMail->AddPage();
                $pdfMail->writeHTML($this->generateOfferHTML($offer), true, false, true, false, '');
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
