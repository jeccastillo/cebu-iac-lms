<?php

// Usage: php laravel-api/scripts/inspect_tuition_year_fees.php 5

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
    echo "Usage: php laravel-api/scripts/inspect_tuition_year_fees.php <tuitionYearID>\n";
    exit(1);
}

$ty = DB::table('tb_mas_tuition_year')->where('intID', $tyid)->first();
if (!$ty) {
    out('error', 'tuition_year_not_found');
    exit(2);
}

out('tuition_year', ['intID' => $ty->intID, 'year' => $ty->year ?? null]);

// LAB FEES
$lab = DB::table('tb_mas_tuition_year_lab_fee')
    ->where('tuitionYearID', $tyid)
    ->orderBy('name', 'asc')
    ->get()
    ->map(function ($r) {
        return [
            'id' => $r->intID ?? null,
            'name' => $r->name ?? '',
            'tuition_amount' => (float)($r->tuition_amount ?? 0),
            'tuition_amount_online' => (float)($r->tuition_amount_online ?? 0),
            'tuition_amount_hybrid' => (float)($r->tuition_amount_hybrid ?? 0),
            'tuition_amount_hyflex' => (float)($r->tuition_amount_hyflex ?? 0),
        ];
    })
    ->toArray();

out('lab_fee.count', count($lab));
out('lab_fee.rows_sample', array_slice($lab, 0, 20));

// MISC FEES (by type)
$types = ['regular','internship','new_student','late_enrollment','svf','isf','nstp','thesis'];
$miscSummary = [];
foreach ($types as $t) {
    $rows = DB::table('tb_mas_tuition_year_misc')
        ->where('tuitionYearID', $tyid)
        ->where('type', $t)
        ->orderBy('name', 'asc')
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->intID ?? null,
                'type' => $r->type ?? '',
                'name' => $r->name ?? '',
                'tuition_amount' => (float)($r->tuition_amount ?? 0),
                'tuition_amount_online' => (float)($r->tuition_amount_online ?? 0),
                'tuition_amount_hybrid' => (float)($r->tuition_amount_hybrid ?? 0),
                'tuition_amount_hyflex' => (float)($r->tuition_amount_hyflex ?? 0),
            ];
        })
        ->toArray();
    $miscSummary[$t] = [
        'count' => count($rows),
        'rows_sample' => array_slice($rows, 0, 20),
    ];
}
out('misc_fee.summary', $miscSummary);

// For convenience: list distinct lab classifications seen in subjects for current SY (optional: pass second arg syid)
$syid = isset($argv[2]) ? (int)$argv[2] : 0;
if ($syid > 0) {
    $labClasses = DB::table('tb_mas_classlist as cl')
        ->join('tb_mas_classlist_student as cls','cls.intClassListID','=','cl.intID')
        ->join('tb_mas_subjects as s','s.intID','=','cl.intSubjectID')
        ->select('s.strLabClassification as labClass')
        ->where('cl.strAcademicYear', $syid)
        ->whereNotNull('s.strLabClassification')
        ->distinct()
        ->pluck('labClass')
        ->filter()
        ->values()
        ->toArray();
    out('subjects.distinct_lab_classifications_for_sy', $labClasses);
}

echo "inspect_complete\n";
