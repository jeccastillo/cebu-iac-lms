<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClasslistGradesImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization enforced at controller via Gate and header-role fallbacks
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => ['required', 'in:midterm,finals'],
            // Strictly .xlsx only as per requirement
            'file'   => ['required', 'file', 'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];
    }

    public function messages(): array
    {
        return [
            'period.required' => 'The period parameter is required.',
            'period.in'       => 'The period must be either "midterm" or "finals".',
            'file.required'   => 'Please upload an .xlsx file.',
            'file.file'       => 'The upload must be a valid file.',
            'file.mimetypes'  => 'Unsupported file type. Please upload an .xlsx file.',
        ];
    }
}
