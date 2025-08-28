<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClasslistGradeSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled via gates in controller; allow validation to run.
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['required', 'string', 'in:midterm,finals'],
            'overwrite_ngs' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.intCSID' => ['required', 'integer'],
            // Accept numeric (1..100) or system item value (string/number), validation refined in controller
            'items.*.grade' => ['required'],
            'items.*.remarks' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'period.required' => 'Grading period is required.',
            'period.in' => 'Grading period must be either midterm or finals.',
            'items.required' => 'Grade items payload is required.',
            'items.min' => 'At least one grade item is required.',
            'items.*.intCSID.required' => 'Each item must include intCSID.',
            'items.*.grade.required' => 'Each item must include a grade value.',
        ];
    }
}
