<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentAdvisorAssignBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is enforced by route middleware('role:faculty_admin,admin')
        return true;
    }

    public function rules(): array
    {
        return [
            'student_ids' => ['sometimes', 'array'],
            'student_ids.*' => ['integer', 'min:1'],

            'student_numbers' => ['sometimes', 'array'],
            'student_numbers.*' => ['string', 'max:50'],

            'replace_existing' => ['sometimes', 'boolean'],
            'campus_id' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_ids' => 'student IDs',
            'student_numbers' => 'student numbers',
            'replace_existing' => 'replace existing advisor',
            'campus_id' => 'campus',
        ];
    }

    /**
     * Ensure at least one of student_ids or student_numbers is provided.
     */
    protected function prepareForValidation(): void
    {
        // Normalize empty arrays to []
        $ids = $this->input('student_ids', []);
        $sns = $this->input('student_numbers', []);

        if (!is_array($ids)) {
            $ids = [];
        }
        if (!is_array($sns)) {
            $sns = [];
        }

        $this->merge([
            'student_ids' => $ids,
            'student_numbers' => $sns,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $ids = $this->input('student_ids', []);
            $sns = $this->input('student_numbers', []);

            if (empty($ids) && empty($sns)) {
                $v->errors()->add('student_ids', 'Provide at least one student identifier (IDs or student numbers).');
                $v->errors()->add('student_numbers', 'Provide at least one student identifier (IDs or student numbers).');
            }
        });
    }
}
