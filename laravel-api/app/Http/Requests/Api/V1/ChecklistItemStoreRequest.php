<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // apply role middleware on route if needed
    }

    public function rules(): array
    {
        return [
            'intChecklistID' => ['required','integer','min:1'],
            'intSubjectID'   => ['required','integer','min:1'],
            'strStatus'      => ['nullable','string','in:planned,in-progress,passed,failed,waived'],
            'dteCompleted'   => ['nullable','date'],
            'isRequired'     => ['nullable','boolean'],
            'intYearLevel'   => ['nullable','integer','min:1','max:10'],
            'intSem'         => ['nullable','integer','min:1','max:3'],
        ];
    }
}
