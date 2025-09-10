<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PaymentJournalService
{
    /**
     * Create a DEBIT journal entry in payment_details.
     * - Stores subtotal_order as a negative number (increasing balance).
     * - Does NOT consume or assign OR/Invoice numbers.
     * - Optionally links to an invoice_number when invoice_id/invoice_number provided.
     * - status = 'Journal'
     *
     * Returns normalized row using PaymentDetailAdminService mapping when possible.
     */
    public function createDebit(array $payload, ?Request $request = null): array
    {
        $studentId = (int) $payload['student_id'];
        $syid      = (int) $payload['term'];
        $amount    = (float) $payload['amount']; // expected > 0
        $desc      = (string) $payload['description'];
        $remarksIn = isset($payload['remarks']) ? (string) $payload['remarks'] : null;
        $methodIn  = isset($payload['method']) ? (string) $payload['method'] : null;
        $postedAt  = isset($payload['posted_at']) ? (string) $payload['posted_at'] : null;
        $campusId  = isset($payload['campus_id']) ? (int) $payload['campus_id'] : null;
        $modePaymentId = isset($payload['mode_of_payment_id']) ? (int) $payload['mode_of_payment_id'] : null;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Amount must be greater than 0 for debit']
            ]);
        }

        $invoiceRef = $this->resolveInvoiceReference($payload);

        $insert = $this->buildInsertBase($studentId, $syid, $desc, -abs($amount), 'Journal', $methodIn, $postedAt, $campusId, $modePaymentId, $remarksIn, $request);
        if ($invoiceRef !== null && Schema::hasColumn('payment_details', 'invoice_number')) {
            $insert['invoice_number'] = (int) $invoiceRef;
        }

        $id = null;
        DB::transaction(function () use (&$id, $insert) {
            $id = DB::table('payment_details')->insertGetId($insert);
        });

        $normalized = $this->normalizeAfterInsert($id);

        // System log
        try {
            SystemLogService::log('create', 'PaymentDetail', (int) $id, null, $normalized ?? $insert, null);
        } catch (\Throwable $e) {}

        return $normalized ?? [
            'id'                 => (int) $id,
            'entry_type'         => 'debit',
            'syid'               => $syid,
            'invoice_number'     => $insert['invoice_number'] ?? null,
            'posted_at'          => $insert['paid_at'] ?? ($insert['date'] ?? ($insert['created_at'] ?? null)),
            'amount'             => -abs($amount),
            'source'             => 'payment_details',
        ];
    }

    /**
     * Create a CREDIT journal entry in payment_details.
     * - Stores subtotal_order as a positive number (decreasing balance).
     * - Does NOT consume or assign OR/Invoice numbers.
     * - If invoice is linked and enforce flag is true, ensure amount does not exceed remaining.
     * - status = 'Paid'
     *
     * Returns normalized row using PaymentDetailAdminService mapping when possible.
     */
    public function createCredit(array $payload, ?Request $request = null): array
    {
        $studentId = (int) $payload['student_id'];
        $syid      = (int) $payload['term'];
        $amount    = (float) $payload['amount']; // expected > 0
        $desc      = (string) $payload['description'];
        $remarksIn = isset($payload['remarks']) ? (string) $payload['remarks'] : null;
        $methodIn  = isset($payload['method']) ? (string) $payload['method'] : null;
        $postedAt  = isset($payload['posted_at']) ? (string) $payload['posted_at'] : null;
        $campusId  = isset($payload['campus_id']) ? (int) $payload['campus_id'] : null;
        $modePaymentId = isset($payload['mode_of_payment_id']) ? (int) $payload['mode_of_payment_id'] : null;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Amount must be greater than 0 for credit']
            ]);
        }

        $invoiceRef = $this->resolveInvoiceReference($payload);
        $enforceRemaining = true;
        if (array_key_exists('enforce_invoice_remaining', $payload)) {
            $enforceRemaining = (bool) $payload['enforce_invoice_remaining'];
        }

        if ($invoiceRef !== null && $enforceRemaining) {
            $this->assertCreditDoesNotExceedInvoiceRemaining((int) $invoiceRef, $amount);
        }

        $insert = $this->buildInsertBase($studentId, $syid, $desc, abs($amount), 'Paid', $methodIn, $postedAt, $campusId, $modePaymentId, $remarksIn, $request);
        if ($invoiceRef !== null && Schema::hasColumn('payment_details', 'invoice_number')) {
            $insert['invoice_number'] = (int) $invoiceRef;
        }

        $id = null;
        DB::transaction(function () use (&$id, $insert) {
            $id = DB::table('payment_details')->insertGetId($insert);
        });

        $normalized = $this->normalizeAfterInsert($id);

        // System log
        try {
            SystemLogService::log('create', 'PaymentDetail', (int) $id, null, $normalized ?? $insert, null);
        } catch (\Throwable $e) {}

        return $normalized ?? [
            'id'                 => (int) $id,
            'entry_type'         => 'credit',
            'syid'               => $syid,
            'invoice_number'     => $insert['invoice_number'] ?? null,
            'posted_at'          => $insert['paid_at'] ?? ($insert['date'] ?? ($insert['created_at'] ?? null)),
            'amount'             => abs($amount),
            'source'             => 'payment_details',
        ];
    }

    /**
     * Build a payment_details insert payload with schema safety across environments.
     */
    protected function buildInsertBase(
        int $studentId,
        int $syid,
        string $description,
        float $amountSigned,
        string $status,
        ?string $methodIn,
        ?string $postedAt,
        ?int $campusId,
        ?int $modePaymentId,
        ?string $remarksIn,
        ?Request $request
    ): array {
        // Ensure payment_details and required columns exist
        if (!Schema::hasTable('payment_details')) {
            throw ValidationException::withMessages([
                'payment_details' => ['payment_details table not found']
            ]);
        }
        $core = ['student_information_id', 'sy_reference', 'description', 'subtotal_order', 'status'];
        foreach ($core as $c) {
            if (!Schema::hasColumn('payment_details', $c)) {
                throw ValidationException::withMessages([
                    'payment_details' => ["Missing required column payment_details.$c"]
                ]);
            }
        }

        // Optional columns
        $methodCol   = Schema::hasColumn('payment_details', 'pmethod') ? 'pmethod'
                     : (Schema::hasColumn('payment_details', 'payment_method') ? 'payment_method' : null);
        $dateCol     = Schema::hasColumn('payment_details', 'paid_at') ? 'paid_at'
                     : (Schema::hasColumn('payment_details', 'date') ? 'date'
                     : (Schema::hasColumn('payment_details', 'created_at') ? 'created_at' : null));
        $remarksCol  = Schema::hasColumn('payment_details', 'remarks') ? 'remarks' : null;
        $studNumCol  = Schema::hasColumn('payment_details', 'student_number') ? 'student_number' : null;
        $studCampCol = Schema::hasColumn('payment_details', 'student_campus') ? 'student_campus' : null;
        $firstNameCol  = Schema::hasColumn('payment_details', 'first_name') ? 'first_name' : null;
        $middleNameCol = Schema::hasColumn('payment_details', 'middle_name') ? 'middle_name' : null;
        $lastNameCol   = Schema::hasColumn('payment_details', 'last_name') ? 'last_name' : null;
        $emailCol      = Schema::hasColumn('payment_details', 'email_address') ? 'email_address' : null;
        $contactCol    = Schema::hasColumn('payment_details', 'contact_number') ? 'contact_number' : null;
        $orDateCol     = Schema::hasColumn('payment_details', 'or_date') ? 'or_date' : null;

        // Resolve student info for non-nullable fields
        $studentNumber = null;
        $first = $middle = $last = $email = $mobile = null;
        try {
            $sel = ['strStudentNumber', 'strFirstname', 'strMiddlename', 'strLastname', 'strEmail', 'strMobileNumber'];
            $usr = DB::table('tb_mas_users')->select($sel)->where('intID', $studentId)->first();
            if ($usr) {
                if (isset($usr->strStudentNumber)) $studentNumber = (string) $usr->strStudentNumber;
                if (isset($usr->strFirstname))     $first = (string) $usr->strFirstname;
                if (isset($usr->strMiddlename))    $middle = (string) $usr->strMiddlename;
                if (isset($usr->strLastname))      $last = (string) $usr->strLastname;
                if (isset($usr->strEmail))         $email = (string) $usr->strEmail;
                if (isset($usr->strMobileNumber))  $mobile = (string) $usr->strMobileNumber;
            }
        } catch (\Throwable $e) {
            // fail-open
        }

        // Generate request_id/random slug fallback
        $random = $this->randomString(10);
        $requestId = $random;

        $insert = [
            'student_information_id' => $studentId,
            'sy_reference'           => $syid,
            'description'            => $description,
            'subtotal_order'         => $amountSigned,
            'total_amount_due'       => abs($amountSigned), // journal/credit default (no convenience fee)
            'convenience_fee'        => 0,
            'status'                 => $status,
            'request_id'             => $requestId,
            'slug'                   => '', // some envs expect non-null
        ];

        if ($methodCol && $methodIn !== null) $insert[$methodCol] = $methodIn;
        if ($remarksCol) {
            // Default tagging for clarity; preserve provided remarks if any
            $defaultTag = $amountSigned < 0 ? 'DEBIT ADJUSTMENT' : 'CREDIT ADJUSTMENT';
            $insert[$remarksCol] = $remarksIn !== null ? $remarksIn : $defaultTag;
        }
        if ($studNumCol) $insert[$studNumCol] = $studentNumber !== null ? $studentNumber : '';
        if ($studCampCol && $campusId !== null) $insert[$studCampCol] = $campusId;

        // Fill name/email columns when present (empty strings to satisfy NOT NULL)
        if ($firstNameCol)  $insert[$firstNameCol]  = $first  !== null ? $first  : '';
        if ($middleNameCol) $insert[$middleNameCol] = $middle !== null ? $middle : '';
        if ($lastNameCol)   $insert[$lastNameCol]   = $last   !== null ? $last   : '';
        if ($emailCol)      $insert[$emailCol]      = $email  !== null ? $email  : '';
        if ($contactCol)    $insert[$contactCol]    = $mobile !== null ? $mobile : '';

        if ($dateCol) {
            $insert[$dateCol] = $postedAt ?: date('Y-m-d H:i:s');
        }
        if ($orDateCol) {
            $d = $postedAt ? substr((string)$postedAt, 0, 10) : date('Y-m-d');
            $insert[$orDateCol] = $d;
        }
        if (Schema::hasColumn('payment_details', 'mode_of_payment_id') && $modePaymentId !== null) {
            $insert['mode_of_payment_id'] = $modePaymentId;
        }

        return $insert;
    }

    /**
     * Resolve invoice reference (invoice_number) from payload invoice_id or invoice_number when available.
     * Returns int invoice number or null when not resolvable.
     */
    protected function resolveInvoiceReference(array $payload): ?int
    {
        $invoiceRef = null;

        try {
            if (isset($payload['invoice_id'])) {
                $iid = (int) $payload['invoice_id'];
                if ($iid > 0 && Schema::hasTable('tb_mas_invoices')) {
                    $row = DB::table('tb_mas_invoices')
                        ->select('intID', 'invoice_number')
                        ->where('intID', $iid)
                        ->first();
                    if ($row && isset($row->invoice_number) && $row->invoice_number !== null && $row->invoice_number !== '') {
                        $invoiceRef = (int) $row->invoice_number;
                    }
                }
            }
            if ($invoiceRef === null && isset($payload['invoice_number']) && $payload['invoice_number'] !== '') {
                $invoiceRef = (int) $payload['invoice_number'];
            }
        } catch (\Throwable $e) {
            // fail-open
        }

        return $invoiceRef;
    }

    /**
     * Assert that the provided credit amount does not exceed invoice remaining:
     * remaining = invoice_total - SUM(payment_details.subtotal_order where status='Paid' and invoice_number = ...)
     */
    protected function assertCreditDoesNotExceedInvoiceRemaining(int $invoiceNumber, float $amount): void
    {
        if (!Schema::hasTable('payment_details')) {
            return; // cannot validate without table
        }
        try {
            $invoiceTotal = null;
            if (Schema::hasTable('tb_mas_invoices')) {
                $row = DB::table('tb_mas_invoices')
                    ->select('invoice_number', 'amount_total', 'amount', 'total')
                    ->where('invoice_number', $invoiceNumber)
                    ->first();
                if ($row) {
                    $cands = [
                        $row->amount_total ?? null,
                        $row->amount ?? null,
                        $row->total ?? null,
                    ];
                    foreach ($cands as $cand) {
                        if ($cand !== null && is_numeric($cand)) {
                            $invoiceTotal = (float) $cand;
                            break;
                        }
                    }
                }
            }

            // If invoice total unknown, we cannot enforce; skip validation
            if ($invoiceTotal === null) {
                return;
            }

            if (!Schema::hasColumn('payment_details', 'invoice_number')) {
                return;
            }

            $paidSum = (float) DB::table('payment_details')
                ->where('invoice_number', $invoiceNumber)
                ->where('status', 'Paid')
                ->sum('subtotal_order');

            $remaining = max($invoiceTotal - $paidSum, 0);
            if ($amount > $remaining + 0.00001) {
                throw ValidationException::withMessages([
                    'amount' => ["Amount exceeds invoice remaining. Invoice #{$invoiceNumber} total={$invoiceTotal}, paid={$paidSum}, remaining={$remaining}"]
                ]);
            }
        } catch (\Throwable $e) {
            // fail-open: skip enforcement if errors occur
        }
    }

    /**
     * Normalize row via PaymentDetailAdminService::getById if available.
     */
    protected function normalizeAfterInsert(int $id): ?array
    {
        try {
            $svc = app(\App\Services\PaymentDetailAdminService::class);
            return $svc->getById((int) $id);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function randomString(int $len = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $out = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $len; $i++) {
            $out .= $characters[random_int(0, $max)];
        }
        return $out;
    }
}
