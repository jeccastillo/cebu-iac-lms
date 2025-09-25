<?php

// Usage:
//   php laravel-api/scripts/set_classlist_special.php <classlist_id> <special_class:0|1> <special_multiplier>
// Examples:
//   php laravel-api/scripts/set_classlist_special.php 12345 1 1.25
//   php laravel-api/scripts/set_classlist_special.php 12345 0 1.00
//
// Notes:
// - This script permanently updates tb_mas_classlist.special_class and special_multiplier for the given classlist.
// - Ensure you have run migrations to create the columns first:
//       php laravel-api/artisan migrate
// - Multiplier must be > 0 when special_class=1; the script will guard invalid values.

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function println($msg) { echo $msg . PHP_EOL; }
function jout($k, $v) { echo $k . ': ' . json_encode($v, JSON_PRETTY_PRINT) . PHP_EOL; }

$id   = isset($argv[1]) ? (int)$argv[1] : 0;
$flag = isset($argv[2]) ? (int)$argv[2] : null;
$mult = isset($argv[3]) ? (float)$argv[3] : null;

if ($id <= 0 || $flag === null || $mult === null) {
    println("Usage: php laravel-api/scripts/set_classlist_special.php <classlist_id> <special_class:0|1> <special_multiplier>");
    exit(1);
}

if (!Schema::hasTable('tb_mas_classlist')) {
    println("error: tb_mas_classlist table not found");
    exit(2);
}

if (!Schema::hasColumn('tb_mas_classlist', 'special_class') || !Schema::hasColumn('tb_mas_classlist', 'special_multiplier')) {
    println("error: columns special_class/special_multiplier missing. Run migrations first: php laravel-api/artisan migrate");
    exit(3);
}

if (!in_array($flag, [0,1], true)) {
    println("error: special_class must be 0 or 1");
    exit(4);
}
if ($flag === 1 && (!is_finite($mult) || $mult <= 0)) {
    println("error: special_multiplier must be > 0 when special_class=1");
    exit(5);
}
if ($flag === 0) {
    // When disabling, force multiplier to neutral 1.0 for clarity
    $mult = 1.0;
}

$cl = DB::table('tb_mas_classlist')->where('intID', $id)->first();
if (!$cl) {
    println("error: classlist not found: $id");
    exit(6);
}

$before = [
    'intID'              => (int)($cl->intID ?? 0),
    'special_class'      => isset($cl->special_class) ? (int)$cl->special_class : null,
    'special_multiplier' => isset($cl->special_multiplier) ? (float)$cl->special_multiplier : null,
];
jout('before', $before);

// Update
DB::table('tb_mas_classlist')
    ->where('intID', $id)
    ->update([
        'special_class'      => $flag,
        'special_multiplier' => $mult,
    ]);

$afterRow = DB::table('tb_mas_classlist')->where('intID', $id)->first();
$after = [
    'intID'              => (int)($afterRow->intID ?? 0),
    'special_class'      => isset($afterRow->special_class) ? (int)$afterRow->special_class : null,
    'special_multiplier' => isset($afterRow->special_multiplier) ? (float)$afterRow->special_multiplier : null,
];
jout('after', $after);

println("update_complete");
