<?php

// Usage: php laravel-api/scripts/debug_tuition_compute.php C2023-01-082 28

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

$studentNumber = $argv[1] ?? null;
$syid = isset($argv[2]) ? (int)$argv[2] : null;

if (!$studentNumber || !$syid) {
    echo "Usage: php laravel-api/scripts/debug_tuition_compute.php <student_number> <syid>\n";
    exit(1);
}

$user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
if (!$user) {
    out('error', 'student_not_found');
    exit(2);
}
out('student.id', $user->intID);
out('student.program_id', $user->intProgramID ?? null);
out('student.level', $user->level ?? null);
out('student.citizenship', $user->strCitizenship ?? null);

$sy = DB::table('tb_mas_sy')->where('intID', $syid)->first();
if (!$sy) {
    out('error', 'term_not_found');
    exit(3);
}
out('term.syid', $syid);

$registration = DB::table('tb_mas_registration')
    ->where('intStudentID', $user->intID)
    ->where('intAYID', $syid)
    ->first();

if (!$registration) {
    out('error', 'registration_not_found_for_term');
    exit(4);
}

$tuitionYearId = $registration->tuition_year ?? null;
if (!$tuitionYearId) {
    out('error', 'registration_missing_tuition_year');
    exit(5);
}

$classType = $registration->type_of_class ?? 'regular';
$level = strtolower($user->level ?? 'college');
$yearLevel = isset($registration->intYearLevel) ? (int)$registration->intYearLevel : null;
$programUsed = isset($registration->current_program) && $registration->current_program
    ? (int)$registration->current_program
    : (int)($user->intProgramID ?? 0);

out('registration.class_type', $classType);
out('registration.year_level', $yearLevel);
out('program_used', $programUsed);
out('level', $level);
out('tuition_year_id', $tuitionYearId);

$tyRow = DB::table('tb_mas_tuition_year')->where('intID', $tuitionYearId)->first();
$tyArr = $tyRow ? (array)$tyRow : [];
out('tuition_year.row', $tyArr);

// Check presence of columns expected by TuitionCalculator::getUnitPrice
$cols = [
    'tuition_amount',
    'tuition_amount_online',
    'tuition_amount_hybrid',
    'tuition_amount_hyflex',
    'installmentIncrease',
    'installmentDP',
    'installmentFixed',
];
$colPresence = [];
foreach ($cols as $c) {
    $colPresence[$c] = Schema::hasColumn('tb_mas_tuition_year', $c) ? 1 : 0;
}
out('tuition_year.columns_present', $colPresence);

// Program-specific override row
$progRow = DB::table('tb_mas_tuition_year_program')
    ->where('tuitionyear_id', $tuitionYearId)
    ->where('track_id', $programUsed)
    ->first();
out('tuition_year_program.override_row', $progRow ? (array)$progRow : null);

// Subjects snapshot for term
$subjects = DB::table('tb_mas_classlist_student as cls')
    ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
    ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
    ->where('cls.intStudentID', $user->intID)
    ->where('cl.strAcademicYear', $syid)
    ->select(
        's.intID as subjectID',
        's.strCode as code',
        's.strUnits as units',
        's.strTuitionUnits as tuitionUnits',
        's.intLab as intLab',
        's.strLabClassification as labClass',
        's.isNSTP as isNSTP',
        's.isThesisSubject as isThesisSubject',
        's.intMajor as intMajor',
        's.isElective as isElective',
        'cls.additional_elective as additional_elective',
        'cl.is_modular as is_modular',
        'cl.payment_amount as payment_amount'
    )
    ->limit(20)
    ->get()
    ->map(function ($r) use ($syid) {
        $arr = (array)$r;
        $arr['units'] = isset($arr['units']) ? (int)$arr['units'] : 0;
        $arr['tuitionUnits'] = isset($arr['tuitionUnits']) ? (int)$arr['tuitionUnits'] : null;
        $arr['intLab'] = isset($arr['intLab']) ? (int)$arr['intLab'] : 0;
        $arr['isNSTP'] = (int)($arr['isNSTP'] ?? 0);
        $arr['isThesisSubject'] = (int)($arr['isThesisSubject'] ?? 0);
        $arr['intMajor'] = (int)($arr['intMajor'] ?? 0);
        $arr['isElective'] = (int)($arr['isElective'] ?? 0);
        $arr['additional_elective'] = (int)($arr['additional_elective'] ?? 0);
        $arr['is_modular'] = (int)($arr['is_modular'] ?? 0);
        $arr['payment_amount'] = (float)($arr['payment_amount'] ?? 0);
        // Resolve term-based lab override if any
        $override = DB::table('tb_mas_subjects_labtype')
            ->where('subject_id', $arr['subjectID'])
            ->where('term_id', $syid)
            ->first();
        $arr['lab_override'] = $override ? (string)$override->lab_classification : null;
        return $arr;
    })
    ->toArray();

out('subjects.sample', $subjects);

// Compute unit price via service
$calc = new App\Services\TuitionCalculator();
$unitPrice = $calc->getUnitPrice($tyArr, $classType, $programUsed);
out('computed.unit_price', $unitPrice);

// Show fallback values used from tuition year row (if any)
$fallbacks = [
    'regular' => $tyArr['tuition_amount'] ?? null,
    'online'  => $tyArr['tuition_amount_online'] ?? null,
    'hybrid'  => $tyArr['tuition_amount_hybrid'] ?? null,
    'hyflex'  => $tyArr['tuition_amount_hyflex'] ?? null,
];
out('tuition_year.fallback_amounts', $fallbacks);

// Done
echo "debug_complete\n";
