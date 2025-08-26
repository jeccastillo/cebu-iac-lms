<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CurriculumUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // For create requests, provide safe defaults mirroring CI behavior
        if ($this->isMethod('post')) {
            $this->merge([
                'active'     => $this->input('active', 1),
                'isEnhanced' => $this->input('isEnhanced', 0),
            ]);
        }
    }

    public function rules(): array
    {
        // Common rules
        $rules = [
            'strName'      => ['string', 'max:255'],
            'intProgramID' => ['integer'],
            'campus_id'    => ['integer'],
            'active'       => ['boolean'],
            'isEnhanced'   => ['boolean'],
        ];

        if ($this->isMethod('post')) {
            // Create
            $rules['strName'][] = 'required';
            $rules['intProgramID'][] = 'required';
            $rules['campus_id'][] = 'required';
        } else {
            // Update (PUT/PATCH): all fields optional when present
            $rules['strName'][] = 'sometimes';
            $rules['intProgramID'][] = 'sometimes';
            $rules['campus_id'][] = 'sometimes';
            $rules['active'][] = 'sometimes';
            $rules['isEnhanced'][] = 'sometimes';
        }

        return $rules;
    }
}
