<?php

require_once 'classes/BaseModel.php';

class PriceList extends BaseModel {
    protected $table = 'price_lists';
    protected $primaryKey = 'id';

    public function getAll(int $page = 1, int $perPage = 20, ?string $search = null): array {
        $db = $this->db->connect();
        $offset = ($page - 1) * $perPage;

        $where = '1=1';
        $params = [];

        if ($search) {
            $where .= ' AND (p.title LIKE ? OR p.original_filename LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $sql = "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
                FROM price_lists p
                LEFT JOIN users u ON u.id = p.created_by
                WHERE {$where}
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalCount(?string $search = null): int {
        $db = $this->db->connect();

        $where = '1=1';
        $params = [];

        if ($search) {
            $where .= ' AND (title LIKE ? OR original_filename LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM price_lists WHERE {$where}");
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public function createPriceList(array $data): int {
        $db = $this->db->connect();

        $stmt = $db->prepare('INSERT INTO price_lists (title, file_path, original_filename, mime_type, file_size, created_by) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['title'],
            $data['file_path'],
            $data['original_filename'],
            $data['mime_type'],
            $data['file_size'],
            $data['created_by'],
        ]);

        return (int)$db->lastInsertId();
    }

    public function findOne(int $id): ?array {
        $db = $this->db->connect();
        $stmt = $db->prepare('SELECT * FROM price_lists WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deleteOne(int $id): void {
        $db = $this->db->connect();
        $stmt = $db->prepare('DELETE FROM price_lists WHERE id = ?');
        $stmt->execute([$id]);
    }
}
