<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SubjectSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a Subject.
     * Note: legacy schema stores some numeric values as strings (e.g., strUnits).
     */
    public function rules(): array
    {
        return [
            'strCode'                  => ['required', 'string', 'max:50'], // will be truncated to 20 in prepareForValidation
            'strDescription'           => ['required', 'string', 'max:255'],

            'strUnits'                 => ['nullable', 'string', 'max:20'],
            'strTuitionUnits'          => ['nullable', 'string', 'max:20'],

            'strLabClassification'     => ['nullable', 'string', 'max:50'],
            'intLab'                   => ['nullable', 'integer'],

            'strDepartment'            => ['nullable', 'string', 'max:100'],

            'intLectHours'             => ['nullable', 'integer'],
            'intPrerequisiteID'        => ['nullable', 'integer'],
            'intEquivalentID1'         => ['nullable', 'integer'],
            'intEquivalentID2'         => ['nullable', 'integer'],
            'intProgramID'             => ['nullable', 'integer'],

            'isNSTP'                   => ['nullable', 'boolean'],
            'isThesisSubject'          => ['nullable', 'boolean'],
            'isInternshipSubject'      => ['nullable', 'boolean'],
            'include_gwa'              => ['nullable', 'boolean'],
            'grading_system_id'        => ['nullable', 'integer'],
            'grading_system_id_midterm'=> ['nullable', 'integer'],
            'isElective'               => ['nullable', 'boolean'],
            'isSelectableElective'     => ['nullable', 'boolean'],

            'strand'                   => ['nullable', 'string', 'max:50'],
            'intBridging'              => ['nullable', 'integer'],
            'intMajor'                 => ['nullable', 'integer'],
        ];
    }

    /**
     * Provide safe defaults for legacy non-nullable columns and normalize types.
     */
    protected function prepareForValidation(): void
    {
        $in = $this->all();

        $defaults = [
            'strUnits'                  => '0',
            'strTuitionUnits'           => null,
            'strLabClassification'      => 'none',
            'intLab'                    => 0,
            'strDepartment'             => null,
            'intLectHours'              => 0,
            'intPrerequisiteID'         => 0,
            'intEquivalentID1'          => 0,
            'intEquivalentID2'          => 0,
            'intProgramID'              => 0,
            'isNSTP'                    => 0,
            'isThesisSubject'           => 0,
            'isInternshipSubject'       => 0,
            'include_gwa'               => 0,
            'grading_system_id'         => null,
            'grading_system_id_midterm' => null,
            'isElective'                => 0,
            'isSelectableElective'      => 0,
            'strand'                    => null,
            'intBridging'               => 0,
            'intMajor'                  => 0,
        ];

        // Merge defaults where missing or empty
        foreach ($defaults as $k => $v) {
            if (!array_key_exists($k, $in) || $in[$k] === '' || $in[$k] === null) {
                $in[$k] = $v;
            }
        }

        // Fallback tuition units to units when missing
        if ($in['strTuitionUnits'] === null || $in['strTuitionUnits'] === '') {
            $in['strTuitionUnits'] = isset($in['strUnits']) && $in['strUnits'] !== '' ? (string) $in['strUnits'] : '0';
        }

        // Normalize booleans to 0/1 integers for legacy schema (tinyint)
        foreach (['isNSTP','isThesisSubject','isInternshipSubject','include_gwa','isElective','isSelectableElective'] as $flag) {
            $in[$flag] = isset($in[$flag]) ? (int) (bool) $in[$flag] : 0;
        }

        // Trim and cap strCode to legacy max length (20)
        if (isset($in['strCode'])) {
            $in['strCode'] = substr(trim((string) $in['strCode']), 0, 20);
        }

        $this->merge($in);
    }
}
