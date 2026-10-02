<?php
/**
 * Refactoring Verification Script
 * Validates syntax, class interfaces, services, and core models
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "===============================================\n";
echo "HandyCRM Architecture & Refactoring Verification\n";
echo "===============================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest($description, $condition) {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo " [PASS] $description\n";
        $testsPassed++;
    } else {
        echo " [FAIL] $description\n";
        $testsFailed++;
    }
}

// 1. Config & Constants
require_once __DIR__ . '/../config/config.php';
assertTest("IMAGE_MAX_WIDTH defined", defined('IMAGE_MAX_WIDTH') && IMAGE_MAX_WIDTH === 1920);
assertTest("IMAGE_MAX_HEIGHT defined", defined('IMAGE_MAX_HEIGHT') && IMAGE_MAX_HEIGHT === 1080);
assertTest("IMAGE_QUALITY defined", defined('IMAGE_QUALITY') && IMAGE_QUALITY === 85);
assertTest("DEBUG_MODE defaults to boolean", is_bool(DEBUG_MODE));

// 2. PhotoService
require_once __DIR__ . '/../classes/PhotoService.php';
assertTest("PhotoService class exists", class_exists('PhotoService'));
assertTest("PhotoService has resize method", method_exists('PhotoService', 'resize'));

// Test PhotoService with a dynamically generated image
$testImg = imagecreatetruecolor(200, 200);
$red = imagecolorallocate($testImg, 255, 0, 0);
imagefill($testImg, 0, 0, $red);
$tmpSource = tempnam(sys_get_temp_dir(), 'test_src_') . '.png';
$tmpDest = tempnam(sys_get_temp_dir(), 'test_dest_') . '.jpg';
imagepng($testImg, $tmpSource);
imagedestroy($testImg);

$resizeOk = PhotoService::resize($tmpSource, $tmpDest, 100, 100, 80);
assertTest("PhotoService::resize resized test PNG to JPEG", $resizeOk && file_exists($tmpDest) && filesize($tmpDest) > 0);
@unlink($tmpSource);
@unlink($tmpDest);

// 3. CsvExportService
require_once __DIR__ . '/../classes/CsvExportService.php';
assertTest("CsvExportService class exists", class_exists('CsvExportService'));
assertTest("CsvExportService has stream method", method_exists('CsvExportService', 'stream'));

// 4. ContractParserService
require_once __DIR__ . '/../classes/ContractParserService.php';
assertTest("ContractParserService class exists", class_exists('ContractParserService'));
assertTest("ContractParserService has extractFromPdf method", method_exists('ContractParserService', 'extractFromPdf'));

// 5. BaseModel
require_once __DIR__ . '/../classes/BaseModel.php';
assertTest("BaseModel class exists", class_exists('BaseModel'));
$bmRefl = new ReflectionMethod('BaseModel', 'update');
assertTest("BaseModel::update method signature verified", $bmRefl->getNumberOfParameters() === 2);

// 6. BaseController
require_once __DIR__ . '/../classes/BaseController.php';
assertTest("BaseController class exists", class_exists('BaseController'));
$bcRefl = new ReflectionClass('BaseController');
assertTest("BaseController has validateCsrfToken", $bcRefl->hasMethod('validateCsrfToken'));
assertTest("BaseController has sanitize", $bcRefl->hasMethod('sanitize'));
assertTest("BaseController has uploadFile", $bcRefl->hasMethod('uploadFile'));

// 7. Models
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/DailyTask.php';
require_once __DIR__ . '/../models/UploadedContract.php';
assertTest("Project model has delete method", method_exists('Project', 'delete'));
assertTest("DailyTask model has delete method", method_exists('DailyTask', 'delete'));
assertTest("UploadedContract delegates extractFromPdf", method_exists('UploadedContract', 'extractFromPdf'));

// 8. Uploads execution security
$htaccessFile = __DIR__ . '/../uploads/.htaccess';
assertTest("Uploads directory .htaccess exists", file_exists($htaccessFile));
$htaccessContent = file_get_contents($htaccessFile);
assertTest("Uploads .htaccess blocks PHP execution", strpos($htaccessContent, 'php_flag engine off') !== false || strpos($htaccessContent, 'deny from all') !== false || strpos($htaccessContent, 'Require all denied') !== false);

// 9. Composer scripts outside webroot
assertTest("Root run_composer scripts removed", !file_exists(__DIR__ . '/../run_composer_install.php') && !file_exists(__DIR__ . '/../run_composer_update.php') && !file_exists(__DIR__ . '/../run_composer_audit.php'));
assertTest("Scripts directory has composer utilities", file_exists(__DIR__ . '/composer_install.php') && file_exists(__DIR__ . '/composer_update.php'));

// 10. Database dump syntax
$sqlDump = file_get_contents(__DIR__ . '/../database/handycrm.sql');
$languageOccurrences = substr_count($sqlDump, '`language` varchar(2) DEFAULT \'el\'');
assertTest("handycrm.sql has unique language column definition", $languageOccurrences === 1);

// 11. Storage directory
assertTest("Storage directory exists", is_dir(__DIR__ . '/../storage'));
assertTest("Storage directory has .gitignore", file_exists(__DIR__ . '/../storage/.gitignore'));

echo "\n-----------------------------------------------\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";
echo "===============================================\n";

if ($testsFailed > 0) {
    exit(1);
}
exit(0);
