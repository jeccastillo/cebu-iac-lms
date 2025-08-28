<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SchoolYearUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled at the route middleware level (role:registrar,admin)
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = $this->all();

        // Normalize empty strings to null for nullable fields
        $nullable = [
            'campus_id',
            'midterm_start', 'midterm_end',
            'final_start', 'final_end',
            'end_of_submission',
            'term_label', 'term_student_type',
            'enumStatus', 'enumFinalized',
            'intProcessing',

            // Academic timeline
            'start_of_classes', 'final_exam_start', 'final_exam_end',
            // Viewing windows
            'viewing_midterm_start', 'viewing_midterm_end',
            'viewing_final_start', 'viewing_final_end',
            // Application/reconciliation dates
            'endOfApplicationPeriod', 'reconf_start', 'reconf_end', 'ar_report_date_generation',
            // Installment schedule
            'installment1', 'installment2', 'installment3', 'installment4', 'installment5',
            // Operational flags (string enums)
            'classType',
            'enumGradingPeriod', 'enumMGradingPeriod', 'enumFGradingPeriod',
            // Toggles
            'pay_student_visa', 'is_locked',
        ];

        foreach ($nullable as $field) {
            if (array_key_exists($field, $input) && ($input[$field] === '' || $input[$field] === 'null')) {
                $input[$field] = null;
            }
        }

        // Coerce integers
        if (array_key_exists('campus_id', $input) && $input['campus_id'] !== null && $input['campus_id'] !== '') {
            $input['campus_id'] = (int) $input['campus_id'];
        }
        if (array_key_exists('intProcessing', $input) && $input['intProcessing'] !== null && $input['intProcessing'] !== '') {
            $input['intProcessing'] = (int) $input['intProcessing'];
        }
        if (array_key_exists('pay_student_visa', $input) && $input['pay_student_visa'] !== null && $input['pay_student_visa'] !== '') {
            $input['pay_student_visa'] = (int) $input['pay_student_visa'];
        }
        if (array_key_exists('is_locked', $input) && $input['is_locked'] !== null && $input['is_locked'] !== '') {
            $input['is_locked'] = (int) $input['is_locked'];
        }

        $this->replace($input);
    }

    public function rules(): array
    {
        return [
            // All fields optional on update; validate if present
            'enumSem'           => ['sometimes', 'string', 'max:16'],
            'strYearStart'      => ['sometimes', 'digits:4'],
            'strYearEnd'        => ['sometimes', 'digits:4', 'gte:strYearStart'],

            'term_label'        => ['sometimes', 'nullable', 'string', 'max:32'],
            'term_student_type' => ['sometimes', 'nullable', 'string', 'max:32'],
            'campus_id'         => ['sometimes', 'nullable', 'integer'],

            // Existing grading windows (date-only accepted)
            'midterm_start'     => ['sometimes', 'nullable', 'date'],
            'midterm_end'       => ['sometimes', 'nullable', 'date'],
            'final_start'       => ['sometimes', 'nullable', 'date'],
            'final_end'         => ['sometimes', 'nullable', 'date'],
            'end_of_submission' => ['sometimes', 'nullable', 'date'],

            // Academic timeline
            'start_of_classes'      => ['sometimes', 'nullable', 'date'],
            'final_exam_start'      => ['sometimes', 'nullable', 'date'],
            'final_exam_end'        => ['sometimes', 'nullable', 'date'],

            // Viewing windows
            'viewing_midterm_start' => ['sometimes', 'nullable', 'date'],
            'viewing_midterm_end'   => ['sometimes', 'nullable', 'date'],
            'viewing_final_start'   => ['sometimes', 'nullable', 'date'],
            'viewing_final_end'     => ['sometimes', 'nullable', 'date'],

            // Application / reconciliation
            'endOfApplicationPeriod'    => ['sometimes', 'nullable', 'date'],
            'reconf_start'              => ['sometimes', 'nullable', 'date'],
            'reconf_end'                => ['sometimes', 'nullable', 'date'],
            'ar_report_date_generation' => ['sometimes', 'nullable', 'date'],

            // Installments
            'installment1' => ['sometimes', 'nullable', 'date'],
            'installment2' => ['sometimes', 'nullable', 'date'],
            'installment3' => ['sometimes', 'nullable', 'date'],
            'installment4' => ['sometimes', 'nullable', 'date'],
            'installment5' => ['sometimes', 'nullable', 'date'],

            // Flags / enums
            'classType'          => ['sometimes', 'nullable', 'string', 'max:16'],
            'pay_student_visa'   => ['sometimes', 'nullable', 'integer', 'in:0,1'],
            'is_locked'          => ['sometimes', 'nullable', 'integer', 'in:0,1'],
            'enumGradingPeriod'  => ['sometimes', 'nullable', 'string', 'in:active,inactive'],
            'enumMGradingPeriod' => ['sometimes', 'nullable', 'string', 'in:active,inactive'],
            'enumFGradingPeriod' => ['sometimes', 'nullable', 'string', 'in:active,inactive'],

            'intProcessing'     => ['sometimes', 'nullable', 'integer', 'in:0,1'],
            'enumStatus'        => ['sometimes', 'nullable', 'string', 'max:16'],
            'enumFinalized'     => ['sometimes', 'nullable', 'string', 'max:8'],
        ];
    }
}
