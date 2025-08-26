<?php
// Bootstrap Laravel HTTP Kernel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel as HttpKernel;

function out($k, $v) {
    if (!is_scalar($v)) {
        $v = json_encode($v);
    }
    echo $k . '=' . $v . PHP_EOL;
}

$httpKernel = $app->make(HttpKernel::class);

// Resolve test faculty id (created by ensure_test_faculty.php)
$facultyRow = DB::table('tb_mas_faculty')->where('strUsername', 'testfaculty')->first();
$facultyId = $facultyRow ? (int) $facultyRow->intID : null;
out('faculty_id', $facultyId ?? 'null');

// Helper to dispatch HTTP request through Laravel (includes middleware)
function http_call($kernel, $method, $uri, $headers = []) {
    $req = Request::create($uri, $method);
    foreach ($headers as $hk => $hv) {
        $req->headers->set($hk, $hv);
    }
    $res = $kernel->handle($req);
    $status = $res->getStatusCode();
    $body = $res->getContent();
    $json = null;
    $decoded = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $json = $decoded;
    }
    return ['status' => $status, 'body' => $body, 'json' => $json];
}

// Seed: create 2 campuses directly (bypass route auth) for focused delete tests
$nowTag = date('His');
$unrefName = "HTTP Unref Campus {$nowTag}";
$refName   = "HTTP Ref Campus {$nowTag}";

// Insert unreferenced campus
$unrefId = DB::table('tb_mas_campuses')->insertGetId([
    'campus_name' => $unrefName,
    'description' => 'unreferenced campus for http test',
    'status'      => 'active',
]);

// Insert referenced campus
$refId = DB::table('tb_mas_campuses')->insertGetId([
    'campus_name' => $refName,
    'description' => 'referenced campus for http test',
    'status'      => 'active',
]);

out('seed_unref_id', $unrefId);
out('seed_ref_id', $refId);

// Attach reference in a known table (programs) if possible
$attached = 0;
$programKey = null;
if (Schema::hasTable('tb_mas_programs') && Schema::hasColumn('tb_mas_programs', 'campus_id')) {
    $prog = DB::table('tb_mas_programs')->orderBy('intProgramID', 'asc')->first();
    if ($prog) {
        $programKey = (int) $prog->intProgramID;
        DB::table('tb_mas_programs')->where('intProgramID', $programKey)->update(['campus_id' => $refId]);
        $attached = 1;
    }
}
out('attached_program_ref', $attached);
if ($attached) {
    out('attached_program_id', $programKey);
}

// 1) Attempt delete without X-Faculty-ID header (should be 401 due to role middleware)
$noHdr = http_call($httpKernel, 'DELETE', "/api/v1/campuses/{$unrefId}");
out('no_header_delete_status', $noHdr['status']);
out('no_header_delete_body', $noHdr['body']);

// 2) Delete unreferenced campus with header (should succeed 200)
$headers = [];
if ($facultyId) {
    $headers['X-Faculty-ID'] = (string) $facultyId;
}
$delUnref = http_call($httpKernel, 'DELETE', "/api/v1/campuses/{$unrefId}", $headers);
out('delete_unref_status', $delUnref['status']);
out('delete_unref_body', $delUnref['body']);

// 3) Delete referenced campus with header (should be blocked 409, include usage)
$delRef = http_call($httpKernel, 'DELETE', "/api/v1/campuses/{$refId}", $headers);
out('delete_ref_status', $delRef['status']);
if (is_array($delRef['json'])) {
    out('delete_ref_success', $delRef['json']['success'] ?? null);
    out('delete_ref_message', $delRef['json']['message'] ?? null);
    // summarize usage keys if present
    $usageKeys = isset($delRef['json']['usage']) && is_array($delRef['json']['usage'])
        ? implode(',', array_keys($delRef['json']['usage']))
        : '';
    out('delete_ref_usage_keys', $usageKeys);
} else {
    out('delete_ref_body', $delRef['body']);
}

// 4) Delete nonexistent campus id with header (should be 404)
$nonexistentId = 999999;
$del404 = http_call($httpKernel, 'DELETE', "/api/v1/campuses/{$nonexistentId}", $headers);
out('delete_404_status', $del404['status']);
out('delete_404_body', $del404['body']);

// Cleanup: revert program link if attached
if ($attached) {
    DB::table('tb_mas_programs')->where('intProgramID', $programKey)->update(['campus_id' => null]);
}

// Final status
out('status', 'http-delete-tests-complete');
