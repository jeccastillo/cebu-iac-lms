<?php
/**
 * Critical-path tests for Cashier assignment flow using internal Laravel kernel.
 *
 * Steps:
 *  A) Ensure we have an existing cashier row (use the first from GET /cashiers) and assign a faculty that matches campus.
 *  B) Duplicate conflict: create a second cashier in the same campus and try assigning the same faculty_id (expect 422).
 *  C) Campus mismatch (if available): find a faculty from a different campus and try assigning to an existing cashier (expect 422).
 *
 * Usage:
 *   php laravel-api/scripts/test_cashiers_assign.php
 */
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

define('LARAVEL_START', microtime(true));

$basePath = realpath(__DIR__ . '/..');
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

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

echo "=== Cashier Assign Critical-path Tests ===\n\n";

$headers = [
    'X-Faculty-ID' => '13', // any existing faculty for role middleware context (adjust if needed)
    'Accept'       => 'application/json',
];

// Helper: decode JSON body
function json_data(array $resp) {
    $d = json_decode($resp['body'], true);
    return (json_last_error() === JSON_ERROR_NONE) ? $d : null;
}

// 1) Fetch cashiers and pick first row
$resList = call_api($kernel, 'GET', '/api/v1/cashiers?includeStats=0', $headers);
$list = json_data($resList);
$firstCashierId = null;
$firstCampusId  = null;
if ($list && !empty($list['data'])) {
    $first = $list['data'][0];
    $firstCashierId = isset($first['id']) ? (int)$first['id'] : null;
    $firstCampusId  = isset($first['campus_id']) ? (int)$first['campus_id'] : null;
}

if (!$firstCashierId || !$firstCampusId) {
    echo "No existing cashier row found; attempting to create a bootstrap row...\n\n";
    // Create a starter row (minimal ranges) using a faculty that matches campus later
    // We need some campus id; fall back to 1 if unknown
    $firstCampusId = $firstCampusId ?: 1;
    // Temporarily create with a known faculty_id but we will reassign anyway
    // Find any faculty in this campus
    $faculty = DB::table('tb_mas_faculty')->where('campus_id', $firstCampusId)->first();
    if (!$faculty) {
        echo "No faculty found in campus {$firstCampusId}; aborting.\n";
        exit(1);
    }
    $payload = [
        'faculty_id' => (int)$faculty->intID,
        'campus_id'  => (int)$firstCampusId,
        'or_start'   => 900000,
        'or_end'     => 900009,
        'invoice_start' => 910000,
        'invoice_end'   => 910009,
        'temporary_admin' => 0,
    ];
    $resCreate = call_api($kernel, 'POST', '/api/v1/cashiers', $headers, $payload);
    $created = json_data($resCreate);
    if (!$created || empty($created['data']['id'])) {
        echo "Failed to bootstrap a cashier row; aborting.\n";
        exit(1);
    }
    $firstCashierId = (int)$created['data']['id'];
    echo "Bootstrapped cashier id={$firstCashierId}\n\n";
}

// Discover an assignable faculty for same campus (to satisfy campus match)
$facultySameCampus = DB::table('tb_mas_faculty')
    ->select('intID', 'campus_id')
    ->where('campus_id', $firstCampusId)
    ->orderBy('intID', 'asc')
    ->first();
if (!$facultySameCampus) {
    echo "No faculty found for campus_id={$firstCampusId}; cannot test assign.\n";
    exit(1);
}

// A) Happy path: assign faculty to first cashier
echo "--- A) Assign faculty (happy path) ---\n";
$resAssignOk = call_api(
    $kernel,
    'PATCH',
    "/api/v1/cashiers/{$firstCashierId}/assign",
    $headers,
    ['faculty_id' => (int)$facultySameCampus->intID]
);

// B) Duplicate conflict: create second cashier in same campus and attempt same faculty assign
echo "--- B) Duplicate conflict (same campus, same faculty) ---\n";
$payloadSecond = [
    'faculty_id' => (int)$facultySameCampus->intID, // required on create but will be rejected by uniqueness, so use a placeholder faculty first
    'campus_id'  => (int)$firstCampusId,
    'or_start'   => 800000,
    'or_end'     => 800009,
    'invoice_start' => 820000,
    'invoice_end'   => 820009,
    'temporary_admin' => 0,
];
// Create a second row with a different faculty to avoid unique on store
$altFaculty = DB::table('tb_mas_faculty')
    ->where('campus_id', $firstCampusId)
    ->where('intID', '<>', (int)$facultySameCampus->intID)
    ->orderBy('intID', 'asc')
    ->first();

if ($altFaculty) {
    $payloadSecond['faculty_id'] = (int)$altFaculty->intID;
}
$resCreate2 = call_api($kernel, 'POST', '/api/v1/cashiers', $headers, $payloadSecond);
$created2 = json_data($resCreate2);
$secondId = ($created2 && !empty($created2['data']['id'])) ? (int)$created2['data']['id'] : null;

if ($secondId) {
    // Now attempt to assign the same faculty as the first to the second row -> expect 422
    $resAssignDup = call_api(
        $kernel,
        'PATCH',
        "/api/v1/cashiers/{$secondId}/assign",
        $headers,
        ['faculty_id' => (int)$facultySameCampus->intID]
    );
} else {
    echo "Second cashier creation failed; duplicate assignment test skipped.\n\n";
}

// C) Campus mismatch (if possible): find faculty from a different campus
echo "--- C) Campus mismatch ---\n";
$facultyOtherCampus = DB::table('tb_mas_faculty')
    ->whereNotNull('campus_id')
    ->where('campus_id', '<>', $firstCampusId)
    ->orderBy('intID', 'asc')
    ->first();
if ($facultyOtherCampus) {
    $resAssignMismatch = call_api(
        $kernel,
        'PATCH',
        "/api/v1/cashiers/{$firstCashierId}/assign",
        $headers,
        ['faculty_id' => (int)$facultyOtherCampus->intID]
    );
} else {
    echo "No other-campus faculty found; campus mismatch test skipped.\n\n";
}

echo "=== Done (assign tests) ===\n";
