<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

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

        return $q->orderBy('t.dtePaid', 'asc')
            ->orderBy('t.intORNumber', 'asc')
            ->select(
                't.intTransactionID',
                'u.strStudentNumber',
                't.strTransactionType',
                't.intAmountPaid',
                't.intORNumber',
                't.dtePaid'
            )
            ->get()
            ->map(function ($t) {
                return [
                    'id'             => $t->intTransactionID,
                    'student_number' => $t->strStudentNumber,
                    'type'           => $t->strTransactionType,
                    'method'         => null,
                    'amount'         => (float) $t->intAmountPaid,
                    'or_no'          => $t->intORNumber,
                    'posted_at'      => $t->dtePaid,
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
}
