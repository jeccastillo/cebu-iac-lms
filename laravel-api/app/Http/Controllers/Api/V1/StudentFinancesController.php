<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentMode;
use App\Services\InvoiceService;
use App\Services\StudentPaymentStatusService;
use App\Services\TuitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FinanceService;

class StudentFinancesController extends Controller
{
    /**
     * GET /api/v1/student/finances/summary
     * Query: student_id|student_number, syid (int)
     * Returns a student-safe summary: tuition totals (full/installment), payments meta, outstanding.
     */
    public function summary(Request $request, TuitionService $tuition, StudentPaymentStatusService $paySvc, InvoiceService $invoiceService, \App\Services\FinanceService $finance): JsonResponse
    {
        $syid = (int) $request->query('syid', 0);
        $studentId = $request->query('student_id');
        $studentNumber = $request->query('student_number');

        if ($syid <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'syid (term) is required.'
            ], 422);
        }

        $sid = null;
        $sno = null;

        if (!empty($studentId)) {
            $sid = (int) $studentId;
            $u = DB::table('tb_mas_users')->where('intID', $sid)->select('strStudentNumber')->first();
            if ($u) {
                $sno = (string) $u->strStudentNumber;
            }
        } elseif (!empty($studentNumber)) {
            $sno = (string) $studentNumber;
            $u = DB::table('tb_mas_users')->where('strStudentNumber', $sno)->select('intID')->first();
            if ($u) {
                $sid = (int) $u->intID;
            }
        }

        if (!$sid || !$sno) {
            return response()->json([
                'success' => false,
                'message' => 'student_id or student_number required (and must resolve to an existing student).'
            ], 422);
        }

        // Resolve term label
        $sy = DB::table('tb_mas_sy')->where('intID', $syid)->first();
        $termLabel = null;
        if ($sy) {
            $sem = is_numeric($sy->enumSem ?? null) ? (int) $sy->enumSem : null;
            $semText = $sem === 1 ? '1st Sem' : ($sem === 2 ? '2nd Sem' : ($sem === 3 ? '3rd Sem' : ($sem === 4 ? 'Summer' : '')));
            $termLabel = trim(($semText ? $semText : (string) ($sy->enumSem ?? '')) . ' ' . ($sy->strYearStart ?? '') . '-' . ($sy->strYearEnd ?? ''));
        }

        // Registration meta
        $reg = DB::table('tb_mas_registration')
            ->where('intStudentID', $sid)
            ->where('intAYID', $syid)
            ->select('intRegistrationID', 'paymentType')
            ->orderBy('intRegistrationID', 'desc')
            ->first();

        $registrationId = $reg ? (int) $reg->intRegistrationID : null;
        $paymentType = $reg && isset($reg->paymentType) ? (string) $reg->paymentType : null; // 'full'|'partial'|null

        // Tuition totals: prefer SavedTuition snapshot; fallback to compute
        $tSummary = [
            'total_full' => 0.0,
            'total_installment' => null,
            'selected_total' => 0.0,
            'source' => 'computed'
        ];
        $billingTotal = 0.0;
        try {
            $usedSaved = false;

            // 1) Try latest SavedTuition snapshot for this registration
            if ($registrationId) {
                $saved = DB::table('tb_mas_tuition_saved')
                    ->where('intStudentID', $sid)
                    ->where('intRegistrationID', $registrationId)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($saved && isset($saved->payload)) {
                    $payload = json_decode((string) $saved->payload, true);
                    if (is_array($payload)) {
                        $sum = is_array($payload['summary'] ?? null) ? $payload['summary'] : [];

                        $totalFull = (float) ($sum['total_due'] ?? 0.0);

                        $inst = is_array($sum['installments'] ?? null) ? $sum['installments'] : [];
                        $totalInstallment = null;
                        if (isset($inst['total_installment']) && is_numeric($inst['total_installment'])) {
                            $totalInstallment = (float) $inst['total_installment'];
                        } elseif (isset($sum['total_installment']) && is_numeric($sum['total_installment'])) {
                            $totalInstallment = (float) $sum['total_installment'];
                        }

                        $tSummary['total_full'] = round($totalFull, 2);
                        $tSummary['total_installment'] = $totalInstallment !== null ? round($totalInstallment, 2) : null;

                        $selected = $totalFull;
                        if ($paymentType === 'partial' && $totalInstallment !== null) {
                            $selected = $totalInstallment;
                        }
                        $tSummary['selected_total'] = round((float) $selected, 2);
                        $tSummary['source'] = 'saved';
                        // billing_total from saved payload excluding Reservation Fee lines (case-insensitive, prefix match)
                        $billingTotal = 0.0;
                        try {
                            $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
                            $billRows = is_array($items['billing'] ?? null) ? $items['billing'] : [];
                            if (!empty($billRows)) {
                                foreach ($billRows as $br) {
                                    $name = strtolower(trim((string) ($br['name'] ?? ($br['description'] ?? ''))));
                                    if ($name !== '' && str_starts_with($name, 'reservation')) {
                                        continue; // exclude reservation
                                    }
                                    $billingTotal += (float) ($br['amount'] ?? 0.0);
                                }
                                $billingTotal = round($billingTotal, 2);
                            } else {
                                // fallback to summary when itemized rows are not available
                                $billingTotal = (float) ($sum['billing_total'] ?? ($payload['billing_total'] ?? ($payload['meta']['billing_total'] ?? 0.0)));
                            }
                        } catch (\Throwable $e) {
                            $billingTotal = (float) ($sum['billing_total'] ?? ($payload['billing_total'] ?? ($payload['meta']['billing_total'] ?? 0.0)));
                        }
                        $usedSaved = true;
                    }
                }
            }

            // 2) Fallback to compute when no saved snapshot is available
            if (!$usedSaved) {
                $breakdown = $tuition->compute($sno, $syid, null, null);

                $sum = is_array($breakdown['summary'] ?? null) ? $breakdown['summary'] : [];
                $totalFull = (float) ($sum['total_due'] ?? 0.0);

                $inst = is_array($sum['installments'] ?? null) ? $sum['installments'] : [];
                $totalInstallment = null;
                if (isset($inst['total_installment']) && is_numeric($inst['total_installment'])) {
                    $totalInstallment = (float) $inst['total_installment'];
                } elseif (isset($sum['total_installment']) && is_numeric($sum['total_installment'])) {
                    $totalInstallment = (float) $sum['total_installment'];
                }

                $tSummary['total_full'] = round($totalFull, 2);
                $tSummary['total_installment'] = $totalInstallment !== null ? round($totalInstallment, 2) : null;

                $selected = $totalFull;
                if ($paymentType === 'partial' && $totalInstallment !== null) {
                    $selected = $totalInstallment;
                }
                $tSummary['selected_total'] = round((float) $selected, 2);
                $tSummary['source'] = 'computed';
                // billing_total from compute() payload excluding Reservation Fee when possible
                $billingTotal = 0.0;
                try {
                    $items = is_array($breakdown['items'] ?? null) ? $breakdown['items'] : [];
                    $billRows = is_array($items['billing'] ?? null) ? $items['billing'] : [];
                    if (!empty($billRows)) {
                        foreach ($billRows as $br) {
                            $name = strtolower(trim((string) ($br['name'] ?? ($br['description'] ?? ''))));
                            if ($name !== '' && str_starts_with($name, 'reservation')) {
                                continue; // exclude reservation
                            }
                            $billingTotal += (float) ($br['amount'] ?? 0.0);
                        }
                        $billingTotal = round($billingTotal, 2);
                    } else {
                        $billingTotal = (float) ($sum['billing_total'] ?? 0.0);
                    }
                } catch (\Throwable $e) {
                    $billingTotal = (float) ($sum['billing_total'] ?? 0.0);
                }
            }
        } catch (\Throwable $e) {
            // Fallback gracefully
            $tSummary['source'] = 'fallback';
            // Keep $billingTotal as 0.0 on failure
        }

        // Payments meta using StudentPaymentStatusService
        $paymentsMeta = [
            'total_paid' => 0.0,
            'last_payment_date' => null
        ];
        try {
            $bal = $paySvc->termBalance($sid, $syid);
            // Prefer payments_total when available; some services expose total_paid_filtered or similar
            $tp = $bal['payments_total'] ?? ($bal['total_paid'] ?? 0.0);
            $paymentsMeta['total_paid'] = round((float) $tp, 2);
            $paymentsMeta['last_payment_date'] = $bal['last_payment_date'] ?? null;
        } catch (\Throwable $e) {
            // keep defaults
        }

        // Override billing_total using Billing invoices remaining (exclude Reservation; sum only type='billing')
        try {
            $rows = $invoiceService->list([
                'student_id' => (int) $sid,
                'syid'       => (int) $syid
            ]);

            // Build invoice_number => type map for robust Application fee detection
            $invoiceTypeByNo = [];
            foreach ($rows as $r) {
                $no = $r['invoice_number'] ?? null;
                if ($no !== null && $no !== '') {
                    $invoiceTypeByNo[$no] = strtolower((string) ($r['type'] ?? ''));
                }
            }

            // Recompute total_paid to EXCLUDE Application-related payments (so Outstanding isn't reduced by them)
            try {
                $pd = $finance->listPaymentDetails($sno, $syid, $sid);
                $items = is_array($pd['items'] ?? null) ? $pd['items'] : [];
                $paidEffective = 0.0;
                $lastPaidDate = $paymentsMeta['last_payment_date'] ?? null;

                foreach ($items as $it) {
                    $st = strtolower((string) ($it['status'] ?? ''));
                    if ($st !== 'paid') continue;

                    $desc = strtolower(trim((string) ($it['description'] ?? '')));
                    $invNo = $it['invoice_number'] ?? null;

                    // Description-based application detection
                    $isAppDesc = (strpos($desc, 'application') !== false)
                              || (strpos($desc, 'app fee') !== false)
                              || (strpos($desc, 'admission fee') !== false)
                              || (strpos($desc, 'admissions fee') !== false);

                    // Invoice-type-based application detection
                    $invType = $invNo ? (strtolower((string) ($invoiceTypeByNo[$invNo] ?? ''))) : '';
                    $isAppInv = ($invType !== '' && strpos($invType, 'application') === 0);

                    if ($isAppDesc || $isAppInv) {
                        // Skip application-related payments from effective totals
                        continue;
                    }

                    $amt = isset($it['subtotal_order']) ? (float) $it['subtotal_order'] : 0.0;
                    $paidEffective += abs($amt);

                    $dt = $it['posted_at'] ?? null;
                    if ($dt && (!$lastPaidDate || $dt > $lastPaidDate)) {
                        $lastPaidDate = $dt;
                    }
                }

                $paymentsMeta['total_paid'] = round($paidEffective, 2);
                if ($lastPaidDate) {
                    $paymentsMeta['last_payment_date'] = $lastPaidDate;
                }
            } catch (\Throwable $e2) {
                // Keep paymentsMeta as-is if listPaymentDetails fails
            }

            // Billing Charges should include ALL billing invoice amounts (paid or not), excluding Reservation
            $sumAmt = 0.0;
            foreach ($rows as $r) {
                $typ = strtolower((string) ($r['type'] ?? ''));
                if ($typ !== 'billing') continue; // only billing invoices
                $amt = isset($r['amount_total']) ? (float) $r['amount_total'] : 0.0;
                if (is_numeric($amt)) {
                    $sumAmt += max(0.0, (float) $amt);
                }
            }
            $billingTotal = round($sumAmt, 2);
        } catch (\Throwable $e) {
            // Keep previously computed $billingTotal on failure
        }

        // Grand totals and outstanding including billing
        $billingTotal = round((float) $billingTotal, 2);
        $grandTotal = round(((float) $tSummary['selected_total']) + $billingTotal, 2);
        $outstanding = round(max(0.0, $grandTotal - (float) $paymentsMeta['total_paid']), 2);

        $resp = [
            'student_id' => $sid,
            'student_number' => $sno,
            'term' => [
                'id' => $syid,
                'label' => $termLabel
            ],
            'registration' => [
                'id' => $registrationId,
                'paymentType' => $paymentType
            ],
            'tuition' => $tSummary,
            'payments' => $paymentsMeta,
            // New fields for billing + grand totals
            'billing_total' => $billingTotal,
            'grand_total' => $grandTotal,
            'outstanding' => $outstanding,
            'meta' => [
                'data_source' => 'student_finances.summary',
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $resp
        ]);
    }

    /**
     * GET /api/v1/student/finances/invoices
     * Query: student_id|student_number, syid (int), include_draft=1|0
     * Returns InvoiceItem[] (student-safe subset).
     */
    public function invoices(Request $request, InvoiceService $invoiceService): JsonResponse
    {
        $syid = (int) $request->query('syid', 0);
        $studentId = $request->query('student_id');
        $studentNumber = $request->query('student_number');
        $includeDraft = (int) $request->query('include_draft', 1) === 1;

        if ($syid <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'syid (term) is required.'
            ], 422);
        }

        $filters = ['syid' => $syid];
        if (!empty($studentId)) {
            $filters['student_id'] = (int) $studentId;
        } elseif (!empty($studentNumber)) {
            $filters['student_number'] = (string) $studentNumber;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'student_id or student_number is required.'
            ], 422);
        }

        try {
            $rows = $invoiceService->list($filters);
            if (!$includeDraft) {
                $rows = array_values(array_filter($rows, function ($r) {
                    $st = strtolower((string) ($r['status'] ?? ''));
                    return $st !== 'draft';
                }));
            }

            // Augment with 'remaining' using InvoiceService helper when invoice_number is present
            $items = array_map(function ($r) use ($invoiceService) {
                $row = $r;
                $invNo = $r['invoice_number'] ?? null;
                if ($invNo !== null && $invNo !== '') {
                    try {
                        $row['remaining'] = $invoiceService->getInvoiceRemaining($invNo);
                    } catch (\Throwable $e) {
                        $row['remaining'] = null;
                    }
                } else {
                    $row['remaining'] = null;
                }
                return $row;
            }, $rows);

            return response()->json([
                'success' => true,
                'items' => $items
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load invoices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/student/finances/payment-modes
     * Returns active online payment modes (exclude Onsite).
     */
    public function paymentModes(Request $request): JsonResponse
    {
        try {
            $q = PaymentMode::query()
                ->where('is_active', 1)
                ->where('pmethod', '!=', 'onsite')
                ->orderBy('name', 'asc');

            $rows = $q->get()->map(function ($m) {
                return [
                    'id' => (int) $m->id,
                    'name' => (string) ($m->name ?? ''),
                    'type' => (string) ($m->type ?? ''), // 'fixed'|'percentage'
                    'charge' => (float) ($m->charge ?? 0),
                    'pmethod' => (string) ($m->pmethod ?? ''),
                    'pchannel' => (string) ($m->pchannel ?? ''),
                    'is_active' => (bool) ($m->is_active ?? false),
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'items' => $rows
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load payment modes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/student/finances/payments
     * Query: student_id|student_number, syid (int)
     * Returns PaymentDetail items (all statuses) with a student-safe subset of fields.
     */
    public function payments(Request $request, FinanceService $finance): JsonResponse
    {
        $syid = (int) $request->query('syid', 0);
        $studentId = $request->query('student_id');
        $studentNumber = $request->query('student_number');

        if ($syid <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'syid (term) is required.'
            ], 422);
        }

        $sid = null;
        $sno = null;

        if (!empty($studentId)) {
            $sid = (int) $studentId;
            $u = DB::table('tb_mas_users')->where('intID', $sid)->select('strStudentNumber')->first();
            if ($u) {
                $sno = (string) $u->strStudentNumber;
            }
        } elseif (!empty($studentNumber)) {
            $sno = (string) $studentNumber;
            $u = DB::table('tb_mas_users')->where('strStudentNumber', $sno)->select('intID')->first();
            if ($u) {
                $sid = (int) $u->intID;
            }
        }

        if (!$sid || !$sno) {
            return response()->json([
                'success' => false,
                'message' => 'student_id or student_number required (and must resolve to an existing student).'
            ], 422);
        }

        try {
            $pd = $finance->listPaymentDetails($sno, $syid, $sid);

            $items = array_map(function ($r) {
                $amount = isset($r['subtotal_order']) ? (float) $r['subtotal_order'] : 0.0;
                $amount = abs($amount);
                return [
                    'id'              => (int) ($r['id'] ?? 0),
                    'posted_at'       => $r['posted_at'] ?? null,
                    'description'     => $r['description'] ?? null,
                    'method'          => $r['method'] ?? null,
                    'or_number'       => $r['or_no'] ?? null,
                    'invoice_number'  => $r['invoice_number'] ?? null,
                    'amount'          => round($amount, 2),
                    'status'          => $r['status'] ?? null,
                ];
            }, $pd['items'] ?? []);

            $meta = [
                'total_paid_filtered'    => isset($pd['meta']['total_paid_filtered']) ? (float) $pd['meta']['total_paid_filtered'] : 0.0,
                'total_paid_all_status'  => isset($pd['meta']['total_paid_all_status']) ? (float) $pd['meta']['total_paid_all_status'] : 0.0,
                'total_all_rows'         => isset($pd['meta']['total_all_rows']) ? (float) $pd['meta']['total_all_rows'] : 0.0,
                'count_rows'             => isset($pd['meta']['count_rows']) ? (int) $pd['meta']['count_rows'] : 0,
            ];

            return response()->json([
                'success' => true,
                'items'   => $items,
                'meta'    => $meta,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load payments: ' . $e->getMessage()
            ], 500);
        }
    }
}
