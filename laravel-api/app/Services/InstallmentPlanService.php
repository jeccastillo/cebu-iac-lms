<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class InstallmentPlanService
{
    /**
     * Return active installment plans for a tuition year, optionally filtered by level (college|shs).
     * Result rows are normalized arrays sorted by sort_order, then code.
     *
     * @param int $tuitionYearId
     * @param string|null $level
     * @return array<int,array<string,mixed>>
     */
    public function listByTuitionYear(int $tuitionYearId, ?string $level = null): array
    {
        $rows = DB::table('tb_mas_tuition_year_installment')
            ->where('tuitionyear_id', $tuitionYearId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('code', 'asc')
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();

        $levelNorm = $level ? strtolower(trim($level)) : null;
        if ($levelNorm && in_array($levelNorm, ['college','shs'], true)) {
            $rows = array_values(array_filter($rows, function (array $r) use ($levelNorm) {
                $lv = isset($r['level']) ? strtolower((string)$r['level']) : null;
                // Include when level is null/empty, 'both', or matches requested level
                return ($lv === null || $lv === '' || $lv === 'both' || $lv === $levelNorm);
            }));
        }

        return $rows;
    }

    /**
     * Compute installment totals for a specific plan.
     * - increase_percent applies to tuition and lab only (misc/additional unchanged).
     * - Discounts/scholarships provided in totals are subtracted after increases.
     * - Down payment is either a fixed amount (capped at net) or percent of net.
     * - Per-installment fee divides the remaining (net - dp) by installment_count (guarded).
     *
     * @param array<string,float> $totals keys: tuition, lab, misc, additional, discount_total, scholarship_total
     * @param array<string,mixed> $plan   keys: id, code, label, dp_type, dp_value, increase_percent, installment_count
     * @param string $level               normalized level: college|shs
     * @param int|null $yearLevel
     * @return array<string,mixed> { id, code, label, increase_percent, installment_count, total_installment, down_payment, installment_fee }
     */
    public function computePlan(array $totals, array $plan, string $level, ?int $yearLevel): array
    {
        $tuition  = (float) ($totals['tuition'] ?? 0);
        $lab      = (float) ($totals['lab'] ?? 0);
        $misc     = (float) ($totals['misc'] ?? 0);
        $additional = (float) ($totals['additional'] ?? 0);
        $disc     = (float) ($totals['discount_total'] ?? 0);
        $sch      = (float) ($totals['scholarship_total'] ?? 0);        

        $increase = max(0.0, (float) ($plan['increase_percent'] ?? 0)) / 100.0;

        // Apply plan increase on tuition and lab only
        $tuition_i = $tuition + ($tuition * $increase);
        $lab_i     = $lab + ($lab * $increase);

        $gross = $tuition_i + $lab_i + $misc + $additional;
        $net   = max(0.0, round($gross - $disc - $sch, 2));

        $dpType  = (string) ($plan['dp_type'] ?? 'percent');
        $dpValue = (float) ($plan['dp_value'] ?? 0);
        $count   = (int) ($plan['installment_count'] ?? 5);
        if ($count < 0) $count = 0; // allow 0 → all in DP

        // Down payment
        if ($dpType === 'fixed') {
            $dp = min($net, max(0.0, $dpValue));
        } else {
            // percent
            $pct = max(0.0, min(100.0, $dpValue)) / 100.0;
            $dp = $net * $pct;
        }

        // SHS special case (legacy rule): if level is shs and year level is 2 or 4, DP = 1/2 of installment total
        if ($level === 'shs' && in_array((int) $yearLevel, [2, 4], true)) {
            $dp = $net / 2.0;
        }

        $dp = round($dp, 2);

        $fee = 0.0;
        if ($net > 0) {
            if ($count > 0) {
                $fee = round(($net - $dp) / $count, 2);
            } else {
                // No installments after DP, put all into DP
                $dp = round($net, 2);
                $fee = 0.0;
            }
        }

        return [
            'id'                 => isset($plan['id']) ? (int) $plan['id'] : null,
            'code'               => (string) ($plan['code'] ?? ''),
            'label'              => (string) ($plan['label'] ?? ''),
            'increase_percent'   => (float) ($plan['increase_percent'] ?? 0),
            'installment_count'  => $count,
            'total_installment'  => $net,
            'down_payment'       => $dp,
            'installment_fee'    => $fee,
        ];
    }

    /**
     * Helper: Given totals and all active plans, compute all plan outputs.
     *
     * @param array<string,float> $totals
     * @param array<int,array<string,mixed>> $plans
     * @param string $level
     * @param int|null $yearLevel
     * @return array<int,array<string,mixed>>
     */
    public function computeAllPlans(array $totals, array $plans, string $level, ?int $yearLevel): array
    {
        $out = [];
        foreach ($plans as $p) {
            $out[] = $this->computePlan($totals, $p, $level, $yearLevel);
        }
        return $out;
    }

    /**
     * Resolve selected plan id based on registration preference and available plans.
     *
     * @param int|null $preferredPlanId
     * @param array<int,array<string,mixed>> $plans
     * @return int|null
     */
    public function resolveSelectedPlanId(?int $preferredPlanId, array $plans): ?int
    {
        $ids = [];
        foreach ($plans as $p) {
            if (isset($p['id'])) $ids[] = (int) $p['id'];
        }
        if ($preferredPlanId !== null && in_array((int)$preferredPlanId, $ids, true)) {
            return (int) $preferredPlanId;
        }
        // fallback to first active plan
        return count($ids) ? (int) $ids[0] : null;
    }
}
