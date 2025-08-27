<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust if you need role-based checks
        return true;
    }

    public function rules(): array
    {
        return [
            // Allow null to support fallback to tb_mas_users.intCurriculumID
            'intCurriculumID' => ['nullable','integer','min:1'],
            'remarks'         => ['nullable','string','max:255'],
        ];
    }
}
