<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // Fallback to tuition year defaults (support both new tuition_amount* and legacy pricePerUnit* columns)
        // Prefer tuition_amount* if present; otherwise fallback to pricePerUnit*.
        $keyMapNew = [
            'regular' => 'tuition_amount',
            'online'  => 'tuition_amount_online',
            'hybrid'  => 'tuition_amount_hybrid',
            'hyflex'  => 'tuition_amount_hyflex',
        ];
        $newKey = $keyMapNew[$classType] ?? 'tuition_amount';
        $val = $tuitionYear[$newKey] ?? null;
        if ($val !== null && (float)$val > 0) {
            return (float)$val;
        }

        $keyMapLegacy = [
            'regular' => 'pricePerUnit',
            'online'  => 'pricePerUnitOnline',
            'hybrid'  => 'pricePerUnitHybrid',
            'hyflex'  => 'pricePerUnitHyflex',
        ];
        $legacyKey = $keyMapLegacy[$classType] ?? 'pricePerUnit';
        return (float) ($tuitionYear[$legacyKey] ?? 0);
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

        // Prefer new schema keys tuition_amount*
        $newMap = [
            'regular' => 'tuition_amount',
            'online'  => 'tuition_amount_online',
            'hybrid'  => 'tuition_amount_hybrid',
            'hyflex'  => 'tuition_amount_hyflex',
        ];
        $newKey = $newMap[$classType] ?? 'tuition_amount';
        if (array_key_exists($newKey, $row)) {
            return (float) ($row[$newKey] ?? 0);
        }

        // Fallback to legacy schema keys per bucket
        if ($bucket === 'lab') {
            $legacyLabMap = [
                'regular' => 'labRegular',
                'online'  => 'labOnline',
                'hybrid'  => 'labHybrid',
                'hyflex'  => 'labHyflex',
            ];
            $legacyKey = $legacyLabMap[$classType] ?? 'labRegular';
            return (float) ($row[$legacyKey] ?? 0);
        }

        // bucket 'misc' (and others treated as misc)
        $legacyMiscMap = [
            'regular' => 'miscRegular',
            'online'  => 'miscOnline',
            'hybrid'  => 'miscHybrid',
            'hyflex'  => 'miscHyflex',
        ];
        $legacyKey = $legacyMiscMap[$classType] ?? 'miscRegular';
        return (float) ($row[$legacyKey] ?? 0);
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
                $effectiveRate = (float) $nstpRate; // ignore multiplier for NSTP
                $lineAmount = (int) $units * $effectiveRate;
            } else {
                // apply special multiplier when classlist is flagged (non-NSTP only)
                $specialClass = (int) ($subj['special_class'] ?? 0);
                $mult = (float) ($subj['special_multiplier'] ?? 1.0);
                if ($mult <= 0) { $mult = 1.0; }
                $effectiveRate = (float) $unitFee;
                if ($specialClass === 1) {
                    $effectiveRate = (float) $unitFee * $mult;
                }
                $lineAmount = (int) $units * $effectiveRate;
            }

            $totalTuition += $lineAmount;
            $tuitionItems[] = [
                'code'       => $code,
                'subject_id' => $sid,
                'units'      => $units,
                'rate'       => $effectiveRate,
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
                    $rate = $amt;
                    // apply multiplier when classlist is flagged for this modular subject
                    $sc = (int) ($s['special_class'] ?? 0);
                    $sm = (float) ($s['special_multiplier'] ?? 1.0);
                    if ($sm <= 0) { $sm = 1.0; }
                    if ($sc === 1) {
                        $rate = round($rate * $sm, 2);
                    }

                    $total += $rate;
                    $items[] = [
                        'code'       => 'MODULAR',
                        'subject_id' => (int) ($s['subjectID'] ?? 0),
                        'units'      => null,
                        'rate'       => $rate,
                        'amount'     => $rate,
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

                    // apply multiplier when classlist is flagged for this elective subject
                    $effRate = $rate;
                    $sc = (int) ($s['special_class'] ?? 0);
                    $sm = (float) ($s['special_multiplier'] ?? 1.0);
                    if ($sm <= 0) { $sm = 1.0; }
                    if ($sc === 1) {
                        $effRate = round($rate * $sm, 2);
                    }

                    $electiveTotal += $effRate;
                    $total += $effRate;
                    $items[] = [
                        'code'       => 'ELECTIVE',
                        'subject_id' => $sid,
                        'units'      => null,
                        'rate'       => $effRate,
                        'amount'     => $effRate,
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
        $classType = $this->resolveClassType($classType);
        $tuitionYearId = $tuitionYear['intID'] ?? $tuitionYear['intId'] ?? null;

        $retList = [];
        $totalMisc = 0.0;
        $lateEnrollmentFee = 0.0;
        $newStudentList = [];
        $newStudentTotal = 0.0;

        if (!$tuitionYearId) {
            return [
                'total_misc' => 0.0,
                'list' => [],
                'late_enrollment_fee' => 0.0,
                'new_student_list' => [],
                'new_student_total' => 0.0,
            ];
        }

        // Determine which misc pack to use
        $internshipFlag = (int)($semRow['__internship'] ?? 0);
        $miscType = $internshipFlag ? 'internship' : 'regular';

        // Load misc pack rows
        $rows = DB::table('tb_mas_tuition_year_misc')
            ->where('tuitionYearID', $tuitionYearId)
            ->where('type', $miscType)
            ->get()
            ->map(fn($r) => (array)$r)
            ->toArray();

        // Apply ID VALIDATION omission for new student types
        $stypeNorm = strtolower(trim($stype));
        $isBrandNew = in_array($stypeNorm, [
            'new', 'freshman', 'transferee', '2nd degree', '2nd degree iac'
        ], true);

        foreach ($rows as $r) {
            $name = trim((string)($r['name'] ?? ''));
            $nameUpper = strtoupper($name);

            if ($isBrandNew && $nameUpper === 'ID VALIDATION') {
                // Skip ID Validation for brand-new students
                continue;
            }

            $amt = $this->getExtraFee($r, $classType, 'misc');
            if ($amt > 0) {
                $retList[$name] = ($retList[$name] ?? 0) + (float)$amt;
                $totalMisc += (float)$amt;
            }
        }

        // New student fees (separate pack)
        if ($isBrandNew) {
            $nsRows = DB::table('tb_mas_tuition_year_misc')
                ->where('tuitionYearID', $tuitionYearId)
                ->where('type', 'new_student')
                ->get()
                ->map(fn($r) => (array)$r)
                ->toArray();

            foreach ($nsRows as $r) {
                $name = trim((string)($r['name'] ?? ''));
                $amt = $this->getExtraFee($r, $classType, 'misc');
                if ($amt > 0) {
                    $newStudentList[$name] = ($newStudentList[$name] ?? 0) + (float)$amt;
                    $newStudentTotal += (float)$amt;
                }
            }
        }

        // Late enrollment fee (only if not withdrawal before)
        if ($withdrawalStatus !== 'before') {
            $reconfStart = isset($semRow['reconf_start']) ? (string)$semRow['reconf_start'] : null;
            if ($reconfStart && $dteRegistered) {
                $dr = date('Y-m-d', strtotime($dteRegistered));
                if ($dr >= $reconfStart) {
                    $lateRows = DB::table('tb_mas_tuition_year_misc')
                        ->where('tuitionYearID', $tuitionYearId)
                        ->where('type', 'late_enrollment')
                        ->get()
                        ->map(fn($r) => (array)$r)
                        ->toArray();

                    foreach ($lateRows as $r) {
                        $lateEnrollmentFee += (float)$this->getExtraFee($r, $classType, 'misc');
                    }
                }
            }
        }

        return [
            'total_misc' => round($totalMisc, 2),
            'list' => $retList,
            'late_enrollment_fee' => round($lateEnrollmentFee, 2),
            'new_student_list' => $newStudentList,
            'new_student_total' => round($newStudentTotal, 2),
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
        $classType = $this->resolveClassType($classType);
        $tuitionYearId = $tuitionYear['intID'] ?? $tuitionYear['intId'] ?? null;

        if (!$tuitionYearId || strtolower(trim($citizenship)) === 'philippines') {
            return [
                'total_foreign' => 0.0,
                'list' => [],
            ];
        }

        $list = [];
        $total = 0.0;

        // Student Visa Fee (SVF) only when term indicates pay_student_visa != 0
        $payVisa = (int)($semRow['pay_student_visa'] ?? 0);
        if ($payVisa !== 0) {
            $svf = DB::table('tb_mas_tuition_year_misc')
                ->where('tuitionYearID', $tuitionYearId)
                ->where('type', 'svf')
                ->first();
            if ($svf) {
                $amt = $this->getExtraFee((array)$svf, $classType, 'misc');
                if ($amt > 0) {
                    $list['Student Visa'] = (float)$amt;
                    $total += (float)$amt;
                }
            }
        }

        // International Student Fee (ISF)
        $isf = DB::table('tb_mas_tuition_year_misc')
            ->where('tuitionYearID', $tuitionYearId)
            ->where('type', 'isf')
            ->first();
        if ($isf) {
            $amt = $this->getExtraFee((array)$isf, $classType, 'misc');
            if ($amt > 0) {
                $list['International Student Fee'] = (float)$amt;
                $total += (float)$amt;
            }
        }

        return [
            'total_foreign' => round($total, 2),
            'list' => $list,
        ];
    }

    public function computeInstallments(array $totals, array $tuitionYear, string $level, ?int $yearLevel, ?array $dsLines = null): array
    {
        // Expect $totals keys:
        // tuition, lab, misc, additional, discount_total, scholarship_total
        $tuition = (float) ($totals['tuition'] ?? 0);
        $lab     = (float) ($totals['lab'] ?? 0);
        $misc    = (float) ($totals['misc'] ?? 0);
        $additional = (float) ($totals['additional'] ?? 0);
        $disc    = (float) ($totals['discount_total'] ?? 0);
        $sch     = (float) ($totals['scholarship_total'] ?? 0);

        $increase = (float) ($tuitionYear['installmentIncrease'] ?? 0) / 100.0;

        // Baseline installment total uses configured installmentIncrease on tuition and lab only
        $tuition_i = $tuition + ($tuition * $increase);
        $lab_i     = $lab + ($lab * $increase);
        $gross_installment = $tuition_i + $lab_i + $misc + $additional;

        // Variants per business rules:
        // - 30% DP applies plan increase on all buckets (tuition, lab, misc, additional)
        // - 50% DP should have the same total as the Standard installment
        $gross_normal = $tuition + $lab + $misc + $additional;

        // 30% scheme: +15% applied to tuition and misc (lab/additional unchanged)
        $tuition_i30      = $tuition * 1.15;
        $lab_i30         = $lab  * 1.15;
        $gross_installment30 = $tuition_i30 + $lab_i30 + $misc + $additional;

        // 50% scheme: +9% applied to tuition and misc (lab/additional unchanged)
        $tuition_i50      = $tuition * 1.09;
        $lab_i50         = $lab * 1.09;
        $gross_installment50 = $tuition_i50 + $lab_i50 + $misc + $additional;

        // Net of discounts/scholarships
        // When dsLines provided (discount/scholarship lines with basis), scale tuition/lab deductions by installment increases per scheme.
        $useDiscStd = $disc;
        $useSchStd  = $sch;
        $useDisc30  = $disc;
        $useSch30   = $sch;
        $useDisc50  = $disc;
        $useSch50   = $sch;

        if ($dsLines !== null && is_array($dsLines)) {
            // Normalize to flat list of lines
            $flat = [];
            if (isset($dsLines['discounts']) || isset($dsLines['scholarships'])) {
                if (isset($dsLines['discounts']) && is_array($dsLines['discounts'])) {
                    foreach ($dsLines['discounts'] as $ln) { $flat[] = $ln; }
                }
                if (isset($dsLines['scholarships']) && is_array($dsLines['scholarships'])) {
                    foreach ($dsLines['scholarships'] as $ln) { $flat[] = $ln; }
                }
            } else {
                $flat = $dsLines;
            }

            $incStd = max(0.0, (float) ($tuitionYear['installmentIncrease'] ?? 0)) / 100.0;
            $inc30  = 0.15; // 15% on tuition/lab for 30% scheme
            $inc50  = 0.09; // 9% on tuition/lab for 50% scheme

            $discStd = 0.0; $schStd = 0.0;
            $disc30s = 0.0; $sch30s = 0.0;
            $disc50s = 0.0; $sch50s = 0.0;

            foreach ((array)$flat as $line) {
                if (!is_array($line)) continue;
                $basis = strtolower((string)($line['basis'] ?? ''));
                $type  = strtolower((string)($line['deduction_type'] ?? 'discount'));
                $amt   = (float) ($line['amount'] ?? 0);
                if ($amt <= 0) continue;

                $isTuLab = in_array($basis, ['tuition','lab'], true);
                $fStd = $isTuLab ? (1.0 + $incStd) : 1.0;
                $f30  = $isTuLab ? 1.15 : 1.0;
                $f50  = $isTuLab ? 1.09 : 1.0;

                $adjStd = round($amt * $fStd, 2);
                $adj30  = round($amt * $f30, 2);
                $adj50  = round($amt * $f50, 2);

                if ($type === 'scholarship') {
                    $schStd += $adjStd;
                    $sch30s += $adj30;
                    $sch50s += $adj50;
                } else {
                    $discStd += $adjStd;
                    $disc30s += $adj30;
                    $disc50s += $adj50;
                }
            }

            $useDiscStd = $discStd;
            $useSchStd  = $schStd;
            $useDisc30  = $disc30s;
            $useSch30   = $sch30s;
            $useDisc50  = $disc50s;
            $useSch50   = $sch50s;
        }

        $net_installment   = max(0.0, round($gross_installment   - $useDiscStd - $useSchStd, 2));
        $net_installment30 = max(0.0, round($gross_installment30 - $useDisc30  - $useSch30,  2));
        $net_installment50 = max(0.0, round($gross_installment50 - $useDisc50  - $useSch50,  2));
        
        // Down payment rules
        $dpPercent = (float) ($tuitionYear['installmentDP'] ?? 0) / 100.0;
        $dpFixed   = (float) ($tuitionYear['installmentFixed'] ?? 0);

        // Default DP from percent unless fixed is set (>0)
        $dp   = $dpFixed > 0 ? min($net_installment, $dpFixed) : $net_installment * $dpPercent;
        $dp30 = $net_installment30 * 0.30; // explicit 30% scheme
        $dp50 = $net_installment50 * 0.50; // explicit 50% scheme

        // SHS special rule: if level is shs and year level is 2 or 4, DP = 1/2 of installment
        if (strtolower($level) === 'shs' && in_array((int)$yearLevel, [2, 4], true)) {
            $dp = $net_installment / 2.0;
        }

        $dp   = round($dp, 2);
        $dp30 = round($dp30, 2);
        $dp50 = round($dp50, 2);

        // Per-installment fee (5 remaining payments after DP)
        $ifee   = $net_installment   > 0 ? round(($net_installment   - $dp)   / 5.0, 2) : 0.0;
        $ifee30 = $net_installment30 > 0 ? round(($net_installment30 - $dp30) / 5.0, 2) : 0.0;
        $ifee50 = $net_installment50 > 0 ? round(($net_installment50 - $dp50) / 5.0, 2) : 0.0;
        
        return [    
            'total_installment'    => $net_installment,
            'total_installment30'  => $net_installment30,
            'total_installment50'  => $net_installment50,
            'down_payment'         => $dp,
            'down_payment30'       => $dp30,
            'down_payment50'       => $dp50,
            'installment_fee'      => $ifee,
            'installment_fee30'    => $ifee30,
            'installment_fee50'    => $ifee50,
        ];
    }
}
