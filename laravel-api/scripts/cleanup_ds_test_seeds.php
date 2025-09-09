<?php

/**
 * Cleanup DS test seed data created by test_tuition_scholarship_compute.php.
 *
 * Usage:
 *   php laravel-api/scripts/cleanup_ds_test_seeds.php <student_number> <syid>
 *
 * This removes:
 *  - tb_mas_student_discount rows for the given student/syid whose discount_id references
 *    a scholarship with name LIKE 'DS_THR_TEST_%'
 *  - the referenced tb_mas_scholarships rows with name LIKE 'DS_THR_TEST_%'
 *
 * Guards with Schema::hasTable/hasColumn to avoid crashes on schema variance.
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

function usage(): void {
    echo "Usage: php laravel-api/scripts/cleanup_ds_test_seeds.php <student_number> <syid>\n";
}

$studentNumber = $argv[1] ?? null;
$syid = isset($argv[2]) ? (int)$argv[2] : null;

if (!$studentNumber || !$syid) {
    usage();
    exit(1);
}

if (!Schema::hasTable('tb_mas_scholarships') || !Schema::hasTable('tb_mas_student_discount') || !Schema::hasTable('tb_mas_users')) {
    out('error', 'Required tables missing');
    exit(2);
}

$user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
if (!$user) {
    out('error', 'Student not found: ' . $studentNumber);
    exit(3);
}

out('student.id', $user->intID);
out('term.syid', $syid);

// Collect DS test scholarships
$scholarshipIds = DB::table('tb_mas_scholarships')
    ->where('name', 'like', 'DS_THR_TEST_%')
    ->pluck('intID')
    ->map(fn($v) => (int)$v)
    ->toArray();

out('found_scholarship_ids_count', count($scholarshipIds));

$deletedAssignments = 0;
$deletedScholarships = 0;

// Delete student_discount rows referencing these scholarships for this student/term
if (!empty($scholarshipIds)) {
    try {
        $q = DB::table('tb_mas_student_discount')
            ->where('student_id', $user->intID)
            ->where('syid', $syid)
            ->whereIn('discount_id', $scholarshipIds);
        $deletedAssignments = $q->delete();
    } catch (\Throwable $e) {
        out('warning', 'Failed deleting assignments: ' . $e->getMessage());
    }

    try {
        $deletedScholarships = DB::table('tb_mas_scholarships')
            ->whereIn('intID', $scholarshipIds)
            ->delete();
    } catch (\Throwable $e) {
        out('warning', 'Failed deleting scholarships: ' . $e->getMessage());
    }
}

out('deleted_assignments', $deletedAssignments);
out('deleted_scholarships', $deletedScholarships);

echo "cleanup_complete\n";
