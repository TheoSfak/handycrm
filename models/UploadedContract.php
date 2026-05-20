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
     * Extract text and key fields from a PDF file using smalot/pdfparser.
     * Returns array with keys: text, title, amount, start_date, end_date, description
     */
    public static function extractFromPdf(string $filePath): array {
        $result = [
            'text'        => '',
            'title'       => '',
            'amount'      => '',
            'start_date'  => '',
            'end_date'    => '',
            'description' => '',
        ];

        if (!file_exists($filePath)) {
            return $result;
        }

        try {
            $parserClass = '\Smalot\PdfParser\Parser';
            if (!class_exists($parserClass)) {
                return $result;
            }
            $parser  = new $parserClass();
            $pdf     = $parser->parseFile($filePath);
            $text    = $pdf->getText();
            $result['text'] = $text;

            // ── TITLE ─────────────────────────────────────────────────────
            // Look for "ΣΥΜΦΩΝΗΤΙΚΟ", "ΣΥΜΦΩΝΙΑ", "ΣΥΜΒΑΣΗ", "ΣΥΜΒΟΛΑΙΟ"
            // or the first meaningful non-whitespace line
            if (preg_match('/ΣΥΜΦΩΝΗΤΙΚ[ΟΑ]\s+[^\n]{5,80}/ui', $text, $m)) {
                $result['title'] = trim($m[0]);
            } elseif (preg_match('/ΣΥΜΒΑΣΗ\s+[^\n]{5,80}/ui', $text, $m)) {
                $result['title'] = trim($m[0]);
            } else {
                // First substantial line
                foreach (explode("\n", $text) as $line) {
                    $line = trim($line);
                    if (mb_strlen($line) >= 10) {
                        $result['title'] = mb_substr($line, 0, 200);
                        break;
                    }
                }
            }

            // ── AMOUNT ────────────────────────────────────────────────────
            // Match patterns like: 1.500,00 € / €1.500,00 / 1500 ευρώ
            if (preg_match('/(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{1,2})?)\s*(?:€|EUR|ευρ)/iu', $text, $m)) {
                // Normalize Greek number format (1.500,00 → 1500.00)
                $raw = $m[1];
                $raw = str_replace('.', '', $raw);  // remove thousand sep
                $raw = str_replace(',', '.', $raw); // decimal sep
                $result['amount'] = $raw;
            }

            // ── DATES ─────────────────────────────────────────────────────
            // Collect all dates in the document (dd/mm/yyyy or dd-mm-yyyy or dd.mm.yyyy)
            $datePattern = '/\b(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{4})\b/';
            preg_match_all($datePattern, $text, $dateMatches, PREG_SET_ORDER);

            $dates = [];
            foreach ($dateMatches as $dm) {
                $day   = str_pad($dm[1], 2, '0', STR_PAD_LEFT);
                $month = str_pad($dm[2], 2, '0', STR_PAD_LEFT);
                $year  = $dm[3];
                if ((int)$month >= 1 && (int)$month <= 12 && (int)$day >= 1 && (int)$day <= 31) {
                    $dates[] = "$year-$month-$day";
                }
            }

            if (!empty($dates)) {
                sort($dates);
                $result['start_date'] = $dates[0];
                $result['end_date']   = end($dates);
            }

            // ── DESCRIPTION ───────────────────────────────────────────────
            // Look for "ΑΝΤΙΚΕΙΜΕΝΟ", "ΕΡΓΑΣΙΕΣ", "ΕΡΓΟ", "ΑΦΟΡΑ"
            foreach (['/ΑΝΤΙΚΕΙΜΕΝΟ[:\s]+([^\n]{10,300})/ui',
                      '/ΕΡΓΑΣΙΕΣ[:\s]+([^\n]{10,300})/ui',
                      '/ΑΦΟΡΑ[:\s]+([^\n]{10,300})/ui',
                      '/ΑΦΟΡ[ΑΩ]\s+(?:ΤΗΝ|ΤΟ|ΤΑ)\s+([^\n]{10,300})/ui'] as $pat) {
                if (preg_match($pat, $text, $m)) {
                    $result['description'] = trim($m[1]);
                    break;
                }
            }

        } catch (\Exception $e) {
            error_log('PDF extraction error: ' . $e->getMessage());
        }

        return $result;
    }
}
