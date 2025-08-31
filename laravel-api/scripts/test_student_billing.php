<?php

// Bootstrap Laravel for API smoke testing of Student Billing feature.
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\DB;

define('LARAVEL_START', microtime(true));

// Resolve project base (this script resides in laravel-api/scripts/)
$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    fwrite(STDERR, "Failed to resolve laravel base path\n");
    exit(1);
}

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
// Bootstrap the application so Facades (DB, etc.) are available
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

// Utility: send request and print response
function call_api(Kernel $kernel, string $method, string $uri, array $headers = [], array $payload = []): void {
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
}

// ------------------------------
// Preconditions / Resolve inputs
// ------------------------------
echo "=== DB sanity checks ===\n";

// Term (use latest for convenience)
$sy = DB::table('tb_mas_sy')->orderBy('intID', 'desc')->first();
if (!$sy) {
    echo "No tb_mas_sy rows found. Aborting.\n";
    exit(1);
}
$syid = (int) $sy->intID;
echo "Resolved syid={$syid}\n";

// Student with number
$user = DB::table('tb_mas_users')
    ->whereNotNull('strStudentNumber')
    ->orderBy('intID', 'asc')
    ->first();

if (!$user) {
    echo "No tb_mas_users with strStudentNumber found. Aborting.\n";
    exit(1);
}
$studentNumber = (string) $user->strStudentNumber;
$studentId     = (int) $user->intID;
echo "Resolved student_number={$studentNumber}; student_id={$studentId}\n\n";

// Headers for acting context (Finance/Admin middleware uses role guard via session bridge;
// we include X-Faculty-ID for parity with other finance endpoints)
// Resolve an acting faculty id dynamically (prefer the test account if present)
$facRow = DB::table('tb_mas_faculty')->where('strUsername', 'testfaculty')->first();
$actingFacultyId = $facRow ? (int) $facRow->intID : (int) (DB::table('tb_mas_faculty')->orderBy('intID', 'asc')->value('intID') ?? 0);
if (!$actingFacultyId) {
    echo "No faculty found to act as X-Faculty-ID. Aborting.\n";
    exit(1);
}
$headers = [
    'X-Faculty-ID' => (string) $actingFacultyId,
    'Accept'       => 'application/json',
];

// ------------------------------
// Student Billing CRUD sequence
// ------------------------------
echo "=== Student Billing CRUD Smoke ===\n";

// 1) List before create (should be empty or existing baseline)
call_api($kernel, 'GET', "/api/v1/finance/student-billing?student_id={$studentId}&term={$syid}", $headers);

// 2) Create a positive charge
$createPayload = [
    'student_id'  => $studentId,
    'term'        => $syid,
    'description' => 'Test Charge - ID Replacement',
    'amount'      => 250.00,
    'remarks'     => 'Automated smoke',
];
call_api($kernel, 'POST', "/api/v1/finance/student-billing", $headers, $createPayload);

// Fetch the latest created id for this student/term
$created = DB::table('tb_mas_student_billing')
    ->where('intStudentID', $studentId)
    ->where('syid', $syid)
    ->orderBy('intID', 'desc')
    ->first();

$createdId = $created ? (int) $created->intID : 0;
echo "Created billing id={$createdId}\n";

// 3) Show created item
if ($createdId) {
    call_api($kernel, 'GET', "/api/v1/finance/student-billing/{$createdId}", $headers);
}

// 4) Update to adjust amount and description
$updatePayload = [
    'description' => 'Test Charge - ID Replacement (Adjusted)',
    'amount'      => 275.00,
    'remarks'     => 'Adjusted by smoke test',
];
if ($createdId) {
    call_api($kernel, 'PUT', "/api/v1/finance/student-billing/{$createdId}", $headers, $updatePayload);
}

// 5) Create a negative credit line
$createCreditPayload = [
    'student_id'  => $studentId,
    'term'        => $syid,
    'description' => 'Test Credit - Courtesy Discount',
    'amount'      => -100.00,
    'remarks'     => 'Automated smoke credit',
];
call_api($kernel, 'POST', "/api/v1/finance/student-billing", $headers, $createCreditPayload);

// 6) List after create/update to verify two rows and net effect
call_api($kernel, 'GET', "/api/v1/finance/student-billing?student_id={$studentId}&term={$syid}", $headers);

// ------------------------------
// Tuition compute integration
// ------------------------------
echo "=== Tuition Compute Integration (Check additional lines) ===\n";

// Use the dedicated tuition compute controller (expects 'term' for syid): GET /api/v1/tuition/compute?student_number=&term=
// Note: In this project, the route is /api/v1/tuition/compute with TuitionController.
call_api($kernel, 'GET', "/api/v1/tuition/compute?student_number={$studentNumber}&term={$syid}", $headers);

// ------------------------------
// Cleanup: delete the created rows (optional)
// ------------------------------
echo "=== Cleanup (optional) ===\n";
$rows = DB::table('tb_mas_student_billing')
    ->where('intStudentID', $studentId)
    ->where('syid', $syid)
    ->whereIn('description', [
        'Test Charge - ID Replacement',
        'Test Charge - ID Replacement (Adjusted)',
        'Test Credit - Courtesy Discount',
    ])->get();

foreach ($rows as $r) {
    $rid = (int) $r->intID;
    call_api($kernel, 'DELETE', "/api/v1/finance/student-billing/{$rid}", $headers);
}

// Done
$kernel->terminate(Request::capture(), response());
