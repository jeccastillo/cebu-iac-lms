<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RequirementUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled via route middleware('role:admissions,admin')
        return true;
    }

    public function rules(): array
    {
        $id = (int) ($this->route('id') ?? 0);

        return [
            'name'       => ['sometimes', 'string', 'max:255', "unique:tb_mas_requirements,name,{$id},intID"],
            'type'       => ['sometimes', 'string', 'in:college,shs,grad'],
            'is_foreign' => ['sometimes', 'boolean'],
            'is_initial_requirements' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                     => 'requirement name',
            'type'                     => 'requirement type',
            'is_foreign'              => 'foreign applicant flag',
            'is_initial_requirements' => 'initial requirements flag',
        ];
    }
}
