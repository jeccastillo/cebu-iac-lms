<?php
// Integration test for max_stacks behavior on tb_mas_scholarships
// Usage: php scripts/test_max_stacks.php
declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ScholarshipService;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function out($label, $data) {
    echo $label . ': ' . json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

function resolve_sy_and_student(): array {
    $sy = DB::table('tb_mas_sy')->orderBy('intID')->value('intID');
    $syid = $sy ? (int) $sy : 1;

    $sid = DB::table('tb_mas_users')->orderBy('intID')->value('intID');
    $studentId = $sid ? (int) $sid : 1;

    return [$syid, $studentId];
}

/**
 * Ensure scholarship exists with desired max_stacks; create or update as needed.
 */
function ensure_scholarship_with_max(string $name, string $deductionType, int $maxStacks): int {
    $row = DB::table('tb_mas_scholarships')->where('name', $name)->first();
    $creator = DB::table('tb_mas_users')->select('intID')->orderBy('intID')->first();
    $createdBy = $creator && isset($creator->intID) ? (int) $creator->intID : 1;

    if (!$row) {
        $payload = [];
        if (Schema::hasColumn('tb_mas_scholarships', 'name'))            $payload['name'] = $name;
        if (Schema::hasColumn('tb_mas_scholarships', 'code'))            $payload['code'] = null;
        if (Schema::hasColumn('tb_mas_scholarships', 'deduction_type'))  $payload['deduction_type'] = $deductionType;
        if (Schema::hasColumn('tb_mas_scholarships', 'deduction_from'))  $payload['deduction_from'] = 'in-house';
        if (Schema::hasColumn('tb_mas_scholarships', 'status'))          $payload['status'] = 'active';
        if (Schema::hasColumn('tb_mas_scholarships', 'description'))     $payload['description'] = 'Max-stacks test seed';
        if (Schema::hasColumn('tb_mas_scholarships', 'created_by_id'))   $payload['created_by_id'] = $createdBy;
        if (Schema::hasColumn('tb_mas_scholarships', 'max_stacks'))      $payload['max_stacks'] = $maxStacks;

        $id = (int) DB::table('tb_mas_scholarships')->insertGetId($payload);
        return $id;
    } else {
        // Update max_stacks if column exists
        if (Schema::hasColumn('tb_mas_scholarships', 'max_stacks')) {
            DB::table('tb_mas_scholarships')->where('intID', $row->intID)->update([
                'max_stacks' => $maxStacks
            ]);
        }
        // Ensure active status for testing
        if (Schema::hasColumn('tb_mas_scholarships', 'status')) {
            DB::table('tb_mas_scholarships')->where('intID', $row->intID)->update([
                'status' => 'active'
            ]);
        }
        return (int) $row->intID;
    }
}

[$syid, $studentId] = resolve_sy_and_student();
out('Resolved', ['syid' => $syid, 'student_id' => $studentId]);

/** @var ScholarshipService $svc */
$svc = app(ScholarshipService::class);

// Case A: max_stacks = 2, allow two assignments then block third
$nameA = 'Stack Test A (max2)';
$idA = ensure_scholarship_with_max($nameA, 'scholarship', 2);

// Clean prior assignments for this test
DB::table('tb_mas_student_discount')
    ->where('student_id', $studentId)
    ->where('syid', $syid)
    ->where('discount_id', $idA)
    ->delete();

$resultsA = ['first' => null, 'second' => null, 'third' => null];
$errorsA  = ['first' => null, 'second' => null, 'third' => null];

try {
    $resultsA['first'] = $svc->assignmentUpsert([
        'student_id'  => $studentId,
        'syid'        => $syid,
        'discount_id' => $idA
    ]);
} catch (\Throwable $e) { $errorsA['first'] = $e->getMessage(); }

try {
    $resultsA['second'] = $svc->assignmentUpsert([
        'student_id'  => $studentId,
        'syid'        => $syid,
        'discount_id' => $idA
    ]);
} catch (\Throwable $e) { $errorsA['second'] = $e->getMessage(); }

try {
    $resultsA['third'] = $svc->assignmentUpsert([
        'student_id'  => $studentId,
        'syid'        => $syid,
        'discount_id' => $idA
    ]);
} catch (\Throwable $e) { $errorsA['third'] = $e->getMessage(); }

out('Case A (max=2) results', $resultsA);
out('Case A (max=2) errors', $errorsA);

// Case B: max_stacks = 1, allow one assignment then block second
$nameB = 'Stack Test B (max1)';
$idB = ensure_scholarship_with_max($nameB, 'scholarship', 1);

// Clean prior assignments for this test
DB::table('tb_mas_student_discount')
    ->where('student_id', $studentId)
    ->where('syid', $syid)
    ->where('discount_id', $idB)
    ->delete();

$resultsB = ['first' => null, 'second' => null];
$errorsB  = ['first' => null, 'second' => null];

try {
    $resultsB['first'] = $svc->assignmentUpsert([
        'student_id'  => $studentId,
        'syid'        => $syid,
        'discount_id' => $idB
    ]);
} catch (\Throwable $e) { $errorsB['first'] = $e->getMessage(); }

try {
    $resultsB['second'] = $svc->assignmentUpsert([
        'student_id'  => $studentId,
        'syid'        => $syid,
        'discount_id' => $idB
    ]);
} catch (\Throwable $e) { $errorsB['second'] = $e->getMessage(); }

out('Case B (max=1) results', $resultsB);
out('Case B (max=1) errors', $errorsB);

// Summary checks
$summary = [
    'A_first_success'  => $resultsA['first'] !== null && empty($errorsA['first']),
    'A_second_success' => $resultsA['second'] !== null && empty($errorsA['second']),
    'A_third_blocked'  => $resultsA['third'] === null && is_string($errorsA['third']) && strpos($errorsA['third'], 'only be assigned 2') !== false,
    'B_first_success'  => $resultsB['first'] !== null && empty($errorsB['first']),
    'B_second_blocked' => $resultsB['second'] === null && is_string($errorsB['second']) && (strpos($errorsB['second'], 'only be assigned 1') !== false || strpos($errorsB['second'], 'only be assigned') !== false),
];

out('Summary', $summary);

echo "DONE\n";
