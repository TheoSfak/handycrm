<?php

require_once 'models/MaintenanceOffer.php';

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

        try {
            $pdfContent = $this->buildOfferPDF($offer, 'S');
            $filename   = 'Prosfora_Sintirisis_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $offer['offer_number']) . '.pdf';

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Content-Length: ' . strlen($pdfContent));
            echo $pdfContent;
        } catch (\Exception $e) {
            error_log('exportPDF error: ' . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/maintenance-offers');
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Shared helper: build PDF with TCPDF (no shell_exec / LibreOffice needed)
    // $mode: 'S' = return string, 'D' = download, 'F' = save to file ($dest)
    // ──────────────────────────────────────────────────────────────────────────
    private function buildOfferPDF(array $offer, string $mode = 'S', string $dest = ''): string {
        if (!class_exists('TCPDF')) {
            require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor('HandyCRM');
        $pdf->SetTitle('Προσφορά Συντήρησης ' . $offer['offer_number']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();

        $price       = number_format((float)$offer['price'], 2, ',', '.') . ' €';
        $count       = (int)$offer['transformers_count'];
        $expiryDate  = !empty($offer['offer_expiry_date']) ? date('d/m/Y', strtotime($offer['offer_expiry_date'])) : '-';
        $today       = date('d/m/Y');
        $company     = htmlspecialchars($offer['company_name']);
        $address     = htmlspecialchars($offer['address'] ?? '');
        $phone       = htmlspecialchars($offer['phone'] ?? '');
        $offerNum    = htmlspecialchars($offer['offer_number']);
        $notes       = nl2br(htmlspecialchars($offer['notes'] ?? ''));

        $html = '
<table cellpadding="4" style="width:100%; border-bottom:2px solid #2c3e50; margin-bottom:10px;">
  <tr>
    <td style="width:60%;"><span style="font-size:18px; font-weight:bold; color:#2c3e50;">ΠΡΟΣΦΟΡΑ ΣΥΝΤΗΡΗΣΗΣ ΥΠΟΣΤΑΘΜΩΝ</span></td>
    <td style="width:40%; text-align:right; font-size:10px;">
      <strong>Αρ. Προσφοράς:</strong> ' . $offerNum . '<br/>
      <strong>Ημερομηνία:</strong> ' . $today . '<br/>
      <strong>Ισχύς έως:</strong> ' . $expiryDate . '
    </td>
  </tr>
</table>

<table cellpadding="4" style="width:100%; margin-bottom:14px; border:1px solid #ccc;">
  <tr><td style="width:30%; background-color:#f5f5f5;"><strong>Επωνυμία:</strong></td><td>' . $company . '</td></tr>
  <tr><td style="background-color:#f5f5f5;"><strong>Διεύθυνση:</strong></td><td>' . $address . '</td></tr>
  <tr><td style="background-color:#f5f5f5;"><strong>Τηλέφωνο:</strong></td><td>' . $phone . '</td></tr>
</table>

<h3 style="color:#2c3e50; border-bottom:1px solid #2c3e50;">Αντικείμενο Εργασιών</h3>
<p>Η παρούσα προσφορά αφορά στην ετήσια <strong>συντήρηση και έλεγχο υποσταθμών μέσης τάσης</strong>.</p>

<table cellpadding="5" style="width:100%; border:1px solid #ccc; margin-bottom:14px;">
  <tr style="background-color:#2c3e50; color:#ffffff;">
    <td style="width:60%;"><strong>Περιγραφή</strong></td>
    <td style="width:20%; text-align:center;"><strong>Ποσότητα</strong></td>
    <td style="width:20%; text-align:right;"><strong>Τιμή</strong></td>
  </tr>
  <tr>
    <td>Ετήσια συντήρηση &amp; έλεγχος υποσταθμού μέσης τάσης</td>
    <td style="text-align:center;">' . $count . '</td>
    <td style="text-align:right;"><strong>' . $price . '</strong></td>
  </tr>
</table>

<table cellpadding="4" style="width:100%; margin-bottom:14px;">
  <tr>
    <td style="width:60%;"></td>
    <td style="width:40%; border:2px solid #2c3e50; text-align:right; padding:6px;">
      <span style="font-size:13px;"><strong>Σύνολο: ' . $price . '</strong></span><br/>
      <span style="font-size:9px; color:#666;">(χωρίς ΦΠΑ)</span>
    </td>
  </tr>
</table>
';

        if (!empty($offer['notes'])) {
            $html .= '<h3 style="color:#2c3e50;">Σημειώσεις</h3><p>' . $notes . '</p>';
        }

        $html .= '<p style="font-size:9px; color:#888; margin-top:20px; border-top:1px solid #ddd; padding-top:6px;">
  Η προσφορά ισχύει έως ' . $expiryDate . '. Για οποιαδήποτε διευκρίνιση επικοινωνήστε μαζί μας.
</p>';

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output($dest ?: 'offer.pdf', $mode);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Shared helper: fill template placeholders → save filled .docx to temp path
    // ──────────────────────────────────────────────────────────────────────────
    private function buildFilledDocx(array $offer): string {
        $templatePath = __DIR__ . '/../templates/maintenance_offer_template.docx';
        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Το πρότυπο Word δεν βρέθηκε στον φάκελο templates/.');
        }

        require_once __DIR__ . '/../vendor/autoload.php';

        $processor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        $processor->setValue('XXXXX', number_format((float)$offer['price'], 2, ',', '.') . ' €');
        $processor->setValue('XXXX',  (string)$offer['transformers_count']);
        $processor->setValue('XXX',   $offer['company_name']);
        $processor->setValue('DATE',        date('d/m/Y'));
        $processor->setValue('OFFER_NUMBER', $offer['offer_number']);
        $processor->setValue('EXPIRY_DATE',  $offer['offer_expiry_date'] ? date('d/m/Y', strtotime($offer['offer_expiry_date'])) : '');
        $processor->setValue('ADDRESS',      $offer['address'] ?? '');
        $processor->setValue('PHONE',        $offer['phone'] ?? '');

        $tmpDocx = sys_get_temp_dir() . '/offer_' . $offer['offer_number'] . '_' . time() . '.docx';
        $processor->saveAs($tmpDocx);
        return $tmpDocx;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EXPORT WORD — fills template and downloads .docx
    // ──────────────────────────────────────────────────────────────────────────
    public function exportWord(int $id): void {
        $offer = $this->offerModel->find($id);
        if (!$offer) {
            $_SESSION['error'] = 'Η προσφορά δεν βρέθηκε.';
            header('Location: ' . BASE_URL . '/maintenance-offers');
            exit;
        }

        try {
            $tmpDocx  = $this->buildFilledDocx($offer);
            $filename = 'Προσφορά_Συντήρησης_' . preg_replace('/[^a-zA-Z0-9_-]/u', '_', $offer['company_name']) . '_' . $offer['offer_number'] . '.docx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Content-Length: ' . filesize($tmpDocx));

            readfile($tmpDocx);
            unlink($tmpDocx);
        } catch (\Exception $e) {
            error_log('exportWord error: ' . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/maintenance-offers');
        }
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

                // Generate PDF attachment via TCPDF (no shell_exec needed)
                $pdfFilename    = 'Prosfora_Sintirisis_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $offer['offer_number']) . '.pdf';
                $attachmentPath = sys_get_temp_dir() . '/' . $pdfFilename;
                $this->buildOfferPDF($offer, 'F', $attachmentPath);

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
