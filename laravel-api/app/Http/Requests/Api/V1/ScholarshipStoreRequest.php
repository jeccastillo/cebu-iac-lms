<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ScholarshipStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role middleware guards these endpoints; allow request validation here.
        return true;
    }

    public function rules(): array
    {
        return [
            'code'            => 'required|string|max:64|unique:tb_mas_scholarships,code',
            'name'            => 'required|string|max:255|unique:tb_mas_scholarships,name',
            'deduction_type'  => 'required|in:discount,scholarship',
            'deduction_from'  => 'required|in:in-house,external',
            'status'          => 'sometimes|in:active,inactive,suspended,revoked,expired,pending',
            'percent'         => 'nullable|numeric|min:0|max:100',
            'fixed_amount'    => 'nullable|numeric|min:0',
            'description'     => 'nullable|string|max:2000',

            'created_by_id'           => 'sometimes|integer|min:1',

            'tuition_fee_rate'        => 'nullable|integer|min:0|max:100',
            'tuition_fee_fixed'       => 'nullable|numeric|min:0',

            'basic_fee_rate'          => 'nullable|integer|min:0|max:100',
            'basic_fee_fixed'         => 'nullable|numeric|min:0',

            'misc_fee_rate'           => 'nullable|integer|min:0|max:100',
            'misc_fee_fixed'          => 'nullable|numeric|min:0',

            'lab_fee_rate'            => 'nullable|integer|min:0|max:100',
            'lab_fee_fixed'           => 'nullable|numeric|min:0',

            'penalty_fee_rate'        => 'nullable|integer|min:0|max:100',
            'penalty_fee_fixed'       => 'nullable|numeric|min:0',

            'other_fees_rate'         => 'nullable|integer|min:0|max:100',
            'other_fees_fixed'        => 'nullable|numeric|min:0',

            'total_assessment_rate'   => 'nullable|integer|min:0|max:100',
            'total_assessment_fixed'  => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Code is required.',
            'code.unique' => 'Code must be unique.',
            'name.required' => 'Name is required.',
            'name.unique' => 'Name must be unique.',
            'deduction_type.required' => 'Deduction type is required.',
            'deduction_type.in' => 'Deduction type must be one of: discount, scholarship.',
            'deduction_from.required' => 'Deduction from is required.',
            'deduction_from.in' => 'Deduction from must be one of: in-house, external.',
            'status.in' => 'Invalid status value.',
            'percent.numeric' => 'Percent must be numeric.',
            'percent.min' => 'Percent must be at least 0.',
            'percent.max' => 'Percent must be at most 100.',
            'fixed_amount.numeric' => 'Fixed amount must be numeric.',
            'fixed_amount.min' => 'Fixed amount must be at least 0.',
        ];
    }
}
