<?php

// Bootstrap Laravel to use DB facade in a standalone script.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Fix invalid datetime values blocking migrations
    $affected = DB::table('tb_mas_sy')
        ->where('end_of_submission', '0000-00-00 00:00:00')
        ->update(['end_of_submission' => null]);

    echo "Fix applied. Rows updated: {$affected}\n";
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
