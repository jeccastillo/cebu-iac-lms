<?php
// Bootstrap Laravel (CLI)
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\CampusController;

function out($k, $v) {
    if (!is_scalar($v)) {
        $v = json_encode($v);
    }
    echo $k . '=' . $v . PHP_EOL;
}

$controller = new CampusController();

// Duplicate name critical-path test
$name = 'CRIT Campus ' . date('His');
$createdId = null;

try {
    // First create (should succeed)
    $req1 = Request::create('/api/v1/campuses', 'POST', [
        'campus_name' => $name,
        'description' => 'critical path baseline'
    ]);
    $res1 = $controller->store($req1);
    $payload1 = $res1->getData(true);
    $createdId = $payload1['data']['id'] ?? null;
    out('dup_first_create_status', $res1->getStatusCode());
    out('dup_first_created_id', $createdId);

    // Second create with same name (should fail 422 via ValidationException)
    try {
        $req2 = Request::create('/api/v1/campuses', 'POST', [
            'campus_name' => $name,
            'description' => 'should fail due to duplicate'
        ]);
        $controller->store($req2);
        out('dup_second_create_status', 'unexpected_success');
    } catch (\Illuminate\Validation\ValidationException $ex) {
        out('dup_second_create_status', 422);
        out('dup_second_errors', $ex->errors());
    } catch (\Throwable $t) {
        out('dup_second_create_status', 'unexpected_error: ' . $t->getMessage());
    }

} catch (\Throwable $t) {
    out('dup_flow_error', $t->getMessage());
}

// Missing campus_name (should 422)
try {
    $reqMissing = Request::create('/api/v1/campuses', 'POST', [
        'description' => 'no name provided'
    ]);
    $controller->store($reqMissing);
    out('missing_name_status', 'unexpected_success');
} catch (\Illuminate\Validation\ValidationException $ex) {
    out('missing_name_status', 422);
    out('missing_name_errors', $ex->errors());
} catch (\Throwable $t) {
    out('missing_name_status', 'unexpected_error: ' . $t->getMessage());
}

// Invalid status (should 422)
try {
    $reqBadStatus = Request::create('/api/v1/campuses', 'POST', [
        'campus_name' => $name . ' BAD',
        'status' => 'disabled'
    ]);
    $controller->store($reqBadStatus);
    out('invalid_status_status', 'unexpected_success');
} catch (\Illuminate\Validation\ValidationException $ex) {
    out('invalid_status_status', 422);
    out('invalid_status_errors', $ex->errors());
} catch (\Throwable $t) {
    out('invalid_status_status', 'unexpected_error: ' . $t->getMessage());
}

// Show/Destory non-existent id (404)
$nonExistentId = 99999999;

try {
    $showRes = $controller->show($nonExistentId);
    out('show_nonexistent_status', $showRes->getStatusCode());
} catch (\Throwable $t) {
    out('show_nonexistent_error', $t->getMessage());
}

try {
    $delRes = $controller->destroy($nonExistentId);
    out('delete_nonexistent_status', $delRes->getStatusCode());
} catch (\Throwable $t) {
    out('delete_nonexistent_error', $t->getMessage());
}

// Cleanup and verify logs for created campus if created
if ($createdId) {
    try {
        $delRes = $controller->destroy((int)$createdId);
        out('cleanup_delete_status', $delRes->getStatusCode());
    } catch (\Throwable $t) {
        out('cleanup_delete_error', $t->getMessage());
    }

    // System logs for this entity_id
    $logs = DB::table('tb_mas_system_log')
        ->where('entity', 'Campus')
        ->where('entity_id', (int)$createdId)
        ->orderBy('id', 'asc')
        ->get();

    out('crit_system_log_count', $logs->count());
    $actions = [];
    foreach ($logs as $log) {
        $actions[] = $log->action;
    }
    out('crit_system_log_actions', $actions);
}

out('status', 'critical-path-tests-complete');
