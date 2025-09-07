<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SchoolYearImportRequest;
use App\Exports\SchoolYearTemplateExport;
use App\Services\SchoolYearImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SchoolYearImportController extends Controller
{
    protected SchoolYearImportService $service;

    public function __construct(SchoolYearImportService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/school-years/import/template
     * Role: registrar,admin
     * Returns an .xlsx template with headers for school years (terms) import.
     */
    public function template(Request $request)
    {
        $export = new SchoolYearTemplateExport($this->service);
        $spreadsheet = $export->build();
        $writer = new Xlsx($spreadsheet);

        $filename = 'school-years-import-template.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * POST /api/v1/school-years/import
     * Role: registrar,admin
     * Accepts multipart/form-data with "file": .xlsx/.xls/.csv
     * Optional: "dry_run" (boolean) -> parse/validate without writing
     *
     * Returns:
     * {
     *   success: true,
     *   result: { totalRows, inserted, updated, skipped, errors: [ { line, code, message } ] }
     * }
     */
    public function import(SchoolYearImportRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid upload.',
                ], 422);
            }

            $ext = strtolower($file->getClientOriginalExtension() ?: '');
            if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
                $mime = $file->getMimeType() ?: '';
                if (str_contains($mime, 'spreadsheetml')) $ext = 'xlsx';
                elseif (str_contains($mime, 'excel')) $ext = 'xls';
                elseif (str_contains($mime, 'csv')) $ext = 'csv';
            }
            if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported file type. Please upload .xlsx, .xls, or .csv.',
                ], 422);
            }

            // Ensure readable temp path for PhpSpreadsheet
            $tmpPath = $file->getRealPath();
            if ($tmpPath === false || $tmpPath === null) {
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('school_years_import_', true) . '.' . $ext;
                $file->move(dirname($tmpPath), basename($tmpPath));
            }

            $iter = $this->service->parse($tmpPath, $ext);
            $dryRun = (bool) $request->input('dry_run', false);

            $res = $this->service->upsertRows($iter, $dryRun);

            $totalRows = (int) (($res['inserted'] ?? 0) + ($res['updated'] ?? 0) + ($res['skipped'] ?? 0));

            return response()->json([
                'success' => true,
                'result'  => array_merge(['totalRows' => $totalRows], $res),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
