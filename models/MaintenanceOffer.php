<?php

class MaintenanceOffer extends BaseModel {
    protected $table = 'maintenance_offers';
    protected $primaryKey = 'id';

    /**
     * Generate next offer number: PRO-YYYYMM-XXXX
     */
    public function generateOfferNumber(): string {
        $prefix = 'PRO-' . date('Ym') . '-';
        $db = $this->db->connect();
        $stmt = $db->prepare("SELECT offer_number FROM maintenance_offers WHERE offer_number LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();
        $seq = $last ? (intval(substr($last, -4)) + 1) : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate standard price based on transformer count
     */
    public static function calculatePrice(int $count): float {
        $prices = [1 => 400, 2 => 600, 3 => 900, 4 => 1200];
        return (float)($prices[$count] ?? ($count > 4 ? 1200 + ($count - 4) * 300 : 400));
    }

    /**
     * Get paginated list with optional search/filter
     */
    public function getAll(
        int $page = 1,
        int $perPage = 20,
        ?string $search = null,
        ?string $accepted = null
    ): array {
        $db = $this->db->connect();
        $where = ['o.deleted_at IS NULL'];
        $params = [];

        if ($search) {
            $where[] = '(o.company_name LIKE ? OR o.offer_number LIKE ? OR o.phone LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($accepted !== null && $accepted !== '') {
            $where[] = 'o.accepted = ?';
            $params[] = (int)$accepted;
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
                FROM maintenance_offers o
                LEFT JOIN users u ON u.id = o.created_by
                WHERE $whereStr
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total rows for pagination
     */
    public function getTotalCount(?string $search = null, ?string $accepted = null): int {
        $db = $this->db->connect();
        $where = ['deleted_at IS NULL'];
        $params = [];

        if ($search) {
            $where[] = '(company_name LIKE ? OR offer_number LIKE ? OR phone LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($accepted !== null && $accepted !== '') {
            $where[] = 'accepted = ?';
            $params[] = (int)$accepted;
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT COUNT(*) FROM maintenance_offers WHERE $whereStr");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
