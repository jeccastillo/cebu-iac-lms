<?php
// Test runner: Thoroughly exercise Subjects write endpoints and verify DB side-effects.

// 1) Bootstrap Laravel for DB access
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

// 2) HTTP helpers
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

function http_get_json($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $code, 'error' => $err, 'body' => $body];
}

// 3) Prepare baseline IDs from DB (first subject, first room, first program)
$subjectFirst = $db->table('tb_mas_subjects')->orderBy('intID', 'asc')->first();
$roomFirst    = $db->table('tb_mas_classrooms')->orderBy('intID', 'asc')->first();
$programFirst = $db->table('tb_mas_programs')->orderBy('intProgramID', 'asc')->first();
$sidFirst = $subjectFirst ? (int)$subjectFirst->intID : 0;
$ridFirst = $roomFirst ? (int)$roomFirst->intID : 0;
$pidFirst = $programFirst ? (int)$programFirst->intProgramID : 1;

out('subject_first', $sidFirst);
out('room_first', $ridFirst);
out('program_id_first', $pidFirst);

// 4) Create a unique subject code to avoid collisions
$code = 'ZZTEST' . date('ymdHis');
out('subject_code', $code);

// 5) POST /subjects/submit (create)
$createPayload = [
    'strCode'             => $code,
    'strDescription'      => 'ZZ Temp Subject',
    'strUnits'            => 3,
    'strTuitionUnits'     => 0,
    'strLabClassification'=> 'none',
    'intLab'              => 0,
    'strDepartment'       => 'QA',
    'intLectHours'        => 3,
    'intProgramID'        => $pidFirst,
    'include_gwa'         => 0,
    'isNSTP'              => 0,
    'isThesisSubject'     => 0,
    'isInternshipSubject' => 0,
    'intBridging'         => 0,
    'intMajor'            => 0,
    'isElective'          => 0,
    'isSelectableElective'=> 0,
];
$r = http_post_json($BASE . '/subjects/submit', $createPayload);
out('create.status', $r['status']);
out('create.error', $r['error']);
out('create.body', $r['body']);

// Verify created in DB
$new = $db->table('tb_mas_subjects')->where('strCode', $code)->orderBy('intID','desc')->first();
$newId = $new ? (int)$new->intID : 0;
out('new_subject_id', $newId);

// 6) POST /subjects/edit (update description)
if ($newId > 0) {
    $editPayload = [
        'intID'          => $newId,
        'strDescription' => 'ZZ Temp Subject Edited'
    ];
    $r = http_post_json($BASE . '/subjects/edit', $editPayload);
    out('edit.status', $r['status']);
    out('edit.error', $r['error']);
    out('edit.body', $r['body']);
}

// 7) POST /subjects/submit-eq (link new as equivalent of first)
if ($newId > 0 && $sidFirst > 0) {
    $eqPayload = [
        'intSubjectID' => $sidFirst,
        'subj'         => [$newId]
    ];
    $r = http_post_json($BASE . '/subjects/submit-eq', $eqPayload);
    out('submit_eq.status', $r['status']);
    out('submit_eq.error', $r['error']);
    out('submit_eq.body', $r['body']);
}

// 8) POST /subjects/submit-days (for subjectFirst) with sample days
if ($sidFirst > 0) {
    $daysPayload = [
        'intSubjectID' => $sidFirst,
        'subj'         => ['1 3','2 4']
    ];
    $r = http_post_json($BASE . '/subjects/submit-days', $daysPayload);
    out('submit_days.status', $r['status']);
    out('submit_days.error', $r['error']);
    out('submit_days.body', $r['body']);
}

// 9) POST /subjects/submit-room (for subjectFirst) if a room exists
if ($sidFirst > 0 && $ridFirst > 0) {
    $roomPayload = [
        'intSubjectID' => $sidFirst,
        'rooms'        => [$ridFirst]
    ];
    $r = http_post_json($BASE . '/subjects/submit-room', $roomPayload);
    out('submit_room.status', $r['status']);
    out('submit_room.error', $r['error']);
    out('submit_room.body', $r['body']);
} else {
    out('submit_room.skipped', 'no_room_or_subject_first');
}

// 10) POST /subjects/submit-prereq (subjectFirst requires new subject)
if ($sidFirst > 0 && $newId > 0) {
    $prePayload = [
        'intSubjectID'      => $sidFirst,
        'program'           => $pidFirst,
        'intPrerequisiteID' => $newId
    ];
    $r = http_post_json($BASE . '/subjects/submit-prereq', $prePayload);
    out('submit_prereq.status', $r['status']);
    out('submit_prereq.error', $r['error']);
    out('submit_prereq.body', $r['body']);
}

// 11) DB verification of side-effects
$checks = [];

// edit verification
$updated = $db->table('tb_mas_subjects')->where('intID', $newId)->first();
$checks['edit_desc_ok'] = ($updated && $updated->strDescription === 'ZZ Temp Subject Edited') ? 1 : 0;

// equivalents
$checks['equivalents'] = $db->table('tb_mas_equivalents')
                            ->where(['intSubjectID' => $sidFirst, 'intEquivalentID' => $newId])
                            ->count();

// days
$days = $db->table('tb_mas_days')->where('intSubjectID', $sidFirst)->pluck('strDays')->toArray();
$checks['days_total']     = count($days);
$checks['days_has_1_3']   = in_array('1 3', $days, true) ? 1 : 0;
$checks['days_has_2_4']   = in_array('2 4', $days, true) ? 1 : 0;

// room link
if ($ridFirst > 0) {
    $checks['room_linked'] = $db->table('tb_mas_room_subject')
                                ->where(['intSubjectID' => $sidFirst, 'intRoomID' => $ridFirst])
                                ->count();
} else {
    $checks['room_linked'] = -1;
}

// prereq
$checks['prereq'] = $db->table('tb_mas_prerequisites')
                       ->where(['intSubjectID' => $sidFirst, 'intPrerequisiteID' => $newId])
                       ->count();

out('checks', $checks);

// 12) Cleanup of created subject (optional; comment out to keep)
// $del = http_post_json($BASE . '/subjects/delete', ['id' => $newId]);
// out('delete.status', $del['status']);
// out('delete.error', $del['error']);
// out('delete.body', $del['body']);

out('status', 'ok');
