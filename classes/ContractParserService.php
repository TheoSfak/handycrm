<?php
/**
 * Contract Parser Service
 * Extracts text and metadata (title, amount, dates, description) from PDF contracts.
 */

class ContractParserService {
    /**
     * Extract text and key fields from a PDF file using smalot/pdfparser with multi-strategy fallback.
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
            $result['strategy'] = 'smalot';
        }

        // ── Normalize Unicode (NFC) ────────────────────────────────────────
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $text) ?? $text;
        if (function_exists('normalizer_normalize')) {
            $norm = normalizer_normalize($text, Normalizer::FORM_C);
            if ($norm !== false && $norm !== '') {
                $text = $norm;
            }
        }

        // ── Fix inter-character spaces ─────────────────────────────────────
        $text = preg_replace_callback('/(?:[Α-ΩA-Z\d] ){3,}/u', function ($m) {
            return preg_replace('/\s+/u', '', $m[0]);
        }, $text) ?? $text;

        $result['text'] = $text;
        if (strlen(trim($text)) === 0) {
            return $result;
        }

        // ── TITLE ──────────────────────────────────────────────────────────
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
            foreach (explode("\n", $text) as $line) {
                $line = trim($line);
                if (mb_strlen($line) < 15) continue;
                if (preg_match('/^\s*[\d]+[\.)]/u', $line)) continue;
                if (preg_match('/Α\.\s*Π\.|ΑΡ\.\s*ΠΡΩΤ|ΑΡΙΘ\.\s*ΠΡΩΤ|ΑΔΑΜ:/ui', $line)) continue;
                if (preg_match('/@|www\.|http/i', $line)) continue;
                if (preg_match('/\d{6,}/', $line)) continue;
                if (preg_match('/[«»]/', $line)) continue;
                if (preg_match('/^\s*[\d\.\,\/\-\s]+$/', $line)) continue;
                $digitCount = preg_match_all('/\d/', $line);
                if ($digitCount > mb_strlen($line) * 0.4) continue;
                $result['title'] = mb_substr($line, 0, 200);
                break;
            }
        }

        if ($result['title'] === '' && $originalFilename) {
            $base = pathinfo($originalFilename, PATHINFO_FILENAME);
            $base = html_entity_decode($base, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $base = str_replace(['_', '-'], ' ', $base);
            $base = preg_replace('/\s+/', ' ', $base);
            $result['title'] = mb_substr(trim($base), 0, 200);
        }

        // ── AMOUNT ─────────────────────────────────────────────────────────
        $amountPatterns = [
            '/ανέρχεται\s+σε\s+([\d]{1,3}(?:[\.,\s]\d{3})*(?:[,\.]\d{1,2})?)\s*(?:€|ευρ)/iu',
            '/(?:ΑΜΟΙΒ[ΗΑ]|τ[ίι]μημα|ΤΙΜΗΜΑ|ΠΟΣΟ|ΣΥΝΟΛ[ΟΑ]|ΤΙΜΗ\s+ΣΥΜΒΑΣ)[^\d]{0,30}([\d]{1,3}(?:[\.\s]\d{3})*(?:,\d{1,2})?)/ui',
            '/€\s*([\d]{1,3}(?:\.\d{3})*(?:,\d{1,2})?)/u',
            '/([\d]{1,3}(?:\.\d{3})+(?:,\d{1,2})?)\s*(?:€|EUR|ευρ)/iu',
            '/([\d]+,\d{1,2})\s*(?:€|EUR|ευρ)/iu',
            '/([\d]{3,})\s*(?:€|EUR|ευρ)/iu',
            '/5\.1[^0-9]{0,20}([\d]{1,3}(?:\.\d{3})+,\d{2})/u',
        ];
        foreach ($amountPatterns as $pat) {
            if (preg_match($pat, $text, $m)) {
                $raw = trim($m[1]);
                $raw = preg_replace('/\s/', '', $raw);
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
                if (is_numeric($raw) && (float)$raw > 0) {
                    $result['amount'] = $raw;
                    break;
                }
            }
        }

        if ($result['amount'] === '') {
            preg_match_all('/(?<!\d)([\d]{1,3}(?:\.\d{3})+,\d{2})(?!\d)/u', $text, $am);
            if (!empty($am[1])) {
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

    /**
     * Raw fallback: decompress PDF content streams and extract plain text.
     */
    private static function extractTextRaw(string $filePath): string {
        $content = file_get_contents($filePath);
        if (!$content) {
            return '';
        }

        $text = '';

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

        $text .= self::extractBtEtText($content);

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
            if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)\s*Tj/s', $block, $m)) {
                foreach ($m[1] as $t) { $out .= self::decodePdfStr($t); }
                $out .= "\n";
            }
            if (preg_match_all('/\[((?:[^\[\]]|\\\\.)*)\]\s*TJ/s', $block, $m)) {
                foreach ($m[1] as $arr) {
                    if (preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/s', $arr, $parts)) {
                        foreach ($parts[1] as $t) { $out .= self::decodePdfStr($t); }
                    }
                    if (preg_match_all('/<([0-9A-Fa-f]{2,})>/', $arr, $hparts)) {
                        foreach ($hparts[1] as $hex) {
                            $out .= self::decodePdfBytes(@pack('H*', $hex));
                        }
                    }
                }
                $out .= "\n";
            }
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
     */
    private static function decodePdfBytes(string $bytes): string {
        if ($bytes === '') return '';
        if (mb_check_encoding($bytes, 'UTF-8')) {
            return $bytes;
        }

        $tryEnc = function(string $enc) use ($bytes): string {
            if (function_exists('iconv')) {
                $r = @iconv($enc, 'UTF-8//IGNORE', $bytes);
                if ($r !== false && $r !== '') return $r;
            }
            foreach ([$enc, str_replace(['-', '.'], '', $enc)] as $alias) {
                $r = @mb_convert_encoding($bytes, 'UTF-8', $alias);
                if ($r !== false && $r !== '') return $r;
            }
            return '';
        };

        foreach (['CP1253', 'WINDOWS-1253', 'windows-1253', 'ISO-8859-7'] as $enc) {
            $converted = $tryEnc($enc);
            if ($converted !== '' && preg_match('/[\x{0370}-\x{03FF}]/u', $converted)) {
                return $converted;
            }
        }

        $r = $tryEnc('CP1253');
        return $r !== '' ? $r : $tryEnc('ISO-8859-7');
    }

    /** Decode a raw PDF literal string (octal/common escapes + encoding) */
    private static function decodePdfStr(string $s): string {
        $s = preg_replace_callback('/\\\\([0-7]{3})/', function($m) {
            return chr(octdec($m[1]));
        }, $s);
        $s = str_replace(['\\n','\\r','\\t','\\\\','\\(','\\)'],
                         ["\n", "\r", "\t", '\\',   '(',   ')'], $s);
        return self::decodePdfBytes($s);
    }
}
