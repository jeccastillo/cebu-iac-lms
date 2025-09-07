<?php
// Quick critical-path test for Curriculum template generation (no DB access required)
require __DIR__ . '/../vendor/autoload.php';

use App\Services\CurriculumImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

function out($label, $value) {
    echo $label . ': ' . $value . PHP_EOL;
}

try {
    $svc = new CurriculumImportService();
    $ss = $svc->generateTemplateXlsx();
    if (!$ss instanceof Spreadsheet) {
        out('result', 'FAILED: not a Spreadsheet');
        exit(1);
    }

    $sheetNames = $ss->getSheetNames();
    out('sheets', implode(', ', $sheetNames));

    // Read headers row 1 for each sheet (limit to first 12 cols for display)
    foreach ($ss->getAllSheets() as $sheet) {
        $title = $sheet->getTitle();
        $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $headers = [];
        for ($c = 1; $c <= min($highestCol, 12); $c++) {
            $v = (string) $sheet->getCellByColumnAndRow($c, 1)->getValue();
            if ($v === '') break;
            $headers[] = $v;
        }
        out("headers[$title]", implode(' | ', $headers));
    }

    out('status', 'OK');
    exit(0);
} catch (\Throwable $e) {
    out('error', $e->getMessage());
    exit(1);
}
