<?php
// Thorough test for compute_full on tb_mas_scholarships
// Usage: php scripts/test_compute_full.php
declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ScholarshipService;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function out($label, $data) {
    echo $label . ': ' . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

function first_user_id(): int {
    $u = DB::table('tb_mas_users')->select('intID')->orderBy('intID')->first();
    return $u && isset($u->intID) ? (int) $u->intID : 1;
}

/**
 * Insert scholarship row without the compute_full field to assert DB default (true) applies.
 */
function create_scholarship_without_compute_full(string $name): int {
    $payload = [];

    if (Schema::hasColumn('tb_mas_scholarships', 'name'))            $payload['name'] = $name;
    if (Schema::hasColumn('tb_mas_scholarships', 'code'))            $payload['code'] = null;
    if (Schema::hasColumn('tb_mas_scholarships', 'deduction_type'))  $payload['deduction_type'] = 'scholarship';
    if (Schema::hasColumn('tb_mas_scholarships', 'deduction_from'))  $payload['deduction_from'] = 'in-house';
    if (Schema::hasColumn('tb_mas_scholarships', 'status'))          $payload['status'] = 'active';
    if (Schema::hasColumn('tb_mas_scholarships', 'description'))     $payload['description'] = 'compute_full default test';
    if (Schema::hasColumn('tb_mas_scholarships', 'created_by_id'))   $payload['created_by_id'] = first_user_id();

    // Intentionally DO NOT set compute_full; rely on DB default (true)
    $id = (int) DB::table('tb_mas_scholarships')->insertGetId($payload);
    return $id;
}

/**
 * Ensure scholarship exists; if not, create; then update compute_full explicitly.
 */
function ensure_and_set_compute_full(string $name, bool $computeFull): int {
    $row = DB::table('tb_mas_scholarships')->where('name', $name)->first();
    if (!$row) {
        $payload = [];
        if (Schema::hasColumn('tb_mas_scholarships', 'name'))            $payload['name'] = $name;
        if (Schema::hasColumn('tb_mas_scholarships', 'code'))            $payload['code'] = null;
        if (Schema::hasColumn('tb_mas_scholarships', 'deduction_type'))  $payload['deduction_type'] = 'scholarship';
        if (Schema::hasColumn('tb_mas_scholarships', 'deduction_from'))  $payload['deduction_from'] = 'in-house';
        if (Schema::hasColumn('tb_mas_scholarships', 'status'))          $payload['status'] = 'active';
        if (Schema::hasColumn('tb_mas_scholarships', 'description'))     $payload['description'] = 'compute_full explicit test';
        if (Schema::hasColumn('tb_mas_scholarships', 'created_by_id'))   $payload['created_by_id'] = first_user_id();
        if (Schema::hasColumn('tb_mas_scholarships', 'compute_full'))    $payload['compute_full'] = $computeFull ? 1 : 0;

        $id = (int) DB::table('tb_mas_scholarships')->insertGetId($payload);
        return $id;
    }

    if (Schema::hasColumn('tb_mas_scholarships', 'compute_full')) {
        DB::table('tb_mas_scholarships')->where('intID', $row->intID)->update([
            'compute_full' => $computeFull ? 1 : 0
        ]);
    }
    if (Schema::hasColumn('tb_mas_scholarships', 'status')) {
        DB::table('tb_mas_scholarships')->where('intID', $row->intID)->update(['status' => 'active']);
    }
    return (int) $row->intID;
}

/** @var ScholarshipService $svc */
$svc = app(ScholarshipService::class);

// 1) Create a row WITHOUT compute_full in payload; expect default true from DB
$nameDefault = 'ComputeFull Default TRUE Test';
$idDefault = create_scholarship_without_compute_full($nameDefault);
$getDefault = $svc->get($idDefault);
$listAll    = $svc->list(['status' => 'active']);
$listedDefault = null;
foreach ($listAll as $it) {
    if ((int)($it['id'] ?? 0) === $idDefault) {
        $listedDefault = $it;
        break;
    }
}
out('DefaultRow', ['id' => $idDefault, 'service_get' => $getDefault, 'service_list_item' => $listedDefault]);

// 2) Create/ensure a row, set compute_full = false explicitly, verify via service get/list
$nameFalse = 'ComputeFull Explicit FALSE Test';
$idFalse = ensure_and_set_compute_full($nameFalse, false);
$getFalse = $svc->get($idFalse);
$listAll2 = $svc->list(['status' => 'active']);
$listedFalse = null;
foreach ($listAll2 as $it) {
    if ((int)($it['id'] ?? 0) === $idFalse) {
        $listedFalse = $it;
        break;
    }
}
out('ExplicitFalseRow', ['id' => $idFalse, 'service_get' => $getFalse, 'service_list_item' => $listedFalse]);

// 3) Toggle compute_full via service update: false -> true and true -> false
$toggleA_before = $svc->get($idFalse);
$toggleA_after  = $svc->update($idFalse, ['compute_full' => true]);
$toggleB_id     = $idDefault;
$toggleB_before = $svc->get($toggleB_id);
$toggleB_after  = $svc->update($toggleB_id, ['compute_full' => false]);

out('ToggleA_false_to_true', ['before' => $toggleA_before, 'after' => $toggleA_after]);
out('ToggleB_true_to_false', ['before' => $toggleB_before, 'after' => $toggleB_after]);

// 4) Summary checks
$summary = [
    'DefaultCreated_compute_full_true' => isset($getDefault['compute_full']) ? (bool) $getDefault['compute_full'] === true : null,
    'ExplicitFalse_compute_full_false' => isset($getFalse['compute_full']) ? (bool) $getFalse['compute_full'] === false : null,
    'ToggleA_to_true'                  => isset($toggleA_after['compute_full']) ? (bool) $toggleA_after['compute_full'] === true : null,
    'ToggleB_to_false'                 => isset($toggleB_after['compute_full']) ? (bool) $toggleB_after['compute_full'] === false : null,
];
out('Summary', $summary);

echo "DONE\n";
