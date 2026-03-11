<?php

require_once 'classes/BaseController.php';
require_once 'classes/Database.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class ContractController extends BaseController {

    public function __construct() {
        parent::__construct();
    }

    // GET /projects/contract/{id}
    public function generate(int $projectId): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }

        $pdo = $this->db->getPdo();

        // ── project + customer ────────────────────────────────────────────
        $stmt = $pdo->prepare("
            SELECT p.*,
                   c.first_name, c.last_name, c.company_name,
                   c.address AS cust_address, c.city AS cust_city,
                   c.phone AS cust_phone, c.email AS cust_email
            FROM projects p
            LEFT JOIN customers c ON p.customer_id = c.id
            WHERE p.id = ? AND p.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$projectId]);
        $proj = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$proj) {
            $_SESSION['error'] = 'Το έργο δεν βρέθηκε.';
            $this->redirect('/projects');
        }

        // ── tasks ─────────────────────────────────────────────────────────
        $s2 = $pdo->prepare("
            SELECT description, task_type, task_date, date_from, date_to,
                   materials_total, labor_total, daily_total
            FROM project_tasks
            WHERE project_id = ? AND deleted_at IS NULL
            ORDER BY COALESCE(task_date, date_from)
        ");
        $s2->execute([$projectId]);
        $tasks = $s2->fetchAll(PDO::FETCH_ASSOC);

        // ── totals ────────────────────────────────────────────────────────
        $s3 = $pdo->prepare("
            SELECT COALESCE(SUM(tm.subtotal), 0) AS mat
            FROM task_materials tm
            INNER JOIN project_tasks pt ON tm.task_id = pt.id
            WHERE pt.project_id = ? AND pt.deleted_at IS NULL
        ");
        $s3->execute([$projectId]);
        $mat = (float)$s3->fetchColumn();

        $s4 = $pdo->prepare("
            SELECT COALESCE(SUM(tl.subtotal), 0) AS lab
            FROM task_labor tl
            INNER JOIN project_tasks pt ON tl.task_id = pt.id
            WHERE pt.project_id = ? AND pt.deleted_at IS NULL
        ");
        $s4->execute([$projectId]);
        $lab = (float)$s4->fetchColumn();

        // ── technician ────────────────────────────────────────────────────
        $s5 = $pdo->prepare("
            SELECT u.first_name, u.last_name, u.phone
            FROM users u
            INNER JOIN projects pr ON pr.assigned_technician = u.id
            WHERE pr.id = ?
        ");
        $s5->execute([$projectId]);
        $tech = $s5->fetch(PDO::FETCH_ASSOC);

        // ── company settings ────────────────────────────────────────────
        $sSettings = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = array_column($sSettings->fetchAll(PDO::FETCH_ASSOC), 'setting_value', 'setting_key');

        $subtotal  = $mat + $lab;
        $vatRate   = (float)($settings['default_vat_rate'] ?? 24);
        $vat       = $subtotal * ($vatRate / 100);
        $grandTotal = $subtotal + $vat;
        $custName  = trim($proj['first_name'] . ' ' . $proj['last_name']);
        if ($proj['company_name']) $custName = $proj['company_name'];
        $techName  = $tech ? trim($tech['first_name'] . ' ' . $tech['last_name']) : '';

        // ── build Word document ───────────────────────────────────────────
        $docx = $this->buildDocx($proj, $tasks, $mat, $lab, $subtotal, $vat, $grandTotal, $custName, $techName, $settings, $vatRate);

        // ── save to uploads/contracts/ and redirect ──────────────────────
        $translitMap = [
            'α'=>'a','β'=>'b','γ'=>'g','δ'=>'d','ε'=>'e','ζ'=>'z','η'=>'i','θ'=>'th',
            'ι'=>'i','κ'=>'k','λ'=>'l','μ'=>'m','ν'=>'n','ξ'=>'x','ο'=>'o','π'=>'p',
            'ρ'=>'r','σ'=>'s','ς'=>'s','τ'=>'t','υ'=>'y','φ'=>'f','χ'=>'ch','ψ'=>'ps','ω'=>'o',
            'Α'=>'A','Β'=>'B','Γ'=>'G','Δ'=>'D','Ε'=>'E','Ζ'=>'Z','Η'=>'I','Θ'=>'Th',
            'Ι'=>'I','Κ'=>'K','Λ'=>'L','Μ'=>'M','Ν'=>'N','Ξ'=>'X','Ο'=>'O','Π'=>'P',
            'Ρ'=>'R','Σ'=>'S','Τ'=>'T','Υ'=>'Y','Φ'=>'F','Χ'=>'Ch','Ψ'=>'Ps','Ω'=>'O',
            'ά'=>'a','έ'=>'e','ή'=>'i','ί'=>'i','ό'=>'o','ύ'=>'y','ώ'=>'o',
            'Ά'=>'A','Έ'=>'E','Ή'=>'I','Ί'=>'I','Ό'=>'O','Ύ'=>'Y','Ώ'=>'O',
            'ϊ'=>'i','ϋ'=>'y','ΐ'=>'i','ΰ'=>'y','Ϊ'=>'I','Ϋ'=>'Y',
        ];
        $safeTitle = strtr($proj['title'], $translitMap);
        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $safeTitle);
        $safeTitle = trim(preg_replace('/_+/', '_', $safeTitle), '_');
        $filename  = 'Συμφωνητικό_' . $safeTitle . '_' . date('Y-m-d') . '.docx';

        $contractDir = APP_ROOT . '/uploads/contracts';
        if (!is_dir($contractDir)) {
            mkdir($contractDir, 0755, true);
        }

        $filePath = $contractDir . '/' . $filename;

        try {
            $writer = IOFactory::createWriter($docx, 'Word2007');
            $writer->save($filePath);
        } catch (\Exception $e) {
            error_log('ContractController: Failed to save docx: ' . $e->getMessage());
            $_SESSION['error'] = 'Αποτυχία δημιουργίας συμφωνητικού: ' . $e->getMessage();
            $this->redirect('/projects/show/' . $projectId);
            return;
        }

        if (!file_exists($filePath) || filesize($filePath) < 100) {
            error_log('ContractController: File not created or too small: ' . $filePath);
            $_SESSION['error'] = 'Αποτυχία δημιουργίας αρχείου συμφωνητικού.';
            $this->redirect('/projects/show/' . $projectId);
            return;
        }

        // Stream file as a download — forces browser to save instead of open.
        // Use RFC 5987 encoding so Greek filename works in all browsers.
        $encodedFilename = rawurlencode($filename);
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        ob_end_clean();
        readfile($filePath);
        exit;
    }

    // ── document builder ──────────────────────────────────────────────────────
    private function buildDocx(array $p, array $tasks, float $mat, float $lab, float $subtotal, float $vat, float $grandTotal, string $custName, string $techName, array $settings = [], float $vatRate = 24): PhpWord {
        Settings::setOutputEscapingEnabled(true);
        $word = new PhpWord();
        $word->setDefaultFontName('Times New Roman');
        $word->setDefaultFontSize(11);

        $section = $word->addSection([
            'pageSizeW'    => 11906,
            'pageSizeH'    => 16838,
            'marginTop'    => 1000,
            'marginBottom' => 1000,
            'marginLeft'   => 1100,
            'marginRight'  => 1000,
        ]);

        // ── style helpers ─────────────────────────────────────────────────
        $bold    = ['bold' => true];
        $boldU   = ['bold' => true, 'underline' => 'single'];
        $center  = ['alignment' => Jc::CENTER];
        $justify = ['alignment' => Jc::BOTH];
        $pN      = ['spaceAfter' => 100];
        $pS      = ['spaceAfter' => 60];
        $dots    = '.................................';
        $dotSm   = '....................';

        $fDate  = static function(string $d): string {
            return $d ? date('d/m/Y', strtotime($d)) : '........../.........../...........';
        };
        $fEur   = static fn(float $v): string  => number_format($v, 2, ',', '.') . ' €';

        // ── derive dates ──────────────────────────────────────────────────
        $dateFrom = !empty($p['start_date']) ? $fDate($p['start_date']) : '';
        $dateTo   = !empty($p['end_date'])   ? $fDate($p['end_date'])   : '';
        if (!$dateFrom && !empty($tasks)) {
            $ts = array_filter(array_merge(array_column($tasks, 'date_from'), array_column($tasks, 'task_date')));
            if ($ts) { sort($ts); $dateFrom = $fDate($ts[0]); }
        }
        if (!$dateTo && !empty($tasks)) {
            $ts = array_filter(array_merge(array_column($tasks, 'date_to'), array_column($tasks, 'task_date')));
            if ($ts) { rsort($ts); $dateTo = $fDate($ts[0]); }
        }
        if (!$dateFrom) $dateFrom = $dotSm;
        if (!$dateTo)   $dateTo   = $dotSm;

        // ── company info ──────────────────────────────────────────────────
        $coName  = $settings['company_name']       ?? '';
        $coLegal = $settings['company_legal_form']  ?? '';
        $coAddr  = $settings['company_address']    ?? '';
        $coTax   = $settings['company_tax_id']     ?? '';
        $coDoy   = $settings['company_tax_office'] ?? '';
        $coPhone = $settings['company_phone']      ?? '';
        $coRep   = $settings['company_legal_rep']  ?? '';
        $coCity  = $settings['company_city']       ?? 'Ηράκλειο Κρήτης';

        $custAddr = trim(($p['cust_address'] ?? '') . ($p['cust_city'] ? ', ' . $p['cust_city'] : ''), ', ');

        $greekMonths = [
            1=>'Ιανουαρίου', 2=>'Φεβρουαρίου', 3=>'Μαρτίου',   4=>'Απριλίου',
            5=>'Μαΐου',      6=>'Ιουνίου',     7=>'Ιουλίου',   8=>'Αυγούστου',
            9=>'Σεπτεμβρίου',10=>'Οκτωβρίου', 11=>'Νοεμβρίου',12=>'Δεκεμβρίου',
        ];
        $signDate = date('j') . ' ' . $greekMonths[(int)date('n')] . ' ' . date('Y');

        // ── TITLE ─────────────────────────────────────────────────────────
        $section->addText(
            'ΙΔΙΩΤΙΚΟ ΣΥΜΦΩΝΗΤΙΚΟ ΕΚΤΕΛΕΣΗΣ ΕΡΓΟΥ',
            ['bold' => true, 'size' => 15, 'underline' => 'single'],
            $center + ['spaceAfter' => 60]
        );
        $section->addText(
            mb_strtoupper($p['title']),
            ['bold' => true, 'size' => 13],
            $center + ['spaceAfter' => 40]
        );
        if (!empty($p['location'])) {
            $section->addText($p['location'], ['italic' => true, 'size' => 11], $center + ['spaceAfter' => 40]);
        }
        $section->addTextBreak(1);

        // ── INTRO – PARTIES ───────────────────────────────────────────────
        $trIntro = $section->addTextRun($justify + ['spaceAfter' => 140]);
        $trIntro->addText('Στο ');
        $trIntro->addText($coCity ?: 'Ηράκλειο Κρήτης', $bold);
        $trIntro->addText(' σήμερα στις  ');
        $trIntro->addText($signDate, $bold);
        $trIntro->addText(', οι παρακάτω συμβαλλόμενοι:');

        // Party α – ΕΡΓΟΔΟΤΗΣ (customer)
        $trA = $section->addTextRun($justify + ['spaceAfter' => 100, 'spaceBefore' => 80]);
        $trA->addText('α)  ');
        $trA->addText(($custName ?: $dots), $bold);
        if ($custAddr) { $trA->addText(' με έδρα ' . $custAddr); }
        $trA->addText(', ΑΦΜ: ' . $dots . ', Δ.Ο.Υ. ' . $dots);
        if (!empty($p['cust_phone'])) { $trA->addText(', τηλ. ' . $p['cust_phone']); }
        $trA->addText(', που θα καλείται στη συνέχεια ');
        $trA->addText('"Εργοδότης"', $bold);
        $trA->addText('.');

        // Party β – ΑΝΑΔΟΧΟΣ (our company)
        $trB = $section->addTextRun($justify + ['spaceAfter' => 100]);
        $trB->addText('β)  ');
        $trB->addText(($coName ?: $dots), $bold);
        if ($coLegal) { $trB->addText(', ' . $coLegal); }
        if ($coAddr)  { $trB->addText(' με έδρα ' . $coAddr); }
        if ($coTax)   { $trB->addText(', ΑΦΜ: ' . $coTax); }
        if ($coDoy)   { $trB->addText(', Δ.Ο.Υ. ' . $coDoy); }
        if ($coRep)   { $trB->addText(', νόμιμα εκπροσωπούμενη από τον κ. ' . $coRep); }
        if ($coPhone) { $trB->addText(', τηλ. ' . $coPhone); }
        $trB->addText(', που θα καλείται στη συνέχεια ');
        $trB->addText('"Ανάδοχος"', $bold);
        $trB->addText('.');

        $section->addTextBreak(1);
        $section->addText('συμφώνησαν και συναποδέχθηκαν τα παρακάτω:', null, ['spaceAfter' => 160]);

        // ── ΑΝΑΘΕΣΗ ΕΡΓΟΥ ────────────────────────────────────────────────
        $trWork = $section->addTextRun($justify + $pN);
        $trWork->addText('Ο ');
        $trWork->addText('"Εργοδότης"', $bold);
        $trWork->addText(' αναθέτει στον ');
        $trWork->addText('"Ανάδοχο"', $bold);
        $workDesc = ' την εκτέλεση: ' . $p['title'];
        if (!empty($p['description'])) { $workDesc .= ' - ' . $p['description']; }
        if (!empty($p['location']))    { $workDesc .= ', στο ' . $p['location']; }
        $workDesc .= '.';
        $trWork->addText($workDesc);

        if (!empty($tasks)) {
            $section->addText('Αναλυτικά οι εργασίες που περιλαμβάνονται:', null, ['spaceAfter' => 40, 'spaceBefore' => 80]);
            foreach ($tasks as $tk) {
                if (!empty($tk['description'])) {
                    $section->addListItem($tk['description'], 0, null, null, $pS);
                }
            }
            $section->addTextBreak(1);
        }

        $trDecl = $section->addTextRun($justify + ['spaceAfter' => 160]);
        $trDecl->addText('Ο ');
        $trDecl->addText('"Ανάδοχος"', $bold);
        $trDecl->addText(' δηλώνει ότι έχει λάβει γνώση όλων των σχεδίων και των τοπικών συνθηκών και αποδέχεται την εκτέλεση του έργου σύμφωνα με τους κανόνες της τέχνης, τις διατάξεις Ασφαλείας Εργαζομένων, τους ισχύοντες Κανονισμούς, την παρακάτω τιμή κατ\'αποκοπή και τους ειδικούς όρους και συμφωνίες.');

        // ── ΤΙΜΕΣ ─────────────────────────────────────────────────────────
        $section->addText('ΤΙΜΕΣ', $boldU, ['spaceBefore' => 160, 'spaceAfter' => 80]);

        $trTimima = $section->addTextRun($justify + $pN);
        $trTimima->addText('Το τίμημα του συμφωνητικού είναι κατ\'αποκοπή βάσει της προμέτρησης και της έγγραφης προσφοράς του ');
        $trTimima->addText('"Αναδόχου"', $bold);
        $trTimima->addText('.');

        // cost table
        $tblC = $section->addTable(['borderSize' => 6, 'borderColor' => 'AAAAAA', 'cellMargin' => 90]);
        $rSub = $tblC->addRow();
        $rSub->addCell(7000, ['bgColor' => 'EFEFEF'])->addText('Κόστος υλικών και εργασίας  (Άνευ ΦΠΑ ' . number_format($vatRate, 0) . '%)', $bold);
        $rSub->addCell(2500, ['bgColor' => 'EFEFEF'])->addText($subtotal > 0 ? $fEur($subtotal) : $dots, $bold);
        $rVat = $tblC->addRow();
        $rVat->addCell(7000)->addText('ΦΠΑ ' . number_format($vatRate, 0) . '%');
        $rVat->addCell(2500)->addText($subtotal > 0 ? $fEur($vat) : $dots);
        $rG = $tblC->addRow();
        $rG->addCell(7000, ['bgColor' => 'D5E8D4'])->addText('ΓΕΝΙΚΟ ΣΥΝΟΛΟ  (Με ΦΠΑ ' . number_format($vatRate, 0) . '%)', $bold);
        $rG->addCell(2500, ['bgColor' => 'D5E8D4'])->addText($subtotal > 0 ? $fEur($grandTotal) : $dots, $bold);

        $section->addTextBreak(1);

        // ── ΓΕΝΙΚΟΙ ΟΡΟΙ ΤΙΜΟΛΟΓΙΟΥ ──────────────────────────────────────
        $section->addText('ΓΕΝΙΚΟΙ ΟΡΟΙ ΤΙΜΟΛΟΓΙΟΥ', $boldU, ['spaceBefore' => 160, 'spaceAfter' => 80]);

        $section->addText(
            'Τα κατ\'αποκοπή τιμήματα του παρόντος αναφέρονται σε έργα πλήρως και άρτια περαιωμένα και περιλαμβάνουν όλες τις δαπάνες για την πλήρη εκτέλεση του συνόλου των εργασιών, σύμφωνα με τους όρους του παρόντος και τη μελέτη εφαρμογής. Σε κάθε περίπτωση στα κατ\'αποκοπή τμήματα περιέχεται, ενδεικτικά και όχι περιοριστικά:',
            null, $justify + $pN
        );
        foreach ([
            'Κάθε δαπάνη γενικά, έστω και αν δεν κατονομάζεται ρητά, αλλά είναι απαραίτητη για την πλήρη και έντεχνη εκτέλεση. Καμία αξίωση δεν μπορεί να θεμελιωθεί σχετικά με το είδος και την απόδοση μηχανημάτων ή την ειδικότητα και τον αριθμό του εργατοτεχνικού προσωπικού.',
            'Η αξία, επί τόπου των έργων, όλων των απαιτούμενων αρίστης ποιότητας υλικών, ενσωματωμένων ή μη, κυρίων και βοηθητικών, σύμφωνα με τους κανόνες της τέχνης και τα λοιπά συμβατικά τεύχη.',
            'Η δαπάνη για τη φθορά και απομείωση των υλικών γενικά.',
            'Η δαπάνη προσκόμισης και προσέγγισης όλων των υλικών μέχρι τα σημεία χρησιμοποίησής τους.',
            'Κάθε είδους επιβάρυνση των ενσωματωμένων υλικών από φόρους, τέλη, δασμούς, έξοδα εκτελωνισμού κλπ., πλην του Φ.Π.Α.',
            'Οι δαπάνες μισθών, ημερομισθίων, υπερωριών, ασφαλιστικών εισφορών, δώρων εορτών και λοιπών επιδομάτων που καθορίζονται από τις ισχύουσες Συλλογικές Συμβάσεις Εργασίας.',
            'Η δαπάνη για τη μόρφωση ή διάνοιξη αυλάκων, οπών ή φωλεών, διόδου ή εντοιχισμού σωληνώσεων, αγωγών ή εξαρτημάτων σε τοίχους, οροφές ή πατώματα από οποιοδήποτε υλικό και οποιουδήποτε πάχους.',
        ] as $term) {
            $section->addListItem($term, 0, null, null, $pS);
        }
        $section->addTextBreak(1);

        // ── ΕΝΑΡΞΗ – ΠΕΡΑΙΩΣΗ ΕΡΓΑΣΙΩΝ ────────────────────────────────────
        $section->addText('ΕΝΑΡΞΗ - ΠΕΡΑΙΩΣΗ ΕΡΓΑΣΙΩΝ', $boldU, ['spaceBefore' => 160, 'spaceAfter' => 80]);

        $tblDates = $section->addTable(['borderSize' => 6, 'borderColor' => 'AAAAAA', 'cellMargin' => 80]);
        $thD = $tblDates->addRow();
        $thD->addCell(4750, ['bgColor' => 'D5E8D4'])->addText('Έναρξη Εργασιών', $bold);
        $thD->addCell(4750, ['bgColor' => 'D5E8D4'])->addText('Εκτιμώμενη Αποπεράτωση', $bold);
        $tdD = $tblDates->addRow();
        $tdD->addCell(4750)->addText($dateFrom);
        $tdD->addCell(4750)->addText($dateTo);

        if (!empty($tasks)) {
            $section->addTextBreak(1);
            $section->addText('Αναλυτικό χρονοδιάγραμμα εργασιών:', null, ['spaceAfter' => 40, 'spaceBefore' => 60]);
            $tblT = $section->addTable(['borderSize' => 6, 'borderColor' => 'AAAAAA', 'cellMargin' => 70]);
            $thT  = $tblT->addRow();
            foreach ([[2000, 'Περίοδος'], [6500, 'Περιγραφή Εργασίας']] as [$w, $h]) {
                $thT->addCell($w, ['bgColor' => '1F497D'])->addText($h, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9]);
            }
            foreach ($tasks as $tk) {
                $period = ($tk['task_type'] === 'single_day')
                    ? $fDate((string)($tk['task_date'] ?? ''))
                    : ($fDate((string)($tk['date_from'] ?? '')) . ' - ' . $fDate((string)($tk['date_to'] ?? '')));
                $tr = $tblT->addRow();
                $tr->addCell(2000)->addText($period, ['size' => 9]);
                $tr->addCell(6500)->addText($tk['description'] ?? '', ['size' => 9]);
            }
        }
        $section->addTextBreak(1);

        // ── ΤΡΟΠΟΣ ΠΛΗΡΩΜΗΣ ──────────────────────────────────────────────
        $section->addText('ΤΡΟΠΟΣ ΠΛΗΡΩΜΗΣ', $boldU, ['spaceBefore' => 160, 'spaceAfter' => 80]);

        $section->addText('Οι πληρωμές θα πραγματοποιηθούν ως εξής:', null, $pS);
        $tblP = $section->addTable(['borderSize' => 6, 'borderColor' => 'AAAAAA', 'cellMargin' => 80]);
        $thP  = $tblP->addRow();
        foreach ([[500, '#'], [5800, 'Φάση Πληρωμής'], [2200, 'Ποσό']] as [$w, $h]) {
            $thP->addCell($w, ['bgColor' => '1F497D'])->addText($h, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9]);
        }
        foreach ([
            ['1', 'Προκαταβολή κατά την υπογραφή του παρόντος', $dots],
            ['2', 'Πρόοδος εργασιών κατόπιν επιμέτρησης', $dots],
            ['3', 'Εξόφληση κατά την οριστική παραλαβή', $dots],
        ] as [$num, $phase, $amount]) {
            $tr = $tblP->addRow();
            $tr->addCell(500)->addText($num, ['size' => 9]);
            $tr->addCell(5800)->addText($phase, ['size' => 9]);
            $tr->addCell(2200)->addText($amount, ['size' => 9]);
        }
        $section->addTextBreak(1);
        $trPay = $section->addTextRun($justify + $pN);
        $trPay->addText('Τρόπος πληρωμής: ');
        $trPay->addText($dots);
        $trPay->addText('. Ο Εργοδότης θα καταβάλλει το οφειλόμενο ποσό μετά την παρακράτηση του αναλογούντος φόρου.');

        // ── ΛΟΙΠΟΙ ΟΡΟΙ ──────────────────────────────────────────────────
        $section->addText('ΛΟΙΠΟΙ ΟΡΟΙ', $boldU, ['spaceBefore' => 200, 'spaceAfter' => 80]);

        foreach ([
            'Η εργασία θα είναι έντεχνη και θα εκτελείται από προσωπικό απολύτως εξειδικευμένο. Τυχόν κακότεχνη κατασκευή που εντοπιστεί θα αποξηλώνεται και θα επανακατασκευάζεται με δαπάνη του Αναδόχου.',
            'Ο Ανάδοχος είναι αποκλειστικά υπεύθυνος για την τήρηση όλων των μέτρων και διατάξεων ασφαλείας εργαζομένων. Δηλώνει ότι έχει γνώση της ισχύουσας νομοθεσίας Υγείας και Ασφάλειας και ότι έχει υπολογίσει τις σχετικές επιβαρύνσεις κατά τη σύνταξη της προσφοράς του. Ολόκληρο το προσωπικό του θα είναι ασφαλισμένο κατά τον Νόμο. Ο Εργοδότης δεν φέρει καμία ευθύνη για ζημίες ή ατυχήματα που τυχόν προκύψουν κατά την εκτέλεση από τον Ανάδοχο.',
            'Οι συμφωνηθείσες τιμές δεν υπόκεινται σε αναθεώρηση παρά μόνο με έγγραφη συμφωνία. Κάθε τροποποίηση του παρόντος ισχύει μόνο εφόσον είναι έγγραφη και υπογεγραμμένη και από τα δύο μέρη. Ο Ανάδοχος παραιτείται από κάθε αξίωση που πηγάζει από το άρθρο 696 του Αστικού Κώδικα.',
            'Σε περίπτωση διαφωνίας, αρμόδια για την επίλυσή της ορίζονται τα Δικαστήρια ' . ($coCity ?: $dots) . '.',
        ] as $i => $term) {
            $trO = $section->addTextRun($justify + ['spaceAfter' => 100, 'spaceBefore' => 60]);
            $trO->addText(($i + 1) . '.   ', $bold);
            $trO->addText($term);
        }

        $section->addTextBreak(1);

        // ── SIGNATURES ───────────────────────────────────────────────────
        $section->addText(
            'Το παρόν συντάχθηκε και υπογράφηκε σε δύο (2) πρωτότυπα αντίτυπα και έλαβε από ένα αντίγραφο κάθε ένας από τους συμβαλλόμενους.',
            ['italic' => true],
            $justify + ['spaceBefore' => 200, 'spaceAfter' => 280]
        );

        $tblS = $section->addTable(['borderSize' => 0, 'cellMargin' => 100]);
        $trS  = $tblS->addRow(2400);
        $c1   = $trS->addCell(4750);
        $c1->addText('Ο ΑΝΑΔΟΧΟΣ', $bold, $center);
        $c1->addText(' ', null, $center);
        $c1->addText($dotSm, null, $center);
        $c1->addText('(Σφραγίδα και Υπογραφή)', ['italic' => true, 'size' => 9], $center);
        $c1->addText(' ', null, $center);
        $c1->addText('Ημερομηνία: ' . $dotSm, null, $center);

        $c2 = $trS->addCell(4750);
        $c2->addText('Ο ΕΡΓΟΔΟΤΗΣ', $bold, $center);
        $c2->addText(' ', null, $center);
        $c2->addText($custName ?: $dotSm, $bold, $center);
        $c2->addText('(Σφραγίδα και Υπογραφή)', ['italic' => true, 'size' => 9], $center);
        $c2->addText(' ', null, $center);
        $c2->addText('Ημερομηνία: ' . $dotSm, null, $center);

        return $word;
    }
}
