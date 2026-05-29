<?php

require_once 'classes/BaseModel.php';

class KnowledgeBase extends BaseModel {
    protected $table = 'knowledge_articles';
    protected $primaryKey = 'id';

    public function getAll(int $page = 1, int $perPage = 20, ?string $search = null, ?int $categoryId = null): array {
        $db = $this->db->connect();
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = '(a.title LIKE ? OR a.content LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($categoryId) {
            $where[] = 'EXISTS (
                SELECT 1 FROM knowledge_article_categories kac
                WHERE kac.article_id = a.id AND kac.category_id = ?
            )';
            $params[] = $categoryId;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT
                    a.id,
                    a.title,
                    a.content,
                    a.created_by,
                    a.created_at,
                    a.updated_at,
                    CONCAT(u.first_name, ' ', u.last_name) AS author_name,
                    GROUP_CONCAT(DISTINCT kc.name ORDER BY kc.name SEPARATOR ', ') AS categories
                FROM knowledge_articles a
                LEFT JOIN users u ON u.id = a.created_by
                LEFT JOIN knowledge_article_categories kac ON kac.article_id = a.id
                LEFT JOIN knowledge_categories kc ON kc.id = kac.category_id
                WHERE {$whereSql}
                GROUP BY a.id
                ORDER BY a.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount(?string $search = null, ?int $categoryId = null): int {
        $db = $this->db->connect();

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = '(a.title LIKE ? OR a.content LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($categoryId) {
            $where[] = 'EXISTS (
                SELECT 1 FROM knowledge_article_categories kac
                WHERE kac.article_id = a.id AND kac.category_id = ?
            )';
            $params[] = $categoryId;
        }

        $sql = 'SELECT COUNT(*) FROM knowledge_articles a WHERE ' . implode(' AND ', $where);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public function getById(int $id): ?array {
        $db = $this->db->connect();

        $sql = "SELECT
                    a.*,
                    CONCAT(u.first_name, ' ', u.last_name) AS author_name
                FROM knowledge_articles a
                LEFT JOIN users u ON u.id = a.created_by
                WHERE a.id = ?
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            return null;
        }

        $article['category_ids'] = $this->getArticleCategoryIds($id);
        $article['categories'] = $this->getArticleCategories($id);
        $article['attachments'] = $this->getAttachments($id);

        return $article;
    }

    public function createArticle(array $data, array $categoryIds): int {
        $db = $this->db->connect();

        $stmt = $db->prepare('INSERT INTO knowledge_articles (title, content, created_by) VALUES (?, ?, ?)');
        $stmt->execute([$data['title'], $data['content'], $data['created_by']]);
        $articleId = (int)$db->lastInsertId();

        $this->replaceCategories($articleId, $categoryIds);

        return $articleId;
    }

    public function updateArticle(int $id, array $data, array $categoryIds): void {
        $db = $this->db->connect();

        $stmt = $db->prepare('UPDATE knowledge_articles SET title = ?, content = ? WHERE id = ?');
        $stmt->execute([$data['title'], $data['content'], $id]);

        $this->replaceCategories($id, $categoryIds);
    }

    public function deleteArticle(int $id): void {
        $db = $this->db->connect();
        $stmt = $db->prepare('DELETE FROM knowledge_articles WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function getAllCategories(): array {
        $db = $this->db->connect();
        $stmt = $db->query('SELECT * FROM knowledge_categories ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCategory(string $name, ?int $createdBy): bool {
        $db = $this->db->connect();
        $stmt = $db->prepare('INSERT INTO knowledge_categories (name, created_by) VALUES (?, ?)');
        return $stmt->execute([$name, $createdBy]);
    }

    public function deleteCategory(int $id): void {
        $db = $this->db->connect();
        $stmt = $db->prepare('DELETE FROM knowledge_categories WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function addAttachment(int $articleId, array $attachment): void {
        $db = $this->db->connect();
        $stmt = $db->prepare('INSERT INTO knowledge_attachments (article_id, file_path, original_filename, mime_type, file_size) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $articleId,
            $attachment['file_path'],
            $attachment['original_filename'],
            $attachment['mime_type'],
            $attachment['file_size'],
        ]);
    }

    public function getAttachments(int $articleId): array {
        $db = $this->db->connect();
        $stmt = $db->prepare('SELECT * FROM knowledge_attachments WHERE article_id = ? ORDER BY id ASC');
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttachmentById(int $attachmentId): ?array {
        $db = $this->db->connect();
        $stmt = $db->prepare('SELECT * FROM knowledge_attachments WHERE id = ? LIMIT 1');
        $stmt->execute([$attachmentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function replaceCategories(int $articleId, array $categoryIds): void {
        $db = $this->db->connect();

        $stmt = $db->prepare('DELETE FROM knowledge_article_categories WHERE article_id = ?');
        $stmt->execute([$articleId]);

        if (empty($categoryIds)) {
            return;
        }

        $insert = $db->prepare('INSERT INTO knowledge_article_categories (article_id, category_id) VALUES (?, ?)');

        foreach ($categoryIds as $categoryId) {
            $insert->execute([$articleId, $categoryId]);
        }
    }

    private function getArticleCategoryIds(int $articleId): array {
        $db = $this->db->connect();
        $stmt = $db->prepare('SELECT category_id FROM knowledge_article_categories WHERE article_id = ?');
        $stmt->execute([$articleId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function getArticleCategories(int $articleId): array {
        $db = $this->db->connect();
        $stmt = $db->prepare(
            'SELECT kc.*
             FROM knowledge_categories kc
             INNER JOIN knowledge_article_categories kac ON kac.category_id = kc.id
             WHERE kac.article_id = ?
             ORDER BY kc.name ASC'
        );
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
