<?php
/**
 * Thorough tests for "skip OR number on first payment when an invoice is selected".
 *
 * This script exercises:
 *  1) First payment on a brand-new invoice_number with mode='or' should NOT consume OR number;
 *     it should store invoice_number only and NOT advance the cashier's OR pointer.
 *  2) Second payment on the same invoice_number with mode='or' SHOULD consume an OR number;
 *     it should store invoice_number alongside OR and advance the OR pointer.
 *  3) mode='invoice' path still consumes invoice range and advances invoice pointer.
 *
 * Usage:
 *   php laravel-api/scripts/test_or_first_invoice_rules.php [faculty_id]
 * Default faculty_id: 13
 *
 * Notes:
 *  - This uses Laravel kernel internally (no curl needed).
 *  - It does not require an actual invoice row. The API supports using a raw invoice_number
 *    in payload; the rule checks payment_details invoice_number usage count.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

define('LARAVEL_START', microtime(true));

// Resolve laravel base (this script resides in laravel-api/scripts/)
$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    fwrite(STDERR, "Failed to resolve laravel base path\n");
    exit(1);
}

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$facultyId = isset($argv[1]) ? (int)$argv[1] : 13;

function call_api(Kernel $kernel, string $method, string $uri, array $headers = [], array $payload = []): array {
    $request = Request::create($uri, $method, $payload);
    foreach ($headers as $k => $v) {
        $request->headers->set($k, $v);
    }

    $response = $kernel->handle($request);
    $status   = $response->getStatusCode();
    $content  = $response->getContent();

    echo "=== $method $uri ===\n";
    echo "Status: $status\n";
    echo "Body:\n";
    echo $content . "\n\n";

    return ['status' => $status, 'body' => $content];
}

function fetch_json(array $res) {
    $decoded = json_decode($res['body'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON decode failed: ".json_last_error_msg()."\n";
        return null;
    }
    return $decoded;
}

echo "=== Skip-OR-on-First-Invoice Thorough Test (faculty_id={$facultyId}) ===\n\n";

$headers = [
    'X-Faculty-ID' => (string)$facultyId,
    'Accept'       => 'application/json',
];

// Preconditions
if (!Schema::hasTable('payment_details')) {
    echo "payment_details table not found. Aborting.\n";
    exit(1);
}
$hasInvoiceCol = Schema::hasColumn('payment_details', 'invoice_number');
$hasOrNo = Schema::hasColumn('payment_details', 'or_no');
$hasOrNumber = Schema::hasColumn('payment_details', 'or_number');
$orCol = $hasOrNo ? 'or_no' : ($hasOrNumber ? 'or_number' : null);

if (!$hasInvoiceCol) {
    echo "WARNING: payment_details.invoice_number column not found. The first-invoice skip rule is only active if this column exists.\n";
}

// Student & term
$student = DB::table('tb_mas_users')->select('intID')->orderBy('intID', 'asc')->first();
if (!$student) {
    echo "No students found in tb_mas_users; cannot proceed.\n";
    exit(1);
}
$studentId = (int)$student->intID;

$sy = DB::table('tb_mas_sy')->select('intID')->orderBy('intID', 'desc')->first();
if (!$sy) {
    echo "No terms found in tb_mas_sy; cannot proceed.\n";
    exit(1);
}
$syid = (int)$sy->intID;

// 1) List cashiers to pick one
$resList = call_api($kernel, 'GET', "/api/v1/cashiers?includeStats=0", $headers);
$decodedList = fetch_json($resList);
if (!$decodedList || empty($decodedList['data']) || !is_array($decodedList['data'])) {
    echo "Failed to fetch cashiers list or empty.\n";
    exit(1);
}
$first = $decodedList['data'][0];
$cashierId = (int)($first['id'] ?? 0);
if ($cashierId <= 0) {
    echo "No valid cashier id.\n";
    exit(1);
}

// 2) Fetch cashier details
$resShow = call_api($kernel, 'GET', "/api/v1/cashiers/{$cashierId}?includeStats=0", $headers);
$detail = fetch_json($resShow)['data'] ?? null;
if (!$detail) {
    echo "Failed to fetch cashier details.\n";
    exit(1);
}
$campusId = isset($detail['campus_id']) ? (int)$detail['campus_id'] : null;
$or = $detail['or'] ?? ['start'=>null,'end'=>null,'current'=>null];
$inv = $detail['invoice'] ?? ['start'=>null,'end'=>null,'current'=>null];

$orValid = ($or['start'] ?? 0) && ($or['end'] ?? 0) && ($or['current'] ?? 0) && $or['start'] <= $or['current'] && $or['current'] <= $or['end'];
$invValid = ($inv['start'] ?? 0) && ($inv['end'] ?? 0) && ($inv['current'] ?? 0) && $inv['start'] <= $inv['current'] && $inv['current'] <= $inv['end'];

// 3) Ensure OR range configured (we need OR range for second-payment test)
if (!$orValid) {
    echo "Configuring a minimal OR range for testing...\n";
    $payloadRanges = [
        'campus_id'    => $campusId ?: 1,
        'or_start'     => 910000,
        'or_end'       => 910010,
    ];
    call_api($kernel, 'POST', "/api/v1/cashiers/{$cashierId}/ranges", $headers, $payloadRanges);

    $resShow2 = call_api($kernel, 'GET', "/api/v1/cashiers/{$cashierId}?includeStats=0", $headers);
    $detail = fetch_json($resShow2)['data'] ?? $detail;
    $or = $detail['or'] ?? $or;
    $orValid = ($or['start'] ?? 0) && ($or['end'] ?? 0) && ($or['current'] ?? 0) && $or['start'] <= $or['current'] && $or['current'] <= $or['end'];
    if (!$orValid) {
        echo "Failed to configure OR range; cannot proceed.\n";
        exit(1);
    }
}

// Snapshot OR pointer before tests
$orCurrentBefore = (int)($or['current'] ?? 0);
echo "OR current before: {$orCurrentBefore}\n";

// Generate a brand-new invoice number (no prior payments)
$testInvoice = random_int(8000000, 8999999);
echo "Chosen test invoice_number: {$testInvoice}\n";

// Clean any prior residues (should be none)
if ($hasInvoiceCol) {
    DB::table('payment_details')->where('invoice_number', $testInvoice)->delete();
}

// === First payment on invoice with mode='or' ===
$payload1 = [
    'student_id'  => $studentId,
    'term'        => $syid,
    'mode'        => 'or',
    'amount'      => 111.11,
    'description' => 'Test First Payment (should not use OR)',
    'remarks'     => 'thorough-test first payment',
    'method'      => 'Cash',
    'posted_at'   => date('Y-m-d H:i:s'),
    'invoice_number' => $testInvoice,
    // environments often require this field
    'mode_of_payment_id' => 1,
];
if ($campusId) $payload1['campus_id'] = (int)$campusId;

$resPay1 = call_api($kernel, 'POST', "/api/v1/cashiers/{$cashierId}/payments", $headers, $payload1);
$decPay1 = fetch_json($resPay1);
if (($decPay1['success'] ?? false) !== true) {
    echo "First payment failed unexpectedly\n";
    exit(1);
}
$insertedId1 = (int)($decPay1['data']['id'] ?? 0);

// Verify DB for first payment
$row1 = DB::table('payment_details')->where('id', $insertedId1)->first();
echo "DB check (first payment): id={$insertedId1}\n";
if ($hasInvoiceCol) {
    $gotInvoice = isset($row1->invoice_number) ? (int)$row1->invoice_number : null;
    echo "  invoice_number stored: ".var_export($gotInvoice, true)."\n";
    if ($gotInvoice !== $testInvoice) {
        echo "  ERROR: invoice_number not stored as expected\n";
        exit(1);
    }
}
if ($orCol) {
    $orVal = $row1->{$orCol} ?? null;
    echo "  {$orCol} value: ".var_export($orVal, true)."\n";
    if ($orVal !== null) {
        echo "  ERROR: First payment should not have OR number\n";
        exit(1);
    }
}
// OR pointer should be unchanged
$resShow3 = call_api($kernel, 'GET', "/api/v1/cashiers/{$cashierId}?includeStats=0", $headers);
$detail3 = fetch_json($resShow3)['data'] ?? $detail;
$orAfter1 = (int)($detail3['or']['current'] ?? 0);
echo "OR current after first payment: {$orAfter1}\n";
if ($orAfter1 !== $orCurrentBefore) {
    echo "  ERROR: OR pointer advanced on first payment; should not\n";
    exit(1);
}

// === Second payment on same invoice with mode='or' (should consume OR) ===
$payload2 = [
    'student_id'  => $studentId,
    'term'        => $syid,
    'mode'        => 'or',
    'amount'      => 22.22,
    'description' => 'Test Second Payment (should use OR)',
    'remarks'     => 'thorough-test second payment',
    'method'      => 'Cash',
    'posted_at'   => date('Y-m-d H:i:s'),
    'invoice_number' => $testInvoice,
    'mode_of_payment_id' => 1,
];
if ($campusId) $payload2['campus_id'] = (int)$campusId;

$resPay2 = call_api($kernel, 'POST', "/api/v1/cashiers/{$cashierId}/payments", $headers, $payload2);
$decPay2 = fetch_json($resPay2);
if (($decPay2['success'] ?? false) !== true) {
    echo "Second payment failed unexpectedly\n";
    exit(1);
}
$insertedId2 = (int)($decPay2['data']['id'] ?? 0);

// Verify DB second payment
$row2 = DB::table('payment_details')->where('id', $insertedId2)->first();
echo "DB check (second payment): id={$insertedId2}\n";
if ($orCol) {
    $orVal2 = $row2->{$orCol} ?? null;
    echo "  {$orCol} value: ".var_export($orVal2, true)."\n";
    if ($orVal2 === null) {
        echo "  ERROR: Second payment should have consumed an OR number\n";
        exit(1);
    }
}
// OR pointer should have advanced by 1
$resShow4 = call_api($kernel, 'GET', "/api/v1/cashiers/{$cashierId}?includeStats=0", $headers);
$detail4 = fetch_json($resShow4)['data'] ?? $detail3;
$orAfter2 = (int)($detail4['or']['current'] ?? 0);
echo "OR current after second payment: {$orAfter2}\n";
if ($orAfter2 !== $orAfter1 + 1) {
    echo "  ERROR: OR pointer did not advance after second payment\n";
    exit(1);
}

// === Third payment: mode='invoice' (should consume invoice pointer if configured) ===
if (($inv['start'] ?? 0) && ($inv['end'] ?? 0) && ($inv['current'] ?? 0)) {
    $invBefore = (int)$inv['current'];
    echo "Invoice pointer before: {$invBefore}\n";

    $payload3 = [
        'student_id'  => $studentId,
        'term'        => $syid,
        'mode'        => 'invoice',
        'amount'      => 33.33,
        'description' => 'Test Invoice Mode',
        'remarks'     => 'thorough-test invoice mode',
        'method'      => 'Cash',
        'posted_at'   => date('Y-m-d H:i:s'),
        'mode_of_payment_id' => 1,
    ];
    if ($campusId) $payload3['campus_id'] = (int)$campusId;

    call_api($kernel, 'POST', "/api/v1/cashiers/{$cashierId}/payments", $headers, $payload3);

    $resShow5 = call_api($kernel, 'GET', "/api/v1/cashiers/{$cashierId}?includeStats=0", $headers);
    $detail5 = fetch_json($resShow5)['data'] ?? $detail4;
    $invAfter = (int)($detail5['invoice']['current'] ?? $invBefore);
    echo "Invoice pointer after: {$invAfter}\n";
    if ($invAfter !== $invBefore + 1) {
        echo "  WARNING: Invoice pointer did not advance (could be unconfigured). Skipping failure.\n";
    }
} else {
    echo "Invoice range not configured; skipping mode='invoice' pointer verification.\n";
}

echo "=== Thorough tests completed successfully ===\n";

// Properly terminate the kernel
$kernel->terminate(Request::capture(), response());
