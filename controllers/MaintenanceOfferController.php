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
            $this->Cell(0, 5, 'ECOWATT Ενεργειακές Λύσεις | ecowatt.gr | info@ecowatt.gr', 0, 1, 'C');
            $this->SetY(-8);
            $this->SetFont('dejavusans', 'I', 7);
            $this->Cell(0, 3, 'Σελίδα ' . $this->getAliasNumPage() . ' από ' . $this->getAliasNbPages(), 0, 0, 'C');
        }
    }
}

class MaintenanceOfferController extends BaseController {

    private MaintenanceOffer $offerModel;
    private const ITEMS_PER_PAGE = 20;

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
        $date  = $input['scheduled_date'] ?? null;

        // Validate date format
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(['success' => false, 'message' => 'Μη έγκυρη ημερομηνία']);
            exit;
        }

        $this->offerModel->update($id, ['scheduled_date' => $date ?: null]);

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
    // EXPORT PDF (TCPDF — ελληνικό HTML layout)
    // ──────────────────────────────────────────────────────────────────────────
    public function exportPDF(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        $pdf = new CustomOfferPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor('ECOWATT Ενεργειακές Λύσεις');
        $pdf->SetTitle('Προσφορά Συντήρησης ' . $offer['offer_number']);
        $pdf->SetFont('dejavusans', '', 10, '', true);
        $pdf->setHeaderData('', 0, 'ECOWATT Ενεργειακές Λύσεις', 'Προσφορά Συντήρησης Υποσταθμού', [0, 102, 204], [0, 64, 128]);
        $pdf->setHeaderFont(['dejavusans', '', 10]);
        $pdf->setFooterFont(['dejavusans', '', 8]);
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(20);
        $pdf->SetAutoPageBreak(true, 25);
        $pdf->AddPage();

        $html = $this->generateOfferHTML($offer);
        $pdf->writeHTMLCell(0, 0, null, null, $html, 0, 1, 0, true, '', true);

        $filename = 'Προσφορά_Συντήρησης_' . preg_replace('/[^a-zA-Z0-9_-]/u', '_', $offer['company_name']) . '_' . $offer['offer_number'] . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Generate HTML content for the offer PDF
     */
    private function generateOfferHTML(array $offer): string {
        $company    = htmlspecialchars($offer['company_name']);
        $address    = htmlspecialchars($offer['address'] ?? '-');
        $phone      = htmlspecialchars($offer['phone'] ?? '-');
        $offerNum   = htmlspecialchars($offer['offer_number']);
        $today      = date('d/m/Y');
        $expiry     = $offer['offer_expiry_date'] ? date('d/m/Y', strtotime($offer['offer_expiry_date'])) : '-';
        $transCount = (int)$offer['transformers_count'];
        $price      = number_format((float)$offer['price'], 2, ',', '.') . ' €';
        $notes      = nl2br(htmlspecialchars($offer['notes'] ?? ''));

        return '
<style>
    body { font-family: dejavusans; font-size: 10px; color: #333; }
    h2 { color: #0066cc; font-size: 14px; text-align: center; margin-bottom: 4px; }
    .subtitle { text-align: center; font-size: 9px; color: #666; margin-bottom: 14px; }
    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .info-table td { padding: 5px 8px; font-size: 9px; }
    .info-table .label { font-weight: bold; color: #0066cc; width: 35%; background: #f0f6ff; }
    .section-title { background: #0066cc; color: #fff; padding: 5px 8px; font-size: 10px; font-weight: bold; margin-top: 12px; margin-bottom: 0; }
    .offer-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .offer-table th { background: #e6f0ff; color: #0066cc; padding: 6px 8px; font-size: 9px; border: 1px solid #b3ccee; text-align: left; }
    .offer-table td { padding: 7px 8px; font-size: 10px; border: 1px solid #ddd; }
    .price-row td { font-weight: bold; font-size: 12px; background: #f0f6ff; color: #0a3d6b; }
    .notes-box { border: 1px solid #ddd; padding: 8px; background: #fafafa; font-size: 9px; margin-top: 4px; min-height: 20px; }
    .sign-area { margin-top: 30px; }
    .sign-table { width: 100%; }
    .sign-table td { width: 50%; padding: 0 10px; font-size: 9px; }
    .sign-line { border-top: 1px solid #333; margin-top: 25px; padding-top: 4px; }
</style>

<h2>ΠΡΟΣΦΟΡΑ ΣΥΝΤΗΡΗΣΗΣ ΥΠΟΣΤΑΘΜΟΥ</h2>
<p class="subtitle">Αρ. Προσφοράς: <strong>' . $offerNum . '</strong> &nbsp;|&nbsp; Ημερομηνία: <strong>' . $today . '</strong> &nbsp;|&nbsp; Ισχύς έως: <strong>' . $expiry . '</strong></p>

<p class="section-title">ΣΤΟΙΧΕΙΑ ΠΑΡΑΛΗΠΤΗ</p>
<table class="info-table" cellspacing="0">
    <tr>
        <td class="label">Επωνυμία:</td>
        <td>' . $company . '</td>
    </tr>
    <tr>
        <td class="label">Διεύθυνση:</td>
        <td>' . $address . '</td>
    </tr>
    <tr>
        <td class="label">Τηλέφωνο:</td>
        <td>' . $phone . '</td>
    </tr>
</table>

<p class="section-title">ΑΝΤΙΚΕΙΜΕΝΟ ΠΡΟΣΦΟΡΑΣ</p>
<table class="offer-table" cellspacing="0">
    <tr>
        <th>Περιγραφή Εργασιών</th>
        <th style="width:20%; text-align:center;">Αρ. Μ/Σ</th>
        <th style="width:20%; text-align:right;">Τιμή (χωρίς ΦΠΑ)</th>
    </tr>
    <tr>
        <td>Ετήσια Συντήρηση Μετασχηματιστή(ών) Υποσταθμού<br>
            <span style="font-size:8px; color:#555;">Περιλαμβάνει: Έλεγχος μονώσεων, αντιστάσεων, γείωσης, πληρότητα ελαίου, καθαρισμός, σύνταξη αναφοράς</span>
        </td>
        <td style="text-align:center;">' . $transCount . '</td>
        <td style="text-align:right;">' . $price . '</td>
    </tr>
    <tr class="price-row">
        <td colspan="2" style="text-align:right; padding-right:10px;">Σύνολο (χωρίς ΦΠΑ):</td>
        <td style="text-align:right;">' . $price . '</td>
    </tr>
</table>

' . (!empty($offer['notes']) ? '
<p class="section-title">ΠΑΡΑΤΗΡΗΣΕΙΣ / ΕΙΔΙΚΟΙ ΟΡΟΙ</p>
<div class="notes-box">' . $notes . '</div>
' : '') . '

<p class="section-title">ΟΡΟΙ ΠΡΟΣΦΟΡΑΣ</p>
<div class="notes-box" style="font-size:8px;">
• Οι παραπάνω τιμές είναι σε Ευρώ και δεν συμπεριλαμβάνουν ΦΠΑ.<br>
• Η παρούσα προσφορά ισχύει έως ' . $expiry . '.<br>
• Η τιμολόγηση πραγματοποιείται μετά την ολοκλήρωση των εργασιών.<br>
• Ο χρόνος ανταπόκρισης σε έκτακτα περιστατικά: εντός 24 ωρών.
</div>

<div class="sign-area">
<table class="sign-table" cellspacing="0">
    <tr>
        <td>
            <div class="sign-line">Για την ECOWATT Ενεργειακές Λύσεις<br><br></div>
        </td>
        <td>
            <div class="sign-line">Για την ' . $company . '<br><br></div>
        </td>
    </tr>
</table>
</div>
';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EXPORT WORD (TemplateProcessor with .docx template)
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
            $_SESSION['error'] = 'Το πρότυπο Word δεν βρέθηκε. Παρακαλώ τοποθετήστε το αρχείο "maintenance_offer_template.docx" στον φάκελο templates/.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        require_once __DIR__ . '/../vendor/autoload.php';

        $processor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        // Replace placeholders as defined by the user:
        // XXX  = Επωνυμία επιχείρησης
        // XXXX = Αριθμός μετασχηματιστών
        // XXXXX = Τιμή
        // Note: order matters — replace longer keys first to avoid partial replacement
        $processor->setValue('XXXXX', number_format($offer['price'], 2, ',', '.') . ' €');
        $processor->setValue('XXXX',  (string)$offer['transformers_count']);
        $processor->setValue('XXX',   $offer['company_name']);

        // Additional useful placeholders
        $processor->setValue('OFFER_NUMBER',  $offer['offer_number']);
        $processor->setValue('DATE',          date('d/m/Y'));
        $processor->setValue('EXPIRY_DATE',   $offer['offer_expiry_date'] ? date('d/m/Y', strtotime($offer['offer_expiry_date'])) : '');
        $processor->setValue('ADDRESS',       $offer['address'] ?? '');
        $processor->setValue('PHONE',         $offer['phone'] ?? '');

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

                // Generate PDF attachment
                $pdfFilename    = 'Προσφορά_Συντήρησης_' . $offer['offer_number'] . '.pdf';
                $attachmentPath = sys_get_temp_dir() . '/' . $pdfFilename;

                $pdf = new CustomOfferPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator('HandyCRM');
                $pdf->SetAuthor('ECOWATT Ενεργειακές Λύσεις');
                $pdf->SetTitle('Προσφορά Συντήρησης ' . $offer['offer_number']);
                $pdf->SetFont('dejavusans', '', 10, '', true);
                $pdf->setHeaderData('', 0, 'ECOWATT Ενεργειακές Λύσεις', 'Προσφορά Συντήρησης Υποσταθμού', [0, 102, 204], [0, 64, 128]);
                $pdf->setHeaderFont(['dejavusans', '', 10]);
                $pdf->setFooterFont(['dejavusans', '', 8]);
                $pdf->SetMargins(15, 27, 15);
                $pdf->SetHeaderMargin(5);
                $pdf->SetFooterMargin(20);
                $pdf->SetAutoPageBreak(true, 25);
                $pdf->AddPage();
                $pdf->writeHTMLCell(0, 0, null, null, $this->generateOfferHTML($offer), 0, 1, 0, true, '', true);
                $pdf->Output($attachmentPath, 'F');

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
