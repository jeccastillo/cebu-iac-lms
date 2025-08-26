<?php
// Test runner: exercise TuitionYear submit-extra/delete-type across all types and verify via DB.

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/** @var \Illuminate\Database\DatabaseManager $db */
$db = $app->make('db');

function out($k, $v) {
    if (!is_scalar($v)) {
        $v = json_encode($v);
    }
    echo $k . '=' . $v . PHP_EOL;
}

$BASE = 'http://127.0.0.1:8000/api/v1';

function http_post_json($url, $payload) {
    $ch = curl_init($url);
    $data = json_encode($payload);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ],
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $code, 'error' => $err, 'body' => $body];
}

// Reference IDs
$programFirst = $db->table('tb_mas_programs')->orderBy('intProgramID','asc')->first();
$subjectFirst = $db->table('tb_mas_subjects')->orderBy('intID','asc')->first();

$pid = $programFirst ? (int)$programFirst->intProgramID : 1;
$sid = $subjectFirst ? (int)$subjectFirst->intID : 1;

out('program_id_first', $pid);
out('subject_id_first', $sid);

// 1) Create a new tuition year row we can mutate
$yearLabel = 'ZZTY-' . date('ymdHis');
$add = http_post_json($BASE . '/tuition-years/add', ['year' => $yearLabel, 'isDefault' => 0, 'isDefaultShs' => 0]);
out('add.status', $add['status']);
out('add.body', $add['body']);

$newId = 0;
if ($add['status'] === 200 && $add['body']) {
    $obj = json_decode($add['body'], true);
    if (is_array($obj) && isset($obj['newid'])) {
        $newId = (int)$obj['newid'];
    } else {
        // fallback: find by label
        $ty = $db->table('tb_mas_tuition_year')->where('year', $yearLabel)->orderBy('intID','desc')->first();
        $newId = $ty ? (int)$ty->intID : 0;
    }
}
out('ty_new_id', $newId);

if ($newId <= 0) {
    out('status', 'add_failed');
    exit(0);
}

$prefix = 'ZZQA-' . date('His');

// 2) misc: add and delete
$miscBefore = $db->table('tb_mas_tuition_year_misc')->where('tuitionYearID', $newId)->count();
$miscPayload = [
    'type' => 'misc',
    'tuitionYearID' => $newId,
    'name' => $prefix . '-FEE',
    'type_misc' => 'regular',
    'tuition_amount' => 101,
    'tuition_amount_online' => 102,
    'tuition_amount_hybrid' => 103,
    'tuition_amount_hyflex' => 104,
];
$r = http_post_json($BASE . '/tuition-years/submit-extra', $miscPayload);
out('misc.add.status', $r['status']);
$miscAfter = $db->table('tb_mas_tuition_year_misc')->where('tuitionYearID', $newId)->count();
$outRow = $db->table('tb_mas_tuition_year_misc')->where(['tuitionYearID' => $newId, 'name' => $miscPayload['name']])->orderBy('intID','desc')->first();
$miscId = $outRow ? (int)$outRow->intID : 0;
out('misc.delta', $miscAfter - $miscBefore);
out('misc.id', $miscId);

if ($miscId > 0) {
    $del = http_post_json($BASE . '/tuition-years/delete-type', ['type' => 'misc', 'id' => $miscId]);
    out('misc.delete.status', $del['status']);
    $exists = $db->table('tb_mas_tuition_year_misc')->where('intID', $miscId)->exists();
    out('misc.deleted_ok', $exists ? 0 : 1);
}

// 3) lab_fee: add and delete
$labBefore = $db->table('tb_mas_tuition_year_lab_fee')->where('tuitionYearID', $newId)->count();
$labPayload = [
    'type' => 'lab_fee',
    'tuitionYearID' => $newId,
    'name' => $prefix . '-LAB',
    'tuition_amount' => 201,
    'tuition_amount_online' => 202,
    'tuition_amount_hybrid' => 203,
    'tuition_amount_hyflex' => 204,
];
$r = http_post_json($BASE . '/tuition-years/submit-extra', $labPayload);
out('lab.add.status', $r['status']);
$labAfter = $db->table('tb_mas_tuition_year_lab_fee')->where('tuitionYearID', $newId)->count();
$outRow = $db->table('tb_mas_tuition_year_lab_fee')->where(['tuitionYearID' => $newId, 'name' => $labPayload['name']])->orderBy('intID','desc')->first();
$labId = $outRow ? (int)$outRow->intID : 0;
out('lab.delta', $labAfter - $labBefore);
out('lab.id', $labId);

if ($labId > 0) {
    $del = http_post_json($BASE . '/tuition-years/delete-type', ['type' => 'lab_fee', 'id' => $labId]);
    out('lab.delete.status', $del['status']);
    $exists = $db->table('tb_mas_tuition_year_lab_fee')->where('intID', $labId)->exists();
    out('lab.deleted_ok', $exists ? 0 : 1);
}

// 4) track: add and delete (FK: tuitionyear_id & track_id)
$trackBefore = $db->table('tb_mas_tuition_year_track')->where('tuitionyear_id', $newId)->count();
$trackPayload = [
    'type' => 'track',
    'tuitionyear_id' => $newId,
    'track_id' => $pid,
    'tuition_amount' => 301,
    'tuition_amount_online' => 302,
    'tuition_amount_hybrid' => 303,
    'tuition_amount_hyflex' => 304,
];
$r = http_post_json($BASE . '/tuition-years/submit-extra', $trackPayload);
out('track.add.status', $r['status']);
$trackAfter = $db->table('tb_mas_tuition_year_track')->where('tuitionyear_id', $newId)->count();
$trackRow = $db->table('tb_mas_tuition_year_track')->where(['tuitionyear_id' => $newId, 'track_id' => $pid])->orderBy('id','desc')->first();
$trackId = $trackRow ? (int)$trackRow->id : 0;
out('track.delta', $trackAfter - $trackBefore);
out('track.id', $trackId);

if ($trackId > 0) {
    $del = http_post_json($BASE . '/tuition-years/delete-type', ['type' => 'track', 'id' => $trackId]);
    out('track.delete.status', $del['status']);
    $exists = $db->table('tb_mas_tuition_year_track')->where('id', $trackId)->exists();
    out('track.deleted_ok', $exists ? 0 : 1);
}

// 5) program: add and delete (FK: tuitionyear_id & track_id)
$progBefore = $db->table('tb_mas_tuition_year_program')->where('tuitionyear_id', $newId)->count();
$progPayload = [
    'type' => 'program',
    'tuitionyear_id' => $newId,
    'track_id' => $pid,
    'tuition_amount' => 401,
    'tuition_amount_online' => 402,
    'tuition_amount_hybrid' => 403,
    'tuition_amount_hyflex' => 404,
];
$r = http_post_json($BASE . '/tuition-years/submit-extra', $progPayload);
out('program.add.status', $r['status']);
$progAfter = $db->table('tb_mas_tuition_year_program')->where('tuitionyear_id', $newId)->count();
$progRow = $db->table('tb_mas_tuition_year_program')->where(['tuitionyear_id' => $newId, 'track_id' => $pid])->orderBy('id','desc')->first();
$progId = $progRow ? (int)$progRow->id : 0;
out('program.delta', $progAfter - $progBefore);
out('program.id', $progId);

if ($progId > 0) {
    $del = http_post_json($BASE . '/tuition-years/delete-type', ['type' => 'program', 'id' => $progId]);
    out('program.delete.status', $del['status']);
    $exists = $db->table('tb_mas_tuition_year_program')->where('id', $progId)->exists();
    out('program.deleted_ok', $exists ? 0 : 1);
}

// 6) elective: add and delete (FK: tuitionyear_id & subject_id)
$electBefore = $db->table('tb_mas_tuition_year_elective')->where('tuitionyear_id', $newId)->count();
$electPayload = [
    'type' => 'elective',
    'tuitionyear_id' => $newId,
    'subject_id' => $sid,
    'tuition_amount' => 501,
    'tuition_amount_online' => 502,
    'tuition_amount_hybrid' => 503,
    'tuition_amount_hyflex' => 504,
];
$r = http_post_json($BASE . '/tuition-years/submit-extra', $electPayload);
out('elective.add.status', $r['status']);
$electAfter = $db->table('tb_mas_tuition_year_elective')->where('tuitionyear_id', $newId)->count();
$electRow = $db->table('tb_mas_tuition_year_elective')->where(['tuitionyear_id' => $newId, 'subject_id' => $sid])->orderBy('id','desc')->first();
$electId = $electRow ? (int)$electRow->id : 0;
out('elective.delta', $electAfter - $electBefore);
out('elective.id', $electId);

if ($electId > 0) {
    $del = http_post_json($BASE . '/tuition-years/delete-type', ['type' => 'elective', 'id' => $electId]);
    out('elective.delete.status', $del['status']);
    $exists = $db->table('tb_mas_tuition_year_elective')->where('id', $electId)->exists();
    out('elective.deleted_ok', $exists ? 0 : 1);
}

// 7) Cleanup: delete the created tuition year row
$delTy = http_post_json($BASE . '/tuition-years/delete', ['id' => $newId]);
out('ty.delete.status', $delTy['status']);
$existsTy = $db->table('tb_mas_tuition_year')->where('intID', $newId)->exists();
out('ty.deleted_ok', $existsTy ? 0 : 1);

out('status', 'ok');
