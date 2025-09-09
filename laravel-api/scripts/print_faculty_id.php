<?php
declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$fid = null;

// Try tb_mas_faculty
try {
    $row = DB::table('tb_mas_faculty')->select('intID')->orderBy('intID')->first();
    if ($row && isset($row->intID)) {
        $fid = (int) $row->intID;
    }
} catch (\Throwable $e) {}

// Fallback: any user id if faculty table unavailable
if ($fid === null) {
    $u = DB::table('tb_mas_users')->select('intID')->orderBy('intID')->first();
    if ($u && isset($u->intID)) {
        $fid = (int) $u->intID;
    }
}

if (!$fid) {
    fwrite(STDERR, "No faculty or user id found.\n");
    exit(1);
}

echo (string) $fid, "\n";
