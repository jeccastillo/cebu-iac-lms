<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\NonStudentPaymentStoreRequest;
use App\Models\Cashier;
use App\Models\Payee;
use App\Services\PaymentDetailAdminService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class NonStudentPaymentsController extends Controller
{
    /**
     * POST /api/v1/cashiers/{id}/payee-payments
     * Create a payment_details row for a NON-STUDENT (tenant/payee) using the cashier's next OR/Invoice number.
     * Notes:
     *  - No sy_reference stored (NULL when column exists).
     *  - Requires payee_id and id_number (cross-checked).
     *  - Fills optional name/email/contact columns from tb_mas_payee when present.
     *  - Skips any applicant/student updates & journey logs.
     */
    public function store($cashierId, NonStudentPaymentStoreRequest $request)
    {
        $cashier = Cashier::findOrFail((int) $cashierId);
        $payload = $request->validated();

        $payeeId   = (int) $payload['payee_id'];
        $idNumber  = (string) $payload['id_number'];
        $modeIn    = (string) $payload['mode'];
        $mode      = in_array($modeIn, ['or','invoice','none'], true) ? $modeIn : 'or';
        $amount    = (float) $payload['amount'];
        $desc      = (string) $payload['description'];
        $remarks   = (string) $payload['remarks'];
        $methodIn  = array_key_exists('method', $payload) ? ($payload['method'] !== null ? (string) $payload['method'] : null) : null;
        $postedAt  = array_key_exists('posted_at', $payload) ? ($payload['posted_at'] !== null ? (string) $payload['posted_at'] : null) : null;
        $campusId  = array_key_exists('campus_id', $payload) ? ($payload['campus_id'] !== null ? (int) $payload['campus_id'] : null) : null;
        $modePaymentId = (int) $payload['mode_of_payment_id'];
        $conFee   = array_key_exists('convenience_fee', $payload) && $payload['convenience_fee'] !== null ? (float) $payload['convenience_fee'] : 0.0;
        $specificNumber = array_key_exists('number', $payload) && $payload['number'] !== null ? (int) $payload['number'] : null;

        // Cross-check payee
        $payee = Payee::find($payeeId);
        if (!$payee) {
            throw ValidationException::withMessages([
                'payee_id' => ['Payee not found']
            ]);
        }
        $dbIdNumber = trim((string) ($payee->id_number ?? ''));
        if ($dbIdNumber === '' || strcasecmp($dbIdNumber, trim($idNumber)) !== 0) {
            throw ValidationException::withMessages([
                'id_number' => ['ID number does not match Payee record']
            ]);
        }

        // Resolve invoice number reference (optional)
        $invoiceNumberRef = null;
        if ((isset($payload['invoice_id']) || isset($payload['invoice_number']))) {
            try {
                $invoiceRow = null;
                if (isset($payload['invoice_id'])) {
                    $iid = (int) $payload['invoice_id'];
                    if ($iid > 0 && Schema::hasTable('tb_mas_invoices')) {
                        $invoiceRow = DB::table('tb_mas_invoices')
                            ->select('intID', 'invoice_number', 'amount_total', 'status')
                            ->where('intID', $iid)
                            ->first();
                    }
                }
                if ($invoiceRow && isset($invoiceRow->invoice_number) && $invoiceRow->invoice_number !== null && $invoiceRow->invoice_number !== '') {
                    $invoiceNumberRef = (int) $invoiceRow->invoice_number;
                } elseif (isset($payload['invoice_number']) && $payload['invoice_number'] !== '') {
                    $invoiceNumberRef = (int) $payload['invoice_number'];
                }
            } catch (\Throwable $e) {
                // ignore resolution failures
            }
        }

        // Ensure payment_details table and core columns exist
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

        // Determine number column based on mode
        $numberCol = null;
        if ($mode === 'invoice') {
            if (Schema::hasColumn('payment_details', 'invoice_number')) {
                $numberCol = 'invoice_number';
            }
        } else {
            if (Schema::hasColumn('payment_details', 'or_no')) {
                $numberCol = 'or_no';
            } elseif (Schema::hasColumn('payment_details', 'or_number')) {
                $numberCol = 'or_number';
            }
        }
        if ($numberCol === null && $mode !== 'none') {
            throw ValidationException::withMessages([
                'mode' => ['Number column not available in payment_details for selected mode']
            ]);
        }

        // Policy: if mode='or' and invoice is referenced and this is the first payment for that invoice,
        // skip assigning/consuming an OR number and just save with the invoice number.
        $skipOrAndUseInvoice = false;
        if ($mode === 'or' && $invoiceNumberRef !== null && Schema::hasColumn('payment_details', 'invoice_number')) {
            try {
                $existingCount = (int) DB::table('payment_details')
                    ->where('invoice_number', $invoiceNumberRef)
                    ->count();
                if ($existingCount === 0) {
                    $skipOrAndUseInvoice = true;
                    $numberCol = 'invoice_number';
                }
            } catch (\Throwable $e) {
                // if we cannot determine, do not skip to be safe
            }
        }

        // Determine number to use (sequence or specific) when applicable and not skipping
        $current = null;
        if ($mode !== 'none' && !$skipOrAndUseInvoice) {
            if ($specificNumber !== null) {
                // Validate specific number
                $start = $mode === 'invoice' ? (int) ($cashier->invoice_start ?? 0) : (int) ($cashier->or_start ?? 0);
                $end   = $mode === 'invoice' ? (int) ($cashier->invoice_end ?? 0)   : (int) ($cashier->or_end ?? 0);
                if ($start <= 0 || $end <= 0 || $specificNumber < $start || $specificNumber > $end) {
                    throw ValidationException::withMessages([
                        'number' => ['Number must be within cashier\'s configured range (' . $start . '-' . $end . ')']
                    ]);
                }
                $usage = app(\App\Services\CashierService::class)->validateRangeUsage($mode, $specificNumber, $specificNumber);
                if (!$usage['ok']) {
                    throw ValidationException::withMessages([
                        'number' => ['Number already used', $usage]
                    ]);
                }
                $current = $specificNumber;
            } else {
                // Use next in sequence
                $start   = $mode === 'invoice' ? (int) ($cashier->invoice_start ?? 0) : (int) ($cashier->or_start ?? 0);
                $end     = $mode === 'invoice' ? (int) ($cashier->invoice_end ?? 0)   : (int) ($cashier->or_end ?? 0);
                $current = $mode === 'invoice' ? (int) ($cashier->invoice_current ?? 0) : (int) ($cashier->or_current ?? 0);

                if ($start <= 0 || $end <= 0 || $end < $start) {
                    throw ValidationException::withMessages([
                        'range' => ['Cashier range is not properly configured']
                    ]);
                }
                if ($current <= 0 || $current < $start || $current > $end) {
                    throw ValidationException::withMessages([
                        'current' => ['Current pointer must be within configured range']
                    ]);
                }
                // Re-validate number usage for the single current number
                $usage = app(\App\Services\CashierService::class)->validateRangeUsage($mode, (int) $current, (int) $current);
                if (!$usage['ok']) {
                    throw ValidationException::withMessages([
                        'number' => ['Selected number already used', $usage]
                    ]);
                }
            }
        }

        // Optional columns detection (align with CashierController::createPayment)
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

        // Build insert payload (non-student)
        $insert = [
            'student_information_id' => null,     // Explicitly NULL for non-student
            'sy_reference'           => null,     // No term linkage for tenants
            'description'            => $desc,
            'subtotal_order'         => $amount,
            'total_amount_due'       => $amount + $conFee,
            'status'                 => 'Paid',
            'convenience_fee'        => $conFee,
            'payee_id'               => $payeeId,
        ];

        // Ensure request_id/slug when schema requires them (align with journal behavior)
        try {
            if (Schema::hasColumn('payment_details', 'request_id')) {
                $insert['request_id'] = $this->randomString(10);
            }
            if (Schema::hasColumn('payment_details', 'slug')) {
                // Some environments expect non-null slug
                $insert['slug'] = '';
            }
        } catch (\Throwable $e) {
            // fail-open; DB will error only if columns are non-nullable without defaults
        }

        // number column handling
        if ($mode !== 'none') {
            if ($skipOrAndUseInvoice) {
                $insert[$numberCol] = (int) $invoiceNumberRef;
            } else {
                $insert[$numberCol] = (int) $current;
            }
        }

        // Optional mappings
        if ($methodCol && $methodIn !== null) {
            $insert[$methodCol] = $methodIn;
        }
        if ($remarksCol) {
            $insert[$remarksCol] = $remarks;
        }
        if ($studCampCol && $campusId !== null) {
            $insert[$studCampCol] = $campusId;
        }
        // For non-student payments, if student_number column exists, store empty string or payee id_number.
        if ($studNumCol) {
            $insert[$studNumCol] = ''; // do not conflate with student identifiers
        }
        // Fill name/email/contact columns from Payee when present (satisfy NOT NULL constraints)
        if ($firstNameCol)  $insert[$firstNameCol]  = (string) ($payee->firstname ?? '');
        if ($middleNameCol) $insert[$middleNameCol] = (string) ($payee->middlename ?? '');
        if ($lastNameCol)   $insert[$lastNameCol]   = (string) ($payee->lastname ?? '');
        if ($emailCol)      $insert[$emailCol]      = (string) ($payee->email ?? '');
        if ($contactCol)    $insert[$contactCol]    = (string) ($payee->contact_number ?? '');
        if ($dateCol) {
            $insert[$dateCol] = $postedAt ?: date('Y-m-d H:i:s');
        }
        if ($orDateCol) {
            $orDate = null;
            if (isset($payload['or_date']) && $payload['or_date'] !== null) {
                $s = (string) $payload['or_date'];
                $orDate = substr($s, 0, 10);
            }
            $insert[$orDateCol] = $orDate ?: date('Y-m-d');
        }
        if (Schema::hasColumn('payment_details', 'mode_of_payment_id') && $modePaymentId !== null) {
            $insert['mode_of_payment_id'] = $modePaymentId;
        }

        // When issuing an OR (mode='or') and an invoice is selected (optional),
        // persist invoice_number alongside the OR number (unless already used as primary).
        if (!$skipOrAndUseInvoice && $mode === 'or' && Schema::hasColumn('payment_details', 'invoice_number')) {
            try {
                $invoiceRow = null;
                $invNum = null;
                if (isset($payload['invoice_id'])) {
                    $iid = (int) $payload['invoice_id'];
                    if ($iid > 0 && Schema::hasTable('tb_mas_invoices')) {
                        $invoiceRow = DB::table('tb_mas_invoices')
                            ->select('intID', 'invoice_number')
                            ->where('intID', $iid)
                            ->first();
                    }
                }
                if ($invoiceRow && isset($invoiceRow->invoice_number) && $invoiceRow->invoice_number !== null && $invoiceRow->invoice_number !== '') {
                    $invNum = (int) $invoiceRow->invoice_number;
                }
                if ($invNum === null && isset($payload['invoice_number']) && $payload['invoice_number'] !== '') {
                    $invNum = (int) $payload['invoice_number'];
                }
                if ($invNum !== null && $invNum > 0) {
                    $insert['invoice_number'] = $invNum;
                }
            } catch (\Throwable $e) {
                // ignore linking failure
            }
        }

        // Insert and increment pointer (if used sequence and mode consumes a number)
        $idInserted = null;
        DB::transaction(function () use (&$idInserted, $insert, $cashier, $mode, $skipOrAndUseInvoice, $specificNumber) {
            $idInserted = DB::table('payment_details')->insertGetId($insert);
            if ($mode === 'invoice') {
                if ($specificNumber === null) {
                    $cashier->invoice_current = (int) $cashier->invoice_current + 1;
                    $cashier->save();
                }
            } elseif ($mode === 'or') {
                if (!$skipOrAndUseInvoice && $specificNumber === null) {
                    $cashier->or_current = (int) $cashier->or_current + 1;
                    $cashier->save();
                }
            }
        });

        // System log: create payment detail (normalized when possible)
        try {
            $normalized = (new PaymentDetailAdminService())->getById((int) $idInserted);
        } catch (\Throwable $e) {
            $normalized = null;
        }
        SystemLogService::log('create', 'PaymentDetail', (int) $idInserted, null, $normalized ?? $insert, $request);

        return response()->json([
            'success' => true,
            'data' => [
                'id'          => (int) $idInserted,
                'number_used' => $skipOrAndUseInvoice ? (int) $invoiceNumberRef : ($mode === 'none' ? null : (int) $insert[$numberCol]),
                'mode'        => $mode,
                'cashier_id'  => (int) $cashier->intID,
                'payee_id'    => (int) $payeeId,
            ],
        ], 201);
    }

    protected function randomString(int $len = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $out = '';
        $max = strlen($characters) - 1;
        try {
            for ($i = 0; $i < $len; $i++) {
                $out .= $characters[random_int(0, $max)];
            }
        } catch (\Throwable $e) {
            // fallback for environments without random_int
            for ($i = 0; $i < $len; $i++) {
                $out .= $characters[mt_rand(0, $max)];
            }
        }
        return $out;
    }
}
