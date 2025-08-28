<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class TuitionComputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Read-only endpoint; gated by controller/middleware if needed
        return true;
    }

    public function rules(): array
    {
        return [
            'student_number' => ['required', 'string'],
            'term'           => ['required', 'integer'],
            'discount_id'    => ['nullable', 'integer'],
            'scholarship_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_number.required' => 'student_number is required',
            'term.required'           => 'term (syid) is required',
            'term.integer'            => 'term must be an integer (tb_mas_sy.intID)',
        ];
    }
}
