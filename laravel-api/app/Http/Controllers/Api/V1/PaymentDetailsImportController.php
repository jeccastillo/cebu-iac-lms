<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PaymentDetailsImportService;
use App\Exports\PaymentDetailsTemplateExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PaymentDetailsImportController extends Controller
{
    protected PaymentDetailsImportService $service;

    public function __construct(PaymentDetailsImportService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/finance/payment-details/import/template
     * Roles: finance_admin,admin
     * Returns an .xlsx template with headers for payment_details import.
     */
    public function template(Request $request)
    {
        $export = new PaymentDetailsTemplateExport();
        $spreadsheet = $export->build();
        $writer = new Xlsx($spreadsheet);

        $filename = 'payment-details-import-template.xlsx';

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
     * POST /api/v1/finance/payment-details/import
     * Roles: finance_admin,admin
     * Accepts multipart/form-data with "file": .xlsx/.xls/.csv
     *
     * Returns:
     * {
     *   success: true,
     *   result: { totalRows, inserted, updated, skipped, errors: [ { line, code, message } ] }
     * }
     */
    public function import(Request $request): JsonResponse
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
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('payment_details_import_', true) . '.' . $ext;
                $file->move(dirname($tmpPath), basename($tmpPath));
            }

            $res = $this->service->import($tmpPath, $ext);

            return response()->json([
                'success' => true,
                'result'  => $res,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
