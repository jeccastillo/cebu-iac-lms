<?php
// Quick smoke test for Schedule import template generation (no DB required).
// Usage: php laravel-api/scripts/test_schedule_template.php

require __DIR__ . '/../vendor/autoload.php';

use App\Services\ScheduleImportService;

try {
    $svc = new ScheduleImportService();
    $ss = $svc->generateTemplateXlsx();

    $names = [];
    for ($i = 0; $i < $ss->getSheetCount(); $i++) {
        $names[] = $ss->getSheet($i)->getTitle();
    }
    echo 'sheets: ' . implode(' ', $names) . PHP_EOL;

    // Dump headers of "schedules" sheet (row 1)
    $sheet = $ss->getSheetByName('schedules') ?: $ss->getSheet(0);
    $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
    $headers = [];
    for ($c = 1; $c <= $highestCol; $c++) {
        $headers[] = (string) $sheet->getCellByColumnAndRow($c, 1)->getValue();
    }
    echo 'headers[schedules]: ' . implode(' | ', array_filter($headers, fn($x) => $x !== '')) . PHP_EOL;

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
