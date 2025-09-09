<?php
// Integration test for scholarship mutual-exclusion enforcement
// Usage: php scripts/test_scholarship_me.php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ScholarshipService;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

/**
 * Helper to print a section header.
 */
function section(string $title): void {
    echo "\n==== {$title} ====\n";
}

/**
 * Helper to pretty print arrays/objects.
 */
function dump_line($label, $value): void {
    echo $label . ': ' . json_encode($value, JSON_PRETTY_PRINT) . "\n";
}

section('Setup: seed sample scholarships and mutual exclusions');

// Clean old test data (best-effort)
DB::table('tb_mas_scholarships')->whereIn('name', ['ME Test A','ME Test B','ME Test C'])->delete();
DB::table('tb_mas_scholarship_me')->whereIn('discount_id_a', [0])->delete(); // no-op guard
// Ensure a test student exists (or create one)
$student = DB::table('tb_mas_users')
    ->select('intID','strStudentNumber','strFirstname','strLastname')
    ->orderBy('intID')
    ->first();
if (!$student) {
    echo "ERROR: No students found in tb_mas_users. Please seed a student and rerun.\n";
    exit(1);
}
$studentId = (int)($student->intID ?? 0);
if ($studentId <= 0) {
    echo "ERROR: Invalid student id.\n";
    exit(1);
}

// Use an existing term id from tb_mas_sy to satisfy FK constraints
$sy = DB::table('tb_mas_sy')->select('intID')->orderBy('intID')->first();
if (!$sy) {
    echo "ERROR: No terms found in tb_mas_sy. Please seed a term (school year) and rerun.\n";
    exit(1);
}
$syid = (int) ($sy->intID ?? 0);
if ($syid <= 0) {
    echo "ERROR: Invalid term id.\n";
    exit(1);
}

// Clean any prior assignments for this specific student and term
DB::table('tb_mas_student_discount')->where('student_id', $studentId)->where('syid', $syid)->delete();

// Insert catalog scholarships/discounts A, B, C
$insertItem = function (string $code, string $name, string $deductionType = 'scholarship'): int {
    // Build payload only with columns that exist in current schema
    $payload = [];
    if (Schema::hasColumn('tb_mas_scholarships', 'name')) {
        $payload['name'] = $name;
    }
    if (Schema::hasColumn('tb_mas_scholarships', 'deduction_type')) {
        $payload['deduction_type'] = $deductionType;
    }
    if (Schema::hasColumn('tb_mas_scholarships', 'deduction_from')) {
        $payload['deduction_from'] = 'in-house';
    }
    if (Schema::hasColumn('tb_mas_scholarships', 'status')) {
        $payload['status'] = 'active';
    }
    if (Schema::hasColumn('tb_mas_scholarships', 'description')) {
        $payload['description'] = 'ME test seed';
    }
    // created_by_id (if required by schema, supply a valid id)
    if (Schema::hasColumn('tb_mas_scholarships', 'created_by_id')) {
        $creator = DB::table('tb_mas_users')->select('intID')->orderBy('intID')->first();
        $payload['created_by_id'] = $creator && isset($creator->intID) ? (int) $creator->intID : 1;
    }
    return (int) DB::table('tb_mas_scholarships')->insertGetId($payload);
};

$aId = $insertItem('ME_TEST_A', 'ME Test A', 'scholarship'); // A
$bId = $insertItem('ME_TEST_B', 'ME Test B', 'discount');    // B (intentionally make this a discount to cover cross-type)
$cId = $insertItem('ME_TEST_C', 'ME Test C', 'scholarship'); // C (non-conflicting)

// Insert ME pair (A,B) in canonical order (min, max)
$aa = min($aId, $bId);
$bb = max($aId, $bId);

// Remove any existing pair then insert
DB::table('tb_mas_scholarship_me')
    ->where(function ($q) use ($aa, $bb) {
        $q->where('discount_id_a', $aa)->where('discount_id_b', $bb);
    })
    ->delete();

DB::table('tb_mas_scholarship_me')->insert([
    'discount_id_a' => $aa,
    'discount_id_b' => $bb,
    'status'        => 'active'
]);

dump_line('Seeded A,B,C IDs', ['A' => $aId, 'B' => $bId, 'C' => $cId]);
dump_line('Student ID', $studentId);
dump_line('SYID', $syid);

section('Step 1: Tag B (should succeed)');
/** @var ScholarshipService $svc */
$svc = app(ScholarshipService::class);

try {
    $resB = $svc->assignmentUpsert([
        'student_id'  => $studentId,
        'syid'        => $syid,
        'discount_id' => $bId
    ]);
    dump_line('Result B', $resB);
} catch (\Throwable $e) {
    echo "ERROR tagging B: " . $e->getMessage() . "\n";
    exit(1);
}

section('Step 2: Tag A (should fail with mutual-exclusion)');
try {
    $resA = $svc->assignmentUpsert([
        'student_id'  => $studentId,
        'syid'        => $syid,
        'discount_id' => $aId
    ]);
    echo "UNEXPECTED: tagging A succeeded. Result: " . json_encode($resA) . "\n";
    exit(2);
} catch (\InvalidArgumentException $e) {
    // Expect this path
    echo "Expected conflict message: " . $e->getMessage() . "\n";
} catch (\Throwable $e) {
    echo "ERROR (unexpected) tagging A: " . $e->getMessage() . "\n";
    exit(3);
}

section('Step 3: Tag C (should succeed, no conflict)');
try {
    $resC = $svc->assignmentUpsert([
        'student_id'  => $studentId,
        'syid'        => $syid,
        'discount_id' => $cId
    ]);
    dump_line('Result C', $resC);
    echo "\nAll tests completed.\n";
} catch (\Throwable $e) {
    echo "ERROR tagging C: " . $e->getMessage() . "\n";
    exit(4);
}
