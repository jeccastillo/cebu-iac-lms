<?php
declare(strict_types=1);

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Helper: insert row into table with only existing columns from $data.
 */
function insert_with_schema_guard(string $table, array $data): void {
    $filtered = [];
    foreach ($data as $k => $v) {
        if (Schema::hasColumn($table, $k)) {
            $filtered[$k] = $v;
        }
    }
    if (!empty($filtered)) {
        DB::table($table)->insert($filtered);
    }
}

/**
 * Ensure scholarship by name exists, create minimally if missing.
 * Returns intID.
 */
function ensure_scholarship(string $name, string $deductionType = 'scholarship'): int {
    $row = DB::table('tb_mas_scholarships')->where('name', $name)->first();
    if (!$row) {
        $payload = [
            'name'            => $name,
            'code'            => null,
            'deduction_type'  => $deductionType, // 'scholarship' | 'discount'
            'deduction_from'  => 'in-house',
            'status'          => 'active',
            'description'     => 'ME test seed',
            'created_by_id'   => 1,
            // Optional schema fields (guarded by insert_with_schema_guard)
            'tuition_fee_rate'       => null,
            'tuition_fee_fixed'      => null,
            'basic_fee_rate'         => null,
            'basic_fee_fixed'        => null,
            'misc_fee_rate'          => null,
            'misc_fee_fixed'         => null,
            'lab_fee_rate'           => null,
            'lab_fee_fixed'          => null,
            'penalty_fee_rate'       => null,
            'penalty_fee_fixed'      => null,
            'other_fees_rate'        => null,
            'other_fees_fixed'       => null,
            'total_assessment_rate'  => null,
            'total_assessment_fixed' => null,
        ];
        insert_with_schema_guard('tb_mas_scholarships', $payload);
        $row = DB::table('tb_mas_scholarships')->where('name', $name)->first();
    }
    return (int) ($row->intID ?? 0);
}

/**
 * Ensure ME pair exists (active) for two discount ids (normalized a < b).
 */
function ensure_me_pair_active(int $id1, int $id2): void {
    if ($id1 <= 0 || $id2 <= 0 || $id1 === $id2) return;
    $a = min($id1, $id2);
    $b = max($id1, $id2);
    $table = 'tb_mas_scholarship_me';
    if (!Schema::hasTable($table)) return;

    $exists = DB::table($table)->where([
        'discount_id_a' => $a,
        'discount_id_b' => $b,
    ])->exists();

    if (!$exists) {
        insert_with_schema_guard($table, [
            'discount_id_a' => $a,
            'discount_id_b' => $b,
            'status'        => 'active',
        ]);
    } else {
        // Ensure status is active
        DB::table($table)->where([
            'discount_id_a' => $a,
            'discount_id_b' => $b,
        ])->update(['status' => 'active']);
    }
}

/**
 * Resolve any existing SY id and Student id (minimal).
 */
function resolve_sy_and_student(): array {
    // Resolve SY
    $sy = DB::table('tb_mas_sy')->orderBy('intID')->value('intID');
    if (!$sy) {
        // fallback to 1 if table empty
        $sy = 1;
    } else {
        $sy = (int) $sy;
    }

    // Resolve student (users table)
    $sid = DB::table('tb_mas_users')->orderBy('intID')->value('intID');
    if (!$sid) {
        $sid = 1;
    } else {
        $sid = (int) $sid;
    }

    return [$sy, $sid];
}

/**
 * MAIN
 */
[$syid, $studentId] = resolve_sy_and_student();

// Ensure 3 test scholarships (mix of types)
$aid = ensure_scholarship('ME Test A', 'scholarship');
$bid = ensure_scholarship('ME Test B', 'discount');
$cid = ensure_scholarship('ME Test C', 'scholarship');

// Ensure mutual-exclusion pair (A,B) is active
ensure_me_pair_active($aid, $bid);

// Clean any prior assignments for clarity of tests
if (Schema::hasTable('tb_mas_student_discount')) {
    DB::table('tb_mas_student_discount')
        ->where('student_id', $studentId)
        ->where('syid', $syid)
        ->delete();
}

// Output JSON for shell consumption
header('Content-Type: application/json');
echo json_encode([
    'syid'        => $syid,
    'student_id'  => $studentId,
    'a_id'        => $aid,
    'b_id'        => $bid,
    'c_id'        => $cid,
], JSON_UNESCAPED_SLASHES), PHP_EOL;
