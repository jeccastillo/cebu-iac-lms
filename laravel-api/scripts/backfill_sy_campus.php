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

$targetCampusId = 2;

if (!Schema::hasTable('tb_mas_sy')) {
    out('error', 'table tb_mas_sy does not exist');
    exit(1);
}

if (!Schema::hasColumn('tb_mas_sy', 'campus_id')) {
    out('error', 'column campus_id does not exist on tb_mas_sy');
    exit(1);
}

try {
    $affected = DB::table('tb_mas_sy')->update(['campus_id' => $targetCampusId]);
    out('rows_updated', $affected);
    out('campus_id_set_to', $targetCampusId);
    out('status', 'ok');
} catch (\Throwable $e) {
    out('error', $e->getMessage());
    exit(1);
}
