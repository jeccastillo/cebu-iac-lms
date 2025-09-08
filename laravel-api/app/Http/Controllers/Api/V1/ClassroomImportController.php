<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClassroomImportRequest;
use App\Exports\ClassroomTemplateExport;
use App\Services\ClassroomImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ClassroomImportController extends Controller
{
    protected ClassroomImportService $service;

    public function __construct(ClassroomImportService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/classrooms/import/template
     * Role: building_admin,admin
     * Returns an .xlsx template for classrooms import.
     */
    public function template(Request $request)
    {
        $export = new ClassroomTemplateExport($this->service);
        $spreadsheet = $export->build();
        $writer = new Xlsx($spreadsheet);

        $filename = 'classrooms-import-template.xlsx';

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
     * POST /api/v1/classrooms/import
     * Role: building_admin,admin
     * Accepts multipart/form-data with "file": .xlsx/.xls/.csv
     * Optional: "dry_run" (boolean) -> parse/validate without writing
     *
     * Returns:
     * {
     *   success: true,
     *   result: { totalRows, inserted, updated, skipped, errors: [ { line, room_code, message } ] }
     * }
     */
    public function import(ClassroomImportRequest $request): JsonResponse
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
            // Fallback to mime if needed
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

            // Move to a temporary readable path
            $tmpPath = $file->getRealPath();
            if ($tmpPath === false || $tmpPath === null) {
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('classrooms_import_', true) . '.' . $ext;
                $file->move(dirname($tmpPath), basename($tmpPath));
            }

            $iter = $this->service->parse($tmpPath, $ext);
            $dryRun = (bool) $request->input('dry_run', false);

            $result = $this->service->upsertRows($iter, $dryRun);

            return response()->json([
                'success' => true,
                'result'  => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
