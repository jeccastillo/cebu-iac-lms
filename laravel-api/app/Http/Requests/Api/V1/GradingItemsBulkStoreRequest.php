<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GradingItemsBulkStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled via route middleware (role)
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.value' => ['required'],
            'items.*.remarks' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Items payload is required',
            'items.array' => 'Items must be an array',
            'items.min' => 'At least one item is required',
            'items.*.value.required' => 'Each item must have a value',
            // 'items.*.value.numeric' => 'Each item value must be numeric',
            'items.*.remarks.required' => 'Each item must have remarks',
        ];
    }
}
