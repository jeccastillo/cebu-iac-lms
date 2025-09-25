<?php

// Usage:
//   php laravel-api/scripts/test_special_class_multiplier.php <student_number> <syid> <classlist_id> <multiplier>
// Example:
//   php laravel-api/scripts/test_special_class_multiplier.php C2023-01-082 28 12345 1.25

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function println($msg) {
    echo $msg . PHP_EOL;
}
function jout($k, $v) {
    echo $k . ': ' . json_encode($v, JSON_PRETTY_PRINT) . PHP_EOL;
}
function getTuitionCompute(string $studentNumber, int $syid): array {
    $svc = new \App\Services\TuitionService();
    return $svc->compute($studentNumber, $syid, null, null);
}

$studentNumber = $argv[1] ?? null;
$syid          = isset($argv[2]) ? (int)$argv[2] : null;
$classlistId   = isset($argv[3]) ? (int)$argv[3] : null;
$multiplier    = isset($argv[4]) ? (float)$argv[4] : null;

if (!$studentNumber || !$syid || !$classlistId || !$multiplier) {
    println("Usage: php laravel-api/scripts/test_special_class_multiplier.php <student_number> <syid> <classlist_id> <multiplier>");
    exit(1);
}

// Validate student and classlist
$user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
if (!$user) {
    println("error: student_not_found");
    exit(2);
}
$cl = DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();
if (!$cl) {
    println("error: classlist_not_found");
    exit(3);
}
println("student_id=" . (int)$user->intID . " classlist_id=" . (int)$classlistId . " syid=" . (int)$syid);

// Capture original flag/multiplier to restore later
$orig = [
    'special_class'     => isset($cl->special_class) ? (int)$cl->special_class : 0,
    'special_multiplier'=> isset($cl->special_multiplier) ? (float)$cl->special_multiplier : null,
];

// Baseline compute
println("Computing baseline tuition ...");
$base = getTuitionCompute($studentNumber, (int)$syid);

// Resolve the subject_id associated to the classlist (for diff focus)
$subjectId = (int) (DB::table('tb_mas_classlist')->where('intID', $classlistId)->value('intSubjectID') ?? 0);

// Extract helper for tuition items keyed by subject_id (when present)
$extract = function (array $compute) {
    $items = $compute['items']['tuition'] ?? [];
    $map = [];
    foreach ($items as $it) {
        $sid = isset($it['subject_id']) ? (int)$it['subject_id'] : null;
        if ($sid !== null) {
            $map[$sid] = [
                'rate'   => isset($it['rate']) ? (float)$it['rate'] : null,
                'amount' => isset($it['amount']) ? (float)$it['amount'] : null,
                'units'  => isset($it['units']) ? $it['units'] : null,
                'code'   => $it['code'] ?? null,
            ];
        }
    }
    return $map;
};

$baseItemsBySubject = $extract($base);
$baseSummary = $base['summary'] ?? [];

// Apply special_class multiplier
println("Applying special_class=1, special_multiplier=" . $multiplier . " to classlist_id=" . (int)$classlistId . " ...");
DB::table('tb_mas_classlist')
    ->where('intID', $classlistId)
    ->update([
        'special_class'      => 1,
        'special_multiplier' => $multiplier,
    ]);

// Recompute after change
println("Computing tuition after applying multiplier ...");
$after = getTuitionCompute($studentNumber, (int)$syid);
$afterItemsBySubject = $extract($after);
$afterSummary = $after['summary'] ?? [];

// Prepare deltas
$deltas = [
    'subject_id' => $subjectId,
    'before' => [
        'item' => $subjectId && isset($baseItemsBySubject[$subjectId]) ? $baseItemsBySubject[$subjectId] : null,
        'summary' => [
            'tuition'  => isset($baseSummary['tuition']) ? (float)$baseSummary['tuition'] : null,
            'lab_total'=> isset($baseSummary['lab_total']) ? (float)$baseSummary['lab_total'] : null,
            'total_due'=> isset($baseSummary['total_due']) ? (float)$baseSummary['total_due'] : null,
        ],
    ],
    'after' => [
        'item' => $subjectId && isset($afterItemsBySubject[$subjectId]) ? $afterItemsBySubject[$subjectId] : null,
        'summary' => [
            'tuition'  => isset($afterSummary['tuition']) ? (float)$afterSummary['tuition'] : null,
            'lab_total'=> isset($afterSummary['lab_total']) ? (float)$afterSummary['lab_total'] : null,
            'total_due'=> isset($afterSummary['total_due']) ? (float)$afterSummary['total_due'] : null,
        ],
    ],
];

// Output result deltas
jout('delta', $deltas);

// Always restore original state
println("Restoring original classlist flags ...");
DB::table('tb_mas_classlist')
    ->where('intID', $classlistId)
    ->update([
        'special_class'      => $orig['special_class'],
        'special_multiplier' => $orig['special_multiplier'],
    ]);

println("done");
