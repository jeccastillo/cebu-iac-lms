<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ScholarshipAssignmentApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Routes using this request are already role-gated to scholarship/admin
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer'],
            'force'  => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'IDs are required.',
            'ids.array'    => 'IDs must be an array.',
            'ids.min'      => 'At least one ID must be provided.',
            'ids.*.integer'=> 'Each ID must be an integer.',
        ];
    }
}
