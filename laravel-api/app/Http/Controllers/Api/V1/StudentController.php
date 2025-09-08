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
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    protected DataFetcherService $fetcher;
    protected ScheduleService $scheduleService;

    public function __construct(DataFetcherService $fetcher, ScheduleService $scheduleService)
    {
        $this->fetcher = $fetcher;
        $this->scheduleService = $scheduleService;
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
     * Body: { student_id: int }
     * Returns StudentBalanceResource.
     */
    public function balances(StudentBalanceRequest $request): JsonResponse
    {
        $studentId = (int) $request->input('student_id');
        $data = $this->fetcher->getStudentBalances($studentId);

        return response()->json([
            'success' => true,
            'data'    => new StudentBalanceResource($data),
        ]);
    }

    /**
     * POST /api/v1/student/records
     * Body: { student_id: int, include_grades?: boolean, term?: string }
     * Returns academic records (optionally grades).
     */
    public function records(StudentRecordsRequest $request): JsonResponse
    {
        $studentId     = (int) $request->input('student_id');
        $term          = $request->input('term'); // nullable
        $includeGrades = (bool) $request->input('include_grades', false);

        $data = $this->fetcher->getStudentRecords($studentId, $term, $includeGrades);

        // Enrich records with schedule information
        if (isset($data['records']) && is_array($data['records'])) {
            $this->scheduleService->enrichRecordsWithSchedules($data['records'], $term);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/v1/student/records-by-term
     * Body: { student_id: int, term: string, include_grades?: boolean }
     * Returns academic records grouped for the specific term in a { terms: [ { records: [...] } ] } shape.
     */
    public function recordsByTerm(StudentRecordsRequest $request): JsonResponse
    {
        $studentId     = (int) $request->input('student_id');
        $term          = (string) $request->input('term');
        $includeGrades = (bool) $request->input('include_grades', false);

        $data = $this->fetcher->getStudentRecordsByTerm($studentId, $term, $includeGrades);

        // Enrich terms with schedule information
        if (isset($data['terms']) && is_array($data['terms'])) {
            $this->scheduleService->enrichTermsWithSchedules($data['terms']);
        }

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
                DB::raw('(SELECT r.intYearLevel FROM tb_mas_registration r WHERE r.intStudentID = u.intID ORDER BY r.date_enrolled DESC, r.intRegistrationID DESC LIMIT 1) as year_level'),
                'u.student_status as status',
                'u.student_type as type',
                'u.level as student_level',
                'u.intProgramID as program_id',
                'p.strProgramCode as program',
                'p.strProgramDescription as program_description'
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
            

        // Existing filters
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

        // Column filters (server-side per-column search)
        $studentNumber = trim((string) $request->query('student_number', ''));
        if ($studentNumber !== '') {
            $q->where('u.strStudentNumber', 'like', $studentNumber . '%');
        }

        $lastName = trim((string) $request->query('last_name', ''));
        if ($lastName !== '') {
            $q->where('u.strLastname', 'like', '%' . $lastName . '%');
        }

        $firstName = trim((string) $request->query('first_name', ''));
        if ($firstName !== '') {
            $q->where('u.strFirstname', 'like', '%' . $firstName . '%');
        }

        $middleName = trim((string) $request->query('middle_name', ''));
        if ($middleName !== '') {
            $q->where('u.strMiddlename', 'like', '%' . $middleName . '%');
        }

        // Program code (code only, partial contains)
        $programCode = trim((string) $request->query('program_code', ''));
        if ($programCode !== '') {
            $q->where('p.strProgramCode', 'like', '%' . $programCode . '%');
        }

        // Status/type/level contains filters
        $statusText = trim((string) $request->query('status_text', ''));
        if ($statusText !== '') {
            $q->where('u.student_status', 'like', '%' . $statusText . '%');
        }

        $typeText = trim((string) $request->query('type_text', ''));
        if ($typeText !== '') {
            $q->where('u.student_type', 'like', '%' . $typeText . '%');
        }

        $studentLevelText = trim((string) $request->query('student_level_text', ''));
        if ($studentLevelText !== '') {
            $q->where('u.level', 'like', '%' . $studentLevelText . '%');
        }

        // Applicants scope: exclude applicants by default, include when include_applicants=1
        $includeApplicants = (int) $request->query('include_applicants', 0) === 1;
        if (!$includeApplicants) {
            $q->where(function ($w) {
                $w->whereNull('u.student_status')
                  ->orWhere('u.student_status', '<>', 'applicant');
            });
        }

        // Free-text search (student number, last/first/middle name; supports "Last, First" and "First Last")
        $search = trim((string)$request->query('q', ''));
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $s = $search;

                // Student number starts-with and name contains matches
                $w->where('u.strStudentNumber', 'like', $s . '%')
                  ->orWhere('u.strLastname', 'like', '%' . $s . '%')
                  ->orWhere('u.strFirstname', 'like', '%' . $s . '%')
                  ->orWhere('u.strMiddlename', 'like', '%' . $s . '%');

                // "Last, First" format
                if (strpos($s, ',') !== false) {
                    $parts = array_map('trim', explode(',', $s, 2));
                    $last = $parts[0] ?? '';
                    $first = $parts[1] ?? '';
                    if ($last !== '' && $first !== '') {
                        $w->orWhere(function ($w2) use ($last, $first) {
                            $w2->where('u.strLastname', 'like', '%' . $last . '%')
                               ->where('u.strFirstname', 'like', '%' . $first . '%');
                        });
                    }
                } elseif (strpos($s, ' ') !== false) { // "First Last" or "Last First"
                    $tokens = preg_split('/\s+/', $s);
                    if (count($tokens) >= 2) {
                        $first = $tokens[0];
                        $last = $tokens[count($tokens) - 1];
                        // First Last
                        $w->orWhere(function ($w2) use ($first, $last) {
                            $w2->where('u.strFirstname', 'like', '%' . $first . '%')
                               ->where('u.strLastname', 'like', '%' . $last . '%');
                        });
                        // Last First
                        $w->orWhere(function ($w3) use ($first, $last) {
                            $w3->where('u.strLastname', 'like', '%' . $first . '%')
                               ->where('u.strFirstname', 'like', '%' . $last . '%');
                        });
                    }
                }
            });
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
                    $sub->whereNotNull('r.date_enlisted');
                } else {
                    $sub->whereNotNull('r.date_enrolled');
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
                DB::raw('(SELECT r.intYearLevel FROM tb_mas_registration r WHERE r.intStudentID = u.intID ORDER BY r.date_enrolled DESC, r.intRegistrationID DESC LIMIT 1) as year_level'),
                'u.student_status as status',
                'u.student_type as type',
                'u.level as student_level',
                'u.intProgramID as program_id',
                'p.strProgramCode as program',
                'p.strProgramDescription as program_description'
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
     * Body: { student_id: int }
     * Returns transaction ledger using TransactionResource.
     */
    public function ledger(StudentBalanceRequest $request): JsonResponse
    {
        $studentId = (int) $request->input('student_id');
        $data = $this->fetcher->getStudentLedger($studentId);

        $transactions = $data['transactions'] ?? [];

        return response()->json([
            'success' => true,
            'data'    => [
                'student_id'   => $studentId,
                'transactions' => TransactionResource::collection($transactions),
            ],
        ]);
    }

    /**
     * GET /api/v1/student/applicant-journey/{applicantDataId}
     * Read-only journey logs for the given applicant_data_id (student-facing).
     * Mirrors ApplicantJourneyController@index but without role middleware.
     * Returns: { success: true, data: [ { id, applicant_data_id, remarks, log_date }, ... ] }
     */
    public function applicantJourney(int $applicantDataId): JsonResponse
    {
        // Basic validation
        if ($applicantDataId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid applicant data id.'
            ], 422);
        }

        try {
            // Ensure table exists; then fetch ordered by log_date ASC
            $rows = DB::table('tb_mas_applicant_journey')
                ->where('applicant_data_id', $applicantDataId)
                ->orderBy('log_date', 'asc')
                ->select('id', 'applicant_data_id', 'remarks', 'log_date')
                ->get();
        } catch (\Throwable $e) {
            // If table missing or other DB error, return empty list for student UI
            $rows = collect([]);
        }

        return response()->json([
            'success' => true,
            'data' => $rows
        ]);
    }

    /**
     * POST /api/v1/student/applicant
     * Resolve current student's applicant details (read-only) using either:
     *  - token: string (preferred; same as /student/viewer)
     *  - student_id: int (fallback)
     *
     * Returns similar payload to ApplicantController@show:
     * {
     *   success: true,
     *   data: {
     *     user, status, applicant_data, created_at, updated_at, hash, applicant_data_id,
     *     applicant_type, applicant_type_name,
     *     paid_application_fee, paid_reservation_fee,
     *     waive_application_fee, waive_reason, waived_at,
     *     syid, interviewed, interview_summary
     *   }
     * }
     */
    public function applicant(Request $request): JsonResponse
    {
        // Accept multiple shapes from various frontends
        $token = $request->input('token', $request->input('username'));
        $studentIdInput = $request->input('student_id', $request->input('id', $request->input('user_id', $request->input('intID'))));
        $studentNumber = $request->input('student_number');

        $token = is_string($token) ? trim($token) : $token;
        $studentNumber = is_string($studentNumber) ? trim($studentNumber) : $studentNumber;

        $id = null;

        // 1) Direct numeric id when provided in any of: student_id, id, user_id, intID
        if ($studentIdInput !== null && $studentIdInput !== '' && is_numeric($studentIdInput)) {
            $id = (int) $studentIdInput;
        }

        // 2) Resolve via token/username (parity with /student/viewer)
        if ($id === null && $token !== null && $token !== '') {
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
                }
            } catch (\Throwable $e) {
                // ignore token resolution failure
            }
        }

        // 3) Resolve via student number when available
        if ($id === null && $studentNumber !== null && $studentNumber !== '') {
            try {
                $idBySn = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->value('intID');
                if ($idBySn !== null) {
                    $id = (int) $idBySn;
                }
            } catch (\Throwable $e) {
                // ignore student number resolution failure
            }
        }

        // 4) Resolve via explicit email, or token if it looks like an email
        if ($id === null) {
            $email = $request->input('email', $request->input('strEmail'));
            if (is_string($email)) { $email = trim($email); }
            if ($email !== null && $email !== '') {
                try {
                    $idByEmail = DB::table('tb_mas_users')->where('strEmail', $email)->value('intID');
                    if ($idByEmail !== null) {
                        $id = (int) $idByEmail;
                    }
                } catch (\Throwable $e) {
                    // ignore email resolution failure
                }
            } elseif (is_string($token) && strpos($token, '@') !== false) {
                try {
                    $idByTokenEmail = DB::table('tb_mas_users')->where('strEmail', $token)->value('intID');
                    if ($idByTokenEmail !== null) {
                        $id = (int) $idByTokenEmail;
                    }
                } catch (\Throwable $e) {
                    // ignore token email resolution failure
                }
            }
        }

        // 5) Resolve via username (when token is a non-email username)
        if ($id === null && is_string($token) && $token !== '' && strpos($token, '@') === false) {
            try {
                $idByUsername = DB::table('tb_mas_users')->where('strUsername', $token)->value('intID');
                if ($idByUsername !== null) {
                    $id = (int) $idByUsername;
                }
            } catch (\Throwable $e) {
                // ignore username resolution failure
            }
        }

        if ($id === null) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to resolve student id.'
            ], 422);
        }

        // Mirror ApplicantController@show using resolved $id
        $user = DB::table('tb_mas_users')->where('intID', $id)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Applicant not found'
            ], 404);
        }

        // Enrich campus name for core user (prefer campus_name, fallback to legacy campus or campus_id)
        try {
            $campusName = null;
            if (property_exists($user, 'campus_id') && !is_null($user->campus_id)) {
                $campusName = DB::table('tb_mas_campuses')->where('id', (int)$user->campus_id)->value('campus_name');
            }
            if (!isset($user->campus)) {
                $user->campus = $campusName ?? (property_exists($user, 'campus') ? $user->campus : (property_exists($user, 'campus_id') ? $user->campus_id : null));
            } elseif ($user->campus === null || $user->campus === '') {
                $user->campus = $campusName ?? $user->campus;
            }
        } catch (\Throwable $e) {
            // ignore enrichment failure
        }

        // Latest applicant_data row
        $appData = DB::table('tb_mas_applicant_data')
            ->where('user_id', $id)
            ->orderByDesc('id')
            ->first();

        if (!$appData) {
            return response()->json([
                'success' => false,
                'message' => 'Applicant data not found'
            ], 404);
        }

        $decoded = null;
        if (isset($appData->data)) {
            try {
                $decoded = is_string($appData->data) ? json_decode($appData->data, true) : $appData->data;
            } catch (\Throwable $e) {
                $decoded = null;
            }
        }

        // Surface applicant_type and payment flags from latest applicant_data row
        $applicantTypeId = isset($appData->applicant_type) ? (int) $appData->applicant_type : null;
        $applicantTypeName = null;
        if ($applicantTypeId) {
            try {
                $applicantTypeName = DB::table('tb_mas_applicant_types')->where('intID', $applicantTypeId)->value('name');
            } catch (\Throwable $e) {
                $applicantTypeName = null;
            }
        }
        $paidApplicationFee = isset($appData->paid_application_fee) ? (bool) $appData->paid_application_fee : null;
        $paidReservationFee = isset($appData->paid_reservation_fee) ? (bool) $appData->paid_reservation_fee : null;

        // Waiver fields
        $waiveApplicationFee = isset($appData->waive_application_fee) ? (bool) $appData->waive_application_fee : null;
        $waiveReason = isset($appData->waive_reason) ? (string) $appData->waive_reason : null;
        $waivedAt = isset($appData->waived_at) ? $appData->waived_at : null;

        // Interview summary (optional; safe if table not present)
        $interviewSummary = null;
        try {
            $intv = DB::table('tb_mas_applicant_interviews')
                ->where('applicant_data_id', $appData->id)
                ->first();
            if ($intv) {
                $interviewSummary = [
                    'scheduled_at' => isset($intv->scheduled_at) ? (string) $intv->scheduled_at : null,
                    'assessment'   => isset($intv->assessment) ? (string) $intv->assessment : null,
                    'completed_at' => isset($intv->completed_at) ? (string) $intv->completed_at : null,
                ];
            }
        } catch (\Throwable $e) {
            $interviewSummary = null;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'status' => $appData->status ?? null,
                'applicant_data' => $decoded,
                'created_at' => $appData->created_at ?? null,
                'updated_at' => $appData->updated_at ?? null,
                'hash' => $appData->hash ?? null,
                'applicant_data_id' => isset($appData->id) ? (int) $appData->id : null,
                // Surfaced fields
                'applicant_type' => $applicantTypeId,
                'applicant_type_name' => $applicantTypeName,
                'paid_application_fee' => $paidApplicationFee,
                'paid_reservation_fee' => $paidReservationFee,
                // Waiver surfaced fields
                'waive_application_fee' => $waiveApplicationFee,
                'waive_reason' => $waiveReason,
                'waived_at' => $waivedAt,
                // Term id of latest applicant_data (nullable)
                'syid' => isset($appData->syid) ? (int) $appData->syid : null,
                // Interview flags
                'interviewed' => isset($appData->interviewed) ? (bool) $appData->interviewed : false,
                'interview_summary' => $interviewSummary,
            ],
        ]);
    }
}
