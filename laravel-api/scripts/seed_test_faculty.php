<?php
/**
 * Seed a test Faculty with required roles to pass role middleware for API testing.
 *
 * - Ensures tb_mas_roles contains 'finance' and 'cashier_admin'
 * - Ensures tb_mas_faculty has a row with intID=1
 * - Assigns the roles to faculty intID=1 via tb_mas_faculty_roles
 *
 * Usage:
 *   php laravel-api/scripts/seed_test_faculty.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function now_str() { return date('Y-m-d H:i:s'); }

echo "=== Seed Test Faculty and Roles ===\n";

$requiredTables = ['tb_mas_roles', 'tb_mas_faculty', 'tb_mas_faculty_roles'];
$missing = [];
foreach ($requiredTables as $t) {
    if (!Schema::hasTable($t)) $missing[] = $t;
}
if (!empty($missing)) {
    echo "ERROR: Missing required tables: " . implode(', ', $missing) . "\n";
    echo "Run migrations first.\n";
    exit(1);
}

// Ensure roles
$roleCodes = ['finance', 'cashier_admin'];
$roleIds = [];
foreach ($roleCodes as $code) {
    $exists = DB::table('tb_mas_roles')->where('strCode', $code)->exists();
    if (!$exists) {
        DB::table('tb_mas_roles')->insert([
            'strCode' => $code,
            'strName' => ucfirst(str_replace('_', ' ', $code)),
            'strDescription' => 'Seeded for testing',
            'intActive' => 1,
        ]);
        echo "- Inserted role '{$code}'\n";
    }
    $rid = DB::table('tb_mas_roles')->where('strCode', $code)->value('intRoleID');
    if ($rid) $roleIds[$code] = (int) $rid;
}

// Ensure faculty row id=1
$fac = DB::table('tb_mas_faculty')->where('intID', 1)->first();
if (!$fac) {
    DB::table('tb_mas_faculty')->insert([
        'intID'         => 1,
        'strFirstname'  => 'Test',
        'strMiddlename' => '',
        'strLastname'   => 'Cashier',
        'campus_id'     => 1,
        'teaching'      => 0,
    ]);
    echo "- Inserted faculty intID=1\n";
} else {
    // Ensure campus_id exists for some flows
    $upd = [];
    if (!isset($fac->campus_id)) {
        $upd['campus_id'] = 1;
    }
    if (!empty($upd)) {
        DB::table('tb_mas_faculty')->where('intID', 1)->update($upd);
        echo "- Updated faculty intID=1 baseline fields\n";
    }
}

// Assign roles to faculty id=1
foreach ($roleIds as $code => $rid) {
    $exists = DB::table('tb_mas_faculty_roles')
        ->where('intFacultyID', 1)
        ->where('intRoleID', $rid)
        ->exists();
    if (!$exists) {
        DB::table('tb_mas_faculty_roles')->insert([
            'intFacultyID' => 1,
            'intRoleID'    => $rid,
        ]);
        echo "- Assigned role '{$code}' (id={$rid}) to faculty 1\n";
    }
}

echo "=== Done ===\n";
