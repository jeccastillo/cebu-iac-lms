<?php

namespace App\Http\Requests\Api\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceMatrixTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization enforced in controller via Gate/header fallbacks
        return true;
    }

    public function rules(): array
    {
        return [
            'start'  => ['required', 'date_format:Y-m-d'],
            'end'    => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
            'period' => ['required', 'in:midterm,finals'],
        ];
    }

    public function messages(): array
    {
        return [
            'start.required'          => 'Start date is required.',
            'start.date_format'       => 'Start date must be in YYYY-MM-DD format.',
            'end.required'            => 'End date is required.',
            'end.date_format'         => 'End date must be in YYYY-MM-DD format.',
            'end.after_or_equal'      => 'End date must be on or after the start date.',
            'period.required'         => 'Attendance period is required.',
            'period.in'               => 'Attendance period must be either midterm or finals.',
        ];
    }
}
