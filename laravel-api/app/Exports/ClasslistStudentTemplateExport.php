<?php

namespace App\Exports;

use App\Services\ClasslistStudentImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ClasslistStudentTemplateExport
 *
 * Thin wrapper around ClasslistStudentImportService to build the class-records import template Spreadsheet.
 */
class ClasslistStudentTemplateExport
{
    protected ClasslistStudentImportService $service;

    public function __construct(ClasslistStudentImportService $service)
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
