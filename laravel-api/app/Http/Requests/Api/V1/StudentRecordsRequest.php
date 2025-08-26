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
            'student_number' => ['required', 'string'],
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

        $this->merge($in);
    }
}
