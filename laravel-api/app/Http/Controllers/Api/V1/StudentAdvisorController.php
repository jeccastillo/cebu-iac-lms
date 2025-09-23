<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StudentAdvisorAssignBulkRequest;
use App\Http\Requests\Api\V1\StudentAdvisorShowRequest;
use App\Http\Requests\Api\V1\StudentAdvisorSwitchRequest;
use App\Services\StudentAdvisorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentAdvisorController extends Controller
{
    /**
     * GET /api/v1/student-advisors
     * Query by student_id or student_number
     */
    public function index(StudentAdvisorShowRequest $request, StudentAdvisorService $service): JsonResponse
    {
        $studentId = $request->query('student_id') !== null ? (int) $request->query('student_id') : null;
        $studNo = $request->query('student_number');

        $data = $service->showByStudent($studentId, $studNo);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/v1/advisors/{advisorId}/assign-bulk
     * Body: { student_ids?:int[], student_numbers?:string[], replace_existing?:bool }
     */
    public function assignBulk(int $advisorId, StudentAdvisorAssignBulkRequest $request, StudentAdvisorService $service): JsonResponse
    {
        $actorId = $this->actorFacultyId($request);

        $ids = (array) $request->input('student_ids', []);
        $sns = (array) $request->input('student_numbers', []);
        $replace = (bool) $request->input('replace_existing', false);
        $campusId = $request->input('campus_id');
        $campusId = ($campusId !== null && $campusId !== '') ? (int) $campusId : null;

        $result = $service->assignBulk($advisorId, $ids, $sns, $replace, $actorId, $campusId);

        // If service returns a leading error-only result, still 200 with success=true for parity with other endpoints
        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * POST /api/v1/advisors/switch
     * Body: { from_advisor_id:int, to_advisor_id:int }
     */
    public function switch(StudentAdvisorSwitchRequest $request, StudentAdvisorService $service): JsonResponse
    {
        $actorId = $this->actorFacultyId($request);

        $from = (int) $request->input('from_advisor_id');
        $to   = (int) $request->input('to_advisor_id');

        $summary = $service->switchAll($from, $to, $actorId);

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }

    /**
     * DELETE /api/v1/student-advisors/{studentId}
     * Ends current advisor assignment for a student (if any).
     */
    public function destroy(int $studentId, Request $request, StudentAdvisorService $service): JsonResponse
    {
        $actorId = $this->actorFacultyId($request);

        $out = $service->unassign($studentId, $actorId);

        return response()->json([
            'success' => $out['ok'] ?? false,
            'data'    => $out,
        ], ($out['ok'] ?? false) ? 200 : 422);
    }

    /**
     * Resolve acting faculty id:
     * - Prefer RequireRole-attached faculty model on request attributes ('faculty')
     * - Fallback to X-Faculty-ID header or 'faculty_id' input
     */
    private function actorFacultyId(Request $request): int
    {
        $fac = $request->attributes->get('faculty');
        if ($fac && isset($fac->intID)) {
            return (int) $fac->intID;
        }
        $raw = $request->headers->get('X-Faculty-ID', $request->input('faculty_id'));
        if ($raw !== null && $raw !== '') {
            return (int) $raw;
        }
        return 0;
    }

    /**
     * GET /api/v1/student-advisors/list
     * Returns students with their current advisor pointers.
     * Optional query: campus_id (int) to filter by campus.
     * Sorted by last_name ASC then first_name ASC.
     */
    public function list(Request $request): JsonResponse
    {
        $campusId = $request->query('campus_id');
        $campusFilter = ($campusId !== null && $campusId !== '' && is_numeric($campusId)) ? (int) $campusId : null;

        $q = DB::table('tb_mas_users as u')
            ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'u.intAdvisorID');

        $hasCampuses = false;
        try {
            $hasCampuses = Schema::hasTable('tb_mas_campuses');
        } catch (\Throwable $e) {
            $hasCampuses = false;
        }

        if ($hasCampuses) {
            $q->leftJoin('tb_mas_campuses as c', 'c.id', '=', 'u.campus_id');
        }

        if ($campusFilter !== null) {
            $q->where('u.campus_id', $campusFilter);
        }

        // Optional filter: has_advisor = 1|0|true|false|yes|no
        $hasRaw = $request->query('has_advisor');
        if ($hasRaw !== null && $hasRaw !== '') {
            $val = is_string($hasRaw) ? strtolower(trim($hasRaw)) : $hasRaw;
            $truthy = ['1','true','yes',1,true];
            $falsy  = ['0','false','no',0,false];
            if (in_array($val, $truthy, true)) {
                $q->whereNotNull('u.intAdvisorID')->where('u.intAdvisorID', '>', 0);
            } elseif (in_array($val, $falsy, true)) {
                $q->where(function ($w) {
                    $w->whereNull('u.intAdvisorID')->orWhere('u.intAdvisorID', 0);
                });
            }
        }

        // Sorting
        $q->orderBy('u.strLastname')
          ->orderBy('u.strFirstname');

        // Pagination controls
        $page = max((int) $request->query('page', 1), 1);
        $perPage = min(max((int) $request->query('per_page', 50), 1), 500);

        // Select minimal listing fields
        $selects = [
            'u.intID as student_id',
            'u.strStudentNumber as student_number',
            'u.strFirstname as first_name',
            'u.strMiddlename as middle_name',
            'u.strLastname as last_name',
            'u.campus_id',
            'u.intAdvisorID as advisor_id',
            DB::raw("TRIM(CONCAT(COALESCE(f.strFirstname,''),' ',COALESCE(f.strLastname,''))) as advisor_name"),
        ];
        if ($hasCampuses) {
            $selects[] = DB::raw('c.campus_name as campus_name');
        } else {
            $selects[] = DB::raw('NULL as campus_name');
        }

        // Total count (before pagination)
        $total = (clone $q)->count('u.intID');

        // Fetch paginated rows
        $rows = $q->select($selects)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'meta'    => [
                'total'    => (int) $total,
                'page'     => (int) $page,
                'per_page' => (int) $perPage,
            ],
        ]);
    }
}
