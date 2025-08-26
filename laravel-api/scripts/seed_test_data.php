<?php
// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/** @var \Illuminate\Database\DatabaseManager $db */
$db = $app->make('db');

function out($k, $v) {
    echo $k . '=' . $v . PHP_EOL;
}

// 1) Ensure a test faculty account exists for auth (loginType=faculty)
$facultyUsername = 'testfaculty';
$facultyPass     = 'P@ssw0rd!';

$exists = $db->table('tb_mas_faculty')->where('strUsername', $facultyUsername)->exists();
if (!$exists) {
    try {
        $db->table('tb_mas_faculty')->insert([
            'strUsername'     => $facultyUsername,
            'strPass'         => password_hash($facultyPass, PASSWORD_DEFAULT),
            'strFirstname'    => 'Test',
            'strMiddlename'   => '',
            'strLastname'     => 'Faculty',
            'strEmail'        => 'test.faculty@example.com',
            'strMobileNumber' => '0000000000',
            'strAddress'      => 'N/A',
            'strDepartment'   => 'N/A',
            'strSchool'       => 'N/A',
            'intUserLevel'    => 2, // super admin in CI app roles
            'teaching'        => 1,
            'intIsOnline'     => date('Y-m-d H:i:s'),
        ]);
        out('faculty_seed', 'inserted');
    } catch (\Throwable $e) {
        // Schema differences may require additional non-null fields; skip faculty seeding if constraints block it.
        out('faculty_seed', 'skipped');
    }
} else {
    out('faculty_seed', 'exists');
}
out('faculty_username', $facultyUsername);
out('faculty_password', $facultyPass);

// 2) Provide a curriculum id (first available) for subject tests
$curr = $db->table('tb_mas_curriculum')->orderBy('intID', 'asc')->first();
if ($curr) {
    out('curriculum_id', $curr->intID);
} else {
    out('curriculum_id', 0);
}

// 3) Provide a tuition year id (first available) for tuition tests
$ty = $db->table('tb_mas_tuition_year')->orderBy('intID', 'asc')->first();
if ($ty) {
    out('tuition_year_id', $ty->intID);
} else {
    out('tuition_year_id', 0);
}

// 4) Seed test student account and registration for portal/student-data
$studentUsername = 'teststudent';
$studentPass     = 'Stud3ntP@ss!';
$studentEmail    = 'test.student@example.com';
$studentToken    = 'TESTTOKEN123';

 // program for the student and general reference ids
$prog = $db->table('tb_mas_programs')->orderBy('intProgramID', 'asc')->first();
$programId = $prog ? $prog->intProgramID : 1;
if ($prog) {
    out('program_id_first', $prog->intProgramID);
} else {
    out('program_id_first', 0);
}

// first subject id for prereq/equivalent tests
$firstSubject = $db->table('tb_mas_subjects')->orderBy('intID', 'asc')->first();
if ($firstSubject) {
    out('subject_id_first', $firstSubject->intID);
} else {
    out('subject_id_first', 0);
}

// first room id for submit-room tests (optional)
$firstRoom = $db->table('tb_mas_classrooms')->orderBy('intID', 'asc')->first();
if ($firstRoom) {
    out('room_id_first', $firstRoom->intID);
} else {
    out('room_id_first', 0);
}

$student = $db->table('tb_mas_users')->where('strUsername', $studentUsername)->first();
if (!$student) {
    try {
        $db->table('tb_mas_users')->insert([
            'strUsername'        => $studentUsername,
            'strPass'            => password_hash($studentPass, PASSWORD_DEFAULT),
            'strEmail'           => $studentEmail,
            'strFirstname'       => 'Test',
            'strMiddlename'      => '',
            'strLastname'        => 'Student',
            'intProgramID'       => $programId,
            'strGSuiteEmail'     => $studentToken,
            'dteCreated'         => date('Y-m-d'),
            'student_type'       => 'freshman',
            'strStudentNumber'   => 'T' . date('y') . '-00-001', // harmless placeholder if column exists
            'slug'               => $studentUsername, // provide slug if non-nullable
        ]);
        $student = $db->table('tb_mas_users')->where('strUsername', $studentUsername)->first();
        out('student_seed', 'inserted');
    } catch (\Throwable $e) {
        // Fallback: update first existing user to carry the test token for parity tests
        $any = $db->table('tb_mas_users')->orderBy('intID', 'asc')->first();
        if ($any) {
            $db->table('tb_mas_users')->where('intID', $any->intID)->update([
                'intProgramID'     => $programId,
                'strEmail'         => $studentEmail,
                'strGSuiteEmail'   => $studentToken,
                'strStudentNumber' => 'T' . date('y') . '-00-001',
            ]);
            $student = $db->table('tb_mas_users')->where('intID', $any->intID)->first();
            out('student_seed', 'fallback_updated_existing');
        } else {
            out('student_seed', 'skipped_no_rows');
        }
    }
} else {
    // ensure token/program/email are set
    $db->table('tb_mas_users')->where('intID', $student->intID)->update([
        'intProgramID'     => $programId,
        'strEmail'         => $studentEmail,
        'strGSuiteEmail'   => $studentToken,
        'strStudentNumber' => 'T' . date('y') . '-00-001',
    ]);
    out('student_seed', 'exists');
}
out('student_username', $studentUsername);
out('student_password', $studentPass);
out('student_email', $studentEmail);
out('student_portal_token', $studentToken);

// Registration for the student to satisfy portal/student-data (needs dteRegistered not null)
if ($ty && $student) {
    $reg = $db->table('tb_mas_registration')
        ->where([
            'intStudentID' => $student->intID,
            'intAYID'      => $ty->intID
        ])->first();
    if (!$reg) {
        $db->table('tb_mas_registration')->insert([
            'intStudentID'       => $student->intID,
            'intAYID'            => $ty->intID,
            'dteRegistered'      => date('Y-m-d H:i:s'),
            'intROG'             => 1, // enrolled
            'enumStudentType'    => 'continuing',
            // Provide non-null fields commonly required by schema
            'intYearLevel'       => 1,
            'loa_remarks'        => '',       // satisfy NOT NULL without default
            'withdrawal_period'  => 'before', // satisfy NOT NULL without default
        ]);
        out('student_registration', 'inserted');
    } else {
        out('student_registration', 'exists');
    }
} else {
    out('student_registration', 'skipped');
}

out('status', 'ok');
