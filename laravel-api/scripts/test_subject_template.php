<?php
// Quick smoke test for Subject import template generation (no DB required).
// Usage: php laravel-api/scripts/test_subject_template.php

require __DIR__ . '/../vendor/autoload.php';

use App\Services\SubjectImportService;

try {
    $svc = new SubjectImportService();
    $ss = $svc->generateTemplateXlsx();

    $names = [];
    for ($i = 0; $i < $ss->getSheetCount(); $i++) {
        $names[] = $ss->getSheet($i)->getTitle();
    }
    echo 'sheets: ' . implode(' ', $names) . PHP_EOL;

    // Dump headers of "subjects" sheet (row 1)
    $subjects = $ss->getSheetByName('subjects') ?: $ss->getSheet(0);
    $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($subjects->getHighestColumn());
    $headers = [];
    for ($c = 1; $c <= $highestCol; $c++) {
        $headers[] = (string) $subjects->getCellByColumnAndRow($c, 1)->getValue();
    }
    echo 'headers[subjects]: ' . implode(' | ', array_filter($headers, fn($x) => $x !== '')) . PHP_EOL;

    // Notes sheet header (A1) when present
    $notes = $ss->getSheetByName('Notes');
    if ($notes) {
        echo 'headers[Notes]: ' . (string) $notes->getCell('A1')->getValue() . PHP_EOL;
    }

    echo 'status: OK' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'status: ERROR ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
