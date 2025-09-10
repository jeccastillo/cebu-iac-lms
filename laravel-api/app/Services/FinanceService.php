<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceService
{
    /**
     * List transactions filtered by optional student number, registration id, and term (syid).
     * Returns an array of normalized transaction rows.
     *
     * Each item shape:
     * [
     *   'id' => int,
     *   'student_number' => string,
     *   'type' => string|null,
     *   'method' => string|null,
     *   'amount' => float,
     *   'or_no' => string|int|null,
     *   'posted_at' => string|null,
     *   'remarks' => string|null,
     * ]
     */
    public function listTransactions(?string $studentNumber = null, ?int $registrationId = null, ?int $syid = null): array
    {
        $q = DB::table('tb_mas_transactions as t')
            ->join('tb_mas_registration as r', 'r.intRegistrationID', '=', 't.intRegistrationID')
            ->join('tb_mas_users as u', 'u.intID', '=', 'r.intStudentID');

        if (!empty($studentNumber)) {
            $q->where('u.strStudentNumber', $studentNumber);
        }

        if (!empty($registrationId)) {
            $q->where('t.intRegistrationID', $registrationId);
        }

        if (!empty($syid)) {
            // filter by AY on transaction if available (fallback to registration AY if needed later)
            $q->where('t.intAYID', $syid);
        }

        return $q->orderBy('t.or_date', 'asc')
            ->orderBy('t.intORNumber', 'asc')
            ->select(
                't.intTransactionID',
                'u.strStudentNumber',
                't.strTransactionType',
                't.intAmountPaid',
                't.intORNumber',
                't.or_date',
                't.pmethod'
            )
            ->get()
            ->map(function ($t) {
                return [
                    'id'             => $t->intTransactionID,
                    'student_number' => $t->strStudentNumber,
                    'type'           => $t->strTransactionType,
                    'method'         => $t->pmethod,
                    'amount'         => (float) $t->intAmountPaid,
                    'or_no'          => $t->intORNumber,
                    'posted_at'      => $t->or_date,
                    'remarks'        => null,
                ];
            })
            ->toArray();
    }

    /**
     * Lookup an OR number and aggregate its items and total.
     * Returns:
     * [
     *   'or_no' => string|int,
     *   'date'  => string|null,
     *   'items' => [ ['type' => string, 'amount' => float], ... ],
     *   'total' => float
     * ]
     * or null if not found.
     */
    public function orLookup(string|int $or): ?array
    {
        $tx = DB::table('tb_mas_transactions')
            ->where('intORNumber', $or)
            ->orderBy('dtePaid', 'asc')
            ->select('strTransactionType', 'intAmountPaid', 'dtePaid')
            ->get();

        if ($tx->isEmpty()) {
            return null;
        }

        $items = [];
        $total = 0.0;
        $date = null;

        foreach ($tx as $t) {
            $items[] = [
                'type'   => $t->strTransactionType,
                'amount' => (float) $t->intAmountPaid,
            ];
            $total += (float) $t->intAmountPaid;
            if (!$date && !empty($t->dtePaid)) {
                $date = $t->dtePaid;
            }
        }

        return [
            'or_no' => $or,
            'date'  => $date,
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * List payment_details rows for the student's registration in the selected term.
     * Filters strictly by:
     *  - payment_details.student_information_id = tb_mas_users.intID
     *  - payment_details.sy_reference = tb_mas_registration.intRegistrationID (for the given term)
     *
     * Returns normalized items and meta totals, along with sy label.
     *
     * Response shape:
     * [
     *   'student_number'  => string|null,
     *   'registration_id' => int|null,
     *   'syid'            => int|null,
     *   'sy_label'        => string|null,
     *   'items'           => PaymentDetailItem[],
     *   'meta'            => [
     *       'total_paid_filtered' => float, // status = 'Paid' AND (description LIKE 'Tuition%' OR 'Reservation%')
     *       'total_paid_all_status' => float, // status = 'Paid'
     *       'total_all_rows' => float, // all rows regardless of status/description
     *       'count_rows' => int
     *   ]
     * ]
     */
    public function listPaymentDetails(?string $studentNumber = null, ?int $syid = null, ?int $studentIdArg = null): array
    {
        $studentNumber = $studentNumber !== null ? (string) $studentNumber : null;
        $syid = $syid !== null ? (int) $syid : null;
        $studentIdArg = $studentIdArg !== null ? (int) $studentIdArg : null;

        // Build default response scaffold
        $empty = function (?string $sn = null, ?int $regId = null, ?int $sy = null, ?string $label = null): array {
            return [
                'student_number'  => $sn,
                'registration_id' => $regId,
                'syid'            => $sy,
                'sy_label'        => $label,
                'items'           => [],
                'meta'            => [
                    'total_paid_filtered'  => 0.0,
                    'total_paid_all_status'=> 0.0,
                    'total_all_rows'       => 0.0,
                    'count_rows'           => 0,
                ],
            ];
        };

        // Resolve sy label from tb_mas_sy when possible
        $syLabel = null;
        if ($syid) {
            $sy = DB::table('tb_mas_sy')->where('intID', $syid)->first();
            if ($sy) {
                $enumSem = isset($sy->enumSem) ? trim((string)$sy->enumSem) : '';
                $ys = isset($sy->strYearStart) ? trim((string)$sy->strYearStart) : '';
                $ye = isset($sy->strYearEnd) ? trim((string)$sy->strYearEnd) : '';
                $parts = [];
                if ($enumSem !== '') $parts[] = $enumSem;
                if ($ys !== '' || $ye !== '') $parts[] = ($ys !== '' && $ye !== '') ? ($ys . '-' . $ye) : ($ys . $ye);
                $syLabel = trim(implode(' ', $parts));
            }
        }

        if (!$syid) {
            return $empty($studentNumber, null, $syid, $syLabel);
        }

        // Determine student ID priority: use explicit student_id when provided; otherwise resolve via student_number
        $studentId = null;
        if ($studentIdArg !== null) {
            $studentId = (int) $studentIdArg;
        } elseif ($studentNumber !== null) {
            $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
            if ($user) {
                $studentId = (int) $user->intID;
            }
        }

        if ($studentId === null) {
            // Unknown student -> empty response
            return $empty($studentNumber, null, $syid, $syLabel);
        }

        // Resolve registration for the given term
        $registration = DB::table('tb_mas_registration')
            ->where('intStudentID', $studentId)
            ->where('intAYID', $syid)
            ->first();

        // if (!$registration) {
        //     // No registration for term -> empty list
        //     return $empty($studentNumber, null, $syid, $syLabel);
        // }
        if($registration)
            $registrationId = (int) $registration->intRegistrationID;
        else
            $registrationId = null;
        
        //get billing descriptions
        $billingDescriptions = [];
        try {
            if (Schema::hasTable('tb_mas_student_billing')) {                
                $billingRows = DB::table('tb_mas_student_billing')
                    ->where('intStudentID', $studentId)
                    ->where('syid', $syid)
                    ->orderBy('posted_at', 'desc')
                    ->orderBy('intID', 'desc')
                    ->get();                                
                foreach ($billingRows as $br) {
                    $desc = (string) ($br->description ?? '');
                    $amt  = round((float) ($br->amount ?? 0), 2);
                    if ($desc !== '' && $amt != 0.0) {
                        // Collect billing descriptions for matching payments
                        $trimmed = trim($desc);
                        if ($trimmed !== '') {
                            $billingDescriptions[] = $trimmed;
                        }
                    }
                }
            }
        }catch (\Throwable $e) {            
            // Silently ignore in environments without the table/columns
            $billingTotal = 0.0;
        }        
        // Ensure payment_details table exists
        if (!Schema::hasTable('payment_details')) {
            return $empty($studentNumber, $registrationId, $syid, $syLabel);
        }

        // Core required columns
        $required = ['id', 'student_information_id', 'sy_reference', 'description', 'subtotal_order', 'status'];
        foreach ($required as $c) {
            if (!Schema::hasColumn('payment_details', $c)) {
                // Missing critical column(s) -> return empty gracefully
                return $empty($studentNumber, $registrationId, $syid, $syLabel);
            }
        }

        // Optional columns detection
        $hasOrNo   = Schema::hasColumn('payment_details', 'or_no') ? 'or_no' :
                     (Schema::hasColumn('payment_details', 'or_number') ? 'or_number' : null);
        $hasMethod = Schema::hasColumn('payment_details', 'pmethod') ? 'pmethod' :
                     (Schema::hasColumn('payment_details', 'payment_method') ? 'payment_method' : null);
        // Prefer 'paid_at' then 'date' then 'created_at'
        $hasDate   = Schema::hasColumn('payment_details', 'paid_at') ? 'paid_at' :
                     (Schema::hasColumn('payment_details', 'date') ? 'date' :
                     (Schema::hasColumn('payment_details', 'created_at') ? 'created_at' : null));

        // Base query filtered by student + registration
        $base = DB::table('payment_details')
            ->where('student_information_id', $studentId)
            ->where('sy_reference', $syid);

        // Meta totals (use clones with same filters)
        $totalAllRows = (float) (clone $base)->sum('subtotal_order');

        $totalPaidAllStatus = (float) (clone $base)
            ->where('status', 'Paid')
            ->sum('subtotal_order');

        $totalPaidFiltered = (float) (clone $base)
            ->where('status', 'Paid')
            ->where(function ($q)  use ($billingDescriptions) {
                $q->where('description', 'like', 'Tuition%')
                  ->orWhere('description', 'like', 'Reservation%');
                  if (!empty($billingDescriptions)) {
                    $q->orWhereIn('description', array_values(array_unique($billingDescriptions)));
                  }
            })
            ->sum('subtotal_order');

        // Include Payment Journal debit/credit entries into amountPaid
            try {
                if (Schema::hasTable('payment_details')) {
                    // Payment Journal debits (negative subtotal_order, status='Journal')
                    $journalDebitSum = (float) DB::table('payment_details')
                        ->where('student_information_id', $studentId)
                        ->where('sy_reference', $syid)
                        ->where('status', 'Journal')
                        ->sum('subtotal_order');

                    // Payment Journal credits (status='Paid') identified via default remarks flag when available
                    $journalCreditSum = 0.0;
                    if (Schema::hasColumn('payment_details', 'remarks')) {
                        $journalCreditSum = (float) DB::table('payment_details')
                            ->where('student_information_id', $studentId)
                            ->where('sy_reference', $syid)
                            ->where('status', 'Paid')
                            ->where('remarks', 'like', '%CREDIT ADJUSTMENT%')
                            ->sum('subtotal_order');
                    }

                    // Add journal sums to amountPaid (debits are negative, credits positive)                
                    $totalPaidFiltered = (float) ($totalPaidFiltered + $journalDebitSum + $journalCreditSum);
                }
            } catch (\Throwable $e) {
                // keep existing amountPaid when journal tables/columns are missing
                echo $e->getMessage();
            }

        $countRows = (int) (clone $base)->count();

        // Build select list (with null fallbacks for optional columns)
        $select = ['id', 'description', 'subtotal_order', 'status', 'sy_reference','or_date','invoice_number'];
        if ($hasOrNo) {
            $select[] = $hasOrNo . ' as or_no';
        } else {
            $select[] = DB::raw('NULL as or_no');
        }
        if ($hasMethod) {
            $select[] = $hasMethod . ' as method';
        } else {
            $select[] = DB::raw('NULL as method');
        }
        if ($hasDate) {
            $select[] = $hasDate . ' as posted_at';
        } else {
            $select[] = DB::raw('NULL as posted_at');
        }

        $q = DB::table('payment_details')
            ->where('student_information_id', $studentId)
            ->where('sy_reference', $syid)
            ->select($select);

        // Ordering: newest first by date if available, then OR no, then id
        if ($hasDate) {
            $q->orderBy($hasDate, 'desc');
        }
        if ($hasOrNo) {
            $q->orderBy($hasOrNo, 'desc');
        }
        $q->orderBy('id', 'desc');

        $rows = $q->get()->map(function ($r) use ($syid, $syLabel) {
            return [
                'id'            => (int) $r->id,
                'posted_at'     => $r->or_date !== null ? (string) $r->or_date : null,
                'or_no'         => $r->or_no !== null ? $r->or_no : null,
                'invoice_number'=> $r->invoice_number !== null ? $r->invoice_number : null,
                'description'   => $r->description !== null ? (string) $r->description : null,
                'subtotal_order'=> isset($r->subtotal_order) ? (float) $r->subtotal_order : 0.0,
                'status'        => $r->status !== null ? (string) $r->status : null,
                'method'        => $r->method !== null ? (string) $r->method : null,
                'sy_reference'  => isset($r->sy_reference) ? (int) $r->sy_reference : null,
                'syid'          => $syid,
                'sy_label'      => $syLabel,
                'source'        => 'payment_details',
            ];
        })->toArray();

        return [
            'student_number'  => $studentNumber,
            'registration_id' => $registrationId,
            'syid'            => $syid,
            'sy_label'        => $syLabel,
            'items'           => $rows,
            'meta'            => [
                'total_paid_filtered'   => round($totalPaidFiltered, 2),
                'total_paid_all_status' => round($totalPaidAllStatus, 2),
                'total_all_rows'        => round($totalAllRows, 2),
                'count_rows'            => $countRows,
            ],
        ];
    }
}
