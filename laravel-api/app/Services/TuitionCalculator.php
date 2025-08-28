<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TuitionCalculator
{
    /**
     * Resolve the class type string to a normalized variant.
     * Allowed: regular, online, hybrid, hyflex. Defaults to 'regular'.
     */
    public function resolveClassType(?string $classType): string
    {
        $type = strtolower(trim((string) $classType));
        if (in_array($type, ['regular', 'online', 'hybrid', 'hyflex'], true)) {
            return $type;
        }
        return 'regular';
    }

    /**
     * Get effective unit price for a program for the tuition year and class type,
     * using tb_mas_tuition_year_program if present, else fallback to tuition year defaults.
     */
    public function getUnitPrice(array $tuitionYear, string $classType = 'regular', ?int $programId = null): float
    {
        $classType = $this->resolveClassType($classType);

        // Program-specific override
        if ($programId) {
            $row = DB::table('tb_mas_tuition_year_program')
                ->where('tuitionyear_id', $tuitionYear['intID'] ?? $tuitionYear['intId'] ?? null)
                ->where('track_id', $programId)
                ->first();
            if ($row) {
                switch ($classType) {
                    case 'online': return (float) ($row->tuition_amount_online ?? 0);
                    case 'hybrid': return (float) ($row->tuition_amount_hybrid ?? 0);
                    case 'hyflex': return (float) ($row->tuition_amount_hyflex ?? 0);
                    default:       return (float) ($row->tuition_amount ?? 0);
                }
            }
        }

        // Fallback to tuition year defaults (column names aligned to misc/lab for parity)
        // If not present, return 0 as safe default.
        switch ($classType) {
            case 'online': return (float) ($tuitionYear['tuition_amount_online'] ?? 0);
            case 'hybrid': return (float) ($tuitionYear['tuition_amount_hybrid'] ?? 0);
            case 'hyflex': return (float) ($tuitionYear['tuition_amount_hyflex'] ?? 0);
            default:       return (float) ($tuitionYear['tuition_amount'] ?? 0);
        }
    }

    /**
     * Return the correct fee value for misc/lab row based on class type.
     * Expects $row to have columns tuition_amount(_online|_hybrid|_hyflex).
     */
    public function getExtraFee(?array $row, string $classType = 'regular', string $bucket = 'misc'): float
    {
        if (!$row) {
            return 0.0;
        }
        $classType = $this->resolveClassType($classType);
        switch ($classType) {
            case 'online': return (float) ($row['tuition_amount_online'] ?? 0);
            case 'hybrid': return (float) ($row['tuition_amount_hybrid'] ?? 0);
            case 'hyflex': return (float) ($row['tuition_amount_hyflex'] ?? 0);
            default:       return (float) ($row['tuition_amount'] ?? 0);
        }
    }

    /**
     * Resolve per-term lab classification override: tb_mas_subjects_labtype.lab_classification.
     * If no override, return $default.
     */
    public function resolveLabClassification(int $subjectId, int $syid, string $default): string
    {
        $override = DB::table('tb_mas_subjects_labtype')
            ->where('subject_id', $subjectId)
            ->where('term_id', $syid)
            ->first();

        return $override ? (string) $override->lab_classification : $default;
    }

    /**
     * Compute college tuition totals for a list of subjects.
     * Returns:
     *  [
     *    'tuition' => float,
     *    'lab_total' => float,
     *    'thesis_fee' => float,
     *    'lab_list' => [ code => amount ],
     *    'tuition_items' => [[code, subject_id, units, rate, amount], ...]
     *  ]
     */
    public function computeCollegeTuition(array $subjects, array $tuitionYear, string $classType, int $syid, float $unitFee): array
    {
        $totalTuition = 0.0;
        $totalLab = 0.0;
        $thesisFee = 0.0;
        $labList = [];
        $tuitionItems = [];

        foreach ($subjects as $subj) {
            $sid = (int) ($subj['subjectID'] ?? $subj['intID'] ?? 0);
            if ($sid === 0) {
                continue;
            }

            $class = DB::table('tb_mas_subjects')->where('intID', $sid)->first();
            if (!$class) {
                continue;
            }

            $code = (string) ($class->strCode ?? ('SUBJ-' . $sid));
            $units = (int) ($class->strTuitionUnits ?? $class->strUnits ?? 0);
            $intLab = (int) ($class->intLab ?? 0);
            $defaultLabClass = (string) ($class->strLabClassification ?? 'none');
            $labClass = $defaultLabClass !== 'none'
                ? $this->resolveLabClassification($sid, $syid, $defaultLabClass)
                : 'none';

            // NSTP special-case
            if (!empty($class->isNSTP)) {
                $nstpRow = DB::table('tb_mas_tuition_year_misc')
                    ->where('tuitionYearID', $tuitionYear['intID'] ?? null)
                    ->where('type', 'nstp')
                    ->first();

                $nstpRate = $this->getExtraFee($nstpRow ? (array) $nstpRow : null, $classType, 'misc');
                $lineAmount = (int) $units * $nstpRate;
            } else {
                $lineAmount = (int) $units * $unitFee;
            }

            $totalTuition += $lineAmount;
            $tuitionItems[] = [
                'code'       => $code,
                'subject_id' => $sid,
                'units'      => $units,
                'rate'       => $unitFee,
                'amount'     => $lineAmount,
            ];

            // Lab fee
            if ($labClass !== 'none' && $intLab > 0) {
                $labRow = DB::table('tb_mas_tuition_year_lab_fee')
                    ->where('tuitionYearID', $tuitionYear['intID'] ?? null)
                    ->where('name', $labClass)
                    ->first();

                $labFee = $this->getExtraFee($labRow ? (array) $labRow : null, $classType, 'lab') * $intLab;
                if ($labFee > 0) {
                    $labList[$code] = ($labList[$code] ?? 0) + $labFee;
                    $totalLab += $labFee;
                }
            }

            // Thesis fee
            if (!empty($class->isThesisSubject)) {
                $thesisRow = DB::table('tb_mas_tuition_year_misc')
                    ->where('tuitionYearID', $tuitionYear['intID'] ?? null)
                    ->where('type', 'thesis')
                    ->first();
                $thesisFee += $this->getExtraFee($thesisRow ? (array) $thesisRow : null, $classType, 'misc');
            }
        }

        return [
            'tuition'       => $totalTuition,
            'lab_total'     => $totalLab,
            'thesis_fee'    => $thesisFee,
            'lab_list'      => $labList,
            'tuition_items' => $tuitionItems,
        ];
    }

    /**
     * Compute SHS tuition (track rate, modular payments, elective add-ons).
     * Returns:
     *  [
     *    'tuition' => float,
     *    'elective_total' => float,
     *    'tuition_items' => [[code, subject_id, units|null, rate, amount], ...]
     *  ]
     */
    public function computeSHSTuition(array $subjects, array $tuitionYear, string $classType, int $yearLevel, int $programId): array
    {
        $total = 0.0;
        $electiveTotal = 0.0;
        $items = [];

        // Base track rate
        $track = DB::table('tb_mas_tuition_year_track')
            ->where('tuitionyear_id', $tuitionYear['intID'] ?? null)
            ->where('track_id', $programId)
            ->first();

        if ($track) {
            // Map year level to class type rates per legacy (1=>regular,2=>online,3=>hyflex,4=>hybrid)
            $rate = match ($yearLevel) {
                2 => (float) ($track->tuition_amount_online ?? 0),
                3 => (float) ($track->tuition_amount_hyflex ?? 0),
                4 => (float) ($track->tuition_amount_hybrid ?? 0),
                default => (float) ($track->tuition_amount ?? 0),
            };

            $total += $rate;
            $items[] = [
                'code'       => 'TRACK',
                'subject_id' => null,
                'units'      => null,
                'rate'       => $rate,
                'amount'     => $rate,
            ];
        }

        // Modular subjects: add payment_amount from classlist
        foreach ($subjects as $s) {
            if (!empty($s['is_modular']) && isset($s['payment_amount'])) {
                $amt = (float) $s['payment_amount'];
                if ($amt > 0) {
                    $total += $amt;
                    $items[] = [
                        'code'       => 'MODULAR',
                        'subject_id' => (int) ($s['subjectID'] ?? 0),
                        'units'      => null,
                        'rate'       => $amt,
                        'amount'     => $amt,
                    ];
                }
            }
        }

        // Electives: per subject rates (tb_mas_tuition_year_elective)
        foreach ($subjects as $s) {
            $sid = (int) ($s['subjectID'] ?? 0);
            if ($sid === 0) {
                continue;
            }
            // Additional elective only counts if major/elective/additional flags per legacy
            if (
                isset($s['intMajor'], $s['isElective'], $s['additional_elective']) &&
                ((int)$s['intMajor'] === 1) && ((int)$s['isElective'] === 1) && ((int)$s['additional_elective'] === 1)
            ) {
                $row = DB::table('tb_mas_tuition_year_elective')
                    ->where('tuitionyear_id', $tuitionYear['intID'] ?? null)
                    ->where('subject_id', $sid)
                    ->first();

                if ($row) {
                    $rate = match ($yearLevel) {
                        2 => (float) ($row->tuition_amount_online ?? 0),
                        3 => (float) ($row->tuition_amount_hyflex ?? 0),
                        4 => (float) ($row->tuition_amount_hybrid ?? 0),
                        default => (float) ($row->tuition_amount ?? 0),
                    };
                    $electiveTotal += $rate;
                    $total += $rate;
                    $items[] = [
                        'code'       => 'ELECTIVE',
                        'subject_id' => $sid,
                        'units'      => null,
                        'rate'       => $rate,
                        'amount'     => $rate,
                    ];
                }
            }
        }

        return [
            'tuition'        => $total,
            'elective_total' => $electiveTotal,
            'tuition_items'  => $items,
        ];
    }

    /**
     * Placeholder for misc pack computation (regular/internship, ID validation, late enrollment, internship pack).
     * Expected return:
     *  [
     *    'total_misc' => float,
     *    'list' => [ name => amount, ... ],
     *    'late_enrollment_fee' => float,
     *  ]
     */
    public function computeMiscFees(array $tuitionYear, string $classType, string $stype, int $syid, ?string $withdrawalStatus, array $semRow, ?string $dteRegistered): array
    {
        return [
            'total_misc' => 0.0,
            'list' => [],
            'late_enrollment_fee' => 0.0,
        ];
    }

    /**
     * Placeholder for foreign computation (SVF/ISF).
     * Returns:
     *  [
     *    'total_foreign' => float,
     *    'list' => [ name => amount, ... ]
     *  ]
     */
    public function computeForeignFees(string $citizenship, array $semRow, array $tuitionYear, string $classType): array
    {
        return [
            'total_foreign' => 0.0,
            'list' => [],
        ];
    }

    /**
     * Placeholder for discounts/scholarships aggregation and AR fields.
     * Return bucket with all totals and arrays necessary.
     */
    public function computeDiscountsAndScholarships(array $args): array
    {
        return [
            'scholarship_grand_total' => 0.0,
            'discount_grand_total' => 0.0,
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

    /**
     * Placeholder for installment math (installmentIncrease, DP rules, 30/50 variants).
     * Return array with totals and DP/installment_fee values.
     */
    public function computeInstallments(array $totals, array $tuitionYear, string $level, ?int $yearLevel): array
    {
        return [
            'total_installment' => 0.0,
            'total_installment30' => 0.0,
            'total_installment50' => 0.0,
            'down_payment' => 0.0,
            'down_payment30' => 0.0,
            'down_payment50' => 0.0,
            'installment_fee' => 0.0,
            'installment_fee30' => 0.0,
            'installment_fee50' => 0.0,
        ];
    }
}
