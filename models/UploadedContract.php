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

        // ── Strategy 1: smalot/pdfparser (best quality) ───────────────────
        $text = '';
        try {
            $parserClass = '\Smalot\PdfParser\Parser';
            if (class_exists($parserClass)) {
                $parser = new $parserClass();
                $pdf    = $parser->parseFile($filePath);
                $text   = $pdf->getText();
            }
        } catch (\Exception $e) {
            error_log('PDF smalot extraction error: ' . $e->getMessage());
        }

        // ── Strategy 2: pdftotext shell command (poppler-utils) ───────────
        if (strlen(trim($text)) < 20) {
            $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
            if (!in_array('shell_exec', $disabled)) {
                $cmd  = 'pdftotext ' . escapeshellarg($filePath) . ' - 2>&1';
                $out  = @shell_exec($cmd);
                if ($out && strlen(trim($out)) > 20 && strpos($out, 'command not found') === false && strpos($out, 'No such file') === false) {
                    $text = $out;
                }
            }
        }

        // ── Strategy 3: raw PDF stream extraction (no dependencies) ───────
        if (strlen(trim($text)) < 20) {
            $text = self::extractTextRaw($filePath);
        }

        $result['text'] = $text;
        if (strlen(trim($text)) === 0) {
            return $result;
        }

        // ── TITLE ──────────────────────────────────────────────────────────
        // 1. Look for a full line containing a contract keyword
        if (preg_match('/^.*(?:ΣΥΜΦΩΝΗΤΙΚ[ΟΑ]|ΣΥΜΒΑΣΗ|ΣΥΜΒΟΛΑΙΟ).{0,120}$/mui', $text, $m)) {
            $result['title'] = trim($m[0]);
        } else {
            // 2. Fallback: first "clean" all-caps-ish line (skip lines with digits/refs)
            foreach (explode("\n", $text) as $line) {
                $line = trim($line);
                // Skip short, mostly-numeric, or reference-number lines
                if (mb_strlen($line) < 15) continue;
                if (preg_match('/\d{4}.*Π\.|Α\.\s*Π\.|ΑΡ\.\s*ΠΡΩΤ/ui', $line)) continue;
                if (preg_match('/^\s*[\d\.\,\/\-\s]+$/', $line)) continue;
                $result['title'] = mb_substr($line, 0, 200);
                break;
            }
        }

        // ── AMOUNT ─────────────────────────────────────────────────────────
        // Matches: 1.234,56 € / 1234,56€ / 1.234.000,00 EUR / ευρώ
        $amountPatterns = [
            // Greek thousand-separator format: 1.234,56 €
            '/(\d{1,3}(?:\.\d{3})+(?:,\d{1,2})?)\s*(?:€|EUR|ευρ)/iu',
            // Plain with comma decimal: 1234,56 €
            '/(\d+,\d{1,2})\s*(?:€|EUR|ευρ)/iu',
            // Plain integer: 1234 €
            '/(\d{3,})\s*(?:€|EUR|ευρ)/iu',
        ];
        foreach ($amountPatterns as $pat) {
            if (preg_match($pat, $text, $m)) {
                $raw = $m[1];
                // Remove thousand dots, convert comma decimal to dot
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
                if (is_numeric($raw)) {
                    $result['amount'] = $raw;
                    break;
                }
            }
        }

        // ── DATES ──────────────────────────────────────────────────────────
        // Restrict year to 1900-2099 to avoid misidentifying reference numbers as years
        $datePattern = '/\b(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-]((?:19|20)\d{2})\b/';
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

        // ── DESCRIPTION ────────────────────────────────────────────────────
        foreach (['/ΑΝΤΙΚΕΙΜΕΝΟ[:\s]+([^\n]{10,300})/ui',
                  '/ΕΡΓΑΣΙΕΣ[:\s]+([^\n]{10,300})/ui',
                  '/ΑΦΟΡΑ[:\s]+([^\n]{10,300})/ui',
                  '/ΑΦΟΡ[ΑΩ]\s+(?:ΤΗΝ|ΤΟ|ΤΑ)\s+([^\n]{10,300})/ui'] as $pat) {
            if (preg_match($pat, $text, $m)) {
                $result['description'] = trim($m[1]);
                break;
            }
        }

        return $result;
    }

    /**
     * Raw fallback: decompress PDF content streams and extract plain text.
     * Works for most PDFs generated by Word / LibreOffice without any library.
     */
    private static function extractTextRaw(string $filePath): string {
        $content = file_get_contents($filePath);
        if (!$content) {
            return '';
        }

        $text = '';

        // Try decompressing FlateDecode streams first (most modern PDFs)
        if (function_exists('gzuncompress') || function_exists('gzinflate')) {
            if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $content, $streams)) {
                foreach ($streams[1] as $stream) {
                    $decompressed = false;
                    if (function_exists('gzuncompress')) {
                        $decompressed = @gzuncompress($stream);
                    }
                    if ($decompressed === false && function_exists('gzinflate')) {
                        $decompressed = @gzinflate(substr($stream, 2));
                    }
                    $src = $decompressed !== false ? $decompressed : $stream;
                    $text .= self::extractBtEtText($src) . "\n";
                }
            }
        }

        // Also scan the raw PDF for uncompressed BT...ET blocks
        $text .= self::extractBtEtText($content);

        // Normalise whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    /** Extract text from PDF BT...ET operator blocks */
    private static function extractBtEtText(string $src): string {
        $out = '';
        if (!preg_match_all('/BT\s+(.*?)\s*ET/s', $src, $blocks)) {
            return $out;
        }
        foreach ($blocks[1] as $block) {
            // Parenthesis strings: (Hello) Tj  or  [(Hel) -30 (lo)] TJ
            if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)\s*Tj/s', $block, $m)) {
                foreach ($m[1] as $t) {
                    $out .= self::decodePdfStr($t);
                }
                $out .= "\n";
            }
            if (preg_match_all('/\[((?:[^\[\]]|\\\\.)*)\]\s*TJ/s', $block, $m)) {
                foreach ($m[1] as $arr) {
                    if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/s', $arr, $parts)) {
                        foreach ($parts[1] as $t) {
                            $out .= self::decodePdfStr($t);
                        }
                    }
                }
                $out .= "\n";
            }
        }
        return $out;
    }

    /** Decode a raw PDF string (octal escapes, encoding detection) */
    private static function decodePdfStr(string $s): string {
        // Octal escapes \ddd
        $s = preg_replace_callback('/\\\\([0-7]{3})/', function($m) {
            return chr(octdec($m[1]));
        }, $s);
        // Common escapes
        $s = str_replace(['\\n','\\r','\\t','\\\\','\\(','\\)'],
                         ["\n", "\r", "\t", '\\',   '(',   ')'], $s);
        // Convert to UTF-8 if needed
        if (!mb_check_encoding($s, 'UTF-8')) {
            foreach (['ISO-8859-7','Windows-1253','ISO-8859-1'] as $enc) {
                $converted = @mb_convert_encoding($s, 'UTF-8', $enc);
                if ($converted && mb_check_encoding($converted, 'UTF-8')) {
                    return $converted;
                }
            }
        }
        return $s;
    }
}
