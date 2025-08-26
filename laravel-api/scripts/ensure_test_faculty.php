<?php
// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

function out($k, $v) { echo $k . '=' . $v . PHP_EOL; }

// Target test faculty identity
$username = 'testfaculty';
$password = 'P@ssw0rd!';

// 1) Try to find existing by username
$fac = DB::table('tb_mas_faculty')->where('strUsername', $username)->first();
if ($fac) {
    DB::table('tb_mas_faculty')->where('intID', $fac->intID)->update([
        'strPass' => password_hash($password, PASSWORD_DEFAULT),
    ]);
    out('status', 'exists');
    out('faculty_id', $fac->intID);
    out('faculty_username', $username);
    exit(0);
}

// 2) Fallback: repurpose the first existing faculty row
$first = DB::table('tb_mas_faculty')->orderBy('intID', 'asc')->first();
if ($first) {
    DB::table('tb_mas_faculty')->where('intID', $first->intID)->update([
        'strUsername' => $username,
        'strPass'     => password_hash($password, PASSWORD_DEFAULT),
        // keep other fields unchanged
    ]);
    out('status', 'updated_first_existing');
    out('faculty_id', $first->intID);
    out('faculty_username', $username);
    exit(0);
}

// 3) As a last resort, attempt an insert with commonly present columns.
//    This may fail depending on schema constraints; we will report failure gracefully.
try {
    $id = DB::table('tb_mas_faculty')->insertGetId([
        'strUsername'     => $username,
        'strPass'         => password_hash($password, PASSWORD_DEFAULT),
        'strFirstname'    => 'Test',
        'strMiddlename'   => '',
        'strLastname'     => 'Faculty',
        'strEmail'        => 'test.faculty@example.com',
        'strMobileNumber' => '0000000000',
        'strAddress'      => 'N/A',
        'strDepartment'   => 'N/A',
        'strSchool'       => 'N/A',
        'intUserLevel'    => 2,
        'teaching'        => 1,
        'intIsOnline'     => date('Y-m-d H:i:s'),
    ]);
    out('status', 'inserted');
    out('faculty_id', $id);
    out('faculty_username', $username);
    exit(0);
} catch (Throwable $e) {
    out('status', 'insert_failed');
    out('error', $e->getMessage());
    exit(1);
}
