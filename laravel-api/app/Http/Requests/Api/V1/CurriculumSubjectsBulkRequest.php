<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CurriculumSubjectsBulkRequest extends FormRequest
{
    /**
     * Authorization is handled by route middleware (role:registrar,admin).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize boolean flags and ensure structure before validation.
     */
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Coerce update_if_exists to boolean (accept "1","0","true","false", 1, 0)
        if (array_key_exists('update_if_exists', $data)) {
            $val = $data['update_if_exists'];
            $bool = false;
            if (is_bool($val)) {
                $bool = $val;
            } elseif (is_numeric($val)) {
                $bool = ((int)$val) === 1;
            } elseif (is_string($val)) {
                $bool = in_array(strtolower($val), ['1','true','yes','on'], true);
            }
            $data['update_if_exists'] = $bool;
        }

        // Ensure subjects is an array, and coerce int fields
        if (!isset($data['subjects']) || !is_array($data['subjects'])) {
            // leave as-is, rules() will catch missing/invalid array
        } else {
            $normalized = [];
            foreach ($data['subjects'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $normalized[] = [
                    'intSubjectID' => isset($item['intSubjectID']) ? (int)$item['intSubjectID'] : null,
                    'intYearLevel' => isset($item['intYearLevel']) ? (int)$item['intYearLevel'] : null,
                    'intSem'       => isset($item['intSem']) ? (int)$item['intSem'] : null,
                ];
            }
            $data['subjects'] = $normalized;
        }

        $this->replace($data);
    }

    /**
     * Validation rules for bulk curriculum-subject add/update.
     */
    public function rules(): array
    {
        return [
            'update_if_exists' => ['sometimes', 'boolean'],
            'subjects'         => ['required', 'array', 'min:1', 'max:60'],

            'subjects.*.intSubjectID' => ['required', 'integer', 'min:1'],
            'subjects.*.intYearLevel' => ['required', 'integer', 'min:1', 'max:10'],
            'subjects.*.intSem'       => ['required', 'integer', 'min:1', 'max:3'],
        ];
    }

    /**
     * Custom messages for clearer client feedback.
     */
    public function messages(): array
    {
        return [
            'subjects.required' => 'The subjects array is required.',
            'subjects.array'    => 'The subjects field must be an array.',
            'subjects.min'      => 'At least one subject must be provided.',
            'subjects.max'      => 'A maximum of 60 subjects can be added per request.',

            'subjects.*.intSubjectID.required' => 'Each subject item must include intSubjectID.',
            'subjects.*.intSubjectID.integer'  => 'intSubjectID must be an integer.',
            'subjects.*.intSubjectID.min'      => 'intSubjectID must be at least 1.',

            'subjects.*.intYearLevel.required' => 'Each subject item must include intYearLevel.',
            'subjects.*.intYearLevel.integer'  => 'intYearLevel must be an integer.',
            'subjects.*.intYearLevel.min'      => 'intYearLevel must be at least 1.',
            'subjects.*.intYearLevel.max'      => 'intYearLevel must not be greater than 10.',

            'subjects.*.intSem.required' => 'Each subject item must include intSem.',
            'subjects.*.intSem.integer'  => 'intSem must be an integer.',
            'subjects.*.intSem.min'      => 'intSem must be at least 1.',
            'subjects.*.intSem.max'      => 'intSem must not be greater than 3.',
        ];
    }
}
