<?php
// Bootstrap Laravel (CLI-safe)
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function out($k, $v) {
    if (!is_scalar($v)) {
        $v = json_encode($v);
    }
    echo $k . '=' . $v . PHP_EOL;
}

$out = [
    'tb_mas_campuses_exists' => false,
    'tb_mas_campuses_schema' => [],
    'seed_count' => 0,
    'rows' => [],
    'columns_added' => [],
];

$targetTables = [
    'tb_mas_users',
    'tb_mas_faculty',
    'tb_mas_programs',
    'tb_mas_curriculum',
    'tb_mas_classrooms',
    'tb_mas_classlist',
    'tb_mas_subjects',
];

// 1) Verify tb_mas_campuses exists and schema
$out['tb_mas_campuses_exists'] = Schema::hasTable('tb_mas_campuses');
out('campuses_table_exists', $out['tb_mas_campuses_exists'] ? 1 : 0);

if ($out['tb_mas_campuses_exists']) {
    $out['tb_mas_campuses_schema'] = [
        'id' => Schema::hasColumn('tb_mas_campuses', 'id'),
        'campus_name' => Schema::hasColumn('tb_mas_campuses', 'campus_name'),
        'description' => Schema::hasColumn('tb_mas_campuses', 'description'),
    ];
    out('campuses_schema_id', $out['tb_mas_campuses_schema']['id'] ? 1 : 0);
    out('campuses_schema_campus_name', $out['tb_mas_campuses_schema']['campus_name'] ? 1 : 0);
    out('campuses_schema_description', $out['tb_mas_campuses_schema']['description'] ? 1 : 0);

    // 2) Verify seeding produced rows
    $rows = DB::table('tb_mas_campuses')->orderBy('id', 'asc')->get(['id','campus_name','description']);
    $out['seed_count'] = $rows->count();
    $out['rows'] = $rows->toArray();
    out('campuses_seed_count', $out['seed_count']);
    foreach ($rows as $r) {
        out('campus_row', ['id' => $r->id, 'campus_name' => $r->campus_name]);
    }
}

// 3) Confirm campus_id columns exist on legacy tables
foreach ($targetTables as $t) {
    $hasTable = Schema::hasTable($t);
    $hasCol = $hasTable ? Schema::hasColumn($t, 'campus_id') : false;
    $out['columns_added'][$t] = [
        'table_exists' => $hasTable,
        'campus_id_exists' => $hasCol,
    ];
    out("table_exists:$t", $hasTable ? 1 : 0);
    out("campus_id_exists:$t", $hasCol ? 1 : 0);
}

out('status', 'ok');
