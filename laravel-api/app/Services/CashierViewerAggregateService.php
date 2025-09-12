<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CashierViewerAggregateService
{
    /**
     * Build aggregated cashier viewer data using student_number as the primary identifier.
     *
     * @param string $studentNumber
     * @param int    $term  syid
     * @param int|null $studentId Optional fallback/override when already known
     * @return array{
     *   student_number:string,
     *   student_id:int|null,
     *   syid:int,
     *   sy_label:?string,
     *   invoices:array<int,array>,
     *   payment_details:array,
     *   student_billing:array<int,array>,
     *   missing_billing:array<int,array>,
     *   meta:array{
     *     amount_paid:float,
     *     billing_paid:float,
     *     reservation_paid:float,
     *     invoice_counts:array{total:int,tuition:int,billing:int},
     *     totals:array{invoices_total:float,invoices_paid_total:float,invoices_remaining_total:float},
     *     sy_label:?string
     *   }
     * }
     */
    public function buildByStudentNumber(string $studentNumber, int $term, ?int $studentId = null): array
    {
        // Resolve student id if not provided
        $sid = $studentId;
        if ($sid === null) {
            $u = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
            $sid = $u ? (int) $u->intID : null;
        }

        // 1) Payment details (provides sy_label and paid totals meta with journal adjustments)
        /** @var \App\Services\FinanceService $finance */
        $finance = app(\App\Services\FinanceService::class);
        $payment = $finance->listPaymentDetails($studentNumber, $term, $sid);
        $paymentItems = is_array($payment['items'] ?? null) ? $payment['items'] : [];

        // 2) Reservation paid sum
        $reservationPaid = $this->sumReservationPaid($paymentItems);

        // 3) Invoices (list + enrichment)
        /** @var \App\Services\InvoiceService $invSvc */
        $invSvc = app(\App\Services\InvoiceService::class);
        $invoices = $invSvc->list([
            'student_id'     => $sid,
            'student_number' => $studentNumber,
            'syid'           => $term,
        ]);
        $invoicesEnriched = $this->computeInvoiceEnrichment($invoices, $paymentItems, $reservationPaid);

        // 4) Student billing list
        /** @var \App\Services\StudentBillingService $sbSvc */
        $sbSvc = app(\App\Services\StudentBillingService::class);
        $studentBilling = $sbSvc->list($studentNumber, $sid, $term);

        // 5) Missing billing invoices list
        $missing = [];
        try {
            if ($sid) {
                /** @var \App\Services\StudentBillingExtrasService $extraSvc */
                $extraSvc = app(\App\Services\StudentBillingExtrasService::class);
                $missing = $extraSvc->listMissingInvoices((int) $sid, (int) $term);
            }
        } catch (\Throwable $e) {            
            $missing = [];
        }

        // 6) Invoice index by invoice_number -> type (lowercased)
        $invTypeIndex = [];
        foreach ($invoicesEnriched as $inv) {
            $num = isset($inv['invoice_number']) ? (string) $inv['invoice_number'] : (isset($inv['number']) ? (string) $inv['number'] : '');
            if ($num !== '') {
                $invTypeIndex[$num] = isset($inv['type']) ? strtolower((string) $inv['type']) : null;
            }
            // Add billing_id index for direct linkage
            if (isset($inv['billing_id']) && $inv['billing_id'] !== null) {
                $invTypeIndex['billing_id_' . (int)$inv['billing_id']] = strtolower((string) $inv['type']);
            }
        }

        // 7) Billing paid (classify paid rows toward 'billing' invoices or non-tuition/reservation/application)
        $billingPaid = $this->sumBillingPaid($paymentItems, $invTypeIndex);

        // 8) amount_paid from payment meta (already adjusted by journals in FinanceService)
        $amountPaid = 0.0;
        try {
            if (isset($payment['meta']['total_paid_filtered'])) {
                $amountPaid = (float) $payment['meta']['total_paid_filtered'];
            }
        } catch (\Throwable $e) {}

        // 9) Invoice totals and counts
        $invTotal = 0.0;
        $invPaidTotal = 0.0;
        $invRemainingTotal = 0.0;
        $countTuition = 0;
        $countBilling = 0;

        foreach ($invoicesEnriched as $inv) {
            $t = isset($inv['amount_total']) ? (float) $inv['amount_total'] : (isset($inv['amount']) ? (float) $inv['amount'] : 0.0);
            $p = isset($inv['_paid']) ? (float) $inv['_paid'] : 0.0;
            $r = isset($inv['_remaining']) && $inv['_remaining'] !== null ? (float) $inv['_remaining'] : max(0.0, $t - $p);

            $invTotal += $t;
            $invPaidTotal += $p;
            $invRemainingTotal += $r;

            $typ = strtolower((string) ($inv['type'] ?? ''));
            if ($typ === 'tuition') $countTuition++;
            if ($typ === 'billing') $countBilling++;
        }

        // 10) Payment Modes (active) and Payment Descriptions (for UI dropdowns/auto-fill)
        try {
            $paymentModes = DB::table('payment_modes')
                ->select('id', 'name', 'type', 'charge', 'image_url', 'pchannel', 'pmethod', 'is_nonbank', 'is_active')
                ->whereNull('deleted_at')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get()
                ->map(function ($r) {
                    return [
                        'id'         => isset($r->id) ? (int) $r->id : null,
                        'name'       => isset($r->name) ? (string) $r->name : null,
                        'type'       => isset($r->type) ? (string) $r->type : null,
                        'charge'     => isset($r->charge) ? (float) $r->charge : null,
                        'image_url'  => isset($r->image_url) ? (string) $r->image_url : null,
                        'pchannel'   => isset($r->pchannel) ? (string) $r->pchannel : null,
                        'pmethod'    => isset($r->pmethod) ? (string) $r->pmethod : null,
                        'is_nonbank' => isset($r->is_nonbank) ? (bool) $r->is_nonbank : false,
                        'is_active'  => isset($r->is_active) ? (bool) $r->is_active : false,
                    ];
                })
                ->toArray();
        } catch (\Throwable $e) {
            $paymentModes = [];
        }

        try {
            $paymentDescriptions = DB::table('payment_descriptions')
                ->select(['intID as id', 'name', 'amount'])
                ->orderBy('name')
                ->get()
                ->map(function ($r) {
                    return [
                        'id'     => isset($r->id) ? (int) $r->id : null,
                        'name'   => isset($r->name) ? (string) $r->name : null,
                        'amount' => isset($r->amount) ? (float) $r->amount : null,
                    ];
                })
                ->toArray();
        } catch (\Throwable $e) {
            $paymentDescriptions = [];
        }

        return [
            'student_number'   => $studentNumber,
            'student_id'       => $sid,
            'syid'             => $term,
            'sy_label'         => $payment['sy_label'] ?? null,
            'invoices'         => $invoicesEnriched,
            'payment_details'  => $payment,
            'student_billing'  => $studentBilling,
            'missing_billing'  => $missing,
            // Newly added aggregates for dropdowns and auto-fill
            'payment_modes'        => $paymentModes,
            'payment_descriptions' => $paymentDescriptions,
            'meta'             => [
                'amount_paid'  => round((float) $amountPaid, 2),
                'billing_paid' => round((float) $billingPaid, 2),
                'reservation_paid' => round((float) $reservationPaid, 2),
                'invoice_counts' => [
                    'total'   => count($invoicesEnriched),
                    'tuition' => $countTuition,
                    'billing' => $countBilling,
                ],
                'totals' => [
                    'invoices_total'            => round($invTotal, 2),
                    'invoices_paid_total'       => round($invPaidTotal, 2),
                    'invoices_remaining_total'  => round($invRemainingTotal, 2),
                ],
                'sy_label' => $payment['sy_label'] ?? null,
            ],
        ];
    }

    /**
     * Enrich invoices with _paid, _remaining, and effective totals for tuition considering reservation offsets.
     *
     * @param array<int,array> $invoices
     * @param array<int,array> $paymentItems
     * @param float $reservationPaid
     * @return array<int,array>
     */
    public function computeInvoiceEnrichment(array $invoices, array $paymentItems, float $reservationPaid): array
    {
        // Sum of Paid amounts grouped by invoice_number from payment_details
        $paidByInv = [];
        foreach ($paymentItems as $row) {
            try {
                if (($row['status'] ?? null) !== 'Paid') continue;
                $invNo = isset($row['invoice_number']) && $row['invoice_number'] !== null ? trim((string) $row['invoice_number']) : '';
                if ($invNo === '') continue;

                $amt = isset($row['subtotal_order']) ? (float) $row['subtotal_order'] : 0.0;
                if (!isset($paidByInv[$invNo])) $paidByInv[$invNo] = 0.0;
                $paidByInv[$invNo] += $amt;
            } catch (\Throwable $e) {
                // ignore row errors
            }
        }

        $out = [];
        foreach ($invoices as $inv) {
            $row = $inv; // normalized array from InvoiceService::list()
            $num = isset($row['invoice_number']) ? (string) $row['invoice_number'] : (isset($row['number']) ? (string) $row['number'] : '');

            // Pick total
            $total = null;
            foreach (['amount_total', 'amount', 'total'] as $k) {
                if (isset($row[$k]) && is_numeric($row[$k])) { $total = (float) $row[$k]; break; }
            }
            if ($total === null) $total = 0.0;

            $paid = ($num !== '' && isset($paidByInv[$num])) ? (float) $paidByInv[$num] : 0.0;
            $remaining = max(0.0, $total - $paid);

            $row['_total']     = round($total, 2);
            $row['_paid']      = round($paid, 2);
            $row['_remaining'] = round($remaining, 2);

            $type = strtolower((string) ($row['type'] ?? ''));
            if ($type === 'tuition') {
                $te = max(0.0, $total - $reservationPaid);
                $re = max(0.0, $te - $paid);
                $row['_reservation_applied']  = round($reservationPaid, 2);
                $row['_total_effective']      = round($te, 2);
                $row['_remaining_effective']  = round($re, 2);
            } else {
                $row['_reservation_applied']  = 0.0;
                $row['_total_effective']      = round($total, 2);
                $row['_remaining_effective']  = round($remaining, 2);
            }

            $out[] = $row;
        }

        return $out;
    }

    /**
     * Sum Paid Reservation payments for the term.
     *
     * @param array<int,array> $paymentItems
     */
    public function sumReservationPaid(array $paymentItems): float
    {
        $sum = 0.0;
        foreach ($paymentItems as $row) {
            try {
                if (($row['status'] ?? null) !== 'Paid') continue;
                $desc = strtolower(trim((string) ($row['description'] ?? '')));
                if ($desc === '') continue;
                if (strpos($desc, 'reservation') === 0) {
                    $amt = (float) ($row['subtotal_order'] ?? 0);
                    $sum += $amt;
                }
            } catch (\Throwable $e) {
                // ignore row
            }
        }
        return round($sum, 2);
    }

    /**
     * Sum Paid amounts toward billing invoices or non-tuition/reservation/application descriptors.
     *
     * @param array<int,array> $paymentItems
     * @param array<string,string|null> $invoiceTypeIndex invoice_number => type
     */
    public function sumBillingPaid(array $paymentItems, array $invoiceTypeIndex): float
    {
        $sum = 0.0;
        foreach ($paymentItems as $row) {
            try {
                if (($row['status'] ?? null) !== 'Paid') continue;
                $amt = (float) ($row['subtotal_order'] ?? 0);
                $invNo = isset($row['invoice_number']) && $row['invoice_number'] !== null ? trim((string) $row['invoice_number']) : '';
                $itype = $invNo !== '' && isset($invoiceTypeIndex[$invNo]) ? strtolower((string) $invoiceTypeIndex[$invNo]) : null;
                $desc = strtolower(trim((string) ($row['description'] ?? '')));

                // classify as billing when invoice type says 'billing', or when not clearly tuition/reservation/application
                $isTuitionResApp = ($desc === 'tuition fee') || (strpos($desc, 'reservation') === 0) || ($desc === 'application payment') || ($desc === 'application fee');

                if ($itype === 'billing' || (!$itype && !$isTuitionResApp)) {
                    $sum += $amt;
                }
            } catch (\Throwable $e) {
                // ignore row
            }
        }
        return round($sum, 2);
    }
}
