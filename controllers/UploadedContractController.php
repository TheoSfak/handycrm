<?php

require_once 'classes/BaseController.php';
require_once 'models/UploadedContract.php';

class UploadedContractController extends BaseController {

    private UploadedContract $model;

    public function __construct() {
        parent::__construct();
        $this->model = new UploadedContract();
    }

    // ── AUTH GUARD ────────────────────────────────────────────────────────
    private function requireAdminOrSupervisor(): void {
        $user = $this->getCurrentUser();
        if (!$user || !in_array($user['role'] ?? '', ['admin', 'supervisor'])) {
            $this->redirect('/dashboard?error=unauthorized');
            exit;
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /uploaded-contracts
    // ─────────────────────────────────────────────────────────────────────
    public function index(): void {
        $this->requireAdminOrSupervisor();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search  = trim($_GET['search'] ?? '');

        $contracts = $this->model->getAll($page, $perPage, $search ?: null);
        $total     = $this->model->getTotalCount($search ?: null);
        $pages     = (int)ceil($total / $perPage);

        $this->view('uploaded_contracts/index', [
            'title'     => 'Συμφωνητικά - ' . APP_NAME,
            'contracts' => $contracts,
            'total'     => $total,
            'page'      => $page,
            'pages'     => $pages,
            'search'    => $search,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /uploaded-contracts/store
    // ─────────────────────────────────────────────────────────────────────
    public function store(): void {
        $this->requireAdminOrSupervisor();
        $this->validateCsrfToken();

        $customerName = trim($_POST['customer_name'] ?? '');
        if ($customerName === '') {
            $this->redirect('/uploaded-contracts?error=missing_customer');
            return;
        }

        if (empty($_FILES['contract_file']['tmp_name'])) {
            $this->redirect('/uploaded-contracts?error=no_file');
            return;
        }

        $file     = $_FILES['contract_file'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $this->redirect('/uploaded-contracts?error=not_pdf');
            return;
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if ($mime !== 'application/pdf') {
            $this->redirect('/uploaded-contracts?error=not_pdf');
            return;
        }

        // Max 20 MB
        if ($file['size'] > 20 * 1024 * 1024) {
            $this->redirect('/uploaded-contracts?error=file_too_large');
            return;
        }

        $uploadDir = __DIR__ . '/../uploads/contracts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName  = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $destPath  = $uploadDir . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->redirect('/uploaded-contracts?error=upload_failed');
            return;
        }

        $user = $this->getCurrentUser();
        $id   = $this->model->createContract([
            'customer_name'     => $customerName,
            'file_path'         => 'uploads/contracts/' . $safeName,
            'original_filename' => htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8'),
            'created_by'        => $user['id'] ?? null,
        ]);

        $this->redirect('/uploaded-contracts/show/' . $id . '?uploaded=1');
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /uploaded-contracts/show/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function show(int $id): void {
        $this->requireAdminOrSupervisor();

        $contract = $this->model->findActive($id);
        if (!$contract) {
            $this->redirect('/uploaded-contracts?error=not_found');
            return;
        }

        $this->view('uploaded_contracts/show', [
            'title'    => 'Συμφωνητικό #' . $id . ' - ' . APP_NAME,
            'contract' => $contract,
            'uploaded' => isset($_GET['uploaded']),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /uploaded-contracts/scan/{id}   (AJAX)
    // ─────────────────────────────────────────────────────────────────────
    public function scan(int $id): void {
        $this->requireAdminOrSupervisor();

        try {
            $contract = $this->model->findActive($id);
            if (!$contract) {
                $this->json(['success' => false, 'message' => 'Δεν βρέθηκε το αρχείο'], 404);
                return;
            }

            $filePath  = __DIR__ . '/../' . $contract['file_path'];
            $extracted = UploadedContract::extractFromPdf($filePath, $contract['original_filename'] ?? null);

            // Persist raw text to DB
            if (!empty($extracted['text'])) {
                $db   = (new Database())->connect();
                $stmt = $db->prepare("UPDATE uploaded_contracts SET extracted_text = ? WHERE id = ?");
                $stmt->execute([mb_substr($extracted['text'], 0, 60000), $id]);
            }

            $this->json([
                'success'     => true,
                'title'       => $extracted['title'],
                'amount'      => $extracted['amount'],
                'start_date'  => $extracted['start_date'],
                'end_date'    => $extracted['end_date'],
                'description' => $extracted['description'],
                'text_length' => mb_strlen($extracted['text']),
                'strategy'    => $extracted['strategy'] ?? '',
            ]);
        } catch (\Throwable $e) {
            error_log('UploadedContract scan error [id=' . $id . ']: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Σφάλμα κατά την ανάλυση: ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /uploaded-contracts/update/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function update(int $id): void {
        $this->requireAdminOrSupervisor();
        $this->validateCsrfToken();

        $contract = $this->model->findActive($id);
        if (!$contract) {
            $this->redirect('/uploaded-contracts?error=not_found');
            return;
        }

        $amountRaw = str_replace(['.', ','], ['', '.'], trim($_POST['amount'] ?? ''));
        $amount    = is_numeric($amountRaw) ? $amountRaw : null;

        $startDate = $this->parseDate($_POST['start_date'] ?? '');
        $endDate   = $this->parseDate($_POST['end_date']   ?? '');

        $this->model->updateFields($id, [
            'customer_name' => trim($_POST['customer_name'] ?? $contract['customer_name']),
            'title'         => trim($_POST['title']         ?? ''),
            'amount'        => $amount,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'description'   => trim($_POST['description']   ?? ''),
            'notes'         => trim($_POST['notes']         ?? ''),
            'extracted_text'=> $contract['extracted_text'],
        ]);

        $this->redirect('/uploaded-contracts/show/' . $id . '?saved=1');
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /uploaded-contracts/delete/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function delete(int $id): void {
        $this->requireAdminOrSupervisor();
        $this->validateCsrfToken();

        $contract = $this->model->findActive($id);
        if ($contract) {
            // Delete physical file
            $filePath = __DIR__ . '/../' . $contract['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->model->softDelete($id);
        }

        $this->redirect('/uploaded-contracts?deleted=1');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Helper: parse date from dd/mm/yyyy or yyyy-mm-dd
    // ─────────────────────────────────────────────────────────────────────
    private function parseDate(string $raw): ?string {
        $raw = trim($raw);
        if ($raw === '') return null;
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $raw, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /uploaded-contracts/file/{id}  — stream PDF to browser
    // ─────────────────────────────────────────────────────────────────────
    public function file(int $id): void {
        $this->requireAdminOrSupervisor();

        $contract = $this->model->findActive($id);
        if (!$contract) {
            http_response_code(404);
            exit('Not found');
        }

        $filePath = __DIR__ . '/../' . $contract['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('File not found');
        }

        // Sanitise filename for Content-Disposition
        $safeName = preg_replace('/[^\w\-.]/', '_', $contract['original_filename']) ?: 'contract.pdf';

        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . $safeName . '"');
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');

        readfile($filePath);
        exit;
    }
}
