<?php
/**
 * Critical-path test for Cashier Payment Entry (internal Laravel kernel, no external curl).
 *
 * Usage:
 *   php laravel-api/scripts/test_cashier_payment_entry.php [faculty_id]
 * Default faculty_id: 13
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

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

echo "=== Cashier Payment Entry Critical-path Test (faculty_id={$facultyId}) ===\n\n";

$headers = [
    'X-Faculty-ID' => (string)$facultyId,
    'Accept'       => 'application/json',
];

// Ensure we have a student id and a SYID (term)
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

// 1) GET /cashiers
$resList = call_api($kernel, 'GET', "/api/v1/cashiers?includeStats=0", $headers);
$decodedList = json_decode($resList['body'], true);
if (json_last_error() !== JSON_ERROR_NONE || empty($decodedList['data']) || !is_array($decodedList['data'])) {
    echo "Failed to fetch cashiers list or empty list returned.\n";
    exit(1);
}
$first = $decodedList['data'][0];
$cashierId = (int)($first['id'] ?? 0);
if ($cashierId <= 0) {
    echo "No valid cashier id found.\n";
    exit(1);
}

// 2) SHOW /cashiers/{id} to inspect ranges and campus
$resShow = call_api($kernel, 'GET', "/api/v1/cashiers/{$cashierId}?includeStats=0", $headers);
$decodedShow = json_decode($resShow['body'], true);
if (json_last_error() !== JSON_ERROR_NONE || empty($decodedShow['data'])) {
    echo "Failed to fetch cashier details.\n";
    exit(1);
}
$detail = $decodedShow['data'];
$campusId = isset($detail['campus_id']) ? (int)$detail['campus_id'] : null;

$or = $detail['or'] ?? ['start'=>null,'end'=>null,'current'=>null];
$inv = $detail['invoice'] ?? ['start'=>null,'end'=>null,'current'=>null];

$orValid = ($or['start'] ?? 0) && ($or['end'] ?? 0) && ($or['current'] ?? 0) && $or['start'] <= $or['current'] && $or['current'] <= $or['end'];
$invValid = ($inv['start'] ?? 0) && ($inv['end'] ?? 0) && ($inv['current'] ?? 0) && $inv['start'] <= $inv['current'] && $inv['current'] <= $inv['end'];

// 3) If both invalid, try to configure OR range minimally
if (!$orValid && !$invValid) {
    $payloadRanges = [
        'campus_id'    => $campusId ?: 1,
        'or_start'     => 900000,
        'or_end'       => 900010,
        // leave invoice untouched in this critical path
    ];
    call_api($kernel, 'POST', "/api/v1/cashiers/{$cashierId}/ranges", $headers, $payloadRanges);

    // Re-fetch details
    $resShow2 = call_api($kernel, 'GET', "/api/v1/cashiers/{$cashierId}?includeStats=0", $headers);
    $decodedShow2 = json_decode($resShow2['body'], true);
    $detail = $decodedShow2['data'] ?? $detail;
    $or = $detail['or'] ?? $or;
    $inv = $detail['invoice'] ?? $inv;

    $orValid = ($or['start'] ?? 0) && ($or['end'] ?? 0) && ($or['current'] ?? 0) && $or['start'] <= $or['current'] && $or['current'] <= $or['end'];
}

// Pick mode
$mode = $orValid ? 'or' : ($invValid ? 'invoice' : 'or');
echo "Chosen mode: {$mode}\n";

// 4) POST /cashiers/{id}/payments (happy path)
$amount = 123.45;
$payloadPayment = [
    'student_id'  => $studentId,
    'term'        => $syid, // SYID
    'mode'        => $mode,
    'amount'      => $amount,
    'description' => 'Tuition Payment',
    'remarks'     => 'Critical-path test entry',
    'method'      => 'Cash',
    'posted_at'   => date('Y-m-d H:i:s'),
];
if ($campusId) $payloadPayment['campus_id'] = (int)$campusId;

$resPay = call_api($kernel, 'POST', "/api/v1/cashiers/{$cashierId}/payments", $headers, $payloadPayment);

// 5) Refresh stats and show
call_api($kernel, 'GET', "/api/v1/cashiers/{$cashierId}/stats", $headers);

// 6) FinanceService compatibility: GET /finance/payment-details
$uriPd = "/api/v1/finance/payment-details?student_id={$studentId}&term={$syid}";
call_api($kernel, 'GET', $uriPd, $headers);

echo "=== Done (critical-path payment entry) ===\n";

// Properly terminate the kernel (optional for CLI)
$kernel->terminate(Request::capture(), response());
