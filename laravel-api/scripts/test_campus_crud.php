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

// 0) Baseline counts
$baseline = DB::table('tb_mas_campuses')->count();
out('baseline_count', $baseline);

// 1) Create campus (happy path)
$name = 'ZZ Campus ' . date('His');
$req = Request::create('/api/v1/campuses', 'POST', [
    'campus_name' => $name,
    'description' => 'temporary seed for test'
]);
$res = $controller->store($req);
$payload = $res->getData(true);
$newId = $payload['data']['id'] ?? 0;
out('create_status', $res->getStatusCode());
out('created_id', $newId);

// 2) Show campus
$showRes = $controller->show((int)$newId);
out('show_status', $showRes->getStatusCode());
out('show_data', $showRes->getData(true));

// 3) Index with search filter
$reqIndex = Request::create('/api/v1/campuses?q=ZZ', 'GET', ['q' => 'ZZ']);
$indexRes = $controller->index($reqIndex);
out('index_status', $indexRes->getStatusCode());
$dataList = $indexRes->getData(true);
$found = 0;
if (isset($dataList['data']) && is_array($dataList['data'])) {
    foreach ($dataList['data'] as $row) {
        if (($row['id'] ?? 0) === (int)$newId) {
            $found = 1;
            break;
        }
    }
}
out('index_found_created', $found);

// 4) Update campus (happy path)
$reqUpdate = Request::create('/api/v1/campuses/'.$newId, 'PUT', [
    'campus_name' => $name . ' Updated',
    'description' => 'updated'
]);
$updRes = $controller->update($reqUpdate, (int)$newId);
out('update_status', $updRes->getStatusCode());
out('update_data', $updRes->getData(true));

// 5) Duplicate validation (should fail 422) using existing seeded name 'Main Campus'
try {
    $dupReq = Request::create('/api/v1/campuses', 'POST', [
        'campus_name' => 'Main Campus',
        'description' => 'dup test'
    ]);
    // In CLI context, validation throws ValidationException; catch it
    $controller->store($dupReq);
    out('dup_create_status', 'unexpected_success');
} catch (\Illuminate\Validation\ValidationException $ex) {
    out('dup_create_status', 422);
    out('dup_errors', $ex->errors());
} catch (\Throwable $t) {
    out('dup_create_status', 'unexpected_error: ' . $t->getMessage());
}

// 6) FK behavior test: assign campus_id to a program then delete campus and ensure campus_id becomes NULL
$fkTable = 'tb_mas_programs';
$fkAssignedNull = -1;
if (Schema::hasTable($fkTable) && Schema::hasColumn($fkTable, 'campus_id')) {
    $prog = DB::table($fkTable)->orderBy('intProgramID', 'asc')->first();
    if ($prog) {
        DB::table($fkTable)->where('intProgramID', $prog->intProgramID)->update(['campus_id' => (int)$newId]);
        $before = DB::table($fkTable)->where('intProgramID', $prog->intProgramID)->value('campus_id');
        out('fk_before_delete', $before);

        // 7) Delete campus
        $delRes = $controller->destroy((int)$newId);
        out('delete_status', $delRes->getStatusCode());

        $after = DB::table($fkTable)->where('intProgramID', $prog->intProgramID)->value('campus_id');
        // Expect NULL due to ON DELETE SET NULL
        $fkAssignedNull = is_null($after) ? 1 : 0;
        out('fk_after_delete_is_null', $fkAssignedNull);
    } else {
        // No program rows to test with
        $delRes = $controller->destroy((int)$newId);
        out('delete_status', $delRes->getStatusCode());
        out('fk_after_delete_is_null', -1);
    }
} else {
    // Table/column missing (guarded env)
    $delRes = $controller->destroy((int)$newId);
    out('delete_status', $delRes->getStatusCode());
    out('fk_after_delete_is_null', -2);
}

 // System logs for this entity_id
$logs = DB::table('tb_mas_system_log')
    ->where('entity', 'Campus')
    ->where('entity_id', (int)$newId)
    ->orderBy('id', 'asc')
    ->get();

out('system_log_count', $logs->count());

$actions = [];
foreach ($logs as $log) {
    $actions[] = $log->action;
}
out('system_log_actions', $actions);

// Final counts
$final = DB::table('tb_mas_campuses')->count();
out('final_count', $final);
out('status', 'ok');
