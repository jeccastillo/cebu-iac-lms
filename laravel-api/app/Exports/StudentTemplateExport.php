<?php

namespace App\Exports;

use App\Services\StudentImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * StudentTemplateExport
 *
 * Thin wrapper around StudentImportService to build the students import template Spreadsheet.
 */
class StudentTemplateExport
{
    protected StudentImportService $service;

    public function __construct(StudentImportService $service)
    {
        $this->service = $service;
    }

    /**
     * Build and return the template Spreadsheet instance.
     */
    public function build(): Spreadsheet
    {
        return $this->service->generateTemplateXlsx();
    }
}
