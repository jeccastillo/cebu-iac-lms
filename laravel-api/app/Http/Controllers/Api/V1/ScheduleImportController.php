<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ScheduleImportRequest;
use App\Exports\ScheduleTemplateExport;
use App\Services\ScheduleImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScheduleImportController extends Controller
{
    protected ScheduleImportService $service;

    public function __construct(ScheduleImportService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/schedules/import/template
     * Role: registrar,admin
     * Returns an .xlsx template for schedules import.
     */
    public function template(Request $request)
    {
        $export = new ScheduleTemplateExport($this->service);
        $spreadsheet = $export->build();
        $writer = new Xlsx($spreadsheet);

        $filename = 'schedules-import-template.xlsx';

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
     * POST /api/v1/schedules/import
     * Role: registrar,admin
     * Accepts multipart/form-data with "file": .xlsx/.xls/.csv
     * Optional: "dry_run" (boolean) -> parse/validate without writing
     * Optional header: X-Faculty-ID -> context encoder id for auditing
     *
     * Returns:
     * {
     *   success: true,
     *   result: { totalRows, inserted, updated, skipped, errors: [ { line, code, message, conflicts? } ] }
     * }
     */
    public function import(ScheduleImportRequest $request): JsonResponse
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
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('schedules_import_', true) . '.' . $ext;
                $file->move(dirname($tmpPath), basename($tmpPath));
            }

            $iter = $this->service->parse($tmpPath, $ext);
            $dryRun = (bool) $request->input('dry_run', false);

            // Optional encoder id from header
            $encoderIdHeader = $request->header('X-Faculty-ID');
            $context = [];
            if ($encoderIdHeader !== null && $encoderIdHeader !== '') {
                $eid = (int) $encoderIdHeader;
                if ($eid > 0) {
                    $context['encoder_id'] = $eid;
                }
            }

            $result = $this->service->upsertRows($iter, $dryRun, true, $context);            
            $success = ($result['errors'])?false:true;
                return response()->json([
                    'success' => $success,
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
