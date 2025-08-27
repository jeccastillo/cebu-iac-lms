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
