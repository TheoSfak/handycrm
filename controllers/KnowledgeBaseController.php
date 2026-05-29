<?php

require_once 'classes/BaseController.php';
require_once __DIR__ . '/../classes/AuthMiddleware.php';
require_once 'models/KnowledgeBase.php';

if (!class_exists('TCPDF')) {
    require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
}

class KnowledgeBaseController extends BaseController {
    private KnowledgeBase $model;

    public function __construct() {
        parent::__construct();
        $this->model = new KnowledgeBase();
    }

    public function index(): void {
        if (!$this->canViewKnowledgeBase()) {
            $this->redirect('/dashboard?error=unauthorized');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $search = trim($_GET['search'] ?? '');
        $categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;

        $articles = $this->model->getAll($page, $perPage, $search ?: null, $categoryId ?: null);
        $total = $this->model->getTotalCount($search ?: null, $categoryId ?: null);
        $pages = (int)ceil($total / $perPage);
        $categories = $this->model->getAllCategories();

        $currentUser = $this->getCurrentUser();

        $this->view('knowledge_base/index', [
            'title' => 'Knowledge Base - ' . APP_NAME,
            'articles' => $articles,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'search' => $search,
            'categoryId' => $categoryId,
            'categories' => $categories,
            'canCreate' => $this->canCreateArticle(),
            'canManageCategories' => $this->canManageCategories(),
            'isAdminOrSupervisor' => $this->isAdmin() || $this->isSupervisor(),
            'currentUserId' => (int)($currentUser['id'] ?? 0),
        ]);
    }

    public function create(): void {
        if (!$this->canCreateArticle()) {
            $this->redirect('/knowledge-base?error=unauthorized');
        }

        $this->view('knowledge_base/form', [
            'title' => 'Νέο Άρθρο Γνώσης - ' . APP_NAME,
            'article' => null,
            'categories' => $this->model->getAllCategories(),
            'selectedCategoryIds' => [],
            'formAction' => BASE_URL . '/knowledge-base/store',
        ]);
    }

    public function store(): void {
        if (!$this->canCreateArticle()) {
            $this->redirect('/knowledge-base?error=unauthorized');
        }

        $this->validateCsrfToken();

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);

        if ($title === '' || $content === '') {
            $this->redirect('/knowledge-base/create?error=missing_fields');
        }

        $user = $this->getCurrentUser();

        $articleId = $this->model->createArticle([
            'title' => $title,
            'content' => $content,
            'created_by' => (int)$user['id'],
        ], $categoryIds);

        if (!empty($_FILES['attachments']['name'][0])) {
            $this->handleAttachmentsUpload($articleId, $_FILES['attachments']);
        }

        $this->redirect('/knowledge-base/show/' . $articleId . '?saved=1');
    }

    public function show(int $id): void {
        if (!$this->canViewKnowledgeBase()) {
            $this->redirect('/dashboard?error=unauthorized');
        }

        $article = $this->model->getById($id);
        if (!$article) {
            $this->redirect('/knowledge-base?error=not_found');
        }

        $this->view('knowledge_base/show', [
            'title' => $article['title'] . ' - ' . APP_NAME,
            'article' => $article,
            'canEdit' => $this->canEditArticle($article),
            'canDelete' => $this->canDeleteArticle($article),
            'canExport' => $this->canExportKnowledgeBase(),
        ]);
    }

    public function edit(int $id): void {
        $article = $this->model->getById($id);
        if (!$article) {
            $this->redirect('/knowledge-base?error=not_found');
        }

        if (!$this->canEditArticle($article)) {
            $this->redirect('/knowledge-base/show/' . $id . '?error=unauthorized');
        }

        $this->view('knowledge_base/form', [
            'title' => 'Επεξεργασία Άρθρου - ' . APP_NAME,
            'article' => $article,
            'categories' => $this->model->getAllCategories(),
            'selectedCategoryIds' => array_map('intval', $article['category_ids'] ?? []),
            'formAction' => BASE_URL . '/knowledge-base/update/' . $id,
        ]);
    }

    public function update(int $id): void {
        $article = $this->model->getById($id);
        if (!$article) {
            $this->redirect('/knowledge-base?error=not_found');
        }

        if (!$this->canEditArticle($article)) {
            $this->redirect('/knowledge-base/show/' . $id . '?error=unauthorized');
        }

        $this->validateCsrfToken();

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);

        if ($title === '' || $content === '') {
            $this->redirect('/knowledge-base/edit/' . $id . '?error=missing_fields');
        }

        $this->model->updateArticle($id, [
            'title' => $title,
            'content' => $content,
        ], $categoryIds);

        if (!empty($_FILES['attachments']['name'][0])) {
            $this->handleAttachmentsUpload($id, $_FILES['attachments']);
        }

        $this->redirect('/knowledge-base/show/' . $id . '?saved=1');
    }

    public function delete(int $id): void {
        $article = $this->model->getById($id);
        if (!$article) {
            $this->redirect('/knowledge-base?error=not_found');
        }

        if (!$this->canDeleteArticle($article)) {
            $this->redirect('/knowledge-base/show/' . $id . '?error=unauthorized');
        }

        $this->validateCsrfToken();

        foreach ($article['attachments'] as $attachment) {
            $path = APP_ROOT . '/' . ltrim($attachment['file_path'], '/');
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $this->model->deleteArticle($id);
        $this->redirect('/knowledge-base?deleted=1');
    }

    public function file(int $attachmentId): void {
        if (!$this->canViewKnowledgeBase()) {
            http_response_code(403);
            exit('Unauthorized');
        }

        $attachment = $this->model->getAttachmentById($attachmentId);
        if (!$attachment) {
            http_response_code(404);
            exit('Not found');
        }

        $path = APP_ROOT . '/' . ltrim($attachment['file_path'], '/');
        if (!file_exists($path)) {
            http_response_code(404);
            exit('File not found');
        }

        $safeName = preg_replace('/[^\w\-.]/', '_', $attachment['original_filename']) ?: 'attachment';

        header('Content-Type: ' . $attachment['mime_type']);
        header('Content-Length: ' . filesize($path));

        $disposition = $this->isInlineMime($attachment['mime_type']) ? 'inline' : 'attachment';
        header('Content-Disposition: ' . $disposition . '; filename="' . $safeName . '"');
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    public function categories(): void {
        if (!$this->canManageCategories()) {
            $this->redirect('/knowledge-base?error=unauthorized');
        }

        $this->view('knowledge_base/categories', [
            'title' => 'Κατηγορίες Knowledge Base - ' . APP_NAME,
            'categories' => $this->model->getAllCategories(),
        ]);
    }

    public function storeCategory(): void {
        if (!$this->canManageCategories()) {
            $this->redirect('/knowledge-base?error=unauthorized');
        }

        $this->validateCsrfToken();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->redirect('/knowledge-base/categories?error=missing_name');
        }

        $user = $this->getCurrentUser();
        $ok = $this->model->createCategory($name, (int)($user['id'] ?? 0));

        $this->redirect('/knowledge-base/categories?' . ($ok ? 'saved=1' : 'error=exists'));
    }

    public function deleteCategory(int $id): void {
        if (!$this->canManageCategories()) {
            $this->redirect('/knowledge-base?error=unauthorized');
        }

        $this->validateCsrfToken();
        $this->model->deleteCategory($id);
        $this->redirect('/knowledge-base/categories?deleted=1');
    }

    public function exportPdf(int $id): void {
        if (!$this->canExportKnowledgeBase()) {
            $this->redirect('/knowledge-base?error=unauthorized');
        }

        $article = $this->model->getById($id);
        if (!$article) {
            $this->redirect('/knowledge-base?error=not_found');
        }

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle('Knowledge Base - ' . $article['title']);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);

        $categories = array_map(static fn($c) => htmlspecialchars($c['name']), $article['categories'] ?? []);
        $attachments = array_map(static fn($a) => htmlspecialchars($a['original_filename']), $article['attachments'] ?? []);

        $html = '<h2>' . htmlspecialchars($article['title']) . '</h2>';
        $html .= '<p><strong>Κατηγορίες:</strong> ' . (!empty($categories) ? implode(', ', $categories) : '—') . '</p>';
        $html .= '<p><strong>Συντάκτης:</strong> ' . htmlspecialchars($article['author_name'] ?? '—') . '</p>';
        $html .= '<p><strong>Ημ. δημιουργίας:</strong> ' . date('d/m/Y H:i', strtotime($article['created_at'])) . '</p>';
        $html .= '<hr>';
        $html .= '<div>' . nl2br(htmlspecialchars($article['content'])) . '</div>';

        if (!empty($attachments)) {
            $html .= '<hr><p><strong>Συνημμένα:</strong><br>' . implode('<br>', $attachments) . '</p>';
        }

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('knowledge_case_' . $article['id'] . '.pdf', 'D');
        exit;
    }

    public function exportListPdf(): void {
        if (!$this->canExportKnowledgeBase()) {
            $this->redirect('/knowledge-base?error=unauthorized');
        }

        $search = trim($_GET['search'] ?? '');
        $categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
        $articles = $this->model->getAll(1, 1000, $search ?: null, $categoryId ?: null);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('HandyCRM');
        $pdf->SetAuthor(APP_NAME);
        $pdf->SetTitle('Knowledge Base List');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 9);

        $html = '<h3>Knowledge Base - Λίστα Άρθρων</h3>';
        if ($search !== '') {
            $html .= '<p><strong>Αναζήτηση:</strong> ' . htmlspecialchars($search) . '</p>';
        }

        $html .= '<table border="1" cellpadding="4">
            <thead>
                <tr style="background-color:#f3f4f6;">
                    <th width="40%"><strong>Τίτλος</strong></th>
                    <th width="25%"><strong>Κατηγορίες</strong></th>
                    <th width="20%"><strong>Συντάκτης</strong></th>
                    <th width="15%"><strong>Ημ/νία</strong></th>
                </tr>
            </thead>
            <tbody>';

        foreach ($articles as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($row['title']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['categories'] ?: '—') . '</td>';
            $html .= '<td>' . htmlspecialchars($row['author_name'] ?: '—') . '</td>';
            $html .= '<td>' . date('d/m/Y', strtotime($row['created_at'])) . '</td>';
            $html .= '</tr>';
        }

        if (empty($articles)) {
            $html .= '<tr><td colspan="4">Δεν βρέθηκαν άρθρα.</td></tr>';
        }

        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('knowledge_base_list_' . date('Ymd_His') . '.pdf', 'D');
        exit;
    }

    private function canViewKnowledgeBase(): bool {
        return $this->isAdmin() || $this->isSupervisor() || $this->isTechnician() || can('knowledge_base.view');
    }

    private function canCreateArticle(): bool {
        return $this->isAdmin() || $this->isSupervisor() || $this->isTechnician() || can('knowledge_base.create');
    }

    private function canExportKnowledgeBase(): bool {
        return $this->isAdmin() || $this->isSupervisor() || $this->isTechnician() || can('knowledge_base.export');
    }

    private function canManageCategories(): bool {
        return $this->isAdmin() || $this->isSupervisor() || can('knowledge_categories.manage');
    }

    private function canEditArticle(array $article): bool {
        if ($this->isAdmin() || $this->isSupervisor()) {
            return true;
        }

        $user = $this->getCurrentUser();
        $isOwner = (int)($article['created_by'] ?? 0) === (int)($user['id'] ?? 0);

        return $isOwner && ($this->isTechnician() || can('knowledge_base.edit'));
    }

    private function canDeleteArticle(array $article): bool {
        if ($this->isAdmin() || $this->isSupervisor()) {
            return true;
        }

        $user = $this->getCurrentUser();
        $isOwner = (int)($article['created_by'] ?? 0) === (int)($user['id'] ?? 0);

        return $isOwner && ($this->isTechnician() || can('knowledge_base.delete'));
    }

    private function handleAttachmentsUpload(int $articleId, array $files): void {
        $uploadDir = APP_ROOT . '/uploads/knowledge-base/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedMime = [
            'image/jpeg' => ['ext' => 'jpg', 'max' => 10 * 1024 * 1024],
            'image/png' => ['ext' => 'png', 'max' => 10 * 1024 * 1024],
            'image/webp' => ['ext' => 'webp', 'max' => 10 * 1024 * 1024],
            'application/pdf' => ['ext' => 'pdf', 'max' => 30 * 1024 * 1024],
        ];

        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp = $files['tmp_name'][$i];
            $originalName = $files['name'][$i] ?? 'attachment';
            $size = (int)($files['size'][$i] ?? 0);

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string)$finfo->file($tmp);

            if (!isset($allowedMime[$mime])) {
                continue;
            }

            if ($size <= 0 || $size > $allowedMime[$mime]['max']) {
                continue;
            }

            $filename = uniqid('kb_' . time() . '_', true) . '.' . $allowedMime[$mime]['ext'];
            $destination = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($tmp, $destination)) {
                continue;
            }

            $relativePath = 'uploads/knowledge-base/' . date('Y/m') . '/' . $filename;

            $this->model->addAttachment($articleId, [
                'file_path' => $relativePath,
                'original_filename' => $originalName,
                'mime_type' => $mime,
                'file_size' => $size,
            ]);
        }
    }

    private function isInlineMime(string $mime): bool {
        return strpos($mime, 'image/') === 0 || $mime === 'application/pdf';
    }
}
