<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$templatePath = __DIR__ . '/templates/maintenance_template.xlsx';

try {
    $spreadsheet = IOFactory::load($templatePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    echo "Template Analysis:\n";
    echo "==================\n\n";
    
    // Get highest row and column
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    
    echo "Highest Row: $highestRow\n";
    echo "Highest Column: $highestColumn\n\n";
    
    echo "Cell Contents:\n";
    echo "==============================\n";
    
    // Specific cells we're looking for
    $cellsToCheck = [
        'D1', 'D2', 'D3', 'D4', 'C6', 'H6', 'D7', 'D45', 'D46', 'D47', 
        'B50', 'E50', 'H50', 'D55', 'D60', 'D65', 'D70', 'D75', 'D80',
        'A30', 'A41', 'A44'
    ];
    
    foreach ($cellsToCheck as $cell) {
        $value = $sheet->getCell($cell)->getValue();
        echo "$cell: $value\n";
    }
    
    echo "\n\nSearching for field markers (Πεδίο):\n";
    echo "====================================\n";
    
    for ($row = 1; $row <= $highestRow; $row++) {
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $value = $sheet->getCell($col . $row)->getValue();
            if (strpos($value, 'Πεδίο') !== false) {
                echo "$col$row: $value\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
