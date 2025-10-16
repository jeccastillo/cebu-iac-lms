<?php
/**
 * Backfill tb_mas_invoices from payment_details invoice_number values without an existing invoice.
 *
 * Usage (run from repo root or anywhere with PHP and project available):
 *   php laravel-api/scripts/backfill_invoices_from_payment_details.php [--apply] [--limit=N] [--start=NNN] [--end=NNN] [--campus=ID] [--verbose] [--student=ID] [--term=SYID]
 *
 * Defaults to DRY-RUN (no DB writes). Use --apply to persist.
 *
 * Behavior:
 * - Scans payment_details for distinct non-empty invoice_number values.
 * - For each invoice_number that does NOT exist in tb_mas_invoices:
 *     - Aggregate related payment_details rows
 *     - Require a single unique student_information_id; skip if ambiguous
 *     - Prefer a single unique sy_reference; skip if ambiguous (unless --term is provided)
 *     - Determine earliest posted_at from or_date, paid_at, date, created_at (when available)
 *     - Attach campus_id/cashier_id only if a single distinct value is found
 *     - Compute paid_total using InvoiceService::getInvoicePaidTotal(invoice_number)
 *     - Create invoice via InvoiceService->generate('other', studentId, syid, options), passing:
 *         amount = paid_total
 *         status = 'Paid' if paid_total > 0 else 'Issued'
 *         invoice_number = exact value from payment_details
 *         posted_at = earliest inferred date
 *         remarks = 'Backfilled from payment_details'
 *         campus_id / cashier_id when determinable
 *
 * Idempotency: Checks tb_mas_invoices for existing invoice_number and skips creation if found.
 */

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

define('LARAVEL_START', microtime(true));

// Resolve laravel base (this script resides in laravel-api/scripts/)
$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    fwrite(STDERR, "Failed to resolve laravel base path\n");
    exit(1);
}

require $basePath . '/vendor/autoload.php';
/** @var \Illuminate\Foundation\Application $app */
$app = require_once $basePath . '/bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

/** @var \App\Services\InvoiceService $invoiceService */
$invoiceService = $app->make(\App\Services\InvoiceService::class);

function ts(): string {
    return '[' . date('Y-m-d H:i:s') . ']';
}
function println(string $msg): void {
    echo ts() . ' ' . $msg . PHP_EOL;
}

function parseArgs(array $argv): array
{
    $args = [
        'apply'   => false,
        'limit'   => null,
        'start'   => null,
        'end'     => null,
        'campus'  => null,
        'verbose' => false,
        'student' => null,
        'term'    => null,
    ];
    foreach ($argv as $i => $raw) {
        if ($i === 0) continue;
        if ($raw === '--apply') {
            $args['apply'] = true;
        } elseif ($raw === '--verbose') {
            $args['verbose'] = true;
        } elseif (preg_match('/^--limit=(\d+)$/', $raw, $m)) {
            $args['limit'] = (int)$m[1];
        } elseif (preg_match('/^--start=(.+)$/', $raw, $m)) {
            $args['start'] = $m[1];
        } elseif (preg_match('/^--end=(.+)$/', $raw, $m)) {
            $args['end'] = $m[1];
        } elseif (preg_match('/^--campus=(\d+)$/', $raw, $m)) {
            $args['campus'] = (int)$m[1];
        } elseif (preg_match('/^--student=(\d+)$/', $raw, $m)) {
            $args['student'] = (int)$m[1];
        } elseif (preg_match('/^--term=(\d+)$/', $raw, $m)) {
            $args['term'] = (int)$m[1];
        } else {
            // Ignore unknown args
        }
    }
    return $args;
}

$args = parseArgs($argv);

println('Backfill invoices from payment_details');
println('Mode: ' . ($args['apply'] ? 'APPLY' : 'DRY-RUN'));
if ($args['limit'] !== null) println('Limit: ' . $args['limit']);
if ($args['start'] !== null) println('Start invoice_number filter: ' . $args['start']);
if ($args['end'] !== null) println('End invoice_number filter: ' . $args['end']);
if ($args['campus'] !== null) println('Campus filter: ' . $args['campus']);
if ($args['student'] !== null) println('Student filter: ' . $args['student']);
if ($args['term'] !== null) println('Term filter (sy_reference): ' . $args['term']);
if ($args['verbose']) println('Verbose logging enabled');

// Schema checks
if (!Schema::hasTable('payment_details')) {
    println('ERROR: payment_details table not found. Abort.');
    exit(1);
}
$mustCols = ['invoice_number', 'student_information_id'];
foreach ($mustCols as $c) {
    if (!Schema::hasColumn('payment_details', $c)) {
        println("ERROR: payment_details missing required column: {$c}. Abort.");
        exit(1);
    }
}

if (!Schema::hasTable('tb_mas_invoices')) {
    println('ERROR: tb_mas_invoices table not found. Abort.');
    exit(1);
}
if (!Schema::hasColumn('tb_mas_invoices', 'invoice_number')) {
    println('ERROR: tb_mas_invoices missing invoice_number column. Abort.');
    exit(1);
}

 // Optional columns
$hasSyRef    = Schema::hasColumn('payment_details', 'sy_reference');
$hasOrDate   = Schema::hasColumn('payment_details', 'or_date');
$hasPaidAt   = Schema::hasColumn('payment_details', 'paid_at');
$hasDate     = Schema::hasColumn('payment_details', 'date');
$hasCreated  = Schema::hasColumn('payment_details', 'created_at');
$hasCampusId = Schema::hasColumn('payment_details', 'campus_id');
$hasCashier  = Schema::hasColumn('payment_details', 'cashier_id');
$hasDescription = Schema::hasColumn('payment_details', 'description');

// Build candidate invoice_numbers query
$q = DB::table('payment_details as pd')
    ->leftJoin('tb_mas_invoices as inv', 'inv.invoice_number', '=', 'pd.invoice_number')
    ->whereNotNull('pd.invoice_number')
    ->where('pd.invoice_number', '!=', '')
    ->whereNull('inv.invoice_number');

if ($args['campus'] !== null && $hasCampusId) {
    $q->where('pd.campus_id', (int)$args['campus']);
}
if ($args['student'] !== null) {
    $q->where('pd.student_information_id', (int)$args['student']);
}
if ($args['term'] !== null && $hasSyRef) {
    $q->where('pd.sy_reference', (int)$args['term']);
}

$invNumbers = $q->distinct()->pluck('pd.invoice_number')->toArray();

// Apply optional start/end filters in PHP (lexicographic if non-numeric)
$invNumbers = array_values(array_filter($invNumbers, function ($v) use ($args) {
    $s = $args['start'];
    $e = $args['end'];
    if ($s !== null && strcmp((string)$v, (string)$s) < 0) {
        return false;
    }
    if ($e !== null && strcmp((string)$v, (string)$e) > 0) {
        return false;
    }
    return true;
}));

$totalCandidates = count($invNumbers);
println("Candidates found (missing in tb_mas_invoices): {$totalCandidates}");

$createdCount = 0;
$skippedCount = 0;
$errorsCount  = 0;

foreach ($invNumbers as $idx => $invNo) {
    if ($args['limit'] !== null && $createdCount >= (int)$args['limit']) {
        println("Limit {$args['limit']} reached; stopping.");
        break;
    }

    $invNoStr = (string)$invNo;
    if ($args['verbose']) println("Processing invoice_number={$invNoStr}");

    // Double-check existence guard (idempotent)
    $exists = DB::table('tb_mas_invoices')->where('invoice_number', $invNoStr)->exists();
    if ($exists) {
        if ($args['verbose']) println(" - Already exists in tb_mas_invoices; skip");
        $skippedCount++;
        continue;
    }

    // Fetch related payment_details rows
    $select = ['id', 'student_information_id', 'invoice_number'];
    if ($hasSyRef)    $select[] = 'sy_reference';
    if ($hasOrDate)   $select[] = 'or_date';
    if ($hasPaidAt)   $select[] = 'paid_at';
    if ($hasDate)     $select[] = 'date';
    if ($hasCreated)  $select[] = 'created_at';
    if ($hasCampusId) $select[] = 'campus_id';
    if ($hasCashier)  $select[] = 'cashier_id';
    if ($hasDescription) $select[] = 'description';

    $rowsQ = DB::table('payment_details')->where('invoice_number', $invNoStr);
    if ($args['campus'] !== null && $hasCampusId) {
        $rowsQ->where('campus_id', (int)$args['campus']);
    }
    if ($args['student'] !== null) {
        $rowsQ->where('student_information_id', (int)$args['student']);
    }
    if ($args['term'] !== null && $hasSyRef) {
        $rowsQ->where('sy_reference', (int)$args['term']);
    }

    $rows = $rowsQ->select($select)->get();

    if ($rows->isEmpty()) {
        if ($args['verbose']) println(" - No payment_details rows remaining after filters; skip");
        $skippedCount++;
        continue;
    }

    // Determine unique student id
    $studentIds = [];
    foreach ($rows as $r) {
        if (isset($r->student_information_id)) {
            $studentIds[(int)$r->student_information_id] = true;
        }
    }
    $studentIds = array_keys($studentIds);
    if (count($studentIds) !== 1) {
        println(" - SKIP invoice_number={$invNoStr} due to ambiguous or missing student_information_id: " . json_encode($studentIds));
        $skippedCount++;
        continue;
    }
    $studentId = (int)$studentIds[0];

    // Determine unique sy_reference (term) if available
    $syid = 0;
    if ($hasSyRef) {
        $syVals = [];
        foreach ($rows as $r) {
            $val = isset($r->sy_reference) ? $r->sy_reference : null;
            if ($val !== null) {
                $syVals[(int)$val] = true;
            }
        }
        $syVals = array_keys($syVals);

        if ($args['term'] !== null) {
            // Force to provided term
            $syid = (int)$args['term'];
        } else {
            if (count($syVals) === 1) {
                $syid = (int)$syVals[0];
            } elseif (count($syVals) === 0) {
                $syid = 0;
                if ($args['verbose']) println(" - No sy_reference values; defaulting syid=0");
            } else {
                println(" - SKIP invoice_number={$invNoStr} due to ambiguous sy_reference values: " . json_encode($syVals));
                $skippedCount++;
                continue;
            }
        }
    } else {
        $syid = $args['term'] !== null ? (int)$args['term'] : 0;
        if ($args['verbose']) println(" - sy_reference column missing; using syid={$syid}");
    }

    // Determine earliest posted_at
    $dates = [];
    foreach ($rows as $r) {
        $candidates = [];
        if ($hasOrDate && !empty($r->or_date))   $candidates[] = (string)$r->or_date;
        if ($hasPaidAt && !empty($r->paid_at))   $candidates[] = (string)$r->paid_at;
        if ($hasDate && !empty($r->date))        $candidates[] = (string)$r->date;
        if ($hasCreated && !empty($r->created_at)) $candidates[] = (string)$r->created_at;

        foreach ($candidates as $dt) {
            $t = strtotime($dt);
            if ($t !== false && $t > 0) {
                $dates[] = $t;
            }
        }
    }
    $postedAt = null;
    if (!empty($dates)) {
        $min = min($dates);
        $postedAt = date('Y-m-d H:i:s', $min);
    }

    // Resolve campus_id / cashier_id if consistent
    $campusId = null;
    if ($hasCampusId) {
        $campusVals = [];
        foreach ($rows as $r) {
            if (isset($r->campus_id) && $r->campus_id !== null && $r->campus_id !== '') {
                $campusVals[(int)$r->campus_id] = true;
            }
        }
        $campusKeys = array_keys($campusVals);
        if (count($campusKeys) === 1) {
            $campusId = (int)$campusKeys[0];
        } elseif ($args['campus'] !== null) {
            $campusId = (int)$args['campus'];
        }
    } elseif ($args['campus'] !== null) {
        $campusId = (int)$args['campus'];
    }

    $cashierId = null;
    if ($hasCashier) {
        $cashierVals = [];
        foreach ($rows as $r) {
            if (isset($r->cashier_id) && $r->cashier_id !== null && $r->cashier_id !== '') {
                $cashierVals[(int)$r->cashier_id] = true;
            }
        }
        $cashierKeys = array_keys($cashierVals);
        if (count($cashierKeys) === 1) {
            $cashierId = (int)$cashierKeys[0];
        }
    }

    // Determine invoice type from payment_details.description (most frequent non-empty; tie-break by earliest posted date)
    $type = 'other';
    if (isset($hasDescription) && $hasDescription) {
        $descCounts = [];
        foreach ($rows as $r) {
            $d = isset($r->description) ? strtolower(trim((string)$r->description)) : '';
            if ($d !== '') {
                $descCounts[$d] = ($descCounts[$d] ?? 0) + 1;
            }
        }
        if (!empty($descCounts)) {
            $maxCount = max($descCounts);
            $candidates = array_keys(array_filter($descCounts, function ($c) use ($maxCount) { return $c === $maxCount; }));
            if (count($candidates) === 1) {
                $type = $candidates[0];
            } else {
                $bestDesc = null;
                $bestTs = null;
                foreach ($rows as $r) {
                    $d = isset($r->description) ? strtolower(trim((string)$r->description)) : '';
                    if ($d === '' || !in_array($d, $candidates, true)) continue;

                    $candDates = [];
                    if ($hasOrDate && !empty($r->or_date))   $candDates[] = (string)$r->or_date;
                    if ($hasPaidAt && !empty($r->paid_at))   $candDates[] = (string)$r->paid_at;
                    if ($hasDate && !empty($r->date))        $candDates[] = (string)$r->date;
                    if ($hasCreated && !empty($r->created_at)) $candDates[] = (string)$r->created_at;

                    $tsRow = null;
                    foreach ($candDates as $dt) {
                        $t = strtotime($dt);
                        if ($t !== false && $t > 0) {
                            $tsRow = $tsRow === null ? $t : min($tsRow, $t);
                        }
                    }
                    if ($tsRow === null) continue;

                    if ($bestTs === null || $tsRow < $bestTs) {
                        $bestTs = $tsRow;
                        $bestDesc = $d;
                    }
                }
                if ($bestDesc !== null) {
                    $type = $bestDesc;
                } else {
                    sort($candidates);
                    $type = $candidates[0];
                }
            }
        }
    }

    // Canonicalize known descriptions to standard invoice types
    if ($type === 'tuition fee' || $type === 'tuition') {
        $type = 'tuition';
    }

    // Paid total from service (counts status='Paid' credits; returns positive)
    $paidTotal = 0.0;
    try {
        $paidTotal = (float)$invoiceService->getInvoicePaidTotal($invNoStr);
    } catch (\Throwable $e) {
        println(" - ERROR computing paid total for invoice_number={$invNoStr}: " . $e->getMessage());
        $errorsCount++;
        continue;
    }

    $status = $paidTotal > 0 ? 'Paid' : 'Issued';

    $options = [
        'amount'         => round($paidTotal, 2),
        'status'         => $status,
        'invoice_number' => $invNoStr,
        'posted_at'      => $postedAt,
        'remarks'        => 'Backfilled from payment_details',
    ];
    if ($campusId !== null) $options['campus_id'] = $campusId;
    if ($cashierId !== null) $options['cashier_id'] = $cashierId;
    // Tag payload/source meta by passing items (optional) or rely on InvoiceService to set payload with meta
    // We rely on amount override, so items are not mandatory.

    if ($args['apply']) {
        try {
            $created = $invoiceService->generate($type, (int)$studentId, (int)$syid, $options, null);
            $createdId = (int)($created['id'] ?? 0);
            $amt = (float)($created['amount_total'] ?? 0);
            println("CREATED invoice id={$createdId} invoice_number={$invNoStr} student_id={$studentId} syid={$syid} amount_total={$amt} status={$status} type={$type}" . ($postedAt ? " posted_at={$postedAt}" : ""));
            $createdCount++;
        } catch (\Throwable $e) {
            println(" - ERROR creating invoice for invoice_number={$invNoStr}: " . $e->getMessage());
            $errorsCount++;
            continue;
        }
    } else {
        println("DRY-RUN would create: invoice_number={$invNoStr} student_id={$studentId} syid={$syid} amount_total=" . number_format($paidTotal, 2) . " status={$status} type={$type}" . ($postedAt ? " posted_at={$postedAt}" : "") . ($campusId !== null ? " campus_id={$campusId}" : "") . ($cashierId !== null ? " cashier_id={$cashierId}" : ""));
        $createdCount++; // Count as candidate processed toward limit in dry-run
    }
}

println("Done. Candidates processed=" . count($invNumbers) . " created=" . $createdCount . " skipped=" . $skippedCount . " errors=" . $errorsCount);
if (!$args['apply']) {
    println("Note: This was a DRY-RUN. Re-run with --apply to persist.");
}
