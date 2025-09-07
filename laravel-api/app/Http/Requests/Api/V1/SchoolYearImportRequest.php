<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SchoolYearImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by route middleware (role:registrar,admin)
        return true;
    }

    public function rules(): array
    {
        return [
            'file'    => 'required|file|mimes:xlsx,xls,csv',
            'dry_run' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Upload file is required.',
            'file.mimes'    => 'Unsupported file type. Please upload .xlsx, .xls, or .csv.',
        ];
    }
}
