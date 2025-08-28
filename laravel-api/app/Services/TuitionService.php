<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
        // Validate student exists
        $user = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        if (!$user) {
            throw new \InvalidArgumentException('Student not found');
        }

        // Validate term exists
        $sy = DB::table('tb_mas_sy')->where('intID', $syid)->first();
        if (!$sy) {
            throw new \InvalidArgumentException('Term not found');
        }

        // Registration for the given term
        $registration = DB::table('tb_mas_registration')
            ->where('intStudentID', $user->intID)
            ->where('intAYID', $syid)
            ->first();

        if (!$registration) {
            throw new \InvalidArgumentException('Registration not found for term');
        }

        // Must have tuition_year selected on registration
        $tuitionYearId = $registration->tuition_year ?? null;
        if (!$tuitionYearId) {
            throw new \InvalidArgumentException('Registration missing tuition_year');
        }

        // Minimal shaped output (zeros) for critical-path endpoint readiness.
        // Full parity logic will replace these placeholders in subsequent steps.
        $summary = [
            'tuition'            => 0.0,
            'misc_total'         => 0.0,
            'lab_total'          => 0.0,
            'discounts_total'    => 0.0,
            'scholarships_total' => 0.0,
            'additional_total'   => 0.0,
            'total_due'          => 0.0,
        ];

        $items = [
            'tuition'      => [],
            'misc'         => [],
            'lab'          => [],
            'discounts'    => [],
            'scholarships' => [],
            'additional'   => [],
        ];

        $meta = [
            'class_type'        => $registration->type_of_class ?? null,
            'tuition_year_id'   => (int) $tuitionYearId,
            'year_level'        => isset($registration->intYearLevel) ? (int) $registration->intYearLevel : null,
            'program_id_used'   => isset($registration->current_program) && $registration->current_program ? (int) $registration->current_program : (int) $user->intProgramID,
            'student_id'        => (int) $user->intID,
            'syid'              => (int) $syid,
            'computed_at'       => now()->toDateTimeString(),
            'discount_id'       => $discountId,
            'scholarship_id'    => $scholarshipId,
        ];

        return [
            'summary' => $summary,
            'items'   => $items,
            'meta'    => $meta,
        ];
    }
}
