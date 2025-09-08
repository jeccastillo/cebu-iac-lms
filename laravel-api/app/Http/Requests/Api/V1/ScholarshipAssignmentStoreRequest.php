<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ScholarshipAssignmentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Routes using this request are already role-gated to scholarship/admin
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id'  => ['required', 'integer'],
            'syid'        => ['required', 'integer'],
            'discount_id' => ['required', 'integer', 'exists:tb_mas_scholarships,intID'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required'  => 'Student is required.',
            'student_id.integer'   => 'Student ID must be an integer.',
            'syid.required'        => 'Term (syid) is required.',
            'syid.integer'         => 'Term (syid) must be an integer.',
            'discount_id.required' => 'Scholarship/discount selection is required.',
            'discount_id.integer'  => 'Scholarship/discount id must be an integer.',
            'discount_id.exists'   => 'Selected scholarship/discount does not exist.',
        ];
    }
}
