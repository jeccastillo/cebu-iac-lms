<?php
// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Simple CLI arg parser
function parse_args(array $argv): array {
    $out = [
        'username' => null,
        'roles'    => [],
        'faculty_id' => null,
    ];
    // Accept: --username=xxx, --role=code (repeatable), --roles=code1,code2, --faculty-id=123
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--username=')) {
            $out['username'] = trim(substr($arg, strlen('--username=')));
        } elseif (str_starts_with($arg, '--role=')) {
            $val = trim(substr($arg, strlen('--role=')));
            if ($val !== '') $out['roles'][] = $val;
        } elseif (str_starts_with($arg, '--roles=')) {
            $val = trim(substr($arg, strlen('--roles=')));
            if ($val !== '') {
                foreach (explode(',', $val) as $r) {
                    $r = trim($r);
                    if ($r !== '') $out['roles'][] = $r;
                }
            }
        } elseif (str_starts_with($arg, '--faculty-id=')) {
            $fid = (int) trim(substr($arg, strlen('--faculty-id=')));
            if ($fid > 0) $out['faculty_id'] = $fid;
        }
    }
    // normalize roles lowercased unique
    $roles = [];
    foreach ($out['roles'] as $r) {
        $r = strtolower(trim($r));
        if ($r !== '' && !in_array($r, $roles, true)) $roles[] = $r;
    }
    $out['roles'] = $roles;
    return $out;
}

function fail($msg, $code = 1) {
    fwrite(STDERR, $msg . PHP_EOL);
    exit($code);
}

$args = parse_args($argv);

// Resolve faculty
$faculty = null;
if ($args['faculty_id']) {
    $faculty = DB::table('tb_mas_faculty')->where('intID', $args['faculty_id'])->first();
} elseif ($args['username']) {
    $faculty = DB::table('tb_mas_faculty')->where('strUsername', $args['username'])->first();
} else {
    fail("Usage: php scripts/grant_faculty_role.php --username=testfaculty --roles=admin,registrar
Alternatively: php scripts/grant_faculty_role.php --faculty-id=123 --role=admin");
}

if (!$faculty) {
    fail("Faculty not found. Provide a valid --username or --faculty-id.");
}

$fid = (int) $faculty->intID;

// Resolve roles list
if (empty($args['roles'])) {
    // default to admin for convenience
    $args['roles'] = ['admin'];
}

$roleCodes = $args['roles'];

// Ensure roles exist and active; create if missing (active)
$roleIds = [];
foreach ($roleCodes as $code) {
    $role = DB::table('tb_mas_roles')->where('strCode', $code)->first();
    if (!$role) {
        // Auto-create role with reasonable defaults
        $name = ucfirst($code);
        DB::table('tb_mas_roles')->insert([
            'strCode' => $code,
            'strName' => $name,
            'strDescription' => "Auto-created by grant_faculty_role for testing",
            'intActive' => 1,
        ]);
        $role = DB::table('tb_mas_roles')->where('strCode', $code)->first();
        echo "created_role={$code}" . PHP_EOL;
    } elseif ((int) $role->intActive !== 1) {
        DB::table('tb_mas_roles')->where('intRoleID', $role->intRoleID)->update(['intActive' => 1]);
        echo "reactivated_role={$code}" . PHP_EOL;
    }
    if ($role) $roleIds[] = (int) $role->intRoleID;
}

// Attach roles in pivot tb_mas_faculty_roles
$attached = [];
foreach ($roleIds as $rid) {
    $exists = DB::table('tb_mas_faculty_roles')
        ->where(['intFacultyID' => $fid, 'intRoleID' => $rid])
        ->exists();
    if (!$exists) {
        DB::table('tb_mas_faculty_roles')->insert([
            'intFacultyID' => $fid,
            'intRoleID'    => $rid,
        ]);
        $attached[] = $rid;
    }
}

$finalRoles = DB::table('tb_mas_roles')
    ->join('tb_mas_faculty_roles', 'tb_mas_roles.intRoleID', '=', 'tb_mas_faculty_roles.intRoleID')
    ->where('tb_mas_faculty_roles.intFacultyID', $fid)
    ->orderBy('tb_mas_roles.strCode')
    ->get(['tb_mas_roles.intRoleID', 'tb_mas_roles.strCode']);

echo "faculty_id={$fid}" . PHP_EOL;
echo "faculty_username={$faculty->strUsername}" . PHP_EOL;
echo "attached_role_ids=" . implode(',', $attached) . PHP_EOL;
echo "roles_codes=" . implode(',', array_map(fn($r) => $r->strCode, $finalRoles->all())) . PHP_EOL;
echo "status=ok" . PHP_EOL;
