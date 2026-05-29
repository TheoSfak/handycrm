<?php

require_once 'classes/BaseController.php';
require_once __DIR__ . '/../classes/AuthMiddleware.php';
require_once 'models/PriceList.php';

class PriceListsController extends BaseController {
    private PriceList $model;

    public function __construct() {
        parent::__construct();
        $this->model = new PriceList();
    }

    public function index(): void {
        if (!$this->canViewPriceLists()) {
            $this->redirect('/dashboard?error=unauthorized');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search = trim($_GET['search'] ?? '');

        $records = $this->model->getAll($page, $perPage, $search ?: null);
        $total = $this->model->getTotalCount($search ?: null);
        $pages = (int)ceil($total / $perPage);

        $currentUser = $this->getCurrentUser();

        $this->view('price_lists/index', [
            'title' => 'Τιμοκατάλογοι - ' . APP_NAME,
            'records' => $records,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'search' => $search,
            'currentUserId' => (int)($currentUser['id'] ?? 0),
            'isAdminOrSupervisor' => $this->isAdmin() || $this->isSupervisor(),
            'canCreate' => $this->canCreatePriceList(),
        ]);
    }

    public function store(): void {
        if (!$this->canCreatePriceList()) {
            $this->redirect('/price-lists?error=unauthorized');
        }

        $this->validateCsrfToken();

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->redirect('/price-lists?error=missing_title');
        }

        if (empty($_FILES['price_list_file']['tmp_name'])) {
            $this->redirect('/price-lists?error=no_file');
        }

        $file = $_FILES['price_list_file'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($file['tmp_name']);

        $allowed = [
            'application/pdf' => ['ext' => 'pdf', 'max' => 30 * 1024 * 1024],
            'image/jpeg' => ['ext' => 'jpg', 'max' => 10 * 1024 * 1024],
            'image/png' => ['ext' => 'png', 'max' => 10 * 1024 * 1024],
            'image/webp' => ['ext' => 'webp', 'max' => 10 * 1024 * 1024],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['ext' => 'xlsx', 'max' => 30 * 1024 * 1024],
            'application/vnd.ms-excel' => ['ext' => 'xls', 'max' => 30 * 1024 * 1024],
            'text/csv' => ['ext' => 'csv', 'max' => 30 * 1024 * 1024],
        ];

        if (!isset($allowed[$mime])) {
            $this->redirect('/price-lists?error=invalid_type');
        }

        if ($file['size'] <= 0 || $file['size'] > $allowed[$mime]['max']) {
            $this->redirect('/price-lists?error=file_too_large');
        }

        $dir = APP_ROOT . '/uploads/price-lists';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeName = uniqid('price_' . time() . '_', true) . '.' . $allowed[$mime]['ext'];
        $destination = $dir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->redirect('/price-lists?error=upload_failed');
        }

        $user = $this->getCurrentUser();
        $this->model->createPriceList([
            'title' => $title,
            'file_path' => 'uploads/price-lists/' . $safeName,
            'original_filename' => $file['name'],
            'mime_type' => $mime,
            'file_size' => (int)$file['size'],
            'created_by' => (int)$user['id'],
        ]);

        $this->redirect('/price-lists?saved=1');
    }

    public function file(int $id): void {
        if (!$this->canViewPriceLists()) {
            http_response_code(403);
            exit('Unauthorized');
        }

        $row = $this->model->findOne($id);
        if (!$row) {
            http_response_code(404);
            exit('Not found');
        }

        $path = APP_ROOT . '/' . ltrim($row['file_path'], '/');
        if (!file_exists($path)) {
            http_response_code(404);
            exit('File not found');
        }

        $safeName = preg_replace('/[^\w\-.]/', '_', $row['original_filename']) ?: 'file';

        header('Content-Type: ' . $row['mime_type']);
        header('Content-Length: ' . filesize($path));
        $inline = strpos($row['mime_type'], 'image/') === 0 || $row['mime_type'] === 'application/pdf';
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $safeName . '"');
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    public function delete(int $id): void {
        $row = $this->model->findOne($id);
        if (!$row) {
            $this->redirect('/price-lists?error=not_found');
        }

        if (!$this->canDeletePriceList($row)) {
            $this->redirect('/price-lists?error=unauthorized');
        }

        $this->validateCsrfToken();

        $path = APP_ROOT . '/' . ltrim($row['file_path'], '/');
        if (file_exists($path)) {
            @unlink($path);
        }

        $this->model->deleteOne($id);
        $this->redirect('/price-lists?deleted=1');
    }

    private function canViewPriceLists(): bool {
        return $this->isAdmin() || $this->isSupervisor() || $this->isTechnician() || can('price_lists.view');
    }

    private function canCreatePriceList(): bool {
        return $this->isAdmin() || $this->isSupervisor() || $this->isTechnician() || can('price_lists.create');
    }

    private function canDeletePriceList(array $record): bool {
        if ($this->isAdmin() || $this->isSupervisor()) {
            return true;
        }

        $user = $this->getCurrentUser();
        return (int)($record['created_by'] ?? 0) === (int)($user['id'] ?? 0) && ($this->isTechnician() || can('price_lists.delete'));
    }
}
