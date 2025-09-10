<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Throwable;
use App\Services\SystemLogService;

class RegistrationService
{
    /**
     * Aggregate daily enrollment tallies for a given term (syid) within a date window.
     * Mirrors RegistrarController::dailyEnrollment behavior but returns plain arrays.
     *
     * @param int $syid
     * @param string $start Y-m-d
     * @param string $end Y-m-d
     * @param array $secondDegreeIac list of student slugs to be counted under "secondIAC"
     * @return array{
     *   success: bool,
     *   data: array<string, array{
     *     freshman:int, transferee:int, second:int, secondIAC:int, continuing:int, shiftee:int, returning:int, total:int, date:string
     *   }>,
     *   totals: array{freshman:int, transferee:int, second:int, secondIAC:int, continuing:int, shiftee:int, returning:int},
     *   withdrawnTotals: array{
     *     freshmanWithdrawn:int, transfereeWithdrawn:int, secondWithdrawn:int, secondIACWithdrawn:int, continuingWithdrawn:int, shifteeWithdrawn:int, returningWithdrawn:int
     *   },
     *   sem_type: string|null,
     *   sy: \Illuminate\Support\Collection
     * }
     */
    public function getDailyEnrollment(int $syid, string $start, string $end, array $secondDegreeIac = []): array
    {
        $activeSem = DB::table('tb_mas_sy')->where('intID', $syid)->first();
        if (!$activeSem) {
            return [
                'success' => false,
                'data' => [],
                'totals' => [],
                'withdrawnTotals' => [],
                'sem_type' => null,
                'sy' => collect(),
            ];
        }

        // Build skeleton per day inclusive of end
        $period = new \DatePeriod(new \DateTime($start), new \DateInterval('P1D'), (new \DateTime($end))->modify('+1 day'));
        $perDay = [];
        foreach ($period as $dt) {
            $date = $dt->format('Y-m-d');
            $perDay[$date] = [
                'freshman' => 0,
                'transferee' => 0,
                'second' => 0,
                'secondIAC' => 0,
                'continuing' => 0,
                'shiftee' => 0,
                'returning' => 0,
                'total' => 0,
                'date' => date('M j, Y', strtotime($date)),
            ];
        }

        $totals = [
            'freshman' => 0,
            'transferee' => 0,
            'second' => 0,
            'secondIAC' => 0,
            'continuing' => 0,
            'shiftee' => 0,
            'returning' => 0,
        ];
        $withdrawnTotals = [
            'freshmanWithdrawn' => 0,
            'transfereeWithdrawn' => 0,
            'secondWithdrawn' => 0,
            'secondIACWithdrawn' => 0,
            'continuingWithdrawn' => 0,
            'shifteeWithdrawn' => 0,
            'returningWithdrawn' => 0,
        ];

        $begin = (new \DateTime($start))->format('Y-m-d') . ' 00:00:00';
        $endDate = (new \DateTime($end))->format('Y-m-d') . ' 23:59:59';

        // Pull all registrations in the window for that term
        $enrollments = DB::table('tb_mas_registration as r')
            ->join('tb_mas_users as u', 'u.intID', '=', 'r.intStudentID')
            ->where('r.intAYID', $syid)
            ->where('r.intROG', '>=', 1) // enrolled/withdrawn etc.
            ->where('r.intROG', '!=', 5) // exclude some terminal state (parity with CI)
            ->whereNotNull('r.dteRegistered')
            ->whereBetween('r.dteRegistered', [$begin, $endDate])
            ->orderBy('r.intRegistrationID', 'desc')
            ->select('r.*', 'u.student_type', 'u.slug', 'u.enumStudentType')
            ->get();

        foreach ($enrollments as $st) {
            $date = substr((string)$st->dteRegistered, 0, 10);
            if (!isset($perDay[$date])) {
                $perDay[$date] = [
                    'freshman' => 0,
                    'transferee' => 0,
                    'second' => 0,
                    'secondIAC' => 0,
                    'continuing' => 0,
                    'shiftee' => 0,
                    'returning' => 0,
                    'total' => 0,
                    'date' => date('M j, Y', strtotime($date)),
                ];
            }

            $addWithdrawn = ((int)$st->intROG === 3) ? 1 : 0;

            if ($st->enumStudentType === 'continuing') {
                $perDay[$date]['continuing'] += 1;
                $perDay[$date]['total'] += 1;
                $totals['continuing'] += 1;
                $withdrawnTotals['continuingWithdrawn'] += $addWithdrawn;
            } elseif ($st->enumStudentType === 'shiftee') {
                $perDay[$date]['shiftee'] += 1;
                $perDay[$date]['total'] += 1;
                $totals['shiftee'] += 1;
                $withdrawnTotals['shifteeWithdrawn'] += $addWithdrawn;
            } elseif ($st->enumStudentType === 'returning') {
                $perDay[$date]['returning'] += 1;
                $perDay[$date]['total'] += 1;
                $totals['returning'] += 1;
                $withdrawnTotals['returningWithdrawn'] += $addWithdrawn;
            } else {
                // Use student_type classification similar to CI
                $stype = $st->student_type ?? 'freshman';
                switch ($stype) {
                    case 'freshman':
                    case 'new':
                        $perDay[$date]['freshman'] += 1;
                        $totals['freshman'] += 1;
                        $withdrawnTotals['freshmanWithdrawn'] += $addWithdrawn;
                        break;
                    case 'transferee':
                        $perDay[$date]['transferee'] += 1;
                        $totals['transferee'] += 1;
                        // parity with CI increments freshmanWithdrawn here
                        $withdrawnTotals['freshmanWithdrawn'] += $addWithdrawn;
                        break;
                    case 'second degree':
                        if (!empty($secondDegreeIac) && in_array((string)$st->slug, $secondDegreeIac, true)) {
                            $perDay[$date]['secondIAC'] += 1;
                            $totals['secondIAC'] += 1;
                            $withdrawnTotals['secondIACWithdrawn'] += $addWithdrawn;
                        } else {
                            $perDay[$date]['second'] += 1;
                            $totals['second'] += 1;
                            $withdrawnTotals['secondWithdrawn'] += $addWithdrawn;
                        }
                        break;
                    default:
                        $perDay[$date]['freshman'] += 1;
                        $totals['freshman'] += 1;
                        $withdrawnTotals['freshmanWithdrawn'] += $addWithdrawn;
                        break;
                }
                $perDay[$date]['total'] += 1;
            }
        }

        $syList = DB::table('tb_mas_sy')->get();

        return [
            'success' => true,
            'data' => $perDay,
            'totals' => $totals,
            'withdrawnTotals' => $withdrawnTotals,
            'sem_type' => $activeSem->term_student_type ?? null,
            'sy' => $syList,
        ];
    }

    /**
     * Registration state transition handler (stub for now).
     * @return array{success:bool, message:string}
     */
    public function transition(int $registrationId, string $action, array $context = []): array
    {
        return [
            'success' => false,
            'message' => 'Not Implemented',
        ];
    }

    /**
     * Find an existing registration row by student number and term, with optional program/curriculum display fields.
     */
    public function findByStudentNumberAndTerm(string $studentNumber, int $term): ?object
    {
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        if (!$user) {
            return null;
        }

        $row = DB::table('tb_mas_registration as r')
            ->leftJoin('tb_mas_programs as p', 'p.intProgramID', '=', 'r.current_program')
            ->leftJoin('tb_mas_curriculum as c', 'c.intID', '=', 'r.current_curriculum')
            ->where('r.intStudentID', $user->intID)
            ->where('r.intAYID', $term)
            ->select(
                'r.*',
                'p.strProgramCode as program_code',
                'p.strProgramDescription as program_description',
                'c.strName as curriculum_name'
            )
            ->orderByDesc('r.intRegistrationID')
            ->first();

        return $row ?: null;
    }

    /**
     * Update allowed registration fields for an existing row (no create). Returns status and fresh row.
     */
    public function updateByStudentNumberAndTerm(string $studentNumber, int $term, array $fields, Request $request): array
    {
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        if (!$user) {
            return ['success' => false, 'message' => 'Student not found', 'status' => 404];
        }

        $existing = DB::table('tb_mas_registration')
            ->where('intStudentID', $user->intID)
            ->where('intAYID', $term)
            ->orderByDesc('intRegistrationID')
            ->first();

        if (!$existing) {
            return ['success' => false, 'message' => 'Registration not found', 'status' => 404];
        }

        // Whitelist updatable fields
        $whitelist = [
            'intYearLevel',
            'enumStudentType',
            'current_program',
            'current_curriculum',
            'tuition_year',
            'tuition_installment_plan_id',
            'paymentType',
            'loa_remarks',
            'withdrawal_period',
            // parity with CI update endpoints
            'allow_enroll',
            'downpayment',
            'intROG',
        ];

        $update = [];
        foreach ($whitelist as $k) {
            if (array_key_exists($k, $fields)) {
                $update[$k] = $fields[$k];
            }
        }

        // Validate tuition_installment_plan_id belongs to the registration's tuition_year
        if (array_key_exists('tuition_installment_plan_id', $update)) {
            $planId = $update['tuition_installment_plan_id'];
            if ($planId !== null && $planId !== '') {
                $planId = (int) $planId;
                // Determine target tuition year: prefer new field if being updated, else current
                $targetTuitionYear = array_key_exists('tuition_year', $update)
                    ? (int) $update['tuition_year']
                    : (int) ($existing->tuition_year ?? 0);

                if ($targetTuitionYear > 0) {
                    $ok = DB::table('tb_mas_tuition_year_installment')
                        ->where('id', $planId)
                        ->where('tuitionyear_id', $targetTuitionYear)
                        ->exists();
                    if (!$ok) {
                        // Invalidate when plan does not belong to the registration's tuition year
                        $update['tuition_installment_plan_id'] = null;
                    }
                }
            }
        }

        if (empty($update)) {
            // Nothing to update
            $fresh = $this->findByStudentNumberAndTerm($studentNumber, $term);
            return [
                'success' => true,
                'message' => 'No changes',
                'data' => [
                    'updated' => 0,
                    'registration' => $fresh,
                ],
            ];
        }

        $old = (array) $existing;
        $affected = DB::table('tb_mas_registration')
            ->where('intRegistrationID', $existing->intRegistrationID)
            ->update($update);

        $fresh = $this->findByStudentNumberAndTerm($studentNumber, $term);

        // Audit log
        try {
            SystemLogService::log(
                'update',
                'Registration',
                (int) $existing->intRegistrationID,
                $old,
                $fresh ? (array) $fresh : null,
                $request
            );
        } catch (Throwable $e) {
            // ignore logging failure
        }

        return [
            'success' => true,
            'message' => 'Updated',
            'data' => [
                'updated' => $affected,
                'registration' => $fresh,
            ],
        ];
    }
}
