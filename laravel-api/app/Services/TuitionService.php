<?php

namespace App\Services;

use Illuminate\Support\Arr;

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
}
