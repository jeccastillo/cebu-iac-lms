<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ApplicantType;

class ApplicantTypeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization via route middleware ('role:admissions,admin')
        return true;
    }

    public function rules(): array
    {
        $id = (int) ($this->route('id') ?? 0);
        // If 'type' is not supplied in payload during update, use the current value from payload fallback
        // We still validate 'type' when supplied.
        $type = $this->input('type');
        $resolvedType = $type;
        if ($resolvedType === null) {
            $row = ApplicantType::find($id);
            if ($row) {
                $resolvedType = $row->type;
            }
        }

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                // Unique composite on (name, type) ignoring current row
                Rule::unique('tb_mas_applicant_types', 'name')
                    ->where(function ($q) use ($resolvedType) {
                        // Enforce composite uniqueness (name, resolved type)
                        if ($resolvedType !== null) {
                            $q->where('type', $resolvedType);
                        }
                        return $q;
                    })
                    ->ignore($id, 'intID'),
            ],
            'type' => [
                'sometimes',
                'required',
                Rule::in(['college', 'shs', 'grad']),
            ],
            'sub_type' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'applicant type name',
            'type' => 'applicant category',
        ];
    }
}
