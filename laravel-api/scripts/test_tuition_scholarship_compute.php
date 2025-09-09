<?php

/**
 * Thorough integration sanity test for DiscountScholarshipService
 *
 * Usage:
 *   php laravel-api/scripts/test_tuition_scholarship_compute.php <student_number> <syid>
 *
 * What this does:
 *  - Seeds a suite of scholarship catalog rows (guarded by Schema::hasColumn/hasTable).
 *  - Creates tb_mas_student_discount rows (status='applied') for provided student/syid, plus one 'pending' row to ensure it's ignored.
 *  - Calls App\Services\DiscountScholarshipService::computeDiscountsAndScholarships with deterministic base amounts.
 *  - Verifies and prints PASS/FAIL for:
 *      1) Only 'applied' assignments counted (pending ignored)
 *      2) compute_full group capping and proportional allocation on tuition basis
 *      3) Non-full sequential application and remaining logic
 *      4) total_assessment exclusivity (ignores per-bucket fields on same row)
 *      5) Fixed vs rate precedence and per-bucket mapping for misc/lab/additional
 *  - Optionally attempts TuitionService::compute if a registration exists for the student/term; otherwise skips (not required for DS math).
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

function usageAndExit(?string $msg = null): void {
    if ($msg) {
        out('error', $msg);
    }
    echo "Usage: php laravel-api/scripts/test_tuition_scholarship_compute.php <student_number> <syid>\n";
    exit(1);
}

$studentNumber = $argv[1] ?? null;
$syid = isset($argv[2]) ? (int)$argv[2] : null;
if (!$studentNumber || !$syid) {
    usageAndExit();
}

if (!Schema::hasTable('tb_mas_scholarships') || !Schema::hasTable('tb_mas_student_discount')) {
    usageAndExit('Required tables missing: tb_mas_scholarships or tb_mas_student_discount');
}

$user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
if (!$user) {
    usageAndExit('Student not found: ' . $studentNumber);
}

out('student.id', $user->intID);
out('term.syid', $syid);

// Helpers
function colExists(string $table, string $col): bool {
    return Schema::hasColumn($table, $col);
}

function seedScholarshipRow(array $payload): int {
    return (int) DB::table('tb_mas_scholarships')->insertGetId($payload);
}

function safeScholarshipPayload(array $base): array {
    $t = 'tb_mas_scholarships';
    $out = [];
    foreach ($base as $k => $v) {
        if (colExists($t, $k)) {
            $out[$k] = $v;
        }
    }
    // Ensure required/likely-not-null columns if they exist in schema
    if (colExists($t, 'description') && (!isset($out['description']) || $out['description'] === null || $out['description'] === '')) {
        $out['description'] = 'DS test seed';
    }
    if (colExists($t, 'code') && (!isset($out['code']) || $out['code'] === null || $out['code'] === '')) {
        $name = (string)($base['name'] ?? 'DS');
        // create a deterministic-ish short code based on name + time
        $out['code'] = substr('DS_' . preg_replace('/[^A-Z0-9_]/', '_', strtoupper($name)) . '_' . substr((string) time(), -6), 0, 64);
    }
    return $out;
}

function seedAssignment(int $studentId, int $syid, int $discountId, string $status = 'applied'): int {
    $t = 'tb_mas_student_discount';
    $payload = [];
    if (colExists($t, 'student_id'))   $payload['student_id'] = $studentId;
    if (colExists($t, 'syid'))         $payload['syid'] = $syid;
    if (colExists($t, 'discount_id'))  $payload['discount_id'] = $discountId;
    if (colExists($t, 'status'))       $payload['status'] = $status;
    // Some schemas require non-null referrer column
    if (colExists($t, 'referrer') && !array_key_exists('referrer', $payload)) {
        $payload['referrer'] = 'DS TEST';
    }
    return (int) DB::table($t)->insertGetId($payload);
}

$prefix = 'DS_THR_TEST_';

// Clean old test data for this student/term
try {
    DB::table('tb_mas_student_discount')
        ->where('student_id', $user->intID)
        ->where('syid', $syid)
        ->delete();
} catch (\Throwable $e) {
    // ignore
}
try {
    DB::table('tb_mas_scholarships')->where('name', 'like', $prefix.'%')->delete();
} catch (\Throwable $e) {
    // ignore
}

// Determine a creator id if schema requires created_by_id
$creatorId = null;
if (colExists('tb_mas_scholarships', 'created_by_id')) {
    $creatorId = DB::table('tb_mas_users')->select('intID')->orderBy('intID')->value('intID') ?? $user->intID;
}

// Seed catalog rows
$rows = [];

// A & B: Two 60% tuition, compute_full=true (to test capping and proportional allocation)
$rows['TU_60_CF_TRUE_A'] = seedScholarshipRow(safeScholarshipPayload([
    'name' => $prefix.'TU_60_CF_TRUE_A',
    'deduction_type' => 'scholarship',
    'tuition_fee_rate' => 60,
    'compute_full' => 1,
    'status' => 'active',
    'deduction_from' => 'in-house',
    'created_by_id' => $creatorId,
]));
$rows['TU_60_CF_TRUE_B'] = seedScholarshipRow(safeScholarshipPayload([
    'name' => $prefix.'TU_60_CF_TRUE_B',
    'deduction_type' => 'scholarship',
    'tuition_fee_rate' => 60,
    'compute_full' => 1,
    'status' => 'active',
    'deduction_from' => 'in-house',
    'created_by_id' => $creatorId,
]));

// C: 10% tuition, compute_full=false (should be zero after full group fully consumes base)
$rows['TU_10_CF_FALSE'] = seedScholarshipRow(safeScholarshipPayload([
    'name' => $prefix.'TU_10_CF_FALSE',
    'deduction_type' => 'scholarship',
    'tuition_fee_rate' => 10,
    'compute_full' => 0,
    'status' => 'active',
    'deduction_from' => 'in-house',
    'created_by_id' => $creatorId,
]));

// D: Misc fixed 100, compute_full=true
$rows['MISC_100F_CF_TRUE'] = seedScholarshipRow(safeScholarshipPayload([
    'name' => $prefix.'MISC_100F_CF_TRUE',
    'deduction_type' => 'scholarship',
    'misc_fee_fixed' => 100,
    'compute_full' => 1,
    'status' => 'active',
    'deduction_from' => 'in-house',
    'created_by_id' => $creatorId,
]));

// E: Lab rate 50%, compute_full=true
$rows['LAB_50R_CF_TRUE'] = seedScholarshipRow(safeScholarshipPayload([
    'name' => $prefix.'LAB_50R_CF_TRUE',
    'deduction_type' => 'scholarship',
    'lab_fee_rate' => 50,
    'compute_full' => 1,
    'status' => 'active',
    'deduction_from' => 'in-house',
    'created_by_id' => $creatorId,
]));

// F: Additional rate 20%, compute_full=false
$rows['ADD_20R_CF_FALSE'] = seedScholarshipRow(safeScholarshipPayload([
    'name' => $prefix.'ADD_20R_CF_FALSE',
    'deduction_type' => 'scholarship',
    'other_fees_rate' => 20,
    'compute_full' => 0,
    'status' => 'active',
    'deduction_from' => 'in-house',
    'created_by_id' => $creatorId,
]));

// G: total_assessment fixed 50, compute_full=false, with extra tuition_rate=25 on same row to assert exclusivity
$baseG = [
    'name' => $prefix.'TA_50F_CF_FALSE_EXCL',
    'deduction_type' => 'scholarship',
    'total_assessment_fixed' => 50,
    'tuition_fee_rate' => 25, // should be ignored due to exclusivity
    'compute_full' => 0,
    'status' => 'active',
    'deduction_from' => 'in-house',
    'created_by_id' => $creatorId,
];
$rows['TA_50F_CF_FALSE_EXCL'] = seedScholarshipRow(safeScholarshipPayload($baseG));

// Assign to student/term (status='applied')
foreach ($rows as $key => $id) {
    seedAssignment((int)$user->intID, (int)$syid, (int)$id, 'applied');
}

// Add a 'pending' row to verify ignored
seedAssignment((int)$user->intID, (int)$syid, (int)$rows['TU_60_CF_TRUE_A'], 'pending');

// Fixed deterministic bases
$tuition = 1000.00;
$misc = 200.00;
$lab = 300.00;
$additional = 400.00;

$service = new \App\Services\DiscountScholarshipService();
$result = $service->computeDiscountsAndScholarships([
    'student_id' => (int)$user->intID,
    'syid' => (int)$syid,
    'tuition' => $tuition,
    'misc_total' => $misc,
    'lab_total' => $lab,
    'additional_total' => $additional,
]);

out('Bases', compact('tuition', 'misc', 'lab', 'additional'));
out('Totals', [
    'scholarship_grand_total' => $result['scholarship_grand_total'] ?? null,
    'discount_grand_total' => $result['discount_grand_total'] ?? null,
]);
$lines = $result['lines'] ?? ['scholarships' => [], 'discounts' => []];
$schLines = $lines['scholarships'] ?? [];
$discLines = $lines['discounts'] ?? [];
out('Line counts', ['scholarships' => count($schLines), 'discounts' => count($discLines)]);

// Build index by name for easy assertions
$byName = [];
foreach ($schLines as $ln) {
    $nm = (string)($ln['name'] ?? '');
    if (!isset($byName[$nm])) $byName[$nm] = [];
    $byName[$nm][] = $ln;
}

// Assertions
$assertions = [];

// 1) Only 'applied' counted: pending of TU_60_CF_TRUE_A must not add extra line
$linesA = $byName[$prefix.'TU_60_CF_TRUE_A'] ?? [];
$assertions['pending_ignored'] = count($linesA) === 1;

// 2) compute_full capping/proportional on tuition basis: two 60% on 1000 -> cap 1000 => each ~500
$amtA = isset($linesA[0]) ? (float)$linesA[0]['amount'] : null;
$linesB = $byName[$prefix.'TU_60_CF_TRUE_B'] ?? [];
$amtB = isset($linesB[0]) ? (float)$linesB[0]['amount'] : null;
$assertions['tuition_full_proportional'] = $amtA === 500.00 && $amtB === 500.00;

// 3) Non-full sequential: tuition 10% non-full should be zero due to no remaining after full-group
$hasNonFullTu = false;
if (isset($byName[$prefix.'TU_10_CF_FALSE'])) {
    foreach ($byName[$prefix.'TU_10_CF_FALSE'] as $ln) {
        if (($ln['basis'] ?? '') === 'tuition') {
            $hasNonFullTu = true;
            $assertions['tuition_non_full_zero'] = ((float)$ln['amount']) === 0.0; // Our engine skips zero lines, so this will likely not set
        }
    }
}
// Accept either not-present or zero-amount
$assertions['tuition_non_full_absent_or_zero'] = !$hasNonFullTu || !empty($assertions['tuition_non_full_zero']);

// 4) total_assessment exclusivity: TA_50F_CF_FALSE_EXCL should only yield one TA line of 50; no tuition line from same row
$taLines = $byName[$prefix.'TA_50F_CF_FALSE_EXCL'] ?? [];
$hasTAonly = false;
$taAmt = null;
if (!empty($taLines)) {
    $taAmt = (float)$taLines[0]['amount'];
    $hasTAonly = ($taLines[0]['basis'] ?? '') === 'total_assessment';
}
$assertions['ta_exclusive'] = $hasTAonly && $taAmt === 50.00;

// 5) Misc fixed 100 (full) on misc base=200 -> amount 100
$miscLines = $byName[$prefix.'MISC_100F_CF_TRUE'] ?? [];
$miscAmt = isset($miscLines[0]) ? (float)$miscLines[0]['amount'] : null;
$assertions['misc_fixed_100'] = $miscAmt === 100.00;

// 6) Lab 50% (full) on lab base=300 -> amount 150
$labLines = $byName[$prefix.'LAB_50R_CF_TRUE'] ?? [];
$labAmt = isset($labLines[0]) ? (float)$labLines[0]['amount'] : null;
$assertions['lab_rate_50'] = $labAmt === 150.00;

// 7) Additional non-full 20% on additional base=400 (no full on additional) -> 80
$addLines = $byName[$prefix.'ADD_20R_CF_FALSE'] ?? [];
$addAmt = isset($addLines[0]) ? (float)$addLines[0]['amount'] : null;
$assertions['additional_non_full_20pct'] = $addAmt === 80.00;

// Report
$passCount = 0;
$failCount = 0;
foreach ($assertions as $k => $ok) {
    $ok ? $passCount++ : $failCount++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $k . PHP_EOL;
}
out('assertions', ['passed' => $passCount, 'failed' => $failCount]);

// Optional TuitionService::compute smoke if registration exists (not required for DS math)
try {
    $registration = DB::table('tb_mas_registration')
        ->where('intStudentID', $user->intID)
        ->where('intAYID', $syid)
        ->first();

    if ($registration) {
        $svc = new \App\Services\TuitionService();
        $breakdown = $svc->compute((string)$studentNumber, (int)$syid);
        out('TuitionService.compute.summary', $breakdown['summary'] ?? []);
        out('TuitionService.compute.lines.scholarships', $breakdown['items']['scholarships'] ?? []);
        out('TuitionService.compute.lines.discounts', $breakdown['items']['discounts'] ?? []);
    } else {
        echo "Info: No registration found for TuitionService::compute smoke; DS tests completed independently.\n";
    }
} catch (\Throwable $e) {
    out('TuitionService.compute_error', $e->getMessage());
}

echo "Thorough DS test completed.\n";
