<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class TuitionYearAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a Tuition Year row.
     */
    public function rules(): array
    {
        return [
            'year'                  => ['required', 'string', 'max:50'],
            'isDefault'             => ['nullable', 'boolean'],
            'isDefaultShs'          => ['nullable', 'boolean'],

            // Monetary / numeric fields (nullable + defaults in prepareForValidation)
            'pricePerUnit'          => ['nullable', 'numeric'],
            'pricePerUnitOnline'    => ['nullable', 'numeric'],
            'pricePerUnitHybrid'    => ['nullable', 'numeric'],
            'pricePerUnitHyflex'    => ['nullable', 'numeric'],

            'installmentDP'         => ['nullable', 'numeric'],
            'installmentIncrease'   => ['nullable', 'numeric'],
        ];
    }

    /**
     * Provide safe defaults and normalize types expected by the legacy schema.
     */
    protected function prepareForValidation(): void
    {
        $in = $this->all();

        $defaults = [
            'isDefault'            => 0,
            'isDefaultShs'         => 0,

            'pricePerUnit'         => 0,
            'pricePerUnitOnline'   => 0,
            'pricePerUnitHybrid'   => 0,
            'pricePerUnitHyflex'   => 0,

            'installmentDP'        => 0,
            'installmentIncrease'  => 0,
        ];

        foreach ($defaults as $k => $v) {
            if (!array_key_exists($k, $in) || $in[$k] === '' || $in[$k] === null) {
                $in[$k] = $v;
            }
        }

        // Normalize booleans to 0/1 tinyint
        foreach (['isDefault', 'isDefaultShs'] as $flag) {
            $in[$flag] = isset($in[$flag]) ? (int) (bool) $in[$flag] : 0;
        }

        // Coerce numeric fields
        $numericFields = [
            'pricePerUnit',
            'pricePerUnitOnline',
            'pricePerUnitHybrid',
            'pricePerUnitHyflex',
            'installmentDP',
            'installmentIncrease',
        ];
        foreach ($numericFields as $nf) {
            $in[$nf] = is_numeric($in[$nf]) ? 0 + $in[$nf] : 0;
        }

        // Trim year
        if (isset($in['year'])) {
            $in['year'] = trim((string) $in['year']);
        }

        $this->merge($in);
    }
}
