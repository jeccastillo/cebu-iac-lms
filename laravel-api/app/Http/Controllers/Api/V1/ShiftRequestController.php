<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ShiftRequest;
use App\Models\SystemAlert;
use App\Services\DataFetcherService;
use App\Services\SystemAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShiftRequestController extends Controller
{
    protected DataFetcherService $fetcher;
    protected SystemAlertService $alertService;

    public function __construct(DataFetcherService $fetcher, SystemAlertService $alertService)
    {
        $this->fetcher = $fetcher;
        $this->alertService = $alertService;
    }

    /**
     * GET /api/v1/student/shift-requests
     * Student-safe: list own shift requests (resolve by token or student_id).
     * Optional query: term|term_id|syid to filter by term.
     */
    public function index(Request $request): JsonResponse
    {
        $resolve = $this->resolveStudentId($request);
        if (!$resolve['ok']) {
            return response()->json([
                'success' => false,
                'message' => $resolve['message'] ?? 'Unable to resolve student id.',
            ], 422);
        }
        $studentId = (int) $resolve['id'];

        $termRaw = $request->query('term', $request->query('term_id', $request->query('syid')));
        $termId = is_numeric($termRaw) ? (int) $termRaw : null;

        try {
            $q = DB::table('tb_mas_shift_requests')->where('student_id', $studentId);
            if ($termId !== null) {
                $q->where('term_id', $termId);
            }
            $rows = $q->orderByDesc('id')->get();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * POST /api/v1/student/shift-requests
     * Body: { token?: string, student_id?: int, term|term_id|syid: int, program_to: int, reason?: string }
     * Student-safe: creates a pending shift request; enforces unique per (student_id, term_id).
     * Auto-creates a System Alert to Registrar role.
     */
    public function store(Request $request): JsonResponse
    {
        // Ensure required tables exist
        if (!Schema::hasTable('tb_mas_users')) {
            return response()->json([
                'success' => false,
                'message' => 'Users table not found.',
            ], 422);
        }
        if (!Schema::hasTable('tb_mas_programs')) {
            return response()->json([
                'success' => false,
                'message' => 'Programs table not found.',
            ], 422);
        }
        if (!Schema::hasTable('tb_mas_shift_requests')) {
            return response()->json([
                'success' => false,
                'message' => 'Shift requests table not found.',
            ], 422);
        }

        // 1) Resolve student id (token preferred)
        $resolve = $this->resolveStudentId($request);
        if (!$resolve['ok']) {
            return response()->json([
                'success' => false,
                'message' => $resolve['message'] ?? 'Unable to resolve student id.',
            ], 422);
        }
        $studentId = (int) $resolve['id'];

        // 2) Resolve term id (body or header)
        $termRaw = $request->input('term', $request->input('term_id', $request->input('syid')));
        if ($termRaw === null || $termRaw === '') {
            $hdr = $request->header('X-Term-ID');
            if ($hdr !== null && $hdr !== '') {
                $termRaw = $hdr;
            }
        }
        if ($termRaw === null || $termRaw === '' || !is_numeric($termRaw)) {
            return response()->json([
                'success' => false,
                'message' => 'Term is required.',
            ], 422);
        }
        $termId = (int) $termRaw;

        // 3) Validate program_to
        $programToRaw = $request->input('program_to');
        if ($programToRaw === null || $programToRaw === '' || !is_numeric($programToRaw)) {
            return response()->json([
                'success' => false,
                'message' => 'program_to is required and must be a valid program id.',
            ], 422);
        }
        $programTo = (int) $programToRaw;

        try {
            $programExists = DB::table('tb_mas_programs')->where('intProgramID', $programTo)->exists();
            if (!$programExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program not found.',
                ], 422);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program validation failed: ' . $e->getMessage(),
            ], 422);
        }

        // 4) Snapshot current program from user and optionally campus id and student_number
        try {
            $user = DB::table('tb_mas_users')
                ->select('intID', 'intProgramID', 'campus_id', 'strStudentNumber')
                ->where('intID', $studentId)
                ->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found.',
                ], 404);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student fetch failed: ' . $e->getMessage(),
            ], 422);
        }

        $programFrom = isset($user->intProgramID) ? (int) $user->intProgramID : null;
        $campusId = isset($user->campus_id) ? (int) $user->campus_id : null;
        $studentNumber = isset($user->strStudentNumber) ? (string) $user->strStudentNumber : null;

        // 5) Enforce unique per (student_id, term_id)
        try {
            $exists = DB::table('tb_mas_shift_requests')
                ->where('student_id', $studentId)
                ->where('term_id', $termId)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'A request already exists for this term.',
                ], 409);
            }
        } catch (\Throwable $e) {
            // Attempt insert w/ unique index as guard if exists
        }

        // 6) Insert row
        $payload = [
            'student_id' => $studentId,
            'student_number' => $studentNumber,
            'term_id' => $termId,
            'program_from' => $programFrom,
            'program_to' => $programTo,
            'reason' => $request->input('reason'),
            'status' => 'pending',
            'requested_at' => now()->toDateTimeString(),
            'processed_at' => null,
            'processed_by_faculty_id' => null,
            'campus_id' => $campusId,
            'meta' => null,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        try {
            $id = DB::table('tb_mas_shift_requests')->insertGetId($payload);
            $row = DB::table('tb_mas_shift_requests')->where('id', $id)->first();
        } catch (\Throwable $e) {
            // Duplicate unique key edge-case catch
            if (stripos($e->getMessage(), 'duplicate') !== false || stripos($e->getMessage(), 'unique') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'A request already exists for this term.',
                ], 409);
            }
            return response()->json([
                'success' => false,
                'message' => 'Create failed: ' . $e->getMessage(),
            ], 422);
        }

        // 7) Create System Alert to Registrar (non-blocking best effort)
        try {
            $msg = sprintf(
                'Program change request from %s: %s -> %s (term %d)',
                $studentNumber ?: ('ID:' . $studentId),
                ($programFrom !== null ? (string) $programFrom : 'N/A'),
                (string) $programTo,
                $termId
            );

            $alert = new SystemAlert();
            $alert->title = 'Program Change Request';
            $alert->message = $msg;
            $alert->link = '#/registrar/shifting';
            $alert->type = 'info';
            $alert->target_all = false;
            $alert->role_codes = ['registrar'];
            $alert->campus_ids = $campusId ? [$campusId] : [];
            $alert->starts_at = now();
            // Optional 14-day window
            $alert->ends_at = now()->copy()->addDays(14);
            $alert->intActive = 1;
            $alert->system_generated = true;
            $alert->created_by = null;
            $alert->save();

            // Broadcast
            $this->alertService->broadcast('create', $alert);
        } catch (\Throwable $e) {
            // Swallow alert failures; main operation succeeded
        }

        return response()->json([
            'success' => true,
            'message' => 'Shift request created.',
            'data' => $row,
        ], 201);
    }

    /**
     * PATCH /api/v1/student/shift-requests/status
     * Body: { student_id: int, term|term_id|syid: int, status: 'approved'|'rejected'|'cancelled' }
     * Roles: registrar, admin
     * Updates tb_mas_shift_requests row for (student_id, term_id) setting status and processed markers.
     */
    public function setStatus(Request $request): JsonResponse
    {
        if (!Schema::hasTable('tb_mas_shift_requests')) {
            return response()->json([
                'success' => false,
                'message' => 'Shift requests table not found.',
            ], 422);
        }

        $studentIdRaw = $request->input('student_id', $request->input('id', $request->input('user_id')));
        if ($studentIdRaw === null || $studentIdRaw === '' || !is_numeric($studentIdRaw)) {
            return response()->json([
                'success' => false,
                'message' => 'student_id is required and must be numeric.',
            ], 422);
        }
        $studentId = (int)$studentIdRaw;

        $termRaw = $request->input('term', $request->input('term_id', $request->input('syid')));
        if ($termRaw === null || $termRaw === '' || !is_numeric($termRaw)) {
            return response()->json([
                'success' => false,
                'message' => 'term is required and must be numeric.',
            ], 422);
        }
        $termId = (int)$termRaw;

        $status = strtolower((string)$request->input('status', ''));
        if (!in_array($status, ['approved','rejected','cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status. Allowed: approved, rejected, cancelled.',
            ], 422);
        }

        $actorRaw = $request->header('X-Faculty-ID', $request->input('faculty_id'));
        $actorId = (is_numeric($actorRaw) && (int)$actorRaw > 0) ? (int)$actorRaw : null;

        try {
            $row = DB::table('tb_mas_shift_requests')
                ->where('student_id', $studentId)
                ->where('term_id', $termId)
                ->orderByDesc('id')
                ->first();

            if (!$row) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift request not found for student and term.',
                ], 404);
            }

            DB::table('tb_mas_shift_requests')
                ->where('id', $row->id)
                ->update([
                    'status' => $status,
                    'processed_at' => now()->toDateTimeString(),
                    'processed_by_faculty_id' => $actorId,
                    'updated_at' => now()->toDateTimeString(),
                ]);

            $updated = DB::table('tb_mas_shift_requests')->where('id', $row->id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Shift request updated.',
                'data' => $updated,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Helper to resolve student id from token or student_id.
     * Mirrors logic used in StudentController@applicant with simplified paths.
     */
    protected function resolveStudentId(Request $request): array
    {
        // Direct numeric student_id
        $studentIdInput = $request->input('student_id', $request->input('id', $request->input('user_id', $request->input('intID'))));
        if ($studentIdInput !== null && $studentIdInput !== '' && is_numeric($studentIdInput)) {
            return ['ok' => true, 'id' => (int) $studentIdInput];
        }

        // Token resolution (preferred)
        $token = $request->input('token', $request->input('username'));
        if (is_string($token)) { $token = trim($token); }

        if ($token !== null && $token !== '') {
            try {
                $row = $this->fetcher->getStudentByToken((string) $token);
                if ($row) {
                    if (is_array($row)) {
                        $id = isset($row['intID']) ? (int) $row['intID'] : (isset($row['id']) ? (int) $row['id'] : null);
                    } else {
                        $id = (property_exists($row, 'intID') && $row->intID !== null)
                            ? (int) $row->intID
                            : ((property_exists($row, 'id') && $row->id !== null) ? (int) $row->id : null);
                    }
                    if ($id !== null) {
                        return ['ok' => true, 'id' => $id];
                    }
                }
            } catch (\Throwable $e) {
                // ignore and continue
            }
        }

        // Email resolution (optional)
        $email = $request->input('email', $request->input('strEmail'));
        if (is_string($email)) { $email = trim($email); }
        if ($email !== null && $email !== '') {
            try {
                $idByEmail = DB::table('tb_mas_users')->where('strEmail', $email)->value('intID');
                if ($idByEmail !== null) {
                    return ['ok' => true, 'id' => (int) $idByEmail];
                }
            } catch (\Throwable $e) {
                // ignore
            }
        } elseif (is_string($token) && strpos($token, '@') !== false) {
            try {
                $idByTokenEmail = DB::table('tb_mas_users')->where('strEmail', $token)->value('intID');
                if ($idByTokenEmail !== null) {
                    return ['ok' => true, 'id' => (int) $idByTokenEmail];
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Username resolution
        if (is_string($token) && $token !== '' && strpos($token, '@') === false) {
            try {
                $idByUsername = DB::table('tb_mas_users')->where('strUsername', $token)->value('intID');
                if ($idByUsername !== null) {
                    return ['ok' => true, 'id' => (int) $idByUsername];
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return ['ok' => false, 'message' => 'Unable to resolve student id. Provide token or student_id.'];
    }
}
