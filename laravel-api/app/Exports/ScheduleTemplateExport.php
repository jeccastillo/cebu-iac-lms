<?php

namespace App\Exports;

use App\Services\ScheduleImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * ScheduleTemplateExport
 *
 * Thin wrapper around ScheduleImportService to build the schedules import template Spreadsheet.
 */
class ScheduleTemplateExport
{
    protected ScheduleImportService $service;

    public function __construct(ScheduleImportService $service)
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
