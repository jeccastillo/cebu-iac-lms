<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClasslistStoreRequest;
use App\Http\Requests\Api\V1\ClasslistUpdateRequest;
use App\Models\Classlist;
use App\Models\ClasslistStudent;
use App\Services\SystemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\V1\ClasslistAssignFacultyBulkRequest;
use App\Exports\FacultyAssignmentsExport;
use Maatwebsite\Excel\Facades\Excel;

class ClasslistController extends Controller
{
    /**
     * GET /api/v1/classlists
     * Query params:
     *  - includeDissolved: bool (default false) => when false, filter isDissolved = 0
     *  - strAcademicYear|term: string (syid/term)
     *  - intSubjectID: int
     *  - intFacultyID: int
     *  - intFinalized: int
     */
    public function index(Request $request): JsonResponse
    {
        $query = Classlist::query()
            ->leftJoin('tb_mas_subjects as s', 's.intID', '=', 'tb_mas_classlist.intSubjectID')
            ->leftJoin('tb_mas_faculty as f', 'f.intID', '=', 'tb_mas_classlist.intFacultyID')
            ->select(
                'tb_mas_classlist.*',
                's.strCode as subjectCode',
                's.strDescription as subjectDescription',
                'f.strFirstname as facultyFirstname',
                'f.strLastname as facultyLastname'
            );

        $includeDissolved = filter_var($request->query('includeDissolved', 'false'), FILTER_VALIDATE_BOOLEAN);
        if (!$includeDissolved) {
            $query->where('tb_mas_classlist.isDissolved', 0);
        }

        // Accept either strAcademicYear or term for convenience
        $term = $request->query('strAcademicYear', $request->query('term', null));
        if ($term !== null && $term !== '') {
            $query->where('tb_mas_classlist.strAcademicYear', $term);
        }

        if ($request->filled('intSubjectID')) {
            $query->where('tb_mas_classlist.intSubjectID', (int) $request->query('intSubjectID'));
        }
        if ($request->filled('intFacultyID')) {
            $query->where('tb_mas_classlist.intFacultyID', (int) $request->query('intFacultyID'));
        }
        // Optional filter: sectionCode (supports partial match)
        if ($request->filled('sectionCode')) {
            $section = trim((string) $request->query('sectionCode', ''));
            if ($section !== '') {
                $query->where('tb_mas_classlist.sectionCode', 'like', '%' . str_replace(['%', '_'], ['\\%','\\_'], $section) . '%');
            }
        }
        // Optional filter: subjectCode (supports partial match on subject strCode)
        if ($request->filled('subjectCode')) {
            $subj = trim((string) $request->query('subjectCode', ''));
            if ($subj !== '') {
                $query->where('s.strCode', 'like', '%' . str_replace(['%', '_'], ['\\%','\\_'], $subj) . '%');
            }
        }
        // Use query() presence check instead of filled() so that "0" is a valid filter value
        if ($request->query('intFinalized', '') !== '') {
            $query->where('tb_mas_classlist.intFinalized', (int) $request->query('intFinalized'));
        }

        // Pagination
        $perPage = (int) $request->query('per_page', 20);
        if ($perPage < 1) { $perPage = 1; }
        if ($perPage > 100) { $perPage = 100; }
        $page = (int) $request->query('page', 1);
        if ($page < 1) { $page = 1; }

        $paginator = $query
            ->orderBy('tb_mas_classlist.intID', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
        }

    /**
     * GET /api/v1/classlists/{id}
     */
    public function show(int $id): JsonResponse
    {
        $classlist = Classlist::find($id);
        if (!$classlist) {
            return response()->json([
                'success' => false,
                'message' => 'Classlist not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $classlist,
        ]);
    }

    /**
     * POST /api/v1/classlists
     * Body: payload validating ClasslistStoreRequest
     * Note: strClassName, year, strSection, sub_section are always saved as "".
     */
    public function store(ClasslistStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Enforce restricted fields to blank strings
        $this->applyRestrictedBlank($data);

        // Defaults
        if (!array_key_exists('intFinalized', $data) || $data['intFinalized'] === null) {
            $data['intFinalized'] = 0;
        }
        // Keep dissolved default 0 if present on table
        if (!array_key_exists('isDissolved', $data)) {
            $data['isDissolved'] = 0;
        }

        $classlist = Classlist::create($data);

        // System log: create
        SystemLogService::log('create', 'Classlist', (int) $classlist->getKey(), null, $classlist->toArray(), $request);

        return response()->json([
            'success' => true,
            'data' => $classlist,
        ], 201);
    }

    /**
     * PUT /api/v1/classlists/{id}
     * Body: payload validating ClasslistUpdateRequest
     * Note: strClassName, year, strSection, sub_section are always saved as "".
     */
    public function update(ClasslistUpdateRequest $request, int $id): JsonResponse
    {
        $classlist = Classlist::find($id);
        if (!$classlist) {
            return response()->json([
                'success' => false,
                'message' => 'Classlist not found',
            ], 404);
        }

        $old = $classlist->toArray();
        $data = $request->validated();

        // Enforce faculty assignment constraints when provided
        if (array_key_exists('intFacultyID', $data)) {
            // Unassign branch: allow clearing assignment when null (still block dissolved)
            if ($data['intFacultyID'] === null) {
                if ((int)($classlist->isDissolved ?? 0) === 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot unassign faculty from a dissolved classlist.',
                    ], 422);
                }
                // Skip further faculty validations; proceed to update below
            } else {
                if ((int)($classlist->isDissolved ?? 0) === 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot assign faculty to a dissolved classlist.',
                    ], 422);
                }
                $fid = (int) $data['intFacultyID'];
                $faculty = DB::table('tb_mas_faculty')->where('intID', $fid)->first();
                if (!$faculty) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Faculty not found.',
                    ], 422);
                }
                if ((int) ($faculty->teaching ?? 0) !== 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected faculty is not marked as teaching.',
                    ], 422);
                }
                $clCampus = $classlist->campus_id ?? null;
                $facCampus = $faculty->campus_id ?? null;
                if ($clCampus !== null) {
                    if ($facCampus === null || (int) $clCampus !== (int) $facCampus) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Campus mismatch between classlist and faculty.',
                        ], 422);
                    }
                }
            }
        }

        // Always enforce restricted fields to blank strings on update
        $this->applyRestrictedBlank($data);

        // Perform update
        if (!empty($data)) {
            $classlist->update($data);
        }

        $new = $classlist->fresh();

        // System log: update
        SystemLogService::log('update', 'Classlist', (int) $classlist->getKey(), $old, $new->toArray(), $request);

        return response()->json([
            'success' => true,
            'data' => $new,
        ]);
    }

    /**
     * DELETE /api/v1/classlists/{id}
     * Legacy behavior: Dissolve (set isDissolved=1) instead of hard delete,
     * only if there are no related tb_mas_classlist_student rows.
     */
    public function destroy(int $id): JsonResponse
    {
        $classlist = Classlist::find($id);
        if (!$classlist) {
            return response()->json([
                'success' => false,
                'message' => 'Classlist not found',
            ], 404);
        }

        $hasStudents = ClasslistStudent::where('intClassListID', $id)->exists();
        if ($hasStudents) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot dissolve classlist; students exist.',
            ], 422);
        }

        // Idempotent dissolve
        if ((int) ($classlist->isDissolved ?? 0) === 1) {
            return response()->json([
                'success' => true,
                'message' => 'Classlist already dissolved',
            ]);
        }

        $old = $classlist->toArray();
        $classlist->update(['isDissolved' => 1]);
        $new = $classlist->fresh();

        // System log: update (dissolve)
        SystemLogService::log('update', 'Classlist', (int) $classlist->getKey(), $old, $new->toArray(), request());

        return response()->json([
            'success' => true,
            'message' => 'Classlist dissolved',
        ]);
    }

    /**
     * Bulk assign faculty to classlists with validations.
     * POST /api/v1/classlists/assign-faculty-bulk
     */
    use App\Services\ScheduleImportService;

    public function assignFacultyBulk(ClasslistAssignFacultyBulkRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $term = (int) ($payload['term'] ?? 0);
        $assignments = $payload['assignments'] ?? [];

        $results = [];
        $applied = 0;

        $scheduleService = new ScheduleImportService();

        foreach ($assignments as $idx => $item) {
            $cid = (int) ($item['classlist_id'] ?? 0);
            $fid = (int) ($item['faculty_id'] ?? 0);

            try {
                $classlist = \App\Models\Classlist::find($cid);
                if (!$classlist) {
                    $results[] = ['classlist_id' => $cid, 'ok' => false, 'message' => 'Classlist not found'];
                    continue;
                }
                if ((int)($classlist->isDissolved ?? 0) === 1) {
                    $results[] = ['classlist_id' => $cid, 'ok' => false, 'message' => 'Classlist dissolved'];
                    continue;
                }
                // Term check
                $clTerm = (int) ($classlist->strAcademicYear ?? 0);
                if ($term > 0 && $clTerm !== $term) {
                    $results[] = ['classlist_id' => $cid, 'ok' => false, 'message' => 'Classlist term does not match provided term'];
                    continue;
                }

                $faculty = DB::table('tb_mas_faculty')->where('intID', $fid)->first();
                if (!$faculty) {
                    $results[] = ['classlist_id' => $cid, 'ok' => false, 'message' => 'Faculty not found'];
                    continue;
                }
                if ((int) ($faculty->teaching ?? 0) !== 1) {
                    $results[] = ['classlist_id' => $cid, 'ok' => false, 'message' => 'Selected faculty is not marked as teaching'];
                    continue;
                }

                $clCampus = $classlist->campus_id ?? null;
                $facCampus = $faculty->campus_id ?? null;
                if ($clCampus !== null) {
                    if ($facCampus === null || (int) $clCampus !== (int) $facCampus) {
                        $results[] = ['classlist_id' => $cid, 'ok' => false, 'message' => 'Campus mismatch between classlist and faculty'];
                        continue;
                    }
                }

                // Schedule conflict check
                $conflicts = $scheduleService->checkFacultyConflicts(
                    ['intClasslistID' => $cid],
                    null,
                    $fid
                );
                if (!empty($conflicts)) {
                    $results[] = [
                        'classlist_id' => $cid,
                        'ok' => false,
                        'message' => 'Faculty schedule conflict detected',
                        'conflicts' => $conflicts,
                    ];
                    continue;
                }

                $old = $classlist->toArray();
                $classlist->update(['intFacultyID' => $fid]);
                $new = $classlist->fresh();

                try {
                    SystemLogService::log('update', 'Classlist', (int) $classlist->getKey(), $old, $new->toArray(), $request);
                } catch (\Throwable $e) {
                    // swallow logging errors
                }

                $applied++;
                $results[] = ['classlist_id' => $cid, 'ok' => true];
            } catch (\Throwable $e) {
                $results[] = ['classlist_id' => $cid, 'ok' => false, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => true,
            'applied_count' => $applied,
            'total' => count($assignments),
            'results' => $results,
        ]);
    }

    /**
     * GET /api/v1/classlists/export-faculty-assignments
     *
     * Streams an XLSX of faculty assignments for a term with optional filters.
     * Guards: role:registrar,faculty_admin,admin (via routes).
     */
    public function exportFacultyAssignments(Request $request)
    {
        $payload = $request->validate([
            'term'              => ['required', 'integer'],
            'intFacultyID'      => ['sometimes', 'integer'],
            'sectionCode'       => ['sometimes', 'string'],
            'subjectCode'       => ['sometimes', 'string'],
            'includeUnassigned' => ['sometimes', 'boolean'],
            'includeDissolved'  => ['sometimes', 'boolean'],
        ]);

        $filters = [];
        foreach (['term','intFacultyID','sectionCode','subjectCode','includeUnassigned','includeDissolved'] as $key) {
            if (array_key_exists($key, $payload)) {
                $filters[$key] = $payload[$key];
            }
        }

        $filename = 'faculty-assignments-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new FacultyAssignmentsExport($filters), $filename);
    }

    /**
     * Ensure restricted fields are blank in the provided array by reference.
     * Restricted: strClassName, year, strSection, sub_section
     * Note: 'year' is an integer column in legacy DB; use 0 instead of '' to satisfy strict SQL mode.
     */
    private function applyRestrictedBlank(array &$data): void
    {
        // String fields → empty string
        $data['strClassName'] = '';
        $data['strSection']   = '';
        $data['sub_section']  = '';

        // Integer field 'year' → 0 (avoid '' which fails with SQL strict mode)
        $data['year'] = 0;
    }
}
