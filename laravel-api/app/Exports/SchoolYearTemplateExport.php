<?php

namespace App\Exports;

use App\Services\SchoolYearImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * SchoolYearTemplateExport
 *
 * Thin wrapper around SchoolYearImportService to build the school years (terms) import template Spreadsheet.
 */
class SchoolYearTemplateExport
{
    protected SchoolYearImportService $service;

    public function __construct(SchoolYearImportService $service)
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
