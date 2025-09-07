<?php

namespace App\Exports;

use App\Services\CurriculumImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * CurriculumTemplateExport
 *
 * Thin wrapper around CurriculumImportService to build the curricula import template Spreadsheet.
 */
class CurriculumTemplateExport
{
    protected CurriculumImportService $service;

    public function __construct(CurriculumImportService $service)
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
