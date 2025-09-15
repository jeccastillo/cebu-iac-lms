<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentDeficienciesRequest extends FormRequest
{
    /**
     * Authorize all authenticated callers; route middleware will enforce roles.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for fetching student deficiencies across all terms.
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer'],
        ];
    }

    /**
     * Custom messages for clarity.
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'student_id is required.',
            'student_id.integer'  => 'student_id must be an integer.',
        ];
    }
}
