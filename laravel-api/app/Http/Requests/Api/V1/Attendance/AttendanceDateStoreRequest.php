<?php

namespace App\Http\Requests\Api\V1\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced in controller via Gate and header fallbacks
        return true;
    }

    public function rules(): array
    {
        return [
            'date'   => ['required', 'date_format:Y-m-d'],
            'period' => ['required', 'in:midterm,finals'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Attendance date is required.',
            'date.date_format' => 'Attendance date must be in YYYY-MM-DD format.',
            'period.required' => 'Attendance period is required.',
            'period.in' => 'Attendance period must be either midterm or finals.',
        ];
    }
}
