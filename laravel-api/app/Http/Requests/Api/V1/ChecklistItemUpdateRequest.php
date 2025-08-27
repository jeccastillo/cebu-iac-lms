<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistItemUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Apply route middleware for roles if needed
    }

    public function rules(): array
    {
        return [
            'strStatus'    => ['nullable','string','in:planned,in-progress,passed,failed,waived'],
            'dteCompleted' => ['nullable','date'],
            'isRequired'   => ['nullable','boolean'],
            'intYearLevel' => ['nullable','integer','min:1','max:10'],
            'intSem'       => ['nullable','integer','min:1','max:3'],
        ];
    }
}
