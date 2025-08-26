<?php
// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/** @var \Illuminate\Database\DatabaseManager $db */
$db = $app->make('db');

function out($k, $v) {
    echo $k . '=' . (is_scalar($v) ? $v : json_encode($v)) . PHP_EOL;
}

$result = [
    'status' => 'ok',
    'new_subject_id' => 0,
    'subject_first' => 0,
    'room_first' => 0,
    'checks' => [],
];

$code = isset($argv[1]) && is_string($argv[1]) && $argv[1] !== '' ? $argv[1] : 'ZZTEST101';
out('subject_code', $code);

// Locate the newly created test subject by code (from harness)
$new = $db->table('tb_mas_subjects')->where('strCode', $code)->orderBy('intID','desc')->first();
if ($new) {
    $result['new_subject_id'] = (int) $new->intID;
    out('new_subject_id', $result['new_subject_id']);
} else {
    $result['status'] = 'missing_new_subject';
    out('status', $result['status']);
    // We still try to provide baseline ids
}

// Determine the baseline (first) subject id used in harness for linking
$first = $db->table('tb_mas_subjects')->orderBy('intID', 'asc')->first();
if ($first) {
    $result['subject_first'] = (int) $first->intID;
}
out('subject_first', $result['subject_first']);

// First room id if any (used by submit-room test)
$room = $db->table('tb_mas_classrooms')->orderBy('intID', 'asc')->first();
if ($room) {
    $result['room_first'] = (int) $room->intID;
}
out('room_first', $result['room_first']);

// Only continue deep verification if we found a new subject
if ($result['new_subject_id'] > 0 && $result['subject_first'] > 0) {
    $sidNew = $result['new_subject_id'];
    $sidFirst = $result['subject_first'];

    // 1) submit-eq: tb_mas_equivalents linking first -> new
    $eqCount = $db->table('tb_mas_equivalents')
                  ->where(['intSubjectID' => $sidFirst, 'intEquivalentID' => $sidNew])
                  ->count();
    $result['checks']['equivalents'] = $eqCount;
    out('equivalents_count', $eqCount);

    // 2) submit-days: tb_mas_days for subject_first with strDays entries
    $days = $db->table('tb_mas_days')
               ->where('intSubjectID', $sidFirst)
               ->pluck('strDays')
               ->toArray();
    $has_13 = in_array('1 3', $days, true);
    $has_24 = in_array('2 4', $days, true);
    $result['checks']['days_total'] = count($days);
    $result['checks']['days_has_1_3'] = $has_13 ? 1 : 0;
    $result['checks']['days_has_2_4'] = $has_24 ? 1 : 0;
    out('days_total', count($days));
    out('days_has_1_3', $has_13 ? 1 : 0);
    out('days_has_2_4', $has_24 ? 1 : 0);

    // 3) submit-room: tb_mas_room_subject for subject_first -> room_first (if any room exists)
    if ($result['room_first'] > 0) {
        $roomLink = $db->table('tb_mas_room_subject')
                       ->where(['intSubjectID' => $sidFirst, 'intRoomID' => $result['room_first']])
                       ->count();
        $result['checks']['room_linked'] = $roomLink;
        out('room_linked', $roomLink);
    } else {
        $result['checks']['room_linked'] = -1;
        out('room_linked', -1); // signifies no room id available in db
    }

    // 4) submit-prereq: tb_mas_prerequisites for subject_first requires newId
    $preCount = $db->table('tb_mas_prerequisites')
                   ->where(['intSubjectID' => $sidFirst, 'intPrerequisiteID' => $sidNew])
                   ->count();
    $result['checks']['prereq'] = $preCount;
    out('prereq_count', $preCount);

    // 5) edit: verify updated description of the new subject
    $updated = $db->table('tb_mas_subjects')->where('intID', $sidNew)->first();
    $descOk = $updated && isset($updated->strDescription) && $updated->strDescription === 'ZZ Temp Subject Edited';
    $result['checks']['edit_desc_ok'] = $descOk ? 1 : 0;
    out('edit_desc_ok', $descOk ? 1 : 0);
}

// Final status
out('status', $result['status']);
