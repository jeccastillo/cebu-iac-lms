<?php

namespace App\Exports;

use App\Services\ProgramImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ProgramTemplateExport
 *
 * Thin wrapper around ProgramImportService to build the programs import template Spreadsheet.
 */
class ProgramTemplateExport
{
    protected ProgramImportService $service;

    public function __construct(ProgramImportService $service)
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
