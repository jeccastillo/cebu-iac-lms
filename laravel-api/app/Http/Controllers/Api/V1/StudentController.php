<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StudentLookupRequest;
use App\Http\Requests\Api\V1\StudentBalanceRequest;
use App\Http\Requests\Api\V1\StudentRecordsRequest;
use App\Http\Resources\StudentResource;
use App\Http\Resources\StudentBalanceResource;
use App\Http\Resources\TransactionResource;
use App\Services\DataFetcherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    protected DataFetcherService $fetcher;

    public function __construct(DataFetcherService $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * POST /api/v1/student/viewer
     * Body: { token: string }
     * Returns student profile by portal token (parity with PortalController::studentData fields).
     */
    public function viewer(StudentLookupRequest $request): JsonResponse
    {
        $token = (string) $request->input('token');
        $data  = $this->fetcher->getStudentByToken($token);

        if (!$data) {
            // Parity with PortalController when not found
            return response()->json([
                'success' => false
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => (new StudentResource($data))
        ]);
    }

    /**
     * POST /api/v1/student/balances
     * Body: { student_number: string }
     * Placeholder baseline; will be implemented to return StudentBalanceResource.
     */
    public function balances(StudentBalanceRequest $request): JsonResponse
    {
        $studentNumber = (string) $request->input('student_number');
        $data = $this->fetcher->getStudentBalances($studentNumber);

        return response()->json([
            'success' => true,
            'data'    => new StudentBalanceResource($data),
        ]);
    }

    /**
     * POST /api/v1/student/records
     * Body: { student_number: string, include_grades?: boolean, term?: string }
     * Placeholder baseline; will return academic records (optionally grades).
     */
    public function records(StudentRecordsRequest $request): JsonResponse
    {
        $studentNumber = (string) $request->input('student_number');
        $term          = $request->input('term'); // nullable
        $includeGrades = (bool) $request->input('include_grades', false);

        $data = $this->fetcher->getStudentRecords($studentNumber, $term, $includeGrades);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/v1/student/records-by-term
     * Body: { student_number: string, term: string, include_grades?: boolean }
     * Returns academic records grouped for the specific term in a { terms: [ { records: [...] } ] } shape.
     */
    public function recordsByTerm(StudentRecordsRequest $request): JsonResponse
    {
        $studentNumber = (string) $request->input('student_number');
        $term          = (string) $request->input('term');
        $includeGrades = (bool) $request->input('include_grades', false);

        $data = $this->fetcher->getStudentRecordsByTerm($studentNumber, $term, $includeGrades);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * GET /api/v1/students/{id}
     * Returns minimal student info including student_number.
     */
    public function show(int $id): JsonResponse
    {
        $row = DB::table('tb_mas_users as u')
            ->leftJoin('tb_mas_programs as p', 'u.intProgramID', '=', 'p.intProgramID')
            ->where('u.intID', $id)
            ->select(
                'u.intID as id',
                'u.strStudentNumber as student_number',
                'u.strFirstname as first_name',
                'u.strMiddlename as middle_name',
                'u.strLastname as last_name',
                DB::raw('(SELECT r.intYearLevel FROM tb_mas_registration r WHERE r.intStudentID = u.intID ORDER BY r.dteRegistered DESC, r.intRegistrationID DESC LIMIT 1) as year_level'),
                'u.student_status as status',
                'u.student_type as type',
                'u.level as student_level',
                'p.strProgramCode as program'
            )
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $row
        ]);
    }

    /**
     * GET /api/v1/students
     * Query params (all optional):
     *  - page: number (default 1)
     *  - per_page: number (default 20, max 100)
     *  - program: intProgramID
     *  - year_level: intYearLevel
     *  - gender: 1=male, 2=female
     *  - graduated: 1=yes, 2=no
     *  - inactive: active|inactive|loa|awol
     *  - registered: 1=enlisted, 2=enrolled (requires sem)
     *  - sem: tb_mas_sy.intID (used with registered filter)
     *
     * Returns a paginated list of students with minimal fields for listing.
     */
    public function index(Request $request): JsonResponse
    {
        $page = max((int)$request->query('page', 1), 1);
        $perPage = min(max((int)$request->query('per_page', 20), 1), 100);

        $q = DB::table('tb_mas_users as u')
            ->leftJoin('tb_mas_programs as p', 'u.intProgramID', '=', 'p.intProgramID');
            

        // Filters
        if ($request->has('program') && $request->query('program') !== null && $request->query('program') !== '') {
            $q->where('u.intProgramID', (int)$request->query('program'));
        }
        // Campus filter
        if ($request->has('campus_id') && $request->query('campus_id') !== null && $request->query('campus_id') !== '') {
            $q->where('u.campus_id', (int)$request->query('campus_id'));
        }
        if ($request->has('year_level') && $request->query('year_level') !== null && $request->query('year_level') !== '') {
            $yl = (int)$request->query('year_level');
            $q->whereExists(function ($sub) use ($yl) {
                $sub->from('tb_mas_registration as r')
                    ->whereColumn('r.intStudentID', 'u.intID')
                    ->where('r.intYearLevel', $yl);
            });
        }
        if ($request->has('gender') && in_array((int)$request->query('gender'), [1, 2], true)) {
            $gender = (int)$request->query('gender') === 1 ? 'male' : 'female';
            $q->where('u.strGender', $gender);
        }
        if ($request->has('graduated') && in_array((int)$request->query('graduated'), [1, 2], true)) {
            if ((int)$request->query('graduated') === 1) {
                $q->whereNotNull('u.date_of_graduation');
            } else {
                $q->whereNull('u.date_of_graduation');
            }
        }
        if ($request->has('inactive')) {
            $inactive = (string)$request->query('inactive');
            if (in_array($inactive, ['active', 'inactive', 'loa', 'awol'], true)) {
                $q->where('u.student_status', $inactive);
            }
        }

        // Registered filter (requires sem)
        $registered = (int)$request->query('registered', 0);
        $sem = $request->query('sem');
        if (in_array($registered, [1, 2], true) && $sem !== null && $sem !== '') {
            $q->whereExists(function ($sub) use ($registered, $sem) {
                $sub->from('tb_mas_registration as r')
                    ->whereColumn('r.intStudentID', 'u.intID')
                    ->where('r.intAYID', (int)$sem);
                if ($registered === 1) {
                    $sub->whereNotNull('r.dteEnlisted');
                } else {
                    $sub->whereNotNull('r.dteRegistered');
                }
            });
        }

        $total = (clone $q)->count();

        $rows = $q->orderBy('u.strLastname')
            ->orderBy('u.strFirstname')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->select(
                'u.intID as id',
                'u.strStudentNumber as student_number',
                'u.strFirstname as first_name',
                'u.strMiddlename as middle_name',
                'u.strLastname as last_name',
                DB::raw('(SELECT r.intYearLevel FROM tb_mas_registration r WHERE r.intStudentID = u.intID ORDER BY r.dteRegistered DESC, r.intRegistrationID DESC LIMIT 1) as year_level'),
                'u.student_status as status',
                'u.student_type as type',
                'u.level as student_level',
                'p.strProgramCode as program'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage
            ],
        ]);
    }

    /**
     * POST /api/v1/student/ledger
     * Body: { student_number: string }
     * Placeholder baseline; will return transaction ledger using TransactionResource.
     */
    public function ledger(StudentBalanceRequest $request): JsonResponse
    {
        $studentNumber = (string) $request->input('student_number');
        $data = $this->fetcher->getStudentLedger($studentNumber);

        $transactions = $data['transactions'] ?? [];

        return response()->json([
            'success' => true,
            'data'    => [
                'student_number' => $data['student_number'] ?? $studentNumber,
                'transactions'   => TransactionResource::collection($transactions),
            ],
        ]);
    }
}
