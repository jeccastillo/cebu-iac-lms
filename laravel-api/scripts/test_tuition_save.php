<?php

// Bootstrap Laravel and dispatch HTTP requests internally for critical-path testing
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

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
/** Bootstrap facades so DB::table and other Facades work before handling any HTTP request */
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

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

echo "=== DB sanity checks ===\n";

// Try to resolve a term (active/latest)
$sy = DB::table('tb_mas_sy')->orderBy('intID', 'desc')->first();
if (!$sy) {
    echo "No tb_mas_sy rows found. Aborting.\n";
    exit(1);
}
$syid = (int) $sy->intID;
echo "Resolved syid={$syid}\n";

// Try to resolve a student_number
$user = DB::table('tb_mas_users')
    ->whereNotNull('strStudentNumber')
    ->orderBy('intID', 'asc')
    ->first();

if (!$user) {
    echo "No tb_mas_users with strStudentNumber found. Aborting.\n";
    exit(1);
}
$studentNumber = (string) $user->strStudentNumber;
echo "Resolved student_number={$studentNumber}\n\n";

// Headers for acting user
$headers = [
    'X-Faculty-ID' => '1',
    'Accept'       => 'application/json',
];

// Preflight: active-term (public route)
call_api($kernel, 'GET', "/api/v1/generic/active-term");

// Preflight: tuition-saved (should be protected by role middleware; we pass header)
call_api($kernel, 'GET', "/api/v1/unity/tuition-saved?student_number={$studentNumber}&term={$syid}", $headers);

// Attempt save (expected 200 on valid registration with tuition_year; otherwise 4xx with message)
call_api($kernel, 'POST', "/api/v1/unity/tuition-save", $headers, [
    'student_number' => $studentNumber,
    'term'           => $syid,
]);

/**
 * Inspect generated tuition invoice by registration_id after saving tuition.
 */
try {
    // Resolve user and registration for the same student/term we used above
    $userRow = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
    if ($userRow) {
        $regRow = DB::table('tb_mas_registration')
            ->where('intStudentID', $userRow->intID)
            ->where('intAYID', $syid)
            ->first();

        if ($regRow && isset($regRow->intRegistrationID)) {
            $registrationId = (int) $regRow->intRegistrationID;
            echo "=== Inspect tuition invoice for registration_id={$registrationId} ===\n";
            $invoice = DB::table('tb_mas_invoices')
                ->where('registration_id', $registrationId)
                ->where('type', 'tuition')
                ->orderBy('intID', 'desc')
                ->first();

            if ($invoice) {
                $payloadStr = is_string($invoice->payload) ? $invoice->payload : json_encode($invoice->payload);
                $campusOut = isset($invoice->campus_id) ? (int)$invoice->campus_id : 0;
                $invNoOut  = isset($invoice->invoice_number) ? $invoice->invoice_number : 'null';
                echo "Invoice found: id={$invoice->intID}, amount_total=" . (float)$invoice->amount_total . ", status={$invoice->status}, campus_id={$campusOut}, invoice_number={$invNoOut}\n";
                echo "Payload: {$payloadStr}\n";
            } else {
                echo "No tuition invoice found for registration_id={$registrationId}\n";
            }
        } else {
            echo "No registration found for student_number={$studentNumber} syid={$syid}\n";
        }
    } else {
        echo "Student row not found for student_number={$studentNumber}\n";
    }
} catch (\Throwable $e) {
    echo "Error inspecting tuition invoice: " . $e->getMessage() . "\n";
}

// Done
$kernel->terminate(Request::capture(), response());
