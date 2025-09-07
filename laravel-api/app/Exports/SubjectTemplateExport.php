<?php

namespace App\Exports;

use App\Services\SubjectImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * SubjectTemplateExport
 *
 * Thin wrapper around SubjectImportService to build the subjects import template Spreadsheet.
 */
class SubjectTemplateExport
{
    protected SubjectImportService $service;

    public function __construct(SubjectImportService $service)
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
