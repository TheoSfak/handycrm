<?php

require_once 'classes/BaseModel.php';

class UploadedContract extends BaseModel {
    protected $table = 'uploaded_contracts';
    protected $primaryKey = 'id';

    /**
     * Get paginated list with optional search
     */
    public function getAll(int $page = 1, int $perPage = 20, ?string $search = null): array {
        $db = $this->db->connect();
        $where = ['uc.deleted_at IS NULL'];
        $params = [];

        if ($search) {
            $where[] = '(uc.customer_name LIKE ? OR uc.title LIKE ? OR uc.original_filename LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $sql = "SELECT uc.*, CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
                FROM uploaded_contracts uc
                LEFT JOIN users u ON u.id = uc.created_by
                WHERE $whereStr
                ORDER BY uc.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count rows for pagination
     */
    public function getTotalCount(?string $search = null): int {
        $db = $this->db->connect();
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if ($search) {
            $where[] = '(customer_name LIKE ? OR title LIKE ? OR original_filename LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT COUNT(*) FROM uploaded_contracts WHERE $whereStr");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Find single record by id (non-deleted)
     */
    public function findActive(int $id): ?array {
        $db   = $this->db->connect();
        $stmt = $db->prepare("SELECT * FROM uploaded_contracts WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Insert new contract record
     */
    public function createContract(array $data): int {
        $db   = $this->db->connect();
        $stmt = $db->prepare("
            INSERT INTO uploaded_contracts
                (customer_name, file_path, original_filename, title, amount,
                 start_date, end_date, description, notes, extracted_text, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['customer_name'],
            $data['file_path'],
            $data['original_filename'],
            $data['title']          ?? null,
            $data['amount']         ?? null,
            $data['start_date']     ?? null,
            $data['end_date']       ?? null,
            $data['description']    ?? null,
            $data['notes']          ?? null,
            $data['extracted_text'] ?? null,
            $data['created_by']     ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    /**
     * Update editable fields after scan or manual edit
     */
    public function updateFields(int $id, array $data): void {
        $db   = $this->db->connect();
        $stmt = $db->prepare("
            UPDATE uploaded_contracts SET
                customer_name  = ?,
                title          = ?,
                amount         = ?,
                start_date     = ?,
                end_date       = ?,
                description    = ?,
                notes          = ?,
                extracted_text = ?
            WHERE id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([
            $data['customer_name'],
            $data['title']          ?? null,
            $data['amount']         ?? null,
            $data['start_date']     ?? null,
            $data['end_date']       ?? null,
            $data['description']    ?? null,
            $data['notes']          ?? null,
            $data['extracted_text'] ?? null,
            $id,
        ]);
    }

    /**
     * Soft-delete
     */
    public function softDelete(int $id): void {
        $db   = $this->db->connect();
        $stmt = $db->prepare("UPDATE uploaded_contracts SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Count contracts expiring within $days days (for dashboard reminder)
     */
    public function getExpiringCount(int $days = 30): int {
        $db   = $this->db->connect();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM uploaded_contracts
            WHERE deleted_at IS NULL
              AND end_date IS NOT NULL
              AND end_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ");
        $stmt->execute([$days]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * List of expiring contracts for dashboard
     */
    public function getExpiringList(int $days = 30): array {
        $db   = $this->db->connect();
        $stmt = $db->prepare("
            SELECT id, customer_name, title, end_date,
                   DATEDIFF(end_date, CURDATE()) AS days_left
            FROM uploaded_contracts
            WHERE deleted_at IS NULL
              AND end_date IS NOT NULL
              AND end_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY end_date ASC
            LIMIT 20
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extract text and key fields from a PDF file.
     * Delegates to ContractParserService.
     * Returns array with keys: text, title, amount, start_date, end_date, description, strategy
     */
    public static function extractFromPdf(string $filePath, ?string $originalFilename = null): array {
        require_once __DIR__ . '/../classes/ContractParserService.php';
        return ContractParserService::extractFromPdf($filePath, $originalFilename);
    }
}
