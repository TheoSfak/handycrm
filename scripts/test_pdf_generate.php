<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

// DB credentials (can be overridden via environment)
if (!getenv('DB_HOST')) putenv('DB_HOST=localhost');
if (!getenv('DB_USER')) putenv('DB_USER=root');
if (!getenv('DB_PASS')) putenv('DB_PASS=');
if (!getenv('DB_NAME')) putenv('DB_NAME=handycrm');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/BaseController.php';
require_once __DIR__ . '/../models/MaintenanceOffer.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../controllers/MaintenanceOfferController.php';

$ref = new ReflectionClass(MaintenanceOfferController::class);
$controller = $ref->newInstanceWithoutConstructor();

$offer = [
    'id'                 => 1,
    'offer_number'       => 'PRO-202610-0001',
    'title'              => 'Συντήρηση Υποσταθμού Μέσης Τάσης',
    'company_name'       => 'Blue Bay',
    'address'            => 'Αγία Πελαγία, Ηράκλειο Κρήτης',
    'phone'              => '2810 811100',
    'email'              => 'info@bluebay.gr',
    'transformers_count' => 2,
    'transformer_power'  => '500 kVA & 630 kVA',
    'price'              => 600.00,
    'offer_expiry_date'  => date('Y-m-d', strtotime('+30 days')),
    'created_at'         => date('Y-m-d H:i:s'),
    'notes'              => 'Συντήρηση 2 μετασχηματιστών μέσης τάσης.',
    'terms'              => 'Στις τιμές δεν περιλαμβάνεται ΦΠΑ 24%. Η προσφορά ισχύει για 30 ημέρες.',
];

echo "Building HTML...\n";
$html = $controller->buildOfferHtml($offer);
file_put_contents(__DIR__ . '/../scratch_preview.html', $html);
echo "Saved scratch_preview.html (" . strlen($html) . " bytes)\n";

echo "Generating PDF with Puppeteer...\n";
$pdfData = $controller->generateOfferPdfWithPuppeteer($html);

if ($pdfData && strlen($pdfData) > 1000) {
    file_put_contents(__DIR__ . '/../scratch_puppeteer_final.pdf', $pdfData);
    echo "SUCCESS: scratch_puppeteer_final.pdf generated (" . strlen($pdfData) . " bytes)\n";
}

echo "Generating TCPDF fallback...\n";
$pdf = $controller->buildOfferPDF($offer);
$pdf->Output(__DIR__ . '/../scratch_tcpdf_final.pdf', 'F');
echo "SUCCESS: scratch_tcpdf_final.pdf generated (" . filesize(__DIR__ . '/../scratch_tcpdf_final.pdf') . " bytes)\n";
