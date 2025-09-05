<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ApplicantInterviewResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware enforces role: admissions,admin
        return true;
    }

    public function rules(): array
    {
        return [
            'assessment'          => ['required', 'in:Passed,Failed'],
            'remarks'             => ['nullable', 'string'],
            'reason_for_failing'  => ['nullable', 'string', 'required_if:assessment,Failed', 'max:255'],
            'completed_at'        => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'assessment'         => 'assessment',
            'remarks'            => 'remarks',
            'reason_for_failing' => 'reason for failing',
            'completed_at'       => 'completion time',
        ];
    }

    public function messages(): array
    {
        return [
            'assessment.required' => 'Assessment is required.',
            'assessment.in'       => 'Assessment must be either Passed or Failed.',
            'reason_for_failing.required_if' => 'Reason for failing is required when assessment is Failed.',
            'reason_for_failing.max' => 'Reason for failing must not exceed 255 characters.',
            'completed_at.date'   => 'Completed at must be a valid date/time.',
        ];
    }
}
