<?php
/**
 * Smoke test: Generate Transcript PDF and verify billing item creation.
 *
 * Usage:
 *   php laravel-api/scripts/test_transcript_billing.php [student_id] [type] [term_id]
 *     - student_id: optional; picks first student if omitted
 *     - type: transcript|copy (default: transcript)
 *     - term_id: optional; picks latest tb_mas_sy.intID if omitted
 *
 * This script boots the Laravel HTTP kernel and invokes:
 *   POST /api/v1/reports/students/{studentId}/transcript
 * with minimal payload. It then prints the most recent transcript_requests row
 * and the most recent tb_mas_student_billing rows for the given student/term.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
/** @var \Illuminate\Contracts\Http\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

/** @var \Illuminate\Database\ConnectionInterface $db */
$db = $app->make('db');

function println($s) { echo $s, PHP_EOL; }

$studentId = isset($argv[1]) ? (int)$argv[1] : 0;
$typeIn    = isset($argv[2]) ? (string)$argv[2] : 'transcript';
$termId    = isset($argv[3]) ? (int)$argv[3] : 0;

// Resolve student id if not provided
if ($studentId <= 0) {
  $u = $db->table('tb_mas_users')->select('intID','strStudentNumber','strLastname','strFirstname')->orderBy('intID','asc')->first();
  if (!$u) {
    println("ERROR: No students found in tb_mas_users.");
    exit(1);
  }
  $studentId = (int)$u->intID;
  println("Picked student_id={$studentId} ({$u->strStudentNumber} {$u->strLastname}, {$u->strFirstname})");
} else {
  $u = $db->table('tb_mas_users')->where('intID', $studentId)->first();
  if (!$u) {
    println("ERROR: Provided student_id={$studentId} not found.");
    exit(1);
  }
}

// Resolve term id if not provided
if ($termId <= 0) {
  $sy = $db->table('tb_mas_sy')->select('intID','strYearStart','strYearEnd','enumSem')->orderBy('intID','desc')->first();
  if (!$sy) {
    println("ERROR: No terms found in tb_mas_sy.");
    exit(1);
  }
  $termId = (int)$sy->intID;
  $syLabel = ($sy->enumSem ? ($sy->enumSem . ' ') : '') . 'SY ' . ($sy->strYearStart ?? '') . '-' . ($sy->strYearEnd ?? '');
  println("Picked term_id={$termId} ({$syLabel})");
}

// Build request payload
$dateIssued = date('Y-m-d H:i:s');
$type = strtolower($typeIn);
if ($type !== 'copy') { $type = 'transcript'; }

$payload = [
  'date_issued' => $dateIssued,
  'remarks' => 'Smoke test',
  'prepared_by' => 'Test User',
  'verified_by' => 'Test Verifier',
  'registrar_signatory' => 'Registrar',
  'signatory' => '',
  'type' => $type,
  'term_ids' => [$termId],
];

// Build HTTP request and send to kernel
$uri = '/api/v1/reports/students/' . $studentId . '/transcript';
$request = \Illuminate\Http\Request::create($uri, 'POST', [], [], [], [
  'HTTP_ACCEPT' => 'application/pdf',
  'HTTP_CONTENT_TYPE' => 'application/json',
  'HTTP_X_FACULTY_ID' => '13', // role middleware context
], json_encode($payload));

println("=== POST {$uri} ===");
$response = $kernel->handle($request);

$status = $response->getStatusCode();
println("Response Status: {$status}");
$ct = $response->headers->get('Content-Type');
$cd = $response->headers->get('Content-Disposition');
println("Headers: Content-Type={$ct}; Content-Disposition={$cd}");
if ($status !== 200) {
  $body = (string) $response->getContent();
  println("Non-200 body (truncated): " . substr($body, 0, 300));
}

// Query transcript_requests latest for this student
println("\n=== Latest transcript_requests for student_id={$studentId} ===");
$trs = $db->table('transcript_requests')
  ->where('student_id', $studentId)
  ->orderBy('id', 'desc')->limit(3)->get();
foreach ($trs as $r) {
  echo json_encode($r, JSON_UNESCAPED_UNICODE), PHP_EOL;
}

// Query student billing for this student+term
println("\n=== Latest tb_mas_student_billing rows for intStudentID={$studentId} syid={$termId} ===");
$bill = $db->table('tb_mas_student_billing')
  ->where('intStudentID', $studentId)
  ->where('syid', $termId)
  ->orderBy('intID', 'desc')->limit(5)->get();
foreach ($bill as $r) {
  echo json_encode($r, JSON_UNESCAPED_UNICODE), PHP_EOL;
}

println("\nDone.");
