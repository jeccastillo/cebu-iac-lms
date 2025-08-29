<?php

// Usage: php laravel-api/scripts/seed_tuition_year_fees.php 5
// Seeds lab and misc fee amounts for a given tuitionYearID based on approved values.

use Illuminate\Support\Facades\DB;

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

$tyid = isset($argv[1]) ? (int)$argv[1] : 0;
if ($tyid <= 0) {
    echo "Usage: php laravel-api/scripts/seed_tuition_year_fees.php <tuitionYearID>\n";
    exit(1);
}

// Approved values (apply equally to regular/online/hybrid/hyflex)
$labFees = [
    'Photography Room' => 1200,
    'Multimedia'       => 1500,
    'Computer'         => 1000,
    'Drawing'          => 800,
    'Cintiq'           => 1800,
    'Light Box'        => 700,
];

$miscRegular = [
    'Registration'         => 1000,
    'Library'              => 500,
    'Internet fee'         => 800,
    'Guidance'             => 300,
    'Health Services'      => 300,
    'Student Activity'     => 400,
    'Student Devt Fee'     => 350,
    'Student Organization' => 200,
    'Student Publication'  => 250,
    'Audio Visual'         => 300,
    'Energy Fee'           => 600,
    'Learning Materials'   => 800,
    'Athletic Fee'         => 300,
    'Online Learning Fee'  => 600,
    'ID Validation'        => 100,
    'Insurance'            => 200,
];

// DB has a trailing space in "Learning Materials ", include alias to match
$miscRegularAliases = [
    'Learning Materials ' => 'Learning Materials',
];

$miscNewStudent = [
    'Matriculation' => 500,
    'Orientation'   => 300,
    'ID'            => 200,
    'Handbook'      => 250,
];

$miscLate = [
    'Late Enrollment Fee' => 500,
];

$miscNstp = [
    'NSTP Fee' => 1000,
];

$miscThesis = [
    'Thesis' => 1500,
];

$updated = [
    'lab' => 0,
    'misc_regular' => 0,
    'misc_new_student' => 0,
    'misc_late' => 0,
    'misc_nstp' => 0,
    'misc_thesis' => 0,
];

function norm($s) {
    return strtoupper(trim((string)$s));
}

// Seed lab fees
$labRows = DB::table('tb_mas_tuition_year_lab_fee')
    ->where('tuitionYearID', $tyid)
    ->get();

foreach ($labRows as $row) {
    $nameNorm = norm($row->name ?? '');
    foreach ($labFees as $targetName => $amount) {
        if ($nameNorm === norm($targetName)) {
            DB::table('tb_mas_tuition_year_lab_fee')
                ->where('intID', $row->intID)
                ->update([
                    'tuition_amount'         => $amount,
                    'tuition_amount_online'  => $amount,
                    'tuition_amount_hybrid'  => $amount,
                    'tuition_amount_hyflex'  => $amount,
                ]);
            $updated['lab']++;
            break;
        }
    }
}

// Helper to seed misc by type and map
function seed_misc($tuitionYearID, $type, $map, &$counter, $aliases = []) {
    $rows = DB::table('tb_mas_tuition_year_misc')
        ->where('tuitionYearID', $tuitionYearID)
        ->where('type', $type)
        ->get();

    // Build normalized lookup with aliases support
    $target = [];
    foreach ($map as $k => $v) {
        $target[norm($k)] = $v;
    }
    foreach ($aliases as $alias => $canon) {
        if (isset($map[$canon])) {
            $target[norm($alias)] = $map[$canon];
        }
    }

    foreach ($rows as $row) {
        $nameNorm = norm($row->name ?? '');
        if (array_key_exists($nameNorm, $target)) {
            DB::table('tb_mas_tuition_year_misc')
                ->where('intID', $row->intID)
                ->update([
                    'tuition_amount'         => $target[$nameNorm],
                    'tuition_amount_online'  => $target[$nameNorm],
                    'tuition_amount_hybrid'  => $target[$nameNorm],
                    'tuition_amount_hyflex'  => $target[$nameNorm],
                ]);
            $counter++;
        }
    }
}

// Seed misc regular
seed_misc($tyid, 'regular', $miscRegular, $updated['misc_regular'], $miscRegularAliases);
// Seed misc new_student
seed_misc($tyid, 'new_student', $miscNewStudent, $updated['misc_new_student']);
// Seed late_enrollment
seed_misc($tyid, 'late_enrollment', $miscLate, $updated['misc_late']);
// Seed nstp
seed_misc($tyid, 'nstp', $miscNstp, $updated['misc_nstp']);
// Seed thesis
seed_misc($tyid, 'thesis', $miscThesis, $updated['misc_thesis']);

out('updated_counts', $updated);
echo "seed_complete\n";
