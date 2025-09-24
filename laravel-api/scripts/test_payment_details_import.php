<?php
/**
 * Smoke test for PaymentDetails Import:
 * - Generates a minimal XLSX in temp with headers and a couple of rows.
 * - Invokes PaymentDetailsImportService->import($path, 'xlsx')
 * - Prints summary and basic assertions.
 *
 * Usage:
 *   C:\xampp8\php\php.exe laravel-api/scripts/test_payment_details_import.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

function println($s) { echo $s . PHP_EOL; }

try {
    $base = __DIR__ . '/..';
    $autoload = $base . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        println("Composer autoload not found at {$autoload}. Run composer install first.");
        exit(1);
    }
    require $autoload;

    /** @var \Illuminate\Foundation\Application $app */
    $app = require $base . '/bootstrap/app.php';

    // Bootstrap console kernel (for DB, config etc.)
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    println("=== Payment Details Import: Smoke Test ===");

    // Build a temporary XLSX file with sample headers and rows
    $tmpDir = sys_get_temp_dir();
    $tmpXlsx = tempnam($tmpDir, 'pd_import_');
    if ($tmpXlsx === false) {
        throw new RuntimeException('Failed to create temp file stub.');
    }
    // PhpSpreadsheet requires actual file extension to detect reader
    $tmpXlsxX = $tmpXlsx . '.xlsx';

    // Build spreadsheet
    $headers = [
        'id',
        'student_number',
        'syid',
        'description',
        'subtotal_order',
        'total_amount_due',
        'method',
        'payment_method',
        'mode_of_payment_id',
        'status',
        'posted_at',
        'or_no',
        'or_number',
        'invoice_number',
        'remarks',
    ];

    $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $ss->getActiveSheet();
    $sheet->setTitle('payment_details');

    $col = 1;
    foreach ($headers as $h) {
        $sheet->setCellValueByColumnAndRow($col, 1, $h);
        $col++;
    }

    // Row 1: Missing/unknown student_number -> should error "Student not found"
    $r = 2;
    $sheet->setCellValue("A{$r}", ''); // id -> insert
    $sheet->setCellValue("B{$r}", 'S-UNKNOWN'); // student_number
    $sheet->setCellValue("C{$r}", '20241'); // syid
    $sheet->setCellValue("D{$r}", 'Reservation Payment'); // description (would classify as reservation payment for invoice)
    $sheet->setCellValue("E{$r}", '5000'); // subtotal_order
    $sheet->setCellValue("N{$r}", '8800001'); // invoice_number
    $sheet->setCellValue("O{$r}", 'Test row with unknown student');

    // Row 2: Missing invoice_number (no auto-create), still unknown student => error
    $r = 3;
    $sheet->setCellValue("A{$r}", '');
    $sheet->setCellValue("B{$r}", 'S-UNKNOWN-2');
    $sheet->setCellValue("C{$r}", '20241');
    $sheet->setCellValue("D{$r}", 'Tuition Partial Payment');
    $sheet->setCellValue("E{$r}", '1234.56');
    $sheet->setCellValue("O{$r}", 'Second row unknown student');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);
    $writer->save($tmpXlsxX);

    println("Temporary XLSX written: {$tmpXlsxX}");

    /** @var \App\Services\PaymentDetailsImportService $svc */
    $svc = $app->make(\App\Services\PaymentDetailsImportService::class);

    $res = $svc->import($tmpXlsxX, 'xlsx');

    println("--- Import Summary ---");
    echo json_encode($res, JSON_PRETTY_PRINT) . PHP_EOL;

    $ok = is_array($res) && array_key_exists('totalRows', $res);
    if (!$ok) {
        throw new RuntimeException('Import summary missing expected keys.');
    }

    // We expect 2 rows total, likely skipped with errors due to unknown student_number
    if ((int)($res['totalRows'] ?? 0) < 1) {
        throw new RuntimeException('Unexpected totalRows; expected >= 1.');
    }

    println("Smoke test completed.");
    exit(0);
} catch (\Throwable $e) {
    echo "Error running test: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
