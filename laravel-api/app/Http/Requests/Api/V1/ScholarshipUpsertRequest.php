<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ScholarshipUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policies/authorization can be enforced later.
        return true;
    }

    public function rules(): array
    {
        return [
            'student_number'  => 'required|string',
            'scholarship_code'=> 'required|string',
            'deduction_type'  => 'required|in:discount,scholarship',
            'deduction_from'  => 'required|in:in-house,external',
            'percent'         => 'nullable|numeric|min:0|max:100',
            'fixed_amount'    => 'nullable|numeric|min:0',
            'status'          => 'nullable|in:active,suspended,revoked,expired,pending',
            'effective_from'  => 'nullable|date',
            'effective_to'    => 'nullable|date|after_or_equal:effective_from',
        ];
    }

    public function messages(): array
    {
        return [
            'student_number.required' => 'Student number is required.',
            'scholarship_code.required' => 'Scholarship code is required.',
            'deduction_type.required' => 'Deduction type is required.',
            'deduction_type.in' => 'Deduction type must be one of: discount, scholarship.',
            'deduction_from.required' => 'Deduction from is required.',
            'deduction_from.in' => 'Deduction from must be one of: in-house, external.',
            'percent.numeric' => 'Percent must be a number.',
            'percent.min' => 'Percent must be at least 0.',
            'percent.max' => 'Percent must be at most 100.',
            'fixed_amount.numeric' => 'Fixed amount must be numeric.',
            'fixed_amount.min' => 'Fixed amount must be at least 0.',
            'status.in' => 'Invalid status value.',
            'effective_to.after_or_equal' => 'Effective to must be after or equal to effective from.',
        ];
    }
}
