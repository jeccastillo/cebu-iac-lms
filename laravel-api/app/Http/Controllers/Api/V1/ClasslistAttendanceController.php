<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Attendance\AttendanceDateStoreRequest;
use App\Http\Requests\Api\V1\Attendance\AttendanceSaveRequest;
use App\Services\ClasslistAttendanceService;
use App\Http\Requests\Api\V1\Attendance\AttendanceImportRequest;
use App\Exports\ClasslistAttendanceTemplateExport;
use App\Exports\ClasslistAttendanceAllTemplateExport;
use App\Services\ClasslistAttendanceImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Exports\ClasslistAttendanceMatrixTemplateExport;
use App\Http\Requests\Api\V1\Attendance\AttendanceMatrixImportRequest;

class ClasslistAttendanceController extends Controller
{
    protected ClasslistAttendanceService $svc;

    public function __construct(ClasslistAttendanceService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * GET /api/v1/classlists/{id}/attendance/dates
     * List attendance dates with summary counts.
     */
    public function dates(Request $request, int $id): JsonResponse
    {
        if (!$this->authorized($request, $id, 'view')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        try {
            $rows = $this->svc->listDates($id);
            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/v1/classlists/{id}/attendance/dates
     * Body: { date: 'YYYY-MM-DD' }
     * Creates an attendance date and seeds rows for all students (is_present=null).
     */
    public function createDate(AttendanceDateStoreRequest $request, int $id): JsonResponse
    {
        if (!$this->authorized($request, $id, 'edit')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $payload = $request->validated();
        $date = (string) ($payload['date'] ?? '');
        $period = (string) ($payload['period'] ?? '');

        // Actor: from X-Faculty-ID if present
        $actorId = (int) ($request->header('X-Faculty-ID') ?? 0);
        if ($actorId <= 0 && $request->user()) {
            $actorId = (int) ($request->user()->intID ?? 0);
        }

        try {
            $out = $this->svc->createDate($id, $date, $period, $actorId > 0 ? $actorId : null);
            return response()->json(['success' => true, 'data' => $out]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /api/v1/classlists/{id}/attendance/dates/{dateId}
     * Get details (students + marks) for a date.
     */
    public function dateDetails(Request $request, int $id, int $dateId): JsonResponse
    {
        if (!$this->authorized($request, $id, 'view')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        try {
            $out = $this->svc->getDateDetails($id, $dateId);
            return response()->json(['success' => true, 'data' => $out]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * PUT /api/v1/classlists/{id}/attendance/dates/{dateId}
     * Body: { items: [ { intCSID, is_present, remarks? } ] }
     * Bulk save marks.
     */
    public function save(AttendanceSaveRequest $request, int $id, int $dateId): JsonResponse
    {
        if (!$this->authorized($request, $id, 'edit')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $payload = $request->validated();
        $items = $payload['items'] ?? [];

        // Actor: from X-Faculty-ID if present
        $actorId = (int) ($request->header('X-Faculty-ID') ?? 0);
        if ($actorId <= 0 && $request->user()) {
            $actorId = (int) ($request->user()->intID ?? 0);
        }

        try {
            $out = $this->svc->saveMarks($id, $dateId, $items, $actorId > 0 ? $actorId : null);
            return response()->json(['success' => true, 'data' => $out]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * DELETE /api/v1/classlists/{id}/attendance/dates/{dateId}
     * Optional: remove an attendance date and its rows.
     */
    public function deleteDate(Request $request, int $id, int $dateId): JsonResponse
    {
        if (!$this->authorized($request, $id, 'edit')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        try {
            // Simple guarded delete
            DB::table('tb_mas_classlist_attendance')->where('intAttendanceDateID', $dateId)->delete();
            DB::table('tb_mas_classlist_attendance_date')
                ->where('intID', $dateId)
                ->where('intClassListID', $id)
                ->delete();

            return response()->json(['success' => true, 'data' => ['deleted' => true]]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /api/v1/classlists/{id}/attendance/dates/{dateId}/template
     * Download per-date attendance Excel template.
     */
    public function template(Request $request, int $id, int $dateId)
    {
        if (!$this->authorized($request, $id, 'view')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        try {
            /** @var ClasslistAttendanceTemplateExport $export */
            $export = app(ClasslistAttendanceTemplateExport::class);
            $ss = $export->build($id, $dateId);
            $writer = new Xlsx($ss);

            $filename = 'classlist-' . $id . '-attendance-' . $dateId . '-template.xlsx';
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'              => 'no-cache',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/v1/classlists/{id}/attendance/dates/{dateId}/import
     * Body: multipart/form-data { file: .xlsx }
     */
    public function import(AttendanceImportRequest $request, int $id, int $dateId): JsonResponse
    {
        if (!$this->authorized($request, $id, 'edit')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        // Actor: from X-Faculty-ID if present
        $actorId = (int) ($request->header('X-Faculty-ID') ?? 0);
        if ($actorId <= 0 && $request->user()) {
            $actorId = (int) ($request->user()->intID ?? 0);
        }

        try {
            $file = $request->file('file');
            if (!$file || !$file->isValid()) {
                return response()->json(['success' => false, 'message' => 'Invalid upload.'], 422);
            }

            // Ensure readable temp path for PhpSpreadsheet
            $tmpPath = $file->getRealPath();
            if ($tmpPath === false || $tmpPath === null) {
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('class_attendance_import_', true) . '.xlsx';
                $file->move(dirname($tmpPath), basename($tmpPath));
            }

            /** @var ClasslistAttendanceImportService $svc */
            $svc = app(ClasslistAttendanceImportService::class);
            $iter = $svc->parseXlsx($tmpPath);
            $res = $svc->upsert($iter, $id, $dateId, $actorId > 0 ? $actorId : null);

            return response()->json(['success' => true, 'result' => $res]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /api/v1/classlists/{id}/attendance/template?scope=all
     * Download all-dates attendance Excel template for a classlist.
     */
    public function templateAll(Request $request, int $id)
    {
        if (!$this->authorized($request, $id, 'view')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        try {
            /** @var ClasslistAttendanceAllTemplateExport $export */
            $export = app(ClasslistAttendanceAllTemplateExport::class);
            $ss = $export->build($id);
            $writer = new Xlsx($ss);

            $filename = 'classlist-' . $id . '-attendance-all-template.xlsx';
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'              => 'no-cache',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/v1/classlists/{id}/attendance/import
     * Body: multipart/form-data { file: .xlsx }
     * Applies updates across multiple attendance dates in a single upload.
     */
    public function importAll(AttendanceImportRequest $request, int $id): JsonResponse
    {
        if (!$this->authorized($request, $id, 'edit')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        // Actor: from X-Faculty-ID if present
        $actorId = (int) ($request->header('X-Faculty-ID') ?? 0);
        if ($actorId <= 0 && $request->user()) {
            $actorId = (int) ($request->user()->intID ?? 0);
        }

        try {
            $file = $request->file('file');
            if (!$file || !$file->isValid()) {
                return response()->json(['success' => false, 'message' => 'Invalid upload.'], 422);
            }

            // Ensure readable temp path for PhpSpreadsheet
            $tmpPath = $file->getRealPath();
            if ($tmpPath === false || $tmpPath === null) {
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('class_attendance_import_all_', true) . '.xlsx';
                $file->move(dirname($tmpPath), basename($tmpPath));
            }

            /** @var ClasslistAttendanceImportService $svc */
            $svc = app(ClasslistAttendanceImportService::class);
            $iter = $svc->parseXlsxAll($tmpPath);
            $res = $svc->upsertAll($iter, $id, $actorId > 0 ? $actorId : null);

            return response()->json(['success' => true, 'result' => $res]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /api/v1/classlists/{id}/attendance/matrix/template
     * Query: start=YYYY-MM-DD&amp;end=YYYY-MM-DD&amp;period=midterm|finals
     * Download attendance matrix template for date range (per period).
     */
    public function templateMatrix(Request $request, int $id)
    {
        if (!$this->authorized($request, $id, 'view')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        try {
            $start = (string) $request->query('start', '');
            $end   = (string) $request->query('end', '');
            $period = (string) $request->query('period', 'midterm');

            /** @var ClasslistAttendanceMatrixTemplateExport $export */
            $export = app(ClasslistAttendanceMatrixTemplateExport::class);
            $ss = $export->build($id, $start, $end, $period);
            $writer = new Xlsx($ss);

            $filename = 'classlist-' . $id . '-attendance-matrix-' . $period . '.xlsx';
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'              => 'no-cache',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/v1/classlists/{id}/attendance/matrix/import
     * Body: multipart/form-data { file: .xlsx, period: midterm|finals }
     * Applies updates across multiple dates in a single matrix sheet.
     */
    public function importMatrix(AttendanceMatrixImportRequest $request, int $id): JsonResponse
    {
        if (!$this->authorized($request, $id, 'edit')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        // Actor: from X-Faculty-ID if present
        $actorId = (int) ($request->header('X-Faculty-ID') ?? 0);
        if ($actorId <= 0 && $request->user()) {
            $actorId = (int) ($request->user()->intID ?? 0);
        }

        try {
            $file = $request->file('file');
            if (!$file || !$file->isValid()) {
                return response()->json(['success' => false, 'message' => 'Invalid upload.'], 422);
            }
            $period = (string) $request->input('period', 'midterm');

            // Ensure readable temp path for PhpSpreadsheet
            $tmpPath = $file->getRealPath();
            if ($tmpPath === false || $tmpPath === null) {
                $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('class_attendance_import_matrix_', true) . '.xlsx';
                $file->move(dirname($tmpPath), basename($tmpPath));
            }

            /** @var ClasslistAttendanceImportService $svc */
            $svc = app(ClasslistAttendanceImportService::class);
            $iter = $svc->parseXlsxMatrix($tmpPath);
            $res = $svc->upsertMatrix($iter, $id, $period, $actorId > 0 ? $actorId : null);

            return response()->json(['success' => true, 'result' => $res]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ----------------------
    // Helpers
    // ----------------------

    protected function authorized(Request $request, int $classlistId, string $action): bool
    {
        // Gate
        $gateAbility = $action === 'edit' ? 'attendance.classlist.edit' : 'attendance.classlist.view';
        if (Gate::allows($gateAbility, $classlistId)) {
            return true;
        }

        // Header fallbacks
        $headerRoles = [];
        try {
            $hdr = (string) ($request->header('X-User-Roles') ?? '');
            if ($hdr !== '') {
                $headerRoles = array_filter(array_map('trim', explode(',', strtolower($hdr))));
            }
        } catch (\Throwable $e) {
            $headerRoles = [];
        }
        $headerIsAdmin = in_array('admin', $headerRoles, true);
        if ($headerIsAdmin) {
            return true;
        }

        // Faculty header check: must be assigned to the classlist
        $headerHasFaculty = in_array('faculty', $headerRoles, true);
        $headerFacultyId = (int) ($request->header('X-Faculty-ID') ?? 0);
        if ($headerHasFaculty && $headerFacultyId > 0) {
            $cl = DB::table('tb_mas_classlist')->where('intID', $classlistId)->first();
            if ($cl && (int) ($cl->intFacultyID ?? 0) === $headerFacultyId) {
                return true;
            }
        }

        return false;
    }
}
