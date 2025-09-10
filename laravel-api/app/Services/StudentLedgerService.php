<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentLedgerService
{
    /**
     * Return a unified ledger-like view composed from:
     * - SavedTuition (single "Tuition Assessment" per term)
     * - Student Billing (charges/credits)
     * - Payment Details (Paid payments)
     *
     * Request params:
     * - $studentNumber: optional when $studentId provided
     * - $studentId: optional when $studentNumber provided
     * - $term: 'all' | int (syid)
     * - $sort: 'asc'|'desc' (default 'asc')
     *
     * Response:
     * [
     *   'student_id'     => ?int,
     *   'student_number' => ?string,
     *   'scope'          => ['term' => 'all'|int, 'sy_label' => ?string],
     *   'meta'           => [
     *      'opening_balance'  => float,
     *      'total_assessment' => float,
     *      'total_payment'    => float,
     *      'closing_balance'  => float,
     *      'terms_included'   => int[]
     *   ],
     *   'rows'           => LedgerRow[]
     * ]
     *
     * LedgerRow:
     * - id: string (ref_type:source_id:syid)
     * - ref_type: 'tuition_assessment'|'billing'|'payment'
     * - syid: ?int
     * - sy_label: ?string
     * - posted_at: ?string
     * - or_no: string|int|null
     * - invoice_number: int|null
     * - assessment: ?float
     * - payment: ?float
     * - cashier_id: ?int
     * - cashier_name: ?string
     * - remarks: ?string
     * - source: 'saved_tuition'|'student_billing'|'payment_details'
     * - source_id: ?int
     */
    /**
     * @param string|int $term
     */
    public function getLedger(?string $studentNumber, ?int $studentId, $term = 'all', string $sort = 'asc'): array
    {
        $studentNumber = $studentNumber !== null ? (string) $studentNumber : null;
        $studentId = $this->resolveStudentId($studentNumber, $studentId);

        $scopeSyLabel = null;
        if ($term !== 'all' && is_numeric($term)) {
            $scopeSyLabel = $this->syLabel((int) $term);
        }

        if (!$studentId) {
            return [
                'student_id'     => null,
                'student_number' => $studentNumber,
                'scope'          => ['term' => $term, 'sy_label' => $scopeSyLabel],
                'meta'           => [
                    'opening_balance'  => 0.0,
                    'total_assessment' => 0.0,
                    'total_payment'    => 0.0,
                    'closing_balance'  => 0.0,
                    'terms_included'   => [],
                ],
                'rows' => [],
            ];
        }

        // Collect syids to include
        $syids = $term === 'all'
            ? $this->collectSyids($studentId)
            : (is_numeric($term) ? [(int) $term] : []);

        // Build labels map
        $syLabels = $this->buildSyLabelsMap($syids);

        // Aggregate rows
        $rows = [];
        // Saved Tuition (assessment per term)
        $rows = array_merge($rows, $this->fetchSavedTuitionRows($studentId, $syids, $syLabels));

        // Student Billing (charges/credits)
        $rows = array_merge($rows, $this->fetchBillingRows($studentId, $syids, $syLabels));

        // Payment Details (Paid payments)
        $rows = array_merge($rows, $this->fetchPaymentRows($studentId, $syids, $syLabels, $term));

        // Payment Details (Journal debits -> treated as assessment/charges)
        $rows = array_merge($rows, $this->fetchPaymentDebitRows($studentId, $syids, $syLabels, $term));

        // Include applied excess payments as negative payments in source term and positive payments in target term
        $excessApplications = DB::table('excess_payment_applications')
            ->where('student_id', $studentId)
            ->where('status', 'applied')
            ->get();

        foreach ($excessApplications as $app) {
            // Negative payment row in source term
            $rows[] = [
                'id' => 'excess_payment_source:' . $app->id . ':' . $app->source_term_id,
                'ref_type' => 'excess_payment_source',
                'syid' => $app->source_term_id,
                'sy_label' => $syLabels[$app->source_term_id] ?? null,
                'posted_at' => null,
                'or_no' => null,
                'invoice_number' => null,
                'assessment' => null,
                'payment' => round($app->amount, 2),
                'cashier_id' => null,
                'cashier_name' => 'Excess Payment Applied',
                'remarks' => 'Applied excess payment to term ' . ($syLabels[$app->target_term_id] ?? $app->target_term_id),
                'source' => 'excess_payment',
                'source_id' => $app->id,
            ];
            // Positive payment row in target term
            $rows[] = [
                'id' => 'excess_payment_target:' . $app->id . ':' . $app->target_term_id,
                'ref_type' => 'excess_payment_target',
                'syid' => $app->target_term_id,
                'sy_label' => $syLabels[$app->target_term_id] ?? null,
                'posted_at' => null,
                'or_no' => null,
                'invoice_number' => null,
                'assessment' => null,
                'payment' => round(-$app->amount, 2),
                'cashier_id' => null,
                'cashier_name' => 'Excess Payment Received',
                'remarks' => 'Received excess payment from term ' . ($syLabels[$app->source_term_id] ?? $app->source_term_id),
                'source' => 'excess_payment',
                'source_id' => $app->id,
            ];
        }

        // Sort
        $rows = $this->sortRows($rows, $sort);

        // Totals
        $totalAssessment = 0.0;
        $totalPayment = 0.0;
        foreach ($rows as $r) {
            if (isset($r['assessment']) && is_numeric($r['assessment'])) {
                $totalAssessment += (float) $r['assessment'];
            }
            if (isset($r['payment']) && is_numeric($r['payment'])) {
                $totalPayment += (float) $r['payment'];
            }
        }
        $totalAssessment = round($totalAssessment, 2);
        $totalPayment = round($totalPayment, 2);

        // Opening balance assumed 0 for this unified display
        $opening = 0.0;
        $closing = round($opening + $totalAssessment - $totalPayment, 2);

        return [
            'student_id'     => $studentId,
            'student_number' => $studentNumber,
            'scope'          => ['term' => $term, 'sy_label' => $scopeSyLabel],
            'meta'           => [
                'opening_balance'  => round($opening, 2),
                'total_assessment' => $totalAssessment,
                'total_payment'    => $totalPayment,
                'closing_balance'  => $closing,
                'terms_included'   => array_values($syids),
            ],
            'rows' => $rows,
        ];
    }


    protected function resolveStudentId(?string $studentNumber, ?int $studentId): ?int
    {
        if (!empty($studentId)) {
            return (int) $studentId;
        }
        if (!empty($studentNumber)) {
            try {
                $u = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
                return $u ? (int) $u->intID : null;
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }

    protected function syLabel(int $syid): ?string
    {
        try {
            $sy = DB::table('tb_mas_sy')->where('intID', $syid)->first();
            if (!$sy) return null;
            $enumSem = isset($sy->enumSem) ? trim((string)$sy->enumSem) : '';
            $ys = isset($sy->strYearStart) ? trim((string)$sy->strYearStart) : '';
            $ye = isset($sy->strYearEnd) ? trim((string)$sy->strYearEnd) : '';
            $parts = [];
            if ($enumSem !== '') $parts[] = $enumSem;
            if ($ys !== '' || $ye !== '') $parts[] = ($ys !== '' && $ye !== '') ? ($ys . '-' . $ye) : ($ys . $ye);
            return trim(implode(' ', $parts));
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function buildSyLabelsMap(array $syids): array
    {
        $map = [];
        foreach ($syids as $id) {
            $map[$id] = $this->syLabel((int)$id);
        }
        return $map;
    }

    protected function collectSyids(int $studentId): array
    {
        $out = [];

        // Saved Tuition
        try {
            if (Schema::hasTable('tb_mas_tuition_saved')) {
                $sy = DB::table('tb_mas_tuition_saved')
                    ->where('intStudentID', $studentId)
                    ->whereNotNull('syid')
                    ->distinct()
                    ->pluck('syid')
                    ->toArray();
                foreach ($sy as $id) {
                    if ($id !== null) $out[(int)$id] = (int)$id;
                }
            }
        } catch (\Throwable $e) {}

        // Student Billing
        try {
            if (Schema::hasTable('tb_mas_student_billing')) {
                $sy = DB::table('tb_mas_student_billing')
                    ->where('intStudentID', $studentId)
                    ->whereNotNull('syid')
                    ->distinct()
                    ->pluck('syid')
                    ->toArray();
                foreach ($sy as $id) {
                    if ($id !== null) $out[(int)$id] = (int)$id;
                }
            }
        } catch (\Throwable $e) {}

        // Payment Details (sy_reference)
        try {
            if (Schema::hasTable('payment_details') && Schema::hasColumn('payment_details', 'sy_reference')) {
                $sy = DB::table('payment_details')
                    ->where('student_information_id', $studentId)
                    ->whereNotNull('sy_reference')
                    ->distinct()
                    ->pluck('sy_reference')
                    ->toArray();
                foreach ($sy as $id) {
                    if ($id !== null && is_numeric($id)) $out[(int)$id] = (int)$id;
                }
            }
        } catch (\Throwable $e) {}

        ksort($out, SORT_NUMERIC);
        return array_values($out);
    }

    protected function fetchSavedTuitionRows(int $studentId, array $syids, array $syLabels): array
    {
        $rows = [];
        if (empty($syids)) return $rows;
        if (!Schema::hasTable('tb_mas_tuition_saved')) return $rows;

        foreach ($syids as $syid) {
            try {
                $saved = DB::table('tb_mas_tuition_saved')
                    ->where('intStudentID', $studentId)
                    ->where('syid', $syid)
                    ->orderBy('updated_at', 'desc')
                    ->orderBy('intID', 'desc')
                    ->first();

                if (!$saved) {
                    continue;
                }

                $total = $this->extractTuitionTotal($saved);
                if ($total <= 0) {
                    // Skip zero/negative totals
                    continue;
                }

                $postedAt = null;
                if (isset($saved->updated_at) && !empty($saved->updated_at)) {
                    $postedAt = (string) $saved->updated_at;
                } elseif (isset($saved->created_at)) {
                    $postedAt = (string) $saved->created_at;
                }

                // Try to enrich invoice_number from tb_mas_invoices
                $invoiceNumber = null;
                try {
                    if (Schema::hasTable('tb_mas_invoices')) {
                        $q = DB::table('tb_mas_invoices')
                            ->where('intStudentID', $studentId);

                        // Prefer exact syid match when available
                        if (Schema::hasColumn('tb_mas_invoices', 'syid')) {
                            $q->where('syid', $syid);
                        }

                        // Prefer type='tuition' when column exists
                        if (Schema::hasColumn('tb_mas_invoices', 'type')) {
                            $q->where('type', 'tuition');
                        }

                        $inv = $q->orderBy('updated_at', 'desc')
                            ->orderBy('intID', 'desc')
                            ->first();

                        if ($inv && isset($inv->invoice_number) && $inv->invoice_number !== null && $inv->invoice_number !== '') {
                            $invoiceNumber = (int) $inv->invoice_number;
                        }
                    }
                } catch (\Throwable $e) {
                    $invoiceNumber = null;
                }

                $rows[] = [
                    'id'             => 'tuition_assessment:' . ((int) ($saved->intID ?? 0)) . ':' . (int) $syid,
                    'ref_type'       => 'tuition_assessment',
                    'syid'           => (int) $syid,
                    'sy_label'       => $syLabels[$syid] ?? null,
                    'posted_at'      => $postedAt,
                    'or_no'          => null,
                    'invoice_number' => $invoiceNumber,
                    'detail'         => 'Tuition Fee',
                    'assessment'     => round((float) $total, 2),
                    'payment'        => null,
                    'cashier_id'     => null,
                    'cashier_name'   => null,
                    'remarks'        => 'Tuition Assessment',
                    'source'         => 'saved_tuition',
                    'source_id'      => isset($saved->intID) ? (int) $saved->intID : null,
                ];
            } catch (\Throwable $e) {
                // continue on per-term failure
            }
        }

        return $rows;
    }

    protected function fetchBillingRows(int $studentId, array $syids, array $syLabels): array
    {
        $rows = [];
        if (!Schema::hasTable('tb_mas_student_billing')) return $rows;

        $q = DB::table('tb_mas_student_billing')
            ->where('intStudentID', $studentId);

        if (!empty($syids)) {
            $q->whereIn('syid', $syids);
        }

        try {
            $res = $q->orderBy('posted_at', 'asc')
                ->orderBy('intID', 'asc')
                ->get();
        } catch (\Throwable $e) {
            $res = collect();
        }

        foreach ($res as $r) {
            $amt = isset($r->amount) ? (float) $r->amount : 0.0;
            $isAssessment = $amt > 0;
            $isPayment = $amt < 0;

            $rows[] = [
                'id'             => 'billing:' . (int) ($r->intID ?? 0) . ':' . (int) ($r->syid ?? 0),
                'ref_type'       => 'billing',
                'syid'           => isset($r->syid) ? (int) $r->syid : null,
                'sy_label'       => isset($r->syid) && isset($syLabels[$r->syid]) ? $syLabels[$r->syid] : null,
                'posted_at'      => isset($r->posted_at) ? (string) $r->posted_at : null,
                'or_no'          => null,
                'invoice_number' => null,
                'detail'         => isset($r->description) ? (string) $r->description : null,
                'assessment'     => $isAssessment ? round(abs($amt), 2) : null,
                'payment'        => $isPayment ? round(abs($amt), 2) : null,
                'cashier_id'     => null,
                'cashier_name'   => null,
                'remarks'        => isset($r->remarks) ? (string) $r->remarks : null,
                'source'         => 'student_billing',
                'source_id'      => isset($r->intID) ? (int) $r->intID : null,
            ];
        }

        return $rows;
    }

    /**
     * @param string|int $term
     */
    protected function fetchPaymentRows(int $studentId, array $syids, array $syLabels, $term): array
    {
        $rows = [];
        if (!Schema::hasTable('payment_details')) return $rows;

        // Detect important columns
        $orCol = null;
        if (Schema::hasColumn('payment_details', 'or_no')) {
            $orCol = 'or_no';
        } elseif (Schema::hasColumn('payment_details', 'or_number')) {
            $orCol = 'or_number';
        }

        $invoiceCol = Schema::hasColumn('payment_details', 'invoice_number') ? 'invoice_number' : null;
        $cashierIdCol = Schema::hasColumn('payment_details', 'cashier_id') ? 'cashier_id' : null;
        $createdByCol = Schema::hasColumn('payment_details', 'created_by') ? 'created_by' : null;

        // Prefer 'or_date' for display, fallback to paid_at/date/created_at
        $orDateCol = Schema::hasColumn('payment_details', 'or_date') ? 'or_date' : null;
        $dateCol   = $orDateCol ?: (Schema::hasColumn('payment_details', 'paid_at') ? 'paid_at'
                        : (Schema::hasColumn('payment_details', 'date') ? 'date'
                            : (Schema::hasColumn('payment_details', 'created_at') ? 'created_at' : null)));

        $q = DB::table('payment_details')
            ->where('student_information_id', $studentId)
            ->where('status', 'Paid');

        if ($term !== 'all' && is_numeric($term) && Schema::hasColumn('payment_details', 'sy_reference')) {
            $q->where('sy_reference', (int) $term);
        } elseif (!empty($syids) && Schema::hasColumn('payment_details', 'sy_reference')) {
            // When term=all, include all sy_reference; no filter needed (allows payments with null sy_reference)
        }

        // Build select
        $select = ['id', 'description', 'subtotal_order', 'status'];
        if ($orCol) $select[] = $orCol . ' as or_no';
        if ($invoiceCol) $select[] = $invoiceCol . ' as invoice_number';
        if ($cashierIdCol) $select[] = $cashierIdCol . ' as cashier_id';
        if ($createdByCol) $select[] = $createdByCol . ' as created_by';
        if ($dateCol) $select[] = $dateCol . ' as posted_at';
        if (Schema::hasColumn('payment_details', 'sy_reference')) $select[] = 'sy_reference';

        try {
            // Order by date asc then number asc for predictable running balance order
            if ($dateCol) $q->orderBy($dateCol, 'asc');
            if ($orCol) $q->orderBy($orCol, 'asc');
            $q->orderBy('id', 'asc');

            $res = $q->select($select)->get();
        } catch (\Throwable $e) {
            $res = collect();
        }

        foreach ($res as $r) {
            $syid = isset($r->sy_reference) && is_numeric($r->sy_reference) ? (int) $r->sy_reference : null;

            // Resolve cashier name from mapping
            $cashierId = isset($r->cashier_id) ? (int) $r->cashier_id : null;
            $createdBy = isset($r->created_by) ? (int) $r->created_by : null;
            $cashierName = $this->resolveCashierName($cashierId, $createdBy);

            $rows[] = [
                'id'             => 'payment:' . (int) ($r->id ?? 0) . ':' . (int) ($syid ?? 0),
                'ref_type'       => 'payment',
                'syid'           => $syid,
                'sy_label'       => ($syid !== null && isset($syLabels[$syid])) ? $syLabels[$syid] : null,
                'posted_at'      => isset($r->posted_at) ? (string) $r->posted_at : null,
                'or_no'          => isset($r->or_no) ? $r->or_no : null,
                'invoice_number' => isset($r->invoice_number) ? (int) $r->invoice_number : null,
                'detail'         => isset($r->description) ? (string) $r->description : null,
                'assessment'     => null,
                'payment'        => isset($r->subtotal_order) ? round((float) $r->subtotal_order, 2) : 0.0,
                'cashier_id'     => $cashierId,
                'cashier_name'   => $cashierName,
                'remarks'        => isset($r->description) ? (string) $r->description : null,
                'source'         => 'payment_details',
                'source_id'      => isset($r->id) ? (int) $r->id : null,
            ];
        }

        return $rows;
    }

    /**
     * Fetch debit adjustments from payment_details and map them as assessment (charges) rows.
     * Criteria:
     *  - student_information_id = $studentId
     *  - When status column exists: status='Journal'
     *  - Fallback: subtotal_order < 0
     *  - Optional sy_reference filtering (term or all)
     */
    protected function fetchPaymentDebitRows(int $studentId, array $syids, array $syLabels, $term): array
    {
        $rows = [];
        if (!Schema::hasTable('payment_details')) return $rows;

        // Detect important columns
        $invoiceCol = Schema::hasColumn('payment_details', 'invoice_number') ? 'invoice_number' : null;

        // Prefer 'or_date' for display, fallback to paid_at/date/created_at
        $orDateCol = Schema::hasColumn('payment_details', 'or_date') ? 'or_date' : null;
        $dateCol   = $orDateCol ?: (Schema::hasColumn('payment_details', 'paid_at') ? 'paid_at'
                        : (Schema::hasColumn('payment_details', 'date') ? 'date'
                            : (Schema::hasColumn('payment_details', 'created_at') ? 'created_at' : null)));

        $hasStatus = Schema::hasColumn('payment_details', 'status');

        $q = DB::table('payment_details')
            ->where('student_information_id', $studentId);

        if ($hasStatus) {
            $q->where('status', 'Journal');
        } else {
            // Fallback: negative amount rows are treated as debit adjustments
            $q->where('subtotal_order', '<', 0);
        }

        if ($term !== 'all' && is_numeric($term) && Schema::hasColumn('payment_details', 'sy_reference')) {
            $q->where('sy_reference', (int) $term);
        } elseif (!empty($syids) && Schema::hasColumn('payment_details', 'sy_reference')) {
            // When term=all, include all sy_reference; no filter needed
        }

        // Build select
        $select = ['id', 'description', 'subtotal_order'];
        if ($invoiceCol) $select[] = $invoiceCol . ' as invoice_number';
        if ($dateCol) $select[] = $dateCol . ' as posted_at';
        if (Schema::hasColumn('payment_details', 'sy_reference')) $select[] = 'sy_reference';

        try {
            // Order by date asc then id for stable running balance order
            if ($dateCol) $q->orderBy($dateCol, 'asc');
            $q->orderBy('id', 'asc');

            $res = $q->select($select)->get();
        } catch (\Throwable $e) {
            $res = collect();
        }

        foreach ($res as $r) {
            $amt = isset($r->subtotal_order) ? (float) $r->subtotal_order : 0.0;
            if ($amt >= 0) {
                // Guard: only keep true debits in fallback path
                if (!$hasStatus) continue;
            }
            $syid = isset($r->sy_reference) && is_numeric($r->sy_reference) ? (int) $r->sy_reference : null;

            $rows[] = [
                'id'             => 'payment_debit:' . (int) ($r->id ?? 0) . ':' . (int) ($syid ?? 0),
                'ref_type'       => 'billing', // treat as charge
                'syid'           => $syid,
                'sy_label'       => ($syid !== null && isset($syLabels[$syid])) ? $syLabels[$syid] : null,
                'posted_at'      => isset($r->posted_at) ? (string) $r->posted_at : null,
                'or_no'          => null,
                'invoice_number' => isset($r->invoice_number) ? (int) $r->invoice_number : null,
                'detail'         => isset($r->description) ? (string) $r->description : 'Debit Adjustment',
                'assessment'     => round(abs($amt), 2),
                'payment'        => null,
                'cashier_id'     => null,
                'cashier_name'   => null,
                'remarks'        => isset($r->description) ? (string) $r->description : 'Debit Adjustment',
                'source'         => 'payment_details',
                'source_id'      => isset($r->id) ? (int) $r->id : null,
            ];
        }

        return $rows;
    }

    protected function sortRows(array $rows, string $sort): array
    {
        $asc = strtolower($sort) !== 'desc';

        usort($rows, function ($a, $b) use ($asc) {
            $da = $a['posted_at'] ?? null;
            $db = $b['posted_at'] ?? null;

            if ($da === $db) {
                // Tie-breaker: OR number then invoice then id string
                $oa = $a['or_no'] ?? null;
                $ob = $b['or_no'] ?? null;
                if ($oa !== $ob) {
                    if ($oa === null) return $asc ? -1 : 1;
                    if ($ob === null) return $asc ? 1 : -1;
                    // Try numeric compare if both numeric-like
                    if (is_numeric($oa) && is_numeric($ob)) {
                        return $asc ? ((int)$oa <=> (int)$ob) : ((int)$ob <=> (int)$oa);
                    }
                    return $asc ? strcmp((string)$oa, (string)$ob) : strcmp((string)$ob, (string)$oa);
                }

                $ia = $a['invoice_number'] ?? null;
                $ib = $b['invoice_number'] ?? null;
                if ($ia !== $ib) {
                    if ($ia === null) return $asc ? -1 : 1;
                    if ($ib === null) return $asc ? 1 : -1;
                    return $asc ? ((int)$ia <=> (int)$ib) : ((int)$ib <=> (int)$ia);
                }

                // Fallback: id string compare
                return $asc ? strcmp((string)$a['id'], (string)$b['id']) : strcmp((string)$b['id'], (string)$a['id']);
            }

            if ($da === null) return $asc ? -1 : 1;
            if ($db === null) return $asc ? 1 : -1;

            return $asc ? strcmp((string)$da, (string)$db) : strcmp((string)$db, (string)$da);
        });

        return $rows;
    }

    protected function extractTuitionTotal(object $saved): float
    {
        // payload can be array or JSON string; attempt multiple keys
        $payload = null;
        if (isset($saved->payload)) {
            if (is_array($saved->payload)) {
                $payload = $saved->payload;
            } else {
                $decoded = json_decode((string) $saved->payload, true);
                if (is_array($decoded)) $payload = $decoded;
            }
        }

        if (!is_array($payload)) {
            // Fallback keys on the row
            $candidates = [
                'total', 'grand_total', 'total_due', 'amount_total',
            ];
            foreach ($candidates as $k) {
                if (isset($saved->{$k}) && is_numeric($saved->{$k})) {
                    return round(max(0.0, (float) $saved->{$k}), 2);
                }
            }
            return 0.0;
        }

        // Try common known keys
        $tryKeys = [
            'total_due',
            'total',
            'grand_total',
            'amountPayable',
            'totalPayable',
            'totals.totalPayable',
            'summary.total_due',
            'summary.total',
        ];

        foreach ($tryKeys as $k) {
            $val = $this->arrayGetDot($payload, $k);
            if (is_numeric($val)) {
                return round(max(0.0, (float) $val), 2);
            }
        }

        // Fallback: sum items arrays
        $itemsKeys = [
            'items',
            'breakdown',
            'charges',
            'lines',
            'details',
            'totals.items',
            'summary.items',
        ];
        $sum = 0.0;
        foreach ($itemsKeys as $k) {
            $arr = $this->arrayGetDot($payload, $k);
            if (is_array($arr)) {
                foreach ($arr as $line) {
                    if (is_array($line)) {
                        $amt = null;
                        if (isset($line['amount']) && is_numeric($line['amount'])) {
                            $amt = (float) $line['amount'];
                        } elseif (isset($line['value']) && is_numeric($line['value'])) {
                            $amt = (float) $line['value'];
                        }
                        if ($amt !== null) $sum += (float) $amt;
                    }
                }
                if ($sum > 0.0) break;
            }
        }
        return round(max(0.0, $sum), 2);
    }

    protected function resolveCashierName(?int $cashierId, ?int $createdBy): ?string
    {
        // Primary: tb_mas_cashiers -> tb_mas_faculty name
        if ($cashierId !== null && Schema::hasTable('tb_mas_cashiers')) {
            try {
                $row = DB::table('tb_mas_cashiers as c')
                    ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'c.faculty_id')
                    ->where('c.intID', $cashierId)
                    ->select('f.strFirstname as first', 'f.strLastname as last')
                    ->first();
                if ($row) {
                    $first = isset($row->first) ? trim((string)$row->first) : '';
                    $last  = isset($row->last) ? trim((string)$row->last) : '';
                    $name = trim($first . ' ' . $last);
                    if ($name !== '') return $name;
                }
            } catch (\Throwable $e) {
                // ignore and fallback
            }
        }

        // Fallback: created_by -> tb_mas_faculty (if created_by is faculty id) or tb_mas_users
        if ($createdBy !== null) {
            // Try faculty
            if (Schema::hasTable('tb_mas_faculty')) {
                try {
                    $f = DB::table('tb_mas_faculty')
                        ->where('intID', $createdBy)
                        ->select('strFirstname as first', 'strLastname as last')
                        ->first();
                    if ($f) {
                        $first = isset($f->first) ? trim((string)$f->first) : '';
                        $last  = isset($f->last) ? trim((string)$f->last) : '';
                        $name = trim($first . ' ' . $last);
                        if ($name !== '') return $name;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // Try users table for name fields
            if (Schema::hasTable('tb_mas_users')) {
                try {
                    $u = DB::table('tb_mas_users')
                        ->where('intID', $createdBy)
                        ->select('strFirstname as first', 'strLastname as last')
                        ->first();
                    if ($u) {
                        $first = isset($u->first) ? trim((string)$u->first) : '';
                        $last  = isset($u->last) ? trim((string)$u->last) : '';
                        $name = trim($first . ' ' . $last);
                        if ($name !== '') return $name;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        return null;
    }

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
