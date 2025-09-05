<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviousSchoolUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled via route middleware('role:admissions,admin')
        return true;
    }

    public function rules(): array
    {
        $id = (int) ($this->route('id') ?? $this->input('id') ?? 0);
        $city = $this->input('city');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                // unique on (name, city) but ignore current id
                Rule::unique('previous_schools', 'name')
                    ->where(function ($q) use ($city) {
                        return $q->where('city', $city);
                    })
                    ->ignore($id, 'intID'),
            ],
            'city' => ['sometimes', 'nullable', 'string', 'max:128'],
            'province' => ['sometimes', 'nullable', 'string', 'max:128'],
            'country' => ['sometimes', 'nullable', 'string', 'max:128'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'school name',
            'city' => 'city',
            'province' => 'province',
            'country' => 'country',
        ];
    }
}
