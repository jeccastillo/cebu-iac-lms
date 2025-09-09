<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service to compute student payment status for a specific term (syid).
 *
 * Strategy:
 * - Primary source: tb_mas_student_ledger
 *   * Positive amounts are charges
 *   * Negative amounts are payments
 * - Fallback: StudentBillingService (charges/credits) + FinanceService (Paid payments from payment_details)
 *
 * Public API:
 * - termBalance(int $studentId, int $syid): array
 * - isFullyPaidForTerm(int $studentId, int $syid, float $tolerance = 0.01): array
 */
class StudentPaymentStatusService
{
    /**
     * Compute term-scoped totals for a student.
     *
     * Returns:
     * [
     *   'student_id'        => int,
     *   'syid'              => int,
     *   'charges_total'     => float,   // sum of charges
     *   'payments_total'    => float,   // sum of Paid payments (cash received)
     *   'credits_total'     => float,   // sum of credits/negative billing amounts (discounts/adjustments)
     *   'effective_paid'    => float,   // payments_total + credits_total
     *   'outstanding'       => float,   // charges_total - effective_paid
     *   'last_payment_date' => ?string, // last paid date if known
     *   'source'            => 'ledger'|'fallback',
     * ]
     */
    public function termBalance(int $studentId, int $syid): array
    {
        $chargesTotal = 0.0;
        $paymentsTotal = 0.0;
        $creditsTotal = 0.0;
        $lastPaymentDate = null;
        $source = 'fallback';

        // If there is NO registration data for this student+term, compute balance from tuition (SavedTuition)
        try {
            $registration = DB::table('tb_mas_registration')
                ->where('intStudentID', $studentId)
                ->where('intAYID', $syid)
                ->first();
        } catch (\Throwable $e) {
            $registration = null;
        }

        if (!$registration) {
            // Charges: use tuition total from latest tb_mas_tuition_saved snapshot (if any)
            $tuitionTotal = 0.0;
            try {
                $saved = DB::table('tb_mas_tuition_saved')
                    ->where('intStudentID', $studentId)
                    ->where('syid', $syid)
                    ->orderBy('updated_at', 'desc')
                    ->orderBy('intID', 'desc')
                    ->first();

                if ($saved && isset($saved->payload)) {
                    $payload = is_array($saved->payload) ? $saved->payload : json_decode((string) $saved->payload, true);
                    if (is_array($payload)) {
                        // Try common total keys
                        $possibleTotalKeys = [
                            'total', 'grand_total', 'grandTotal', 'amountPayable', 'totalPayable', 'totals.totalPayable',
                        ];
                        foreach ($possibleTotalKeys as $key) {
                            $val = $this->arrayGetDot($payload, $key);
                            if (is_numeric($val)) {
                                $tuitionTotal = (float) $val;
                                break;
                            }
                        }

                        // Fallback: sum from possible items arrays if total not detected
                        if ($tuitionTotal === 0.0) {
                            $possibleItemsKeys = [
                                'items', 'breakdown', 'charges', 'lines', 'details',
                                'totals.items', 'summary.items',
                            ];
                            foreach ($possibleItemsKeys as $k) {
                                $arr = $this->arrayGetDot($payload, $k);
                                if (is_array($arr)) {
                                    foreach ($arr as $line) {
                                        if (is_array($line)) {
                                            $amt = isset($line['amount']) ? (float) $line['amount'] :
                                                (isset($line['value']) ? (float) $line['value'] : 0.0);
                                            if (is_numeric($amt)) {
                                                $tuitionTotal += (float) $amt;
                                            }
                                        }
                                    }
                                    if ($tuitionTotal > 0.0) break;
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore SavedTuition failures
            }

            $chargesTotal = max(0.0, (float) $tuitionTotal);

            // Payments: Paid Tuition/Reservation (and matched billing descriptions) from payment_details scoped by syid
            try {
                /** @var \App\Services\FinanceService $finance */
                $finance = app(\App\Services\FinanceService::class);
                $pd = $finance->listPaymentDetails(null, $syid, $studentId);

                $paymentsTotal = isset($pd['meta']['total_paid_filtered'])
                    ? (float) $pd['meta']['total_paid_filtered']
                    : 0.0;

                if (!empty($pd['items']) && is_array($pd['items'])) {
                    foreach ($pd['items'] as $row) {
                        $status = $row['status'] ?? null;
                        $posted = $row['posted_at'] ?? null;
                        if ($status === 'Paid' && $posted) {
                            if (!$lastPaymentDate || $posted > $lastPaymentDate) {
                                $lastPaymentDate = $posted;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // keep paymentsTotal as 0.0 on failure
            }

            // Credits: negative student billing rows (discounts/adjustments)
            try {
                /** @var \App\Services\StudentBillingService $billingSvc */
                $billingSvc = app(\App\Services\StudentBillingService::class);
                $billing = $billingSvc->list(null, $studentId, $syid);
            } catch (\Throwable $e) {
                $billing = [];
            }

            foreach ($billing as $it) {
                $amt = isset($it['amount']) ? (float) $it['amount'] : 0.0;
                if ($amt < 0) {
                    $creditsTotal += abs($amt);
                }
            }

            $effectivePaid = round($paymentsTotal + $creditsTotal, 2);
            $outstanding = round($chargesTotal - $effectivePaid, 2);

            return [
                'student_id'        => $studentId,
                'syid'              => $syid,
                'charges_total'     => round($chargesTotal, 2),
                'payments_total'    => round($paymentsTotal, 2),
                'credits_total'     => round($creditsTotal, 2),
                'effective_paid'    => $effectivePaid,
                'outstanding'       => $outstanding,
                'last_payment_date' => $lastPaymentDate,
                'source'            => 'tuition_no_registration',
            ];
        }

        

        
        // Fallback path:
        // - Charges/Credits from StudentBillingService
        // - Payments from FinanceService::listPaymentDetails (meta.total_paid_filtered)
        try {
            /** @var \App\Services\StudentBillingService $billingSvc */
            $billingSvc = app(\App\Services\StudentBillingService::class);
            $billing = $billingSvc->list(null, $studentId, $syid);
        } catch (\Throwable $e) {
            $billing = [];
        }

        foreach ($billing as $it) {
            $amt = isset($it['amount']) ? (float) $it['amount'] : 0.0;
            if ($amt > 0) {
                $chargesTotal += $amt;
            } elseif ($amt < 0) {
                $creditsTotal += abs($amt);
            }
        }

        try {
            /** @var \App\Services\FinanceService $finance */
            $finance = app(\App\Services\FinanceService::class);
            $pd = $finance->listPaymentDetails(null, $syid, $studentId);

            $paymentsTotal = isset($pd['meta']['total_paid_filtered'])
                ? (float) $pd['meta']['total_paid_filtered']
                : 0.0;

            if (!empty($pd['items']) && is_array($pd['items'])) {
                foreach ($pd['items'] as $row) {
                    $status = $row['status'] ?? null;
                    $posted = $row['posted_at'] ?? null;
                    if ($status === 'Paid' && $posted) {
                        if (!$lastPaymentDate || $posted > $lastPaymentDate) {
                            $lastPaymentDate = $posted;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // If FinanceService fails, keep paymentsTotal as 0.0
        }
        

        $effectivePaid = round($paymentsTotal + $creditsTotal, 2);
        $outstanding = round($chargesTotal - $effectivePaid, 2);

        return [
            'student_id'        => $studentId,
            'syid'              => $syid,
            'charges_total'     => round($chargesTotal, 2),
            'payments_total'    => round($paymentsTotal, 2),
            'credits_total'     => round($creditsTotal, 2),
            'effective_paid'    => $effectivePaid,
            'outstanding'       => $outstanding,
            'last_payment_date' => $lastPaymentDate,
            'source'            => $source,
        ];
    }

    /**
     * Determine if the student is fully paid for the term.
     *
     * @param int $studentId tb_mas_users.intID
     * @param int $syid tb_mas_sy.intID
     * @param float $tolerance Amount tolerance considered as fully paid (default 0.01)
     *
     * Returns termBalance() fields +:
     * [
     *   'is_fully_paid' => bool,
     *   'tolerance'     => float,
     * ]
     */
    public function isFullyPaidForTerm(int $studentId, int $syid, float $tolerance = 0.01): array
    {
        // Force not fully paid when there is no registration data for the term
        $hasRegistration = false;
        try {
            $hasRegistration = DB::table('tb_mas_registration')
                ->where('intStudentID', $studentId)
                ->where('intAYID', $syid)
                ->exists();
        } catch (\Throwable $e) {
            $hasRegistration = false;
        }

        $balance = $this->termBalance($studentId, $syid);
        $isFullyPaid = $hasRegistration ? ($balance['outstanding'] <= $tolerance) : false;

        return array_merge($balance, [
            'is_fully_paid' => $isFullyPaid,
            'tolerance'     => $tolerance,
        ]);
    }

    /**
     * Dot-notation array getter helper.
     *
     * @param mixed $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function arrayGetDot($array, string $key, $default = null)
    {
        if (!is_array($array)) return $default;
        if ($key === '') return $array;

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        $segments = explode('.', $key);
        $current = $array;
        foreach ($segments as $segment) {
            if (is_array($current) && array_key_exists($segment, $current)) {
                $current = $current[$segment];
            } else {
                return $default;
            }
        }
        return $current;
    }
}
