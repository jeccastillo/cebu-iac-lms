<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $latestApplicantData = DB::table('tb_mas_applicant_data as ad1')
            ->select('ad1.id')
            ->join('tb_mas_applicant_data as ad2', function ($join) {
                $join->on('ad1.user_id', '=', 'ad2.user_id')
                     ->on('ad1.id', '<=', 'ad2.id');
            })
            ->groupBy('ad1.id', 'ad1.user_id')
            ->havingRaw('ad1.id = MAX(ad2.id)');

        // Base query joining users to latest applicant data (correlated subquery for latest row)
        $q = DB::table('tb_mas_users as u')
            ->join('tb_mas_applicant_data as ad', 'ad.user_id', '=', 'u.intID')
            ->whereRaw('ad.id = (SELECT MAX(adx.id) FROM tb_mas_applicant_data adx WHERE adx.user_id = u.intID)')
            ->select([
                'u.intID as id',
                'u.strFirstname',
                'u.strLastname',
                'u.strEmail',
                DB::raw('COALESCE(u.strMobileNumber, \'\') as strMobileNumber'),
                DB::raw('COALESCE(u.campus_id, \'\') as campus'),
                DB::raw('COALESCE(u.student_type, \'\') as student_type'),
                DB::raw('COALESCE(u.dteCreated, NULL) as dteCreated'),
                'ad.status',
                'ad.created_at as application_created_at',
            ]);

        // Filter: restrict to users tagged as applicant when schema supports it
        $columns = $this->getUserColumns();
        if (in_array('student_status', $columns)) {
            $q->where('u.student_status', 'applicant');
        } else if (in_array('enumEnrolledStatus', $columns)) {
            $q->where('u.enumEnrolledStatus', 'applicant');
        }

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
            $q->where('u.campus', $campus);
        }

        $dateFrom = trim((string) $request->query('date_from', ''));
        if ($dateFrom !== '') {
            $q->where('ad.created_at', '>=', date('Y-m-d H:i:s', strtotime($dateFrom)));
        }

        $dateTo = trim((string) $request->query('date_to', ''));
        if ($dateTo !== '') {
            $q->where('ad.created_at', '<=', date('Y-m-d H:i:s', strtotime($dateTo)));
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

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'status' => $appData->status ?? null,
                'applicant_data' => $decoded,
                'created_at' => $appData->created_at ?? null,
                'updated_at' => $appData->updated_at ?? null,
            ],
        ]);
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
}
