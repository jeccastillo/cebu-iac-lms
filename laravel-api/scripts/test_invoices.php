<?php
/**
 * Minimal critical-path test for Invoice generation and listing.
 *
 * Usage:
 *   php laravel-api/scripts/test_invoices.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

/**
 * Helper to print with timestamp.
 */
function println($msg)
{
    echo '[' . date('Y-m-d H:i:s') . "] $msg\n";
}

println('Starting Invoice critical-path test');

try {
    // Resolve a student id to use
    $student = DB::table('tb_mas_users')->select('intID', 'strStudentNumber')->orderBy('intID', 'asc')->first();
    if (!$student) {
        println('No tb_mas_users row found; cannot proceed with test.');
        exit(1);
    }
    $studentId = (int) $student->intID;
    $studentNumber = isset($student->strStudentNumber) ? (string)$student->strStudentNumber : null;
    println("Using student_id={$studentId}" . ($studentNumber ? " student_number={$studentNumber}" : ''));

    // Choose a syid (term). Use first available or fallback to 0.
    $sy = DB::table('tb_mas_sy')->select('intID')->orderBy('intID', 'asc')->first();
    $syid = $sy ? (int)$sy->intID : 0;
    println("Using syid={$syid}");

    // Generate a simple 'other' invoice with explicit items (ensures deterministic behavior)
    /** @var \App\Services\InvoiceService $svc */
    $svc = $app->make(\App\Services\InvoiceService::class);

    $options = [
        'items'     => [
            ['description' => 'Test Line A', 'amount' => 123.45],
            ['description' => 'Test Line B', 'amount' => 100.00],
        ],
        'status'    => 'Draft',
        'posted_at' => date('Y-m-d H:i:s'),
        'remarks'   => 'Critical-path test entry',
    ];

    println('Generating invoice (type=other) ...');
    $created = $svc->generate('other', $studentId, $syid, $options, null);

    println('Invoice created:');
    echo json_encode($created, JSON_PRETTY_PRINT) . "\n";

    // List invoices for student (basic filter)
    println('Listing invoices for student_id ...');
    $list = $svc->list(['student_id' => $studentId, 'syid' => $syid]);
    println('Invoices count for student: ' . count($list));
    if (!empty($list)) {
        // Show first item summary
        $first = $list[0];
        println('First invoice summary: id=' . $first['id'] . ' type=' . $first['type'] . ' status=' . $first['status'] . ' total=' . $first['amount_total']);
    }

    // Try get() for the created id
    if (!empty($created['id'])) {
        println('Fetching created invoice by id=' . (int)$created['id']);
        $fetched = $svc->get((int)$created['id']);
        echo json_encode($fetched, JSON_PRETTY_PRINT) . "\n";
    }

    // Negative path: non-existing id
    println('Fetching non-existing invoice id=0 (should be null/404 equivalent at service level)');
    $missing = $svc->get(0);
    echo json_encode($missing, JSON_PRETTY_PRINT) . "\n";

    // If registration exists for this student+term, test tuition invoice with registration_id linkage
    $reg = DB::table('tb_mas_registration')
        ->select('intRegistrationID')
        ->where('intStudentID', $studentId)
        ->where('intAYID', $syid)
        ->first();

    if ($reg && isset($reg->intRegistrationID)) {
        $registrationId = (int) $reg->intRegistrationID;
        println("Found registration_id={$registrationId} for student/term; generating tuition invoice with registration_id ...");

        $tuitionOptions = [
            'items'           => [
                ['description' => 'Tuition Test Item', 'amount' => 50.00],
            ],
            'status'          => 'Draft',
            'posted_at'       => date('Y-m-d H:i:s'),
            'remarks'         => 'Registration-linked test invoice',
            'registration_id' => $registrationId,
        ];
        $createdTuition = $svc->generate('tuition', $studentId, $syid, $tuitionOptions, null);
        echo json_encode($createdTuition, JSON_PRETTY_PRINT) . "\n";

        println("Listing invoices filtered by registration_id={$registrationId} ...");
        $byReg = $svc->list(['registration_id' => $registrationId]);
        println('Invoices count for registration_id: ' . count($byReg));
        if (!empty($byReg)) {
            $firstReg = $byReg[0];
            println('First reg-linked invoice summary: id=' . $firstReg['id'] . ' type=' . $firstReg['type'] . ' status=' . $firstReg['status'] . ' total=' . $firstReg['amount_total']);
        }
    } else {
        println('No tb_mas_registration found for this student/term; skipping registration_id linkage test.');
    }

    println('Test complete.');
    exit(0);
} catch (\Throwable $e) {
    println('Exception: ' . $e->getMessage());
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
