<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$s = IOFactory::load('templates/maintenance_template.xlsx')->getActiveSheet();

echo "Searching for V1, V2, V3, V4, V5:\n";
for($r=60; $r<=80; $r++) {
    for($c='A'; $c<='J'; $c++) {
        $v = $s->getCell($c.$r)->getValue();
        if(stripos($v, 'V1') !== false || stripos($v, 'V2') !== false || 
           stripos($v, 'V3') !== false || stripos($v, 'V4') !== false || 
           stripos($v, 'V5') !== false) {
            echo "$c$r: $v\n";
        }
    }
}

echo "\n\nAll content rows 65-70:\n";
for($r=65; $r<=70; $r++) {
    echo "Row $r: ";
    for($c='A'; $c<='J'; $c++) {
        $v = $s->getCell($c.$r)->getValue();
        if(!empty($v)) echo "$c=$v | ";
    }
    echo "\n";
}
