<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CurriculumImportRequest;
use App\Exports\CurriculumTemplateExport;
use App\Services\CurriculumImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CurriculumImportController extends Controller
{
    protected CurriculumImportService $service;

    public function __construct(CurriculumImportService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/v1/curriculum/import/template
     * Role: registrar,admin
     * Returns an .xlsx template with two sheets:
     *   - curricula: [Name, Program Code, Campus, Active, Enhanced]
     *   - curriculum_subjects: [Curriculum Name, Program Code, Campus, Subject Code, Year Level, Sem]
     */
    public function template(Request $request)
    {
        $export = new CurriculumTemplateExport($this->service);
        $spreadsheet = $export->build();
        $writer = new Xlsx($spreadsheet);

        $filename = 'curriculum-import-template.xlsx';

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
     * POST /api/v1/curriculum/import
     * Role: registrar,admin
     * Accepts multipart/form-data with "file": .xlsx/.xls/.csv
     * Optional: "dry_run" (boolean) -> parse/validate without writing
     *
     * Returns:
     * {
     *   success: true,
     *   result: {
     *     totalRows,
     *     insertedCurricula, updatedCurricula, skippedCurricula,
     *     insertedSubjectLinks, updatedSubjectLinks, skippedSubjectLinks,
     *     errors: [ { sheet, line, key?, message } ]
     *   }
     * }
     */
    public function import(CurriculumImportRequest $request): JsonResponse
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
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('curriculum_import_', true) . '.' . $ext;
                $file->move(dirname($tmpPath), basename($tmpPath));
            }

            $iter = $this->service->parse($tmpPath, $ext);
            $dryRun = (bool) $request->input('dry_run', false);

            // Upsert curricula first to obtain an idMap for subject links
            $curRes = $this->service->upsertCurricula($iter['curricula'], $dryRun);
            $idMap = isset($curRes['idMap']) && is_array($curRes['idMap']) ? $curRes['idMap'] : [];

            // Upsert subject links
            $subRes = $this->service->upsertSubjectLinks($iter['subjects'], $idMap, $dryRun);

            $totalRows =
                (int) (($curRes['inserted'] ?? 0) + ($curRes['updated'] ?? 0) + ($curRes['skipped'] ?? 0)) +
                (int) (($subRes['inserted'] ?? 0) + ($subRes['updated'] ?? 0) + ($subRes['skipped'] ?? 0));

            $errors = array_merge(
                (array) ($curRes['errors'] ?? []),
                (array) ($subRes['errors'] ?? [])
            );

            return response()->json([
                'success' => true,
                'result'  => [
                    'totalRows'            => $totalRows,
                    'insertedCurricula'    => (int) ($curRes['inserted'] ?? 0),
                    'updatedCurricula'     => (int) ($curRes['updated'] ?? 0),
                    'skippedCurricula'     => (int) ($curRes['skipped'] ?? 0),
                    'insertedSubjectLinks' => (int) ($subRes['inserted'] ?? 0),
                    'updatedSubjectLinks'  => (int) ($subRes['updated'] ?? 0),
                    'skippedSubjectLinks'  => (int) ($subRes['skipped'] ?? 0),
                    'errors'               => $errors,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
