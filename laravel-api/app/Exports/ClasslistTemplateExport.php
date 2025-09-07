<?php

namespace App\Exports;

use App\Services\ClasslistImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ClasslistTemplateExport
 *
 * Thin wrapper around ClasslistImportService to build the classlists import template Spreadsheet.
 */
class ClasslistTemplateExport
{
    protected ClasslistImportService $service;

    public function __construct(ClasslistImportService $service)
    {
        $this->service = $service;
    }

    /**
     * Build and return the template Spreadsheet instance.
     */
    public function build(): Spreadsheet
    {
        return $this->service->buildTemplateSpreadsheet();
    }
}
