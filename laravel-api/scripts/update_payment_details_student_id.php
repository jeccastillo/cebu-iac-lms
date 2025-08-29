<?php

/**
 * Update payment_details.student_information_id by joining tb_mas_users on slug = payment_details.student_number.
 *
 * Usage:
 *   Dry-run (no changes):
 *     php laravel-api/scripts/update_payment_details_student_id.php
 *
 *   Dry-run with backup (CSV of rows that would be updated):
 *     php laravel-api/scripts/update_payment_details_student_id.php --backup
 *
 *   Apply changes (with backup and transactional by default):
 *     php laravel-api/scripts/update_payment_details_student_id.php --apply --backup
 *
 *   Optional limit (preview/update only first N matched rows):
 *     php laravel-api/scripts/update_payment_details_student_id.php --limit=100 --apply
 *
 * Notes:
 * - Matches rows where payment_details.student_number = tb_mas_users.slug.
 * - Sets payment_details.student_information_id = tb_mas_users.intID if NULL, 0, or different from u.intID.
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    fwrite(STDERR, "Failed to resolve Laravel base path\n");
    exit(1);
}

require $basePath . '/vendor/autoload.php';
$app = require $basePath . '/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

function println($msg = ''): void {
    echo (string)$msg . PHP_EOL;
}

function now_str(): string {
    return date('Y-m-d H:i:s');
}

// Parse CLI flags
$argvCopy = $argv;
array_shift($argvCopy); // remove script path

$apply         = false;
$backup        = false;
$noTransaction = false;
$limit         = null;

foreach ($argvCopy as $arg) {
    if ($arg === '--apply') {
        $apply = true;
    } elseif ($arg === '--backup') {
        $backup = true;
    } elseif ($arg === '--no-transaction') {
        $noTransaction = true;
    } elseif (preg_match('/^--limit=(\d+)$/', $arg, $m)) {
        $limit = (int) $m[1];
    }
}

// Validate schema
$missing = [];
if (!Schema::hasTable('payment_details')) $missing[] = 'payment_details';
if (!Schema::hasTable('tb_mas_users')) $missing[] = 'tb_mas_users';
if (!empty($missing)) {
    println("Missing required tables: " . implode(', ', $missing));
    exit(1);
}

$pdCols = ['id', 'student_number', 'student_information_id'];
$uCols  = ['intID', 'slug'];
foreach ($pdCols as $c) {
    if (!Schema::hasColumn('payment_details', $c)) {
        println("Missing column payment_details.$c");
        exit(1);
    }
}
foreach ($uCols as $c) {
    if (!Schema::hasColumn('tb_mas_users', $c)) {
        println("Missing column tb_mas_users.$c");
        exit(1);
    }
}

println("[" . now_str() . "] Starting " . ($apply ? "APPLY" : "DRY-RUN") . " for updating payment_details.student_information_id");
if ($limit !== null) {
    println("Limit: $limit");
}
println("Backup: " . ($backup ? "enabled" : "disabled"));
println("Transaction: " . ($apply ? ($noTransaction ? "disabled" : "enabled") : "n/a (dry-run)"));

// Build candidate query
$base = DB::table('payment_details as pd')
    ->join('tb_mas_users as u', 'u.slug', '=', 'pd.student_number')
    ->whereNotNull('pd.student_number')
    ->where('pd.student_number', '<>', '')
    ->whereRaw('(pd.student_information_id IS NULL OR pd.student_information_id = 0 OR pd.student_information_id <> u.intID)');

$candidateQuery = (clone $base)
    ->select(['pd.id', 'pd.student_number', 'pd.student_information_id', DB::raw('u.intID as new_student_information_id')])
    ->orderBy('pd.id', 'asc');

if ($limit !== null) {
    $candidateQuery->limit($limit);
}

$candidateCount = (clone $candidateQuery)->count();
$totalJoinCount = (clone $base)->count(); // same as candidateCount (same filters)
$totalPdWithStudNum = DB::table('payment_details')
    ->whereNotNull('student_number')
    ->where('student_number', '<>', '')
    ->count();

println("payment_details rows with non-empty student_number: " . $totalPdWithStudNum);
println("Rows matched to users (slug join) needing update: " . $candidateCount);

// Backup (if requested)
$backupFile = null;
$fp = null;
if ($backup) {
    $logDir = $basePath . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $backupFile = $logDir . '/update_payment_details_backup_' . date('Ymd_His') . '.csv';
    $fp = fopen($backupFile, 'w');
    if ($fp === false) {
        println("Failed to open backup file for writing: $backupFile");
        exit(1);
    }
    // CSV header
    fputcsv($fp, ['pd_id', 'student_number', 'old_student_information_id', 'new_student_information_id']);
    // Stream rows to CSV
    (clone $candidateQuery)->chunk(1000, function ($rows) use ($fp) {
        foreach ($rows as $r) {
            fputcsv($fp, [
                $r->id,
                $r->student_number,
                $r->student_information_id,
                $r->new_student_information_id,
            ]);
        }
    });
    fclose($fp);
    println("Backup written: $backupFile");
}

// Show sample (for dry-run)
$sampleLimit = $limit !== null ? $limit : 10;
$sample = (clone $candidateQuery)->limit($sampleLimit)->get();
if (!$apply) {
    println("Sample rows (up to {$sampleLimit}):");
    foreach ($sample as $row) {
        println(sprintf(
            "pd.id=%d student_number=%s old_sid=%s -> new_sid=%d",
            $row->id,
            (string)$row->student_number,
            $row->student_information_id === null ? 'NULL' : (string)$row->student_information_id,
            $row->new_student_information_id
        ));
    }
    println("Dry-run complete. No changes applied.");
    exit(0);
}

// Apply update
$start = microtime(true);
$updated = 0;

$runUpdate = function () use (&$updated, $limit) {
    $sql = "UPDATE payment_details pd
            JOIN tb_mas_users u ON u.slug = pd.student_number
            SET pd.student_information_id = u.intID
            WHERE pd.student_number IS NOT NULL
              AND pd.student_number <> ''
              AND (pd.student_information_id IS NULL OR pd.student_information_id = 0 OR pd.student_information_id <> u.intID)";
    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $updated = DB::affectingStatement($sql);
};

if ($noTransaction) {
    $runUpdate();
} else {
    DB::transaction(function () use ($runUpdate) {
        $runUpdate();
    });
}

$duration = microtime(true) - $start;
println("Updated rows: " . (int)$updated);
println("Duration: " . number_format($duration, 3) . "s");

// Post-verify count
$remaining = (clone $base)->count();
println("Remaining rows still needing update after run: " . $remaining);

println("[" . now_str() . "] Done.");
