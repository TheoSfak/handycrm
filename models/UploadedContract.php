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
     * Returns array with keys: text, title, amount, start_date, end_date, description, strategy
     */
    public static function extractFromPdf(string $filePath, ?string $originalFilename = null): array {
        $result = [
            'text'        => '',
            'title'       => '',
            'amount'      => '',
            'start_date'  => '',
            'end_date'    => '',
            'description' => '',
            'strategy'    => '',
        ];

        if (!file_exists($filePath)) {
            return $result;
        }

        // ── Strategy 1: smalot/pdfparser (best quality) ───────────────────
        $text = '';
        try {
            $parserClass = '\Smalot\PdfParser\Parser';
            if (class_exists($parserClass)) {
                $configClass = '\Smalot\PdfParser\Config';
                if (class_exists($configClass)) {
                    $cfg = new $configClass();
                    $cfg->setFontSpaceLimit(-100); // improves word spacing in Greek PDFs
                    $cfg->setRetainImageContent(false);
                    $parser = new $parserClass([], $cfg);
                } else {
                    $parser = new $parserClass();
                }
                $pdf  = $parser->parseFile($filePath);
                $text = $pdf->getText();
                if (strlen(trim($text)) >= 100) {
                    $result['strategy'] = 'smalot';
                }
            }
        } catch (\Exception $e) {
            error_log('PDF smalot extraction error: ' . $e->getMessage());
        }

        // ── Strategy 2: pdftotext shell command (poppler-utils) ───────────
        // Falls back to exec() on hosts where shell_exec is restricted.
        // Only triggered when smalot returned very little (<100 chars).
        if (strlen(trim($text)) < 100) {
            $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
            $cmd = 'pdftotext -enc UTF-8 ' . escapeshellarg($filePath) . ' -';
            $out = '';
            if (!in_array('shell_exec', $disabled) && function_exists('shell_exec')) {
                $out = (string)@shell_exec($cmd . ' 2>&1');
            } elseif (!in_array('exec', $disabled) && function_exists('exec')) {
                $lines = [];
                @exec($cmd . ' 2>/dev/null', $lines);
                $out = implode("\n", $lines);
            }
            if (strlen(trim($out)) > 20
                && strpos($out, 'command not found') === false
                && strpos($out, 'No such file') === false
                && strpos($out, 'Error') === false) {
                $text = $out;
                $result['strategy'] = 'pdftotext';
            }
        }

        // ── Strategy 3: raw PDF stream extraction (no dependencies) ───────
        if (strlen(trim($text)) < 100) {
            $raw3 = self::extractTextRaw($filePath);
            if (strlen(trim($raw3)) > strlen(trim($text))) {
                $text = $raw3;
                $result['strategy'] = 'raw';
            }
        }

        if ($result['strategy'] === '' && strlen(trim($text)) >= 100) {
            $result['strategy'] = 'smalot'; // smalot gave text but ratio was low
        }

        // ── Normalize Unicode (NFC) ────────────────────────────────────────
        // Greek PDFs often have decomposed chars (e.g. α + combining tonos)
        // that won't match regex patterns unless normalized to NFC.
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $text) ?? $text;
        if (function_exists('normalizer_normalize')) {
            $norm = normalizer_normalize($text, Normalizer::FORM_C);
            if ($norm !== false && $norm !== '') {
                $text = $norm;
            }
        }

        // ── Fix inter-character spaces ─────────────────────────────────────
        // smalot/pdfparser sometimes extracts large-font headers as
        // "Σ Υ Μ Β Α Σ Η" instead of "ΣΥΜΒΑΣΗ". Collapse runs of 3+ single
        // uppercase characters each followed by a space.
        $text = preg_replace_callback('/(?:[Α-ΩA-Z\d] ){3,}/u', function ($m) {
            return preg_replace('/\s+/u', '', $m[0]);
        }, $text) ?? $text;

        $result['text'] = $text;
        if (strlen(trim($text)) === 0) {
            return $result;
        }

        // ── TITLE ──────────────────────────────────────────────────────────
        // 1. Full line that starts with or contains a contract-type keyword
        $titlePatterns = [
            '/^[ \t]*(?:ΙΔΙΩΤΙΚΟ\s+)?ΣΥΜΦΩΝΗΤΙΚ[ΟΑ].{0,150}$/mui',
            '/^[ \t]*(?:ΙΔΙΩΤΙΚΗ\s+)?ΣΥΜΒΑΣΗ.{0,150}$/mui',
            '/^[ \t]*ΣΥΜΒΟΛΑΙΟ.{0,150}$/mui',
        ];
        foreach ($titlePatterns as $pat) {
            if (preg_match($pat, $text, $m)) {
                $result['title'] = trim($m[0]);
                break;
            }
        }
        if ($result['title'] === '') {
            // 2. Fallback: first "clean" line (skip reference/date/number-heavy lines)
            foreach (explode("\n", $text) as $line) {
                $line = trim($line);
                if (mb_strlen($line) < 15) continue;
                // Skip article/clause numbers like "1.", "2.", "(α)"
                if (preg_match('/^\s*[\d]+[\.)]/u', $line)) continue;
                // Skip protocol/reference markers
                if (preg_match('/Α\.\s*Π\.|ΑΡ\.\s*ΠΡΩΤ|ΑΡΙΘ\.\s*ΠΡΩΤ|ΑΔΑΜ:/ui', $line)) continue;
                // Skip lines with email or URL
                if (preg_match('/@|www\.|http/i', $line)) continue;
                // Skip lines with phone numbers (4+ consecutive digits not part of a year)
                if (preg_match('/\d{6,}/', $line)) continue;
                // Skip lines with quoted text markers «»
                if (preg_match('/[«»]/', $line)) continue;
                // Skip lines that are mostly numeric
                if (preg_match('/^\s*[\d\.\,\/\-\s]+$/', $line)) continue;
                $digitCount = preg_match_all('/\d/', $line);
                if ($digitCount > mb_strlen($line) * 0.4) continue;
                $result['title'] = mb_substr($line, 0, 200);
                break;
            }
        }

        // 3. Last resort: derive title from original filename
        if ($result['title'] === '' && $originalFilename) {
            $base = pathinfo($originalFilename, PATHINFO_FILENAME);
            $base = html_entity_decode($base, ENT_QUOTES | ENT_HTML5, 'UTF-8'); // decode &amp; etc.
            $base = str_replace(['_', '-'], ' ', $base);
            $base = preg_replace('/\s+/', ' ', $base);
            $result['title'] = mb_substr(trim($base), 0, 200);
        }

        // ── AMOUNT ─────────────────────────────────────────────────────────
        // Try keyword-based patterns first (more reliable in Greek contracts)
        $amountPatterns = [
            // "ανέρχεται σε X ευρώ" — very common in Greek procurement contracts
            '/ανέρχεται\s+σε\s+([\d]{1,3}(?:[\.,\s]\d{3})*(?:[,\.]\d{1,2})?)\s*(?:€|ευρ)/iu',
            // Label before amount: ΑΜΟΙΒΗ / ΤΙΜΗΜΑ (also accented τίμημα) / ΠΟΣΟ / ΣΥΝΟΛΟ
            '/(?:ΑΜΟΙΒ[ΗΑ]|τ[ίι]μημα|ΤΙΜΗΜΑ|ΠΟΣΟ|ΣΥΝΟΛ[ΟΑ]|ΤΙΜΗ\s+ΣΥΜΒΑΣ)[^\d]{0,30}([\d]{1,3}(?:[\.\s]\d{3})*(?:,\d{1,2})?)/ui',
            // € before number: € 1.234,56
            '/€\s*([\d]{1,3}(?:\.\d{3})*(?:,\d{1,2})?)/u',
            // Greek thousand-separator: 1.234,56 €/EUR/ευρ
            '/([\d]{1,3}(?:\.\d{3})+(?:,\d{1,2})?)\s*(?:€|EUR|ευρ)/iu',
            // Plain comma-decimal: 1234,56 €/EUR/ευρ
            '/([\d]+,\d{1,2})\s*(?:€|EUR|ευρ)/iu',
            // Integer only: 1234 €/EUR/ευρ
            '/([\d]{3,})\s*(?:€|EUR|ευρ)/iu',
            // Article 5.1 of N.4412/2016 contracts — contract price with stripped Greek text
            // e.g. "5.1. 7.865,00" where the Greek word for "price" has been lost
            '/5\.1[^0-9]{0,20}([\d]{1,3}(?:\.\d{3})+,\d{2})/u',
        ];
        foreach ($amountPatterns as $pat) {
            if (preg_match($pat, $text, $m)) {
                $raw = trim($m[1]);
                $raw = preg_replace('/\s/', '', $raw); // remove any spaces used as thousand-sep
                $raw = str_replace('.', '', $raw);     // remove dot thousand-separator
                $raw = str_replace(',', '.', $raw);    // comma decimal → dot decimal
                if (is_numeric($raw) && (float)$raw > 0) {
                    $result['amount'] = $raw;
                    break;
                }
            }
        }

        // Last-resort amount: largest Greek-formatted number (X.XXX,XX) in the document.
        // Handles PDFs where font encoding strips Greek keywords, leaving bare numbers.
        if ($result['amount'] === '') {
            preg_match_all('/(?<!\d)([\d]{1,3}(?:\.\d{3})+,\d{2})(?!\d)/u', $text, $am);
            if (!empty($am[1])) {
                // Use the FIRST large formatted number (>= 100), not the largest.
                // In Greek procurement contracts the price ex-ΦΠΑ appears before
                // the ΦΠΑ amount and the total-with-ΦΠΑ, so "first" is more reliable.
                foreach ($am[1] as $candidate) {
                    $val = (float)str_replace(',', '.', str_replace('.', '', $candidate));
                    if ($val >= 100.0) {
                        $result['amount'] = (string)$val;
                        break;
                    }
                }
            }
        }

        // ── DATES ──────────────────────────────────────────────────────────
        // Lookbehind filters out reference-code dates like "3122/07-03-2025"
        // (where the date is immediately preceded by / or a digit).
        $datePat = '/(?<![\d\/])(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-]((?:19|20)\d{2})(?!\d)/u';
        preg_match_all($datePat, $text, $dm, PREG_SET_ORDER);
        $allDates = [];
        foreach ($dm as $d) {
            $day   = str_pad($d[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($d[2], 2, '0', STR_PAD_LEFT);
            $year  = $d[3];
            if ((int)$month >= 1 && (int)$month <= 12 && (int)$day >= 1 && (int)$day <= 31) {
                $allDates[] = "$year-$month-$day";
            }
        }
        if (!empty($allDates)) {
            sort($allDates);
            // 1. Try keyword-based start date (από / έναρξη / υπογραφή)
            $startKw = '/(?:από\s+|εναρξ|αρχ[ήη]|υπογραφ|συνήφθ|συνάφθ)[^\d\/]{0,40}(?<![\d\/])(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-]((?:19|20)\d{2})(?!\d)/ui';
            $endKw   = '/(?:έως\s+|εως\s+|μέχρι|μεχρι|λήξεω|λήξη|ληξ[^α])[^\d\/]{0,40}(?<![\d\/])(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-]((?:19|20)\d{2})(?!\d)/ui';

            if (preg_match($startKw, $text, $m)) {
                $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
                if ((int)$mo <= 12 && (int)$d <= 31) $result['start_date'] = "{$m[3]}-$mo-$d";
            }
            if (preg_match($endKw, $text, $m)) {
                $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
                $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT);
                if ((int)$mo <= 12 && (int)$d <= 31) $result['end_date'] = "{$m[3]}-$mo-$d";
            }

            // 2. Fallback to min/max; prefer recent dates (>=2015) if available
            if ($result['start_date'] === '' || $result['end_date'] === '') {
                $recent = array_values(array_filter($allDates, fn($x) => substr($x, 0, 4) >= '2015'));
                $pool   = !empty($recent) ? $recent : $allDates;
                if ($result['start_date'] === '') $result['start_date'] = $pool[0];
                if ($result['end_date']   === '') $result['end_date']   = end($pool);
            }
        }

        // ── DESCRIPTION ────────────────────────────────────────────────────
        $descPatterns = [
            '/ΑΝΤΙΚΕΙΜΕΝΟ\s+ΤΗΣ?\s+ΣΥΜΒ[^\n]{0,20}[\n:]+\s*([^\n]{10,400})/ui',
            '/ΑΝΤΙΚΕΙΜΕΝΟ\s*[:\-]\s*([^\n]{10,400})/ui',
            '/ΠΕΡΙΓΡΑΦΗ\s+ΕΡΓΑΣΙ[^\n]{0,20}[\n:]+\s*([^\n]{10,400})/ui',
            '/ΠΕΡΙΓΡΑΦΗ\s*[:\-]\s*([^\n]{10,400})/ui',
            '/ΘΕΜΑ\s*[:\-]\s*([^\n]{10,400})/ui',
            '/ΕΡΓΑΣΙΕΣ\s*[:\-]\s*([^\n]{10,400})/ui',
            '/ΑΦΟΡΑ\s*[:\-]?\s*(?:ΤΗΝ|ΤΟ|ΤΑ)?\s*([^\n]{10,400})/ui',
        ];
        foreach ($descPatterns as $pat) {
            if (preg_match($pat, $text, $m)) {
                $result['description'] = trim($m[1]);
                break;
            }
        }

        return $result;
    }

    /** Return ratio of Greek Unicode characters to total characters (0.0 – 1.0) */
    private static function greekRatio(string $text): float {
        $total = mb_strlen(trim($text));
        if ($total === 0) return 0.0;
        preg_match_all('/[\x{0370}-\x{03FF}\x{1F00}-\x{1FFF}]/u', $text, $m);
        return count($m[0]) / $total;
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

    /** Extract text from PDF BT...ET operator blocks (literal + hex strings) */
    private static function extractBtEtText(string $src): string {
        $out = '';
        if (!preg_match_all('/BT\s+(.*?)\s*ET/s', $src, $blocks)) {
            return $out;
        }
        foreach ($blocks[1] as $block) {
            // ── Literal strings: (Hello) Tj  or  [(Hel) -30 (lo)] TJ ──────
            if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)\s*Tj/s', $block, $m)) {
                foreach ($m[1] as $t) { $out .= self::decodePdfStr($t); }
                $out .= "\n";
            }
            if (preg_match_all('/\[((?:[^\[\]]|\\\\.)*)\]\s*TJ/s', $block, $m)) {
                foreach ($m[1] as $arr) {
                    // literal substrings inside TJ array
                    if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/s', $arr, $parts)) {
                        foreach ($parts[1] as $t) { $out .= self::decodePdfStr($t); }
                    }
                    // hex substrings inside TJ array  e.g. <C1ED>
                    if (preg_match_all('/<([0-9A-Fa-f]{2,})>/', $arr, $hparts)) {
                        foreach ($hparts[1] as $hex) {
                            $out .= self::decodePdfBytes(@pack('H*', $hex));
                        }
                    }
                }
                $out .= "\n";
            }
            // ── Hex strings: <C1EDDC...> Tj ─────────────────────────────────
            if (preg_match_all('/<([0-9A-Fa-f]{2,})>\s*Tj/s', $block, $m)) {
                foreach ($m[1] as $hex) {
                    $out .= self::decodePdfBytes(@pack('H*', $hex));
                }
                $out .= "\n";
            }
        }
        return $out;
    }

    /**
     * Decode raw bytes (from a PDF hex or literal string) to UTF-8.
     * Tries Windows-1253 and ISO-8859-7 (Greek encodings) before giving up.
     */
    private static function decodePdfBytes(string $bytes): string {
        if ($bytes === '') return '';
        if (mb_check_encoding($bytes, 'UTF-8')) {
            return $bytes;
        }

        // Helper: try one encoding via iconv (preferred) then mb_convert_encoding.
        // Uses multiple alias variants because support varies between servers/builds.
        $tryEnc = function(string $enc) use ($bytes): string {
            if (function_exists('iconv')) {
                $r = @iconv($enc, 'UTF-8//IGNORE', $bytes);
                if ($r !== false && $r !== '') return $r;
            }
            // mb_convert_encoding alias varies: try as-is, then strip dashes/dots
            foreach ([$enc, str_replace(['-', '.'], '', $enc)] as $alias) {
                $r = @mb_convert_encoding($bytes, 'UTF-8', $alias);
                if ($r !== false && $r !== '') return $r;
            }
            return '';
        };

        // Try Greek encodings in order; keep first that yields actual Greek chars.
        foreach (['CP1253', 'WINDOWS-1253', 'windows-1253', 'ISO-8859-7'] as $enc) {
            $converted = $tryEnc($enc);
            if ($converted !== '' && preg_match('/[\x{0370}-\x{03FF}]/u', $converted)) {
                return $converted;
            }
        }

        // Fallback: best-effort CP1253/ISO-8859-7 regardless of Greek content
        $r = $tryEnc('CP1253');
        return $r !== '' ? $r : $tryEnc('ISO-8859-7');
    }

    /** Decode a raw PDF literal string (octal/common escapes + encoding) */
    private static function decodePdfStr(string $s): string {
        // Octal escapes \ddd
        $s = preg_replace_callback('/\\\\([0-7]{3})/', function($m) {
            return chr(octdec($m[1]));
        }, $s);
        // Common escapes
        $s = str_replace(['\\n','\\r','\\t','\\\\','\\(','\\)'],
                         ["\n", "\r", "\t", '\\',   '(',   ')'], $s);
        return self::decodePdfBytes($s);
    }
}
