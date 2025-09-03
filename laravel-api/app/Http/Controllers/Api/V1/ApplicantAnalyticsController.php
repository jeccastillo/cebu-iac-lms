<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplicantAnalyticsController extends Controller
{
    /**
     * GET /api/v1/applicants/analytics/summary
     *
     * Query params:
     * - syid: int (required) primary term id
     * - compare_syid: int (optional) secondary term id for side-by-side
     * - start: Y-m-d (optional; default = 30 days before today)
     * - end: Y-m-d (optional; default = today)
     * - campus: string|int (optional) filter by campus name or id
     * - status: string (optional)
     * - type: string (optional) ApplicantType.type
     * - sub_type: string (optional) ApplicantType.sub_type
     * - search: string (optional) fuzzy search in user name/email/mobile
     */
    public function summary(Request $request): JsonResponse
    {
        // Accept multi-term arrays with backward compatibility to single syid
        $primarySyids = $this->parseIdsList($request->query('syids', $request->query('syids[]', null)));
        $compareSyids = $this->parseIdsList($request->query('compare_syids', $request->query('compare_syids[]', null)));

        $primarySyid = $this->toIntOrNull($request->query('syid', null));
        if (empty($primarySyids) && $primarySyid === null) {
            return response()->json([
                'success' => false,
                'message' => 'Either parameter "syid" (int) or "syids" (array/CSV of ints) is required.',
            ], 422);
        }

        $compareSyid = $this->toIntOrNull($request->query('compare_syid', null));
        [$startDate, $endDate] = $this->resolveDateRange(
            $request->query('start', null),
            $request->query('end', null)
        );

        $filters = [
            'campus'   => $request->query('campus', null),
            'status'   => $request->query('status', null),
            'type'     => $request->query('type', null),
            'sub_type' => $request->query('sub_type', null),
            'search'   => $request->query('search', null),
        ];

        $data = [
            'terms' => [],
            'meta'  => [
                'primary_syid'  => $primarySyid,
                'compare_syid'  => $compareSyid,
                'primary_syids' => !empty($primarySyids) ? $primarySyids : null,
                'compare_syids' => !empty($compareSyids) ? $compareSyids : null,
                'date_range'    => ['start' => $startDate, 'end' => $endDate],
            ],
        ];

        if (!empty($primarySyids)) {
            $data['terms']['__combined_A__'] = $this->computeCombinedSummary($primarySyids, $startDate, $endDate, $filters);
        } else {
            $data['terms'][(string)$primarySyid] = $this->computeSummary($primarySyid, $startDate, $endDate, $filters);
        }

        if (!empty($compareSyids)) {
            $data['terms']['__combined_B__'] = $this->computeCombinedSummary($compareSyids, $startDate, $endDate, $filters);
        } elseif ($compareSyid !== null) {
            $data['terms'][(string)$compareSyid] = $this->computeSummary($compareSyid, $startDate, $endDate, $filters);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Compute the aggregated analytics for a single term (syid).
     */
    protected function computeSummary(int $syid, string $start, string $end, array $filters): array
    {
        $hasSyidColumn = Schema::hasTable('tb_mas_applicant_data')
            && Schema::hasColumn('tb_mas_applicant_data', 'syid');

        // Base scope: latest applicant_data per user for this term (preferred using ad.syid column)
        // Fallback: when syid column missing, use latest per user and then filter by created_at date only.
        $base = DB::table('tb_mas_applicant_data as ad')
            ->join('tb_mas_users as u', 'u.intID', '=', 'ad.user_id')
            ->leftJoin('tb_mas_campuses as c', 'c.id', '=', 'u.campus_id')
            ->leftJoin('tb_mas_applicant_types as t', 't.intID', '=', 'ad.applicant_type');

        if ($hasSyidColumn) {
            $base->where('ad.syid', $syid)
                ->whereRaw(
                    'ad.id = (SELECT MAX(ad2.id) FROM tb_mas_applicant_data ad2 WHERE ad2.user_id = ad.user_id AND ad2.syid = ?)',
                    [$syid]
                );
        } else {
            // No syid column: latest per user only (term filter limited to date window metrics).
            $base->whereRaw(
                'ad.id = (SELECT MAX(ad2.id) FROM tb_mas_applicant_data ad2 WHERE ad2.user_id = ad.user_id)'
            );
        }

        // Apply common filters
        $this->applyFilters($base, $filters);

        // Clone for reuse
        $rowsForStatus    = (clone $base);
        $rowsForTypes     = (clone $base);
        $rowsForSubTypes  = (clone $base);
        $rowsForCampus    = (clone $base);
        $rowsForPayments  = (clone $base);
        $rowsForWaivers   = (clone $base);
        $rowsForTotal     = (clone $base);
        $rowsForTimeserie = (clone $base);

        // Totals
        $totalApplicants = (int) $rowsForTotal
            ->count(DB::raw('distinct ad.user_id'));

        // by_status
        $byStatus = $rowsForStatus
            ->selectRaw('ad.status as label, COUNT(DISTINCT ad.user_id) as cnt')
            ->groupBy('ad.status')
            ->get()
            ->map(function ($r) {
                return ['label' => (string) ($r->label ?? 'unknown'), 'count' => (int) $r->cnt];
            })->toArray();

        // by_applicant_type (t.type)
        $byApplicantType = $rowsForTypes
            ->selectRaw('COALESCE(t.type, "unknown") as label, COUNT(DISTINCT ad.user_id) as cnt')
            ->groupBy('t.type')
            ->get()
            ->map(function ($r) {
                return ['label' => (string) ($r->label ?? 'unknown'), 'count' => (int) $r->cnt];
            })->toArray();

        // by_applicant_sub_type (t.sub_type)
        $byApplicantSubType = $rowsForSubTypes
            ->selectRaw('COALESCE(t.sub_type, "unknown") as label, COUNT(DISTINCT ad.user_id) as cnt')
            ->groupBy('t.sub_type')
            ->get()
            ->map(function ($r) {
                return ['label' => (string) ($r->label ?? 'unknown'), 'count' => (int) $r->cnt];
            })->toArray();

        // by_campus (prefer campus_name else campus_id)
        $byCampus = $rowsForCampus
            ->selectRaw('COALESCE(c.campus_name, CAST(u.campus_id as CHAR), "unknown") as label, COUNT(DISTINCT ad.user_id) as cnt')
            ->groupBy('c.campus_name', 'u.campus_id')
            ->get()
            ->map(function ($r) {
                return ['label' => (string) ($r->label ?? 'unknown'), 'count' => (int) $r->cnt];
            })->toArray();

        // payment flags
        $paymentsRow = $rowsForPayments
            ->selectRaw('SUM(CASE WHEN ad.paid_application_fee = 1 THEN 1 ELSE 0 END) as paid_app, SUM(CASE WHEN ad.paid_reservation_fee = 1 THEN 1 ELSE 0 END) as paid_res')
            ->first();
        $paymentFlags = [
            'paid_application_fee' => (int) ($paymentsRow->paid_app ?? 0),
            'paid_reservation_fee' => (int) ($paymentsRow->paid_res ?? 0),
        ];

        // waivers
        $waiversRow = $rowsForWaivers
            ->selectRaw('SUM(CASE WHEN ad.waive_application_fee = 1 THEN 1 ELSE 0 END) as waived_cnt')
            ->first();
        $waivers = [
            'waive_application_fee' => (int) ($waiversRow->waived_cnt ?? 0),
        ];

        // timeseries (daily new applications within [start,end])
        $timeseries = $rowsForTimeserie
            ->whereBetween('ad.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->selectRaw('DATE(ad.created_at) as d, COUNT(DISTINCT ad.user_id) as cnt')
            ->groupBy(DB::raw('DATE(ad.created_at)'))
            ->orderBy('d', 'asc')
            ->get()
            ->map(function ($r) {
                return ['date' => (string) $r->d, 'count' => (int) $r->cnt];
            })->toArray();

        // Convert arrays (label,count) to associative maps for easier FE usage
        $statusMap   = $this->pairsToAssocMap($byStatus);
        $typeMap     = $this->pairsToAssocMap($byApplicantType);
        $subTypeMap  = $this->pairsToAssocMap($byApplicantSubType);
        $campusMap   = $this->pairsToAssocMap($byCampus);

        return [
            'syid'   => $syid,
            'counts' => [
                'total_applicants'       => $totalApplicants,
                'by_status'              => $statusMap,
                'by_applicant_type'      => $typeMap,
                'by_applicant_sub_type'  => $subTypeMap,
                'by_campus'              => $campusMap,
                'payment_flags'          => $paymentFlags,
                'waivers'                => $waivers,
            ],
            'timeseries' => [
                'daily_new_applications' => $timeseries,
            ],
        ];
    }

    /**
     * Apply shared filters: campus, status, type, sub_type, search.
     */
    protected function applyFilters($query, array $filters): void
    {
        // Campus filter can be name or id
        if ($filters['campus'] !== null && $filters['campus'] !== '') {
            $campus = $filters['campus'];
            $query->where(function ($w) use ($campus) {
                $w->where('c.campus_name', $campus)
                  ->orWhere('u.campus_id', $campus);
            });
        }

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $query->where('ad.status', $filters['status']);
        }

        if ($filters['type'] !== null && $filters['type'] !== '') {
            $query->where('t.type', $filters['type']);
        }

        if ($filters['sub_type'] !== null && $filters['sub_type'] !== '') {
            $query->where('t.sub_type', $filters['sub_type']);
        }

        if ($filters['search'] !== null && $filters['search'] !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $filters['search']) . '%';
            $query->where(function ($w) use ($like) {
                $w->where('u.strFirstname', 'like', $like)
                  ->orWhere('u.strLastname', 'like', $like)
                  ->orWhere('u.strEmail', 'like', $like)
                  ->orWhere('u.strMobileNumber', 'like', $like);
            });
        }
    }

    /**
     * Convert key/value pair arrays into associative map: label => count
     */
    protected function pairsToAssocMap(array $pairs): array
    {
        $out = [];
        foreach ($pairs as $row) {
            $label = (string) ($row['label'] ?? 'unknown');
            $count = (int) ($row['count'] ?? 0);
            $out[$label] = $count;
        }
        return $out;
    }

    /**
     * Compute the aggregated analytics for multiple terms (combined).
     * For databases with ad.syid column, this aggregates per latest (user_id, syid) record
     * and sums across selected terms. If syid column is missing, falls back to latest per user.
     */
    protected function computeCombinedSummary(array $syids, string $start, string $end, array $filters): array
    {
        $syids = array_values(array_unique(array_map('intval', $syids)));
        if (empty($syids)) {
            return [
                'syids'   => [],
                'counts'  => [
                    'total_applicants'      => 0,
                    'by_status'             => [],
                    'by_applicant_type'     => [],
                    'by_applicant_sub_type' => [],
                    'by_campus'             => [],
                    'payment_flags'         => ['paid_application_fee' => 0, 'paid_reservation_fee' => 0],
                    'waivers'               => ['waive_application_fee' => 0],
                ],
                'timeseries' => ['daily_new_applications' => []],
                'combined'   => true,
            ];
        }

        $hasSyidColumn = Schema::hasTable('tb_mas_applicant_data')
            && Schema::hasColumn('tb_mas_applicant_data', 'syid');

        $base = DB::table('tb_mas_applicant_data as ad')
            ->join('tb_mas_users as u', 'u.intID', '=', 'ad.user_id')
            ->leftJoin('tb_mas_campuses as c', 'c.id', '=', 'u.campus_id')
            ->leftJoin('tb_mas_applicant_types as t', 't.intID', '=', 'ad.applicant_type');

        if ($hasSyidColumn) {
            $base->whereIn('ad.syid', $syids)
                ->whereRaw(
                    // keep only latest record per (user_id, syid)
                    'ad.id = (SELECT MAX(ad2.id) FROM tb_mas_applicant_data ad2 WHERE ad2.user_id = ad.user_id AND ad2.syid = ad.syid)'
                );
        } else {
            // Fallback: latest per user only (combined semantics limited without syid column)
            $base->whereRaw(
                'ad.id = (SELECT MAX(ad2.id) FROM tb_mas_applicant_data ad2 WHERE ad2.user_id = ad.user_id)'
            );
        }

        // Apply shared filters
        $this->applyFilters($base, $filters);

        // Clone for reuse
        $rowsForStatus    = (clone $base);
        $rowsForTypes     = (clone $base);
        $rowsForSubTypes  = (clone $base);
        $rowsForCampus    = (clone $base);
        $rowsForPayments  = (clone $base);
        $rowsForWaivers   = (clone $base);
        $rowsForTotal     = (clone $base);
        $rowsForTimeserie = (clone $base);

        // Totals (one row per latest (user,syid) when syid column exists; else per latest user)
        $totalApplicants = (int) $rowsForTotal->count();

        // by_status
        $byStatus = $rowsForStatus
            ->selectRaw('ad.status as label, COUNT(*) as cnt')
            ->groupBy('ad.status')
            ->get()
            ->map(function ($r) {
                return ['label' => (string) ($r->label ?? 'unknown'), 'count' => (int) $r->cnt];
            })->toArray();

        // by_applicant_type (t.type)
        $byApplicantType = $rowsForTypes
            ->selectRaw('COALESCE(t.type, "unknown") as label, COUNT(*) as cnt')
            ->groupBy('t.type')
            ->get()
            ->map(function ($r) {
                return ['label' => (string) ($r->label ?? 'unknown'), 'count' => (int) $r->cnt];
            })->toArray();

        // by_applicant_sub_type (t.sub_type)
        $byApplicantSubType = $rowsForSubTypes
            ->selectRaw('COALESCE(t.sub_type, "unknown") as label, COUNT(*) as cnt')
            ->groupBy('t.sub_type')
            ->get()
            ->map(function ($r) {
                return ['label' => (string) ($r->label ?? 'unknown'), 'count' => (int) $r->cnt];
            })->toArray();

        // by_campus
        $byCampus = $rowsForCampus
            ->selectRaw('COALESCE(c.campus_name, CAST(u.campus_id as CHAR), "unknown") as label, COUNT(*) as cnt')
            ->groupBy('c.campus_name', 'u.campus_id')
            ->get()
            ->map(function ($r) {
                return ['label' => (string) ($r->label ?? 'unknown'), 'count' => (int) $r->cnt];
            })->toArray();

        // payment flags
        $paymentsRow = $rowsForPayments
            ->selectRaw('SUM(CASE WHEN ad.paid_application_fee = 1 THEN 1 ELSE 0 END) as paid_app, SUM(CASE WHEN ad.paid_reservation_fee = 1 THEN 1 ELSE 0 END) as paid_res')
            ->first();
        $paymentFlags = [
            'paid_application_fee' => (int) ($paymentsRow->paid_app ?? 0),
            'paid_reservation_fee' => (int) ($paymentsRow->paid_res ?? 0),
        ];

        // waivers
        $waiversRow = $rowsForWaivers
            ->selectRaw('SUM(CASE WHEN ad.waive_application_fee = 1 THEN 1 ELSE 0 END) as waived_cnt')
            ->first();
        $waivers = [
            'waive_application_fee' => (int) ($waiversRow->waived_cnt ?? 0),
        ];

        // timeseries (daily new applications within [start,end])
        $timeseries = $rowsForTimeserie
            ->whereBetween('ad.created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->selectRaw('DATE(ad.created_at) as d, COUNT(*) as cnt')
            ->groupBy(DB::raw('DATE(ad.created_at)'))
            ->orderBy('d', 'asc')
            ->get()
            ->map(function ($r) {
                return ['date' => (string) $r->d, 'count' => (int) $r->cnt];
            })->toArray();

        // Convert arrays (label,count) to associative maps
        $statusMap   = $this->pairsToAssocMap($byStatus);
        $typeMap     = $this->pairsToAssocMap($byApplicantType);
        $subTypeMap  = $this->pairsToAssocMap($byApplicantSubType);
        $campusMap   = $this->pairsToAssocMap($byCampus);

        return [
            'syids'  => $syids,
            'counts' => [
                'total_applicants'       => $totalApplicants,
                'by_status'              => $statusMap,
                'by_applicant_type'      => $typeMap,
                'by_applicant_sub_type'  => $subTypeMap,
                'by_campus'              => $campusMap,
                'payment_flags'          => $paymentFlags,
                'waivers'                => $waivers,
            ],
            'timeseries' => [
                'daily_new_applications' => $timeseries,
            ],
            'combined' => true,
        ];
    }

    /**
     * Parse IDs array or CSV string into a unique integer array.
     */
    protected function parseIdsList($val): array
    {
        if ($val === null || $val === '') {
            return [];
        }
        $arr = is_array($val) ? $val : preg_split('/[,\s]+/', (string) $val, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        foreach ($arr as $v) {
            if (is_numeric($v)) {
                $out[] = (int) $v;
            }
        }
        return array_values(array_unique($out));
    }

    /**
     * Resolve date range from inputs with sensible defaults.
     */
    protected function resolveDateRange($startIn, $endIn): array
    {
        $today = date('Y-m-d');
        $defaultStart = date('Y-m-d', strtotime('-30 days'));

        $start = $this->toDateOrNull($startIn) ?? $defaultStart;
        $end   = $this->toDateOrNull($endIn) ?? $today;

        if ($start > $end) {
            // swap
            [$start, $end] = [$end, $start];
        }
        return [$start, $end];
    }

    protected function toIntOrNull($val): ?int
    {
        if ($val === null || $val === '') {
            return null;
        }
        return is_numeric($val) ? (int) $val : null;
    }

    protected function toDateOrNull($val): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }
        $ts = strtotime((string)$val);
        return $ts ? date('Y-m-d', $ts) : null;
    }
}
