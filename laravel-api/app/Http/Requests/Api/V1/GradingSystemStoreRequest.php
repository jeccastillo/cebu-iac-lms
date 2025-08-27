<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GradingSystemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization enforced at route level via role middleware
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:tb_mas_grading,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.unique' => 'A grading system with this name already exists',
        ];
    }
}
