<?php
/**
 * Grant a role (by code) to a faculty (by id).
 *
 * Usage:
 *   php laravel-api/scripts/grant_role.php <role_code> <faculty_id>
 * Example:
 *   php laravel-api/scripts/grant_role.php cashier_admin 13
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Faculty;

define('LARAVEL_START', microtime(true));

// Resolve laravel base (this script resides in laravel-api/scripts/)
$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    fwrite(STDERR, "Failed to resolve laravel base path\n");
    exit(1);
}

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
// Boot the application kernel once
$kernel->handle(Request::create('/health', 'GET'));

$args = $argv ?? [];
if (count($args) < 3) {
    echo "Usage: php laravel-api/scripts/grant_role.php <role_code> <faculty_id>\n";
    exit(1);
}
$roleCode  = trim((string)$args[1]);
$facultyId = (int)$args[2];

if ($roleCode === '' || $facultyId <= 0) {
    echo "Invalid arguments. role_code must be non-empty; faculty_id must be positive integer.\n";
    exit(1);
}

// Ensure faculty exists
$faculty = Faculty::find($facultyId);
if (!$faculty) {
    echo "Faculty {$facultyId} not found.\n";
    exit(1);
}

// Ensure role exists (create if missing)
$role = Role::where('strCode', $roleCode)->first();
if (!$role) {
    $roleName = ucwords(str_replace(['_', '-'], ' ', $roleCode));
    $role = Role::create([
        'strCode' => $roleCode,
        'strName' => $roleName,
        'intActive' => 1
    ]);
    echo "Created role '{$roleCode}' (id={$role->intRoleID}).\n";
}

// Attach role to faculty if not already present
$exists = DB::table('tb_mas_faculty_roles')
    ->where('intFacultyID', $facultyId)
    ->where('intRoleID', $role->intRoleID)
    ->exists();

if ($exists) {
    echo "Faculty {$facultyId} already has role '{$roleCode}' (role_id={$role->intRoleID}).\n";
} else {
    DB::table('tb_mas_faculty_roles')->insert([
        'intFacultyID' => $facultyId,
        'intRoleID'    => $role->intRoleID
    ]);
    echo "Granted role '{$roleCode}' (role_id={$role->intRoleID}) to faculty {$facultyId}.\n";
}

echo "Done.\n";
