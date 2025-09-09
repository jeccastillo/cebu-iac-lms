<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DiscountScholarshipService
 *
 * Computes scholarships and discounts with compute_full semantics and bucket mapping.
 *
 * Summary of rules:
 * - Load tb_mas_student_discount rows for (student_id, syid) with sd.status='applied' only; join active tb_mas_scholarships (sc.status='active').
 * - Derive per-bucket specs from scholarship catalog fields:
 *     tuition_fee_* and basic_fee_* => basis='tuition'
 *     misc_fee_* => basis='misc'
 *     lab_fee_* => basis='lab'
 *     other_fees_* and penalty_fee_* => basis='additional'
 *     total_assessment_* => basis='total_assessment' and EXCLUSIVE: ignore other per-bucket fields on that same row
 *   Fixed takes precedence over rate when both are provided for the same basis.
 * - Bases come from args: tuition, misc_total, lab_total, additional_total; total_assessment is their sum. Null/negatives treated as 0.
 * - compute_full=true: each spec computes from ORIGINAL base; sum is capped by base. Individual line amounts are proportionally capped.
 * - compute_full=false: specs are applied sequentially against the remaining amount (after compute_full), ordered by assignment id ascending.
 * - Return totals split into scholarships vs discounts, plus line arrays and reserved 'installment'/'ar' keys.
 */
class DiscountScholarshipService
{
    /**
     * Main entry point: compute discounts and scholarships for a student/term given base amounts.
     *
     * @param array $args
     *   - Required: student_id:int, syid:int
     *   - Bases: tuition:float, misc_total:float, lab_total:float, additional_total:float
     *   - Optional context: tuition_year, class_type, year_level, level, stype, program_id, discount_id, scholarship_id
     * @return array Shape:
     *  [
     *    'scholarship_grand_total' => float,
     *    'discount_grand_total'    => float,
     *    'lines' => [
     *        'scholarships' => DeductionLine[],
     *        'discounts'    => DeductionLine[],
     *    ],
     *    'installment' => [... zeros ...],
     *    'ar'          => [... zeros ...],
     *  ]
     */
    public function computeDiscountsAndScholarships(array $args): array
    {
        // Normalize bases
        $base = [
            'tuition'           => max(0.0, (float)($args['tuition'] ?? 0)),
            'misc'              => max(0.0, (float)($args['misc_total'] ?? 0)),
            'lab'               => max(0.0, (float)($args['lab_total'] ?? 0)),
            'additional'        => max(0.0, (float)($args['additional_total'] ?? 0)),
        ];
        $base['total_assessment'] = $base['tuition'] + $base['misc'] + $base['lab'] + $base['additional'];

        $studentId = (int)($args['student_id'] ?? 0);
        $syid      = (int)($args['syid'] ?? 0);

        $out = $this->emptyOutput();

        if ($studentId <= 0 || $syid <= 0) {
            return $out;
        }

        // Determine PK column for tb_mas_student_discount to alias consistent assignment_id
        $sdPkCol = Schema::hasColumn('tb_mas_student_discount', 'intID') ? 'sd.intID' : (Schema::hasColumn('tb_mas_student_discount', 'id') ? 'sd.id' : null);
        if ($sdPkCol === null) {
            // Cannot safely reference assignments without PK; return empty structure
            return $out;
        }

        // Optional targeted filters
        $discountFilterId    = isset($args['discount_id']) ? (int) $args['discount_id'] : null;
        $scholarshipFilterId = isset($args['scholarship_id']) ? (int) $args['scholarship_id'] : null;

        // Load assignments (applied only) joined to active scholarships, with optional filtering
        $q = DB::table('tb_mas_student_discount as sd')
            ->join('tb_mas_scholarships as sc', 'sc.intID', '=', 'sd.discount_id')
            ->where('sd.student_id', $studentId)
            ->where('sd.syid', $syid)
            ->where('sd.status', 'applied')
            ->where('sc.status', 'active');

        if ($discountFilterId && $scholarshipFilterId) {
            $q->where(function ($w) use ($discountFilterId, $scholarshipFilterId) {
                $w->where(function ($w2) use ($discountFilterId) {
                    $w2->where('sc.intID', $discountFilterId)
                       ->where('sc.deduction_type', 'discount');
                })->orWhere(function ($w3) use ($scholarshipFilterId) {
                    $w3->where('sc.intID', $scholarshipFilterId)
                       ->where('sc.deduction_type', 'scholarship');
                });
            });
        } elseif ($discountFilterId) {
            $q->where('sc.intID', $discountFilterId)
              ->where('sc.deduction_type', 'discount');
        } elseif ($scholarshipFilterId) {
            $q->where('sc.intID', $scholarshipFilterId)
              ->where('sc.deduction_type', 'scholarship');
        }

        $rows = $q->select([
                'sd.*',
                'sc.*',
                DB::raw($sdPkCol . ' as assignment_id'),
                DB::raw('sc.intID as scholarship_id'),
            ])
            ->orderBy('sc.name', 'asc')
            ->get()
            ->map(fn($r) => (array)$r)
            ->toArray();

        // Build specs for all rows
        $specs = [];
        foreach ($rows as $r) {
            $specs = array_merge($specs, $this->mapCatalogRowToBucketSpecs($r));
        }

        if (empty($specs)) {
            return $out;
        }

        // Group by basis and compute_full
        $bases = ['tuition', 'misc', 'lab', 'additional', 'total_assessment'];
        $group = [];
        foreach ($bases as $b) {
            $group[$b] = ['full' => [], 'non' => []];
        }
        foreach ($specs as $sp) {
            $b = $sp['basis'];
            if (!in_array($b, $bases, true)) {
                continue;
            }
            if (!empty($sp['compute_full'])) {
                $group[$b]['full'][] = $sp;
            } else {
                $group[$b]['non'][] = $sp;
            }
        }

        $lines = [
            'scholarships' => [],
            'discounts' => [],
        ];
        $totalsByType = [
            'scholarship' => 0.0,
            'discount' => 0.0,
        ];

        // 1) Apply compute_full groups: proportional capping to base
        $fullAppliedByBasis = array_fill_keys($bases, 0.0);
        foreach ($bases as $b) {
            if ($base[$b] <= 0.0 || empty($group[$b]['full'])) {
                continue;
            }

            $raws = [];
            $sumRaw = 0.0;
            foreach ($group[$b]['full'] as $idx => $sp) {
                $raw = $this->computeAmount($base[$b], $sp['rate'], $sp['fixed']);
                $raw = max(0.0, (float)$raw);
                $raws[$idx] = $raw;
                $sumRaw += $raw;
            }

            $cap = $base[$b];
            $applySum = min($cap, $sumRaw);

            if ($sumRaw <= 0.0) {
                continue;
            }

            $ratio = $applySum / $sumRaw; // 0..1
            foreach ($group[$b]['full'] as $idx => $sp) {
                $applied = round($raws[$idx] * $ratio, 2);
                if ($applied <= 0) {
                    continue;
                }
                $line = $this->makeLine($sp, $b, $applied, $sumRaw > $cap ? 'capped_at_base' : null);
                $this->pushLine($lines, $totalsByType, $line);
                $fullAppliedByBasis[$b] += $applied;
            }
        }

        // 2) Apply non-full sequentially on remaining
        foreach ($bases as $b) {
            if ($base[$b] <= 0.0 || empty($group[$b]['non'])) {
                continue;
            }

            // Deterministic ordering by assignment_id asc then scholarship_id asc
            usort($group[$b]['non'], function ($a, $c) {
                $aid = (int)($a['assignment_id'] ?? 0);
                $cid = (int)($c['assignment_id'] ?? 0);
                if ($aid === $cid) {
                    $as = (int)($a['scholarship_id'] ?? 0);
                    $cs = (int)($c['scholarship_id'] ?? 0);
                    return $as <=> $cs;
                }
                return $aid <=> $cid;
            });

            $remaining = max(0.0, $base[$b] - $fullAppliedByBasis[$b]);

            foreach ($group[$b]['non'] as $sp) {
                if ($remaining <= 0.0) {
                    break;
                }
                $raw = $this->computeAmount($remaining, $sp['rate'], $sp['fixed']);
                $raw = max(0.0, (float)$raw);
                $applied = min($remaining, $raw);
                $applied = round($applied, 2);

                if ($applied <= 0.0) {
                    continue;
                }

                $line = $this->makeLine($sp, $b, $applied, null);
                $this->pushLine($lines, $totalsByType, $line);

                $remaining = max(0.0, $remaining - $applied);
            }
        }

        // Compose output
        $out['scholarship_grand_total'] = round($totalsByType['scholarship'], 2);
        $out['discount_grand_total']    = round($totalsByType['discount'], 2);
        $out['lines'] = $lines;

        return $out;
    }

    /**
     * Compute a raw amount from a base using either fixed or rate.
     * - If $fixed > 0, return fixed (rounded 2)
     * - Else if 0<=rate<=100, return base * (rate/100) (rounded 2)
     * - Else return 0.0
     */
    private function computeAmount(float $base, ?int $rate, ?float $fixed): float
    {
        if ($fixed !== null && $fixed > 0) {
            return round((float)$fixed, 2);
        }
        if ($rate !== null) {
            $r = max(0, min(100, (int)$rate));
            return round($base * ($r / 100.0), 2);
        }
        return 0.0;
    }

    /**
     * Map a joined catalog row (sd+sc) to one or more bucket specs.
     *
     * Input $r is an array with keys from sd.* and sc.* plus aliases:
     *  - assignment_id (aliased PK of student_discount row)
     *  - scholarship_id (sc.intID)
     */
    private function mapCatalogRowToBucketSpecs(array $r): array
    {
        $specs = [];

        $assignmentId   = (int)($r['assignment_id'] ?? ($r['intID'] ?? $r['id'] ?? 0));
        $scholarshipId  = (int)($r['scholarship_id'] ?? $r['intID'] ?? 0);
        $name           = (string)($r['name'] ?? 'Unnamed');
        $deductionType  = in_array(($r['deduction_type'] ?? 'discount'), ['scholarship', 'discount'], true)
            ? (string)$r['deduction_type']
            : 'discount';
        $deductionFrom  = isset($r['deduction_from']) ? (string)$r['deduction_from'] : null;
        $computeFull    = array_key_exists('compute_full', $r) ? (bool)$r['compute_full'] : true;

        // Helpers to fetch safe rate/fixed from row
        $getRate = function (?string $key) use ($r): ?int {
            if (!$key) return null;
            if (!array_key_exists($key, $r)) return null;
            $v = $r[$key];
            if ($v === null || $v === '') return null;
            $iv = (int)$v;
            return max(0, min(100, $iv));
        };
        $getFixed = function (?string $key) use ($r): ?float {
            if (!$key) return null;
            if (!array_key_exists($key, $r)) return null;
            $v = $r[$key];
            if ($v === null || $v === '') return null;
            $fv = (float)$v;
            return $fv > 0 ? $fv : null;
        };

        // Exclusive total_assessment if any of the TA fields are provided
        $taRate  = $getRate('total_assessment_rate');
        $taFixed = $getFixed('total_assessment_fixed');
        $hasTA   = ($taRate !== null) || ($taFixed !== null);

        if ($hasTA) {
            // Exclusive: ignore other fields on this row
            $specs[] = [
                'assignment_id'  => $assignmentId,
                'scholarship_id' => $scholarshipId,
                'name'           => $name,
                'basis'          => 'total_assessment',
                'deduction_type' => $deductionType,
                'deduction_from' => $deductionFrom,
                'compute_full'   => $computeFull,
                'rate'           => $taRate,
                'fixed'          => $taFixed,
                'notes'          => null,
            ];
            return $specs;
        }

        // Tuition (alias: basic_fee_*)
        $tuRate  = $getRate('tuition_fee_rate');
        $tuFixed = $getFixed('tuition_fee_fixed');
        $baRate  = $getRate('basic_fee_rate');
        $baFixed = $getFixed('basic_fee_fixed');

        // Prefer explicit tuition_* pair; if not set, allow basic_* pair
        if ($tuRate !== null || $tuFixed !== null) {
            $specs[] = [
                'assignment_id'  => $assignmentId,
                'scholarship_id' => $scholarshipId,
                'name'           => $name,
                'basis'          => 'tuition',
                'deduction_type' => $deductionType,
                'deduction_from' => $deductionFrom,
                'compute_full'   => $computeFull,
                'rate'           => $tuFixed !== null ? null : $tuRate,
                'fixed'          => $tuFixed,
                'notes'          => null,
            ];
        } elseif ($baRate !== null || $baFixed !== null) {
            $specs[] = [
                'assignment_id'  => $assignmentId,
                'scholarship_id' => $scholarshipId,
                'name'           => $name,
                'basis'          => 'tuition',
                'deduction_type' => $deductionType,
                'deduction_from' => $deductionFrom,
                'compute_full'   => $computeFull,
                'rate'           => $baFixed !== null ? null : $baRate,
                'fixed'          => $baFixed,
                'notes'          => null,
            ];
        }

        // Misc
        $miRate  = $getRate('misc_fee_rate');
        $miFixed = $getFixed('misc_fee_fixed');
        if ($miRate !== null || $miFixed !== null) {
            $specs[] = [
                'assignment_id'  => $assignmentId,
                'scholarship_id' => $scholarshipId,
                'name'           => $name,
                'basis'          => 'misc',
                'deduction_type' => $deductionType,
                'deduction_from' => $deductionFrom,
                'compute_full'   => $computeFull,
                'rate'           => $miFixed !== null ? null : $miRate,
                'fixed'          => $miFixed,
                'notes'          => null,
            ];
        }

        // Lab
        $laRate  = $getRate('lab_fee_rate');
        $laFixed = $getFixed('lab_fee_fixed');
        if ($laRate !== null || $laFixed !== null) {
            $specs[] = [
                'assignment_id'  => $assignmentId,
                'scholarship_id' => $scholarshipId,
                'name'           => $name,
                'basis'          => 'lab',
                'deduction_type' => $deductionType,
                'deduction_from' => $deductionFrom,
                'compute_full'   => $computeFull,
                'rate'           => $laFixed !== null ? null : $laRate,
                'fixed'          => $laFixed,
                'notes'          => null,
            ];
        }

        // Additional (other_fees_* and penalty_fee_* produce separate specs if both present)
        $otRate  = $getRate('other_fees_rate');
        $otFixed = $getFixed('other_fees_fixed');
        if ($otRate !== null || $otFixed !== null) {
            $specs[] = [
                'assignment_id'  => $assignmentId,
                'scholarship_id' => $scholarshipId,
                'name'           => $name,
                'basis'          => 'additional',
                'deduction_type' => $deductionType,
                'deduction_from' => $deductionFrom,
                'compute_full'   => $computeFull,
                'rate'           => $otFixed !== null ? null : $otRate,
                'fixed'          => $otFixed,
                'notes'          => null,
            ];
        }

        $peRate  = $getRate('penalty_fee_rate');
        $peFixed = $getFixed('penalty_fee_fixed');
        if ($peRate !== null || $peFixed !== null) {
            $specs[] = [
                'assignment_id'  => $assignmentId,
                'scholarship_id' => $scholarshipId,
                'name'           => $name,
                'basis'          => 'additional',
                'deduction_type' => $deductionType,
                'deduction_from' => $deductionFrom,
                'compute_full'   => $computeFull,
                'rate'           => $peFixed !== null ? null : $peRate,
                'fixed'          => $peFixed,
                'notes'          => null,
            ];
        }

        return $specs;
    }

    /**
     * Build a DeductionLine entry.
     *
     * @param array $sp Spec with keys: assignment_id, scholarship_id, name, deduction_type, deduction_from, compute_full, rate, fixed
     * @param string $basis
     * @param float $amount
     * @param string|null $note
     * @return array
     */
    private function makeLine(array $sp, string $basis, float $amount, ?string $note): array
    {
        return [
            'id'             => (int)($sp['scholarship_id'] ?? 0),
            'assignment_id'  => (int)($sp['assignment_id'] ?? 0),
            'name'           => (string)($sp['name'] ?? ''),
            'deduction_type' => (string)($sp['deduction_type'] ?? 'discount'),
            'deduction_from' => isset($sp['deduction_from']) ? (string)$sp['deduction_from'] : null,
            'compute_full'   => (bool)($sp['compute_full'] ?? true),
            'basis'          => (string)$basis,
            'basis_label'    => $this->basisLabel((string)$basis),
            'rate'           => isset($sp['rate']) ? (int)$sp['rate'] : null,
            'fixed'          => isset($sp['fixed']) ? (float)$sp['fixed'] : null,
            'amount'         => round((float)$amount, 2),
            'notes'          => $note,
        ];
    }

    /**
     * Push a line into scholarships/discounts arrays and accumulate totals by type.
     */
    private function pushLine(array &$lines, array &$totalsByType, array $line): void
    {
        $type = $line['deduction_type'] === 'scholarship' ? 'scholarship' : 'discount';
        if ($type === 'scholarship') {
            $lines['scholarships'][] = $line;
        } else {
            $lines['discounts'][] = $line;
        }
        $totalsByType[$type] += (float)$line['amount'];
    }

    /**
     * Human-readable label for basis bucket.
     */
    private function basisLabel(string $basis): string
    {
        return match ($basis) {
            'tuition'            => 'Tuition',
            'misc'               => 'Miscellaneous',
            'lab'                => 'Laboratory',
            'additional'         => 'Other Fees',
            'total_assessment'   => 'Full Assessment',
            default              => ucfirst($basis),
        };
    }

    /**
     * Empty output scaffold matching the expected shape.
     */
    private function emptyOutput(): array
    {
        return [
            'scholarship_grand_total' => 0.0,
            'discount_grand_total'    => 0.0,
            'installment' => [
                'scholarship' => 0.0,
                'discount' => 0.0,
                'scholarship30' => 0.0,
                'discount30' => 0.0,
                'scholarship50' => 0.0,
                'discount50' => 0.0,
            ],
            'lines' => [
                'scholarships' => [],
                'discounts' => [],
            ],
            'ar' => [
                'ar_discounts_full' => 0.0,
                'ar_discounts_installment' => 0.0,
                'ar_discounts_installment30' => 0.0,
                'ar_discounts_installment50' => 0.0,
                'ar_external_scholarship_full' => 0.0,
                'ar_external_scholarship_installment' => 0.0,
                'ar_external_scholarship_installment30' => 0.0,
                'ar_external_scholarship_installment50' => 0.0,
                'ar_late_tagged_discounts_full' => 0.0,
                'ar_late_tagged_discounts_installment' => 0.0,
                'ar_late_tagged_discounts_installment30' => 0.0,
                'ar_late_tagged_discounts_installment50' => 0.0,
                'ar_external_discounts_full' => 0.0,
                'ar_external_discounts_installment' => 0.0,
                'ar_external_discounts_installment30' => 0.0,
                'ar_external_discounts_installment50' => 0.0,
            ],
        ];
    }
}
