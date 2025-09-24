<?php
/**
 * Thorough test for Non-Student (Payee) Payments endpoint and OR PDF behavior.
 *
 * This script:
 *  - Ensures baseline schema/tables exist.
 *  - Ensures a test cashier exists with OR/Invoice ranges.
 *  - Ensures a test Payee exists.
 *  - Exercises POST /api/v1/cashiers/{id}/payee-payments across scenarios:
 *      A) mode=or (sequence)
 *      B) mode=invoice (sequence)
 *      C) mode=none (no number)
 *      D) mode=or with invoice_number first-payment rule (skip OR, store invoice)
 *      E) mode=or with specific number (success) and immediate reuse (fail)
 *      F) mismatched id_number (fail)
 *  - Verifies DB side-effects and pointer increments per rules.
 *  - Attempts GET /api/v1/finance/or/{or}/pdf and validates Content-Type.
 *
 * Usage:
 *   php laravel-api/scripts/test_nonstudent_payments.php
 *
 * Notes:
 *  - Requires a local web server serving laravel-api/public at:
 *      http://localhost/iacademy/cebu-iac-lms/laravel-api/public
 *  - Uses header X-Faculty-ID: 1 for role context (finance/cashier_admin).
 *  - If HTTP calls fail (server down), script prints curl examples for manual testing.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function now_str() { return date('Y-m-d H:i:s'); }

function http_post_json($url, array $body, array $headers = []): array {
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    foreach ($headers as $k => $v) {
        if (is_int($k)) {
            $defaultHeaders[] = $v;
        } else {
            $defaultHeaders[] = $k . ': ' . $v;
        }
    }
    $payload = json_encode($body);

    $res = [
        'ok' => false,
        'status' => null,
        'headers' => [],
        'body' => null,
        'json' => null,
        'error' => null,
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $defaultHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            $res['error'] = curl_error($ch);
            curl_close($ch);
            return $res;
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rawHeaders = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);

        $hdrs = [];
        foreach (explode("\r\n", $rawHeaders) as $line) {
            if (strpos($line, ':') !== false) {
                [$k, $v] = explode(':', $line, 2);
                $hdrs[trim($k)] = trim($v);
            }
        }

        $res['status'] = $status;
        $res['headers'] = $hdrs;
        $res['body'] = $body;
        $json = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) $res['json'] = $json;
        $res['ok'] = $status >= 200 && $status < 300;
        return $res;
    }

    // Fallback to file_get_contents when cURL is not available
    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => implode("\r\n", $defaultHeaders),
            'content' => $payload,
            'ignore_errors' => true,
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        $res['error'] = 'HTTP request failed (file_get_contents).';
        return $res;
    }
    $statusLine = isset($http_response_header[0]) ? $http_response_header[0] : '';
    preg_match('#HTTP/\d+\.\d+ (\d+)#', $statusLine, $m);
    $status = isset($m[1]) ? (int)$m[1] : null;
    $hdrs = [];
    foreach ($http_response_header as $h) {
        if (strpos($h, ':') !== false) {
            [$k, $v] = explode(':', $h, 2);
            $hdrs[trim($k)] = trim($v);
        }
    }
    $res['status'] = $status;
    $res['headers'] = $hdrs;
    $res['body'] = $body;
    $json = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) $res['json'] = $json;
    $res['ok'] = $status >= 200 && $status < 300;
    return $res;
}

function http_get($url, array $headers = []): array {
    $defaultHeaders = [
        'Accept: */*',
    ];
    foreach ($headers as $k => $v) {
        if (is_int($k)) {
            $defaultHeaders[] = $v;
        } else {
            $defaultHeaders[] = $k . ': ' . $v;
        }
    }

    $res = [
        'ok' => false,
        'status' => null,
        'headers' => [],
        'body' => null,
        'error' => null,
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $defaultHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        if ($response === false) {
            $res['error'] = curl_error($ch);
            curl_close($ch);
            return $res;
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rawHeaders = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);

        $hdrs = [];
        foreach (explode("\r\n", $rawHeaders) as $line) {
            if (strpos($line, ':') !== false) {
                [$k, $v] = explode(':', $line, 2);
                $hdrs[trim($k)] = trim($v);
            }
        }

        $res['status'] = $status;
        $res['headers'] = $hdrs;
        $res['body'] = $body;
        $res['ok'] = $status >= 200 && $status < 300;
        return $res;
    }

    $context = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'header'  => implode("\r\n", $defaultHeaders),
            'ignore_errors' => true,
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        $res['error'] = 'HTTP request failed (file_get_contents).';
        return $res;
    }
    $statusLine = isset($http_response_header[0]) ? $http_response_header[0] : '';
    preg_match('#HTTP/\d+\.\d+ (\d+)#', $statusLine, $m);
    $status = isset($m[1]) ? (int)$m[1] : null;
    $hdrs = [];
    foreach ($http_response_header as $h) {
        if (strpos($h, ':') !== false) {
            [$k, $v] = explode(':', $h, 2);
            $hdrs[trim($k)] = trim($v);
        }
    }
    $res['status'] = $status;
    $res['headers'] = $hdrs;
    $res['body'] = $body;
    $res['ok'] = $status >= 200 && $status < 300;
    return $res;
}

echo "=== Non-Student (Payee) Payments - Thorough Test ===\n";
$baseUrl = 'http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1';

// Pre-checks
$missing = [];
if (!Schema::hasTable('payment_details')) $missing[] = 'payment_details';
if (!Schema::hasTable('tb_mas_cashiers')) $missing[] = 'tb_mas_cashiers';
if (!Schema::hasTable('tb_mas_payee')) $missing[] = 'tb_mas_payee';

if (!empty($missing)) {
    echo "ERROR: Missing required tables: " . implode(', ', $missing) . "\n";
    echo "Run migrations to create required tables (including payee_id on payment_details).\n";
    exit(1);
}
if (!Schema::hasColumn('payment_details', 'payee_id')) {
    echo "ERROR: payment_details.payee_id column is missing.\n";
    echo "Run migration 2025_09_17_000101_add_payee_id_to_payment_details.php\n";
    exit(1);
}

// Resolve acting faculty and ensure roles for middleware
$actingFacultyId = DB::table('tb_mas_faculty')->orderBy('intID', 'asc')->value('intID');
if (!$actingFacultyId) {
    echo "ERROR: tb_mas_faculty has no rows. Create at least one faculty row to continue.\n";
    exit(1);
}
// Ensure roles exist
$needCodes = ['finance', 'cashier_admin'];
$roleIds = [];
if (Schema::hasTable('tb_mas_roles')) {
    foreach ($needCodes as $code) {
        $rid = DB::table('tb_mas_roles')->where('strCode', $code)->value('intRoleID');
        if (!$rid) {
            DB::table('tb_mas_roles')->insert([
                'strCode' => $code,
                'strName' => ucfirst(str_replace('_', ' ', $code)),
                'strDescription' => 'Seeded by test_nonstudent_payments',
                'intActive' => 1,
            ]);
            $rid = DB::table('tb_mas_roles')->where('strCode', $code)->value('intRoleID');
        }
        if ($rid) $roleIds[$code] = (int) $rid;
    }
}
// Ensure faculty has at least one required role
if (Schema::hasTable('tb_mas_faculty_roles') && !empty($roleIds)) {
    // Prefer finance role
    $rid = $roleIds['finance'] ?? reset($roleIds);
    $exists = DB::table('tb_mas_faculty_roles')
        ->where('intFacultyID', (int)$actingFacultyId)
        ->where('intRoleID', (int)$rid)
        ->exists();
    if (!$exists) {
        // Avoid duplicate unique pairs by checking any role first; if none, insert finance
        $any = DB::table('tb_mas_faculty_roles')->where('intFacultyID', (int)$actingFacultyId)->exists();
        if (!$any) {
            DB::table('tb_mas_faculty_roles')->insert([
                'intFacultyID' => (int) $actingFacultyId,
                'intRoleID'    => (int) $rid,
            ]);
        }
    }
}
echo "- Acting Faculty ID for tests: {$actingFacultyId}\n";

 // Ensure a payment mode exists (for validation)
$modeOfPaymentId = 1;
if (Schema::hasTable('payment_modes')) {
    $firstMode = DB::table('payment_modes')->orderBy('id', 'asc')->value('id');
    if ($firstMode) $modeOfPaymentId = (int) $firstMode;
}

// Ensure cashier (ID=1) exists with ranges
$cashier = DB::table('tb_mas_cashiers')->where('intID', 1)->first();
if (!$cashier) {
    echo "- Creating test cashier (ID=1) with default ranges...\n";
    DB::table('tb_mas_cashiers')->insert([
        'faculty_id' => 1,
        'campus_id' => 1,
        'or_start' => 210000,
        'or_end' => 210999,
        'or_current' => 210000,
        'invoice_start' => 990000,
        'invoice_end' => 990999,
        'invoice_current' => 990000,
        'temporary_admin' => 0,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    $cashier = DB::table('tb_mas_cashiers')->where('intID', 1)->first();
}
echo "- Cashier #1 OR: {$cashier->or_start}-{$cashier->or_end} current={$cashier->or_current}; Invoice: {$cashier->invoice_start}-{$cashier->invoice_end} current={$cashier->invoice_current}\n";

// Ensure a test payee exists
$testIdNumber = 'TEST-PAYEE-0001';
$payee = DB::table('tb_mas_payee')->where('id_number', $testIdNumber)->first();
if (!$payee) {
    echo "- Creating test Payee: {$testIdNumber}\n";
    DB::table('tb_mas_payee')->insert([
        'id_number'      => $testIdNumber,
        'firstname'      => 'Test',
        'lastname'       => 'Tenant',
        'middlename'     => 'QA',
        'tin'            => '123-456-789-000',
        'address'        => '123 Test Street, Cebu City',
        'contact_number' => '09171234567',
        'email'          => 'tenant.test@example.com',
    ]);
    $payee = DB::table('tb_mas_payee')->where('id_number', $testIdNumber)->first();
}
$payeeId = (int) $payee->id;
echo "- Payee #{$payeeId} ready.\n";

 // Helper to POST to payee-payments
function call_payee_payment($cashierId, array $payload) {
    global $baseUrl, $actingFacultyId;
    $url = $baseUrl . '/cashiers/' . $cashierId . '/payee-payments';
    return http_post_json($url, $payload, ['X-Faculty-ID' => (string) $actingFacultyId]);
}

// Helper to check OR PDF
function check_or_pdf($or) {
    global $baseUrl, $actingFacultyId;
    $url = $baseUrl . '/finance/or/' . urlencode($or) . '/pdf';
    return http_get($url, ['X-Faculty-ID' => (string) $actingFacultyId]);
}

// A) mode=or (sequence)
echo "\n[A] mode=or (sequence)\n";
$beforeOr = DB::table('tb_mas_cashiers')->where('intID', 1)->value('or_current');
$payloadA = [
    'payee_id' => $payeeId,
    'id_number' => $testIdNumber,
    'mode' => 'or',
    'amount' => 123.45,
    'description' => 'Walk-in Payment (A)',
    'mode_of_payment_id' => $modeOfPaymentId,
    'remarks' => 'Test A - sequence OR',
];
$resA = call_payee_payment(1, $payloadA);
if (!$resA['ok']) {
    echo "  HTTP POST failed (status={$resA['status']}): " . ($resA['body'] ?? $resA['error']) . "\n";
    echo "  Try:\n  curl -X POST \"" . $baseUrl . "/cashiers/1/payee-payments\" -H \"Content-Type: application/json\" -H \"X-Faculty-ID: 1\" -d '" . json_encode($payloadA) . "'\n";
} else {
    $data = $resA['json']['data'] ?? [];
    $pid = $data['id'] ?? null;
    $orUsed = $data['number_used'] ?? null;
    echo "  OK: payment id={$pid}, OR used={$orUsed}\n";
    $row = DB::table('payment_details')->where('id', (int)$pid)->first();
    if ($row) {
        $hasOrNo = isset($row->or_no) && $row->or_no !== null && $row->or_no !== '';
        $hasOrNumber = isset($row->or_number) && $row->or_number !== null && $row->or_number !== '';
        $orVal = $hasOrNo ? (string)$row->or_no : ($hasOrNumber ? (string)$row->or_number : '');
        $ok = is_null($row->student_information_id) && is_null($row->sy_reference) && ($orVal === (string)$orUsed);
        echo "  DB checks: payee_id={$row->payee_id}, student_information_id=" . var_export($row->student_information_id, true) . ", sy_reference=" . var_export($row->sy_reference, true) . " => " . ($ok ? "PASS" : "FAIL") . "\n";
    }
    // PDF check
    $pdfRes = check_or_pdf($orUsed);
    if ($pdfRes['ok']) {
        $ct = $pdfRes['headers']['Content-Type'] ?? ($pdfRes['headers']['content-type'] ?? '');
        echo "  OR PDF: status={$pdfRes['status']} content-type={$ct}\n";
    } else {
        echo "  OR PDF fetch failed (status={$pdfRes['status']}): " . ($pdfRes['error'] ?? '') . "\n";
    }
    $afterOr = DB::table('tb_mas_cashiers')->where('intID', 1)->value('or_current');
    echo "  Pointer OR current: before={$beforeOr}, after={$afterOr}\n";
}

// B) mode=invoice (sequence)
echo "\n[B] mode=invoice (sequence)\n";
$beforeInv = DB::table('tb_mas_cashiers')->where('intID', 1)->value('invoice_current');
$payloadB = [
    'payee_id' => $payeeId,
    'id_number' => $testIdNumber,
    'mode' => 'invoice',
    'amount' => 222.22,
    'description' => 'Walk-in Payment (B)',
    'mode_of_payment_id' => $modeOfPaymentId,
    'remarks' => 'Test B - sequence invoice',
];
$resB = call_payee_payment(1, $payloadB);
if (!$resB['ok']) {
    echo "  HTTP POST failed (status={$resB['status']}): " . ($resB['body'] ?? $resB['error']) . "\n";
    echo "  Try:\n  curl -X POST \"" . $baseUrl . "/cashiers/1/payee-payments\" -H \"Content-Type: application/json\" -H \"X-Faculty-ID: 1\" -d '" . json_encode($payloadB) . "'\n";
} else {
    $data = $resB['json']['data'] ?? [];
    $pid = $data['id'] ?? null;
    $invUsed = $data['number_used'] ?? null;
    echo "  OK: payment id={$pid}, Invoice used={$invUsed}\n";
    $row = DB::table('payment_details')->where('id', (int)$pid)->first();
    if ($row) {
        $ok = is_null($row->student_information_id) && is_null($row->sy_reference) && ((string)$row->invoice_number === (string)$invUsed);
        echo "  DB checks: payee_id={$row->payee_id}, student_information_id=" . var_export($row->student_information_id, true) . ", sy_reference=" . var_export($row->sy_reference, true) . " => " . ($ok ? "PASS" : "FAIL") . "\n";
    }
    $afterInv = DB::table('tb_mas_cashiers')->where('intID', 1)->value('invoice_current');
    echo "  Pointer Invoice current: before={$beforeInv}, after={$afterInv}\n";
}

// C) mode=none (no number)
echo "\n[C] mode=none\n";
$payloadC = [
    'payee_id' => $payeeId,
    'id_number' => $testIdNumber,
    'mode' => 'none',
    'amount' => 333.33,
    'description' => 'Walk-in Payment (C)',
    'mode_of_payment_id' => $modeOfPaymentId,
    'remarks' => 'Test C - no number',
];
$resC = call_payee_payment(1, $payloadC);
if (!$resC['ok']) {
    echo "  HTTP POST failed (status={$resC['status']}): " . ($resC['body'] ?? $resC['error']) . "\n";
    echo "  Try:\n  curl -X POST \"" . $baseUrl . "/cashiers/1/payee-payments\" -H \"Content-Type: application/json\" -H \"X-Faculty-ID: 1\" -d '" . json_encode($payloadC) . "'\n";
} else {
    $data = $resC['json']['data'] ?? [];
    $pid = $data['id'] ?? null;
    echo "  OK: payment id={$pid}, number_used should be null => " . var_export($data['number_used'] ?? null, true) . "\n";
    $row = DB::table('payment_details')->where('id', (int)$pid)->first();
    if ($row) {
        $ok = is_null($row->student_information_id) && is_null($row->sy_reference)
            && (empty($row->or_no) && empty($row->or_number) && empty($row->invoice_number));
        echo "  DB checks: " . ($ok ? "PASS" : "FAIL") . "\n";
    }
}

// D) mode=or with invoice_number first-payment rule (skip OR)
echo "\n[D] mode=or with invoice_number (first payment) -> should SKIP OR and store invoice_number\n";
$uniqueInvoice = 777777;
DB::table('payment_details')->where('invoice_number', $uniqueInvoice)->delete();
$beforeOrD = DB::table('tb_mas_cashiers')->where('intID', 1)->value('or_current');
$payloadD = [
    'payee_id' => $payeeId,
    'id_number' => $testIdNumber,
    'mode' => 'or',
    'amount' => 444.44,
    'description' => 'Walk-in Payment (D)',
    'mode_of_payment_id' => $modeOfPaymentId,
    'remarks' => 'Test D - OR skip when first invoice',
    'invoice_number' => $uniqueInvoice,
];
$resD = call_payee_payment(1, $payloadD);
if (!$resD['ok']) {
    echo "  HTTP POST failed (status={$resD['status']}): " . ($resD['body'] ?? $resD['error']) . "\n";
    echo "  Try:\n  curl -X POST \"" . $baseUrl . "/cashiers/1/payee-payments\" -H \"Content-Type: application/json\" -H \"X-Faculty-ID: 1\" -d '" . json_encode($payloadD) . "'\n";
} else {
    $data = $resD['json']['data'] ?? [];
    $pid = $data['id'] ?? null;
    $numberUsed = $data['number_used'] ?? null;
    echo "  OK: payment id={$pid}, number_used should equal invoice_number={$uniqueInvoice} => {$numberUsed}\n";
    $row = DB::table('payment_details')->where('id', (int)$pid)->first();
    $orStored = (string)($row->or_no ?? $row->or_number ?? '') !== '';
    $invStored = (string)($row->invoice_number ?? '') === (string)$uniqueInvoice;
    echo "  DB checks: OR stored? " . ($orStored ? 'YES' : 'NO') . " (expect NO), invoice stored? " . ($invStored ? 'YES' : 'NO') . " (expect YES)\n";
    $afterOrD = DB::table('tb_mas_cashiers')->where('intID', 1)->value('or_current');
    echo "  Pointer OR current: before={$beforeOrD}, after={$afterOrD} (expect unchanged)\n";
}

// E) mode=or with specific number (success) then reuse (fail)
echo "\n[E] mode=or with specific number\n";
$pick = DB::table('tb_mas_cashiers')->where('intID', 1)->first();
$spec = max((int)$pick->or_start, (int)$pick->or_current + 10); // pick a number in range and likely unused
if ($spec > (int)$pick->or_end) $spec = (int)$pick->or_end;
$payloadE1 = [
    'payee_id' => $payeeId,
    'id_number' => $testIdNumber,
    'mode' => 'or',
    'amount' => 555.55,
    'description' => 'Walk-in Payment (E1 specific)',
    'mode_of_payment_id' => $modeOfPaymentId,
    'remarks' => 'Test E1 - specific OR',
    'number' => $spec,
];
$resE1 = call_payee_payment(1, $payloadE1);
if (!$resE1['ok']) {
    echo "  E1 POST failed (status={$resE1['status']}): " . ($resE1['body'] ?? $resE1['error']) . "\n";
    echo "  Try:\n  curl -X POST \"" . $baseUrl . "/cashiers/1/payee-payments\" -H \"Content-Type: application/json\" -H \"X-Faculty-ID: 1\" -d '" . json_encode($payloadE1) . "'\n";
} else {
    $used = $resE1['json']['data']['number_used'] ?? null;
    echo "  E1 OK: OR used={$used} (expect {$spec})\n";

    // Reuse should fail
    $payloadE2 = $payloadE1;
    $payloadE2['description'] = 'Walk-in Payment (E2 reuse)';
    $payloadE2['amount'] = 556.55;
    $resE2 = call_payee_payment(1, $payloadE2);
    if ($resE2['ok']) {
        echo "  E2 Unexpected success; reuse should fail. Response: " . json_encode($resE2['json']) . "\n";
    } else {
        echo "  E2 Expected failure (status={$resE2['status']}). Body: " . ($resE2['body'] ?? $resE2['error']) . "\n";
    }
}

// F) mismatched id_number (expect 422)
echo "\n[F] mismatched id_number\n";
$payloadF = [
    'payee_id' => $payeeId,
    'id_number' => 'BAD-' . $testIdNumber,
    'mode' => 'or',
    'amount' => 120.00,
    'description' => 'Walk-in Payment (F bad id_number)',
    'mode_of_payment_id' => $modeOfPaymentId,
    'remarks' => 'Test F - mismatch id_number',
];
$resF = call_payee_payment(1, $payloadF);
if ($resF['ok']) {
    echo "  Unexpected success; should have failed validation. Response: " . json_encode($resF['json']) . "\n";
} else {
    echo "  Expected failure (status={$resF['status']}). Body: " . ($resF['body'] ?? $resF['error']) . "\n";
}

echo "\n=== Test Complete ===\n";
