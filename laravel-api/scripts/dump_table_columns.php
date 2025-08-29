<?php

// Usage: php laravel-api/scripts/dump_table_columns.php tb_mas_tuition_year_lab_fee tb_mas_tuition_year_misc

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function out($k, $v) {
    if (is_array($v) || is_object($v)) {
        echo $k . ': ' . json_encode($v, JSON_PRETTY_PRINT) . PHP_EOL;
    } else {
        echo $k . ': ' . (string)$v . PHP_EOL;
    }
}

$tables = array_slice($argv, 1);
if (empty($tables)) {
    $tables = ['tb_mas_tuition_year_lab_fee', 'tb_mas_tuition_year_misc'];
}

foreach ($tables as $t) {
    try {
        $cols = Schema::getColumnListing($t);
        out($t . '.columns', $cols);
    } catch (Throwable $e) {
        out($t . '.error', $e->getMessage());
    }
}
