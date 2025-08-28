<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClasslistFinalizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Controller will authorize via gates; allow validation here.
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['required', 'string', 'in:midterm,finals'],
            'confirm_complete' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'period.required' => 'Grading period is required.',
            'period.in' => 'Grading period must be either midterm or finals.',
        ];
    }
}
