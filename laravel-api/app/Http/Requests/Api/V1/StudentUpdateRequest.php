<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is protected by middleware('role:admin'); allow here.
        return true;
    }

    public function rules(): array
    {
        // Accept a flexible payload; filtering is done in controller by existing columns.
        // Explicitly allow any keys; block intID changes in controller.
        return [
            // no strict field rules; optionally ensure payload is an array for JSON
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
