<?php

namespace App\Exports;

use App\Services\ClassroomImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ClassroomTemplateExport
 *
 * Thin wrapper around ClassroomImportService to build the classrooms import template Spreadsheet.
 */
class ClassroomTemplateExport
{
    protected ClassroomImportService $service;

    public function __construct(ClassroomImportService $service)
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
