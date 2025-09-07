<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StudentImportRequest;
use App\Exports\StudentTemplateExport;
use App\Services\StudentImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportController extends Controller
{
    protected StudentImportService $service;

    public function __construct(StudentImportService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/students/import/template
     * Role: registrar,admin
     * Returns an .xlsx template with headers from tb_mas_users using substitutions:
     *   - intProgramID -> Program Code
     *   - intCurriculumID -> Curriculum Code
     *   - campus_id -> Campus
     */
    public function template(Request $request)
    {
        $export = new StudentTemplateExport($this->service);
        $spreadsheet = $export->build();
        $writer = new Xlsx($spreadsheet);

        $filename = 'students-import-template.xlsx';

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
     * POST /api/v1/students/import
     * Role: registrar,admin
     * Accepts multipart/form-data with "file": .xlsx/.xls/.csv
     * Optional: "dry_run" (boolean) -> parse/validate without writing
     *
     * Returns:
     * {
     *   success: true,
     *   result: { totalRows, inserted, updated, skipped, errors: [ { line, student_number, message } ] }
     * }
     */
    public function import(StudentImportRequest $request): JsonResponse
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
            // Some clients may misreport; fall back to mime if needed
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

            // Move to a safe temporary path to ensure readable by PhpSpreadsheet
            $tmpPath = $file->getRealPath();
            if ($tmpPath === false || $tmpPath === null) {
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('students_import_', true) . '.' . $ext;
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
