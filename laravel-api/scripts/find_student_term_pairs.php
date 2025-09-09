<?php

/**
 * Utility: Find candidate (student_number, syid) pairs for testing.
 *
 * Usage:
 *   php laravel-api/scripts/find_student_term_pairs.php
 *
 * Notes:
 * - Lists up to 20 pairs where a registration exists. Also includes whether a tuition_year is set.
 * - Safe to run read-only. Requires existing legacy tables tb_mas_users and tb_mas_registration.
 */

use Illuminate\Support\Facades\DB;
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

if (!Schema::hasTable('tb_mas_users') || !Schema::hasTable('tb_mas_registration')) {
    out('error', 'Required tables missing: tb_mas_users and/or tb_mas_registration');
    exit(1);
}

$q = DB::table('tb_mas_users as u')
    ->join('tb_mas_registration as r', 'r.intStudentID', '=', 'u.intID')
    ->select(
        'u.intID as student_id',
        'u.strStudentNumber as student_number',
        'r.intAYID as syid',
        'r.tuition_year as tuition_year'
    )
    ->orderBy('u.intID', 'asc')
    ->orderBy('r.intAYID', 'asc')
    ->limit(20);

$rows = $q->get()->map(function ($r) {
    return [
        'student_id'     => (int) ($r->student_id ?? 0),
        'student_number' => (string) ($r->student_number ?? ''),
        'syid'           => (int) ($r->syid ?? 0),
        'has_tuition_year' => (bool) !empty($r->tuition_year),
        'tuition_year_id'  => isset($r->tuition_year) ? (int)$r->tuition_year : null,
    ];
})->toArray();

if (empty($rows)) {
    out('info', 'No registration pairs found.');
    exit(0);
}

echo "student_number syid has_tuition_year tuition_year_id\n";
foreach ($rows as $r) {
    echo $r['student_number'] . ' ' . $r['syid'] . ' ' . ($r['has_tuition_year'] ? '1' : '0') . ' ' . ($r['tuition_year_id'] ?? 'null') . PHP_EOL;
}
