<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TuitionService
{
    /**
     * Placeholder tuition preview computation.
     * Accepts payload with:
     * - student_number: string
     * - program_id: int
     * - term: string (e.g., sy intID or "1st Term 2024-2025")
     * - subjects: array of { subject_id: int, section?: string }
     *
     * Returns a structure compatible with TuitionBreakdownResource.
     * This will be replaced by actual computation using TuitionYear tables.
     */
    public function preview(array $input): array
    {
        $subjects = Arr::get($input, 'subjects', []);
        $tuitionItems = [];

        // Placeholder tuition items: one line per subject with zero rate/amount
        foreach ($subjects as $subj) {
            $tuitionItems[] = [
                'code'   => Arr::get($subj, 'subject_id'),
                'units'  => null,
                'rate'   => 0.0,
                'amount' => 0.0,
                'section'=> Arr::get($subj, 'section'),
            ];
        }

        $summary = [
            'tuition'            => 0.0,
            'misc_total'         => 0.0,
            'lab_total'          => 0.0,
            'discounts_total'    => 0.0,
            'scholarships_total' => 0.0,
            'additional_total'   => 0.0,
            'billing_total'      => 0.0,
            'total_due'          => 0.0,
        ];

        return [
            'summary' => $summary,
            'items' => [
                'tuition'      => $tuitionItems,
                'misc'         => [],
                'lab'          => [],
                'discounts'    => [],
                'scholarships' => [],
                'additional'   => [],
                'billing'      => [],
            ],
        ];
    }

    /**
     * Critical-path scaffold for full computation.
     * Validates inputs and returns a minimal, shaped payload using the
     * student's selected tuition_year on tb_mas_registration for the term.
     * Throws \InvalidArgumentException for user/term/registration/tuition_year issues.
     */
    public function compute(string $studentNumber, int $syid, ?int $discountId = null, ?int $scholarshipId = null): array
    {
        // 1) Validate entities
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        if (!$user) {
            throw new \InvalidArgumentException('Student not found');
        }

        $sy = DB::table('tb_mas_sy')->where('intID', $syid)->first();
        if (!$sy) {
            throw new \InvalidArgumentException('Term not found');
        }

        $registration = DB::table('tb_mas_registration')
            ->where('intStudentID', $user->intID)
            ->where('intAYID', $syid)
            ->first();

        if (!$registration) {
            throw new \InvalidArgumentException('Registration not found for term');
        }

        $tuitionYearId = $registration->tuition_year ?? null;
        if (!$tuitionYearId) {
            throw new \InvalidArgumentException('Registration missing tuition_year');
        }

        // 2) Load tuition year and setup context
        $tuitionYearRow = DB::table('tb_mas_tuition_year')->where('intID', $tuitionYearId)->first();
        $tuitionYear = $tuitionYearRow ? (array) $tuitionYearRow : [];

        $programUsed = isset($registration->current_program) && $registration->current_program
            ? (int) $registration->current_program
            : (int) $user->intProgramID;

        $classType = $registration->type_of_class ?? 'regular';
        $level = strtolower($user->level ?? 'college');
        $yearLevel = isset($registration->intYearLevel) ? (int) $registration->intYearLevel : null;

        // 3) Gather subjects for the term
        $subjects = DB::table('tb_mas_classlist_student as cls')
            ->join('tb_mas_classlist as cl', 'cl.intID', '=', 'cls.intClassListID')
            ->join('tb_mas_subjects as s', 's.intID', '=', 'cl.intSubjectID')
            ->where('cls.intStudentID', $user->intID)
            ->where('cl.strAcademicYear', $syid)
            ->select(
                's.intID as subjectID',
                's.strCode as code',
                's.strUnits as units',
                's.intLab as intLab',
                's.strLabClassification as labClass',
                's.isNSTP as isNSTP',
                's.isThesisSubject as isThesisSubject',
                's.intMajor as intMajor',
                's.isElective as isElective',
                'cls.additional_elective as additional_elective',
                'cl.is_modular as is_modular',
                'cl.payment_amount as payment_amount'
            )
            ->get()
            ->map(function ($r) {
                $arr = (array) $r;
                // normalize nulls
                $arr['intLab'] = isset($arr['intLab']) ? (int) $arr['intLab'] : 0;
                $arr['units'] = isset($arr['units']) ? (int) $arr['units'] : 0;
                $arr['labClass'] = $arr['labClass'] ?? 'none';
                $arr['isNSTP'] = (int) ($arr['isNSTP'] ?? 0);
                $arr['isThesisSubject'] = (int) ($arr['isThesisSubject'] ?? 0);
                $arr['intMajor'] = (int) ($arr['intMajor'] ?? 0);
                $arr['isElective'] = (int) ($arr['isElective'] ?? 0);
                $arr['additional_elective'] = (int) ($arr['additional_elective'] ?? 0);
                $arr['is_modular'] = (int) ($arr['is_modular'] ?? 0);
                $arr['payment_amount'] = (float) ($arr['payment_amount'] ?? 0);
                return $arr;
            })
            ->toArray();

        // 4) Compute tuition using helper
        $calc = new TuitionCalculator();
        $unitFee = $calc->getUnitPrice($tuitionYear, $classType, $programUsed);

        $tuition = 0.0;
        $labTotal = 0.0;
        $thesisFee = 0.0;
        $tuitionItems = [];
        $labList = [];

        if ($level === 'shs') {
            $res = $calc->computeSHSTuition($subjects, $tuitionYear, $classType, $yearLevel ?? 1, $programUsed);
            $tuition = (float) $res['tuition'];
            $tuitionItems = $res['tuition_items'] ?? [];
            // SHS path typically has no lab fees in track computation; keep labTotal 0 unless added later.
        } else {
            $res = $calc->computeCollegeTuition($subjects, $tuitionYear, $classType, $syid, (float) $unitFee);
            $tuition = (float) $res['tuition'];
            $labTotal = (float) $res['lab_total'];
            $thesisFee = (float) $res['thesis_fee'];
            $labList = $res['lab_list'] ?? [];
            $tuitionItems = $res['tuition_items'] ?? [];
        }

        // 5) Misc fees (placeholder helper)
        $stype = $registration->enumStudentType ?? ($user->student_type ?? 'continuing');
        $syArr = $sy ? (array) $sy : [];
        $withdrawalStatus = $registration->withdrawal_period ?? null;
        $dteRegistered = $registration->dteRegistered ?? null;

        $misc = $calc->computeMiscFees($tuitionYear, $classType, (string) $stype, (int) $syid, $withdrawalStatus, $syArr, $dteRegistered);
        $miscTotal = (float) ($misc['total_misc'] ?? 0);
        $miscList = (array) ($misc['list'] ?? []);
        $lateEnrollmentFee = (float) ($misc['late_enrollment_fee'] ?? 0);
        $newStudentList = (array) ($misc['new_student_list'] ?? []);
        $newStudentTotal = (float) ($misc['new_student_total'] ?? 0);

        // 6) Foreign fees (placeholder helper)
        $citizenship = $user->strCitizenship ?? 'Philippines';
        $foreign = $calc->computeForeignFees((string) $citizenship, $syArr, $tuitionYear, $classType);
        $foreignTotal = (float) ($foreign['total_foreign'] ?? 0);
        $foreignList = (array) ($foreign['list'] ?? []);

        // 7) Build item arrays
        $itemsTuition = [];
        foreach ($tuitionItems as $ti) {
            $itemsTuition[] = [
                'code'       => $ti['code'] ?? null,
                'subject_id' => $ti['subject_id'] ?? null,
                'units'      => $ti['units'] ?? null,
                'rate'       => round((float) ($ti['rate'] ?? 0), 2),
                'amount'     => round((float) ($ti['amount'] ?? 0), 2),
            ];
        }

        $itemsLab = [];
        foreach ($labList as $code => $amt) {
            $itemsLab[] = [
                'code'   => $code,
                'amount' => round((float) $amt, 2),
            ];
        }

        $itemsMisc = [];
        foreach ($miscList as $name => $amt) {
            $itemsMisc[] = [
                'name'   => (string) $name,
                'amount' => round((float) $amt, 2),
            ];
        }

        $itemsAdditional = [];
        $itemsBilling = [];
        // foreign fees
        foreach ($foreignList as $name => $amt) {
            $itemsAdditional[] = [
                'name'   => (string) $name,
                'amount' => round((float) $amt, 2),
            ];
        }
        // thesis
        if ($thesisFee > 0) {
            $itemsAdditional[] = ['name' => 'Thesis Fee', 'amount' => round($thesisFee, 2)];
        }
        // late enrollment
        if ($lateEnrollmentFee > 0) {
            $itemsAdditional[] = ['name' => 'Late Enrollment Fee', 'amount' => round($lateEnrollmentFee, 2)];
        }
        // new student pack
        foreach ($newStudentList as $name => $amt) {
            $itemsAdditional[] = [
                'name'   => (string) $name,
                'amount' => round((float) $amt, 2),
            ];
        }

        // Student Billing (Finance-admin managed): aggregate tb_mas_student_billing by student+syid
        $billingTotal = 0.0;
        $billingDescriptions = [];
        try {
            if (Schema::hasTable('tb_mas_student_billing')) {
                $billingRows = DB::table('tb_mas_student_billing')
                    ->where('intStudentID', $user->intID)
                    ->where('syid', $syid)
                    ->orderBy('posted_at', 'desc')
                    ->orderBy('intID', 'desc')
                    ->get();
                foreach ($billingRows as $br) {
                    $desc = (string) ($br->description ?? '');
                    $amt  = round((float) ($br->amount ?? 0), 2);
                    if ($desc !== '' && $amt != 0.0) {
                        // Allow negative credits; show each line as billing item (separate from 'additional')
                        $itemsBilling[] = ['name' => $desc, 'amount' => $amt];
                        $billingTotal += $amt;

                        // Collect billing descriptions for matching payments
                        $trimmed = trim($desc);
                        if ($trimmed !== '') {
                            $billingDescriptions[] = $trimmed;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore in environments without the table/columns
            $billingTotal = 0.0;
        }

        // 8) Totals and summary
        // Note: billing (student_billing) is separated and NOT included in additional_total
        $additionalTotal = round($foreignTotal + $thesisFee + $lateEnrollmentFee + $newStudentTotal, 2);

        // Discounts and scholarships aggregation (shape only; totals may remain zero until implemented)
        $ds = $calc->computeDiscountsAndScholarships([
            'student_id'        => (int) $user->intID,
            'syid'              => (int) $syid,
            'tuition_year'      => $tuitionYear,
            'class_type'        => (string) $classType,
            'year_level'        => $yearLevel,
            'level'             => (string) $level,
            'stype'             => (string) $stype,
            'tuition'           => (float) $tuition,
            'lab_total'         => (float) $labTotal,
            'misc_total'        => (float) $miscTotal,
            'additional_total'  => (float) $additionalTotal,
            'program_id'        => (int) $programUsed,
            'discount_id'       => $discountId ? (int) $discountId : null,
            'scholarship_id'    => $scholarshipId ? (int) $scholarshipId : null,
        ]);

        $discountTotal    = (float) ($ds['discount_grand_total'] ?? 0.0);
        $scholarshipTotal = (float) ($ds['scholarship_grand_total'] ?? 0.0);

        $itemsDiscounts    = (array) (($ds['lines']['discounts'] ?? []) ?: []);
        $itemsScholarships = (array) (($ds['lines']['scholarships'] ?? []) ?: []);

        $summary = [
            'tuition'            => round($tuition, 2),
            'misc_total'         => round($miscTotal, 2),
            'lab_total'          => round($labTotal, 2),
            'discounts_total'    => round($discountTotal, 2),
            'scholarships_total' => round($scholarshipTotal, 2),
            'additional_total'   => $additionalTotal,
            'billing_total'      => round($billingTotal, 2),
            // total_due excludes separate billing items; billing can be invoiced separately
            'total_due'          => round($tuition + $miscTotal + $labTotal + $additionalTotal - $discountTotal - $scholarshipTotal, 2),
        ];

        // 9) Meta and returned items
        $items = [
            'tuition'      => $itemsTuition,
            'misc'         => $itemsMisc,
            'lab'          => $itemsLab,
            'discounts'    => $itemsDiscounts,
            'scholarships' => $itemsScholarships,
            'additional'   => $itemsAdditional,
            'billing'      => $itemsBilling,
        ];

        // Installment breakdown (baseline using current partial totals and zero discounts/scholarships)
        $installments = (new TuitionCalculator())->computeInstallments([
            'tuition'           => $tuition,
            'lab'               => $labTotal,
            'misc'              => $miscTotal,
            'additional'        => $additionalTotal,
            'discount_total'    => $discountTotal,
            'scholarship_total' => $scholarshipTotal,
        ], $tuitionYear, $level, $yearLevel);

        // Attach installment figures under meta for now
        $summary['installments'] = $installments;

        // Compute amount paid for this registration from payment_details
        // Match on payment_details.student_information_id = tb_mas_users.intID
        // Filter by current registration sy_reference, status 'Paid', and Tuition/Reservation descriptions
        $amountPaid = 0.0;        
        try {
            if (Schema::hasTable('payment_details')) {
                $amountPaid = (float) DB::table('payment_details')
                    ->where('student_information_id', $user->intID)
                    ->where('sy_reference', $syid)
                    ->where('status', 'Paid')
                    ->where(function ($q) use ($billingDescriptions) {
                        $q->where('description', 'like', 'Tuition%')
                          ->orWhere('description', 'like', 'Reservation%');
                        if (!empty($billingDescriptions)) {
                            $q->orWhereIn('description', array_values(array_unique($billingDescriptions)));
                        }
                    })
                    ->sum('subtotal_order');
            }
        } catch (\Throwable $e) {
            // Silently ignore in environments without the table/columns            
            $amountPaid = 0.0;
        }

        $meta = [
            'class_type'        => $classType,
            'tuition_year_id'   => (int) $tuitionYearId,
            'year_level'        => $yearLevel,
            'program_id_used'   => $programUsed,
            'student_id'        => (int) $user->intID,
            'syid'              => (int) $syid,
            'computed_at'       => now()->toDateTimeString(),
            'discount_id'       => $discountId,
            'scholarship_id'    => $scholarshipId,
            'level'             => $level,
            // Expose installmentIncrease percent for frontend display (Standard scheme)
            'installment_increase_percent' => (float) ($tuitionYear['installmentIncrease'] ?? 0),
            // Amount paid derived from payment_details for this registration
            'amount_paid' => round($amountPaid, 2),
        ];

        // Attach AR fields from discounts/scholarships aggregator (shape-ready)
        $meta['ar'] = $ds['ar'] ?? [];

        return [
            'summary' => $summary,
            'items'   => $items,
            'meta'    => $meta,
        ];
    }

    /**
     * Save tuition snapshot for a given student (by intID) and term (syid).
     * Upserts into tb_mas_tuition_saved keyed by (intStudentID,intRegistrationID).
     * Returns summary of the save.
     */
    public function saveSnapshotByStudentId(int $studentId, int $syid, ?int $actorId = null, ?int $discountId = null, ?int $scholarshipId = null): array
    {
        // Resolve student
        $user = DB::table('tb_mas_users')->where('intID', $studentId)->first();
        if (!$user) {
            throw new \InvalidArgumentException('Student not found');
        }

        // Resolve registration for the term
        $registration = DB::table('tb_mas_registration')
            ->where('intStudentID', $user->intID)
            ->where('intAYID', $syid)
            ->first();

        if (!$registration) {
            throw new \InvalidArgumentException('Registration not found for term');
        }
        if (!($registration->tuition_year ?? null)) {
            throw new \InvalidArgumentException('Registration missing tuition_year');
        }

        // Recompute server-side
        $breakdown = $this->compute(
            (string) $user->strStudentNumber,
            (int) $syid,
            $discountId !== null ? (int) $discountId : null,
            $scholarshipId !== null ? (int) $scholarshipId : null
        );

        $now = now()->toDateTimeString();
        $key = [
            'intStudentID'      => (int) $user->intID,
            'intRegistrationID' => (int) $registration->intRegistrationID,
        ];

        $existing = DB::table('tb_mas_tuition_saved')->where($key)->first();

        if ($existing) {
            DB::table('tb_mas_tuition_saved')
                ->where($key)
                ->update([
                    'syid'       => (int) $syid,
                    'payload'    => json_encode($breakdown),
                    'saved_by'   => $actorId,
                    'updated_at' => $now,
                ]);
            $savedId = (int) $existing->intID;
            $overwritten = true;
        } else {
            $savedId = DB::table('tb_mas_tuition_saved')->insertGetId([
                'intStudentID'      => (int) $user->intID,
                'intRegistrationID' => (int) $registration->intRegistrationID,
                'syid'              => (int) $syid,
                'payload'           => json_encode($breakdown),
                'saved_by'          => $actorId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $overwritten = false;
        }

        // Upon saving tuition, generate or update a tuition invoice linked to this registration.
        // Determine amount based on paymentType: partial uses installments total; otherwise total_due. Fallbacks applied.
        try {
            $sum = is_array($breakdown['summary'] ?? null) ? $breakdown['summary'] : [];
            $installments = is_array($sum['installments'] ?? null) ? $sum['installments'] : [];
            $instTotal = $installments['total_installment'] ?? ($sum['total_installment'] ?? null);
            $totalDue = $sum['total_due'] ?? null;
            $pt = isset($registration->paymentType) ? strtolower((string) $registration->paymentType) : null;
            $amount = null;
            if ($pt === 'partial') {
                $amount = is_numeric($instTotal) ? (float) $instTotal : (is_numeric($totalDue) ? (float) $totalDue : 0.0);
            } else {
                $amount = is_numeric($totalDue) ? (float) $totalDue : (is_numeric($instTotal) ? (float) $instTotal : 0.0);
            }
            if (is_finite($amount)) {
                // Resolve acting cashier by faculty_id (= $actorId)
                $cashier = null;
                if (!empty($actorId)) {
                    $cashier = DB::table('tb_mas_cashiers')
                        ->where('faculty_id', (int) $actorId)
                        ->select('intID', 'campus_id', 'invoice_current')
                        ->first();
                }

                $payload = [
                    'source' => 'tuition-save',
                    'meta'   => [
                        'registration_id' => (int) $registration->intRegistrationID,
                        'syid'            => (int) $syid,
                        'total_due'       => $amount,
                    ],
                ];

                $options = ['payload' => $payload];

                if ($cashier) {
                    if (isset($cashier->campus_id)) {
                        $options['campus_id'] = (int) $cashier->campus_id;
                    }
                    $options['cashier_id'] = (int) $cashier->intID;
                    if (!empty($cashier->invoice_current)) {
                        $options['invoice_number'] = (int) $cashier->invoice_current;
                    }
                }

                // Only cashiers can generate/save invoices; skip when no cashier context
                if ($cashier) {
                    app(\App\Services\InvoiceService::class)->upsertTuitionByRegistration(
                        (int) $registration->intRegistrationID,
                        (int) $user->intID,
                        (int) $syid,
                        $amount,
                        $options,
                        $actorId
                    );

                    // Increment the cashier invoice_current if we consumed one
                    if (!empty($options['invoice_number'])) {
                        DB::table('tb_mas_cashiers')
                            ->where('intID', (int) $cashier->intID)
                            ->update(['invoice_current' => (int)$cashier->invoice_current + 1]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore invoice failures to avoid disrupting tuition save.
        }

        return [
            'id'                => (int) $savedId,
            'intStudentID'      => (int) $user->intID,
            'intRegistrationID' => (int) $registration->intRegistrationID,
            'syid'              => (int) $syid,
            'saved_by'          => $actorId,
            'overwritten'       => $overwritten,
            'saved_at'          => $now,
        ];
    }
}
