<?php
// Test runner: exercise TuitionYear write endpoints (duplicate, finalize, delete) and verify via DB.

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

// 1) Pick a baseline tuition year (first available)
$base = $db->table('tb_mas_tuition_year')->orderBy('intID', 'asc')->first();
if (!$base) {
    out('status', 'no_base_tuition_year');
    exit(0);
}
$baseId   = (int) $base->intID;
$baseYear = isset($base->year) ? $base->year : '';
out('base_id', $baseId);
out('base_year', $baseYear);

// 2) Duplicate it via API to create a working copy
$dup = http_post_json($BASE . '/tuition-years/duplicate', ['id' => $baseId]);
out('duplicate.status', $dup['status']);
out('duplicate.error', $dup['error']);
out('duplicate.body', $dup['body']);

// Parse new id
$newId = 0;
if ($dup['status'] === 200 && $dup['body']) {
    $obj = json_decode($dup['body'], true);
    if (is_array($obj) && isset($obj['newid'])) {
        $newId = (int) $obj['newid'];
    }
}
out('new_id', $newId);

if ($newId <= 0) {
    out('status', 'duplicate_failed');
    exit(0);
}

// 3) Finalize/update the duplicated row: tweak year to mark test
$updatedYear = ($baseYear !== '' ? $baseYear : 'TY') . '-Auto';
$fin = http_post_json($BASE . '/tuition-years/finalize', ['intID' => $newId, 'year' => $updatedYear]);
out('finalize.status', $fin['status']);
out('finalize.error', $fin['error']);
out('finalize.body', $fin['body']);

// 4) GET the new object (direct DB) to verify update
$newRow = $db->table('tb_mas_tuition_year')->where('intID', $newId)->first();
$verifiedYear = $newRow && isset($newRow->year) ? $newRow->year : '';
out('verified_year', $verifiedYear);
out('finalize_ok', ($verifiedYear === $updatedYear) ? 1 : 0);

// 5) Basic read endpoint smoke checks for the new id
$show = http_get_json($BASE . '/tuition-years/' . $newId);
out('show.status', $show['status']);
out('show.error', $show['error']);

// 6) Clean up: delete the duplicated tuition year
$del = http_post_json($BASE . '/tuition-years/delete', ['id' => $newId]);
out('delete.status', $del['status']);
out('delete.error', $del['error']);
out('delete.body', $del['body']);

// 7) Verify deletion
$exists = $db->table('tb_mas_tuition_year')->where('intID', $newId)->exists();
out('deleted_ok', $exists ? 0 : 1);

out('status', 'ok');
