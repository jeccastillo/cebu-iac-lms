<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ScholarshipUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role middleware guards these endpoints; allow request validation here.
        return true;
    }

    public function rules(): array
    {
        // Expect route parameter name to be {id} -> /api/v1/scholarships/{id}
        $id = (int) ($this->route('id') ?? 0);

        return [
            'code'            => 'sometimes|string|max:64|unique:tb_mas_scholarships,code,' . $id . ',intID',
            'name'            => 'sometimes|string|max:255|unique:tb_mas_scholarships,name,' . $id . ',intID',
            'deduction_type'  => 'sometimes|in:discount,scholarship',
            'deduction_from'  => 'sometimes|in:in-house,external',
            'status'          => 'sometimes|in:active,inactive,suspended,revoked,expired,pending',
            'percent'         => 'sometimes|nullable|numeric|min:0|max:100',
            'fixed_amount'    => 'sometimes|nullable|numeric|min:0',
            'description'     => 'sometimes|nullable|string|max:2000',
            'max_stacks'      => 'sometimes|integer|min:1|max:255',
            'compute_full'    => 'sometimes|boolean',

            'created_by_id'           => 'sometimes|integer|min:1',

            'tuition_fee_rate'        => 'sometimes|nullable|integer|min:0|max:100',
            'tuition_fee_fixed'       => 'sometimes|nullable|numeric|min:0',

            'basic_fee_rate'          => 'sometimes|nullable|integer|min:0|max:100',
            'basic_fee_fixed'         => 'sometimes|nullable|numeric|min:0',

            'misc_fee_rate'           => 'sometimes|nullable|integer|min:0|max:100',
            'misc_fee_fixed'          => 'sometimes|nullable|numeric|min:0',

            'lab_fee_rate'            => 'sometimes|nullable|integer|min:0|max:100',
            'lab_fee_fixed'           => 'sometimes|nullable|numeric|min:0',

            'penalty_fee_rate'        => 'sometimes|nullable|integer|min:0|max:100',
            'penalty_fee_fixed'       => 'sometimes|nullable|numeric|min:0',

            'other_fees_rate'         => 'sometimes|nullable|integer|min:0|max:100',
            'other_fees_fixed'        => 'sometimes|nullable|numeric|min:0',

            'total_assessment_rate'   => 'sometimes|nullable|integer|min:0|max:100',
            'total_assessment_fixed'  => 'sometimes|nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Code must be unique.',
            'name.unique' => 'Name must be unique.',
            'deduction_type.in' => 'Deduction type must be one of: discount, scholarship.',
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
