<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\V1\ApplicantUpdateRequest;
use App\Services\SystemLogService;
use App\Exports\ApplicantImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class ApplicantController extends Controller
{
    /**
     * GET /api/v1/applicants
     *
     * Query params (all optional):
     * - search: string (applies to name/email/mobile LIKE %search%)
     * - status: string (matches tb_mas_applicant_data.status)
     * - campus: string (matches tb_mas_users.campus)
     * - date_from: Y-m-d or Y-m-d H:i:s (applies to applicant_data.created_at)
     * - date_to: Y-m-d or Y-m-d H:i:s (applies to applicant_data.created_at)
     * - page: int -> when present, returns paginated response
     * - per_page: int (default: 20)
     */
    public function index(Request $request): JsonResponse
    {
        // Build a subquery to get latest applicant_data per user_id
        // $latestApplicantData = DB::table('tb_mas_applicant_data as ad1')
        //     ->select('ad1.id')
        //     ->join('tb_mas_applicant_data as ad2', function ($join) {
        //         $join->on('ad1.user_id', '=', 'ad2.user_id')
        //              ->on('ad1.id', '<=', 'ad2.id');
        //     })
        //     ->groupBy('ad1.id', 'ad1.user_id')
        //     ->havingRaw('ad1.id = MAX(ad2.id)');
                    
        // Base query joining users to latest applicant data (correlated subquery for latest row)
        $q = DB::table('tb_mas_users as u')
            ->join('tb_mas_applicant_data as ad', 'ad.user_id', '=', 'u.intID')
            ->leftJoin('tb_mas_campuses as c', 'c.id', '=', 'u.campus_id')
            ->whereRaw('ad.id = (SELECT MAX(adx.id) FROM tb_mas_applicant_data adx WHERE adx.user_id = u.intID)')
            ->select([
                'u.intID as id',
                'u.strFirstname',
                'u.strLastname',
                'u.strEmail',
                DB::raw('COALESCE(u.strMobileNumber, \'\') as strMobileNumber'),
                // Prefer campus name; fallback to legacy u.campus then campus_id then blank
                DB::raw('COALESCE(c.campus_name,u.campus_id, \'\') as campus'),
                DB::raw('COALESCE(u.student_type, \'\') as student_type'),
                DB::raw('COALESCE(u.dteCreated, NULL) as dteCreated'),
                'ad.status',
                DB::raw('COALESCE(ad.interviewed, 0) as interviewed'),
                'ad.created_at as application_created_at',
            ]);        
        // Filter: restrict to users tagged as applicant when schema supports it
        // $columns = $this->getUserColumns();
        // if (in_array('student_status', $columns)) {
        //     $q->where('u.student_status', 'applicant');
        // } else if (in_array('enumEnrolledStatus', $columns)) {
        //     $q->where('u.enumEnrolledStatus', 'applicant');
        // }

        // Filters
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $q->where(function ($w) use ($like) {
                $w->where('u.strFirstname', 'like', $like)
                  ->orWhere('u.strLastname', 'like', $like)
                  ->orWhere('u.strEmail', 'like', $like)
                  ->orWhere('u.strMobileNumber', 'like', $like);
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $q->where('ad.status', $status);
        }

        $campus = trim((string) $request->query('campus', ''));
        if ($campus !== '') {
            // Support filtering by campus name (c.campus_name), legacy u.campus, or campus_id
            $q->where(function ($w) use ($campus) {
                $w->where('c.campus_name', $campus)                  
                  ->orWhere('u.campus_id', $campus);
            });
        }

        $dateFrom = trim((string) $request->query('date_from', ''));
        if ($dateFrom !== '') {
            $q->where('ad.created_at', '>=', date('Y-m-d H:i:s', strtotime($dateFrom)));
        }

        $dateTo = trim((string) $request->query('date_to', ''));
        if ($dateTo !== '') {
            $q->where('ad.created_at', '<=', date('Y-m-d H:i:s', strtotime($dateTo)));
        }

        // Optional: filter by term/school year id (syid). Accept alias 'term' for parity with other endpoints.
        $syidParam = $request->query('syid', null);
        $termParam = $request->query('term', null);
        $syid = null;
        if ($syidParam !== null && $syidParam !== '') {
            $syid = is_numeric($syidParam) ? (int) $syidParam : null;
        }
        if ($syid === null && $termParam !== null && $termParam !== '') {
            $syid = is_numeric($termParam) ? (int) $termParam : null;
        }
        if ($syid !== null) {
            $q->where('ad.syid', $syid);
        }

        // Sorting
        $sort = (string) $request->query('sort', 'application_created_at');
        $order = strtolower((string) $request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['application_created_at', 'strLastname', 'strFirstname', 'strEmail', 'campus', 'status'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'application_created_at';
        }

        // Pagination optional
        $paginate = $request->filled('page') || $request->filled('per_page');
        if ($paginate) {
            $perPage = max(1, (int) $request->query('per_page', 20));
            $page = max(1, (int) $request->query('page', 1));

            $total = (clone $q)->count();
            $rows = (clone $q)->orderBy($sort, $order)
                ->forPage($page, $perPage)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $rows,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        }

        $rows = $q->orderBy($sort, $order)->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * GET /api/v1/applicants/{id}
     *
     * Returns:
     * {
     *   success: true,
     *   data: {
     *     user: {...},
     *     status: "new|...",
     *     applicant_data: { ...decoded JSON... },
     *     created_at: "...",
     *     updated_at: "..."
     *   }
     * }
     */
    public function show(int $id): JsonResponse
    {
        // Fetch core user
        $user = DB::table('tb_mas_users')->where('intID', $id)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Applicant not found',
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
                'message' => 'Applicant data not found',
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
                // New surfaced fields
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

    /**
     * PUT /api/v1/applicants/{id}
     *
     * Updates core identity/contact fields both in tb_mas_users and in the latest tb_mas_applicant_data JSON.
     * Fields accepted: first_name, middle_name, last_name, email, mobile_number, date_of_birth
     */
    public function update(ApplicantUpdateRequest $request, int $id): JsonResponse
    {
        // Fetch core user
        $user = DB::table('tb_mas_users')->where('intID', $id)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Applicant not found',
            ], 404);
        }

        // Fetch latest applicant_data row (required for update-in-place)
        $appData = DB::table('tb_mas_applicant_data')
            ->where('user_id', $id)
            ->orderByDesc('id')
            ->first();

        if (!$appData) {
            return response()->json([
                'success' => false,
                'message' => 'Applicant data not found for update',
            ], 404);
        }

        // Decode current applicant_data JSON
        $currentData = [];
        if (isset($appData->data)) {
            try {
                $currentData = is_string($appData->data) ? json_decode($appData->data, true) : (array) $appData->data;
                if (!is_array($currentData)) $currentData = [];
            } catch (\Throwable $e) {
                $currentData = [];
            }
        }

        $columns = $this->getUserColumns();

        // Prepare old snapshot for logging (only the fields we touch)
        $oldUserSnapshot = [
            'first_name'    => property_exists($user, 'strFirstname') ? $user->strFirstname : null,
            'middle_name'   => property_exists($user, 'strMiddlename') ? $user->strMiddlename : null,
            'last_name'     => property_exists($user, 'strLastname') ? $user->strLastname : null,
            'email'         => property_exists($user, 'strEmail') ? $user->strEmail : null,
            'mobile_number' => property_exists($user, 'strMobileNumber') ? $user->strMobileNumber : null,
            'date_of_birth' => property_exists($user, 'dteBirthDate') ? $user->dteBirthDate : null,
        ];
        $oldAppSnapshot = [
            'first_name'    => $currentData['first_name']    ?? null,
            'middle_name'   => $currentData['middle_name']   ?? null,
            'last_name'     => $currentData['last_name']     ?? null,
            'email'         => $currentData['email']         ?? null,
            'mobile_number' => $currentData['mobile_number'] ?? null,
            'date_of_birth' => $currentData['date_of_birth'] ?? ($currentData['dob'] ?? null),
        ];

        // Build requested payload fields (only set keys that were sent by client)
        $payload = [];
        if ($request->has('first_name')) {
            $payload['first_name'] = $request->input('first_name');
        }
        if ($request->has('middle_name')) {
            $payload['middle_name'] = $request->input('middle_name');
        }
        if ($request->has('last_name')) {
            $payload['last_name'] = $request->input('last_name');
        }
        if ($request->has('email')) {
            $payload['email'] = $request->input('email');
        }
        if ($request->has('mobile_number')) {
            $payload['mobile_number'] = $request->input('mobile_number');
        }
        if ($request->has('date_of_birth')) {
            $dobIn = $request->input('date_of_birth');
            $ts = strtotime((string) $dobIn);
            $payload['date_of_birth'] = $ts ? date('Y-m-d', $ts) : $dobIn;
        }

        // Map to tb_mas_users columns when present in this installation
        $userUpdates = [];
        if (array_key_exists('first_name', $payload) && in_array('strFirstname', $columns)) {
            $userUpdates['strFirstname'] = (string) $payload['first_name'];
        }
        if (array_key_exists('middle_name', $payload) && in_array('strMiddlename', $columns)) {
            $userUpdates['strMiddlename'] = $payload['middle_name'] !== null ? (string) $payload['middle_name'] : null;
        }
        if (array_key_exists('last_name', $payload) && in_array('strLastname', $columns)) {
            $userUpdates['strLastname'] = (string) $payload['last_name'];
        }
        if (array_key_exists('email', $payload) && in_array('strEmail', $columns)) {
            $userUpdates['strEmail'] = (string) $payload['email'];
        }
        if (array_key_exists('mobile_number', $payload) && in_array('strMobileNumber', $columns)) {
            $userUpdates['strMobileNumber'] = (string) $payload['mobile_number'];
        }
        if (array_key_exists('date_of_birth', $payload) && in_array('dteBirthDate', $columns)) {
            $userUpdates['dteBirthDate'] = (string) $payload['date_of_birth'];
        }

        // Waiver updates for tb_mas_applicant_data row
        $applicantDataUpdates = [];
        if ($request->has('waive_application_fee')) {
            $newFlagRaw = $request->input('waive_application_fee');
            $newFlag = filter_var($newFlagRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $newFlag = (bool) ($newFlag ?? $newFlagRaw);
            $currentFlag = isset($appData->waive_application_fee) ? (bool) $appData->waive_application_fee : false;

            $applicantDataUpdates['waive_application_fee'] = $newFlag;
            if ($newFlag && !$currentFlag) {
                $applicantDataUpdates['waived_at'] = now();
                $fid = $request->header('X-Faculty-ID');
                if (is_numeric($fid)) {
                    $applicantDataUpdates['waived_by_user_id'] = (int) $fid;
                }
            } elseif (!$newFlag) {
                $applicantDataUpdates['waived_at'] = null;
                $applicantDataUpdates['waived_by_user_id'] = null;
            }
        }
        if ($request->has('waive_reason')) {
            $reason = trim((string) $request->input('waive_reason'));
            $applicantDataUpdates['waive_reason'] = ($reason === '') ? null : $reason;
        }

        DB::beginTransaction();
        try {
            if (!empty($userUpdates)) {
                DB::table('tb_mas_users')->where('intID', $id)->update($userUpdates);
            }

            // Merge payload into latest applicant_data JSON
            $merged = $currentData;
            foreach (['first_name','middle_name','last_name','email','mobile_number','date_of_birth'] as $k) {
                if (array_key_exists($k, $payload)) {
                    $merged[$k] = $payload[$k];
                }
            }

            DB::table('tb_mas_applicant_data')->where('id', $appData->id)->update(array_merge([
                'data' => json_encode($merged, JSON_UNESCAPED_UNICODE),
            ], $applicantDataUpdates));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update applicant: ' . $e->getMessage(),
            ], 500);
        }

        // Fetch updated snapshots
        $updatedUser = DB::table('tb_mas_users')->where('intID', $id)->first();
        $updatedAppData = DB::table('tb_mas_applicant_data')->where('id', $appData->id)->first();

        $updatedDecoded = null;
        if ($updatedAppData && isset($updatedAppData->data)) {
            try {
                $updatedDecoded = is_string($updatedAppData->data) ? json_decode($updatedAppData->data, true) : $updatedAppData->data;
            } catch (\Throwable $e) {
                $updatedDecoded = null;
            }
        }

        // Prepare new snapshot for logging
        $newUserSnapshot = [
            'first_name'    => property_exists($updatedUser, 'strFirstname') ? $updatedUser->strFirstname : null,
            'middle_name'   => property_exists($updatedUser, 'strMiddlename') ? $updatedUser->strMiddlename : null,
            'last_name'     => property_exists($updatedUser, 'strLastname') ? $updatedUser->strLastname : null,
            'email'         => property_exists($updatedUser, 'strEmail') ? $updatedUser->strEmail : null,
            'mobile_number' => property_exists($updatedUser, 'strMobileNumber') ? $updatedUser->strMobileNumber : null,
            'date_of_birth' => property_exists($updatedUser, 'dteBirthDate') ? $updatedUser->dteBirthDate : null,
        ];
        $newAppSnapshot = [
            'first_name'    => is_array($updatedDecoded) ? ($updatedDecoded['first_name']    ?? null) : null,
            'middle_name'   => is_array($updatedDecoded) ? ($updatedDecoded['middle_name']   ?? null) : null,
            'last_name'     => is_array($updatedDecoded) ? ($updatedDecoded['last_name']     ?? null) : null,
            'email'         => is_array($updatedDecoded) ? ($updatedDecoded['email']         ?? null) : null,
            'mobile_number' => is_array($updatedDecoded) ? ($updatedDecoded['mobile_number'] ?? null) : null,
            'date_of_birth' => is_array($updatedDecoded) ? ($updatedDecoded['date_of_birth'] ?? ($updatedDecoded['dob'] ?? null)) : null,
        ];

        // System log
        try {
            SystemLogService::log(
                'update',
                'Applicant',
                $id,
                ['user' => $oldUserSnapshot, 'applicant_data' => $oldAppSnapshot],
                ['user' => $newUserSnapshot, 'applicant_data' => $newAppSnapshot],
                $request
            );
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $updatedUser,
                'status' => $updatedAppData->status ?? null,
                'applicant_data' => $updatedDecoded,
                'created_at' => $updatedAppData->created_at ?? null,
                'updated_at' => $updatedAppData->updated_at ?? null,
                // Surfaced fields post-update
                'applicant_type' => isset($updatedAppData->applicant_type) ? (int) $updatedAppData->applicant_type : null,
                'paid_application_fee' => isset($updatedAppData->paid_application_fee) ? (bool) $updatedAppData->paid_application_fee : null,
                'paid_reservation_fee' => isset($updatedAppData->paid_reservation_fee) ? (bool) $updatedAppData->paid_reservation_fee : null,
                'waive_application_fee' => isset($updatedAppData->waive_application_fee) ? (bool) $updatedAppData->waive_application_fee : null,
                'waive_reason' => $updatedAppData->waive_reason ?? null,
                'waived_at' => $updatedAppData->waived_at ?? null,
            ],
        ]);
    }

    /**
     * GET /api/v1/applicants/template
     *
     * Downloads an Excel template for applicants import.
     */
    public function template()
    {
        $export = new ApplicantImportTemplateExport();
        $spreadsheet = $export->build();

        $tempDir = storage_path('framework/cache/laravel-excel');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = tempnam($tempDir, 'applicant_template_');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, 'applicants-import-template.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Helper to introspect tb_mas_users columns safely.
     */
    protected function getUserColumns(): array
    {
        try {
            // Using information_schema for portability across environments that may not have Schema facade configured for legacy tables
            $db = config('database.connections.mysql.database');
            $rows = DB::table('information_schema.COLUMNS')
                ->select('COLUMN_NAME')
                ->where('TABLE_SCHEMA', $db)
                ->where('TABLE_NAME', 'tb_mas_users')
                ->get();
            return $rows->pluck('COLUMN_NAME')->map(function ($c) {
                return (string) $c;
            })->toArray();
        } catch (\Throwable $e) {
            // Fallback to a reasonable default set used by AdmissionsController
            return [
                'strFirstname', 'strMiddlename', 'strLastname',
                'strEmail', 'strMobileNumber', 'enumGender',
                'dteBirthDate', 'intTuitionYear', 'strAddress',
                'intProgramID', 'student_type', 'dteCreated',
                'strUsername', 'strPass', 'student_status',
                'enumEnrolledStatus', 'slug', 'campus'
            ];
        }
    }

    /**
     * GET /api/v1/enlistment/applicants
     *
     * Lists applicants eligible for enlistment:
     * - tb_mas_applicant_data.status = 'Reserved'
     * - paid_application_fee = 1
     * - paid_reservation_fee = 1
     *
     * Optional filters:
     * - search, campus (same as index)
     * - syid or term (alias) to scope by term when available
     * Supports pagination and sorting.
     */
    public function eligibleForEnlistment(Request $request): JsonResponse
    {
        // Guard: ensure required table/columns exist to avoid SQL errors in partially migrated environments
        try {
            $has = \Illuminate\Support\Facades\Schema::hasTable('tb_mas_applicant_data')
                && \Illuminate\Support\Facades\Schema::hasColumn('tb_mas_applicant_data', 'status')
                && \Illuminate\Support\Facades\Schema::hasColumn('tb_mas_applicant_data', 'paid_application_fee')
                && \Illuminate\Support\Facades\Schema::hasColumn('tb_mas_applicant_data', 'paid_reservation_fee');
        } catch (\Throwable $e) {
            $has = false;
        }
        if (!$has) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'meta'    => ['current_page' => 1, 'per_page' => 10, 'total' => 0, 'last_page' => 1],
            ]);
        }

        // Base query: latest applicant_data per user_id
        $q = DB::table('tb_mas_users as u')
            ->join('tb_mas_applicant_data as ad', 'ad.user_id', '=', 'u.intID')
            ->leftJoin('tb_mas_campuses as c', 'c.id', '=', 'u.campus_id')
            ->whereRaw('ad.id = (SELECT MAX(adx.id) FROM tb_mas_applicant_data adx WHERE adx.user_id = u.intID)')
            ->select([
                'u.intID as id',
                'u.strFirstname',
                'u.strLastname',
                'u.strEmail',
                DB::raw('COALESCE(u.strMobileNumber, \'\') as strMobileNumber'),
                DB::raw('COALESCE(c.campus_name,u.campus_id, \'\') as campus'),
                DB::raw('COALESCE(u.student_type, \'\') as student_type'),
                DB::raw('COALESCE(u.dteCreated, NULL) as dteCreated'),
                'ad.status',
                DB::raw('COALESCE(ad.paid_application_fee, 0) as paid_application_fee'),
                DB::raw('COALESCE(ad.paid_reservation_fee, 0) as paid_reservation_fee'),
                DB::raw('COALESCE(ad.interviewed, 0) as interviewed'),
                'ad.syid',
                'ad.created_at as application_created_at',
            ])
            ->where('ad.status', 'Reserved')
            ->where('ad.paid_application_fee', 1)
            ->where('ad.paid_reservation_fee', 1);

        // Filters
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
            $q->where(function ($w) use ($like) {
                $w->where('u.strFirstname', 'like', $like)
                  ->orWhere('u.strLastname', 'like', $like)
                  ->orWhere('u.strEmail', 'like', $like)
                  ->orWhere('u.strMobileNumber', 'like', $like);
            });
        }

        $campus = trim((string) $request->query('campus', ''));
        if ($campus !== '') {
            $q->where(function ($w) use ($campus) {
                $w->where('c.campus_name', $campus)
                  ->orWhere('u.campus_id', $campus);
            });
        }

        // Optional term filter (syid or alias term)
        $syidParam = $request->query('syid', null);
        $termParam = $request->query('term', null);
        $syid = null;
        if ($syidParam !== null && $syidParam !== '') {
            $syid = is_numeric($syidParam) ? (int) $syidParam : null;
        }
        if ($syid === null && $termParam !== null && $termParam !== '') {
            $syid = is_numeric($termParam) ? (int) $termParam : null;
        }
        if ($syid !== null) {
            $q->where('ad.syid', $syid);
        }

        // Sorting
        $sort = (string) $request->query('sort', 'application_created_at');
        $order = strtolower((string) $request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['application_created_at', 'strLastname', 'strFirstname', 'strEmail', 'campus', 'status'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'application_created_at';
        }

        // Pagination optional (default per_page=20)
        $paginate = $request->filled('page') || $request->filled('per_page');
        if ($paginate) {
            $perPage = max(1, (int) $request->query('per_page', 20));
            $page = max(1, (int) $request->query('page', 1));

            $total = (clone $q)->count();
            $rows = (clone $q)->orderBy($sort, $order)
                ->forPage($page, $perPage)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $rows,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        }

        $rows = $q->orderBy($sort, $order)->get();
        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }
}
