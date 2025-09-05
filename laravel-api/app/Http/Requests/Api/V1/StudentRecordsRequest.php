<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for fetching student records (and optional grades).
     */
    public function rules(): array
    {
        return [
            'student_id'     => ['required', 'integer'],
            'include_grades' => ['sometimes', 'boolean'],
            'term'           => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $in = $this->all();

        // Normalize include_grades to boolean
        if (array_key_exists('include_grades', $in)) {
            $in['include_grades'] = (bool) $in['include_grades'];
        }

        // Normalize student_id to integer if present
        if (array_key_exists('student_id', $in)) {
            $in['student_id'] = (int) $in['student_id'];
        }

        $this->merge($in);
    }
}
