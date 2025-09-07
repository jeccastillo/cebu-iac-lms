<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StudentImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role middleware (role: registrar,admin) should guard this route.
        return true;
    }

    /**
     * Normalize/guard inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        // Allow flexible field names but prefer "file"
        $file = $this->file('file')
            ?? $this->file('upload')
            ?? $this->file('students')
            ?? null;

        if ($file !== null) {
            // Force into "file" key for validation rules below
            $this->files->set('file', $file);
        }

        // Optional flags (no hard validation, just normalize)
        $dryRun = $this->input('dry_run', $this->input('dryRun', null));
        if ($dryRun !== null) {
            $this->merge([
                'dry_run' => filter_var($dryRun, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // Accept .xlsx, .xls, .csv up to ~10MB (adjust as needed)
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240', // size in KB (10 MB)
            ],
            // Optional dry-run toggle to parse/validate without writing
            'dry_run' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'import file',
            'dry_run' => 'dry run',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The upload must be a valid file.',
            'file.mimes' => 'Only .xlsx, .xls, or .csv files are supported.',
            'file.max' => 'The file is too large. Please upload a file up to 10MB.',
        ];
    }
}
