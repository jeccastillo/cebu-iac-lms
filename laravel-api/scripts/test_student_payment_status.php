<?php
/**
 * Critical-path test for StudentPaymentStatusService (term payment status).
 *
 * Usage:
 *   php laravel-api/scripts/test_student_payment_status.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Helper to print with timestamp.
 */
function println($msg)
{
    echo '[' . date('Y-m-d H:i:s') . "] $msg\n";
}

println('Starting StudentPaymentStatusService critical-path test');

try {
    // Resolve a student id to use
    $student = DB::table('tb_mas_users')
        ->select('intID', 'strStudentNumber')
        ->orderBy('intID', 'asc')
        ->first();

    if (!$student) {
        println('No tb_mas_users row found; cannot proceed with test.');
        exit(1);
    }

    $studentId = (int) $student->intID;
    $studentNumber = isset($student->strStudentNumber) ? (string) $student->strStudentNumber : null;
    println("Using student_id={$studentId}" . ($studentNumber ? " student_number={$studentNumber}" : ''));

    // Try to pick a term with ledger rows (primary path)
    $syWithLedger = null;
    if (Schema::hasTable('tb_mas_student_ledger')) {
        $syWithLedger = DB::table('tb_mas_student_ledger')
            ->where('student_id', $studentId)
            ->where('is_disabled', 0)
            ->orderBy('syid', 'asc')
            ->value('syid');
        if ($syWithLedger !== null) {
            $syWithLedger = (int) $syWithLedger;
        }
    }

    // Pick a general syid as fallback reference
    $syAny = DB::table('tb_mas_sy')->select('intID')->orderBy('intID', 'asc')->value('intID');
    $syAny = $syAny ? (int) $syAny : 0;

    // Pick a term without ledger rows (fallback path)
    $syNoLedger = null;
    if ($syAny) {
        if ($syWithLedger !== null) {
            $syNoLedger = DB::table('tb_mas_sy')
                ->when($syWithLedger !== null, function ($q) use ($syWithLedger) {
                    $q->where('intID', '!=', $syWithLedger);
                })
                ->orderBy('intID', 'asc')
                ->value('intID');
            $syNoLedger = $syNoLedger ? (int) $syNoLedger : null;
        } else {
            // If no ledger term found, attempt to select a term anyway (will exercise fallback)
            $syNoLedger = $syAny;
        }
    }

    /** @var \App\Services\StudentPaymentStatusService $svc */
    $svc = $app->make(\App\Services\StudentPaymentStatusService::class);

    // Scenario 1: Term with ledger rows (if available)
    if ($syWithLedger !== null) {
        println("=== Scenario 1: term with ledger rows (syid={$syWithLedger}) ===");
        $balance1 = $svc->termBalance($studentId, $syWithLedger);
        $status1 = $svc->isFullyPaidForTerm($studentId, $syWithLedger);
        echo "termBalance:\n" . json_encode($balance1, JSON_PRETTY_PRINT) . "\n";
        echo "isFullyPaidForTerm:\n" . json_encode($status1, JSON_PRETTY_PRINT) . "\n";
    } else {
        println('No ledger rows found for this student; skipping Scenario 1.');
    }

    // Scenario 2: Term without ledger rows (fallback)
    if ($syNoLedger !== null) {
        println("=== Scenario 2: term without ledger rows (fallback) (syid={$syNoLedger}) ===");
        $balance2 = $svc->termBalance($studentId, $syNoLedger);
        $status2 = $svc->isFullyPaidForTerm($studentId, $syNoLedger);
        echo "termBalance:\n" . json_encode($balance2, JSON_PRETTY_PRINT) . "\n";
        echo "isFullyPaidForTerm:\n" . json_encode($status2, JSON_PRETTY_PRINT) . "\n";
    } else {
        println('Could not determine a term for fallback scenario; skipping Scenario 2.');
    }

    println('Test complete.');
    exit(0);
} catch (\Throwable $e) {
    println('Exception: ' . $e->getMessage());
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
