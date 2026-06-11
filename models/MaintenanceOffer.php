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

    /**
     * Get all accepted offers ordered by accepted_at DESC (for dashboard)
     */
    public function getAcceptedList(): array {
        $db = $this->db->connect();
        $stmt = $db->query("
            SELECT id, offer_number, company_name, phone, transformers_count, price, accepted_at
            FROM maintenance_offers
            WHERE deleted_at IS NULL AND accepted = 1
            ORDER BY accepted_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get offers with a future scheduled_date (for dashboard)
     */
    public function getScheduledFutureList(): array {
        $db = $this->db->connect();
        $stmt = $db->query("
            SELECT id, offer_number, company_name, transformers_count, price, scheduled_date
            FROM maintenance_offers
            WHERE deleted_at IS NULL
              AND scheduled_date IS NOT NULL
              AND scheduled_date >= CURDATE()
            ORDER BY scheduled_date ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count accepted contracts whose contract_end_date has already passed
     */
    public function getExpiredCount(): int {
        $db = $this->db->connect();
        $stmt = $db->query("
            SELECT COUNT(*) FROM maintenance_offers
            WHERE deleted_at IS NULL
              AND accepted = 1
              AND contract_end_date IS NOT NULL
              AND contract_end_date < CURDATE()
        ");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Count accepted contracts expiring within the next $days days
     */
    public function getExpiringCount(int $days = 30): int {
        $db = $this->db->connect();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM maintenance_offers
            WHERE deleted_at IS NULL
              AND accepted = 1
              AND contract_end_date IS NOT NULL
              AND contract_end_date >= CURDATE()
              AND contract_end_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
        ");
        $stmt->execute([$days]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get list of accepted contracts expiring within $days days (or already expired)
     * Returns company_name, offer_number, contract_end_date, id
     */
    public function getExpiringList(int $days = 30): array {
        $db = $this->db->connect();
        $stmt = $db->prepare("
            SELECT id, offer_number, company_name, phone, contract_end_date,
                   DATEDIFF(contract_end_date, CURDATE()) AS days_left
            FROM maintenance_offers
            WHERE deleted_at IS NULL
              AND accepted = 1
              AND contract_end_date IS NOT NULL
              AND contract_end_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY contract_end_date ASC
            LIMIT 20
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
